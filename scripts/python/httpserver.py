#!/usr/bin/env python
import sys
from socket import *
import fcntl
import struct
import SimpleHTTPServer
import SocketServer

def get_ip_address(ifname):
  s = socket(AF_INET, SOCK_STREAM)
  return inet_ntoa(fcntl.ioctl(s.fileno(), 0x8915, struct.pack('256s', ifname[:15]))[20:24])

addr    = get_ip_address('eth0')
port    = 80
handler = SimpleHTTPServer.SimpleHTTPRequestHandler
httpd   = SocketServer.TCPServer((addr, port), handler)
print "HTTP server is running at: ", addr, port

httpd.serve_forever()
