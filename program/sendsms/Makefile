CC = gcc
CPP = g++
CFLAGS = -Iinclude -O2
LFLAGS = lib/libfetion_32.a -lcurl -pthread 

BIN = sendsms
SRCS = $(BIN).cpp
OBJS = $(BIN).o 

$(BIN): $(OBJS)
	$(CPP) $^ $(LFLAGS) -o $@ 

$(OBJS) : %.o : %.cpp
	$(CPP) $(CFLAGS) -c $<

install: $(BIN)
	install $< /usr/bin

clean:
	@rm -rf *.o

distclean: clean
	@rm -f $(BIN)
