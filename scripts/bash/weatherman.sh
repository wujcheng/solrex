#!/bin/bash
# This script fetch user specified citys' weather forecast from
# http://weather.com.cn, and send them using a CLI SMS sender "sendsms"
# which you can get from http://share.solrex.cn/dcount/click.php?id=5.
#
# You can look for new or bug fix version 
# @ http://share.solrex.cn/scripts/weatherman.sh.
# Copyright (C) Solrex Yang <http://solrex.cn> with GPL license.
#
# Usage: You should add it to crontab by "crontab -e", and then add a line
# such as:
# 00 20 * * * /usr/bin/weatherman.sh >> ~/bin/log/weatherman.log 2>&1
# which will send weather forecast to your fetion friends at every 8pm.


CITY_LIST=("南京" "北京" "郑州")
URL_LIST=("101190101" "101010100" "101180101")

SMS_USER=("135xxxxxxxx" "136xxxxxxxx,137xxxxxxxx")
SMS_CITY=("郑州" "北京")

URLBASE="http://www.weather.com.cn/html/weather/"

get_html()
{
  i=0
  for city in ${CITY_LIST[*]}; do
    url=$URLBASE${URL_LIST[i]}.shtml
    #wget -e "http_proxy=http://user:passwd@192.168.0.1:8080" -O $city.txt $url
    wget -nv -O $city.txt $url 2> /dev/null
    i=$(($i+1))
  done
}

parse_html()
{
  for city in ${CITY_LIST[*]}; do
    grep -q "18:00" $city.txt
    # Select useful part.
    NOT18=$?
    if [ $NOT18 -eq 0 ]; then
      sed -i -e '1,/c_1_1/d;/surf/,$d;' $city.txt
      sed -i -e '/dl class="right"/,/dd_0/d;' $city.txt
    else
      sed -i -e '1,/c_1_1/d;/box_hist/,$d;' $city.txt
      sed -i -e '/dl class="right"/,/c_1_2/d;s/<br \/>.*<\/dd>//g;' $city.txt
    fi
    # Remove HTML tags.
    sed -i -e 's/<[^>]*>//g;/<!--/d' $city.txt
    # Remove empty lines.
    sed -i -e 's/&nbsp;//g;s/&deg;C//g;s/^\s*//g;/^$/d' $city.txt
    sed -i -e '14,$d;' $city.txt
    # Cut verbose words.
    sed -i -e 's/℃//g;s/高温：//g;s/低温：//g;s/指数//g;' $city.txt
    sed -i -e 's/星期/周/g;s@/@\n@g;s/[ \t\r]*//g;s/：/:/g;'  $city.txt
    # Format file content to SMS.
    LANG=zh_CN.UTF-8
    if [ ${NOT18} -eq 0 ]; then
      MES="${city}(18:00发布)\n"
      MES=$MES`date -d tomorrow +%-d`日周`date -d tomorrow +%a`:
      MES=$MES`sed -n -e '1p' $city.txt`,
      MES=$MES`sed -n -e '2p' $city.txt`到`sed -n -e '3p' $city.txt`度,
      MES=$MES`sed -n -e '4p' $city.txt`'\n'
      MES=$MES`sed -n -e '5p' $city.txt`:`sed -n -e '6p' $city.txt`,
      MES=$MES`sed -n -e '8p' $city.txt`到`sed -n -e '7p' $city.txt`度,
      MES=$MES`sed -n -e '9p' $city.txt`'\n'
      MES=$MES`sed -n -e '10p' $city.txt`:`sed -n -e '11p' $city.txt`,
      MES=$MES`sed -n -e '13p' $city.txt`到`sed -n -e '12p' $city.txt`度,
      MES=$MES`sed -n -e '14p' $city.txt`
    else
      MES="${city}(8:00发布)\n"
      MES=$MES今天白天:
      MES=$MES`sed -n -e '1p' $city.txt`,
      MES=$MES`sed -n -e '2p' $city.txt`到`sed -n -e '3p' $city.txt`度,
      MES=$MES`sed -n -e '4p' $city.txt`'\n'
      MES=$MES`sed -n -e '5p' $city.txt`'\n'
      MES=$MES`sed -n -e '6p' $city.txt`'\n'
      MES=$MES`sed -n -e '7p' $city.txt`'\n'
      MES=$MES`sed -n -e '8p' $city.txt`'\n'
      MES=$MES`sed -n -e '10p' $city.txt`'\n'
      MES=$MES`sed -n -e '11p' $city.txt`
    fi
    echo -ne $MES > $city.txt
  done
}

send_forcast()
{
  i=0
  for user in ${SMS_USER[*]}; do
    sendsms -vlf 13xxxxxxxxx -p **** -t ${SMS_USER[$i]} < ${SMS_CITY[$i]}.txt
    sleep 1
    i=$(($i+1))
  done
}

clear_html()
{
  for city in ${CITY_LIST[*]}; do
    rm -f $city.txt
  done
}

get_html
parse_html
send_forcast
#clear_html
