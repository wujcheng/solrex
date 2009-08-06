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

import wsgiref.handlers, urlparse, logging
from django.utils import simplejson as json
from google.appengine.ext import webapp
from google.appengine.api import urlfetch
from google.appengine.api import urlfetch_errors
import re

class MainHandler(webapp.RequestHandler):
  Server = 'Tohr Router/0.1'
  # hop to hop header should not be forwarded
  SkipHeaders= ['connection', 'keep-alive', 'proxy-authenticate',
             'proxy-authorization', 'te', 'trailers',
             'transfer-encoding', 'upgrade']

  def report(self, status, description):
    # header
    self.response.out.write('HTTP/1.1 %d %s\r\n' % (status, description))
    self.response.out.write('Server: %s\r\n' % self.Server)
    self.response.out.write('Content-Type: text/html\r\n')
    self.response.out.write('Tohr-version: 0.1\r\n')
    self.response.out.write('Tohr-coding: plain\r\n')
    self.response.out.write('\r\n')
    # body
    content = '<h1>Tohr Router Error</h1><p>Error Code: %d<p>Message: %s'\
              % (status, description)
    self.response.out.write(content)

  def encode(self, data, coding):
    if data == '': return data
    if coding == 'zlib' or coding == 'base64':
      return data.encode(coding)
    return data

  def decode(self, data, coding):
    if data == '': return data
    if coding == 'zlib' or coding == 'base64':
      return data.decode(coding)
    return data

  def post(self):
    try:
      # Get Tohr version
      if 'Tohr-Version' not in self.request.headers:
        self.report(590, 'Not a Tohr Message, check your entry Tohr router.')
        return
      
      inTohrVersion = self.request.headers['Tohr-Version']
      inTohrCoding = 'plain'
      # Get Tohr coding and decode(or unzip, or decrypt) incoming Tohr message
      if inTohrVersion == '0.1':
        inTohrCoding = self.request.headers['Tohr-Coding']
        inTohrMessage = self.decode(self.request.body, inTohrCoding)
      else:
        self.report(590, 'Unsupported Tohr version number: %s' % inTohrVersion)
        return

      # Load json object to dictionary.
      inTohrMessageDict = json.loads(inTohrMessage)

      # Get the method of incoming Tohr message
      methodDict = {'GET': urlfetch.GET, 'POST': urlfetch.POST}
      if inTohrMessageDict['method'] not in methodDict:
        self.report(590, 'Unsupported method: %s' % inTohrMessageDict['method'])
        return
      method = methodDict[inTohrMessageDict['method']]

      # Make path from path string in the incoming Tohr message
      path = inTohrMessageDict['path']
      (scm, netloc, path, params, query, _) = urlparse.urlparse(path)
      if (scm.lower() != 'http' and scm.lower() != 'https') or not netloc:
        self.report(590, 'Unsupported scheme: %s' % scm.lower())
        return
      path = urlparse.urlunparse((scm, netloc, path, params, query, ''))

      # Make headers from the 'header' argument of incoming Tohr message
      headers = {}
      for headerStr in inTohrMessageDict['headers'].split('\r\n'):
        (name, _, value) = headerStr.partition(': ')
        # Skip hop to hop headers
        if name.lower() in self.SkipHeaders: continue
        if name != '':  headers[name] = value
      headers['Connection'] = 'close'

      # Get payload coding of incoming Tohr message
      try:
        inTohrPayloadCoding = inTohrMessageDict['payload_coding']
      except KeyError:
        inTohrPayloadCoding = 'base64'

      # Check postdata lenth of incoming Tohr message
      if 'Content-Length' in headers:
        payload = self.decode(inTohrMessageDict['payload'], inTohrPayloadCoding)
        if int(headers['Content-Length']) != len(payload):
          self.report(590, 'Wrong length of postdata: %d, claimed %d' \
                      % (len(payload), contentLength))
          return
      else:
        payload = ''
      if payload != '' and method != urlfetch.POST:
        self.report(590, 'Error http method. Payload without POST.')
        return
    except Exception, e:
      self.report(591, 'Unkown error, %s.' % str(e))
      return

    # Fetch url with Google App Engine Feth API, retry 3 times
    for _ in range(3):
      try:
        resp = urlfetch.fetch(path, payload, method, headers, False, False)
        break
      except urlfetch_errors.ResponseTooLargeError:
        self.report(591, 'Response exceed Google limit: >1MB')
        return
      except Exception:
        continue
    else:
      self.report(591, 'Fails to get the page.')
      return

    # Construct outgoing Tohr Message
    # Codings
    outTohrCoding = inTohrCoding
    outTohrPayloadCoding = inTohrPayloadCoding
    # HTTP status
    outTohrStatus = resp.status_code
    outTohrStatusMsg = self.response.http_status_message(outTohrStatus)
    # HTTP headers
    outTohrHeaders = ''
    for header in resp.headers:
      # Skip hop to hop headers
      if header.strip().lower() in self.SkipHeaders: continue
      # Fix multi-cookie process problem, special to Google.
      if header.lower() == 'set-cookie':
        scs = re.sub(r', ([^;]+=)', r'\n\1', resp.headers[header]).split('\n')
        for sc in scs:
          outTohrHeaders += '%s: %s\r\n' % (header, sc.strip())
        continue
      # Other headers
      outTohrHeaders += '%s: %s\r\n' % (header, resp.headers[header])
    # Response raw data enbedded in payload
    outTohrPayload = self.encode(resp.content, outTohrPayloadCoding)
    # Dump dictionary to JSON object string
    message = json.dumps({'status': outTohrStatus,
                          'status_msg': outTohrStatusMsg,
                          'headers': outTohrHeaders,
                          'payload_coding': outTohrPayloadCoding,
                          'payload': outTohrPayload})
    message = self.encode(message, outTohrCoding)

    # Forward the outgoing Tohr Message back to the request Tohr router
    self.response.headers['Content-Type'] = 'application/octet-stream'
    self.response.headers['Tohr-Version'] = '0.1'
    self.response.headers['Tohr-Coding'] = outTohrCoding

    self.response.out.write(message)

    return

  def get(self):
    self.response.out.write('''<html><head><title>Tohr Router</title></head>
    <body><h1>It works!</h1></body></html>''')

def main():
  application = webapp.WSGIApplication([('/tohr', MainHandler)])
  wsgiref.handlers.CGIHandler().run(application)

if __name__ == '__main__':
  main()
