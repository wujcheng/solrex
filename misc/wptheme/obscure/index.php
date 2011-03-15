<?php
/**
 * The main template file.
 *
 *
 * @package WordPress
 * @subpackage Obscure
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

			<?php
			/* Call the featured posts.
			 */
			 get_template_part( 'featured' );
			
			/* Run the loop to output the posts.
			 * If you want to overload this in a child theme then include a file
			 * called loop-index.php and that will be used instead.
			 */
			 get_template_part( 'loop', 'index' );
			?>
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
