#ifndef __HASH_H
#define __HASH_H

#define HASH_BUCKET_INC 32

struct hash_bucket {
	int dim;						/* current bucket dimension */
	int i;							/* element counter */
	void **elem;					/* element vector */
};

struct hash {
	int n;							/* number of buckets */
	int (*algo)(void *, int);		/* hashing algorhythm */
	int (*cmp)(void *, void *);		/* function that compares elements */
	struct hash_bucket **buckets;	/* bucket vector */
};

extern struct hash *hash_create(int n, int(*algo)(void *, int),
		int(*cmp)(void *, void *));
extern void hash_add(struct hash *h, void *e);
extern void *hash_find(struct hash *h, void *e);
extern void hash_walk(struct hash *h, void (*callback)(void *, void *), void *arg);

#endif
