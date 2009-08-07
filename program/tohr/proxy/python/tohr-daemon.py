#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# This file is part of Tohr.
#
# The originally version of this file was borrowed from GAppProxy project
# <http://code.google.com/p/gappproxy/>. Special thanks to dugang@188.com.
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
# along with Tohr.  If not, see <http://www.gnu.org/licenses/>.

import os, sys, errno, logging
import BaseHTTPServer, SocketServer, socket
import urllib2, urlparse, json
import re

try:
  import ssl
  SSLEnable = True
except:
  SSLEnable = False

# Configure logging level, for debugging.
logging.basicConfig(level=logging.DEBUG,
    #format='%(filename)s:%(lineno)d: %(levelname)s: %(message)s')
    format='Tohr %(levelname)s: %(message)s')

# Global varibles
conf = {}

class TohrDaemonHandler(BaseHTTPServer.BaseHTTPRequestHandler):

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

  # Upper case http header paramaters, e.g. set-cookies => Set-Cookies
  def uc_param(self, param):
    ucParam = ''
    for word in param.split('-'):
      ucParam += word.capitalize() + '-'
    return ucParam.rstrip('-')

  def report(self, status, message):
    self.send_error(status, 'Tohr-daemon: '+message)
    self.connection.close()
    logging.error('%d: %s' % (status, message))

  # Handle https connections
  def do_CONNECT(self):
    if not SSLEnable:
      self.report(501, 'HTTPS support requires Python 2.6 or higher.')
      return

    # For ssl proxy
    (httpsHost, _, httpsPort) = self.path.partition(':')
    if httpsPort != '' and httpsPort != '443':
      self.report(501, 'Only port 443 is allowed for https.')
      return
    caDir = os.path.join(conf['install-dir'], 'ca')
    if sys.platform == 'win32':
      crtFile = os.path.join(caDir, 'ca.crt')
      keyFile = os.path.join(caDir, 'ca.key')
    else:
      crtFile = os.path.join(conf['install-dir'], 'certs', httpsHost + '.crt')
      csrFile = os.path.join(conf['install-dir'], 'certs', httpsHost + '.csr')
      keyFile = os.path.join(conf['install-dir'], 'certs', httpsHost + '.key')
      if not os.path.isfile(crtFile):
        cmd = 'openssl genrsa -out %s 1024' % keyFile
        os.system(cmd)
        cmd = 'openssl req -batch -new -key %s -out %s -subj "/C=CN/ST=BJ/L=BJ/O=%s/CN=%s"' % (keyFile, csrFile, httpsHost, httpsHost)
        os.system(cmd)
        cmd = 'cd %s && openssl ca -batch -config %s/ca.conf -notext -out %s -infiles %s'% (conf['install-dir'], conf['install-dir'], crtFile, csrFile)
        os.system(cmd)

    # Continue
    self.wfile.write('HTTP/1.1 200 OK\r\n')
    self.wfile.write('\r\n')
    sslSock = ssl.wrap_socket(self.connection,
                              server_side=True,
                              certfile=crtFile,
                              keyfile=keyFile)

    # Rewrite request line, url to abs
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
    sock.connect(('127.0.0.1', conf['listen-port']))
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

  # Handle http request and responses.
  def do_METHOD(self):
    # Check upstreaming http method and post data.
    methodList = ['GET', 'POST']
    if self.command not in methodList:
      self.report(501, 'Unsupported HTTP method.')
      return
    payloadLen = 0
    if self.command == 'POST':
      if self.headers.has_key('Content-Length'):
        payloadLen = int(self.headers['Content-Length'])

    # Get post data
    payload = ''
    if payloadLen > 0:
      payload = self.rfile.read(payloadLen)
      if len(payload) != payloadLen:
        self.report(400, "Request Content-Length error.")
        return
      # Encoding payload binary data to a string
      payload = self.encode(payload, conf['payload-coding'])

    # Check path
    (scm, netloc, path, data, query, _) = urlparse.urlparse(self.path)
    if (scm.lower() != 'http' and scm.lower() != 'https') or not netloc:
      self.report(501, 'Unsupported scheme: %s', scm.lower())
      return
    path = urlparse.urlunparse((scm, netloc, path, data, query, ''))

    # Make headers
    headers = ''
    for key in self.headers:
      headers += self.uc_param(key) + ': ' + self.headers[key] + '\r\n'
    print payload

    # Creat Tohr message for Tohr Router
    message = json.dumps({'method': self.command,
                          'path': path,
                          'headers': headers,
                          'payload_coding': conf['payload-coding'],
                          'payload': payload,})
    data = self.encode(message, conf['tohr-coding'])

    request = urllib2.Request(conf['tohr-router'])
    request.add_header('Accept-Encoding', 'identity, *;q=0')
    request.add_header('Connection', 'close')
    request.add_header('Content-Type', 'application/octet-stream')
    request.add_header('Tohr-version', '0.1')
    request.add_header('Tohr-coding', conf['tohr-coding'])

    # Create new opener
    if conf['outgoing-proxy'] != '':
      proxyHandler = urllib2.ProxyHandler({'http': conf['outgoing-proxy']})
    else:
      proxyHandler = urllib2.ProxyHandler({})
    opener = urllib2.build_opener(proxyHandler)
    # Set the opener as the default opener
    urllib2.install_opener(opener)

    try:
      resp = urllib2.urlopen(request, data)
    except urllib2.HTTPError, e:
      logging.error(e)
      self.report(591, 'Http error, see command line log.')
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
      logging.debug(messageDict['headers'])

      # The headers
      for header in headers:
        (name, _, value) = header.partition(': ')
        if ( _ == ': '):
          self.send_header(self.uc_param(name), value)
          print self.uc_param(name), ':', value
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

# Parse configuration file
def get_conf():
  if sys.platform == 'win32':
    settingDir = os.path.join(os.getenv('HOMEDRIVE'),
                              os.getenv('HOMEPATH'),
                              #+ os.getenv('HOMEPATH').decode('gbk').encode('utf8')
                              'Application Data',
                              'tohr')
    installDir = os.path.dirname(sys.argv[0]);
    pathList = [settingDir, installDir]
  else:
    settingDir = os.path.join(os.getenv('HOME'),
                              '.tohr')
    installDir = os.path.abspath(sys.path[0])
    pathList = [settingDir,
                '/etc',
                '/usr/local/etc',
                installDir]
  confPath = ''
  for directory in pathList:
    if os.path.isfile(os.path.join(directory, 'tohr.conf')):
      confPath = os.path.join(directory, 'tohr.conf')
      break
  if confPath == '':
    logging.error('''Configuration file not found.
    Please check the following path for "tohr.conf":
    %s
    Example "tohr.conf" can be found at:
    %s/tohr-example.conf''' % (str(pathList), installDir))
    exit()
  logging.info('Loading configuaration from %s' % confPath)
  try:
    f = open(confPath, 'r')
    content = f.read()
    f.close()
  except IOError, e:
    logging.error(e)
    exit()
  confStr = re.sub(r'#.*\n', r'', content)
  try:
    confs = json.loads(confStr)
  except:
    logging.debug(confStr)
    logging.error('''Unsupported 'tohr.conf' format.
    Check %s accoding to:
    %s/tohr-example.conf''' % (confPath, installDir))
    exit()
  confs['install-dir'] = installDir
  try:
    # Assign default values to options.
    if (confs['listen-address'] == ''):
      confs['listen-address'] = 'localhost'
    if (confs['listen-port'] == ''):
      confs['listen-port'] = 9090
    else:
      confs['listen-port'] = int(confs['listen-port'])
    if (confs['tohr-coding'] == ''):
      conf['tohr-coding'] = 'zlib'
    if (confs['payload-coding'] == ''):
      confs['payload-coding'] = 'base64'
  except KeyError, e:
    logging.error(e)
    logging.error('Your configuration file is incomplete, please check it.')
    exit()
  return confs

if __name__ == '__main__':
  logging.info('HTTPS Enabled: %s' % str(SSLEnable))
  if (SSLEnable != True):
    logging.info('HTTPS support requires python 2.6 or higher.')

  # Get configuration from tohr.conf file.
  conf = get_conf()
  logging.debug(conf)

  
  logging.info('Using Tohr router: %s' % conf['tohr-router'])
  logging.info('Using outgoing proxy: %s' % conf['outgoing-proxy'])
  logging.info('Start serving at http://%s:%d' % (conf['listen-address'],
                                                  conf['listen-port']))

  httpd = ThreadingHTTPServer(('', conf['listen-port']),
                TohrDaemonHandler)
  # Start serving
  httpd.serve_forever()
