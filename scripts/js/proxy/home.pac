
function isBlockedSite(url, host)
{
	if(	dnsDomainIs(host, "2mdn.net") ||
		dnsDomainIs(host, "4sq.com") ||
		dnsDomainIs(host, "airccse.org") ||
//		dnsDomainIs(host, "amazon.com") ||
		dnsDomainIs(host, "android.com") ||
		dnsDomainIs(host, "bit.ly") ||
		dnsDomainIs(host, "blogger.com") ||
		dnsDomainIs(host, "blogspot.com") ||
		dnsDomainIs(host, "blogsearch.google.com") ||
		dnsDomainIs(host, "bullogger.com") ||
		dnsDomainIs(host, "chromium.org") ||
		dnsDomainIs(host, "depositfiles.com"))
		return true;
	if(	dnsDomainIs(host, "edgefcs.net") ||
		dnsDomainIs(host, "ebookee.com.cn") ||
		dnsDomainIs(host, "dropbox.com") ||
		dnsDomainIs(host, "facebook.com") ||
		dnsDomainIs(host, "fbcdn.net") ||
		dnsDomainIs(host, "fb.me") ||
		dnsDomainIs(host, "feedproxy.google.com") ||
		dnsDomainIs(host, "ff.im") ||
		dnsDomainIs(host, "flickr.com") ||
		dnsDomainIs(host, "foursquare.com") ||
		dnsDomainIs(host, "friendfeed.com") ||
		dnsDomainIs(host, "fusion.google.com") ||
		dnsDomainIs(host, "golang.org") ||
		dnsDomainIs(host, "groups.google.com") ||
		dnsDomainIs(host, "hecaitou.net") ||
		dnsDomainIs(host, "img.ly") ||
		dnsDomainIs(host, "mail-archive.com") ||
		dnsDomainIs(host, "mail.google.com") ||
		dnsDomainIs(host, "markmail.org"))
		return true;
	if(dnsDomainIs(host, "mitbbs.com") ||
		dnsDomainIs(host, "nlanr.net") ||
		dnsDomainIs(host, "osdir.com") ||
		dnsDomainIs(host, "opera.com") ||
		dnsDomainIs(host, "picasaweb.google.com") ||
		dnsDomainIs(host, "peacehall.com") ||
		dnsDomainIs(host, "realrumors.net") ||
		dnsDomainIs(host, "samba.org") ||
		dnsDomainIs(host, "sites.google.com") ||
		dnsDomainIs(host, "t66y.com") ||
		dnsDomainIs(host, "technorati.com"))
		return true;
	if(	dnsDomainIs(host, "torproject.org") ||
		dnsDomainIs(host, "twitter.com") ||
//		dnsDomainIs(host, "wordpress.com") ||
		dnsDomainIs(host, "xysblogs.org") ||
		dnsDomainIs(host, "yeeyan.com") ||
		dnsDomainIs(host, "ytimg.com") ||
		dnsDomainIs(host, "youtube.com") ||
		dnsDomainIs(host, "wikileaks.org") ||
		dnsDomainIs(host, "wikimedia.org") ||
		dnsDomainIs(host, "www.google.com") ||
		dnsDomainIs(host, "zh.wikipedia.org"))
		return true;
	return false;
}

function isBlockedIP(addr)
{
	return false;
}

function FindProxyForURL(url, host)
{
	var NO_Proxy	 = 'DIRECT';
	var GFW_Proxy	= 'SOCKS 127.0.0.1:9090';

	if(isBlockedSite(url, host)) {
		return GFW_Proxy;
	}

	if(!isResolvable(host)) {
		return NO_Proxy;
	}

	var IpAddr = dnsResolve(host);

	if(isBlockedIP(IpAddr)) {
		return GFW_Proxy;
	}
	return NO_Proxy;
}
