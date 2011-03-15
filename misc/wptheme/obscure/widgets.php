<?php
/**
 * Contains all obscure custom widget.
 *
 * 1.
 * obscure Video Embed
 */
class obscure_widget_video_embed {
	function show_obscure_widget_video_embed() {
		$settings = get_option( 'video_embed_sidebar_widget' );
	?>
    	<li id="video_embed" class="widget-container">
			<div class="widget-content">
            <?php if($settings['video_embed_title'] <> "") : ?>
            	<h3 class="widget-title"><?php echo stripslashes($settings['video_embed_title']); ?></h3>
            <?php
				endif;
				
            	$embed = stripslashes($settings['video_embed']);
				$embed = $embed <> "" ? $embed : "No video code found!";
				$embed = preg_replace('/width="(.*?)"/', 'width="270"', $embed);
				$embed = preg_replace('/height="(.*?)"/', 'width="220"', $embed);
				
				echo $embed;
			?>
			</div>
            <div class="widget-end"><!-- empty --></div>
		</li>
    <?php
	}
	
	function obscure_widget_video_embed_control() {
		$settings = get_option( 'video_embed_sidebar_widget' );
	
		if( isset( $_POST[ 'video_embed_sidebar_widget' ] ) ) {
			$settings[ 'video_embed_title' ] = stripslashes( $_POST[ 'video_embed_title' ] );
			$settings[ 'video_embed' ] = stripslashes( $_POST[ 'video_embed' ] );
			update_option( 'video_embed_sidebar_widget', $settings );
		}
	?>
		<p>
        	<label for="video_embed_title">Title:</label><br />
            <input type="text" id="video_embed_title" name="video_embed_title" value="<?php echo $settings['video_embed_title']; ?>" /><br/><br/>
			<label for="video_embed">Input video embed code below:</label><br />
            <textarea id="video_embed" name="video_embed" cols="30" rows="10"><?php echo $settings['video_embed']; ?></textarea>
		</p>
		<input type="hidden" id="video_embed_sidebar_widget" name="video_embed_sidebar_widget" value="1" />
	<?php
	}
}
if(function_exists('register_sidebar_widget')){ register_sidebar_widget('Obscure Video Embed', array('obscure_widget_video_embed', 'show_obscure_widget_video_embed')); }
if (function_exists('register_widget_control')){ register_widget_control( 'Obscure Video Embed', array('obscure_widget_video_embed', 'obscure_widget_video_embed_control'), 300, 200 ); }


/**
 * 2.
 * Obscure site and social subscription
 */
class obscure_widget_subscription {
	function show_obscure_widget_subscription() {
		$settings = get_option( 'subscription_sidebar_widget' );
		$rss = $settings['rss'] <> "" ? $settings['rss'] : get_bloginfo("rss2_url");
		$email = $settings['email'] <> "" ? $settings['email'] : "javascript:alert('Not yet configure');";
		$twitter = $settings['twitter'] <> "" ? $settings['twitter'] : "http://twitter.com";
		$facebook = $settings['facebook'] <> "" ? $settings['facebook'] : "http://facebook.com";
	?>
    	<li id="site_social_subscription" class="widget-container">
			<div class="widget-content">
            	<?php if($settings['is_sRSS'] == "true") : ?>
            	<div class="subscription_link clearfix">
                	<img src="<?php bloginfo('template_directory'); ?>/library/images/sRSS.png" border="0" />
                    <span><a href="<?php echo $rss; ?>">Subscribe via RSS</a></span>
                </div>
                <?php endif; ?>
                
                <?php if($settings['is_sEmail'] == "true") : ?>
                <div class="subscription_link clearfix">
                	<img src="<?php bloginfo('template_directory'); ?>/library/images/sEmail.png" border="0" />
                    <span><a href="<?php echo $email; ?>">Subscribe via Email</a></span>
                </div>
                <?php endif; ?>
                
                <?php if($settings['is_sTwitter'] == "true") : ?>
                <div class="subscription_link clearfix">
                	<img src="<?php bloginfo('template_directory'); ?>/library/images/sTwitter.png" border="0" />
                    <span><a href="<?php echo $twitter; ?>">Follow us on twitter</a></span>
                </div>
                <?php endif; ?>
                
                <?php if($settings['is_sFacebook'] == "true") : ?>
                <div class="subscription_link clearfix">
                	<img src="<?php bloginfo('template_directory'); ?>/library/images/sFacebook.png" border="0" />
                    <span><a href="<?php echo $facebook; ?>">Join us on facebook</a></span>
                </div>
                <?php endif; ?>
			</div>
		</li>
    <?php
	}
	
	function obscure_widget_subscription_control() {
		$settings = get_option( 'subscription_sidebar_widget' );
	
		if( isset( $_POST[ 'subscription_sidebar_widget' ] ) ) {
			$settings[ 'rss' ] = stripslashes( $_POST[ 'rss' ] );
			$settings[ 'email' ] = stripslashes( $_POST[ 'email' ] );
			$settings[ 'twitter' ] = stripslashes( $_POST[ 'twitter' ] );
			$settings[ 'facebook' ] = stripslashes( $_POST[ 'facebook' ] );
			$settings[ 'is_sRSS' ] = stripslashes( $_POST[ 'is_sRSS' ] );
			$settings[ 'is_sEmail' ] = stripslashes( $_POST[ 'is_sEmail' ] );
			$settings[ 'is_sTwitter' ] = stripslashes( $_POST[ 'is_sTwitter' ] );
			$settings[ 'is_sFacebook' ] = stripslashes( $_POST[ 'is_sFacebook' ] );
			update_option( 'subscription_sidebar_widget', $settings );
		}
		$settings[ 'is_sRSS' ] = $settings['is_sRSS'] <> "" ? $settings['is_sRSS'] : 1;
		$settings[ 'is_sEmail' ] = $settings['is_sEmail'] <> "" ? $settings['is_sEmail'] : 1;
		$settings[ 'is_sTwitter' ] = $settings['is_sTwitter'] <> "" ? $settings['is_sTwitter'] : 1;
		$settings[ 'is_sFacebook' ] = $settings['is_sFacebook'] <> "" ? $settings['is_sFacebook'] : 1;
	?>
		<p>
        	<!-- =rss -->
			<label for="is_sRSS">Show RSS Link:</label>
            <select id="is_sRSS" name="is_sRSS">
            	<?php
				 $opt = array("false","true");
				 for($i = 1; $i >= 0; $i--) : ?>
            	<option <?php if($settings[ 'is_sRSS' ] == $opt[$i]){ echo "selected"; } ?> value="<?php echo $opt[$i]; ?>"><?php echo $opt[$i]; ?></option>
                <?php endfor; ?>
            </select><br/>
            <label for="rss">RSS Feed URL:</label><br />
            <input type="text" value="<?php echo $settings[ 'rss' ]; ?>" name="rss" id="rss" /><br /><br />
            
            <!-- =email -->
            <label for="is_sEmail">Show Email Link:</label>
            <select id="is_sEmail" name="is_sEmail">
            	<?php
				 $opt = array("false","true");
				 for($i = 1; $i >= 0; $i--) : ?>
            	<option <?php if($settings[ 'is_sEmail' ] == $opt[$i]){ echo "selected"; } ?> value="<?php echo $opt[$i]; ?>"><?php echo $opt[$i]; ?></option>
                <?php endfor; ?>
            </select><br/>
            <label for="email">Email Subscription URL:</label><br />
            <input type="text" value="<?php echo $settings[ 'email' ]; ?>" name="email" id="email" /><br /><br />
            
            <!-- =twitter -->
            <label for="is_sTwitter">Show Twitter Link:</label>
            <select id="is_sTwitter" name="is_sTwitter">
            	<?php
				 $opt = array("false","true");
				 for($i = 1; $i >= 0; $i--) : ?>
            	<option <?php if($settings[ 'is_sTwitter' ] == $opt[$i]){ echo "selected"; } ?> value="<?php echo $opt[$i]; ?>"><?php echo $opt[$i]; ?></option>
                <?php endfor; ?>
            </select><br/>
            <label for="twitter">Full Twitter URL:</label><br />
            <input type="text" value="<?php echo $settings[ 'twitter' ]; ?>" name="twitter" id="twitter" /><br /><br />
            
            <!-- =facebook -->
            <label for="is_sFacebook">Show Facebook Link:</label>
            <select id="is_sFacebook" name="is_sFacebook">
            	<?php
				 $opt = array("false","true");
				 for($i = 1; $i >= 0; $i--) : ?>
            	<option <?php if($settings[ 'is_sFacebook' ] == $opt[$i]){ echo "selected"; } ?> value="<?php echo $opt[$i]; ?>"><?php echo $opt[$i]; ?></option>
                <?php endfor; ?>
            </select><br/>
            <label for="facebook">Full Facebook URL:</label><br />
            <input type="text" value="<?php echo $settings[ 'facebook' ]; ?>" name="facebook" id="facebook" />
		</p>
		<input type="hidden" id="subscription_sidebar_widget" name="subscription_sidebar_widget" value="1" />
	<?php
	}
}
if(function_exists('register_sidebar_widget')){ register_sidebar_widget('Obscure Subscription', array('obscure_widget_subscription', 'show_obscure_widget_subscription')); }
if (function_exists('register_widget_control')){ register_widget_control( 'Obscure Subscription', array('obscure_widget_subscription', 'obscure_widget_subscription_control'), 300, 200 ); }
?>