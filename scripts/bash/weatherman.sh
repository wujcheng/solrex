#!/bin/bash
CITY_LIST=("南京" "北京" "郑州")
URL_LIST=("101190101" "101010100" "101180101")
URLBASE="http://www.weather.com.cn/html/weather/"

MY_CITIES=("南京" "北京")
SMS_USER=("yyyyyyyyy")
SMS_CITY=("郑州")

get_html()
{
  i=0
  for city in ${CITY_LIST[*]}; do
    url=$URLBASE${URL_LIST[i]}.shtml
    #wget -e "http_proxy=http://user:passwd@192.168.0.1:8080" -O $city.txt $url
    wget -O $city.txt $url
    i=$(($i+1))
  done
}

parse_html()
{
  for city in ${CITY_LIST[*]}; do
    grep -q "dd_0" $city.txt
    # Select useful part.
    if [ $? -ne 0 ]; then
      sed -i -e '1,/c_1_1/d;/c_1_2/,$d;' $city.txt
    else
      sed -i -e '1,/dd_0/d;/surf/,$d;' $city.txt
    fi
    # Remove HTML tags.
    sed -i -e 's/<[^>]*>//g;/<!--/d' $city.txt
    # Remove empty lines.
    sed -i -e 's/&nbsp;//g;s/&deg;C//g;s/^\s*//g;/^$/d' $city.txt
    sed -i -e '11,$d;' $city.txt
    # Cut verbose words.
    sed -i -e 's/℃//g;s/高温//g;s/低温//g;s/：//g;s/指数//g;' $city.txt
    sed -i -e 's/星期/周/g;'  $city.txt
    # Format file content to SMS.
    MES="${city}天气\n"
    MES=$MES`sed -n -e '1p' $city.txt | tr -d '\r\n'`:
    MES=$MES`sed -n -e '2p' $city.txt | tr -d '\r\n'`,
    MES=$MES`sed -n -e '4p' $city.txt | tr -d '\r\n'`到
    MES=$MES`sed -n -e '3p' $city.txt | tr -d '\r\n'`度,
    MES=$MES`sed -n -e '5p' $city.txt | tr -d '\r\n'`'\n'
    MES=$MES`sed -n -e '6p' $city.txt | tr -d '\r\n'`:
    MES=$MES`sed -n -e '7p' $city.txt | tr -d '\r\n'`,
    MES=$MES`sed -n -e '9p' $city.txt | tr -d '\r\n'`到
    MES=$MES`sed -n -e '8p' $city.txt | tr -d '\r\n'`度,
    MES=$MES`sed -n -e '10p' $city.txt| tr -d '\r\n'`
    echo -ne $MES >> $city.txt
    sed -i -e '1,10d' $city.txt
  done
}

send_forcast()
{
  for city in ${MY_CITIES[*]}; do
    sendsms -v -f 13xxxxxxxxx -p ******** "`cat $city.txt`"
    sleep 1
  done
  i=0
  for user in ${SMS_USER[*]}; do
    sendsms -v -f 13xxxxxxxxx -p ******** -t ${SMS_USER[$i]} "`cat ${SMS_CITY[$i]}.txt`"
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
clear_html
