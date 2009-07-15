.PHONY: all clean

all: redirect

OBJS = logging.o config.o tools.o redirect.o
CFLAGS = -g #-DDEBUG

redirect: $(OBJS)
	$(CC) $(OBJS) -o redirect -lpq -lconfig

clean:
	rm -f *.o redirect
