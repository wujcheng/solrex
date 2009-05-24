<!-- footer START -->
<div id="footer">
	<a id="gotop" href="#" onclick="MGJS.goTop();return false;"><?php _e('Top', 'inove'); ?></a>
	<a id="powered" href="http://wordpress.org/">WordPress</a>
	<div id="copyright">
		<?php
			global $wpdb;
			$post_datetimes = $wpdb->get_results("SELECT YEAR(min(post_date_gmt)) AS firstyear, YEAR(max(post_date_gmt)) AS lastyear FROM $wpdb->posts WHERE post_date_gmt > 1970");
			if ($post_datetimes) {
				$firstpost_year = $post_datetimes[0]->firstyear;
				$lastpost_year = $post_datetimes[0]->lastyear;

				$copyright = __('Copyright &copy; ', 'inove') . $firstpost_year;
				if($firstpost_year != $lastpost_year) {
					$copyright .= '-'. $lastpost_year;
				}
				$copyright .= ' <a href="http://solrex.cn">Solrex Yang</a>. ';

				echo $copyright;
                _e('This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/3.0/us/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc-nd/3.0/us/80x15.png" />Creative Commons United States License</a>.', 'inove');
			}
		?>
	</div>
	<div id="themeinfo">
		<?php _e('Theme by <a href="http://www.neoease.com/">NeoEase</a>. Valid <a href="http://validator.w3.org/check?uri=referer">XHTML 1.1</a> and <a href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3">CSS 3</a>.', 'inove'); ?>
	</div>
</div>
<!-- footer END -->

</div>
<!-- container END -->
</div>
<!-- wrap END -->

<?php wp_footer(); ?>

<!-- Include the Google Friend Connect javascript library. -->
<script type="text/javascript" src="http://www.google.com/friendconnect/script/friendconnect.js"></script>
<!-- Define the div tag where the gadget will be inserted. -->
<div id="div-9078055125469082983"></div>
<!-- Render the gadget into a div. -->
<script type="text/javascript">
var skin = {};
skin['BORDER_COLOR'] = '#cccccc';
skin['ENDCAP_BG_COLOR'] = '#e0ecff';
skin['ENDCAP_TEXT_COLOR'] = '#333333';
skin['ENDCAP_LINK_COLOR'] = '#0000cc';
skin['ALTERNATE_BG_COLOR'] = '#ffffff';
skin['CONTENT_BG_COLOR'] = '#ffffff';
skin['CONTENT_LINK_COLOR'] = '#0000cc';
skin['CONTENT_TEXT_COLOR'] = '#333333';
skin['CONTENT_SECONDARY_LINK_COLOR'] = '#7777cc';
skin['CONTENT_SECONDARY_TEXT_COLOR'] = '#666666';
skin['CONTENT_HEADLINE_COLOR'] = '#333333';
skin['POSITION'] = 'top';
skin['DEFAULT_COMMENT_TEXT'] = '- \u7ed9\u6211\u7559\u8a00(\u65e5\u5fd7\u8bc4\u8bba\u8bf7\u5230\u5355\u7bc7\u65e5\u5fd7\u9875)-';
skin['HEADER_TEXT'] = '\u7559\u8a00\u677f+\u6211\u7684 MiniBlog';
google.friendconnect.container.setParentUrl('/' /* location of rpc_relay.html and canvas.html */);
google.friendconnect.container.renderSocialBar(
 { id: 'div-9078055125469082983',
   site: '02071694419876609776',
   'view-params':{"scope":"SITE","features":"video,comment","showWall":"true"}
 },
  skin);
</script>
</body>
</html>
