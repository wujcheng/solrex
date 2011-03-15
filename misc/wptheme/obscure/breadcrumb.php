<?php
	$category = get_the_category();
	echo '<span>You are here: </span>';
	echo '<a href="'.get_bloginfo('url').'">Home</a> &raquo; ';
	if(is_single()) {
		echo get_category_parents($category[0]->cat_ID, TRUE, ' &raquo; ');
		echo get_the_title();
	}
	elseif(is_page()) {
		$parent_id = $post->post_parent;
		
		$pages = array();
		while ($parent_id) {
			$page = get_page($parent_id);
			$pages[] = '<a href="'.get_permalink($page->ID).'" title="">'.get_the_title($page->ID).'</a> &raquo; ';
			$parent_id  = $page->post_parent;
		}
		
		$pages = array_reverse($pages);
		foreach ($pages as $page) { 
			echo $page;
		}
		echo get_the_title();
	}
	elseif(is_category()) {
		echo 'From the category archives &quot;';
		single_cat_title();
		echo '&quot;';
	}
	elseif(is_tag()) {
		echo 'From the tag archives &quot;';
		single_tag_title();
		echo '&quot;';
	}
	elseif(is_day()) {
		echo 'From the daily archives &quot;';
		the_time('F jS, Y');
		echo '&quot;';
	}
	elseif (is_month()) {
		echo 'From the daily archives &quot;';
		the_time('F, Y');
		echo '&quot;';
	}
	elseif (is_year()) {
		echo 'From the daily archives &quot;';
		the_time('Y');
		echo '&quot;';
	}
	elseif(is_404()) {
		echo 'you just found mr.404';
	}
	elseif(is_search()) {
		echo 'You Searched &quot;';
		the_search_query();
		echo '&quot;';
	}
?>