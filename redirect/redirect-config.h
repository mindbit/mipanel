#ifndef __CONFIG_H
#define __CONFIG_H

struct config {
	char *sql_host;
	char *sql_user;
	char *sql_pw;
	char *sql_db;
	char *sql_sock;

	char *log_path;
	char *err_url;
	int cache_timeout;
};

extern int parse_config(struct config *cfg);

#endif
