.PHONY: all clean

all: redirect

OBJS = hash.o logging.o config.o tools.o redirect.o

redirect: $(OBJS)
	$(CC) $(OBJS) -o redirect -lpq -lconfig

clean:
	rm -f *.o redirect
