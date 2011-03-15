<?php
/**
 * The featured posts template file.
 *
 *
 * @package WordPress
 * @subpackage Obscure
 */
?>
<?php if(get_option("obscure_featured_type") <> "opt_3") : ?>
	<?php if(get_option("obscure_featured_type") == "opt_0") : ?>
        <div id="featured" class="clearfix">
                <?php $featured = new WP_Query('showposts=5&cat=' . get_post_gallery_options());
                if ($featured->have_posts()) : $nothing_yet = true; ?>
                    <div class="hook-title">Featured Post<?php if($featured->post_count > 1) { echo 's'; }?></div>
                    <div class="slider">
                    <?php while ($featured->have_posts()) : $featured->the_post(); 
                            if(has_post_thumbnail( $post->ID )) :
								$nothing_yet = false;
                                $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' ); ?>
                                
                                <a href="<?php the_permalink(); ?>">
                                    <img alt="<?php the_title(); ?>" src="<?php bloginfo("template_directory"); ?>/timthumb.php?src=<?php echo $image[0]; ?>&w=608&h=280&zc=1" width="608" height="280" class="post_image" />
                                    <div><span><?php the_title(); ?></span></div>
                                </a><!-- end -->
                        <?php
                            endif;
							if($nothing_yet) {
								echo '<img src="'. get_bloginfo('template_directory') .'/library/images/nothing.png" border="0" align="nothing in here yet" />';
							}
                        endwhile; ?>
                    </div><!-- end .slider -->
                    <div id="slider-nav"><!-- slider navigation --></div>
                <?php endif; ?>
        </div>
	<?php elseif(get_option("obscure_featured_type") == "opt_1") : ?>
		<div id="single_video">
			<?php 
				$video_code = stripslashes(get_option("obscure_single_video"));
				$video_code = $video_code <> "" ? $video_code : "No video code found!";
				$video_code = preg_replace('/width="(.*?)"/', 'width="608"', $video_code);
				$video_code = preg_replace('/height="(.*?)"/', 'height="280"', $video_code);
				
				echo $video_code;
			?>
		</div>
    <?php endif; ?>
<?php endif; ?>