#!/usr/bin/env python2.6
# -*- coding: utf-8 -*-

# This file is part of Tohr.
#
# Tohr is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.
#
# Tohr is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with GAEProxy.  If not, see <http://www.gnu.org/licenses/>.

import BaseHTTPServer, SocketServer
import urllib, urllib2, urlparse
import zlib, base64
import socket
import errno
import os, sys
import common
import json

try:
  import ssl
  SSLEnable = True
except:
  SSLEnable = False

# global varibles
localProxy = common.DEF_LOCAL_PROXY
fetchServer = common.DEF_FETCH_SERVER
isGoogleProxy = {}

class LocalProxyHandler(BaseHTTPServer.BaseHTTPRequestHandler):
  postMaxLen = 1024*1024  # 1MB

  def encode(self, data, coding):
    if data == '':  return data;
    if coding == 'zlib' or coding == 'compress':
      return data.encode('zlib')
    elif coding == 'base64':
      return data.encode('base64')
    return data

  def decode(self, data, coding):
    if data == '':  return data
    if coding == 'zlib' or coding == 'compress':
      return data.decode('zlib')
    elif coding == 'base64':
      return data.decode('base64')
    return data

  def uc_param(self, param):
    ucParam = ''
    for word in param.split('-'):
      ucParam += word.capitalize() + '-'
    return ucParam.rstrip('-')

  def do_CONNECT(self):
    if not SSLEnable:
      self.send_error(501, 'Local Proxy Error: HTTPS needs Python 2.6 or newer verion.')
      self.connection.close()
      return

    # for ssl proxy
    (httpsHost, _, httpsPort) = self.path.partition(':')
    if httpsPort != '' and httpsPort != '443':
      self.send_error(501, 'Local Proxy Error: Only port 443 is allowed for https.')
      self.connection.close()
      return
    if sys.platform != 'win32':
      crtFile = common.dir + '/certs/' + httpsHost + '.crt'
      csrFile = common.dir + '/certs/' + httpsHost + '.csr'
      keyFile = common.dir + '/certs/' + httpsHost + '.key'
      if not os.path.isfile(crtFile):
        cmd = 'openssl genrsa -out %s 1024' % keyFile
        os.system(cmd)
        cmd = 'openssl req -batch -new -key %s -out %s -subj "/C=CN/ST=BJ/L=BJ/O=%s/CN=%s"' % (keyFile, csrFile, httpsHost, httpsHost)
        os.system(cmd)
        cmd = 'cd %s && openssl ca -batch -config %s/ca.conf -notext -out %s -infiles %s'% (common.dir, common.dir, crtFile, csrFile)
        os.system(cmd)
    else:
      crtFile = common.dir + '/ca/ca.crt'
      keyFile = common.dir + '/ca/ca.key'

    # continue
    self.wfile.write('HTTP/1.1 200 OK\r\n')
    self.wfile.write('\r\n')
    sslSock = ssl.wrap_socket(self.connection,
                              server_side=True,
                              certfile=crtFile,
                              keyfile=keyFile)

    # rewrite request line, url to abs
    firstLine = ''
    while True:
      chr = sslSock.read(1)
      # EOF?
      if chr == '':
        # bad request
        sslSock.close()
        self.connection.close()
        return
      # newline(\r\n)?
      if chr == '\r':
        chr = sslSock.read(1)
        if chr == '\n':
          # got
          break
        else:
          # bad request
          sslSock.close()
          self.connection.close()
          return
      # newline(\n)?
      if chr == '\n':
        # got
        break
      firstLine += chr

    # get path
    (self.command, path, ver) = firstLine.split()
    if path.startswith('/'):
      path = 'https://%s' % httpsHost + path

    # connect to local proxy server
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock.connect(('127.0.0.1', common.DEF_LISTEN_PORT))
    sock.send('%s %s %s\r\n' % (self.command, path, ver))

    # forward https request
    sslSock.settimeout(1)
    while True:
      try:
        data = sslSock.read(8192)
      except ssl.SSLError, e:
        if str(e).lower().find('timed out') == -1:
          # error
          sslSock.close()
          self.connection.close()
          sock.close()
          return
        # timeout
        break
      if data != '':
        sock.send(data)
      else:
        # EOF
        break
    sslSock.setblocking(True)

    # simply forward response
    while True:
      data = sock.recv(8192)
      if data != '':
        sslSock.write(data)
      else:
        # EOF
        break

    # clean
    sock.close()
    sslSock.shutdown(socket.SHUT_WR)
    sslSock.close()
    self.connection.close()

  def do_METHOD(self):
    # check http self.command and post data
    if self.command == 'GET' or self.command == 'HEAD':
      # no data
      postDataLen = 0
    elif self.command == 'POST':
      # get length of post data
      postDataLen = 0
      if self.headers.has_key('Content-Length'):
        postDataLen = int(self.headers['Content-Length'])
      if postDataLen > self.postMaxLen:
        self.send_error(413, 'Local Proxy Error: Post data length exceeds GAE limit (1MB).')
        self.connection.close()
        return
    else:
      self.send_error(501, 'Local Proxy Error: Unsupported HTTP method.')
      self.connection.close()
      return

    # get post data
    if postDataLen > 0:
      postData = self.rfile.read(postDataLen)
      if len(postData) != postDataLen:
        self.send_error(400, "Local Proxy Error: Bad Request.")
        self.connection.close()
        return
      postData = self.encode(postData, 'base64')
    else:
      postData = ''

    # do path check
    (scm, netloc, path, data, query, _) = urlparse.urlparse(self.path)
    if (scm.lower() != 'http' and scm.lower() != 'https') or not netloc:
      self.send_error(501, 'Local Proxy Error: Unsupported scheme(ftp for example).')
      self.connection.close()
      return

    # create new path
    path = urlparse.urlunparse((scm, netloc, path, data, query, ''))

    headers = ''
    for key in self.headers:
      headers += self.uc_param(key) + ': ' + self.headers[key] + '\r\n'

    # create request for Tohr Router
    message = json.dumps({'method': self.command,
                          'path': path,
                          'payload_coding': 'base64',
                          'payload': postData,
                          'headers': headers,})

    coding = 'zlib'
    data = self.encode(message, coding)
    request = urllib2.Request(fetchServer)
    request.add_header('Accept-Encoding', 'identity, *;q=0')
    request.add_header('Connection', 'close')
    request.add_header('Content-Type', 'application/octet-stream')
    request.add_header('Tohr-version', '0.1')
    request.add_header('Tohr-coding', coding)

    # create new opener
    if localProxy != '':
      proxyHandler = urllib2.ProxyHandler({'http': localProxy})
    else:
      proxyHandler = urllib2.ProxyHandler(isGoogleProxy)
    opener = urllib2.build_opener(proxyHandler)
    # set the opener as the default opener
    urllib2.install_opener(opener)

    try:
      resp = urllib2.urlopen(request, data)
    except urllib2.HTTPError, e:
      print e
      self.connection.close()
      return

    # Parse response
    # Check if response is a Tohr response.
    if 'Tohr-Version' in resp.info():
      tohrVersion = resp.info()['Tohr-Version']
      tohrCoding = 'plain'
      if tohrVersion == '0.1':
        tohrCoding = resp.info()['Tohr-Coding']
        message = self.decode(resp.read(), tohrCoding)
      else:
        print "Unkown version"
        return
      messageDict = json.loads(message)
      try:
        self.send_response(int(messageDict['status']),
                           messageDict['status_msg'])
      except socket.error, (errNum, _):
        # Connection/Webpage closed before proxy return
        if errNum == errno.EPIPE or errNum == 10053: # *nix, Windows
          return
        else:
          raise
      headers = messageDict['headers'].split('\r\n')
      # The headers
      for header in headers:
        (name, _, value) = header.partition(': ')
        if ( _ == ': '):
          self.send_header(self.uc_param(name), value)
      self.end_headers()
      # The page
      payload_coding = messageDict['payload_coding']
      self.wfile.write(self.decode(messageDict['payload'], payload_coding))
    else:
      try:
        self.send_response(200, 'OK')
      except socket.error, (errNum, _):
        # Connection/Webpage closed before proxy return
        if errNum == errno.EPIPE or errNum == 10053: # *nix, Windows
          return
        else:
          raise
      # The headers
      for key in resp.info():
        self.send_header(key, resp.info()[key])
      self.end_headers()
      # The page
      self.wfile.write(resp.read())

    self.connection.close()

  do_GET  = do_METHOD
  do_HEAD = do_METHOD
  do_POST = do_METHOD

class ThreadingHTTPServer(SocketServer.ThreadingMixIn,
              BaseHTTPServer.HTTPServer):
  pass

def shallWeNeedDefaultProxy():
  global isGoogleProxy

  # send http request directly
  request = urllib2.Request(common.LOAD_BALANCE)
  try:
    # avoid wait too long at startup, timeout argument need py2.6 or later.
    if sys.hexversion > 0x20600f0:
      resp = urllib2.urlopen(request, timeout=3)
    else:
      resp = urllib2.urlopen(request)
    resp.read()
  except:
    isGoogleProxy = {'http': common.GOOGLE_PROXY}

def getAvailableFetchServer():
  request = urllib2.Request(common.LOAD_BALANCE)
  if localProxy != '':
    proxyHandler = urllib2.ProxyHandler({'http': localProxy})
  else:
    proxyHandler = urllib2.ProxyHandler(isGoogleProxy)
  opener = urllib2.build_opener(proxyHandler)
  urllib2.install_opener(opener)
  try:
    resp = urllib2.urlopen(request)
    return resp.read().strip()
  except:
    return ''

def parseConf(confFile):
  global localProxy, fetchServer

  # read config file
  try:
    fp = open(confFile, 'r')
  except IOError:
    # use default parameters
    return
  # parse user defined parameters
  while True:
    line = fp.readline()
    if line == '':
      # end
      break
    # parse line
    line = line.strip()
    if line == '':
      # empty line
      continue
    if line.startswith('#'):
      # comments
      continue
    (name, sep, value) = line.partition('=')
    if sep == '=':
      name = name.strip().lower()
      value = value.strip()
      if name == 'local_proxy':
        localProxy = value
      elif name == 'fetch_server':
        fetchServer = value
  fp.close()

if __name__ == '__main__':
  print '--------------------------------------------'
  if SSLEnable:
    print 'HTTP Enabled : YES'
    print 'HTTPS Enabled: YES'
  else:
    print 'HTTP Enabled : YES'
    print 'HTTPS Enabled: NO'

  parseConf(common.DEF_CONF_FILE)

  if localProxy == '':
    shallWeNeedDefaultProxy()

  if fetchServer == '':
    fetchServer = getAvailableFetchServer()
  if fetchServer == '':
    raise common.GAppProxyError('Invalid response from load balance server.')

  # Want to know whether you are connect to fetchserver direct? uncomment it.
  print 'Direct Fetch : %s' % ( isGoogleProxy and 'NO' or 'YES' )
  print 'Local Proxy  : %s' % localProxy
  print 'Fetch Server : %s' % fetchServer
  print '--------------------------------------------'
  httpd = ThreadingHTTPServer(('', common.DEF_LISTEN_PORT),
                LocalProxyHandler)
  httpd.serve_forever()
