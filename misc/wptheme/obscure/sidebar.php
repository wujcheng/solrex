<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package WordPress
 * @subpackage Obscure
 */
?>

		<div id="sidebar" class="widget-area" role="complementary">
			<ul class="xoxo">

<?php
	/* When we call the dynamic_sidebar() function, it'll spit out
	 * the widgets for that widget area. If it instead returns false,
	 * then the sidebar simply doesn't exist, so we'll hard-code in
	 * some default sidebar stuff just in case.
	 */
	if ( ! dynamic_sidebar( 'primary-widget-area' ) ) : ?>

			<li id="recent_posts" class="widget-container">
				<div class="widget-content">
                    <h3 class="widget-title"><?php _e( 'Recent Posts', 'obscure' ); ?></h3>
                    <ul>
                        <?php wp_get_archives( 'type=postbypost&limit=5' ); ?>
                    </ul>
                </div>
                <div class="widget-end"><!-- empty --></div>
			</li>

			<li id="meta" class="widget-container">
            	<div class="widget-content">
                    <h3 class="widget-title"><?php _e( 'Categories', 'obscure' ); ?></h3>
                    <ul>
                        <?php wp_list_categories( 'title_li=&exclude=1' ); ?>
                    </ul>
				</div>
                <div class="widget-end"><!-- empty --></div>
			</li>
            
            <li id="archives" class="widget-container">
				<div class="widget-content">
                    <h3 class="widget-title"><?php _e( 'Archives', 'obscure' ); ?></h3>
                    <ul>
                        <?php wp_get_archives( 'type=monthly&limit=5' ); ?>
                    </ul>
                </div>
                <div class="widget-end"><!-- empty --></div>
			</li>

		<?php endif; // end primary widget area ?>
			</ul>
		</div><!-- #sidebar .widget-area -->
