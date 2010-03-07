#!/usr/bin/env python
# ntpdate.py - set the date and time via NTP
# An IPv6 enabled ntp client, for Windows ONLY.

import ntplib, time
from os import system
from sys import argv

def usage():
  print '''Usage: ntpdate.py  [-qh] server
Example:
  ntpdate.py 210.72.145.44      # IPv4
  ntpdate.py ntp6.remco.org     # IPv6
Options:

  -q     Query only - don't set the clock.
  -h     Print this message.

IPv6 NTP Server List:
  ntp6.remco.org
  ntp6.space.net
  time.buptnet.edu.cn
  time.join.uni-muenster.de
  time6.ipv6.uni-muenster.de
  ntp.sixxs.net
  ntp.eu.sixxs.net
  ntp.us.sixxs.net
  ntp.ap.sixxs.net
  ntp.rhrk.uni-kl.de
  ntp.ipv6.uni-leipzig.de

Report bugs to http://solrex.org.'''
  sys.exit()

def main():
  ntp_svr = ''
  query = False

  for a in argv[1:]:
    if a == '-q':
      query = True
    elif a == '-h':
      usage()
    else:
      ntp_svr = a
  if ntp_svr == '':
    usage()

  c = ntplib.NTPClient()
  res = c.request(ntp_svr, version=3)
  t_epoch = res.offset + res.delay + time.time()
  t = time.localtime(t_epoch)
  centi_sec = t_epoch%1 * 100
  time_str = time.strftime('%H:%M:%S', t)
  if not query:
    system('time %s.%2.0f' % (time_str, centi_sec))
    date_str = time.strftime('%Y-%m-%d', t)
    system('date %s' % date_str)
  if query:
    print 'server %s, stratum %d, offset %f, delay %f' % (
           ntp_svr, res.stratum, res.offset, res.delay)
  print '%s %s ntpdate.py: time server %s offset %f sec' % (
         time.strftime('%d %b', t), time_str, ntp_svr, res.offset)

if __name__ == '__main__':
  main()
