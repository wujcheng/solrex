<?php
/**
 * The loop that displays posts.
 *
 * The loop displays the posts and the post content.  See
 * http://codex.wordpress.org/The_Loop to understand it and
 * http://codex.wordpress.org/Template_Tags to understand
 * the tags used in it.
 *
 * @package WordPress
 * @subpackage Obscure
 */
?>

<?php /* If there are no posts to display, such as an empty archive page */ ?>
<?php if ( ! have_posts() ) : ?>
	<div id="post-0" class="post error404 not-found">
		<h1 class="entry-title"><?php _e( 'Not Found', 'obscure' ); ?></h1>
		<div class="entry-content">
			<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'obscure' ); ?></p>
			<?php get_search_form(); ?>
		</div><!-- .entry-content -->
	</div><!-- #post-0 -->
<?php endif; ?>

<?php
	/* Start the Loop.
	 *
	 * In Obscure we use the same loop in multiple contexts.
	 * It is broken into three main parts: when we're displaying
	 * posts that are in the gallery category, when we're displaying
	 * posts in the asides category, and finally all other posts.
	 *
	 * Additionally, we sometimes check for whether we are on an
	 * archive page, a search page, etc., allowing for small differences
	 * in the loop on each template without actually duplicating
	 * the rest of the loop that is shared.
	 *
	 * Without further ado, the loop:
	 */ ?>
<?php while ( have_posts() ) : the_post(); ?>

<?php /* How to display posts in the Gallery category. */ ?>

	<?php if ( in_category( _x('gallery', 'gallery category slug', 'obscure') ) ) : ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'obscure' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

			<div class="entry-meta">
				<?php obscure_post_utility(); ?>
			</div><!-- .entry-meta -->

			<div class="entry-content">
<?php if ( post_password_required() ) : ?>
				<?php the_content(); ?>
<?php else : ?>
				<div class="gallery-thumb">
<?php
	$images = get_children( array( 'post_parent' => $post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order', 'order' => 'ASC', 'numberposts' => 999 ) );
	$total_images = count( $images );
	$image = array_shift( $images );
	$image_img_tag = wp_get_attachment_image( $image->ID, 'thumbnail' );
?>
					<a class="size-thumbnail" href="<?php the_permalink(); ?>"><?php echo $image_img_tag; ?></a>
				</div><!-- .gallery-thumb -->
				<p><em><?php printf( __( 'This gallery contains <a %1$s>%2$s photos</a>.', 'obscure' ),
						'href="' . get_permalink() . '" title="' . sprintf( esc_attr__( 'Permalink to %s', 'obscure' ), the_title_attribute( 'echo=0' ) ) . '" rel="bookmark"',
						$total_images
					); ?></em></p>

				<?php the_excerpt( '' ); ?>
<?php endif; ?>
			</div><!-- .entry-content -->

			<div class="entry-utility">
				<a href="<?php echo get_term_link( _x('gallery', 'gallery category slug', 'obscure'), 'category' ); ?>" title="<?php esc_attr_e( 'View posts in the Gallery category', 'obscure' ); ?>"><?php _e( 'More Galleries', 'obscure' ); ?></a>
				<span class="meta-sep">|</span>
				<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'obscure' ), __( '1 Comment', 'obscure' ), __( '% Comments', 'obscure' ) ); ?></span>
				<?php edit_post_link( __( 'Edit', 'obscure' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
			</div><!-- .entry-utility -->
		</div><!-- #post-## -->

<?php /* How to display all other posts. */ ?>

	<?php else : ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        	<div class="entry-head clearfix">
                <h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'obscure' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                <div class="entry-meta"><?php obscure_post_meta(); ?></div>
            </div><!-- .entry-head -->
            
	<?php if ( is_home() || is_archive() || is_search() ) : // Only display excerpts with thumbnail for homepage or frontpage. ?>
    		<div class="entry-summary">
            	<?php if(has_post_thumbnail( $post->ID )) :
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'post-thumbnail' );
				?>
                <img class="alignleft" src="<?php bloginfo("template_directory"); ?>/timthumb.php?src=<?php echo $image[0]; ?>&w=150&h=150&zc=1" border="0" />
				<?php 
					endif;
					the_excerpt( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'obscure' ) ); ?>
			</div><!-- .entry-summary -->
	<?php else : ?>
			<div class="entry-content">
				<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'obscure' ) ); ?>
				<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'obscure' ), 'after' => '</div>' ) ); ?>
			</div><!-- .entry-content -->
	<?php endif; ?>

			<div class="entry-utility clearfix">
            	<?php obscure_post_utility(); ?>
			</div><!-- .entry-utility -->
		</div><!-- #post-## -->

		<?php comments_template( '', true ); ?>

	<?php endif; // This was the if statement that broke the loop into three parts based on categories. ?>

<?php endwhile; // End the loop. Whew. ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php if (  $wp_query->max_num_pages > 1 ) : 
	if(function_exists(wp_pagenavi)) : wp_pagenavi(); else : ?>
				<div id="nav-below" class="navigation clearfix">
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Previous Entries', 'newspress' ) ); ?></div>
					<div class="nav-next"><?php previous_posts_link( __( 'Next Entries <span class="meta-nav">&rarr;</span>', 'newspress' ) ); ?></div>
				</div><!-- #nav-below -->
<?php endif; endif; ?>
