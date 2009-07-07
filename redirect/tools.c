#include <ctype.h>
#include "abort.h"

int validate_hostname(char *s) {
	for(;*s!='\0';s++) {
		if (*s >= 'a' && *s <= 'z') continue;
		if (*s >= 'A' && *s <= 'Z') continue;
		if (*s >= '0' && *s <= '9') continue;
		if (*s == '-' || *s == '.') continue;
		return 0;
	}
	return 1;
}

void lowerstr(char *s) {
	for(;*s!='\0';s++) *s=tolower(*s);
}

const char *hex = "0123456789ABCDEF";

void urlencode(char *d, char *s) {
	char *p;

	CRITICAL(s==NULL || d==NULL);
	for(p=d; *s!='\0'; s++) {
		*(p++)='%';
		*(p++)=hex[(*(unsigned char *)s) >> 4];
		*(p++)=hex[(*(unsigned char *)s) & 0xF];
	}
	*p='\0';
}

