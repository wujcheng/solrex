import cgi

from google.appengine.api import users
from google.appengine.api import urlfetch
from google.appengine.ext import webapp
from google.appengine.ext.webapp.util import run_wsgi_app
from google.appengine.ext import db

class Greeting(db.Model):
  author = db.UserProperty()
  content = db.TextProperty()
  date = db.DateTimeProperty(auto_now_add=True)
  

class MainPage(webapp.RequestHandler):
  def get(self):
    greeting = Greeting()
    result = urlfetch.fetch('http://blog.solrex.cn')

   # if result.status_code == 200:
   #   greeting.content = db.Text(result.content, encoding="utf8")
   # else:
    greeting.content = str(result.status_code)
    greeting.put()

    self.response.out.write('<html><body>')

    greetings = db.GqlQuery("SELECT * FROM Greeting ORDER BY date DESC LIMIT 10")

    for greeting in greetings:
      if greeting.author:
        self.response.out.write('<b>%s</b> wrote:' % greeting.author.nickname())
      else:
        self.response.out.write('An anonymous person wrote:')
      self.response.out.write('<blockquote>%s</blockquote>' %
                              greeting.content)

application = webapp.WSGIApplication(
                                     [('/tasks/fetch', MainPage)],
                                     debug=True)

def main():
  run_wsgi_app(application)

if __name__ == "__main__":
  main()
