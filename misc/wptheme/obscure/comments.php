<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form.  The actual display of comments is
 * handled by a callback to obscure_comment which is
 * located in the functions.php file.
 *
 * @package WordPress
 * @subpackage Obscure
 */
?>

			<div id="comments">
<?php if ( post_password_required() ) : ?>
				<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'obscure' ); ?></p>
			</div><!-- #comments -->
<?php
		/* Stop the rest of comments.php from being processed,
		 * but don't kill the script entirely -- we still have
		 * to fully load the template.
		 */
		return;
	endif;
?>

<?php
	// You can start editing here -- including this comment!
?>

<?php if ( have_comments() ) : ?>
			<h3 id="comments-title"><?php
			printf( _n( 'One Response to %2$s', '%1$s Responses to %2$s', get_comments_number(), 'obscure' ),
			number_format_i18n( get_comments_number() ), get_the_title() );
			?></h3>

			<ol class="commentlist">
				<?php
					/* Loop through and list the comments. Tell wp_list_comments()
					 * to use obscure_comment() to format the comments.
					 * See obscure_comment() in obscure/functions.php for more.
					 */
					wp_list_comments( array( 'callback' => 'obscure_comment' ) );
				?>
			</ol>

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'obscure' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'obscure' ) ); ?></div>
			</div><!-- .navigation -->
<?php endif; // check for comment navigation ?>

<?php else : // or, if we don't have comments:

	/* If there are no comments and comments are closed,
	 * let's leave a little note, shall we?
	 */
	if ( ! comments_open() ) :
?>
	<p class="nocomments"><?php _e( 'Comments are closed.', 'obscure' ); ?></p>
<?php else: ?>
	<p class="nocomments"><?php _e( 'Still quiet here.', 'obscure' ); ?>sas</p>
<?php endif; // end ! comments_open() ?>
<?php endif; // end have_comments() ?>

<?php if ( comments_open() ) : ?>
<div id="respond">
	<h3 id="reply-title"><?php comment_form_title( 'Leave a Response', 'Leave a Response to %s' ); ?></h3>
	<div class="cancel-comment-reply">
		<small><?php cancel_comment_reply_link(); ?></small>
	</div>
	<div class="respond-form clearfix">
		<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
			You must be <a href="<?php echo wp_login_url( get_permalink() ); ?>">logged in</a> to post a comment.
		<?php else : ?>
			<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="comment-form">
			<?php if ( is_user_logged_in() ) : ?>
			<p style="text-transform:uppercase; ">Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account">Log out &raquo;</a></p>
			<?php else : ?>
			<p><input class="author" onFocus="this.style.backgroundColor='#f5f5f5'" onBlur="this.style.backgroundColor='#fff'" type="text" name="author" id="author" value="" size="22" tabindex="1" /></p>
			<p><input class="email" onFocus="this.style.backgroundColor='#f5f5f5'" onBlur="this.style.backgroundColor='#fff'" type="text" name="email" id="email" value="" size="22" tabindex="2" /></p>
			<p><input class="url" onFocus="this.style.backgroundColor='#f5f5f5'" onBlur="this.style.backgroundColor='#fff'" type="text" name="url" id="url" value="" size="22" tabindex="3" /></p>
			<?php endif; ?>
			<p><textarea onFocus="this.style.backgroundColor='#f5f5f5'" onBlur="this.style.backgroundColor='#fff'" name="comment" id="comment" tabindex="4"></textarea></p>
			<input name="submit" type="submit" class="btn blue submit-btn" tabindex="5" value="Submit Comment" /><input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
			<?php comment_id_fields(); ?>
			<?php do_action('comment_form', $post->ID); ?>
			</form>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

</div><!-- #comments -->
