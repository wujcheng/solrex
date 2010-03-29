#!/bin/bash

COMMAND="stop"
SERVER="0.0.0.0"
USRNAME=""
PORT="22"

INFOURL="http://example.com"
INFOURLBAK="http://example.org"

SSHOPTS="-C -f -N -g -o PreferredAuthentications=publickey -o StrictHostKeyChecking=no"

get_server_info()
{
  #info=`grep "{*}" | sed -e "s/.*{//;s/}.*//;"`
  info=`wget --timeout=2 --tries=3 -nv -O - $INFOURL 2> /dev/null |
        iconv -f gbk -t utf8 | sed -n "/{*}/s/.*{\(.*\)}.*/\1/p"`
  if [ -z $info ]; then
    info=`wget --timeout=5 --tries=3 -nv -O - $INFOURLBAK 2> /dev/null |
        iconv -f gbk -t utf8 | sed -n "/{*}/s/.*{\(.*\)}.*/\1/p"`
    if [ -z $info ]; then
      echo "INFO server error."
      exit 1
    fi
  fi
  COMMAND=${info%%:*}
  SERVER=${info#*:}
}

tunneling_status()
{
  tun_ps=`ps aux | grep "ssh -C" | wc -l`
  if [ $tun_ps -gt 1 ]; then
    echo -n "running"
  else
    echo -n "died"
  fi
}

start_tunneling()
{
  ps -e | grep -q antinat
  if [ $? -ne 0 ]; then
    /usr/local/bin/antinat &
  fi
  ssh $SSHOPTS -R 1080:127.0.0.1:1080 ${USRNAME}@${SERVER} -p $PORT
  ssh $SSHOPTS -R 3128:127.0.0.1:3128 ${USRNAME}@${SERVER} -p $PORT
  ssh $SSHOPTS -R 22:127.0.0.1:22 ${USRNAME}@${SERVER} -p $PORT
}

failsafe_tunneling()
{
  ssh $SSHOPTS -R 1022:127.0.0.1:22 ${USRNAME}@${SERVER} -p $PORT
}

stop_tunneling()
{
  killall -e ssh
  #vncserver -kill :51
}

echo -n "[`date +%F\ %R`] "
get_server_info
echo -n "$SERVER "

if [ $# -ne 0 ]; then
  COMMAND=$1
fi

case "$COMMAND" in
  sleep | sleeps)
    echo "Sleeping."
    exit 0
    ;;
  start | starts)
    if [ $(tunneling_status) = "running" ]; then
      echo "Starting ssh tunneling.(started, do nothing)"
    else
      echo "Starting ssh tunneling."
      start_tunneling
    fi
    ;;
  restart | restarts)
    if [ $(tunneling_status) = "running" ]; then
      echo "Restarting ssh tunneling."
      stop_tunneling
      start_tunneling
    else
      echo "Restarting ssh tunneling."
      start_tunneling
    fi
    ;;
  stop | stops)
    if [ $(tunneling_status) = "running" ]; then
      echo "Stoping ssh tunneling."
      stop_tunneling
    else
      echo "Stoping ssh tunneling.(stoped, do nothing)"
    fi
    ;;
  failsafe | failsafes)
    echo "Starting ssh tunneling.(failsafe mode)"
    failsafe_tunneling
    ;;
  starth | stoph | restarth | sleeph | failsafeh)
    echo "Not my business, sleeping."
    ;;
  gfw)
    ssh $SSHOPTS -R 9090:127.0.0.1:9090 ${USRNAME}@${SERVER} -p $PORT
    fuckgfw
    ;;
  vnc)
    ssh $SSHOPTS -R 5951:127.0.0.1:5951 ${USRNAME}@${SERVER} -p $PORT
    vncserver :51
    ;;
  svn)
    ssh $SSHOPTS -R 3690:127.0.0.1:3690 ${USRNAME}@${SERVER} -p $PORT
    ;;
  *)
    echo "Unrecogenized server command ($COMMAND)."
    exit 1
    ;;
esac

exit 0
