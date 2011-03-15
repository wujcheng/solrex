<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the wordpress construct of pages
 * and that other 'pages' on your wordpress site will use a
 * different template.
 *
 * @package WordPress
 * @subpackage Obscure
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">
            <?php /* Display breadcrumb trail */ ?>
			<div id="breadcrumb">
				<?php include("breadcrumb.php"); ?>
			</div><!-- #breadcrumb -->

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <div class="entry-head clearfix">
                        <h2 class="entry-title"><?php the_title(); ?></h2>
                    </div>	

					<div class="entry-content">
						<?php the_content(); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'obscure' ), 'after' => '</div>' ) ); ?>
						<?php edit_post_link( __( 'Edit content &raquo;', 'obscure' ), '<span class="edit-link">', '</span>' ); ?>
					</div><!-- .entry-content -->
				</div><!-- #post-## -->

<?php endwhile; ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
