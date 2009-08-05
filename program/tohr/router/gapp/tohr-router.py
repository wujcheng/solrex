#! /usr/bin/env python2.6
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
# along with Tohr.  If not, see <http://www.gnu.org/licenses/>.

import wsgiref.handlers, urlparse, StringIO, logging
from django.utils import simplejson as json
from google.appengine.ext import webapp
from google.appengine.api import urlfetch
from google.appengine.api import urlfetch_errors
import re

class MainHandler(webapp.RequestHandler):
  Server = 'Tohr Router/0.1'
  # hop to hop header should not be forwarded
  HtohHdrs= ['connection', 'keep-alive', 'proxy-authenticate',
             'proxy-authorization', 'te', 'trailers',
             'transfer-entohrCoding', 'upgrade']

  def report(self, status, description, tohrCoding):
    # header
    self.response.out.write('HTTP/1.1 %d %s\r\n' % (status, description))
    self.response.out.write('Server: %s\r\n' % self.Server)
    self.response.out.write('Content-Type: text/html\r\n')
    self.response.out.write('\r\n')
    # body
    content = '<h1>Tohr Router Error</h1><p>Error Code: %d<p>Message: %s'\
              % (status, description)
    content = self.encode(content, tohrCoding)
    self.response.out.write(content)

  def encode(self, data, tohrCoding):
    if data != '':
      if tohrCoding == 'zlib' or tohrCoding == 'base64':
        return data.encode(tohrCoding)
    return data

  def decode(self, data, tohrCoding):
    if data != '':
      if tohrCoding == 'zlib' or tohrCoding == 'base64':
        return data.decode(tohrCoding)
    return data

  def post(self):
    try:
      # Get Tohr version
      tohrVersion = self.request.headers['Tohr-version']
      tohrCoding = 'plain'

      # Get tohrCoding and decode(or unzip, or decrypt) payload
      if tohrVersion == '0.1':
        tohrCoding = self.request.headers['Tohr-Coding']
        message = self.request.body
        message = self.decode(message, tohrCoding)
      else:
        self.report(590, 'Invalide Tohr version number: %s' % tohrVersion,
                    tohrCoding)

      # Dump json objects to dictionary.
      messageDict = json.loads(message)

      # Get the method of original request
      methodDict = {'GET': urlfetch.GET, 'HEAD': urlfetch.HEAD,
                    'POST': urlfetch.POST, 'PUT': urlfetch.PUT}
      if messageDict['method'] not in methodDict:
        self.report(590, 'Invalid method: %s' % messageDict['method'], tohrCoding)
        return
      method = methodDict[messageDict['method']]

      # Make path from path string in the original request
      path = messageDict['path']
      (scm, netloc, path, params, query, _) = urlparse.urlparse(path)
      if (scm.lower() != 'http' and scm.lower() != 'https') or not netloc:
        self.report(590, 'Invalid scheme: %s' % scm.lower(), tohrCoding)
        return
      path = urlparse.urlunparse((scm, netloc, path, params, query, ''))

      # Make headers from the header string of original request
      headers = {}
      contentLength = 0
      si = StringIO.StringIO(messageDict['headers'])
      while True:
        line = si.readline().strip()
        if line == '':  break
        (name, _, value) = line.partition(':')
        name = name.strip()
        value = value.strip()
        # Skip hop to hop headers
        if name.lower() in self.HtohHdrs:  continue
        headers[name] = value
        if name.lower() == 'content-length':
          contentLength = int(value)
      headers['Connection'] = 'close'

      # Check postdata lenth of original request
      if contentLength != 0:
        payload = messageDict['payload'].encode('utf-8')
        logging.info(payload)
        if contentLength != len(payload):
          self.report(590, 'Wrong length of postdata: %d, claimed %d' \
                      % (len(payload), contentLength), tohrCoding)
          return
      else:
        payload = ''

      if payload != '' and method != urlfetch.POST:
        self.report(590, 'Inconsistent method and data.', tohrCoding)
        return
    except Exception, e:
      self.report(591, 'Unkown error, %s.' % str(e), tohrCoding)
      return

    # Fetch url, retry 3 times
    for _ in range(3):
      try:
        resp = urlfetch.fetch(path, payload, method, headers, False, False)
        break
      except urlfetch_errors.ResponseTooLargeError:
        self.report(591, 'Response exceed Google limit: >1MB', tohrCoding)
        return
      except Exception:
        continue
    else:
      self.report(591, 'Fails to get the page. ', tohrCoding)
      return

    # Forward the response back to client
    self.response.headers['Content-Type'] = 'application/octet-stream'
    # HTTP status
    self.response.out.write('HTTP/1.0 %d %s\r\n' % (resp.status_code,
                  self.response.http_status_message(resp.status_code)))
    # HTTP headers
    textContent = True
    for header in resp.headers:
      # Skip hop to hop headers
      if header.strip().lower() in self.HtohHdrs:  continue
      # Fix multi-cookie process problem
      if header.lower() == 'set-cookie':
        scs = re.sub(r', ([^;]+=)', r'\n\1', resp.headers[header]).split('\n')
        for sc in scs:
          self.response.out.write('%s: %s\r\n' % (header, sc.strip()))
        continue
      # Other headers
      self.response.out.write('%s: %s\r\n' % (header, resp.headers[header]))
      # Check Content-Type
      if header.lower() == 'content-type':
        if resp.headers[header].lower().find('text') == -1:
          # not text
          textContent = False
    self.response.out.write('Tohr-version: 0.1\r\n')
    if textContent:
      # Encode text content.
      self.response.out.write('Tohr-coding: %s\r\n' % tohrCoding)
      content = self.encode(resp.content, tohrCoding)
    else:
      content = resp.content

    self.response.out.write('\r\n')
    self.response.out.write(content)

  def get(self):
    self.response.out.write('''<html><head><title>Tohr Router</title></head>
    <body><h1>It works!</h1></body></html>''')

def main():
  application = webapp.WSGIApplication([('/tohr', MainHandler)])
  wsgiref.handlers.CGIHandler().run(application)

if __name__ == '__main__':
  main()
