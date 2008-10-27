#!/usr/bin/env python
# -*- encoding: utf-8 -*-
import os
import urllib

city_list = ['南京','北京']
send_to_self_cities = ['南京','北京']

class weather:
    urlbase = "http://php.weather.sina.com.cn/search.php?city="
    def __init__(self):
        pass

    def clear_html(self):
        for city in city_list:
            cmd = "rm -f %s.htm" % (city)
            os.popen(cmd)

    def get_html(self):
        for city in city_list:
            url = self.urlbase + urllib.quote(city.decode('utf8').encode('gbk'))
            cmd = "wget -O %s.htm %s" % (city, url)
            os.popen(cmd)
        return 0

    def parse_html(self):
        for city in city_list:
            filename = city + ".htm"
            # Select specific part.
            cmd = "sed -i -e '/SideBar/,500d;1,/Weather3DBlk/d;' %s" % (filename)
            os.popen(cmd)
            # Remove html tag
            cmd = "sed -i -e 's/<[^>]*>//g;/<!--/d' %s" % (filename)
            os.popen(cmd)
            # Remove empty line, including &nbsp, &deg; from html
            cmd = "sed -i -e 's/&nbsp;//g;s/&deg;C//g;s/^\s*//g;/^$/d' %s" % (filename)
            os.popen(cmd)
            # Change file encoding so we can edit the Chinese content of the file
            # Why don't sina just use utf8... damn!
            cmd = "iconv -f gb2312 -t utf8 %s > tempfile; mv tempfile %s" % (filename,filename)
            os.popen(cmd)
            # Remove unwanted Chinese characters: remove ℃ and change ～ to -, and remove the details of the weather
            cmd = "sed -i -e 's/℃//g;s/～/-/g;s/：//g;s/≤/小于/g;/\t\t/d' %s" % (filename)
            os.popen(cmd)
            # Changing content of the file
            cmd = "sed -i -e 's/星期/周/g;s/..年//g;s/..月//g;s/日-..日/日/g;/%s/d;s/指数//g' %s" % (city,filename)
            os.popen(cmd)

    def generate_msg(self,city):
        msg = ''
        filename = city + ".htm"
        fp = open(filename)
        msg += city + '天气'
        fp.readline(); fp.readline(); fp.readline();
        for i in [1, 2]:
          line = fp.readline().replace('\r\n','')
          msg += '\n' + line + ':'
          line = fp.readline().replace(' ', ',').replace('\r\n','')
          msg = msg + line + '度,'
          line = fp.readline().replace(' ', '').replace('\r\n','')
          msg = msg + line
        fp.close()
        return msg

if __name__=="__main__":
    start=weather()
    start.clear_html()
    start.get_html()
    start.parse_html()
    message = ""

    # Send the weather report to myself
    for city in send_to_self_cities:
        message = start.generate_msg(city) + '\n'
        print message
        cmd = 'sendsms -f PHONENUM -p PASSWORD "%s"' % (message)
        os.system(cmd)
        cmd = 'sendsms -f PHONENUM -p PASSWORD -t FETIONNUM "%s"' % (message)
        os.system(cmd)
    start.clear_html()
