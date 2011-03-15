<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Obscure
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 * We filter the output of wp_title() a bit -- see
	 * obscure_filter_wp_title() in functions.php.
	 */
	wp_title( '|', true, 'right' );

	?></title>
<?php
	/* Adding meta tags for better SEO
	 * obscure_create_metadata() in functions.php.
	 */
	obscure_create_metadata();
?>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<!--[if IE]>
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'template_directory' ); ?>/style-ie.css" />
<![endif]-->
<?php
	/* Adding custom css file for customization if enabled
	 * in theme option.
	 */
	if(get_option("obscure_customcss") <> "") {
		echo '<link rel="stylesheet" type="text/css" media="all" href="'.get_bloginfo("template_directory").'/custom/custom.css" />';
	}
?>
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php
	/* We add some JavaScript to pages with the comment form
	 * to support sites with threaded comments (when in use).
	 */
	if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	/*
	 * Adding robots and canonical url filter for better SEO
	 * obscure_create_robots() and obscure_canonical_url() in functions.php.
	 */
	 obscure_create_robots();
	 obscure_canonical_url();
	
	/* Always have wp_head() just before the closing </head>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to add elements to <head> such
	 * as styles, scripts, and meta tags.
	 */
	wp_head();
?>
</head>

<body <?php body_class(); ?>>
<div id="wrapper" class="hfeed">
	<div id="header">
		<div id="masthead">
        	<div id="top-menu" class="access" role="navigation-top">
            	<?php /*  Allow screen readers / text browsers to skip the navigation menu and get right to the good stuff */ ?>
				<div class="skip-link screen-reader-text"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'obscure' ); ?>"><?php _e( 'Skip to content', 'obscure' ); ?></a></div>
				<?php /* Our navigation menu.  If one isn't filled out, wp_nav_menu falls back to wp_page_menu.  The menu assiged to the primary position is the one used.  If none is assigned, the menu with the lowest ID is used.  */ ?>
				<?php wp_nav_menu( array( 'container_class' => 'menu', 'menu_class' => 'sf-menu', 'theme_location' => 'primary-menu' ) ); ?>
            </div><!-- #top-menu -->
        
			<div id="branding" class="clearfix" role="banner">
				<div id="site-logo" class="left">
                	<?php
                    	if(get_option("obscure_customlogo") <> "") {
							$logo_img = get_option("obscure_customlogo");
						} else {
							$logo_img = get_bloginfo('template_directory') . "/library/images/logo.png";
						}
					?>
                	<a href="<?php bloginfo("url"); ?>" title="Homepage"><img src="<?php echo $logo_img; ?>" border="0" /></a>
                </div><!-- #site logo -->
                <?php if(get_option("obscure_enable_banner") <> "") : ?>
                <div id="site-ads" class="right">
                	<?php if(get_option("obscure_code_banner") <> "") :
								echo get_option("obscure_code_banner");
						  else :?>
                    		<a href="mailto:<?php bloginfo('admin_email'); ?>" title="Advertise Here"><img src="<?php bloginfo('template_directory'); ?>/library/images/ads-top.jpg" border="0" /></a>
                    <?php endif; ?>
						
                </div><!-- #banner ads -->
                <?php endif; ?>
			</div><!-- #branding -->
			<div id="bottom-menu" class="access" role="navigation-below">
				<?php /*  Allow screen readers / text browsers to skip the navigation menu and get right to the good stuff */ ?>
				<div class="skip-link screen-reader-text"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'obscure' ); ?>"><?php _e( 'Skip to content', 'obscure' ); ?></a></div>
				<?php /* Our navigation menu.  If one isn't filled out, wp_nav_menu falls back to wp_page_menu.  The menu assiged to the primary position is the one used.  If none is assigned, the menu with the lowest ID is used.  */ ?>
				<?php
					function wp_category_menu() {
						echo "<div class='menu'><ul class='sf-menu-2'>";
						wp_list_categories('title_li=&exclude=1');
						echo "</ul></div>";
					}
					wp_nav_menu( array( 'container_class' => 'menu', 'menu_class' => 'sf-menu-2', 'theme_location' => 'secondary-menu', 'fallback_cb' => 'wp_category_menu' ) ); 
				?>
			</div><!-- #bottom-menu -->
		</div><!-- #masthead -->
	</div><!-- #header -->

	<div id="main" class="clearfix">
