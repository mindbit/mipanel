#include <assert.h>
#include <stdlib.h>
#include <stdio.h>
#include "hash.h"
#include "logging.h"

struct hash *hash_create(int n, int(*hash_fn)(void *, int),
		int(*compare)(void *, void *))
{
	struct hash *h;
	int i;

	h = malloc(sizeof(struct hash));
	assert(h);
	h->n = n;
	h->hash_fn = hash_fn;
	h->compare = compare;
	h->buckets = malloc(n*sizeof(struct hash_bucket *));
	assert(h->buckets);
	for (i = 0; i < n; i++)
		h->buckets[i]=NULL;
	return h;
}

void hash_add(struct hash *h, void *e)
{
	int bi = h->hash_fn(e, h->n);
	struct hash_bucket *b;

	if (h->buckets[bi] == NULL) {
		b = h->buckets[bi] = malloc(sizeof(struct hash_bucket));
		assert(b);
		b->dim = HASH_BUCKET_INC;
		b->i = 0;
		b->elem = malloc(HASH_BUCKET_INC * sizeof(void *));
	}
	b = h->buckets[bi];
	log(LOG_DEBUG, "hash_add: bucket #%d at %p\n", bi, b);
	if (b->i == b->dim) {
		b->dim += HASH_BUCKET_INC;
		b->elem = realloc(b->elem, b->dim);
	}
	b->elem[b->i++] = e;
}

void *hash_find(struct hash *h, void *e)
{
	int bi = h->hash_fn(e, h->n), i;
	void **p;

	if (h->buckets[bi] == NULL)
		return NULL;
	for (i = h->buckets[bi]->i, p = h->buckets[bi]->elem; i > 0; i--,p++)
		if (!h->compare(*p, e))
			return *p;
	return NULL;
}

void hash_walk(struct hash *h, void (*callback)(void *, void *), void *arg)
{
	int i, j;
	struct hash_bucket **b;
	void **e;

	for (i = h->n-1, b = h->buckets; i >= 0 ; i--, b++) {
		if (*b == NULL)
			continue;
		log(LOG_DEBUG, "hash_walk: bucket #%d at %p\n", b-h->buckets, *b);
		for (j = (*b)->i-1, e = (*b)->elem; j >= 0; j--, e++)
			callback(*e, arg);
	}
}
