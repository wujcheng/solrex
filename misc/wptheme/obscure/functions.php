<?php
/**
 * Obscure functions and definitions
 *
 * Sets up the theme and provides some helper functions. Some helper functions
 * are used in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * The first function, obscure_setup(), sets up the theme by registering support
 * for various features in WordPress, such as post thumbnails, navigation menus, and the like.
 *
 * @package WordPress
 * @subpackage Obscure
 */

/** Tell WordPress to run obscure_setup() when the 'after_setup_theme' hook is run. */
add_action( 'after_setup_theme', 'obscure_setup' );

if ( ! function_exists( 'obscure_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * @uses add_theme_support() To add support for post thumbnails and automatic feed links.
 * @uses register_nav_menus() To add support for navigation menus.
 * @uses add_custom_background() To add support for a custom background.
 * @uses add_editor_style() To style the visual editor.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 */
function obscure_setup() {

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary-menu' => __( 'Primary Navigation', 'obscure' ),
		'secondary-menu' => __( 'Secondary Navigation', 'obscure' )
	) );

	// This theme allows users to set a custom background
	add_custom_background();

	// Larger images will be auto-cropped to fit, smaller ones will be ignored.
	set_post_thumbnail_size( 300, 300, true );
	
	// Load all custom widgets
	require_once(TEMPLATEPATH . '/widgets.php');
	
	// Remove some defaults
	remove_action('wp_head', 'start_post_rel_link');
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'adjacent_posts_rel_link');
	remove_action('wp_head', 'next_post_rel_link');
	remove_action('wp_head', 'previous_post_rel_link');
}
endif;

/**
 * Get Theme Info - Credits: Joern Kretzschmar & Thematic
 */
 
$themeData = get_theme_data(TEMPLATEPATH . '/style.css');
$version = trim($themeData['Version']);
if(!$version)
	$version = "unknown";

/**
 * Set Theme Info in Constant
 */
 
define('THEMENAME', $themeData['Title']);
define('THEMEVERSION', $version);

/**
 * Makes some changes to the <title> tag, by filtering the output of wp_title().
 *
 * If we have a site description and we're viewing the home page or a blog posts
 * page (when using a static front page), then we will add the site description.
 *
 * If we're viewing a search result, then we're going to recreate the title entirely.
 * We're going to add page numbers to all titles as well, to the middle of a search
 * result title and the end of all other titles.
 *
 * The site title also gets added to all titles.
 *
 * @param string $title Title generated by wp_title()
 * @param string $separator The separator passed to wp_title(). Obscure uses a
 * 	vertical bar, "|", as a separator in header.php.
 * @return string The new title, ready for the <title> tag.
 */
function obscure_filter_wp_title( $title, $separator ) {
	// The $paged global variable contains the page number of a listing of posts.
	// The $page global variable contains the page number of a single post that is paged.
	// We'll display whichever one applies, if we're not looking at the first page.
	global $paged, $page;

	if ( is_search() ) {
		// If we're a search, let's start over:
		$title = sprintf( __( 'Search results for %s', 'obscure' ), '"' . get_search_query() . '"' );
		// Add a page number if we're on page 2 or more:
		if ( $paged >= 2 )
			$title .= " $separator " . sprintf( __( 'Page %s', 'obscure' ), $paged );
		// Add the site name to the end:
		$title .= " $separator " . get_bloginfo( 'name', 'display' );
		// We're done. Let's send the new title back to wp_title():
		return $title;
	}

	// Otherwise, let's start by adding the site name to the end:
	$title .= get_bloginfo( 'name', 'display' );

	// If we have a site description and we're on the home/front page, add the description:
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title .= " $separator " . $site_description;

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		$title .= " $separator " . sprintf( __( 'Page %s', 'obscure' ), max( $paged, $page ) );

	// Return the new title to wp_title():
	return $title;
}
add_filter( 'wp_title', 'obscure_filter_wp_title', 10, 2 );

/**
 * Replace wp version to theme version.
 */

function newspress_wp_generator()
{
	echo '<meta name="generator" content="'.THEMENAME." ".THEMEVERSION.'" />';
}
add_filter('the_generator','newspress_wp_generator');

/**
 * SEO: Create meta data tags.
 */
function obscure_create_metadata() {
	if(is_home()) {
		if ( get_option('obscure_meta_description') <> "" ) { echo '<meta name="description" content="'.stripslashes(get_option('obscure_meta_description')).'" />'; }
		if ( get_option('obscure_meta_keywords') <> "" ) { echo '<meta name="keywords" content="'.stripslashes(get_option('obscure_meta_keywords')).'" />'; }
		if ( get_option('obscure_meta_author') <> "" ) { echo '<meta name="author" content="'.stripslashes(get_option('obscure_meta_author')).'" />'; }
	}
}

/**
 * SEO: Create robots.
 */
function obscure_create_robots() {
	global $paged;

	if((is_home() && ($paged < 2 )) || is_front_page() || is_single() || is_page() || is_attachment()) {
		$content .= "<meta name=\"robots\" content=\"index,follow\" />";
	} elseif (is_search()) {
		$content .= "<meta name=\"robots\" content=\"noindex,nofollow\" />";
	} else {	
		$content .= "<meta name=\"robots\" content=\"noindex,follow\" />";
	}
	$content .= "\n";
	if (get_option('blog_public')) {
		echo apply_filters('obscure_create_robots', $content);
	}
}
 
/**
 * SEO: Create canonical url.
 */
function obscure_canonical_url() {
	if(get_option("obscure_canonical") <> "") {
		if ( is_singular() ) {
			$canonical_url .= '<link rel="canonical" href="' . get_permalink() . '" />';
			$canonical_url .= "\n";        
			echo apply_filters('obscure_canonical_url', $canonical_url);
		}
	}
}

/**
 * Load required javascript to wp_head().
 */
function obscure_head_scripts() {
	$scriptdir_start .= '<script type="text/javascript" src="';
	$scriptdir_start .= get_bloginfo('template_directory');
	$scriptdir_start .= '/library/scripts/';
	$scriptdir_end = '"></script>';
	$scripts .= $scriptdir_start . 'jquery.min.js' . $scriptdir_end . "\n";
	$scripts .= $scriptdir_start . 'common.js' . $scriptdir_end . "\n";
	$scripts .= $scriptdir_start . 'hoverIntent.js' . $scriptdir_end . "\n";
	$scripts .= $scriptdir_start . 'superfish.js' . $scriptdir_end . "\n";
	$scripts .= $scriptdir_start . 'supersubs.js' . $scriptdir_end . "\n";
	$dropdown_options = $scriptdir_start . 'dropdowns.js' . $scriptdir_end . "\n";
	$scripts = $scripts . apply_filters('dropdown_options', $dropdown_options);
	$scripts .= $scriptdir_start . 'jquery.easing.min.js' . $scriptdir_end . "\n";
	$scripts .= $scriptdir_start . 'jquery.lavalamp.js' . $scriptdir_end . "\n";
	$scripts .= $scriptdir_start . 'jquery.cycle.js' . $scriptdir_end . "\n";
	$scripts .= '<script type="text/javascript">' . "\n";
	$scripts .= '//<![CDATA[' . "\n";
	$scripts .= 'jQuery.noConflict();' . "\n";
	$scripts .= 'jQuery(document).ready(function(){' . "\n";
	$scripts .= 'jQuery(".commentlist li:last").css("border","0");' . "\n";
	$scripts .= 'jQuery("#top-menu ul.sf-menu").lavaLamp({ fx: "linear", speed: 333, click: function(event, menuItem) { return menuItem; } });' . "\n";
	$scripts .= 'jQuery(".slider").cycle({ fx: "fade", containerResize: false, pager:  "#slider-nav", timeout: 7000, before: clearTitleText, after: typewriter });' . "\n";
	$scripts .= '});' . "\n";
	$scripts .= '//]]>' . "\n";
	$scripts .= '</script>' . "\n";
	
	// Inset custom header script - via theme option
	if(get_option("obscure_scripts_header") <> "") {
		$scripts .= stripslashes(get_option("obscure_scripts_header")) . "\n";
	}
	
	// Print filtered scripts
	print apply_filters('obscure_head_scripts', $scripts);
	
}
add_action('wp_head','obscure_head_scripts');

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 */
function obscure_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'obscure_page_menu_args' );

/**
 * Add ID and CLASS attributes to the first <ul> occurence in wp_page_menu.
 */
function obscure_add_menuclass($ulclass) {
	return preg_replace('/<ul>/', '<ul class="sf-menu">', $ulclass, 1);
}
add_filter('wp_page_menu','obscure_add_menuclass');

/**
 * Add the proper class of menu container if wp_nav_menu is not set.
 */
function obscure_proper_containerclass($containerclass) {
	return preg_replace('/<div class="sf-menu">/', '<div class="menu">', $containerclass, 1);
}
add_filter('wp_page_menu','obscure_proper_containerclass');

/**
 * Add post and comment rss link to the first <ul> occurence in wp_page_menu and wp_nav_menu ( top-menu ).
 */
function obscure_add_rsslink($ulelement) {
	$rss_link=get_option("obscure_syndication");
	if ($rss_link == "") {
		$rss_link=get_bloginfo('rss2_url');
	}
	return preg_replace('/class="sf-menu">/', 'class="sf-menu"><li class="rss_link"><a href="'.get_bloginfo('comments_rss2_url').'">Comments</a></li><li class="rss_link"><a href="'.$rss_link.'">Posts</a></li>', $ulelement, 1);
}
add_filter('wp_nav_menu','obscure_add_rsslink');
add_filter('wp_page_menu','obscure_add_rsslink');

/**
 * Sets the post excerpt length to 8 white spaces.
 *
 * @return int
 */
function obscure_excerpt_length( $length ) {
	return 8;
}
add_filter( 'excerpt_length', 'obscure_excerpt_length' );

/**
 * Returns a "Continue Reading" link for excerpts
 *
 * @return string "Continue Reading" link
 */
function obscure_continue_reading_link() {
	return ' <a href="'. get_permalink() . '">' . __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'obscure' ) . '</a>';
}

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and obscure_continue_reading_link().
 *
 * @return string An ellipsis
 */
function obscure_auto_excerpt_more( $more ) {
	return ' &hellip;' . obscure_continue_reading_link();
}
add_filter( 'excerpt_more', 'obscure_auto_excerpt_more' );

/**
 * Adds a pretty "Continue Reading" link to custom post excerpts.
 *
 * @return string Excerpt with a pretty "Continue Reading" link
 */
function obscure_custom_excerpt_more( $output ) {
	if ( has_excerpt() && ! is_attachment() ) {
		$output .= obscure_continue_reading_link();
	}
	return $output;
}
add_filter( 'get_the_excerpt', 'obscure_custom_excerpt_more' );

/**
 * Remove inline styles printed when the gallery shortcode is used.
 *
 * @return string The gallery style filter, with the styles themselves removed.
 */
function obscure_remove_gallery_css( $css ) {
	return preg_replace( "#<style type='text/css'>(.*?)</style>#s", '', $css );
}
add_filter( 'gallery_style', 'obscure_remove_gallery_css' );

if ( ! function_exists( 'obscure_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
function obscure_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case '' :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="comment-<?php comment_ID(); ?>">
		<div class="comment-info clearfix">
            <div class="comment-avatar clearfix">
                <?php echo get_avatar( $comment, 48 ); ?>
            </div><!-- .comment-author .vcard -->
    
            <div class="comment-meta commentmetadata">
            	<span class="comment-author vcard"><?php comment_author_link(); ?></span>
                <?php
                    /* translators: 1: date, 2: time, 3: permalink */
                    printf( __( '%1$s at %2$s | <a href="#comment-%3$s">Permalink</a> | ', 'obscure' ), get_comment_date(),  get_comment_time(), get_comment_id() );
					comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) );
                ?>
            </div><!-- .comment-meta .commentmetadata -->
        </div>

		<div class="comment-body"><?php comment_text(); ?></div>
        <?php if ( $comment->comment_approved == '0' ) : ?>
			<em><?php _e( 'Your comment is awaiting moderation.', 'obscure' ); ?></em>
			<br />
		<?php endif; ?>
	</div><!-- #comment-##  -->

	<?php
			break;
		case 'pingback'  :
		case 'trackback' :
	?>
	<li class="post pingback">
    	<div id="comment-<?php comment_ID(); ?>">
        	<div class="comment-info clearfix">
            	<div class="comment-body">
					<p><?php _e( 'Pingback:', 'obscure' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'obscure'), ' ' ); ?></p>
				</div>
            </div>
		</div>
	<?php
			break;
	endswitch;
}
endif;

/**
 * Register widgetized areas, including two sidebars and four widget-ready columns in the footer.
 *
 * @uses register_sidebar
 */
function obscure_widgets_init() {
	// Area 1, located at the top of the sidebar.
	register_sidebar( array(
		'name' => __( 'Primary Widget Area', 'obscure' ),
		'id' => 'primary-widget-area',
		'description' => __( 'The primary widget area', 'obscure' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s"><div class="widget-content">',
		'after_widget' => '</div><div class="widget-end"><!-- empty --></div></li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Area 3, located in the footer.
	register_sidebar( array(
		'name' => __( 'First Footer Widget Area', 'obscure' ),
		'id' => 'first-footer-widget-area',
		'description' => __( 'The first footer widget area', 'obscure' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Area 4, located in the footer.
	register_sidebar( array(
		'name' => __( 'Second Footer Widget Area', 'obscure' ),
		'id' => 'second-footer-widget-area',
		'description' => __( 'The second footer widget area', 'obscure' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Area 5, located in the footer.
	register_sidebar( array(
		'name' => __( 'Third Footer Widget Area', 'obscure' ),
		'id' => 'third-footer-widget-area',
		'description' => __( 'The third footer widget area', 'obscure' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}
/** Register sidebars by running obscure_widgets_init() on the widgets_init hook. */
add_action( 'widgets_init', 'obscure_widgets_init' );

/**
 * Removes the default styles that are packaged with the Recent Comments widget.
 */
function obscure_remove_recent_comments_style() {
	global $wp_widget_factory;
	remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
}
add_action( 'widgets_init', 'obscure_remove_recent_comments_style' );

if ( ! function_exists( 'obscure_post_utility' ) ) :
/**
 * Prints HTML with meta information for the current author, number of comments and categories.
 */
function obscure_post_utility() {
	printf( __( '<div class="author-link"><span class="meta-sep">by</span> %1$s |</div>', 'obscure' ),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			sprintf( esc_attr__( 'View all posts by %s', 'obscure' ), get_the_author() ),
			get_the_author()
		)
	);
	if(!is_attachment()) :
	?> <div class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'obscure' ), __( '1 Comment', 'obscure' ), __( '% Comments', 'obscure' ) ); ?> |</div> <?php
	echo '<div class="category-link">'.get_the_category_list( ', ' ).'</div>';
	else:
		printf( __('<div class="published-link"><span class="%1$s">Published</span> %2$s', 'obscure'),
			'meta-prep meta-prep-entry-date',
			sprintf( '<span class="entry-date"><abbr class="published" title="%1$s">%2$s</abbr></span> |</div>',
				esc_attr( get_the_time() ),
				get_the_date()
			)
		);
		if ( wp_attachment_is_image() ) {
			$metadata = wp_get_attachment_metadata();
			printf( __( '<div class="attachment-link">Full size is %s pixels</div>', 'obscure'),
				sprintf( '<a href="%1$s" title="%2$s">%3$s &times; %4$s</a>',
					wp_get_attachment_url(),
					esc_attr( __('Link to full-size image', 'obscure') ),
					$metadata['width'],
					$metadata['height']
				)
			);
		}
	endif;
}
endif;

if ( ! function_exists( 'obscure_post_meta' ) ) :
/**
 * Prints HTML with meta information for the current post date and edit link.
 */
function obscure_post_meta() {
	printf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
		get_permalink(),
		esc_attr( get_the_time() ),
		get_the_date()
	);
}
endif;

/**
 * Add slider gallery shortcode - use jquery cycle
 */
function add_slider_gallery($atts) {
	extract(shortcode_atts(array(
		'id' => '',
		'fx' => 'fade',
		'timeout' => '5000',
	), $atts));
	
	echo '<script type="text/javascript" language="javascript">jQuery(document).ready(function(){ jQuery("#post_js_gallery_'.$id.'").cycle({ fx:"'.$fx.'", timeout:"'.$timeout.'", containerResize: false }); });</script>';
	echo '<div id="post_slider"><div id="post_js_gallery_'.$id.'" class="post_js_gallery">';
		$args = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'numberposts' => -1,
			'post_status' => 'published',
			'post_parent' => $id
		);
		$attachments = get_posts($args);
		if($attachments) {
			foreach ($attachments as $attachment) {
				$img_url = wp_get_attachment_url($attachment->ID);
				echo '<a href="'.$img_url.'"><img src="'.get_bloginfo('template_directory')."/timthumb.php?src=".$img_url.'&w=600&h=250&zc=1" /></a>';
			}
		}
	echo '</div><div style="clear:both;"></div></div>';
}
add_shortcode('post_slider_gallery','add_slider_gallery');

/**
 * Handle IE8 compatibility.
 */
if (!is_admin())
	header('X-UA-Compatible: IE=EmulateIE7');
	
/**
 * Load theme options.
 */
$themename = "Obscure";
$pre = "obscure";
	
$options = array();
	
$functions_path = TEMPLATEPATH . '/library/admin/';
	
define( OPTION_FILES, 'base.php' );
	
function startit() {
	global $themename, $options, $pre, $functions_path;
			
	if (function_exists('add_menu_page')) {
		$basename = basename( OPTION_FILES );
		add_theme_page( $themename." Options", "$themename Theme Options", 'edit_themes', 'base.php', 'build_options');
	}
}
	
function build_options() {
	global $themename, $pre, $functions_path, $options;
				
	$page = $_GET["page"];
		
	include( $functions_path . '/options/' . $page );
				
	if ( 'save' == $_REQUEST['action'] ) {
					
		foreach ($options as $value) {
			if ( is_array($value['type'])) {
				foreach($value['type'] as $meta => $type){
					if($type == 'text'){
				        update_option( $meta, $_REQUEST[ $meta ]);
					}
				}                 
			}
			elseif($value['type'] != 'multicheck'){
				if(isset( $_REQUEST[ $value['id'] ])){
					update_option( $value['id'], $_REQUEST[ $value['id'] ] );} else { delete_option( $value['id'] ); }
				}
				else { 
					foreach($value['options'] as $mc_key => $mc_value){
						$up_opt = $value['id'].'_'.$mc_key;
						update_option($up_opt, $_REQUEST[$up_opt] );
					}
				} 
			}
		}	
	include( $functions_path . '/build.php' );
}
	
function build_admin_head() {
	global $functions_path;
	
	if ('themes.php' == basename($_SERVER['SCRIPT_FILENAME'])) {
		echo '<link rel="stylesheet" type="text/css" href="'.get_bloginfo('template_directory').'/library/admin/build.css" media="screen" />';
		echo '<script type="text/javascript" src="'.get_bloginfo('template_directory').'/library/script/jquery.min.js"></script>';
		?>
		<script type="text/javascript" language="javascript">
			jQuery(document).ready(function(){
				var tabNav = jQuery('.to-navsections ul li a');
				var tabContainers = jQuery('.to-sections .to-section');
				tabNav.removeClass('selected').hide().filter(':first').show().addClass('selected');
				tabNav.show();
				tabContainers.hide().filter(':first').show();
				jQuery('.to-navsections ul li a').click(function(){
					tabContainers.hide();
					tabContainers.filter(this.hash).show();
					tabNav.removeClass('selected');
					jQuery(this).addClass('selected');
					return false;
				});
						
				jQuery('#buildsave').click(function(e){
					var options_fromform = jQuery('#buildform').serialize();
					var save_button = jQuery(this);
							
					e.preventDefault();
					jQuery.ajax({
						type: "POST",
						url: "themes.php?page=base.php",
						data: options_fromform,
						success: function(response){
							save_button.html("Options Saved");
										
							save_button.blur();
							
							setTimeout(function(){
								save_button.css("background-color","#3169b6");
								save_button.html("Save Changes");
							},1500);
						},
						error: function(err){
							save_button.html("Error");
						}
					});
							
					return false;
				});
						
				jQuery('#buildsave').ajaxStart(function(){
					jQuery(this).css("background-color","#09f");
					jQuery(this).html("Saving...");
				});
			});
		</script>
		<?php
	}  //end of theme accesibility mode
}

function get_post_gallery_options($echo = false) {
	$getcat= get_categories('hide_empty=0');
	$selected_caregory = array();
	foreach ($getcat as $cat) {
		if(get_option('obscure_post_gallery_'.$cat->cat_ID) == 'true') {
			$selected_caregory[] = $cat->cat_ID;
		}
	}
	$selected_caregory = implode(",", $selected_caregory);
		
	if($echo) {
		echo $selected_caregory;
	} else {
		return $selected_caregory;
	}
}
	
add_action('admin_menu', 'startit');
add_action('admin_head', 'build_admin_head');
