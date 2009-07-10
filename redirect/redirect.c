#include <assert.h>
#include <stdio.h>
#include <stdlib.h>
#include <linux/limits.h>
#include <string.h>
#include <time.h>
#include <sys/types.h>
#include <unistd.h>
#include <libpq-fe.h>
#include "hash.h"
#include "tools.h"
#include "logging.h"
#include "config.h"

/* Some maximum length assumptions */
#define MAX_LINE_LENGTH  16383
#define MAX_HOST_LENGTH  1023
#define MAX_QUERY_LENGTH 16383
#define MAX_IP_LENGTH    63

/* Error codes */
#define ERROR_HOST_NOT_FOUND 6
#define ERROR_HOST_DISABLED  11

const char version[] = "v1.1";

int pid;
PGconn *conn;
struct hash *hash;

struct host_info {
	char *name;						/* numele hostului */
	char *ip;						/* ip-ul */
	int port;
	time_t t;						/* timestamp-ul ultimei actualizari */
	int r;							/* raspunsul de redirectare */
};

int hash_fn(void *e, int n)
{
	int s = 0;
	char *p;

	p = ((struct host_info *)e)->name;
	while (*p != '\0')
		s += *(p++);
	return s % n;
}

int hash_compare(void *e1, void *e2)
{
	return strcmp(((struct host_info *)e1)->name, ((struct host_info *)e2)->name);
}

int sql_connect(struct config *cfg)
{
	conn = PQconnectdb(cfg->dbconn);
	assert(conn);
	if (PQstatus(conn) != CONNECTION_OK) {
		log(LOG_ERR, "Failed to connect to the database. Reason: %s\n", PQerrorMessage(conn));
		PQfinish(conn);
		return 1;
	}
	return 0;
}

void sql_close(void)
{
	PQfinish(conn);
}

/* FIXME: these should be parsed from the config */
const char query1[] = "SELECT server_ip, server_port, enabled FROM sites WHERE name='%s' LIMIT 1";
const char query2[] = "SELECT site_id FROM site_aliases WHERE name='%s' LIMIT 1";
const char query3[] = "SELECT server_ip, server_port, enabled FROM sites WHERE site_id=%s";

int sql_host_info(char *host, char *ip, int *port)
{
	/* intoarce:
	 * 0 - hostul nu exista
	 * 1 - hostul exista si e dezactivat
	 * 2 - hostul exista si e activat
	 */
	PGresult *res;
	char query[MAX_QUERY_LENGTH+1];
	int retval = 0;
	int tmp;

	if (!validate_hostname(host)) {
		log(LOG_DEBUG, "Invalid hostname '%s'\n", host);
		return 0;
	}

	sprintf(query, query1, host);
	res = PQexec(conn, query);
	assert(res);
	assert(PQresultStatus(res) == PGRES_TUPLES_OK);
	if (PQntuples(res)) {
		/* e nume primar */
		log(LOG_DEBUG, "Host '%s' is primary\n", host);
		strncpy(ip, PQgetvalue(res, 0, 0), MAX_IP_LENGTH);
		ip[MAX_IP_LENGTH] = '\0';
		sscanf(PQgetvalue(res, 0, 1), "%d", port);
		sscanf(PQgetvalue(res, 0, 2), "%d", &tmp);
		retval = tmp? 2: 1;
	} else {
		/* nu e nume primar, deci e alias sau nu exista deloc */
		sprintf(query, query2, host);
		PQclear(res);
		res = PQexec(conn, query);
		assert(res);
		assert(PQresultStatus(res) == PGRES_TUPLES_OK);
		if (PQntuples(res)) {
			/* e un alias si caut info despre host */
			sprintf(query, query3, PQgetvalue(res, 0, 0));
			PQclear(res);
			res = PQexec(conn, query);
			assert(res);
			assert(PQresultStatus(res) == PGRES_TUPLES_OK);
			if (PQntuples(res)) {
				/* am gasit hostul */
				strncpy(ip, PQgetvalue(res, 0, 0), MAX_IP_LENGTH);
				ip[MAX_IP_LENGTH] = '\0';
				sscanf(PQgetvalue(res, 0, 1), "%d", port);
				sscanf(PQgetvalue(res, 0, 2), "%d", &tmp);
				retval = tmp? 2: 1;
				log(LOG_DEBUG, "Host '%s' is an alias for '%s'\n", host, PQgetvalue(res, 0, 0));
			} else {
				/* nu am gasit hostul, deci e un alias broken */
				log(LOG_DEBUG, "Broken alias for host '%s', idx '%s'\n", host, PQgetvalue(res, 0, 0));
			}
		}
	}
	PQclear(res);
	return retval;
}

int parse_request(char *s, char **host, size_t *hostl, char **path)
{
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
	char dbg_tmp_1, *dbg_tmp_2 = s, dbg_tmp_3;
#endif

	/* am ajuns la campul de URL */
	if (!(l = strcspn(s, ":")))
		return 1;
	if (strncasecmp(s, "http", l) && strncasecmp(s, "https", l))
		return 2;
	s += l;
	if (strspn(s, ":") != 1)
		return 3;
	s++;
	if (strspn(s, "/") != 2)
		return 4;
	s += 2;
	*host = s;
	if (!(l = strcspn(s, "/: ")))
		return 5;
	s += l;
	*path = s;
	*hostl = l;
	if (**path == ':')
		(*path) += strcspn(*path, "/ "); /* elimin si partea de port */
#ifdef DEBUG
	dbg_tmp_1 = s[strlen(s)-1];
	dbg_tmp_3 = (*host)[*hostl];
	s[strlen(s)-1] = '\0';
	(*host)[*hostl] = '\0';
	log(LOG_DEBUG, "get_host: said '%s' for ", *host);
	(*host)[*hostl] = dbg_tmp_3;
	log(LOG_DEBUG, "'%s'\n", dbg_tmp_2);
	s[strlen(s)] = dbg_tmp_1;
#endif
	return 0;
}

void malformed_data(char *s)
{
	printf("\n");
	fflush(stdout);		/* altfel squid se blocheaza in read */
	log(LOG_INFO, "[%d] %ld 3 %s", pid, time(NULL), s);
}

void err_url(char *host, size_t hostl, char save_host, char *path, int error)
{
	char enchost[3*MAX_HOST_LENGTH+1];
	char *path_term, *s;
	size_t l;
	int i;
	
	urlencode(enchost, host);
	host[hostl] = save_host;
	printf("%s?reason=%d&host=%s", config.err_url, error, enchost);

	/* caut inceputul ip-ului client */
	s = host + hostl;
	s += strcspn(s, " "); /* sunt pe spatiul dinaintea campului de ip */
	path_term = s;
	for (i = 0; i < 2; i++) {
		if (!(l = strspn(s, " "))) {
			malformed_data(s);
			return;
		}
		s += l;
		if (!(l = strcspn(s, " "))) {
			malformed_data(s);
			return;
		}
		s += l;
	}
	/* sunt pe spatiul dinaintea campului de metoda */
	*s = '\0';
	
	printf("%s GET\n", path_term);
	fflush(stdout);		/* altfel squid se blocheaza in read */

	host[hostl] = '\0';
	log(LOG_INFO, "[%d] %ld %d %s\n", pid, time(NULL), error, host);
}

void process_line(char *s)
{
	struct host_info s1, *s2;
	size_t hostl;
	char ip[MAX_IP_LENGTH+1], *host, *path, save_host;
	int r, port;

	if (parse_request(s, &host, &hostl, &path)) {
		malformed_data(s);
		return;
	}

	if (hostl > MAX_HOST_LENGTH)
		hostl = MAX_HOST_LENGTH;
	save_host = host[hostl];
	host[hostl] = '\0';
	lowerstr(host);
	s1.name = host;

	/* in continuare ma asigur ca s2 indica spre o structura cu date
	 * de actualitate, fie ca o iau din cache sau o adaug */
	if ((s2 = hash_find(hash, &s1)) == NULL) {
		/* nu e in cache, deci ar trebui sa il adaug */
		r = sql_host_info(host, ip, &port);
		if (!r) {
			/* daca nu exista, nu il adaug in cache, pt. ca altfel ar putea fi
			 * exploatata memoria prin cereri foarte multe pe diferite nume
			 * care nu exista */
			err_url(host, hostl, save_host, path, ERROR_HOST_NOT_FOUND);
			log(LOG_DEBUG, "Authoritative: host '%s' does not exist.\n", host);
			return;
		}
		/* acum pot sa il adaug */
		s2 = (struct host_info *)malloc(sizeof(struct host_info));
		assert(s2);
		s2->name = strdup(host);
		assert(s2->name);
		s2->ip = strdup(ip);
		assert(s2->ip);
		s2->port = port;
		time(&(s2->t));
		s2->r = r;
		hash_add(hash, (void *)s2);
		log(LOG_DEBUG, "Host '%s' not in cache. Now cached.\n", s1.name);
		log(LOG_DEBUG, "Authoritative: host '%s' is at '%s:%d', status %d.\n", s2->name, s2->ip, s2->port, s2->r);
	} else {
		/* e in cache; sa vedem daca trebuie sa fac refresh */
		if (time(NULL)-s2->t > config.cache_timeout) {
			/* a expirat; fac refresh */
			r = sql_host_info(host, ip, &port);
			free(s2->ip);
			if (r) {
				s2->ip = strdup(ip);
				assert(s2->ip);
				s2->port = port;
			} else {
				/* se poate intampla: hostul a fost sters din baza de date dupa ce a
				 * apucat sa intre in cache */
				s2->ip = NULL;
				s2->port = 0;
			}
			time(&(s2->t));
			s2->r = r;
			log(LOG_DEBUG, "Host '%s' aged out. Now refreshed.\n", s1.name);
			log(LOG_DEBUG, "Authoritative: host '%s' is at '%s:%d', status %d.\n", s2->name, s2->ip, s2->port, s2->r);
		} else {
			log(LOG_DEBUG, "Non-authoritative: host '%s' is at '%s:%d', status %d.\n", s2->name, s2->ip, s2->port, s2->r);
		}
	}

	switch (s2->r) {
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
		host[hostl] = save_host;
		save_host = *host;
		*host = '\0';
		if (s2->port == 80) {
			printf("%s%s%s", s, s2->ip, path);
		} else {
			printf("%s%s:%d%s", s, s2->ip, s2->port, path);
		}
		fflush(stdout);		/* altfel squid se blocheaza in read */
		*host = save_host;
		host[hostl] = '\0';
		log(LOG_INFO, "[%d] %ld 1 %s %s:%d\n", pid, time(NULL), host, s2->ip, s2->port);
	}
}

int open_log(void)
{
	switch (config.logging_type) {
	case LOGGING_TYPE_SYSLOG:
		openlog("redirect", LOG_PID, config.logging_facility);
		break;
	case LOGGING_TYPE_LOGFILE:
		if ((config.log = fopen(config.logging_path, "a")) == NULL)
			return -1;
		break;
	default:
		break;
	}

	return 0;
}

void close_log(void)
{
	switch (config.logging_type) {
	case LOGGING_TYPE_SYSLOG:
		closelog();
		return;
	case LOGGING_TYPE_LOGFILE:
		if (config.log != NULL)
			fclose(config.log);
		config.log = NULL;
		return;
	default:
		break;
	}
}

static void show_help(const char *argv0)
{
	fprintf(stderr,
			"Usage: %s <options>\n"
			"\n"
			"Valid options:\n"
			"  -c <path>       Read configuration file from <path>\n"
			"  -h              Show this help\n"
			"\n",
			argv0);
}

int main(int argc, char *argv[])
{
	char buf[MAX_LINE_LENGTH];
	int opt, discard;

	while ((opt = getopt(argc, argv, "hc:")) != -1) {
		switch (opt) {
		case 'c':
			config.path = strdup(optarg);
			break;
		case 'h':
			show_help(argv[0]);
			return 1;
		}
	}

	if (parse_config())
		return 10;

	if (open_log())
		return 2;

	if (sql_connect(&config)) {
		log(LOG_ERR, "[%d] %ld 7\n", pid, time(NULL));
		close_log();
		return 1;
	}

	pid = getpid();
	log(LOG_INFO, "[%d] %ld 0 %s\n", pid, time(NULL), version);

	hash = hash_create(499, hash_fn, hash_compare);

	while (!feof(stdin)) {
		if (fgets(buf, MAX_LINE_LENGTH, stdin) == NULL)
			break;
		discard = 0;
		while (strlen(buf) == MAX_LINE_LENGTH-1) {
			discard = 1;
			if (buf[MAX_LINE_LENGTH-2] == '\n' || feof(stdin))
				break;
			fgets(buf, MAX_LINE_LENGTH, stdin);
		}
		if (discard)
			continue;
		process_line(buf);
	}
	
	log(LOG_INFO, "[%d] %ld 9\n", pid, time(NULL));
	close_log();
	sql_close();

	return 0;
}
