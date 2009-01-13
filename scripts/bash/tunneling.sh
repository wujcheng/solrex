#!/bin/bash
# {start|stop|restart:username:ipaddress}

COMMAND="stop"
SERVER="0.0.0.0"
USRNAME=""
PORT="22"

INFOURL="http://someserver.com/somepage"

SSHOPTS="-C -f -N -g -o PreferredAuthentications=publickey -o StrictHostKeyChecking=no"

get_server_info()
{
  #info=`wget -nv -O - $INFOURL 2> /dev/null | iconv -f gbk -t utf8 |
        grep -o -e "{.*}" | tr -d '{}'`
  info=`wget -nv -O - $INFOURL 2> /dev/null | iconv -f gbk -t utf8 |
        sed -n "/{*}/s/.*{\(.*\)}.*/\1/p"`
  COMMAND=${info%%:*}
  SERVER=${info#*:}
  USRNAME=${SERVER%:*}
  SERVER=${SERVER#*:}
}

tunneling_status()
{
  tun_ps=`ps aux | grep "ssh -C" | wc -l`
  if [ $tun_ps -gt 4 ]; then
    echo -n "running"
  else
    echo -n "died"
  fi
}

start_tunneling()
{
  ssh $SSHOPTS -R 3128:127.0.0.1:3128 ${USRNAME}@${SERVER} -p $PORT
  ssh $SSHOPTS -R 8022:127.0.0.1:22 ${USRNAME}@${SERVER} -p $PORT
}

failsafe_tunneling()
{
  ssh $SSHOPTS -R 8022:127.0.0.1:22 ${USRNAME}@${SERVER} -p $PORT
}

stop_tunneling()
{
  killall -e ssh
}

echo -n "[`date +%F\ %R`] "
get_server_info

case "$COMMAND" in
  start)
    if [ $(tunneling_status) = "running" ]; then
      echo "Starting ssh tunneling.(started, do nothing)"
    else
      echo "Starting ssh tunneling."
      start_tunneling
    fi
    ;;
  restart)
    if [ $(tunneling_status) = "running" ]; then
      echo "Restarting ssh tunneling."
      stop_tunneling
      start_tunneling
    else
      echo "Restarting ssh tunneling."
      start_tunneling
    fi
    ;;
  stop)
    if [ $(tunneling_status) = "running" ]; then
      echo "Stoping ssh tunneling."
      stop_tunneling
    else
      echo "Stoping ssh tunneling.(stoped, do nothing)"
    fi
    ;;
  failsafe)
    echo "Starging ssh tunneling.(failsafe mode)"
    failsafe_tunneling
    ;;
  sleep)
    echo "Sleeping."
    exit 0
    ;;
  *)
    echo "Unrecogenized server command ($COMMAND)."
    exit 1
    ;;
esac

exit 0
