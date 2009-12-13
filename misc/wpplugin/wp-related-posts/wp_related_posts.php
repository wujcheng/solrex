<?php
/*
   Plugin Name: WordPress Related Posts
*/

add_action('init', 'init_textdomain');
function init_textdomain(){
  load_plugin_textdomain('wp_related_posts',PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) . '/lang');
}

function wp_get_related_posts($exclude, $limit, $wp_rp_title, $wp_no_rp, $wp_no_rp_text, $show_date, $show_comments_count, $show_excerpt, $excerpt_length) {
	global $wpdb, $post,$table_prefix;

	if ( $exclude != '' ) {
		$q = "SELECT tt.term_id FROM ". $table_prefix ."term_taxonomy tt, " . $table_prefix . "term_relationships tr WHERE tt.taxonomy = 'category' AND tt.term_taxonomy_id = tr.term_taxonomy_id AND tr.object_id = $post->ID";

		$cats = $wpdb->get_results($q);

		foreach(($cats) as $cat) {
			if (in_array($cat->term_id, $exclude) != false){
				return;
			}
		}
	}
		
	if(!$post->ID){return;}
	$now = current_time('mysql', 1);
	$tags = wp_get_post_tags($post->ID);

	
	$taglist = "'" . $tags[0]->term_id. "'";
	
	$tagcount = count($tags);
	if ($tagcount > 1) {
		for ($i = 1; $i <= $tagcount; $i++) {
			$taglist = $taglist . ", '" . $tags[$i]->term_id . "'";
		}
	}
		
	if ($limit) {
		$limitclause = "LIMIT $limit";
	}	else {
		$limitclause = "LIMIT 10";
	}

	// 2008/10/29 modified by mg12 START
	$q = "SELECT p.ID, p.post_title, p.post_date, p.comment_count, count(t_r.object_id) as cnt, p.post_content FROM $wpdb->term_taxonomy t_t, $wpdb->term_relationships t_r, $wpdb->posts p WHERE t_t.taxonomy ='post_tag' AND t_t.term_taxonomy_id = t_r.term_taxonomy_id AND t_r.object_id  = p.ID AND (t_t.term_id IN ($taglist)) AND p.ID != $post->ID AND p.post_status = 'publish' AND p.post_date_gmt < '$now' GROUP BY t_r.object_id ORDER BY cnt DESC, p.post_date_gmt DESC $limitclause;";
	// 2008/10/29 modified by mg12 END

	//echo $q;

	$related_posts = $wpdb->get_results($q);
	$output = "";
	
	if (!$related_posts){
		
		if(!$wp_no_rp || ($wp_no_rp == "popularity" && !function_exists('akpc_most_popular'))) $wp_no_rp = "text";
		
		if($wp_no_rp == "text"){
			if(!$wp_no_rp_text) $wp_no_rp_text= __("No Related Post",'wp_related_posts');
			$output  .= '<li>'.$wp_no_rp_text .'</li>';
		}	else{
			if($wp_no_rp == "random"){
				if(!$wp_no_rp_text) $wp_no_rp_text= __("Random Posts",'wp_related_posts');
				$related_posts = wp_get_random_posts($limitclause);
			}	elseif($wp_no_rp == "commented"){
				if(!$wp_no_rp_text) $wp_no_rp_text= __("Most Commented Posts",'wp_related_posts');
				$related_posts = wp_get_most_commented_posts($limitclause);
			}	elseif($wp_no_rp == "popularity"){
				if(!$wp_no_rp_text) $wp_no_rp_text= __("Most Popular Posts",'wp_related_posts');
				$related_posts = wp_get_most_popular_posts($limitclause);
			}else{
				return __("Something wrong",'wp_related_posts');;
			}
			$wp_rp_title = $wp_no_rp_text;
		}
	}		

	// 2008/10/29 modified by mg12 START
	foreach ($related_posts as $related_post ){
		$output .= '<li>';

		if ($show_date){
			$dateformat = get_option('date_format');
			$output .= mysql2date($dateformat, $related_post->post_date) . " - ";
		}

		$output .= '<a href="'.get_permalink($related_post->ID).'" title="'.wptexturize($related_post->post_title).'">'.wptexturize($related_post->post_title).'</a>';
		
		if ($show_comments_count){
			$output .= ' <span class="count">(' . $related_post->comment_count . ')</span>';
		}

		if ($show_excerpt && (int)$excerpt_length > 0){
			$output .= ' - <small class="excerpt">' . wp_get_excerpt($related_post->post_content, (int)$excerpt_length) . '</small>';
		}

		$output .=  '</li>';
	}
	// 2008/10/29 modified by mg12 END

	$output = '<ul class="related_post">' . $output . '</ul>';

	if($wp_rp_title != '') $output =  '<h3>'.$wp_rp_title .'</h3>'. $output;
	
	return $output;
}

// 2008/10/30 added by mg12 START
function wp_get_excerpt($content, $length) {
	$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";

	$str = preg_replace('/(\r\n)|(\n)/', '', $content); // 消灭换行符
	$str = rp_remove_blockquotes($str); // 消灭所有 blockquote 内容
	$str = preg_replace('/\<(.+?)\>/', '', $str); // 消灭所有标签

	preg_match_all($pa, $str, $t_str);

	if(count($t_str[0]) - $start > $length) {
		$ellipsis = '...';
		$excerpt = join('', array_slice($t_str[0], $start, $length)) . $ellipsis;
	}
	return $excerpt;
}

/**
 * 移除 HTML 字符串中所有 blockquote
 * @param str			HTML 字符串
 */
function rp_remove_blockquotes($str) {
	// 开头和结尾的标记
	$start_pattern = '<blockquote';
	$end_pattern = '</blockquote>';

	// 出现的次数
	$quote_count = substr_count(strtolower($str), $start_pattern);

	// 如果出现次数为 0, 不进行任何处理, 直接返回
	if ($quote_count <= 0) {
		return $str;

	// 如果出现次数为 1, 将这个 blockquote 内的全部内容移除
	} else if ($quote_count == 1) {
		$all_pattern = '/(\<blockquote(.*?)\>)(.*)(\<\/blockquote\>)/i';
		return preg_replace($all_pattern, '', $str);
	}

	// 如果出现次数大于 1
	// 找到第一个结尾出现的位置
	$end = strpos(strtolower($str), $end_pattern);

	// 处理所有 blockquote
	while ($end) {
		// 获得尾部之前的子串, 并在子串中找到相应的开头位置
		$str_before_end = substr($str, 0, $end);
		$start = strrpos(strtolower($str_before_end), $start_pattern);
		// 将 blockquote 部分除去, 并用分隔符隔开
		$sep = ' ';
		$str = substr_replace($str, $sep, $start, $end + strlen($end_pattern) - $start);
		// 找到下一个结尾的位置
		$end = strpos(strtolower($str), $end_pattern);
	}

	return $str;
}
// 2008/10/30 added by mg12 END

function wp_related_posts(){
	$wp_rp = get_option("wp_rp");

	$exclude = explode(",",$wp_rp["wp_rp_exclude"]);
	$limit = $wp_rp["wp_rp_limit"];
	$wp_rp_title = $wp_rp["wp_rp_title"];
	$wp_no_rp = $wp_rp["wp_no_rp"];
	$wp_no_rp_text = $wp_rp["wp_no_rp_text"];
	$show_date = $wp_rp["wp_rp_date"];
	$show_comments_count = $wp_rp["wp_rp_comments"];
	$show_excerpt = $wp_rp["wp_rp_excerpt"];
	$excerpt_length = $wp_rp["wp_rp_excerpt_length"];

	$output = wp_get_related_posts($exclude, $limit, $wp_rp_title, $wp_no_rp, $wp_no_rp_text, $show_date, $show_comments_count, $show_excerpt, $excerpt_length) ;
	echo $output;
}

function wp23_related_posts() {
	wp_related_posts();
}

function wp_related_posts_for_feed($content=""){
	$wp_rp = get_option("wp_rp");

	$exclude = explode(",",$wp_rp["wp_rp_exclude"]);
	$limit = $wp_rp["wp_rp_limit"];
	$wp_rp_title = $wp_rp["wp_rp_title"];
	$wp_no_rp = $wp_rp["wp_no_rp"];
	$wp_no_rp_text = $wp_rp["wp_no_rp_text"];
	$show_date = $wp_rp["wp_rp_date"];
	$show_comments_count = $wp_rp["wp_rp_comments"];
	$show_excerpt = $wp_rp["wp_rp_excerpt"];
	$excerpt_length = $wp_rp["wp_rp_excerpt_length"];
	$wp_rp_rss = ($wp_rp["wp_rp_rss"] == 'yes') ? 1 : 0;

	if ( (! is_feed()) || (! $wp_rp_rss)) return $content;

	$output = wp_get_related_posts($exclude, $limit, $wp_rp_title, $wp_no_rp, $wp_no_rp_text, $show_date, $show_comments_count, false, '') ;
	$content = $content . $output;

	return $content;
}

add_filter('the_content', 'wp_related_posts_for_feed',1);

function wp_related_posts_auto($content=""){
	$wp_rp = get_option("wp_rp");
	$wp_rp_auto = ($wp_rp["wp_rp_auto"] == 'yes') ? 1 : 0;
	if ( (! is_single()) || (! $wp_rp_auto)) return $content;
	
	$output = wp_get_related_posts() ;
	$content = $content . $output;
	
	return $content;
}

add_filter('the_content', 'wp_related_posts_auto',99);

function wp_get_random_posts ($limitclause="") {
    global $wpdb, $tableposts, $post;
		
    $q = "SELECT ID, post_title, post_date, comment_count FROM $tableposts WHERE post_status = 'publish' AND post_type = 'post' AND ID != $post->ID ORDER BY RAND() $limitclause";
    return $wpdb->get_results($q);
}

function wp_random_posts ($number = 10){
	$limitclause="LIMIT " . $number;
	$random_posts = wp_get_random_posts ($limitclause);
	
	foreach ($random_posts as $random_post ){
		$output .= '<li>';
		
		$output .=  '<a href="'.get_permalink($random_post->ID).'" title="'.wptexturize($random_post->post_title).'">'.wptexturize($random_post->post_title).'</a></li>';
	}
	
	$output = '<ul class="randome_post">' . $output . '</ul>';
	
	echo $output;
}

function wp_get_most_commented_posts($limitclause="") {
	global $wpdb; 
    $q = "SELECT ID, post_title, post_date, COUNT($wpdb->comments.comment_post_ID) AS 'comment_count' FROM $wpdb->posts, $wpdb->comments WHERE comment_approved = '1' AND $wpdb->posts.ID=$wpdb->comments.comment_post_ID AND post_status = 'publish' GROUP BY $wpdb->comments.comment_post_ID ORDER BY comment_count DESC $limitclause"; 
    return $wpdb->get_results($q);
} 

function wp_most_commented_posts ($number = 10){
	$limitclause="LIMIT " . $number;
	$most_commented_posts = wp_get_most_commented_posts ($limitclause);
	
	foreach ($most_commented_posts as $most_commented_post ){
		$output .= '<li>';
		
		$output .=  '<a href="'.get_permalink($most_commented_post->ID).'" title="'.wptexturize($most_commented_post->post_title).'">'.wptexturize($most_commented_post->post_title).'</a></li>';
	}
	
	$output = '<ul class="most_commented_post">' . $output . '</ul>';
	
	echo $output;
}

function wp_get_most_popular_posts ($limitclause="") {
    global $wpdb, $table_prefix;
		
    $q = $sql = "SELECT p.ID, p.post_title, p.post_date, p.comment_count FROM ". $table_prefix ."ak_popularity as akpc,".$table_prefix ."posts as p WHERE p.ID = akpc.post_id ORDER BY akpc.total DESC $limitclause";;
    return $wpdb->get_results($q);
}

function wp_most_popular_posts ($number = 10){
	$limitclause="LIMIT " . $number;
	$most_popular_posts = wp_get_most_popular_posts ($limitclause);
	
	foreach ($most_popular_posts as $most_popular_post ){
		$output .= '<li>';
		
		$output .=  '<a href="'.get_permalink($most_popular_post->ID).'" title="'.wptexturize($most_popular_post->post_title).'">'.wptexturize($most_popular_post->post_title).'</a></li>';
	}
	
	$output = '<ul class="most_popular_post">' . $output . '</ul>';
	
	echo $output;
}

add_action('admin_menu', 'wp_add_related_posts_options_page');

function wp_add_related_posts_options_page() {
	if (function_exists('add_options_page')) {
		add_options_page( __('WordPress Related Posts','wp_related_posts'), __('WordPress Related Posts','wp_related_posts'), 8, basename(__FILE__), 'wp_related_posts_options_subpanel');
	}
}

function wp_related_posts_options_subpanel() {
	if($_POST["wp_rp_Submit"]){
		$message = "WordPress Related Posts Setting Updated";
	
		$wp_rp_saved = get_option("wp_rp");
	
		$wp_rp = array (
			"wp_rp_title" 	=> $_POST['wp_rp_title_option'],
			"wp_no_rp"		=> $_POST['wp_no_rp_option'],
			"wp_no_rp_text"	=> $_POST['wp_no_rp_text_option'],
			"wp_rp_limit"	=> $_POST['wp_rp_limit_option'],
			'wp_rp_exclude'	=> $_POST['wp_rp_exclude_option'],
			'wp_rp_auto'	=> $_POST['wp_rp_auto_option'],
			'wp_rp_rss'		=> $_POST['wp_rp_rss_option'],
			'wp_rp_comments'=> $_POST['wp_rp_comments_option'],
			'wp_rp_date'	=> $_POST['wp_rp_date_option'],
			'wp_rp_excerpt'	=> $_POST['wp_rp_excerpt_option'],
			'wp_rp_excerpt_length'	=> $_POST['wp_rp_excerpt_length_option']
		);
		
		if ($wp_rp_saved != $wp_rp)
			if(!update_option("wp_rp",$wp_rp))
				$message = "Update Failed";
		
		echo '<div id="message" class="updated fade"><p>'.$message.'.</p></div>';
	}
	
	$wp_rp = get_option("wp_rp");
?>
    <div class="wrap">
        <h2 id="write-post"><?php _e("Related Posts Options&hellip;",'wp_related_posts');?></h2>
        <p><?php _e("WordPress Related Posts Plugin will generate a related posts via WordPress tags, and add the related posts to feed.",'wp_related_posts');?></p>
        <div style="float:right;">
          <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
          <input type="hidden" name="cmd" value="_donations">
          <input type="hidden" name="business" value="honghua.deng@gmail.com">
          <input type="hidden" name="item_name" value="Donate to fairyfish.net">
          <input type="hidden" name="no_shipping" value="0">
          <input type="hidden" name="no_note" value="1">
          <input type="hidden" name="currency_code" value="USD">
          <input type="hidden" name="tax" value="0">
          <input type="hidden" name="lc" value="US">
          <input type="hidden" name="bn" value="PP-DonationsBF">
          <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
          <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"><br />
          </form>
        </div>
        <h3><?php _e("Related Posts Preference",'wp_related_posts');?></h3>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo basename(__FILE__); ?>">
        
        <table class="form-table">
          <tr>
            <th><?php _e("Related Posts Title:",'wp_related_posts'); ?></th>
            <td>
              <input type="text" name="wp_rp_title_option" value="<?php echo $wp_rp["wp_rp_title"]; ?>" />
            </td>
          </tr>
          <tr>
            <th><?php _e("When No Related Posts, Dispaly:",'wp_related_posts'); ?></th>
            <td>
              <?php $wp_no_rp = $wp_rp["wp_no_rp"]; ?>
              <select name="wp_no_rp_option" >
              <option value="text" <?php if($wp_no_rp == 'text') echo 'selected' ?> ><?php _e("Text: 'No Related Posts'",'wp_related_posts'); ?></option>
              <option value="random" <?php if($wp_no_rp == 'random') echo 'selected' ?>><?php _e("Random Posts",'wp_related_posts'); ?></option>
              <option value="commented" <?php if($wp_no_rp == 'commented') echo 'selected' ?>><?php _e("Most Commented Posts",'wp_related_posts'); ?></option>
              <?php if (function_exists('akpc_most_popular')){ ?>
              <option value="popularity" <?php if($wp_no_rp == 'popularity') echo 'selected' ?>><?php _e("Most Popular Posts",'wp_related_posts'); ?></option>
              <?php } ?> 
              </select>
            </td>
          </tr>
          <tr>
            <th><?php _e("No Related Post's Title or Text:",'wp_related_posts'); ?></th>
            <td>
              <input type="text" name="wp_no_rp_text_option" value="<?php echo $wp_rp["wp_no_rp_text"]; ?>" />
            </td>
          </tr>
          <tr>
            <th><?php _e("Limit:",'wp_related_posts');?></th>
            <td>
              <input type="text" name="wp_rp_limit_option" value="<?php echo $wp_rp["wp_rp_limit"]; ?>" />
            </td>
          </tr>
          <tr>
            <th><?php _e("Exclude(category IDs):",'wp_related_posts');?></th>
            <td>
              <input type="text" name="wp_rp_exclude_option" value="<?php echo $wp_rp["wp_rp_exclude"]; ?>" />
            </td>
          </tr>
          <tr>
            <th><?php _e("Other Setting:",'wp_related_posts');?></th>
            <td>
              <label>
              <?php
              if ( $wp_rp["wp_rp_auto"] == 'yes' ) {
              echo '<input name="wp_rp_auto_option" type="checkbox" value="yes" checked>';
              } else {
              echo '<input name="wp_rp_auto_option" type="checkbox" value="yes">';
              }
              ?>
              <?php _e("Auto Insert Related Posts",'wp_related_posts');?>
              </label>
              <br />
              <label>
              <?php
              if ( $wp_rp["wp_rp_rss"] == 'yes' ) {
                echo '<input name="wp_rp_rss_option" type="checkbox" value="yes" checked>';
              } else {
                echo '<input name="wp_rp_rss_option" type="checkbox" value="yes">';
              }
              ?>
              <?php _e("Related Posts for RSS",'wp_related_posts');?>
              </label>
              <br />
              <label>
              <?php
              if ( $wp_rp["wp_rp_comments"] == 'yes' ) {
                echo '<input name="wp_rp_comments_option" type="checkbox" value="yes" checked>';
              } else {
                echo '<input name="wp_rp_comments_option" type="checkbox" value="yes">';
              }
              ?>
              <?php _e("Display Comments Count",'wp_related_posts');?>
              </label>
              <br />
              <label>
              <?php
              if ( $wp_rp["wp_rp_date"] == 'yes' ) {
                echo '<input name="wp_rp_date_option" type="checkbox" value="yes" checked>';
              } else {
                echo '<input name="wp_rp_date_option" type="checkbox" value="yes">';
              }
              ?>
              <?php _e("Display Post Date",'wp_related_posts');?>
              </label>
              <br />
				<!-- 2008/10/29 modified by mg12 START -->
              <label>
              <?php
              if ( $wp_rp["wp_rp_excerpt"] == 'yes' ) {
                echo '<input name="wp_rp_excerpt_option" type="checkbox" value="yes" checked>';
              } else {
                echo '<input name="wp_rp_excerpt_option" type="checkbox" value="yes">';
              }
              ?>
              <?php _e("Display Post Excerpt.",'wp_related_posts');?>
              </label>
              <label><?php _e(" Excerpt Length (number of characters):",'wp_related_posts');?></label>
              <?php echo '<input name="wp_rp_excerpt_length_option" type="text" value="' . $wp_rp["wp_rp_excerpt_length"] . '">'; ?>
              <br />
				<!-- 2008/10/29 modified by mg12 END -->
            </td>
          </tr>
        </table>
        <p class="submit"><input type="submit" value="<?php _e("Update Preferences &raquo;",'wp_related_posts');?>" name="wp_rp_Submit" /></p>
        </form>
      </div>
<?php }?>
