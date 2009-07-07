#include <stdio.h>
#include <stdlib.h>
#include <mysql.h>
#include <linux/limits.h>
#include <string.h>
#include <time.h>
#include <sys/types.h>
#include <unistd.h>
#include "hash.h"
#include "redirect-config.h"
#include "tools.h"
#include "abort.h"
#include "constants.h"
#include "version.h"

#define ERROR_HOST_NOT_FOUND	6
#define ERROR_HOST_DISABLED		11

MYSQL mysql;
struct hash *hash;
FILE *log_fp;

struct host_info {
	char *name;						/* numele hostului */
	char *ip;						/* ip-ul */
	int port;
	time_t t;						/* timestamp-ul ultimei actualizari */
	int r;							/* raspunsul de redirectare */
};

struct config cfg={
	"localhost",						/* sql_host */
	"",									/* sql_user */
	"",									/* sql_pw */
	"",									/* sql_db */
	"/tmp/mysql.sock",					/* sql_sock */
	"/var/log/squid/redirect.log",		/* log_path */
	"http://192.168.0.1/badurl.php",	/* out_path */
	30									/* cache_timeout */
};

int pid;

int hash_algo(void *e, int n) {
	int s=0;
	char *p;

	p=((struct host_info *)e)->name;
	while(*p!='\0') s+=*(p++);
	return s%n;
}

int hash_cmp(void *e1, void *e2) {
	return strcmp(((struct host_info *)e1)->name, ((struct host_info *)e2)->name);
}

int sql_connect(struct config *cfg) {
	mysql_init(&mysql);
	//mysql_options(&mysql, MYSQL_READ_DEFAULT_GROUP, "simple");
	if(!mysql_real_connect(&mysql, cfg->sql_host, cfg->sql_user,
				cfg->sql_pw, cfg->sql_db, 0, cfg->sql_sock, 0)) {
		fprintf(stderr, "MySQL connection failed: %s\n", mysql_error(&mysql));
		return 1;
	}
#ifdef DEBUG
	fprintf(stderr, "Successfully connected to %s as user %s, database %s\n",
				cfg->sql_host, cfg->sql_user, cfg->sql_db);
#endif
	return 0;
}

void sql_close(void) {
	mysql_close(&mysql);
}

const char query1[]="SELECT server_ip, server_port, enabled FROM sites WHERE name='%s' LIMIT 1";
const char query2[]="SELECT site_idx FROM site_aliases WHERE name='%s' LIMIT 1";
const char query3[]="SELECT server_ip, server_port, enabled FROM sites WHERE site_idx=%s";

int sql_host_info(char *host, char *ip, int *port) {
	/* intoarce:
	 * 0 - hostul nu exista
	 * 1 - hostul exista si e dezactivat
	 * 2 - hostul exista si e activat
	 */
	MYSQL_RES *res;
	MYSQL_ROW r;
	char query[MAX_QUERY_LENGTH+1];
	int retval=0;
	int tmp;

	if (!validate_hostname(host)) {
#ifdef DEBUG
		fprintf(stderr, "Invalid hostname '%s'\n", host);
#endif
		return 0;
	}
	
	sprintf(query, query1, host);
	CRITICAL(mysql_query(&mysql, query));
	res=mysql_store_result(&mysql);
	if(mysql_num_rows(res)) {
		/* e nume primar */
#ifdef DEBUG
		fprintf(stderr, "Host '%s' is primary\n", host);
#endif
		r=mysql_fetch_row(res);
		strncpy(ip, r[0], MAX_IP_LENGTH);
		ip[MAX_IP_LENGTH]='\0';
		sscanf(r[1], "%d", port);
		sscanf(r[2], "%d", &tmp);
		retval=tmp?2:1;
	} else {
		/* nu e nume primar, deci e alias sau nu exista deloc */
		sprintf(query, query2, host);
		mysql_free_result(res);
		CRITICAL(mysql_query(&mysql, query));
		res=mysql_store_result(&mysql);
		if(mysql_num_rows(res)) {
			/* e un alias si caut info despre host */
			r=mysql_fetch_row(res);
			sprintf(query, query3, r[0]);
			mysql_free_result(res);
			CRITICAL(mysql_query(&mysql, query));
			res=mysql_store_result(&mysql);
			if(mysql_num_rows(res)) {
				/* am gasit hostul */
				r=mysql_fetch_row(res);
				strncpy(ip, r[0], MAX_IP_LENGTH);
				ip[MAX_IP_LENGTH]='\0';
				sscanf(r[1], "%d", port);
				sscanf(r[2], "%d", &tmp);
				retval=tmp?2:1;
#ifdef DEBUG
				fprintf(stderr, "Host '%s' is an alias for '%s'\n", host, r[0]);
#endif
			} else {
				/* nu am gasit hostul, deci e un alias broken */
#ifdef DEBUG
				fprintf(stderr, "Broken alias for host '%s', idx '%s'\n", host, r[0]);
#endif
			}
		}
	}
	mysql_free_result(res);
	return retval;
}

int parse_request(char *s, char **host, size_t *hostl, char **path) {
	/* parseaza o linie de intrare
	 *
	 * primeste:
	 * s - bufferul care contine linia de intrare
	 * host - pointer la locatia in care se depune adresa de inceput a partii de host
	 * hostl - pointer la locatia in care se depune lungimea partii de host
	 * path - pointer la locatia in care se depune adresa de inceput a caii
	 */

	size_t l;
#ifdef DEBUG
	char dbg_tmp_1, *dbg_tmp_2=s, dbg_tmp_3;
#endif

	/* am ajuns la campul de URL */
	if (!(l=strcspn(s, ":"))) return 1;
	if (strncasecmp(s, "http", l) && strncasecmp(s, "https", l)) return 2;
	s+=l;
	if (strspn(s, ":")!=1) return 3;
	s++;
	if (strspn(s, "/")!=2) return 4;
	s+=2;
	*host=s;
	if (!(l=strcspn(s, "/: "))) return 5;
	s+=l;
	*path=s;
	*hostl=l;
	if (**path==':') (*path)+=strcspn(*path, "/ "); /* elimin si partea de port */
#ifdef DEBUG
	dbg_tmp_1=s[strlen(s)-1];
	dbg_tmp_3=(*host)[*hostl];
	s[strlen(s)-1]='\0';
	(*host)[*hostl]='\0';
	fprintf(stderr, "get_host: said '%s' for ", *host);
	(*host)[*hostl]=dbg_tmp_3;
	fprintf(stderr, "'%s'\n", dbg_tmp_2);
	s[strlen(s)]=dbg_tmp_1;
#endif
	return 0;
}

void malformed_data(char *s) {
	printf("\n");
	fflush(stdout);		/* altfel squid se blocheaza in read */
	fprintf(log_fp, "[%d] %ld 3 %s", pid, time(NULL), s);
#ifdef DEBUG
	fflush(log_fp);
#endif
}

void err_url(char *host, size_t hostl, char save_host, char *path, int error) {
	char enchost[3*MAX_HOST_LENGTH+1];
	char *path_term, *s;
	size_t l;
	int i;
	
	urlencode(enchost, host);
	host[hostl]=save_host;
	printf("%s?reason=%d&host=%s", cfg.err_url, error, enchost);

	/* caut inceputul ip-ului client */
	s=host+hostl;
	s+=strcspn(s, " "); /* sunt pe spatiul dinaintea campului de ip */
	path_term=s;
	for (i=0; i<2; i++) {
		if(!(l=strspn(s, " "))) {
			malformed_data(s);
			return;
		}
		s+=l;
		if(!(l=strcspn(s, " "))) {
			malformed_data(s);
			return;
		}
		s+=l;
	}
	/* sunt pe spatiul dinaintea campului de metoda */
	*s='\0';
	
	printf("%s GET\n", path_term);
	fflush(stdout);		/* altfel squid se blocheaza in read */

	host[hostl]='\0';
	fprintf(log_fp, "[%d] %ld %d %s\n", pid, time(NULL), error, host);
#ifdef DEBUG
	fflush(log_fp);
#endif
}

void process_line(char *s) {
	struct host_info s1, *s2;
	size_t hostl;
	char ip[MAX_IP_LENGTH+1], *host, *path, save_host;
	int r, port;

	if(parse_request(s, &host, &hostl, &path)) {
		malformed_data(s);
		return;
	}

	if(hostl>MAX_HOST_LENGTH) hostl=MAX_HOST_LENGTH;
	save_host=host[hostl];
	host[hostl]='\0';
	lowerstr(host);
	s1.name=host;

	/* in continuare ma asigur ca s2 indica spre o structura cu date
	 * de actualitate, fie ca o iau din cache sau o adaug */
	if((s2=hash_find(hash, &s1))==NULL) {
		/* nu e in cache, deci ar trebui sa il adaug */
		r=sql_host_info(host, ip, &port);
		if(!r) {
			/* daca nu exista, nu il adaug in cache, pt. ca altfel ar putea fi
			 * exploatata memoria prin cereri foarte multe pe diferite nume
			 * care nu exista */
			err_url(host, hostl, save_host, path, ERROR_HOST_NOT_FOUND);
#ifdef DEBUG
			fprintf(stderr, "Authoritative: host '%s' does not exist.\n", host);
#endif
			return;
		}
		/* acum pot sa il adaug */
		CRITICAL((s2=(struct host_info *)malloc(sizeof(struct host_info)))==NULL);
		CRITICAL((s2->name=strdup(host))==NULL);
		CRITICAL((s2->ip=strdup(ip))==NULL);
		s2->port=port;
		time(&(s2->t));
		s2->r=r;
		hash_add(hash, (void *)s2);
#ifdef DEBUG
		fprintf(stderr, "Host '%s' not in cache. Now cached.\n", s1.name);
		fprintf(stderr, "Authoritative: host '%s' is at '%s:%d', status %d.\n", s2->name, s2->ip, s2->port, s2->r);
#endif
	} else {
		/* e in cache; sa vedem daca trebuie sa fac refresh */
		if (time(NULL)-s2->t > cfg.cache_timeout) {
			/* a expirat; fac refresh */
			r=sql_host_info(host, ip, &port);
			free(s2->ip);
			if(r) {
				CRITICAL((s2->ip=strdup(ip))==NULL);
				s2->port=port;
			} else {
				/* se poate intampla: hostul a fost sters din baza de date dupa ce a
				 * apucat sa intre in cache */
				s2->ip=NULL;
				s2->port=0;
			}
			time(&(s2->t));
			s2->r=r;
#ifdef DEBUG
			fprintf(stderr, "Host '%s' aged out. Now refreshed.\n", s1.name);
			fprintf(stderr, "Authoritative: host '%s' is at '%s:%d', status %d.\n", s2->name, s2->ip, s2->port, s2->r);
		} else {
			fprintf(stderr, "Non-authoritative: host '%s' is at '%s:%d', status %d.\n", s2->name, s2->ip, s2->port, s2->r);
#endif
		}
	}

	switch(s2->r) {
	case 0:
		/* se poate intampla: hostul a fost sters din baza de date dupa ce a
		 * apucat sa intre in cache */
		err_url(host, hostl, save_host, path, ERROR_HOST_NOT_FOUND);
		break;
	case 1:
		err_url(host, hostl, save_host, path, ERROR_HOST_DISABLED);
		break;
	default:
		/* fac redirectarea */
		host[hostl]=save_host;
		save_host=*host;
		*host='\0';
		if(s2->port==80) {
			printf("%s%s%s", s, s2->ip, path);
		} else {
			printf("%s%s:%d%s", s, s2->ip, s2->port, path);
		}
		fflush(stdout);		/* altfel squid se blocheaza in read */
		*host=save_host;
		host[hostl]='\0';
		fprintf(log_fp, "[%d] %ld 1 %s %s:%d\n", pid, time(NULL), host, s2->ip, s2->port);
#ifdef DEBUG
		fflush(log_fp);
#endif
	}
}

int main(int argc, char **argv) {
	char buf[MAX_LINE_LENGTH];
	int discard;

	if(parse_config(&cfg)) return 10;

	if(!(log_fp=fopen(cfg.log_path, "a"))) {
		fprintf(stderr, "Could not open log file %s.\n", cfg.log_path);
		sql_close();
		return 2;
	}

	if(sql_connect(&cfg)) {
		fprintf(log_fp, "[%d] %ld 7\n", pid, time(NULL));
		fclose(log_fp);
		return 1;
	}

	pid=getpid();
	fprintf(log_fp, "[%d] %ld 0 %s\n", pid, time(NULL), version);
#ifdef DEBUG
	fflush(log_fp);
#endif

	hash=hash_create(499, hash_algo, hash_cmp);

	while(!feof(stdin)) {
		if(fgets(buf, MAX_LINE_LENGTH, stdin)==NULL) break;
		discard=0;
		while(strlen(buf)==MAX_LINE_LENGTH-1) {
			discard=1;
			if(buf[MAX_LINE_LENGTH-2]=='\n' || feof(stdin)) break;
			fgets(buf, MAX_LINE_LENGTH, stdin);
		}
		if(discard) continue;
		process_line(buf);
	}
	
	fprintf(log_fp, "[%d] %ld 9\n", pid, time(NULL));
	fclose(log_fp);
	
	sql_close();
	return 0;
}
