#define _GNU_SOURCE
#include <assert.h>
#include <libconfig.h>
#include <errno.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <syslog.h>

#include "config.h"
#include "logging.h"

/*
 * Server configuration initializer.
 */
struct config config = {
	.path = "/etc/redirect.conf",
	.logging_type = LOGGING_TYPE_STDERR,
	.logging_level = LOG_INFO,
	.logging_facility = LOG_DAEMON,
	.dbconn = NULL,
	.err_url = "http://192.168.0.1/badurl.php",
	.cache_timeout = 30
};

/*
 * Mapping from string representation to numeric value.
 */
struct str2val_map {
	const char *name;
	const int val;
};

static const struct str2val_map log_levels[] = {
	{ "emerg", LOG_EMERG },
	{ "alert", LOG_ALERT },
	{ "crit", LOG_CRIT },
	{ "err", LOG_ERR },
	{ "warning", LOG_WARNING },
	{ "notice", LOG_NOTICE },
	{ "info", LOG_INFO },
	{ "debug", LOG_DEBUG },
	{ NULL, 0 }
};

static const struct str2val_map log_facilities[] = {
	{ "daemon", LOG_DAEMON },
	{ "user", LOG_USER },
	{ "local0", LOG_LOCAL0 },
	{ "local1", LOG_LOCAL1 },
	{ "local2", LOG_LOCAL2 },
	{ "local3", LOG_LOCAL3 },
	{ "local4", LOG_LOCAL4 },
	{ "local5", LOG_LOCAL5 },
	{ "local6", LOG_LOCAL6 },
	{ "local7", LOG_LOCAL7 },
	{ NULL, 0 }
};

static int str_2_val(const struct str2val_map *map, const char *str)
{
	int i;
	for (i = 0; map[i].name; i++) {
		if (!strcmp(map[i].name, str))
			return map[i].val;
	}
	return -EINVAL;
}

/*
 * Server configuration file parser function.
 */
int parse_config(void)
{
	config_setting_t *node, *child;
	struct config local;
	const char *value;
	char *old, *current;
	int i, err = -EINVAL;
	config_t cf;

	memcpy(&local, &config, sizeof(config));

	config_init(&cf);
	if (config_read_file(&cf, local.path) == CONFIG_FALSE) {
		log(LOG_ERR, "Parse error at line %d: %s\n",
				config_error_line(&cf), config_error_text(&cf));
		goto out_err;
	}

	/*
	 * Logging configuration
	 */
	value = config_lookup_string(&cf, "logging.type");
	if (value) {
		if (!strcmp(value, "file"))
			local.logging_type = LOGGING_TYPE_LOGFILE;
		else if (!strcmp(value, "stderr"))
			local.logging_type = LOGGING_TYPE_STDERR;
		else if (!strcmp(value, "syslog"))
			local.logging_type = LOGGING_TYPE_SYSLOG;
		else {
			log(LOG_ERR, "Invalid logging.type value: '%s'\n", value);
			goto out_err;
		}
	}

	if (local.logging_type == LOGGING_TYPE_LOGFILE) {
		if (!(value = config_lookup_string(&cf, "logging.path"))) {
			log(LOG_ERR, "logging.path not found in config file.\n");
			goto out_err;
		}
		local.logging_path = strdup(value);
	}

	if ((value = config_lookup_string(&cf, "logging.level"))) {
		if ((local.logging_level = str_2_val(log_levels, value)) < 0) {
			log(LOG_ERR, "Invalid logging.level value: '%s'.\n", value);
			goto out_err;
		}
	}

	if ((value = config_lookup_string(&cf, "logging.facility"))) {
		if ((local.logging_facility = str_2_val(log_facilities, value)) < 0) {
			log(LOG_ERR, "Invalid logging.facility value: '%s'\n", value);
			goto out_err;
		}
	}

	/*
	 * Database connection configuration
	 */
	if (!(node = config_lookup(&cf, "dbconn"))) {
		log(LOG_ERR, "dbconn node not found in config file.\n");
		goto out_err;
	}

	current = old = NULL;
	for (i = 0; i < config_setting_length(node); i++) {
		child = config_setting_get_elem(node, i);
		assert(child);
		if (old)
			free(old);
		old = current;
		if (old) {
			asprintf(&current, "%s %s = %s",
					old,
					config_setting_name(child),
					config_setting_get_string(child));
			continue;
		}
		asprintf(&current, "%s = %s",
				config_setting_name(child),
				config_setting_get_string(child));
	}
	if (old)
		free(old);

	local.dbconn = current;

	/*
	 * Redirect specific configuration
	 */
	if (!(value = config_lookup_string(&cf, "redirect.err_url"))) {
		log(LOG_ERR, "redirect.err_url not found in config file.\n");
		goto out_err;
	}
	local.err_url = strdup(value);
	if (!(local.cache_timeout = config_lookup_int(&cf, "redirect.cache_timeout"))) {
		log(LOG_ERR, "redirect.cache_timeout not found in config file.\n");
		goto out_err;
	}

	/* TODO parse the SQL prepared statements */

	memcpy(&config, &local, sizeof(config));
	err = 0;
out_err:
	config_destroy(&cf);
	return err;
}
