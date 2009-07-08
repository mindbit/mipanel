#ifndef _CONFIG_H
#define _CONFIG_H

#include <stdio.h>

struct config {
	/* Global configuration parameters (not included in config file) */
	const char *path;

	/* Log file */
	FILE *log;

	/* Configuration file parameters */
	enum {
		LOGGING_TYPE_STDERR,
		LOGGING_TYPE_SYSLOG,
		LOGGING_TYPE_LOGFILE
	} logging_type;
	int logging_level;
	int logging_facility;
	const char *logging_path;

	/* database connection info */
	const char *dbconn;
	const char **query;

	/* redirector specific parameters */
	int cache_timeout;
	const char *err_url;
};

/* Parse the configuration file and fill the config structure */
int parse_config(void);

extern struct config config;

#endif
