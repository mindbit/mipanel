#include <stdlib.h>
#include <stdio.h>
#include "hash.h"

struct hash *hash_create(int n, int(*algo)(void *, int),
		int(*cmp)(void *, void *)) {
	struct hash *h;
	int i;

	h=malloc(sizeof(struct hash));
	h->n=n;
	h->algo=algo;
	h->cmp=cmp;
	h->buckets=malloc(n*sizeof(struct hash_bucket *));
	for(i=0;i<n;i++) h->buckets[i]=NULL;
	return h;
}

void hash_add(struct hash *h, void *e) {
	int bi=h->algo(e, h->n);
	struct hash_bucket *b;

	if(h->buckets[bi]==NULL) {
		b=h->buckets[bi]=malloc(sizeof(struct hash_bucket));
		b->dim=HASH_BUCKET_INC;
		b->i=0;
		b->elem=malloc(HASH_BUCKET_INC*sizeof(void *));
	}
	b=h->buckets[bi];
#ifdef DEBUG
	printf("hash_add: bucket #%d at %p\n", bi, b);
#endif
	if(b->i==b->dim) {
		b->dim+=HASH_BUCKET_INC;
		b->elem=realloc(b->elem, b->dim);
	}
	b->elem[b->i++]=e;
}

void *hash_find(struct hash *h, void *e) {
	int bi=h->algo(e, h->n), i;
	void **p;

	if(h->buckets[bi]==NULL) return NULL;
	for(i=h->buckets[bi]->i, p=h->buckets[bi]->elem;i>0;i--,p++)
		if(!h->cmp(*p, e)) return *p;
	return NULL;
}

void hash_walk(struct hash *h, void (*callback)(void *, void *), void *arg) {
	int i, j;
	struct hash_bucket **b;
	void **e;
	
	for (i=h->n-1, b=h->buckets; i>=0 ; i--,b++) {
		if(*b==NULL) continue;
#ifdef DEBUG
		printf("hash_walk: bucket #%d at %p\n", b-h->buckets, *b);
#endif
		for (j=(*b)->i-1, e=(*b)->elem; j>=0; j--,e++)
			callback(*e, arg);
	}
}
