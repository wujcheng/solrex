<?php
if ( !function_exists('_single_cat_title') ) :
function _single_cat_title($prefix = '', $display = true ) {
	$cat = intval( get_query_var('cat') );
	if ( !empty($cat) && !(strtoupper($cat) == 'ALL') ) {
		$my_cat_name = apply_filters('single_cat_title', get_the_category_by_ID($cat));
		if ( !empty($my_cat_name) ) {
			if ( $display )
				echo '<h3>' . $prefix.strip_tags($my_cat_name) . '</h3>';
			else
				return strip_tags($my_cat_name);

			if ( $index_filename == 'index-wap.php' )
				echo '<br/>';
		}
	}
}
endif;

if ( !function_exists('_single_tag_title') ) :
function _single_tag_title($prefix = '', $display = true ) {
	if ( !is_tag() )
		return;

	$tag_id = intval( get_query_var('tag_id') );

	if ( !empty($tag_id) ) {
		$my_tag = &get_term($tag_id, 'post_tag', OBJECT, 'display');
		if ( is_wp_error( $my_tag ) ) 
			return false;
		$my_tag_name = apply_filters('single_tag_title', $my_tag->name);
		if ( !empty($my_tag_name) ) {
			if ( $display )
				echo '<h3>' . $prefix . $my_tag_name . '</h3>';
			else
				return $my_tag_name;

			if ( $index_filename == 'index-wap.php' )
				echo '<br/>';
		}
	}
}
endif;

if ( !function_exists('_get_tag_link') ) :
function _get_tag_link( $tag_id, $index ) {

	$tag = &get_term($tag_id, 'post_tag');

	if ( is_wp_error( $tag ) )
		return $tag;

	$slug = $tag->slug;
	
    $taglink = $index . '?tag=' . $slug;

	return apply_filters('tag_link', $taglink, $tag_id);
}
endif;

if ( !function_exists('_get_the_tag_list') ) :
function _get_the_tag_list($index=''){
    $posttags = get_the_tags();
		
    $content = "";

    if ($posttags) {
        
        $tag_i = 0;
        foreach($posttags as $tag) {
            if($tag_i > 0) { $content .= ', '; }
            $content .= '<a href="' . _get_tag_link($tag->term_id,$index) . '">' .$tag->name .  '</a>';
            $tag_i++;
        }
    }
    else {
        $content .=  '无';
    }

    return $content;
}
endif;

if ( !function_exists('_get_the_category_list') ) :
function _get_the_category_list($separator = '', $parents='', $index='') {
	$categories = get_the_category();
    $thelist = '';

	if (empty($categories))
		return apply_filters('the_category', __('Uncategorized'), $separator, $parents);

    $i = 0;
    foreach ( $categories as $category ) {
        if ( 0 < $i )
            $thelist .= $separator . ' ';
        
        $thelist .= '<a href="' . $index . '?cat=' . $category->term_id . '" title="' . sprintf(__("View all posts in %s"), apply_filters('the_category', $category->name)) . '" ' . $rel . '>' . apply_filters('the_category', $category->name).'</a>';
        
        ++$i;
    }

	return $thelist;
}
endif;

if ( !function_exists('_wp_list_categories') ) :
function _wp_list_categories($args = '') {
	$defaults = array(
		'show_option_all' => '', 'orderby' => 'name',
		'order' => 'ASC', 'show_last_update' => 0,
		'style' => 'list', 'show_count' => 0,
		'hide_empty' => 1, 'use_desc_for_title' => 1,
		'child_of' => 0, 'feed' => '',
		'feed_image' => '', 'exclude' => '',
		'hierarchical' => true, 'title_li' => __('Categories','wap'),
		'echo' => 1
	);

	$r = wp_parse_args( $args, $defaults );

	if ( !isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
		$r['pad_counts'] = true;
	}

	if ( isset( $r['show_date'] ) ) {
		$r['include_last_update_time'] = $r['show_date'];
	}

	extract( $r );

	$categories = get_categories($r);

	$output = '';
	$output = $r['title_li'];

	if ( empty($categories) ) {
		if ( 'list' == $style )
			$output .= __("No categories") . '<br/>';
		else
			$output .= __("No categories");
	} else {
		foreach ( $categories as $category ) {            
            $output .= '<a href="?cat=' . $category->term_id . '" title="' . sprintf(__("View all posts in %s"), apply_filters('the_category', $category->name)) . '" ' . $rel . '>' . apply_filters('the_category', $category->name) .'</a><br/>';
        }
	}

	if ( $echo )
		echo $output;
	else
		return $output;
}
endif;

if ( !function_exists('_wp') ) :
function _wp($query_vars = '') {
	global $wp, $wp_query, $wp_the_query;

    $wp->init();
    $wp->parse_request($query_vars);
    _send_headers();
    $wp->query_posts();
    $wp->handle_404();
    $wp->register_globals();

    do_action_ref_array('wp', array(&$wp));

	if( !isset($wp_the_query) )
		$wp_the_query = $wp_query;
}
endif;

if ( !function_exists('_send_headers') ) :
function _send_headers() {
    @header('X-Pingback: '. get_bloginfo('pingback_url'));
    if ( is_user_logged_in() )
        nocache_headers();
    @header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
}
endif;

/* 截取UTF-8字符串 */
if ( !function_exists('ustrcut') ) :
function ustrcut($string,$sublen)
{
    if($sublen>=strlen($string))
    {
        return $string;
    }

    $s="";

    for( $i=0; $i< $sublen; $i++ )
    {
        $code = ord($string[$i]);
        if(  $code >= 224 )
        {
            $s.=$string[$i] . $string[++$i] . $string[++$i];

        }
        elseif( $code >= 127 )
        {
            $s.=$string[$i] . $string[++$i];
        }
        else{

            $s.=$string[$i];
        }
    }

    return $s;

}// End Function cnSubStr($string,$sublen)
endif;

function _wp23_get_related_posts() {
	global $wpdb, $post;
	if(!$post->ID){return;}
	$now = current_time('mysql', 1);
	$tags = wp_get_post_tags($post->ID);

	//print_r($tags);

	$taglist = "'" . str_replace("'",'',str_replace('"','',urldecode($tags[0]->term_id))). "'";
	$tagcount = count($tags);
	if ($tagcount > 1) {
		for ($i = 1; $i <= $tagcount; $i++) {
			$taglist = $taglist . ", '" . str_replace("'",'',str_replace('"','',urldecode($tags[$i]->term_id))) . "'";
		}
	}
	
	$limit = get_option("wp23_RP_limit");
	
	if ($limit) $limitclause = "LIMIT $limit";
	$exclude = get_option("wp23_RP_exclude");
	if ( $exclude != '' ) {
	$excludeclause = "AND p.ID NOT IN (SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = 'category' AND tt.term_id REGEXP '[$exclude]')";
	}
	
	$q = "SELECT DISTINCT p.ID, p.post_title, p.post_date, p.comment_count, count(t_r.object_id) as cnt FROM $wpdb->term_taxonomy t_t, $wpdb->term_relationships t_r, $wpdb->posts p WHERE t_t.taxonomy ='post_tag' AND t_t.term_taxonomy_id = t_r.term_taxonomy_id AND t_r.object_id  = p.ID AND (t_t.term_id IN ($taglist)) AND p.ID != $post->ID AND p.post_status = 'publish' AND p.post_date_gmt < '$now' $excludeclause GROUP BY t_r.object_id ORDER BY cnt DESC, p.post_date_gmt DESC $limitclause;";

	//echo $q;

	$related_posts = $wpdb->get_results($q);
	$output = "";
	$wp23_RP_title = get_option("wp23_RP_title");
	if(!$wp23_RP_title) $wp23_RP_title= __("Related Post",'wp23_related_posts');
	
	if (!$related_posts){
	
		$wp23_no_RP = get_option("wp23_no_RP");
		
		if(!$wp23_no_RP || ($wp23_no_RP == "popularity" && !function_exists('akpc_most_popular'))) $wp23_no_RP = "text";
		
		$wp23_no_RP_text = get_option("wp23_no_RP_text");
		
		if($wp23_no_RP == "text"){
			if(!$wp23_no_RP_text) $wp23_no_RP_text= __("No Related Post",'wp23_related_posts');
			$output  .= '<li>'.$wp23_no_RP_text .'</li>';
		}	else{
			if( function_exists('wp23_random_posts') && $wp23_no_RP == "random"){
				if(!$wp23_no_RP_text) $wp23_no_RP_text= __("Random Posts",'wp23_related_posts');
				$related_posts = wp23_random_posts($limitclause);
			}	elseif( function_exists('wp23_most_commented_posts') && $wp23_no_RP == "commented"){
				if(!$wp23_no_RP_text) $wp23_no_RP_text= __("Most Commented Posts",'wp23_related_posts');
				$related_posts = wp23_most_commented_posts($limitclause);
			}	elseif( function_exists('wp23_most_popular_posts') && $wp23_no_RP == "popularity"){
				if(!$wp23_no_RP_text) $wp23_no_RP_text= __("Most Popular Posts",'wp23_related_posts');
				$related_posts = wp23_most_popular_posts($limitclause);
			}else{
				return __("Something wrong",'wp23_related_posts');;
			}
			$wp23_RP_title = $wp23_no_RP_text;
		}
	}
		
	foreach ($related_posts as $related_post ){
		$output .= '<li>';
		
		$show_date = get_option("wp23_RP_Date");
		if ($show_date){
			$dateformat = get_option('date_format');
			$output .=   mysql2date($dateformat, $related_post->post_date) . " -- ";
		}
		
		$output .=  '<a href="'._get_permalink($related_post->ID).'" title="'.wptexturize($related_post->post_title).'">'.wptexturize($related_post->post_title).'';
		
		$show_comments_count = get_option("wp23_RP_Comments");
		if ($show_comments_count){
			$output .=  " (" . $related_post->comment_count . ")";
		}
		
		$output .=  '</a></li>';
	}
		
	$output =  '<h3>'.$wp23_RP_title .'</h3>'.'<ul class="related_post">' . $output . '</ul>';
	return $output;
}

function _wp23_related_posts(){

	$output = _wp23_get_related_posts() ;
	echo $output;	
}

function _get_next_posts_page_link($max_page = 0) {
	global $paged, $pagenow;

	if ( !is_single() ) {
		if ( !$paged )
			$paged = 1;
		$nextpage = intval($paged) + 1;
		if ( !$max_page || $max_page >= $nextpage )
			return _get_pagenum_link($nextpage);
	}
}

function _next_posts($max_page = 0) {
	echo clean_url(_get_next_posts_page_link($max_page));
}

function _next_posts_link($label='Next Page &raquo;', $max_page=0) {
	global $paged, $wpdb, $wp_query;
	if ( !$max_page ) {
		$max_page = $wp_query->max_num_pages;
	}
	if ( !$paged )
		$paged = 1;
	$nextpage = intval($paged) + 1;
	if ( (! is_single()) && (empty($paged) || $nextpage <= $max_page) ) {
		echo '<a href="';
		_next_posts($max_page);
		echo '">'. preg_replace('/&([^#])(?![a-z]{1,8};)/', '&#038;$1', $label) .'</a>';
	}
}

function _get_pagenum_link($pagenum = 1, $using_permalinks = false) {
	global $wp_rewrite;

	$pagenum = (int) $pagenum;

	$request = 'index.php';

	if ( !$using_permalinks ) {
		if ( $pagenum > 1 ) {
			$result = add_query_arg( 'paged', $pagenum, $request );
		} else {
			$result = $request;
		}
	} 
	return $result;
}

function _get_previous_posts_page_link() {
	global $paged, $pagenow;
	if ( !is_single() ) {
		$nextpage = intval($paged) - 1;
		if ( $nextpage < 1 )
			$nextpage = 1;
		return _get_pagenum_link($nextpage);
	}
}

function _previous_posts() {
	echo _get_previous_posts_page_link();
}

function _previous_posts_link($label='&laquo; Previous Page') {
	global $paged;
	if ( (!is_single())	&& ($paged > 1) ) {
		echo '<a href="';
		_previous_posts();
		echo '">'. preg_replace('/&([^#])(?![a-z]{1,8};)/', '&#038;$1', $label) .'</a>&nbsp;';
	}
}

// Navigation links

function _get_previous_post($in_same_cat = false, $excluded_categories = '') {
	global $post, $wpdb;

	if( empty($post) || !is_single() || is_attachment() )
		return null;

	$current_post_date = $post->post_date;

	$join = '';
	if ( $in_same_cat ) {
		$join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id ";
		$cat_array = wp_get_object_terms($post->ID, 'category', 'fields=tt_ids');
		$join .= ' AND (tr.term_taxonomy_id = ' . intval($cat_array[0]);
		for ( $i = 1; $i < (count($cat_array)); $i++ ) {
			$join .= ' OR tr.term_taxonomy_id = ' . intval($cat_array[$i]);
		}
		$join .= ')';
	}

	$sql_exclude_cats = '';
	if ( !empty($excluded_categories) ) {
		$blah = explode(' and ', $excluded_categories);
		$posts_in_ex_cats = get_objects_in_term($blah, 'category');
		$posts_in_ex_cats_sql = 'AND p.ID NOT IN (' . implode($posts_in_ex_cats, ',') . ')';
	}

	$join  = apply_filters( 'get_previous_post_join', $join, $in_same_cat, $excluded_categories );
	$where = apply_filters( 'get_previous_post_where', "WHERE p.post_date < '$current_post_date' AND p.post_type = 'post' AND p.post_status = 'publish' $posts_in_ex_cats_sql", $in_same_cat, $excluded_categories );
	$sort  = apply_filters( 'get_previous_post_sort', 'ORDER BY p.post_date DESC LIMIT 1' );

	return @$wpdb->get_row("SELECT p.ID, p.post_title FROM $wpdb->posts AS p $join $where $sort");
}

function _get_next_post($in_same_cat = false, $excluded_categories = '') {
	global $post, $wpdb;

	if( empty($post) || !is_single() || is_attachment() )
		return null;

	$current_post_date = $post->post_date;

	$join = '';
	if ( $in_same_cat ) {
		$join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id ";
		$cat_array = wp_get_object_terms($post->ID, 'category', 'fields=tt_ids');
		$join .= ' AND (tr.term_taxonomy_id = ' . intval($cat_array[0]);
		for ( $i = 1; $i < (count($cat_array)); $i++ ) {
			$join .= ' OR tr.term_taxonomy_id = ' . intval($cat_array[$i]);
		}
		$join .= ')';
	}

	$sql_exclude_cats = '';
	if ( !empty($excluded_categories) ) {
		$blah = explode(' and ', $excluded_categories);
		$posts_in_ex_cats = get_objects_in_term($blah, 'category');
		$posts_in_ex_cats_sql = 'AND p.ID NOT IN (' . implode($posts_in_ex_cats, ',') . ')';
	}

	$join  = apply_filters( 'get_next_post_join', $join, $in_same_cat, $excluded_categories );
	$where = apply_filters( 'get_next_post_where', "WHERE p.post_date > '$current_post_date' AND p.post_type = 'post' AND p.post_status = 'publish' $posts_in_ex_cats_sql AND p.ID != $post->ID", $in_same_cat, $excluded_categories );
	$sort  = apply_filters( 'get_next_post_sort', 'ORDER BY p.post_date ASC LIMIT 1' );

	return @$wpdb->get_row("SELECT p.ID, p.post_title FROM $wpdb->posts AS p $join $where $sort");
}


function _previous_post_link($format='&laquo; %link', $link='%title', $in_same_cat = false, $excluded_categories = '') {

	if ( is_attachment() )
		$post = & get_post($GLOBALS['post']->post_parent);
	else
		$post = _get_previous_post($in_same_cat, $excluded_categories);

	if ( !$post )
		return;

	$title = $post->post_title;

	if ( empty($post->post_title) )
		$title = __('Previous Post');

	$title = apply_filters('the_title', $title, $post);
	$string = '<a href="'._get_permalink($post->ID).'">';
	$link = str_replace('%title', $title, $link);
	$link = $pre . $string . $link . '</a>';

	$format = str_replace('%link', $link, $format);

	echo $format;
}

function _get_permalink($id = 0) {
	global $index_filename;

	$post = &get_post($id);

	if ( empty($post->ID) ) return FALSE;

	if ( !empty($index_filename) ) return $index_filename . "?p=" . $post->ID;

    return "index.php?p=" . $post->ID;
}

function _next_post_link($format='%link &raquo;', $link='%title', $in_same_cat = false, $excluded_categories = '') {
	$post = _get_next_post($in_same_cat, $excluded_categories);

	if ( !$post )
		return;

	$title = $post->post_title;

	if ( empty($post->post_title) )
		$title = __('Next Post');

	$title = apply_filters('the_title', $title, $post);
	$string = '<a href="'._get_permalink($post->ID).'">';
	$link = str_replace('%title', $title, $link);
	$link = $string . $link . '</a>';
	$format = str_replace('%link', $link, $format);

	echo $format;
}

if(!function_exists('_get_most_viewed')) {
	function _get_most_viewed($mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$where = '';
		$temp = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		$most_viewed = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.*, (meta_value+0) AS views FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE post_date < '".current_time('mysql')."' AND $where AND post_status = 'publish' AND meta_key = 'views' AND post_password = '' ORDER  BY views DESC LIMIT $limit");
		if($most_viewed) {
			if($chars > 0) {
				foreach ($most_viewed as $post) {
					$post_title = get_the_title();
					$post_views = intval($post->views);
					$post_views = number_format($post_views);
					$temp .= "<a href=\"" . _get_permalink($post->ID) . "\">".snippet_chars($post_title, $chars)."</a> - $post_views ".__('views', 'wp-postviews')."<br/>";
				}
			} else {
				foreach ($most_viewed as $post) {
					$post_title = get_the_title();
					$post_views = intval($post->views);
					$post_views = number_format($post_views);
					$temp .= "<a href=\"" . _get_permalink($post->ID) . "\">$post_title</a><br/>";
				}
			}
		} else {
			$temp = __('No Posts', 'wap').'<br/>';
		}
		if($display) {
			echo $temp;
		} else {
			return $temp;
		}
	}
}

if ( !function_exists('_get_wap_home') ):
function _get_wap_home()
{
    $wap_home = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	$wap_home = dirname ( $wap_home );
    $wap_home = "http://" . $wap_home;

    return $wap_home;
}
endif;

if ( !function_exists('_wap_header') ) :
function _wap_header($title = '', $name = ''){   

    $stitle = get_option("wap_sitetitle");
    if( $stitle == '' )
    {
        $stitle = get_bloginfo('name');
    }

    if ( isset( $title ) && $title != '' )
        $stitle = $title;

	$sname = $stitle;

    if ( isset( $name ) && $name != '' )
        $sname = $name;

	$sname = str_replace('\\','',$sname);
	$stitle = str_replace('\\','',$stitle);
echo '<?xml version="1.0" encoding="UTF-8"?>';
    ?>

<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=<?php bloginfo('charset'); ?>" />
<title><?php echo $stitle; ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?></title>
<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" />
<meta name="wap-version" content="1.10 (2008.8.3)" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<link rel="shortcut icon" href="<?php bloginfo('template_url'); ?>/favicon.ico" type="image/x-icon" />

<style type="text/css">
body,ul,ol,form{margin:0 0;padding:0 0}
ul,ol{list-style:none}
h1,h2,h3,div,li,p{margin:0 0;padding:2px 2px;font-size:medium}
h2,li,.s{border-bottom:1px solid #ccc}
h1{background:#7acdea}
h2{background:#d2edf6}
h3{border-bottom:1px solid #ffed00;background:#fffcaa;}
.n{border:1px solid #ffed00;background:#fffcaa}
.t,.a,.stamp,#ft{color:#999;font-size:small}
</style>
<script type="text/javascript">
//<![CDATA[
function focusit() {
    var ele = document.getElementById('user_login');
    if(ele){
        ele.focus();
    }
}
window.onload = focusit;

function addLoadEvent(func) {if ( typeof wpOnload!='function'){wpOnload=func;}else{ var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}}
//]]>
</script>
</head>
<body>
<h1><a href="index-wap2.php" accesskey="0"><?php echo $sname; ?></a></h1>

    <?php
}
endif;

if ( !function_exists('_wap_footer') ) :
function _wap_footer(){
    ?>
<br/>
<hr/>
<div id="nav">
<p><a href="index.php" accesskey="0"><?php _e('Home','wap'); ?></a></p>
<?php
$filename = $_SERVER['PHP_SELF'];
$filename = str_replace('\\','/',$filename);
$filename = str_replace(dirname($filename),'',$filename);
$filename = str_replace('/','',$filename);
if($filename != 'login.php'){
    if ( ! is_user_logged_in() ){    
        echo '<p><a href="login.php">' . __('Login','wap') . '</a></p>';
    }
    else{
        if($filename != 'writer.php'){
            echo '<p><a href="writer.php">' . __('New Post','wap') . '</a></p>';  
        }
        if($filename != 'edit.php'){
            echo '<p><a href="edit.php">' . __('Manage Posts','wap') . '</a></p>';  
        }
        if($filename != 'edit-comments.php'){
            echo '<p><a href="edit-comments.php">' . __('Approve Comments','wap') . '</a></p>';  
        }
        echo '<p><a href="login.php?action=logout">' . __('Logout','wap') . '</a></p>';
    }
}
?>
</div>
<br/>切换访问：2.0版 | <a href="index-wap.php">1.1版</a><br/>
<div id="ft">
<?php 
    if ( get_option("wap_copyright") != '' ){
        echo get_option("wap_copyright");
    }
    else{
        echo '&copy; 2007 tanggaowei.com';  
    }
?>
</div>
</body>
</html>
    <?php
}
endif;

if ( !function_exists('_wap_check_admin_referer') ) :
function _wap_check_admin_referer($action = -1) {
	$adminurl = 'edit-comments.php';
	$referer = strtolower(wp_get_referer());
	if ( !wp_verify_nonce($_REQUEST['_wpnonce'], $action) &&
		!(-1 == $action && strpos($referer, $adminurl) !== false)) {
		_wap_nonce_ays($action);
		die();
	}
	do_action('check_admin_referer', $action);
}
endif;

if ( !function_exists('_wap_nonce_ays') ) :
function _wap_nonce_ays($action) {
	global $pagenow, $menu, $submenu, $parent_file, $submenu_file;

	$adminurl = 'edit-comments.php';
	if ( wp_get_referer() )
		$adminurl = clean_url(wp_get_referer());

	$title = __('WordPress Confirmation');
	// Remove extra layer of slashes.
	$_POST   = stripslashes_deep($_POST  );

    preg_match( '/([a-z]+)-([a-z]+)(_(.+))?/', $action, $matches );

	if ( $_POST ) {
		$q = http_build_query($_POST);
		$q = explode( ini_get('arg_separator.output'), $q);
		$html .= "\t<form method='post' action='" . attribute_escape($pagenow) . "'>\n";
		foreach ( (array) $q as $a ) {
			$v = substr(strstr($a, '='), 1);
			$k = substr($a, 0, -(strlen($v)+1));
			$html .= "\t\t<input type='hidden' name='" . attribute_escape(urldecode($k)) . "' value='" . attribute_escape(urldecode($v)) . "' />\n";
		}
		$html .= "\t\t<input type='hidden' name='_wpnonce' value='" . wp_create_nonce($action) . "' />\n";
		$html .= "\t\t<div id='message' class='confirm fade'>\n\t\t<p>" . sprintf(__("You are about to delete this post '%s'\n  'Cancel' to stop, 'OK' to delete."), get_the_title ( $matches[4] ) ) . "</p>\n\t\t<p><a href='$adminurl'>" . __('No') . "</a> <input type='submit' value='" . __('Yes') . "' /></p>\n\t\t</div>\n\t</form>\n";
	} else {
		$html .= "\t<div id='message' class='confirm fade'>\n\t<p>" . sprintf(__("You are about to delete this post '%s'\n  'Cancel' to stop, 'OK' to delete."), get_the_title ( $matches[4] ) ) . "</p>\n\t<p><a href='$adminurl'>" . __('Cancel','wap') . "</a> <a href='" . clean_url(add_query_arg( '_wpnonce', wp_create_nonce($action), $_SERVER['REQUEST_URI'] )) . "'>" . __('OK','wap') . "</a></p>\n\t</div>\n";
	}
	_wap_die($html, $title);
}
endif;

if ( !function_exists('_wap_die') ) :
function _wap_die( $message, $title = '' ) {
	global $wp_locale;

	if ( function_exists( 'is_wp_error' ) && is_wp_error( $message ) ) {
		if ( empty($title) ) {
			$error_data = $message->get_error_data();
			if ( is_array($error_data) && isset($error_data['title']) )
				$title = $error_data['title'];
		}
		$errors = $message->get_error_messages();
		switch ( count($errors) ) :
		case 0 :
			$message = '';
			break;
		case 1 :
			$message = "<p>{$errors[0]}</p>";
			break;
		default :
			$message = "<ul>\n\t\t<li>" . join( "</li>\n\t\t<li>", $errors ) . "</li>\n\t</ul>";
			break;
		endswitch;
	} elseif ( is_string($message) ) {
		$message = "<p>$message</p>";
	}

	$admin_dir = 'writer.php';

	if ( !function_exists('did_action') || !did_action('admin_head') ) :
	if( !headers_sent() ){
		status_header(500);
		nocache_headers();
		header('Content-Type: text/html; charset=utf-8');
	}

	if ( empty($title) ){
		if( function_exists('__') )
			$title = __('WordPress &rsaquo; Error');
		else
			$title = 'WordPress &rsaquo; Error';
	}

    _wap_header();
?>

<?php endif; ?>

	<?php echo $message; ?>

<?php
    _wap_footer();

	die();
}
endif;

if ( !function_exists('_wap_get_comment_list') ) :
function _wap_get_comment_list( $s = false, $start, $num, $view_all = false ) {
	global $wpdb;

	$start = abs( (int) $start );
	$num = (int) $num;
    
    if ( $view_all ){
    	$comments = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM $wpdb->comments WHERE comment_approved = '0' OR comment_approved = '1' ORDER BY comment_date DESC LIMIT $start, $num" );
    }
    else{
        $comments = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM $wpdb->comments WHERE comment_approved = '0' ORDER BY comment_date DESC LIMIT $start, $num" );
    }

	update_comment_cache($comments);

	$total = $wpdb->get_var( "SELECT FOUND_ROWS()" );

	return array($comments, $total);
}
endif;

if ( !function_exists('_wap_comment_list_item') ) :
function _wap_comment_list_item( $id, $view_all = false ) {
	global $authordata, $comment, $wpdb;
	$id = (int) $id;
	$comment =& get_comment( $id );
	$class = '';
	$post = get_post($comment->comment_post_ID);
	$authordata = get_userdata($post->post_author);
	$comment_status = wp_get_comment_status($comment->comment_ID);
    
	if ( 'unapproved' == $comment_status ){
		$class .= ' unapproved';
    }
    else{
        if ( !$view_all ) return;
    }

	echo "<li id='comment-$comment->comment_ID' class='$class'>";

    $post = get_post($comment->comment_post_ID, OBJECT, 'display');
    $post_title = wp_specialchars( $post->post_title, 'double' );
    $post_title = ('' == $post_title) ? "# $comment->comment_post_ID" : $post_title;
?>
    <p>
        <strong><?php comment_author(); ?></strong>        
        <br><span class="stamp"><?php _e('Post','wap') ?>: <a href="index.php?p=<?php echo $comment->comment_post_ID; ?>"><?php echo $post_title; ?></a></span>
        <br/><span class="stamp"><?php _e('Time','wap') ?>：<?php comment_date(__('Y-m-d H:i:s'));?></span>
        <br/><span class="stamp"><?php _e('IP','wap') ?>: <a href="http://ws.arin.net/cgi-bin/whois.pl?queryinput=<?php comment_author_IP() ?>"><?php comment_author_IP() ?></a></span>
        <?php   
		// email
		if ( $comment->comment_author_email != '' && current_user_can('edit_post', $comment->comment_post_ID) ) {
			?> <br/><span class="stamp"><?php _e('Email','wap') ?>: <a href="<?php echo $comment->comment_author_email ?>"><?php echo $comment->comment_author_email ?></a> </span> <?php
		}
        if ($comment->comment_author_url && 'http://' != $comment->comment_author_url) { ?> <br/><span class="stamp"><?php _e('Site','wap') ?>: <?php comment_author_url_link() ?> </span> <?php } 		
		?>

    </p>
<?php comment_text() ?>

<p> <br/> 
<?php
if ( current_user_can('edit_post', $comment->comment_post_ID) ) {
	echo '[<a href="' . wp_nonce_url('comment.php?action=deletecomment&amp;p=' . $comment->comment_post_ID . '&amp;c=' . $comment->comment_ID, 'delete-comment_' . $comment->comment_ID) . '" onclick="return deleteSomething( \'comment\', ' . $comment->comment_ID . ', \'' . js_escape(sprintf(__("You are about to delete this comment by '%s'.\n'Cancel' to stop, 'OK' to delete."), $comment->comment_author)) . "', theCommentList );\">" . __('Delete','wap') . '</a> ';

	if ( ('none' != $comment_status) && ( current_user_can('moderate_comments') ) ) {
        if ( 'unapproved' != $comment_status )
    		echo '<span class="unapprove"> | <a href="' . wp_nonce_url('comment.php?action=unapprovecomment&amp;p=' . $comment->comment_post_ID . '&amp;c=' . $comment->comment_ID, 'unapprove-comment_' . $comment->comment_ID) . '" onclick="return dimSomething( \'comment\', ' . $comment->comment_ID . ', \'unapproved\', theCommentList );">' . __('Pending','wap') . '</a> </span>';
        else
    		echo '<span class="approve"> | <a href="' . wp_nonce_url('comment.php?action=approvecomment&amp;p=' . $comment->comment_post_ID . '&amp;c=' . $comment->comment_ID, 'approve-comment_' . $comment->comment_ID) . '" onclick="return dimSomething( \'comment\', ' . $comment->comment_ID . ', \'unapproved\', theCommentList );">' . __('Approved','wap') . '</a> </span>';
	}

	echo " | <a href=\"" . wp_nonce_url("comment.php?action=deletecomment&amp;dt=spam&amp;p=" . $comment->comment_post_ID . "&amp;c=" . $comment->comment_ID, 'delete-comment_' . $comment->comment_ID) . "\" onclick=\"return deleteSomething( 'comment-as-spam', $comment->comment_ID, '" . js_escape(sprintf(__("You are about to mark as spam this comment by '%s'.\n'Cancel' to stop, 'OK' to mark as spam."), $comment->comment_author)). "', theCommentList );\">" . __('Spam','wap') . "</a> ]";

}

?>
  </p>
		</li>
<?php
}
endif;

if ( !function_exists('_wp_setcookie') ) :
function _wp_setcookie($username, $password, $already_md5 = false, $home = '', $siteurl = '', $remember = false) {
	if ( !$already_md5 )
		$password = md5( md5($password) ); // Double hash the password in the cookie.

	if ( empty($home) )
		$cookiepath = WAP_COOKIEPATH;
	else
		$cookiepath = preg_replace('|https?://[^/]+|i', '', $home . '/' );

	if ( empty($siteurl) ) {
		$sitecookiepath = WAP_SITECOOKIEPATH;
		$cookiehash = COOKIEHASH;
	} else {
		$sitecookiepath = preg_replace('|https?://[^/]+|i', '', $siteurl . '/' );
		$cookiehash = md5($siteurl);
	}

	if ( $remember )
		$expire = time() + 31536000;
	else
		$expire = 0;

	setcookie(USER_COOKIE, $username, $expire, $cookiepath, WAP_COOKIE_DOMAIN);
	setcookie(PASS_COOKIE, $password, $expire, $cookiepath, WAP_COOKIE_DOMAIN);

	if ( $cookiepath != $sitecookiepath ) {
		setcookie(USER_COOKIE, $username, $expire, $sitecookiepath, WAP_COOKIE_DOMAIN);
		setcookie(PASS_COOKIE, $password, $expire, $sitecookiepath, WAP_COOKIE_DOMAIN);
	}
}
endif;

if ( !function_exists('_wp_clearcookie') ) :
function _wp_clearcookie() {
    setcookie(AUTH_COOKIE, ' ', time() - 31536000, WAP_COOKIEPATH, WAP_COOKIE_DOMAIN);
	setcookie(AUTH_COOKIE, ' ', time() - 31536000, WAP_SITECOOKIEPATH, WAP_COOKIE_DOMAIN);

	// Old cookies
	setcookie(USER_COOKIE, ' ', time() - 31536000, WAP_COOKIEPATH, WAP_COOKIE_DOMAIN);
	setcookie(PASS_COOKIE, ' ', time() - 31536000, WAP_COOKIEPATH, WAP_COOKIE_DOMAIN);
	setcookie(USER_COOKIE, ' ', time() - 31536000, WAP_SITECOOKIEPATH, WAP_COOKIE_DOMAIN);
	setcookie(PASS_COOKIE, ' ', time() - 31536000, WAP_SITECOOKIEPATH, WAP_COOKIE_DOMAIN);
}
endif;

if ( !function_exists('_wap_link_pages') ) :
function _wap_link_pages($args = '') {
	$defaults = array(
		'before' => '<p>' . __('Pages:'), 'after' => '</p>',
		'next_or_number' => 'number', 'nextpagelink' => __('Next page'),
		'previouspagelink' => __('Previous page'), 'pagelink' => '%',
		'more_file' => '', 'echo' => 1
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	global $post, $id, $page, $numpages, $multipage, $more, $pagenow;
	if ( $more_file != '' )
		$file = $more_file;
	else
		$file = $pagenow;

	$output = '';
	if ( $multipage ) {
		if ( 'number' == $next_or_number ) {
			$output .= $before;
			for ( $i = 1; $i < ($numpages+1); $i = $i + 1 ) {
				$j = str_replace('%',"$i",$pagelink);
				$output .= ' ';
				if ( ($i != $page) || ((!$more) && ($page==1)) ) {
					if ( 1 == $i ) {
						$output .= '<a href="' . _get_permalink() . '">';
					} else {
						//if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
							$output .= '<a href="' . _get_permalink() . '&amp;page=' . $i . '">';
						//else
							//$output .= '<a href="' . trailingslashit(_get_permalink()) . user_trailingslashit($i, 'single_paged') . '">';
					}
				}
				$output .= $j;
				if ( ($i != $page) || ((!$more) && ($page==1)) )
					$output .= '</a>';
			}
			$output .= $after;
		} else {
			if ( $more ) {
				$output .= $before;
				$i = $page - 1;
				if ( $i && $more ) {
					if ( 1 == $i ) {
						$output .= '<a href="' . _get_permalink() . '">' . $previouspagelink . '</a>';
					} else {
						//if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
							$output .= '<a href="' . _get_permalink() . '&amp;page=' . $i . '">' . $previouspagelink . '</a>';
						//else
						//	$output .= '<a href="' . trailingslashit(_get_permalink()) . user_trailingslashit($i, 'single_paged') . '">' . $previouspagelink . '</a>';
					}
				}
				$i = $page + 1;
				if ( $i <= $numpages && $more ) {
					if ( 1 == $i ) {
						$output .= '<a href="' . _get_permalink() . '">' . $nextpagelink . '</a>';
					} else {
						//if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
							$output .= '<a href="' . _get_permalink() . '&amp;page=' . $i . '">' . $nextpagelink . '</a>';
						//else
						//	$output .= '<a href="' . trailingslashit(_get_permalink()) . user_trailingslashit($i, 'single_paged') . '">' . $nextpagelink . '</a>';
					}
				}
				$output .= $after;
			}
		}
	}

	if ( $echo )
		echo $output;

	return $output;
}
endif;
?>