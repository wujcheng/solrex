
CC = gcc
CPP = g++
CFLAGS = -Iinclude -g
LFLAGS = -lcurl -pthread

BIN = sendsms
SRC = $(BIN).cpp
OBJ = $(BIN).o

$(BIN): $(OBJ)
	$(CPP) $^ lib/libfetion_32.a $(LFLAGS) -o $@ 

$(OBJ) : %.o : %.cpp
	$(CPP) $(CFLAGS) -c $<

clean:
	rm -rf *.o

distclean: clean
	rm -f sendsms
