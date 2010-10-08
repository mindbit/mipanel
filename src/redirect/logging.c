#define _BSD_SOURCE
#include <stdarg.h>
#include <syslog.h>

#include "logging.h"
#include "config.h"

void __log(int level, const char *format, ...)
{
	va_list ap;

	if (level > config.logging_level) {
		return;
	}

	va_start(ap, format);
	switch (config.logging_type) {
	case LOGGING_TYPE_STDERR:
		vfprintf(stderr, format, ap);
		break;
	case LOGGING_TYPE_SYSLOG:
		vsyslog(config.logging_facility | level, format, ap);
		break;
	case LOGGING_TYPE_LOGFILE:
		vfprintf(config.log, format, ap);
#ifdef DEBUG
		fflush(config.log);
#endif
		break;
	}
	va_end(ap);
}
