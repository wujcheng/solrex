LIBJINGLE_DIR=../libjingle

CPP=g++

BIN = ygtalk
SRCS = ygtalk.cc callclient.cc console.cc chatclient.cc fileclient.cc
HEADERS = ygtalk.h callclient.h console.h chatclient.h fileclient.h
OBJS = ygtalk.o callclient.o console.o chatclient.o fileclient.o


AM_CPPFLAGS = -DPOSIX
CPPFLAGS = -I. -I$(LIBJINGLE_DIR) $(AM_CPPFLAGS) -I/usr/include -I/usr/include/speex -g -O2 

LFLAGS = /bin/sh $(LIBJINGLE_DIR)/libtool --silent --tag=CXX --mode=link g++  -g -O2 -o call call-call_main.o call-callclient.o call-console.o 

LDFLAGS = $(LIBJINGLE_DIR)/talk/examples/login/libcricketexampleslogin.la $(LIBJINGLE_DIR)/talk/session/phone/libcricketsessionphone.la $(LIBJINGLE_DIR)/talk/p2p/client/libcricketp2pclient.la $(LIBJINGLE_DIR)/talk/session/fileshare/libcricketsessionfileshare.la $(LIBJINGLE_DIR)/talk/session/tunnel/libcricketsessiontunnel.la $(LIBJINGLE_DIR)/talk/p2p/base/libcricketp2pbase.la $(LIBJINGLE_DIR)/talk/xmpp/libcricketxmpp.la $(LIBJINGLE_DIR)/talk/xmllite/libcricketxmllite.la $(LIBJINGLE_DIR)/talk/base/libcricketbase.la -lexpat -lortp -lpthread -lssl -lcrypto -L/usr/lib -L/usr/speex/lib -lspeex -lm -Wl,--export-dynamic -pthread -lgmodule-2.0 -ldl -lgthread-2.0 -lrt -lglib-2.0   $(LIBJINGLE_DIR)/talk/third_party/mediastreamer/libmediastreamer.la -lasound

all: $(BIN)

$(BIN): $(OBJS)
	 /bin/sh $(LIBJINGLE_DIR)/libtool --silent --tag=CXX --mode=link g++ -g -O2 -o $@ $^ $(LDFLAGS)

$(OBJS): %.o : %.cc
	$(CPP) $(CPPFLAGS) -c $<

clean:
	@rm -f *.o

distclean: clean
	@rm -f $(BIN)
