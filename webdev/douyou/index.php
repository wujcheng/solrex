<!DOCTYPE html PUBLIC"-//W3C//DTD XHTML 1.0 Transitional//EN" "http：//www.w3.org/TR/xhtml1/DTD/xhtml-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>豆瓣好友统计图标</title>
<meta name="description" content="豆瓣好友图标利用豆瓣API制作您的个性化朋友统计图标。" />
<meta name="keywords" content="豆瓣, 好友, 统计, 图标">
<link href="/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="nav">
<a href="http://solrex.cn">我的主页</a>
<a href="http://blog.solrex.cn">我的博客</a>
</div>
<br clear="all" />
<div id="header">
<h1>豆瓣好友统计图标</h1>
</div>
<div id="content">
<h3>介绍</h3>
<p>呃——我觉得写主页比写主程序还费劲。简单来说，这个东西就跟 Feedburner 的订阅数统计图标类似，利用<a href="http://douban.com">豆瓣</a>提供的 API 抓取你的豆瓣好友数量，并做成一个小图片出来让你可以放在自己的博客上秀一秀。比如下面就是我的豆瓣好友统计图标：</p>
<p><a href="http://www.douban.com/people/Solrex/" title="豆瓣好友统计"><img src="http://solrex.cn/douyou/dc/Solrex" style="border: 0pt none ;" alt="豆瓣" height="26" width="88"></a></p>
<p>你还可以移步到<a href="http://blog.solrex.cn">我的博客</a>右侧栏，看看豆瓣好友统计图标和其它统计图标共存的状况。本统计图标一天更新一次，因此统计数并不完全实时，这是为了减轻服务器负载，请理解。</p>
<p>这个小项目完全是出于兴趣写成的，因此很简陋且维持在可用的水平上，我也没有更优化它的想法。在我服务器能承受的情况下我会尽量维持它，但本人不对此服务做任何保证。</p>
<p>我觉得这个服务本身应该由豆瓣提供，如果你是豆瓣的工作人员，觉得这个站点有趣并想在豆瓣中加入此服务的话，欢迎你和我联系，我将无偿提供所有的代码，仅仅希望在对应产品中加上一个 Thanks to 到我的链接。</p>
<h3>生成图片</h3>
<script type="text/javascript">
function show() {
  var username = document.getElementById("username").value;
  var img = document.getElementById("user_dc_img");
  var code = document.getElementById("user_dc_code");
  img.innerHTML = '<img src="http://solrex.cn/douyou/dc/' + username + '" style="border: 0pt none ;" alt="豆瓣" height="26" width="88">';
  code.innerHTML = '<span style="color: rgb(0, 0, 128); font-weight: bold;">&lt;a</span> <span style="color: rgb(255, 0, 0);">href=</span><span style="color: rgb(0, 0, 255);">"http://www.douban.com/people/' + username + '"</span> <span style="color: rgb(255, 0, 0);">title=</span><span style="color: rgb(0, 0, 255);">"豆瓣好友统计"</span><span style="color: rgb(0, 0, 128); font-weight: bold;">&gt;&lt;img</span> <span style="color: rgb(255, 0, 0);">src=</span><span style="color: rgb(0, 0, 255);">"http://solrex.cn/douyou/dc/' + username + '"</span> <span style="color: rgb(255, 0, 0);">style=</span><span style="color: rgb(0, 0, 255);">"border: 0pt none ;"</span> <span style="color: rgb(255, 0, 0);">alt=</span><span style="color: rgb(0, 0, 255);">"豆瓣"</span> <span style="color: rgb(255, 0, 0);">height=</span><span style="color: rgb(0, 0, 255);">"26"</span> <span style="color: rgb(255, 0, 0);">width=</span><span style="color: rgb(0, 0, 255);">"88"</span><span style="color: rgb(0, 0, 128); font-weight: bold;">&gt;&lt;/a&gt;</span>';
}
</script>
<p>输入豆瓣 UID：<input name="username" id="username" value="solrex" size="20" type="text">
<input value="提交" type="button" onclick="show()">(豆瓣用户 UID，英文或数字，非登录 email 地址)</p>
<p>（若没有即刻显示请稍等后多提交一次，服务器抓取信息可能有延迟。不知道自己的 UID 的话，可以登录豆瓣，查看自己的设置->username项。）</p>
<p id="user_dc_img"><img src="http://solrex.cn/douyou/dc/solrex" style="border: 0pt none ;" alt="豆瓣" width="88" height="26"></p>
<p>您可以把下面这段代码嵌入到您的博客或者主页中来显示豆瓣好友统计：</p>
<p><blockquote id="user_dc_code"><span style="color: rgb(0, 0, 128); font-weight: bold;">&lt;a</span> <span style="color: rgb(255, 0, 0);">href=</span><span style="color: rgb(0, 0, 255);">"http://www.douban.com/people/solrex"</span> <span style="color: rgb(255, 0, 0);">title=</span><span style="color: rgb(0, 0, 255);">"豆瓣好友统计"</span><span style="color: rgb(0, 0, 128); font-weight: bold;">&gt;&lt;img</span> <span style="color: rgb(255, 0, 0);">src=</span><span style="color: rgb(0, 0, 255);">"http://solrex.cn/douyou/dc/solrex"</span> <span style="color: rgb(255, 0, 0);">style=</span><span style="color: rgb(0, 0, 255);">"border: 0pt none ;"</span> <span style="color: rgb(255, 0, 0);">alt=</span><span style="color: rgb(0, 0, 255);">"豆瓣"</span> <span style="color: rgb(255, 0, 0);">height=</span><span style="color: rgb(0, 0, 255);">"26"</span> <span style="color: rgb(255, 0, 0);">width=</span><span style="color: rgb(0, 0, 255);">"88"</span><span style="color: rgb(0, 0, 128); font-weight: bold;">&gt;&lt;/a&gt;</span></blockquote></p>
<h3>联系我</h3>
<p>请到<a href="http://solrex.cn">我的主页</a>寻找我的联系方式。如果您有什么问题或建议，也可以到<a href="http://blog.solrex.cn/articles/douyou-count.html">这篇博客</a>下面评论指出。</p>
<h3>我的其它链接</h3>
<ul>
<li><a href="http://blog.solrex.cn">Solrex Shuffling</a>: 我的博客</li>
<li><a href="http://share.solrex.cn">http://share.solrex.cn</a>: 我共享的有意思和没意思的小玩意儿们</li>
</ul>
</div>
<div id="footer">CopyRight &copy; 2009 Solrex Yang. All rights reserved.</div>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-1164295-1");
pageTracker._trackPageview();
} catch(err) {}
</script>
</body>
</html>
