#! /usr/bin/env python2.6
# -*- coding=utf-8 -*-

# This file is part of GAEProxy.
#
# GAEProxy is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.
#
# GAEProxy is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with GAEProxy.  If not, see <http://www.gnu.org/licenses/>.

import wsgiref.handlers, urlparse, StringIO, logging, base64, zlib
from google.appengine.ext import webapp
from google.appengine.api import urlfetch
from google.appengine.api import urlfetch_errors
import re
# from accesslog import logAccess


class MainHandler(webapp.RequestHandler):
  Software = 'GAppProxy/1.0.0 beta'
  # hop to hop header should not be forwarded
  HtohHdrs= ['connection', 'keep-alive', 'proxy-authenticate',
             'proxy-authorization', 'te', 'trailers',
             'transfer-encoding', 'upgrade']

  def myError(self, status, description, responseCoding):
    # header
    self.response.out.write('HTTP/1.1 %d %s\r\n' % (status, description))
    self.response.out.write('Server: %s\r\n' % self.Software)
    self.response.out.write('Content-Type: text/html\r\n')
    self.response.out.write('\r\n')
    # body
    content = '<h1>Fetch Server Error</h1><p>Error Code: %d<p>Message: %s' % (status, description)
    content = self.encode(content, responseCoding)
    self.response.out.write(content)

  def encode(self, data, coding):
    if data != '':
      if coding == 'zlib' or coding == 'compress':
        return zlib.compress(data)
      elif coding == 'base64':
        return base64.b64encode(data)
    return data

  def decode(self, data, coding):
    if data != '':
      if coding == 'zlib' or coding == 'compress':
        return zlib.decompress(data)
      elif coding == 'base64':
        return base64.b64decode(data)
    return data

  def post(self):
    try:
      version = self.request.get('version')
      origMethod = self.request.get('method')
      origHeaders = self.request.get('headers')

      if version == '1.0.1':
        pathCoding = self.request.get('path_coding')
        postCoding = self.request.get('post_coding')
        responseCoding = self.request.get('response_coding')

        # get post data
        origPath = self.decode(self.request.get('path'), pathCoding)
        origPostData = self.decode(self.request.get('postdata'), postCoding)
      else:
        origPath = self.request.get('encoded_path')
        if origPath != '':
          origPath = base64.b64decode(origPath)
        else:
          origPath = self.request.get('path')
        responseCoding = self.request.get('encodeResponse')
        origPostData = self.request.get('postdata')

      # check method
      if origMethod != 'GET' and origMethod != 'HEAD' \
               and origMethod != 'POST':
        # forbid
        self.myError(590, 'Invalid local proxy, Method not allowed.', responseCoding)
        return
      if origMethod == 'GET':
        method = urlfetch.GET
      elif origMethod == 'HEAD':
        method = urlfetch.HEAD
      elif origMethod == 'POST':
        method = urlfetch.POST

      # check path
      (scm, netloc, path, params, query, _) = urlparse.urlparse(origPath)
      if (scm.lower() != 'http' and scm.lower() != 'https') or not netloc:
        self.myError(590, 'Invalid local proxy, Unsupported Scheme.', responseCoding)
        return
      # create new path
      newPath = urlparse.urlunparse((scm, netloc, path, params, query, ''))

      # make new headers
      newHeaders = {}
      contentLength = 0
      si = StringIO.StringIO(origHeaders)
      while True:
        line = si.readline()
        line = line.strip()
        if line == '':
          break
        # parse line
        (name, _, value) = line.partition(':')
        name = name.strip()
        value = value.strip()
        if name.lower() in self.HtohHdrs:
          # don't forward
          continue
        newHeaders[name] = value
        if name.lower() == 'content-length':
          contentLength = int(value)
      # predined header
      newHeaders['Connection'] = 'close'

      # check post data
      if contentLength != 0:
        if contentLength != len(origPostData):
          self.myError(590, 'Invalid local proxy, Wrong length of post data.',
                       responseCoding)
          return
      else:
        origPostData = ''

      if origPostData != '' and origMethod != 'POST':
        self.myError(590, 'Invalid local proxy, Inconsistent method and data.',
                     responseCoding)
        return
    except Exception, e:
      self.myError(591, 'Fetch server error, %s.' % str(e), responseCoding)
      return

    # fetch, try 3 times
    for _ in range(3):
      try:
        resp = urlfetch.fetch(newPath, origPostData, method, newHeaders, False, False)
        break
      except urlfetch_errors.ResponseTooLargeError:
        self.myError(591, 'Fetch server error, Sorry, Google\'s limit, file size up to 1MB.', responseCoding)
        return
      except Exception:
        continue
    else:
      self.myError(591, 'Fetch server error, The target server may be down or not exist. Another possibility: try to request the URL directly.', responseCoding)
      return

    # forward
    self.response.headers['Content-Type'] = 'application/octet-stream'
    # status line
    self.response.out.write('HTTP/1.1 %d %s\r\n' % (resp.status_code, self.response.http_status_message(resp.status_code)))
    # headers
    # default Content-Type is text
    textContent = True
    for header in resp.headers:
      if header.strip().lower() in self.HtohHdrs:
        # don't forward
        continue
      # NOTE 20090710/Solrex: Fix multi-cookie process problem
      if header.lower() == 'set-cookie':
        logging.info('O %s: %s' % (header, resp.headers[header]))
        scs = re.sub(r', ([^;]+=)', r'\n\1', resp.headers[header]).split('\n')
        for sc in scs:
          logging.info('N %s: %s' % (header, sc.strip()))
          self.response.out.write('%s: %s\r\n' % (header, sc.strip()))
        continue
      # other
      self.response.out.write('%s: %s\r\n' % (header, resp.headers[header]))
      # check Content-Type
      if header.lower() == 'content-type':
        if resp.headers[header].lower().find('text') == -1:
          # not text
          textContent = False
    self.response.out.write('\r\n')
    if textContent:
    # need encode?
      content = self.encode(resp.content, responseCoding)
    else:
      content = resp.content
    self.response.out.write(content)

  # log
  #logAccess(netloc, self.request.remote_addr)

  def get(self):
    self.response.headers['Content-Type'] = 'text/html; charset=utf-8'
    self.response.out.write( \
'''
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Working</title>
    </head>
    <body>
      working
    </body>
</html>
''' % self.Software)


def main():
  application = webapp.WSGIApplication([('/fetch.py', MainHandler)])
  wsgiref.handlers.CGIHandler().run(application)


if __name__ == '__main__':
  main()

