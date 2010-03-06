#!/usr/bin/env python

import httplib, time
from os import system

def main():
  compensation = 0.5
  conn = httplib.HTTPConnection("google.com")
  time.clock()
  conn.request("HEAD", "")
  t_rt = time.clock()
  res = conn.getresponse()
  server_datetime = res.getheader("date")
  t = time.localtime(time.mktime(time.strptime(server_datetime, "%a, %d %b %Y %H:%M:%S %Z")) - time.timezone)
  time_str = time.strftime("%H:%M:%S", t)
  t_handle = time.clock()
  t_handle = (t_handle - t_rt/2)*100
  system('time ' + time_str + '.%2.0f' % t_handle)
  date_str = time.strftime("%Y-%m-%d", t)
  system('date ' + date_str)

if __name__ == "__main__":
    main()
