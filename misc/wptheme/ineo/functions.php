<?php

class iNoveOptions {
function getOptions() {
$options = get_option('inove_options');
if (!is_array($options)) {
$options['keywords'] = '';
$options['description'] = '';
$options['google_cse'] = false;
$options['google_cse_cx'] = '';
$options['menu_type'] = 'pages';
$options['nosidebar'] = false;
$options['collapse'] = false;
$options['notice'] = false;
$options['notice_content'] = '';
$options['banner_registered'] = false;
$options['banner_commentator'] = false;
$options['banner_visitor'] = false;
$options['banner_content'] = '';
$options['post_banner_registered'] = false;
$options['post_banner_commentator'] = false;
$options['post_banner_visitor'] = false;
$options['post_banner_content'] = '';
$options['showcase_registered'] = false;
$options['showcase_commentator'] = false;
$options['showcase_visitor'] = false;
$options['showcase_caption'] = false;
$options['showcase_title'] = '';
$options['showcase_type'] = '4sq';
$options['showcase_content1'] = '';
$options['showcase_content2'] = '';
$options['showcase_content3'] = '';
$options['showcase_content4'] = '';
$options['showcase_content5'] = '';
$options['author'] = true;
$options['categories'] = true;
$options['tags'] = true;
$options['feed_url'] = '';
$options['feed_email'] = false;
$options['feed_url_email'] = '';
$options['social'] = false;
$options['social_name'] = 'twitter';
$options['social_username'] = '';
$options['analytics'] = false;
$options['analytics_content'] = '';
update_option('inove_options',$options);
}
return $options;
}
function add() {
if(isset($_POST['inove_save'])) {
$options = iNoveOptions::getOptions();
$options['keywords'] = stripslashes($_POST['keywords']);
$options['description'] = stripslashes($_POST['description']);
if ($_POST['google_cse']) {
$options['google_cse'] = (bool)true;
}else {
$options['google_cse'] = (bool)false;
}
$options['google_cse_cx'] = stripslashes($_POST['google_cse_cx']);
$options['menu_type'] = stripslashes($_POST['menu_type']);
if ($_POST['nosidebar']) {
$options['nosidebar'] = (bool)true;
}else {
$options['nosidebar'] = (bool)false;
}
if ($_POST['collapse']) {
$options['collapse'] = (bool)true;
}else {
$options['collapse'] = (bool)false;
}
if ($_POST['notice']) {
$options['notice'] = (bool)true;
}else {
$options['notice'] = (bool)false;
}
$options['notice_content'] = stripslashes($_POST['notice_content']);
if (!$_POST['banner_registered']) {
$options['banner_registered'] = (bool)false;
}else {
$options['banner_registered'] = (bool)true;
}
if (!$_POST['banner_commentator']) {
$options['banner_commentator'] = (bool)false;
}else {
$options['banner_commentator'] = (bool)true;
}
if (!$_POST['banner_visitor']) {
$options['banner_visitor'] = (bool)false;
}else {
$options['banner_visitor'] = (bool)true;
}
$options['banner_content'] = stripslashes($_POST['banner_content']);
if (!$_POST['post_banner_registered']) {
$options['post_banner_registered'] = (bool)false;
}else {
$options['post_banner_registered'] = (bool)true;
}
if (!$_POST['post_banner_commentator']) {
$options['post_banner_commentator'] = (bool)false;
}else {
$options['post_banner_commentator'] = (bool)true;
}
if (!$_POST['post_banner_visitor']) {
$options['post_banner_visitor'] = (bool)false;
}else {
$options['post_banner_visitor'] = (bool)true;
}
$options['post_banner_content'] = stripslashes($_POST['post_banner_content']);
if (!$_POST['showcase_registered']) {
$options['showcase_registered'] = (bool)false;
}else {
$options['showcase_registered'] = (bool)true;
}
if (!$_POST['showcase_commentator']) {
$options['showcase_commentator'] = (bool)false;
}else {
$options['showcase_commentator'] = (bool)true;
}
if (!$_POST['showcase_visitor']) {
$options['showcase_visitor'] = (bool)false;
}else {
$options['showcase_visitor'] = (bool)true;
}
if ($_POST['showcase_caption']) {
$options['showcase_caption'] = (bool)true;
}else {
$options['showcase_caption'] = (bool)false;
}
$options['showcase_type'] = stripslashes($_POST['showcase_type']);
$options['showcase_title'] = stripslashes($_POST['showcase_title']);
$options['showcase_content1'] = stripslashes($_POST['showcase_content1']);
$options['showcase_content2'] = stripslashes($_POST['showcase_content2']);
$options['showcase_content3'] = stripslashes($_POST['showcase_content3']);
$options['showcase_content4'] = stripslashes($_POST['showcase_content4']);
$options['showcase_content5'] = stripslashes($_POST['showcase_content5']);
if ($_POST['author']) {
$options['author'] = (bool)true;
}else {
$options['author'] = (bool)false;
}
if ($_POST['categories']) {
$options['categories'] = (bool)true;
}else {
$options['categories'] = (bool)false;
}
if (!$_POST['tags']) {
$options['tags'] = (bool)false;
}else {
$options['tags'] = (bool)true;
}
$options['feed_url'] = stripslashes($_POST['feed_url']);
if ($_POST['feed_email']) {
$options['feed_email'] = (bool)true;
}else {
$options['feed_email'] = (bool)false;
}
$options['feed_url_email'] = stripslashes($_POST['feed_url_email']);
if ($_POST['social']) {
$options['social'] = (bool)true;
}else {
$options['social'] = (bool)false;
}
$options['social_name'] = stripslashes($_POST['social_name']);
$options['social_username'] = stripslashes($_POST['social_username']);
if ($_POST['analytics']) {
$options['analytics'] = (bool)true;
}else {
$options['analytics'] = (bool)false;
}
$options['analytics_content'] = stripslashes($_POST['analytics_content']);
update_option('inove_options',$options);
}else {
iNoveOptions::getOptions();
}
add_theme_page(__('Current Theme Options','inove'),__('Current Theme Options','inove'),'edit_themes',basename(__FILE__),array('iNoveOptions','display'));
}
function display() {
$options = iNoveOptions::getOptions();
;echo '
<form action="#" method="post" enctype="multipart/form-data" name="inove_form" id="inove_form">
	<div class="wrap">
		<h2>';_e('Current Theme Options','inove');;echo '</h2>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						';_e('Meta','inove');;echo '						<br/>
						<small style="font-weight:normal;">';_e('Just in effect homepage','inove');;echo '</small>
					</th>
					<td>
						';_e('Keywords','inove');;echo '						<label>';_e('( Separate keywords with commas )','inove');;echo '</label><br/>
						<input type="text" name="keywords" id="keyword" class="code" size="136" value="';echo($options['keywords']);;echo '">
						<br/>
						';_e('Description','inove');;echo '						<label>';_e('( Main decription for your blog )','inove');;echo '</label>
						<br/>
						<input type="text" name="description" id="description" class="code" size="136" value="';echo($options['description']);;echo '">
					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">';_e('Search','inove');;echo '</th>
					<td>
						<label>
							<input name="google_cse" type="checkbox" value="checkbox" ';if($options['google_cse']) echo "checked='checked'";;echo ' />
							 ';_e('Using google custom search engine.','inove');;echo '						</label>
						<br/>
						';_e('CX:','inove');;echo '						 <input type="text" name="google_cse_cx" id="google_cse_cx" class="code" size="40" value="';echo($options['google_cse_cx']);;echo '">
						<br/>
						';printf(__('Find <code>name="cx"</code> in the <strong>Search box code</strong> of <a href="%1$s">Google Custom Search Engine</a>, and type the <code>value</code> here.<br/>For example: <code>014782006753236413342:1ltfrybsbz4</code>','inove'),'http://www.google.com/coop/cse/');;echo '					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">';_e('Menubar','inove');;echo '</th>
					<td>
						<label style="margin-right:20px;">
							<input name="menu_type" type="radio" value="pages" ';if($options['menu_type'] != 'categories') echo "checked='checked'";;echo ' />
							 ';_e('Show pages as menu.','inove');;echo '						</label>
						<label>
							<input name="menu_type" type="radio" value="categories" ';if($options['menu_type'] == 'categories') echo "checked='checked'";;echo ' />
							 ';_e('Show categories as menu.','inove');;echo '						</label>
					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">';_e('Sidebar','inove');;echo '</th>
					<td>
						<label>
							<input name="nosidebar" type="checkbox" value="checkbox" ';if($options['nosidebar']) echo "checked='checked'";;echo ' />
							 ';_e('Hide sidebar from all pages.','inove');;echo '						</label>
					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">';_e('Theme style','inove');;echo '</th>
					<td>
						<label>
							<input name="collapse" type="checkbox" value="checkbox" ';if($options['collapse']) echo "checked='checked'";;echo ' />
							 ';_e('Switch theme to collapse style.','inove');;echo '						</label>
					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						';_e('Notice','inove');;echo '						<br/>
						<small style="font-weight:normal;">';_e('HTML enabled','inove');;echo '</small>
					</th>
					<td>
						<!-- notice START -->
						<label>
							<input name="notice" type="checkbox" value="checkbox" ';if($options['notice']) echo "checked='checked'";;echo ' />
							 ';_e('This notice bar will display at the top of posts on homepage.','inove');;echo '						</label>
						<br />
						<label>
							<textarea name="notice_content" id="notice_content" cols="50" rows="10" style="width:98%;font-size:12px;" class="code">';echo($options['notice_content']);;echo '</textarea>
						</label>
						<!-- notice END -->
					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						';_e('Title Banner','inove');;echo '						<br/>
						<small style="font-weight:normal;">';_e('HTML enabled','inove');;echo '</small>
					</th>
					<td>
						<!-- banner START -->
						';_e('This banner will display at the right of header. (height: 60 pixels)','inove');;echo '						<br/>
						';_e('Who can see?','inove');;echo '						<label style="margin-left:10px;">
							<input name="banner_registered" type="checkbox" value="checkbox" ';if($options['banner_registered']) echo "checked='checked'";;echo ' />
							 ';_e('Registered Users','inove');;echo '						</label>
						<label style="margin-left:10px;">
							<input name="banner_commentator" type="checkbox" value="checkbox" ';if($options['banner_commentator']) echo "checked='checked'";;echo ' />
							 ';_e('Commentator','inove');;echo '						</label>
						<label style="margin-left:10px;">
							<input name="banner_visitor" type="checkbox" value="checkbox" ';if($options['banner_visitor']) echo "checked='checked'";;echo ' />
							 ';_e('Visitors','inove');;echo '						</label>
						<br/>
						<label>
							<textarea name="banner_content" id="banner_content" cols="50" rows="10" style="width:98%;font-size:12px;" class="code">';echo($options['banner_content']);;echo '</textarea>
						</label>
						<!-- banner END -->
					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						';_e('Post Banner','inove');;echo '						<br/>
						<small style="font-weight:normal;">';_e('HTML enabled','inove');;echo '</small>
					</th>
					<td>
						<!-- post banner START -->
						';_e('This showcase will display at the bottom of post. (width: 300 pixels)','inove');;echo '						<br/>
						';_e('Who can see?','inove');;echo '						<label style="margin-left:10px;">
							<input name="post_banner_registered" type="checkbox" value="checkbox" ';if($options['post_banner_registered']) echo "checked='checked'";;echo ' />
							 ';_e('Registered Users','inove');;echo '						</label>
						<label style="margin-left:10px;">
							<input name="post_banner_commentator" type="checkbox" value="checkbox" ';if($options['post_banner_commentator']) echo "checked='checked'";;echo ' />
							 ';_e('Commentator','inove');;echo '						</label>
						<label style="margin-left:10px;">
							<input name="post_banner_visitor" type="checkbox" value="checkbox" ';if($options['post_banner_visitor']) echo "checked='checked'";;echo ' />
							 ';_e('Visitors','inove');;echo '						</label>
						<br/>
						<label>
							<textarea name="post_banner_content" id="post_banner_content" cols="50" rows="10" style="width:98%;font-size:12px;" class="code">';echo($options['post_banner_content']);;echo '</textarea>
						</label>
						<!-- post banner END -->
					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						';_e('Sidebar Showcase','inove');;echo '						<br/>
						<small style="font-weight:normal;">';_e('HTML enabled','inove');;echo '</small>
					</th>
					<td>
						<!-- sidebar showcase START -->
						';_e('This showcase will display at the top of sidebar.','inove');;echo '						<br/>
						';_e('Who can see?','inove');;echo '						<label style="margin-left:10px;">
							<input name="showcase_registered" type="checkbox" value="checkbox" ';if($options['showcase_registered']) echo "checked='checked'";;echo ' />
							 ';_e('Registered Users','inove');;echo '						</label>
						<label style="margin-left:10px;">
							<input name="showcase_commentator" type="checkbox" value="checkbox" ';if($options['showcase_commentator']) echo "checked='checked'";;echo ' />
							 ';_e('Commentator','inove');;echo '						</label>
						<label style="margin-left:10px;">
							<input name="showcase_visitor" type="checkbox" value="checkbox" ';if($options['showcase_visitor']) echo "checked='checked'";;echo ' />
							 ';_e('Visitors','inove');;echo '						</label>
						<br/>
						<label>
							<input name="showcase_caption" type="checkbox" value="checkbox" ';if($options['showcase_caption']) echo "checked='checked'";;echo ' />
							 ';_e('Title:','inove');;echo '						</label>
						 <input type="text" name="showcase_title" id="showcase_title" class="code" size="40" value="';echo($options['showcase_title']);;echo '" />
						<br/>
						<label>
							<input name="showcase_type" type="radio" value="4sq" ';if($options['showcase_type'] != '1sq') echo "checked='checked'";;echo ' />
							 ';_e('Show 4 squares showcase content. (square size: 125 x 125 pixels)','inove');;echo '						</label>
						<br/>
						<label>
							';_e('Squre1:','inove');;echo ' <textarea name="showcase_content1" id="showcase_content1" cols="50" rows="2" style="width:98%;font-size:12px;" class="code">';echo($options['showcase_content1']);;echo '</textarea>
						</label>
						<label>
							';_e('Squre2:','inove');;echo ' <textarea name="showcase_content2" id="showcase_content2" cols="50" rows="2" style="width:98%;font-size:12px;" class="code">';echo($options['showcase_content2']);;echo '</textarea>
						</label>
						<label>
							';_e('Squre3:','inove');;echo ' <textarea name="showcase_content3" id="showcase_content3" cols="50" rows="2" style="width:98%;font-size:12px;" class="code">';echo($options['showcase_content3']);;echo '</textarea>
						</label>
						<label>
							';_e('Squre4:','inove');;echo ' <textarea name="showcase_content4" id="showcase_content4" cols="50" rows="2" style="width:98%;font-size:12px;" class="code">';echo($options['showcase_content4']);;echo '</textarea>
						</label>
						<label>
							<input name="showcase_type" type="radio" value="1sq" ';if($options['showcase_type'] == '1sq') echo "checked='checked'";;echo ' />
							 ';_e('Show single showcase content. (width: 250 pixels)','inove');;echo '						</label>
						<br/>
						<label>
							<textarea name="showcase_content5" id="showcase_content5" cols="50" rows="10" style="width:98%;font-size:12px;" class="code">';echo($options['showcase_content5']);;echo '</textarea>
						</label>
						<!-- sidebar showcase END -->
					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">';_e('Posts','inove');;echo '</th>
					<td>
						<label style="margin-right:20px;">
							<input name="author" type="checkbox" value="checkbox" ';if($options['author']) echo "checked='checked'";;echo ' />
							 ';_e('Show author on posts.','inove');;echo '						</label>
						<label style="margin-right:20px;">
							<input name="categories" type="checkbox" value="checkbox" ';if($options['categories']) echo "checked='checked'";;echo ' />
							 ';_e('Show categories on posts.','inove');;echo '						</label>
						<label>
							<input name="tags" type="checkbox" value="checkbox" ';if($options['tags']) echo "checked='checked'";;echo ' />
							 ';_e('Show tags on posts.','inove');;echo '						</label>
					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">';_e('Feed','inove');;echo '</th>
					<td>
						 ';_e('Custom feed URL:','inove');;echo ' <input type="text" name="feed_url" id="feed_url" class="code" size="60" value="';echo($options['feed_url']);;echo '">
						<br/>
						<label>
							<input name="feed_email" type="checkbox" value="checkbox" ';if($options['feed_email']) echo "checked='checked'";;echo ' />
							 ';_e('Show email feed in reader list.','inove');;echo '						</label>
						<br />
						 ';_e('Email feed URL:','inove');;echo ' <input type="text" name="feed_url_email" id="feed_url_email" class="code" size="60" value="';echo($options['feed_url_email']);;echo '">
					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">';_e('Social','inove');;echo '</th>
					<td>
						<label>
							<input name="social" type="checkbox" value="checkbox" ';if($options['social']) echo "checked='checked'";;echo ' />
							';_e('Add Social button.','blocks');;echo '						</label>
						<br />
						<div>
							';_e('Select Social.','blocks');;echo '							<select name="social_name" size="1">
								<option value="twitter" ';if($options['social_name'] == 'twitter') echo ' selected ';;echo '>';_e('Twitter','blocks');;echo '</option>
								<option value="tencent" ';if($options['social_name'] != 'twitter'&&$options['social_name'] != 'sina') echo ' selected ';;echo '>';_e('Tencent','blocks');;echo '</option>
								<option value="sina" ';if($options['social_name'] == 'sina') echo ' selected ';;echo '>';_e('Sina','blocks');;echo '</option>
							</select>
						</div>
						 ';_e('Social username:','inove');;echo '						 <input type="text" name="social_username" id="social_username" class="code" size="40" value="';echo($options['social_username']);;echo '">
						<br />
						<a href="http://t.sina.com.cn/neoner/" onclick="window.open(this.href);return false;">Follow NeOne</a>
						 | <a href="http://twitter.com/jevonszhou/" onclick="window.open(this.href);return false;">Follow Jevons Zhou</a>
					</td>
				</tr>
			</tbody>
		</table>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						';_e('Web Analytics','inove');;echo '						<br/>
						<small style="font-weight:normal;">';_e('HTML enabled','inove');;echo '</small>
					</th>
					<td>
						<label>
							<input name="analytics" type="checkbox" value="checkbox" ';if($options['analytics']) echo "checked='checked'";;echo ' />
							 ';_e('Add web analytics code to your site. (e.g. Google Analytics, Yahoo! Web Analytics, ...)','inove');;echo '						</label>
						<label>
							<textarea name="analytics_content" cols="50" rows="10" id="analytics_content" class="code" style="width:98%;font-size:12px;">';echo($options['analytics_content']);;echo '</textarea>
						</label>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input class="button-primary" type="submit" name="inove_save" value="';_e('Save Changes','inove');;echo '" />
		</p>
	</div>
</form>

';
}
}
add_action('admin_menu',array('iNoveOptions','add'));
function theme_init(){
load_theme_textdomain('inove',get_template_directory() .'/languages');
}
add_action ('init','theme_init');
if( function_exists('register_sidebar') ) {
register_sidebar(array(
'name'=>'north_sidebar',
'before_widget'=>'<div id="%1$s" class="widget %2$s">',
'after_widget'=>'</div>',
'before_title'=>'<div class="title">',
'after_title'=>'</div>'
));
register_sidebar(array(
'name'=>'south_sidebar',
'before_widget'=>'<div id="%1$s" class="widget %2$s">',
'after_widget'=>'</div>',
'before_title'=>'<div class="title">',
'after_title'=>'</div>'
));
register_sidebar(array(
'name'=>'west_sidebar',
'before_widget'=>'<div id="%1$s" class="%2$s">',
'after_widget'=>'</div>',
'before_title'=>'<div class="title">',
'after_title'=>'</div>'
));
register_sidebar(array(
'name'=>'east_sidebar',
'before_widget'=>'<div id="%1$s" class="%2$s">',
'after_widget'=>'</div>',
'before_title'=>'<div class="title">',
'after_title'=>'</div>'
));
}
remove_action('wp_head','wp_generator');
remove_action('wp_head','wlwmanifest_link');
remove_action('wp_head','rsd_link');
remove_action('wp_head','index_rel_link');
remove_action('wp_head','wp_shortlink_wp_head',10,0 );
remove_action('wp_head','feed_links_extra',3 );
remove_action('wp_head','start_post_rel_link');
remove_action('wp_head','adjacent_posts_rel_link_wp_head',10,0);
add_action('widgets_init','my_remove_recent_comments_style');
wp_deregister_script('l10n');
function my_remove_recent_comments_style() {
global $wp_widget_factory;
remove_action('wp_head',array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'],'recent_comments_style'));
}
if( !is_admin()){
wp_deregister_script('jquery');
wp_register_script('jquery',("http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"),false,'1.4.2');
wp_enqueue_script('jquery');
}
function load_post() {
if($_GET['action'] == 'load_post'&&$_GET['id'] != '') {
$id = $_GET["id"];
$output = '';
global $wpdb,$post;
$post = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d LIMIT 1",$id));
if($post) {
$content = $post->post_content;
$output = balanceTags($content);
$output = wpautop($output);
$output = convert_smilies($output);
}
echo $output;
die();
}
}
add_action('init','load_post');
function add_nofollow_to_link($link) {
return str_replace('<a','<a rel="nofollow"',$link);
}
add_filter('the_content_more_link','add_nofollow_to_link',0);
function add_nofollow_to_comments_popup_link(){
return ' rel="nofollow" ';
}
add_filter('comments_popup_link_attributes','add_nofollow_to_comments_popup_link');
if (function_exists('wp_list_comments')) {
function comment_count( $commentcount ) {
global $id;
$_comments = get_comments('status=approve&post_id='.$id);
$comments_by_type = &separate_comments($_comments);
return count($comments_by_type['comment']);
}
}
function load_comment(){
if($_GET['action'] =='load_comment'&&$_GET['id'] != ''){
$comment = get_comment($_GET['id']);
if(!$comment) {
fail(printf('Whoops! Can\'t find the comment with id  %1$s',$_GET['id']));
}
custom_comments($comment,null,null);
die();
}
}
add_action('init','load_comment');
function custom_comments($comment,$args,$depth) {
$GLOBALS['comment'] = $comment;
global $commentcount;
if(!$commentcount) {
$page = get_query_var('cpage')-1;
$cpp=get_option('comments_per_page');
$commentcount = $cpp * $page;
if ($commentcount <0) $commentcount = 0;
}
;echo '	<li class="hreview clearfix ';if($comment->comment_author_email == get_the_author_email()) {echo 'admincomment';}else {echo 'evencomment';};echo '" id="comment-';comment_ID() ;echo '">
		<div class="info clearfix">
			';if (function_exists('get_avatar') &&get_option('show_avatars')) {echo get_avatar($comment,32);};echo '			';if (get_comment_author_url()) : ;echo '				<a class="reviewer" id="reviewer-';comment_ID() ;echo '" href="';comment_author_url() ;echo '" rel="external nofollow">
			';else : ;echo '				<span class="reviewer" id="reviewer-';comment_ID() ;echo '">
			';endif;;echo '
			';comment_author();;echo '
			';if(get_comment_author_url()) : ;echo '				</a>
			';else : ;echo '				</span>
			';endif;;echo '				 | <a class="anchor" rel="nofollow" href="#comment-';comment_ID() ;echo '">';printf('#%1$s',++$commentcount);;echo '</a>
			<div class="dtreviewed">';printf( __('%1$s at %2$s','inove'),get_comment_time(__('F jS, Y','inove')),get_comment_time(__('H:i','inove')) );;echo '</div>
		</div>

		';if ($comment->comment_approved == '0') : ;echo '			<p><small>';_e('Your comment is awaiting moderation.','inove');;echo '</small></p>
		';endif;;echo '		
		<div class="description" id="commentbody-';comment_ID() ;echo '">
			';comment_text();;echo '		</div>

';
}
?>