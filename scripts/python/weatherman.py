#!/usr/bin/env python
# -*- encoding: utf-8 -*-
import os
import urllib

city_list = {
    '南京': '101190101',
    '北京': '101010100',
    '郑州': '101180101',
}
my_cities = ['南京','北京']
weather_users = {
    'yyyyyyyy': '郑州',
}

class weather:
    urlbase = "http://www.weather.com.cn/html/weather/"
    def __init__(self):
        pass

    def clear_html(self):
        for city in city_list.keys():
            cmd = "rm -f %s.htm" % (city)
            os.popen(cmd)

    def get_html(self):
        for city in city_list.keys():
            url = self.urlbase + city_list[city] + '.shtml'
            cmd = "wget -O %s.htm %s" % (city, url)
            os.popen(cmd)
        return 0

    def parse_html(self):
        for city in city_list:
            filename = city + ".htm"
            # Select specific part.
            cmd = "sed -i -e '/surf/,1000d;1,/dd_0/d;' %s" % (filename)
            os.popen(cmd)
            # Remove html tag
            cmd = "sed -i -e 's/<[^>]*>//g;/<!--/d' %s" % (filename)
            os.popen(cmd)
            # Remove empty line, including &nbsp, &deg; from html
            cmd = "sed -i -e 's/&nbsp;//g;s/&deg;C//g;s/^\s*//g;/^$/d' %s" % (filename)
            os.popen(cmd)
            # Remove unwanted Chinese characters: remove ℃ and change ～ to -, and remove the details of the weather
            cmd = "sed -i -e 's/℃//g;s/高温//g;s/低温//g;s/：//g;/\t\t/d' %s" % (filename)
            os.popen(cmd)
            # Changing content of the file
            cmd = "sed -i -e 's/星期/周/g;/%s/d;s/指数//g' %s" % (city,filename)
            os.popen(cmd)

    def generate_msg(self,city):
        msg = ''
        filename = city + ".htm"
        fp = open(filename)
        msg += city + '天气'
        for i in [1, 2]:
          msg += '\n' + fp.readline().replace('\r\n', ':')
          msg += fp.readline().replace('\r\n', ',')
          hi_temp = fp.readline().replace('\r\n', '')
          lo_temp = fp.readline().replace('\r\n', '')
          msg += lo_temp + '-' + hi_temp + '度,'
          msg += fp.readline().replace('\r\n', '')
        fp.close()
        return msg

if __name__=="__main__":
    start = weather()
    start.clear_html()
    start.get_html()
    start.parse_html()
    message = ""

    # Send the weather report to myself
    for city in my_cities:
        message = start.generate_msg(city)
        print message
        cmd = 'sendsms -f 13xxxxxxxxx -p ******** "%s"' % (message)
        os.system(cmd)
        cmd = 'sendsms -f 13xxxxxxxxx -p ******** -t yyyyyyyyy "%s"' % (message)
        os.system(cmd)

    for user in weather_users.keys():
        message = start.generate_msg(weather_users[user])
        print message
        cmd = 'sendsms -f 13xxxxxxxxx -p ******** -t %s "%s"' % (user, message)
        os.system(cmd)
    start.clear_html()
