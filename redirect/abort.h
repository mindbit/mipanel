#ifndef __ABORT_H
#define __ABORT_H

#include <stdlib.h>
#include <stdio.h>

#define CRITICAL(cond) \
if(cond) { \
	fprintf(stderr, "CRITICAL: file %s, line %d. ", __FILE__, __LINE__); \
	abort(); \
}

#endif
