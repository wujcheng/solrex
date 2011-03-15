<?php

	/*	DEFINE
	---------------------------
	*/
	$getcat= get_categories('hide_empty=0');
	$allcaregory = array();
	foreach ($getcat as $cat) {
		$allcaregory[$cat->cat_ID] = $cat->cat_name;
	}
		
	/*	GENERAL SETTINGS
	---------------------------
	*/
	$options[] = array(	"name" => "General Settings",
						"id" => "general",
						"type" => "section");
		$options[] = array(	"name" => "Customize Your Design",
						"label" => "Use Custom Stylesheet",
						"description" => "Check if you want to make customize design changes. Use <span class='emp'>&quot;/custom/custom.css&quot;</span> file if enabled.",
						"id" => $pre."_customcss",
						"std" => "false",
						"type" => "checkbox");
		$options[] = array(	"name" => "Custom Logo",
						"description" => "Specify the image address of your online logo. ( http://yoursite.com/logo.png )",
						"id" => $pre."_customlogo",
						"type" => "text");
		$options[] = array(	"name" => "Syndication ( FEED )",
						"description" => "If you are using a service like Feedburner to manage your RSS feed, enter full URL to your feed into box above. If you'd prefer to use the default WordPress feed, simply leave this box blank.",
						"id" => $pre."_syndication",
						"type" => "text");
	$options[] = array(	"type" => "sectionbreak");
	
	
	
	/*	SEO
	---------------------------
	*/
	$options[] = array(	"name" => "SEO Options",
						"id" => "seo",
						"type" => "section");
		$options[] = array(	"name" => "Canonical URLs",
						"label" => "Canonical URLS",
						"description" => "Check if you want to add canonical URLs to your site.",
						"id" => $pre."_canonical",
						"std" => "false",
						"type" => "checkbox");
		$options[] = array(	"name" => "Meta Description",
						"description" => "You should use meta descriptions to provide search engines with additional information about topics that appear on your site. This only applies to your home page.",
						"id" => $pre."_meta_description",
						"type" => "textarea");
		$options[] = array(	"name" => "Meta Keywords (comma separated)",
						"description" => "Meta keywords are rarely used nowadays but you can still provide search engines with additional information about topics that appear on your site. This only applies to your home page.",
						"id" => $pre."_meta_keywords",
						"type" => "text");
		$options[] = array(	"name" => "Meta Author",
						"description" => "You should write your <span class='emp'>&quot;full name&quot;</span> here but only do so if this blog is writen only by one outhor. This only applies to your home page.",
						"id" => $pre."_meta_author",
						"type" => "text");
	$options[] = array(	"type" => "sectionbreak");
	
	
	/*	Advertisement
	---------------------------
	*/
	$options[] = array(	"name" => "Advertisement",
						"id" => "advertisement",
						"type" => "section");
		$options[] = array(	"name" => "Advertisement",
						"label" => "Enable advertisement",
						"description" => "Check if you want to enable banner size ( 468x60 ) advertisement in header.",
						"id" => $pre."_enable_banner",
						"std" => "false",
						"type" => "checkbox");
		$options[] = array(	"name" => "Banner embed code",
						"description" => "Input the advertisement code here to appear in header. Leave blank to show default banner image",
						"id" => $pre."_code_banner",
						"type" => "textarea");
	$options[] = array(	"type" => "sectionbreak");
	
	
	/*	FEATURED
	---------------------------
	*/
	$presentation = array('POST GALLERY','SINGLE VIDEO','NONE');
	$options[] = array(	"name" => "Featured Panel",
						"id" => "featuredpanel",
						"type" => "section");
		$options[] = array(	"name" => "Type of Presentation",
						"description" => "Select type of presentation to be displayed on the top of index posts",
						"id" => $pre."_featured_type",
						"std" => "false",
						"options" => $presentation,
						"type" => "radio");
		$options[] = array(	"name" => "Featured Gallery",
						"description" => "If <strong>GALLERY</strong> is selected then select catogory / categories of post you want to set for your featured gallery.",
						"id" => $pre."_post_gallery",
						"std" => "false",
						"options" => $allcaregory,
						"type" => "multicheck");
		$options[] = array(	"name" => "Embed Video Code",
						"description" => "If <strong>VIDEO</strong> is selected then input your video code to embed. Width must be <strong style='color:black'>608px</strong>",
						"id" => $pre."_single_video",
						"type" => "textarea");
	$options[] = array(	"type" => "sectionbreak");
	
	/*	SCRIPTS
	---------------------------
	*/
	$options[] = array(	"name" => "Stats and Scripts",
						"id" => "statsnscripts",
						"type" => "section");
		$options[] = array(	"name" => "Header Scripts",
						"description" => "If you need to add scripts to your header (like <a href='http://haveamint.com/'>Mint</a> tracking code), do so here.",
						"id" => $pre."_scripts_header",
						"type" => "textarea");
		$options[] = array(	"name" => "Footer Scripts",
						"description" => "If you need to add scripts to your footer (like <a href='http://www.google.com/analytics/'>Google Analytics</a> tracking code), do so here.",
						"id" => $pre."_scripts_footer",
						"type" => "textarea");
	$options[] = array(	"type" => "sectionbreak");
	
?>