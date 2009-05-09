#!/usr/bin/env python
# -*- coding: gbk -*-

import socket
import httplib, urllib
import sys
import time

# Global variable to share connection information between functions.
conn_info = []

def get_ipaddr():
  soc = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
  soc.connect(('bbs.gucas.ac.cn', 0))
  return soc.getsockname()[0]

def login(id, passwd, host):
  if len(conn_info) == 0:
    conn = httplib.HTTPConnection(host)
    conn_info.insert(0, conn)
  else:
    conn = conn_info[0]
  conn.connect()
  param = 'id=%s&passwd=%s&kick_multi=1' % (id, passwd)
  headers = {'Host': host, 'User-Agent': 'Mozilla/5.0',
             'Content-Type': 'application/x-www-form-urlencoded'}
  conn.request('POST','/bbslogin.php', param, headers)
  res = conn.getresponse()
  if res.status == 302:
    cookie = 'cookies=true; '+res.getheader('Set-Cookie').replace(',', ';')
    headers = {'Host': host, 'User-Agent': 'Mozilla/5.0',
             'Cookie': cookie, 'Content-Type':'application/x-www-form-urlencoded'}
    conn_info.insert(1, headers)
    return True
  return False

def set_status(status, ipaddr):
  conn = conn_info[0]
  headers = conn_info[1]
  param = urllib.urlencode({'text': '个人主页: http://solrex.cn --- 我的博客: http://blog.solrex.cn\n{'+status+':'+ipaddr+'}'})
  conn.request('POST','/bbsplan.php?type=1', param, headers)
  res = conn.getresponse()
  if res.status == 200:
    return True
  return False

def logout():
  conn = conn_info[0]
  headers = conn_info[1]
  headers.pop('Content-Type')
  conn.request('GET', '/bbslogout.php', None, headers)
  res = conn.getresponse()
  if res.status == 302:
    return True
  return False

def main():
  host = 'bbs.gucas.ac.cn'
  id = ''
  passwd = ''
  ipaddr = get_ipaddr()
  
  login(id, passwd, host)
  
  if len(sys.argv) == 1:
    set_status("start", ipaddr)
    time.sleep(700)
    set_status("sleep", ipaddr)
  else:
    ret = set_status(sys.argv[1], ipaddr)
    print ret
  logout()
  
if __name__ == "__main__":
  main()
