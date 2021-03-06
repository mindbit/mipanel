#define _BSD_SOURCE
#define _POSIX_C_SOURCE 2

#include <assert.h>
#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <linux/limits.h>
#include <string.h>
#include <sys/types.h>
#include <unistd.h>
#include <libpq-fe.h>
#include <stdint.h>
#include <regex.h>

#include "list.h"
#include "tools.h"
#include "logging.h"
#include "config.h"

/* Program version */
const char version[] = "v1.1";

/* Some maximum length assumptions */
#define MAX_LINE_LENGTH  16383
#define MAX_HOST_LENGTH  1023
#define MAX_IP_LENGTH    63
#define MAX_PMATCH_SIZE  16

/* Error codes */
#define ERROR_HOST_NOT_FOUND 6
#define ERROR_HOST_DISABLED  11

static int pid;

/* Database connection */
static PGconn *conn;

/* Prepared SQL statements */
enum {
	PSTMT_GET_SITE_BY_ID,
	PSTMT_GET_SITE_BY_NAME,
	PSTMT_GET_SITE_ID_BY_ALIAS,
	PSTMT_GET_SITE_REWRITES
};

static const char *prepared_statements[] = {
	[PSTMT_GET_SITE_BY_ID] =
		"SELECT server_ip, server_port, enabled FROM sites WHERE site_id=$1::integer",
	[PSTMT_GET_SITE_BY_NAME] =
		"SELECT site_id, server_ip, server_port, enabled FROM sites WHERE name=$1 LIMIT 1",
	[PSTMT_GET_SITE_ID_BY_ALIAS] =
		"SELECT site_id FROM site_aliases WHERE name=$1 LIMIT 1",
	[PSTMT_GET_SITE_REWRITES] =
		"SELECT pattern, replacement, prio, continue FROM site_rewrites WHERE site_id=$1::integer ORDER BY prio",
};

static uint32_t prepared_mask = 0;

#define _PQexecPrepared(id, nParams, paramValues, paramLengths, paramFormats, resultFormat) \
	__PQexecPrepared(#id, id, nParams, paramValues, paramLengths, paramFormats, resultFormat)

static PGresult *__PQexecPrepared(const char *stmt, int stmt_id,
		int nParams, const char * const *paramValues, const int *paramLengths,
		const int *paramFormats, int resultFormat)
{
	PGresult *res;
	int mask = 1 << stmt_id;

	if (!(prepared_mask & mask)) {
		res = PQprepare(conn, stmt, prepared_statements[stmt_id], 0, NULL);
		log(LOG_DEBUG, "Preparing statement '%s': '%s'\n", stmt, prepared_statements[stmt_id]);
		if (res == NULL) {
			log(LOG_ERR, "[%d] %ld PQprepare(%s) failed: %s\n",
					pid, time(NULL), stmt, PQerrorMessage(conn));
			return NULL;
		}
		if (PQresultStatus(res) != PGRES_COMMAND_OK) {
			log(LOG_ERR, "[%d] %ld PQprepare(%s) failed(%d): %s\n",
					pid, time(NULL), stmt, PQresultStatus(res), PQerrorMessage(conn));
			PQclear(res);
			return NULL;
		}
		PQclear(res);
		prepared_mask |= mask;
	}
	res = PQexecPrepared(conn, stmt, nParams, paramValues, paramLengths, paramFormats, resultFormat);
	if (res == NULL) {
		log(LOG_ERR, "[%d] %ld PQexecPrepared(%s) failed: %s\n", pid, time(NULL),
				prepared_statements[stmt_id], PQerrorMessage(conn));
		return NULL;
	}
	if (PQresultStatus(res) != PGRES_COMMAND_OK && PQresultStatus(res) != PGRES_TUPLES_OK) {
		log(LOG_ERR, "[%d] %ld PQexecPrepared(%s) failed(%d): %s\n", pid, time(NULL),
				prepared_statements[stmt_id], PQresultStatus(res), PQerrorMessage(conn));
		PQclear(res);
		return NULL;
	}
	return res;
}

struct site_rewrite {
	regex_t preg;
	uint32_t id;
	char *pattern;
	char *replacement;
	uint8_t cont;
	uint8_t prio;
	struct list_head lh;
};

struct host_info {
	char *name;
	char *ip;
	int port;
	time_t t;
	int r;
	struct list_head rewrites;
	struct list_head lh;
};

#define HOST_INFO_HASH_BITS 8
#define HOST_INFO_HASH_SIZE (1 << HOST_INFO_HASH_BITS)

/* Host info cache (hash table) */
struct list_head host_cache[HOST_INFO_HASH_SIZE];

static int hash_fn(char *p)
{
	int s = 0;

	while (*p != '\0')
		s += *(p++);
	return s % HOST_INFO_HASH_SIZE;
}

static struct host_info *host_cache_find(struct host_info *host)
{
	int bucket = hash_fn(host->name);
	struct host_info *entry;

	list_for_each_entry(entry, &host_cache[bucket], lh)
		if (!strcmp(host->name, entry->name))
			return entry;
	return NULL;
}

static int sql_reload_rewrites(struct host_info *host, char *site_id)
{
	PGresult *res;
	struct list_head old_lh;
	struct site_rewrite *entry, *tmp;
	char *pattern, *replacement;
	int i, ret = 0, prio, cont;

	res = _PQexecPrepared(PSTMT_GET_SITE_REWRITES, 1,
			(const char * const[]){site_id},
			(const int[]){strlen(site_id)},
			(const int[]){0},
			0);
	log(LOG_DEBUG, "PSTMT_GET_SITE_REWRITES(%d) returned %d tuples, %d fields\n",
			site_id, PQntuples(res), PQnfields(res));

	INIT_LIST_HEAD(&old_lh);
	list_for_each_entry_safe(entry, tmp, &host->rewrites, lh) {
		list_del(&entry->lh);
		list_add_tail(&entry->lh, &old_lh);
	}

	for (i = 0; i < PQntuples(res); i++) {
		pattern = PQgetvalue(res, i, 0);
		replacement = PQgetvalue(res, i, 1);
		sscanf(PQgetvalue(res, i, 2), "%d", &prio);
		sscanf(PQgetvalue(res, i, 3), "%d", &cont);

		/* try to reuse already compiled patterns from the old list */
		entry = NULL;
		list_for_each_entry(tmp, &old_lh, lh) {
			if (!strcmp(tmp->pattern, pattern)) {
				entry = tmp;
				break;
			}
		}

		if (entry) {
			list_del(&entry->lh);
			free(entry->replacement);
			log(LOG_DEBUG, "Reusing pattern '%s' found in old list\n", entry->pattern);
		}
		else {
			entry = (struct site_rewrite *)malloc(sizeof(struct site_rewrite));
			assert(entry);
			entry->pattern = strdup(pattern);
			/* FIXME: should we use REG_ICASE? */
			ret = regcomp(&entry->preg, entry->pattern, 0);
			assert(ret == 0);
			log(LOG_DEBUG, "Adding new pattern '%s'\n", entry->pattern);
		}
		entry->replacement = strdup(replacement);
		assert(entry->replacement);
		entry->prio = prio;
		entry->cont = cont;
		list_add_tail(&entry->lh, &host->rewrites);
	}
	PQclear(res);

	/* cleanup remaining entries in the old list */
	list_for_each_entry_safe(entry, tmp, &old_lh, lh) {
		list_del(&entry->lh);
		regfree(&entry->preg);
		free(entry->pattern);
		free(entry->replacement);
		free(entry);
	}

	return 0;
}

/*
 * Url rewrite state machine.
 */
enum {
	COPY_INPUT,
	BACKREF_FOUND,
	PARSE_NUMBER
};

static int url_rewrite(struct host_info *host, char *url, char **new_url)
{
	regmatch_t pmatch[MAX_PMATCH_SIZE];
	struct site_rewrite *entry;
	int i, j, k, state, new_size, err = 1;
	char *tmp, *buf, *__url, save;

	list_for_each_entry(entry, &host->rewrites, lh) {
		tmp = index(url, ' ');
		save = *tmp;
		*tmp = '\0';

		log(LOG_DEBUG, "url_rewrite('%s'), pattern='%s', replacement='%s'\n",
				url, entry->pattern, entry->replacement);

		if (regexec(&entry->preg, url, MAX_PMATCH_SIZE, pmatch, 0)) {
			log(LOG_DEBUG, "Pattern didn't match, trying next pattern ...\n");
			*tmp = save;
			continue;
		}

		log(LOG_DEBUG, "Pattern matched, entering state COPY_INPUT\n");
		new_size = strlen(url) - pmatch[0].rm_eo + pmatch[0].rm_so +
			strlen(entry->replacement) + 1;
		__url = (char *)malloc(new_size);
		assert(__url);
		memcpy(__url, url, pmatch[0].rm_so);
		j = pmatch[0].rm_so;
		state = COPY_INPUT;

		for (i = 0; i < strlen(entry->replacement); i++) {
			switch (state) {
			case COPY_INPUT:
				if (entry->replacement[i] == '$') {
					state = BACKREF_FOUND;
					buf = &entry->replacement[i];
					log(LOG_DEBUG, "Found '$' at %d. Switching to state BACKREF_FOUND\n", i);
					break;
				}
				__url[j++] = entry->replacement[i];
				break;
			case BACKREF_FOUND:
				if (entry->replacement[i] != '{') {
					memcpy(&__url[j], buf, &entry->replacement[i] - buf);
					j += &entry->replacement[i] - buf;
					state = COPY_INPUT;
					break;
				}
				log(LOG_DEBUG, "Found '{' at %d. Switching to state PARSE_NUMBER\n", i);
				state = PARSE_NUMBER;
				break;
			case PARSE_NUMBER:
				if (entry->replacement[i] == '}') {
					buf += 2;
					entry->replacement[i] = '\0';
					k = atoi(buf);
					log(LOG_DEBUG, "Found '}' at %d. Parsed number is %d. Going back to COPY_INPUT\n", i, k);
					assert(k < MAX_PMATCH_SIZE);
					assert(pmatch[k].rm_so != -1);
					if (strlen(buf) + 3 < pmatch[k].rm_eo - pmatch[k].rm_so) {
						new_size += pmatch[k].rm_eo - pmatch[k].rm_so - strlen(buf) - 3;
						__url = realloc(__url, new_size);
						assert(__url);
					}
					entry->replacement[i] = '}';
					memcpy(&__url[j], url + pmatch[k].rm_so,
							pmatch[k].rm_eo - pmatch[k].rm_so);
					j += pmatch[k].rm_eo - pmatch[k].rm_so;
					state = COPY_INPUT;
					break;
				}
				if (entry->replacement[i] < '0' || entry->replacement[i] > '9') {
					memcpy(&__url[j], buf, &entry->replacement[i] - buf);
					j += &entry->replacement[i] - buf;
					log(LOG_DEBUG, "Invalid char '%c' found at %d. Switching back to COPY_INPUT\n",
							entry->replacement[i], i);
					state = COPY_INPUT;
				}
				break;
			default:
				err = 1;
				break;
			}
		}
		__url[j] = '\0';
		log(LOG_DEBUG, "Rewritten url '%s' to '%s'\n", url, __url);
		*tmp = save;
		__url = realloc(__url, new_size + strlen(tmp));
		memcpy(&__url[j], url + pmatch[0].rm_eo, strlen(url) - pmatch[0].rm_eo);
		j += strlen(url) - pmatch[0].rm_eo;
		__url[j++] = '\0';
		*new_url = __url;
		err = 0;
		if (!entry->cont)
			break;
		url = __url;
	}


	return err;
}

static int sql_host_info(char *host, char *ip, int *port, char **site_id)
{
	/* intoarce:
	 * 0 - hostul nu exista
	 * 1 - hostul exista si e dezactivat
	 * 2 - hostul exista si e activat
	 */
	PGresult *res;
	int retval = 0;
	int enabled;

	if (!validate_hostname(host)) {
		log(LOG_DEBUG, "Invalid hostname '%s'\n", host);
		return 0;
	}

	res = _PQexecPrepared(PSTMT_GET_SITE_BY_NAME, 1,
			(const char * const[]){host},
			(const int[]){strlen(host)},
			(const int[]){0},
			0);
	assert(res);
	assert(PQresultStatus(res) == PGRES_TUPLES_OK);
	log(LOG_DEBUG, "PSTMT_GET_SITE_BY_NAME(%s) returned %d tuples, %d fields\n",
			host, PQntuples(res), PQnfields(res));
	if (PQntuples(res)) {
		/* e nume primar */
		log(LOG_DEBUG, "Host '%s' is primary,  ip='%s', port='%s', enabled='%s'\n",
				host, PQgetvalue(res, 0, 1), PQgetvalue(res, 0, 2), PQgetvalue(res, 0, 3));
		*site_id = strdup(PQgetvalue(res, 0, 0));
		assert(*site_id);
		strncpy(ip, PQgetvalue(res, 0, 1), MAX_IP_LENGTH);
		ip[MAX_IP_LENGTH] = '\0';
		sscanf(PQgetvalue(res, 0, 2), "%d", port);
		sscanf(PQgetvalue(res, 0, 3), "%d", &enabled);
		retval = enabled? 2: 1;
	} else {
		/* nu e nume primar, deci e alias sau nu exista deloc */
		PQclear(res);
		res = _PQexecPrepared(PSTMT_GET_SITE_ID_BY_ALIAS, 1,
				(const char * const[]){host},
				(const int[]){strlen(host)},
				(const int[]){0},
				0);
		assert(res);
		assert(PQresultStatus(res) == PGRES_TUPLES_OK);
		log(LOG_DEBUG, "PSTMT_GET_SITE_ID_BY_ALIAS(%s) returned %d tuples\n",
				host, PQntuples(res));
		if (PQntuples(res)) {
			/* e un alias si caut info despre host */
			*site_id = strdup(PQgetvalue(res, 0, 0));
			assert(*site_id);
			PQclear(res);
			res = _PQexecPrepared(PSTMT_GET_SITE_BY_ID, 1,
					(const char * const[]){*site_id},
					(const int[]){strlen(*site_id)},
					(const int[]){0},
					0);
			assert(res);
			assert(PQresultStatus(res) == PGRES_TUPLES_OK);
			log(LOG_DEBUG, "PSTMT_GET_SITE_BY_ID(%s) returned %d tuples\n",
					host, PQntuples(res));
			if (PQntuples(res)) {
				/* am gasit hostul */
				strncpy(ip, PQgetvalue(res, 0, 0), MAX_IP_LENGTH);
				ip[MAX_IP_LENGTH] = '\0';
				sscanf(PQgetvalue(res, 0, 1), "%d", port);
				sscanf(PQgetvalue(res, 0, 2), "%d", &enabled);
				retval = enabled? 2: 1;
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

static int parse_request(char *s, char **host, size_t *hostl, char **path)
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

static void malformed_data(char *s)
{
	printf("\n");
	fflush(stdout);		/* altfel squid se blocheaza in read */
	log(LOG_INFO, "[%d] %ld 3 %s", pid, time(NULL), s);
}

static void err_url(char *host, size_t hostl, char save_host, char *path, int error)
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

static void process_line(char *s)
{
	struct host_info s1, *s2;
	size_t hostl;
	char ip[MAX_IP_LENGTH+1], *host, *path, *new_url = NULL, save_host;
	int r, port;
	char *site_id = NULL;

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
	if ((s2 = host_cache_find(&s1)) == NULL) {
		/* nu e in cache, deci ar trebui sa il adaug */
		r = sql_host_info(host, ip, &port, &site_id);
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
		INIT_LIST_HEAD(&s2->rewrites);
		sql_reload_rewrites(s2, site_id);
		free(site_id);
		list_add_tail(&s2->lh, &host_cache[hash_fn(s2->name)]);
		log(LOG_DEBUG, "Host '%s' not in cache. Now cached.\n", s1.name);
		log(LOG_DEBUG, "Authoritative: host '%s' is at '%s:%d', status %d.\n", s2->name, s2->ip, s2->port, s2->r);
	} else {
		/* e in cache; sa vedem daca trebuie sa fac refresh */
		if (time(NULL)-s2->t > config.cache_timeout) {
			/* a expirat; fac refresh */
			r = sql_host_info(host, ip, &port, &site_id);
			free(s2->ip);
			if (r) {
				s2->ip = strdup(ip);
				assert(s2->ip);
				s2->port = port;
				sql_reload_rewrites(s2, site_id);
				free(site_id);
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
		/* if we've rewritten the url, redirect to the rewritten url,
		 * otherwise redirect using the host info from the cache */
		if (!url_rewrite(s2, host, &new_url)) {
			save_host = *host;
			*host = '\0';
			printf("%s%s", s, new_url);
			fflush(stdout);
			*host = save_host;
			free(new_url);
		} else {
			save_host = *host;
			*host = '\0';
			if (s2->port == 80) {
				printf("%s%s%s", s, s2->ip, path);
			} else {
				printf("%s%s:%d%s", s, s2->ip, s2->port, path);
			}
			fflush(stdout);
			*host = save_host;
			host[hostl] = '\0';
			log(LOG_INFO, "[%d] %ld 1 %s %s:%d\n", pid, time(NULL), host, s2->ip, s2->port);
		}
	}
}

static int open_log(void)
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

static void close_log(void)
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
	int opt, discard, i;

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

	pid = getpid();
	log(LOG_INFO, "[%d] %ld 0 %s\n", pid, time(NULL), version);

	/* Connect to the database */
	conn = PQconnectdb(config.dbconn);
	assert(conn);
	if (PQstatus(conn) != CONNECTION_OK) {
		log(LOG_ERR, "[%d] %ld Failed to connect to the database. Reason: %s\n",
				pid, time(NULL), PQerrorMessage(conn));
		PQfinish(conn);
		close_log();
		return 1;
	}

	/* Initialize the host cache hash table */
	for (i = 0; i < HOST_INFO_HASH_SIZE; i++)
		INIT_LIST_HEAD(&host_cache[i]);

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
	PQfinish(conn);

	return 0;
}
