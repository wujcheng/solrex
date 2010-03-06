#!/usr/bin/env python
import httplib, time
from os import system

def main():
  conn = httplib.HTTPConnection('google.com')
  time.clock()
  conn.request('HEAD', '')
  t_rtt = time.clock()
  res_time = conn.getresponse().getheader('date')
  t = time.localtime(time.mktime(time.strptime(res_time,
                                 '%a, %d %b %Y %H:%M:%S %Z')) - time.timezone)
  time_str = time.strftime('%H:%M:%S', t)
  t_exe = time.clock()
  centi_sec = (t_exe - t_rtt/2)*100
  system('time ' + time_str + '.%2.0f' % centi_sec)
  date_str = time.strftime('%Y-%m-%d', t)
  system('date ' + date_str)

if __name__ == '__main__':
    main()
