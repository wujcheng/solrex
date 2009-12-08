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
    grep -q '"tableTop"'  $city.txt
    ISOLD=$?
    if [ $ISOLD -eq 0 ]; then
      grep -q " 08:00发布)" $city.txt
      # Select useful part.
      IS18=$?
      sed -i -e '1,/"tableTop"/d;/weatherYubao2/,$d;1,/风力/d;' $city.txt
    else
      grep -q " 08:00发布）" $city.txt
      # Select useful part.
      IS18=$?
      if [ $IS18 -ne 0 ]; then
        sed -i -e '1,/"dd_0"/d;/ddd_0/,$d;1,/风力/d;' $city.txt
      else
        sed -i -e '1,/ch_text/d;/未来/,$d;1,/风力/d;' $city.txt
      fi
    fi
    # Add seperate chars
    sed -i -e 's$</li><li>$</li>\n<li>$g' $city.txt
    # Remove HTML tags and empty lines.
    sed -i -e 's/<[^>]*>//g;/<!--/d' $city.txt
    sed -i -e 's/&nbsp;//g;s/&deg;C//g;s/^\s*//g;/^$/d' $city.txt
    # Cut verbose words.
    sed -i -e 's/无持续风向/不定向/g;s/℃/度/g;s/星期/周/g;s/\r//g;' $city.txt
    #continue
    # Format file content to SMS.
    LINES=(`cat $city.txt`)
    COUNT=0
    if [ ${ISOLD} -eq 0 ]; then
      if [ ${IS18} -ne 0 ]; then
        MES="${city}(18:00发布)\n"
        MES=$MES${LINES[$((COUNT++))]}:
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]},
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]},
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]}'.\n'
      else
        MES="${city}(8:00发布)\n"
      fi
        MES=$MES${LINES[$((COUNT++))]}:
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]},
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]},
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]}';'
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]},
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]},
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]}'.\n'
        MES=$MES${LINES[$((COUNT++))]}:
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]},
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]},
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]}';'
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]},
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]},
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]}'.\n'
    else
      if [ ${IS18} -ne 0 ]; then
        MES="${city}(18:00发布)\n"
        MES=$MES${LINES[$((COUNT++))]}:
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]},
        MES=$MES${LINES[$((COUNT++))]},
        MES=$MES${LINES[$((COUNT++))]}${LINES[$((COUNT++))]}'.\n'
      else
        MES="${city}(8:00发布)\n"
      fi
        MES=$MES${LINES[$((COUNT++))]}:
        MES=$MES${LINES[$((COUNT))]}${LINES[$((COUNT+2))]},
        MES=$MES${LINES[$((COUNT+4))]},
        MES=$MES${LINES[$((COUNT+6))]}${LINES[$((COUNT+8))]}';'
        MES=$MES${LINES[$((COUNT+1))]}${LINES[$((COUNT+3))]},
        MES=$MES${LINES[$((COUNT+5))]},
        MES=$MES${LINES[$((COUNT+7))]}${LINES[$((COUNT+9))]}'.\n'
        COUNT=$((COUNT+10))
        MES=$MES${LINES[$((COUNT++))]}:
        MES=$MES${LINES[$((COUNT))]}${LINES[$((COUNT+2))]},
        MES=$MES${LINES[$((COUNT+4))]},
        MES=$MES${LINES[$((COUNT+6))]}${LINES[$((COUNT+8))]}';'
        MES=$MES${LINES[$((COUNT+1))]}${LINES[$((COUNT+3))]},
        MES=$MES${LINES[$((COUNT+5))]},
        MES=$MES${LINES[$((COUNT+7))]}${LINES[$((COUNT+9))]}'.\n'
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
