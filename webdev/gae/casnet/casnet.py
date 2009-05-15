import sys
sys.path.append('tasks')

from fetch import *
from google.appengine.ext import webapp
from google.appengine.ext.webapp.util import run_wsgi_app
from google.appengine.ext import db
from django.utils.feedgenerator import Rss201rev2Feed, Atom1Feed

class Feeds(webapp.RequestHandler):
  def get(self):
    site = self.request.path[len('/feeds/'):]
    link = db.Link('http://' + site)

    feed_type = 'rss2'
    feed_title = 'blog.solrex.cn'
    if feed_type == 'rss2': 
      feed = Rss201rev2Feed(title = feed_title, link = 'http://blog.solrex.cn',
                            description= u'test', subtitle = u'test' )
    statusdbs = db.GqlQuery("SELECT * FROM StatusDB WHERE url='%s' ORDER BY date DESC LIMIT 10" % link)
    for status in statusdbs:
      post_url = status.url
      if feed_type == 'rss2':
        feed.add_item(title = str(status.status_code), author_email = 'solrex@gmail.com', 
                      link = status.url, description = u'test', pubdate = status.date,
                      unique_id = status.url, categories = 'test')
    self.response.headers['Content-Type'] = 'application/rss+xml; charset=utf-8'
    from StringIO import StringIO
    buffer = StringIO()
    feed.write(buffer, 'utf-8')
    feed_xml = buffer.getvalue()
    buffer.close()
    self.response.out.write(feed_xml)

class MainPage(webapp.RequestHandler):
  def get(self):
    self.response.out.write('<html><body>')

    statusdbs = db.GqlQuery("SELECT * FROM StatusDB ORDER BY date DESC LIMIT 10")

    for i in statusdbs:
      self.response.out.write('%s returns at time %s' % (i.url, i.date))
      self.response.out.write('<blockquote>%s</blockquote>' %
                              i.status_code)

url_mapping = [
   ('/', MainPage),
   ('/feeds/[a-zA-Z0-9-\.]+\.[a-zA-Z.]{2,5}$', Feeds),
  ]

def main():
  mapping = url_mapping
  application = webapp.WSGIApplication(mapping, debug=True)
  run_wsgi_app(application)

if __name__ == "__main__":
  main()
