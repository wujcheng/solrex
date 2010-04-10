
  if(
      dnsDomainIs(host, "gucas.ac.cn") ||
      dnsDomainIs(host, "acm.org") 
    )
    return true;
  return false;
}

function isLibSite(url, host)
{
  if( dnsDomainIs(host, "ieee.org") ||
      dnsDomainIs(host, "159.226.100.24") )
    return true;
  return false;
}

function isBlockedSite(url, host)
{
  if(
      dnsDomainIs(host, "2mdn.net") ||
      dnsDomainIs(host, "airccse.org") ||
      dnsDomainIs(host, "amazon.com") ||
      dnsDomainIs(host, "android.com") ||
      dnsDomainIs(host, "bit.ly") ||
      dnsDomainIs(host, "blogger.com") ||
      dnsDomainIs(host, "blogspot.com") ||
      dnsDomainIs(host, "blogsearch.google.com") ||
      dnsDomainIs(host, "bullogger.com") ||
      dnsDomainIs(host, "chromium.org") ||
      dnsDomainIs(host, "depositfiles.com") ||
      dnsDomainIs(host, "edgefcs.net") ||
      dnsDomainIs(host, "ebookee.com.cn") ||
      dnsDomainIs(host, "facebook.com") ||
      dnsDomainIs(host, "fbcdn.net") ||
      dnsDomainIs(host, "ff.im") ||
      dnsDomainIs(host, "friendfeed.com") ||
      dnsDomainIs(host, "golang.org") ||
      dnsDomainIs(host, "hecaitou.net") ||
      dnsDomainIs(host, "mail-archive.com") ||
      dnsDomainIs(host, "markmail.org") ||
      dnsDomainIs(host, "mitbbs.com") ||
      dnsDomainIs(host, "nlanr.net") ||
      dnsDomainIs(host, "osdir.com") ||
      dnsDomainIs(host, "opera.com") ||
      dnsDomainIs(host, "picasaweb.google.com") ||
      dnsDomainIs(host, "peacehall.com") ||
      dnsDomainIs(host, "realrumors.net") ||
      dnsDomainIs(host, "samba.org") ||
      dnsDomainIs(host, "t66y.com") ||
      dnsDomainIs(host, "technorati.com") ||
      dnsDomainIs(host, "torproject.org") ||
      dnsDomainIs(host, "twitter.com") ||
      dnsDomainIs(host, "wordpress.com") ||
      dnsDomainIs(host, "xysblogs.org") ||
      dnsDomainIs(host, "yeeyan.com") ||
      dnsDomainIs(host, "ytimg.com") ||
      dnsDomainIs(host, "youtube.com") ||
      dnsDomainIs(host, "zh.wikipedia.org")
    )
    return true;
  return false;
}

function isLocalIP(addr)
{
  if( isInNet(addr,"127.0.0.0","255.0.0.0") ||
      isInNet(addr,"10.0.0.0","255.0.0.0") ||
      isInNet(addr,"192.168.0.0","255.255.0.0") ||
      isInNet(addr,"172.16.0.0","255.255.0.0") )
    return true;
  return false;
}

function isFreeIP(addr)
{
  if( isInNet(addr,"210.77.23.0","255.255.248.0") )
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
  return false;
}

function FindProxyForURL(url, host)
{
  var NO_Proxy   = 'DIRECT';
  var HTTP_Proxy = 'PROXY localhost:11110';
  var GFW_Proxy  = 'SOCKS5 localhost:9090';
  var LIB_Proxy  = 'PROXY 159.226.100.43:8918';
  LIB_Proxy = HTTP_Proxy;

  if(isFreeSite(url, host) || isLocalSite(url, host)) {
    return NO_Proxy;
  } else if(isBlockedSite(url, host)) {
    return GFW_Proxy;
  } else if (isLibSite(url, host)) {
    return LIB_Proxy;
  }

  if(!isResolvable(host)) {
    return HTTP_Proxy;
  }

  var IpAddr = dnsResolve(host);

  if(isFreeIP(IpAddr) || isLocalIP(IpAddr) || isIPV6(IpAddr)) {
    return NO_Proxy;
  } else if(isBlockedIP(IpAddr)) {
    return GFW_Proxy;
  } else {
    return HTTP_Proxy;
  }
}

