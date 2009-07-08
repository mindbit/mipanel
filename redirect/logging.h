#ifndef _LOGGING_H
#define _LOGGING_H

#include <syslog.h>

#ifdef DEBUG
#define log(level, text, par...) __log(LOG_DEBUG, text, ##par)
#else
#define log(level, text, par...) do {\
	if (level < LOG_DEBUG) \
		__log(level, text, ##par); \
} while (0)
#endif

extern void __log(int level, const char *format, ...);

#endif
