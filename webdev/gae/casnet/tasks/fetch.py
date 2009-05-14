import cgi

from google.appengine.api import users
from google.appengine.api import urlfetch
from google.appengine.ext import webapp
from google.appengine.ext.webapp.util import run_wsgi_app
from google.appengine.ext import db

class StatusDB(db.Model):
  url = db.LinkProperty()
  status_code = db.IntegerProperty()
  content = db.TextProperty()
  date = db.DateTimeProperty(auto_now_add=True)
  

class Fetch(webapp.RequestHandler):
  def get(self):
    statusdb = StatusDB()
    result = urlfetch.fetch('http://blog.solrex.cn')
    statusdb.url = 'http://blog.solrex.cn'
    statusdb.status_code = result.status_code
   #statusdb.content = str(result.status_code)
    statusdb.put()

    self.response.out.write('<html><body>')

    statusdbs = db.GqlQuery("SELECT * FROM StatusDB ORDER BY date DESC LIMIT 10")

    for i in statusdbs:
      self.response.out.write('%s returns at time %s' % (i.url, i.date))
      self.response.out.write('<blockquote>%s</blockquote>' %
                              i.status_code)

application = webapp.WSGIApplication(
                                     [('/tasks/fetch', Fetch)],
                                     debug=True)

def main():
  run_wsgi_app(application)

if __name__ == "__main__":
  main()
