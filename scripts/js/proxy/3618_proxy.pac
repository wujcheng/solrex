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
  if(
      dnsDomainIs(host, "2mdn.net") ||
      dnsDomainIs(host, "amazon.com") ||
      dnsDomainIs(host, "android.com") ||
      dnsDomainIs(host, "blogger.com") ||
      dnsDomainIs(host, "blogspot.com") ||
      dnsDomainIs(host, "blogsearch.google.com") ||
      dnsDomainIs(host, "depositfiles.com") ||
      dnsDomainIs(host, "edgefcs.net") ||
      dnsDomainIs(host, "ebookee.com.cn") ||
      dnsDomainIs(host, "facebook.com") ||
      dnsDomainIs(host, "fbcdn.net") ||
      dnsDomainIs(host, "ff.im") ||
      dnsDomainIs(host, "flickr.com") ||
      dnsDomainIs(host, "friendfeed.com") ||
      dnsDomainIs(host, "ggpht.com") ||
      dnsDomainIs(host, "mail-archive.com") ||
      dnsDomainIs(host, "markmail.org") ||
      dnsDomainIs(host, "nlanr.net") ||
      dnsDomainIs(host, "osdir.com") ||
      dnsDomainIs(host, "picasaweb.google.com") ||
      dnsDomainIs(host, "realrumors.net") ||
      dnsDomainIs(host, "samba.org") ||
      dnsDomainIs(host, "technorati.com") ||
      dnsDomainIs(host, "torproject.org") ||
      dnsDomainIs(host, "twitter.com") ||
      dnsDomainIs(host, "wordpress.com") ||
      dnsDomainIs(host, "yeeyan.com") ||
      dnsDomainIs(host, "ytimg.com") ||
      dnsDomainIs(host, "youtube.com") ||
      dnsDomainIs(host, "zh.wikipedia.org")
    )
    return true;
  else
    return false;
}

function isBlockedURL(url, host)
{
  if( dnsDomainIs(host, "www.google.com") ) {
    if (
         shExpMatch(url, "*android.com*") ||
         shExpMatch(url, "*blogger.com*") ||
         shExpMatch(url, "*blogspot.com*") ||
         shExpMatch(url, "*friendfeed.com*") ||
         shExpMatch(url, "*flickr.com*") ||
         shExpMatch(url, "*mail-archive.com*") ||
         shExpMatch(url, "*markmail.com*") ||
         shExpMatch(url, "*osdir.com*") ||
         shExpMatch(url, "*samba.org*") ||
         shExpMatch(url, "*security*") ||
         shExpMatch(url, "*technorati.com*") ||
         shExpMatch(url, "*wordpress.com*") ||
         shExpMatch(url, "*youtube.com*") ||
         shExpMatch(url, "*zh.wikipedia.org*")
      )
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
  var tohrProxy   = "PROXY localhost:9090";

  if(isFreeHost(host) || isLocalHost(host)) {
    return direct;
  } else if(isBlockedURL(url, host) || isBlockedHost(host)) {
    return tohrProxy;
  }

  if(!isResolvable(host)) {
    return hHttpProxy;
  }

  var IpAddr = dnsResolve(host);

  if(isFreeIP(IpAddr) || isLocalIP(IpAddr) || isIPV6(IpAddr)) {
    return direct;
  } else if(isBlockedIP(IpAddr)) {
    return tohrProxy;
  } else {
    return hHttpProxy;
  }
}

