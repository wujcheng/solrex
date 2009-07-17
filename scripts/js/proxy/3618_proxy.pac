function isLocalHost(host)
{
  if( dnsDomainIs(host, "localhost") )
    return true;
  else
    return false;
}

function isFreeHost(host)
{
  if ( dnsDomainIs(host, "gucas.ac.cn") )
    return true;
  return false;
}

function isBlockedHost(host)
{
  if( dnsDomainIs(host, "zh.wikipedia.org") ||
      dnsDomainIs(host, "blogspot.com") ||
      dnsDomainIs(host, "wordpress.com") ||
      dnsDomainIs(host, "android.com") ||
      dnsDomainIs(host, "youtube.com") ||
      dnsDomainIs(host, "mail-archive.com") ||
      dnsDomainIs(host, "osdir.com") ||
      dnsDomainIs(host, "markmail.org") ||
      dnsDomainIs(host, "blogger.com") ||
      dnsDomainIs(host, "technorati.com") ||
      dnsDomainIs(host, "twitter.com") ||
      dnsDomainIs(host, "amazon.com") ||
      dnsDomainIs(host, "edgefcs.net") ||
      dnsDomainIs(host, "realrumors.net") ||
      dnsDomainIs(host, "facebook.com") ||
      dnsDomainIs(host, "fbcdn.net") ||
      dnsDomainIs(host, "friendfeed.com") ||
      dnsDomainIs(host, "picasaweb.google.com") ||
      dnsDomainIs(host, "ggpht.com") ||
      dnsDomainIs(host, "flickr.com") )
    return true;
  else
    return false;
}

function isBlockedURL(url, host)
{
  if( dnsDomainIs(host, "google.com") ) {
    if ( shExpMatch(url, "*zh.wikipedia.org*") ||
         shExpMatch(url, "*blogspot.com*") ||
         shExpMatch(url, "*wordpress.com*") ||
         shExpMatch(url, "*android.com*") ||
         shExpMatch(url, "*youtube.com*") ||
         shExpMatch(url, "*mail-archive.com*") ||
         shExpMatch(url, "*osdir.com*") ||
         shExpMatch(url, "*markmail.com*") ||
         shExpMatch(url, "*blogger.com*") ||
         shExpMatch(url, "*technorati.com*") ||
         shExpMatch(url, "*friendfeed.com*") ||
         shExpMatch(url, "*flickr.com*") )
      return true;
  }
  return false;
}

function isLocalIP(addr)
{
  if( isInNet(addr,"127.0.0.0","255.0.0.0") ||
      isInNet(addr,"10.0.0.0","255.0.0.0") ||
      isInNet(addr,"192.168.0.0","255.255.0.0") ||
      isInNet(addr,"172.16.0.0","255.255.0.0") )
    return true;
  else
    return false;
}

function isFreeIP(addr)
{
  if( isInNet(addr,"210.77.23.0","255.255.255.0") )
    return true;
  return false;
}

function isBlockedIP(addr)
{
  return false;
}

function isIPV6(addr)
{
  if( shExpMatch(addr, "*:*") )
    return true;
  else
    return false;
}

function FindProxyForURL(url, host)
{
  var direct      = "DIRECT";
  var hHttpProxy  = "PROXY localhost:4080";
  var gaeProxy    = "PROXY localhost:8000";

  if(isFreeHost(host) || isLocalHost(host)) {
    return direct;
  } else if(isBlockedURL(url, host) || isBlockedHost(host)) {
    return gaeProxy;
  }

  if(!isResolvable(host)) {
    return hHttpProxy;
  }

  var IpAddr = dnsResolve(host);

  if(isFreeIP(IpAddr) || isLocalIP(IpAddr) || isIPV6(IpAddr)) {
    return direct;
  } else if(isBlockedIP(IpAddr)) {
    return gaeProxy;
  } else {
    return hHttpProxy;
  }
}

