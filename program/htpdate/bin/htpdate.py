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
  local_time = time.asctime()
  t_exe = time.clock()
  centi_sec = (t_exe - t_rtt/2)*100
  if centi_sec > 99:
    centi_sec = 99
  system('time %s.%2.0f' % (time_str, centi_sec))
  date_str = time.strftime('%Y-%m-%d', t)
  system('date %s' % date_str)
  print 'LOCAL  TIME: ' + local_time
  print 'SERVER TIME: ' + time.asctime(t)
  print 'LOCAL  TIME: ' + time.asctime()
  if (t_exe - t_rtt/2) >= 1:
    print 'Round trip time is too long. Time error might be larger than 1 sec.'

if __name__ == '__main__':
  main()
