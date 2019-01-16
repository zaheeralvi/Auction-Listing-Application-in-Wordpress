<?php
if ( !defined('ABSPATH') ){ die(); }

global $avia_config;

/*
 * if you run a child theme and dont want to load the default functions.php file
 * set the global var below in you childthemes function.php to true:
 *
 * example: global $avia_config; $avia_config['use_child_theme_functions_only'] = true;
 * The default functions.php file will then no longer be loaded. You need to make sure then
 * to include framework and functions that you want to use by yourself. 
 *
 * This is only recommended for advanced users
 */

if(isset($avia_config['use_child_theme_functions_only'])) return;

/*
 * create a global var which stores the ids of all posts which are displayed on the current page. It will help us to filter duplicate posts
 */
$avia_config['posts_on_current_page'] = array();


/*
 * wpml multi site config file
 * needs to be loaded before the framework
 */
require_once( 'config-wpml/config.php' );

/**
 * layerslider plugin - needs to be loaded before framework because we need to add data to the options array
 * 
 * To be backwards compatible we still support  add_theme_support('deactivate_layerslider'); 
 * This will override the option setting "activation" of the bundled plugin !!
 * 
 * @since 4.2.1
 */
require_once( 'config-layerslider/config.php' );


/*
 * These are the available color sets in your backend.
 * If more sets are added users will be able to create additional color schemes for certain areas
 *
 * The array key has to be the class name, the value is only used as tab heading on the styling page
 */


$avia_config['color_sets'] = array(
    'header_color'      => 'Logo Area',
    'main_color'        => 'Main Content',
    'alternate_color'   => 'Alternate Content',
    'footer_color'      => 'Footer',
    'socket_color'      => 'Socket'
 );
 
 

/*
 * add support for responsive mega menus
 */
 
add_theme_support('avia_mega_menu');



/*
 * add support for improved backend styling
 */
 
add_theme_support('avia_improved_backend_style');



/*
 * deactivates the default mega menu and allows us to pass individual menu walkers when calling a menu
 */
 
add_filter('avia_mega_menu_walker', '__return_false');


/*
 * adds support for the new avia sidebar manager
 */
 
add_theme_support('avia_sidebar_manager');


/*
 * Filters for post formats etc
 */
//add_theme_support('avia_queryfilter');


/*
 * Register theme text domain
 */
if(!function_exists('avia_lang_setup'))
{
	add_action('after_setup_theme', 'avia_lang_setup');
	
	function avia_lang_setup()
	{
		$lang = apply_filters('ava_theme_textdomain_path', get_template_directory()  . '/lang');
		load_theme_textdomain('avia_framework', $lang);
	}
	
	avia_lang_setup();
}


/*
function that changes the icon of the  theme update tab
*/

if(!function_exists('avia_theme_update_filter'))
{
	function avia_theme_update_filter( $data )
	{
		if(current_theme_supports('avia_improved_backend_style'))
		{
			$data['icon'] = 'new/arrow-repeat-two-7@3x.png';
		}
		return $data;
	}
	
	add_filter('avf_update_theme_tab', 'avia_theme_update_filter', 30, 1);
}

/**
 * Needed by framework options page already - not only in frontend
 */
require_once( 'includes/helper-privacy.php' ); 					// holds privacy managment shortcodes and functions

##################################################################
# AVIA FRAMEWORK by Kriesi

# this include calls a file that automatically includes all
# the files within the folder framework and therefore makes
# all functions and classes available for later use

require_once( 'framework/avia_framework.php' );

##################################################################


/*
 * Register additional image thumbnail sizes
 * Those thumbnails are generated on image upload!
 *
 * If the size of an array was changed after an image was uploaded you either need to re-upload the image
 * or use the thumbnail regeneration plugin: http://wordpress.org/extend/plugins/regenerate-thumbnails/
 */

$avia_config['imgSize']['widget'] 			 	= array('width'=>36,  'height'=>36);						// small preview pics eg sidebar news
$avia_config['imgSize']['square'] 		 	    = array('width'=>180, 'height'=>180);		                 // small image for blogs
$avia_config['imgSize']['featured'] 		 	= array('width'=>1500, 'height'=>430 );						// images for fullsize pages and fullsize slider
$avia_config['imgSize']['featured_large'] 		= array('width'=>1500, 'height'=>630 );						// images for fullsize pages and fullsize slider
$avia_config['imgSize']['extra_large'] 		 	= array('width'=>1500, 'height'=>1500 , 'crop' => false);	// images for fullscrren slider
$avia_config['imgSize']['portfolio'] 		 	= array('width'=>495, 'height'=>400 );						// images for portfolio entries (2,3 column)
$avia_config['imgSize']['portfolio_small'] 		= array('width'=>260, 'height'=>185 );						// images for portfolio 4 columns
$avia_config['imgSize']['gallery'] 		 		= array('width'=>845, 'height'=>684 );						// images for portfolio entries (2,3 column)
$avia_config['imgSize']['magazine'] 		 	= array('width'=>710, 'height'=>375 );						// images for magazines
$avia_config['imgSize']['masonry'] 		 		= array('width'=>705, 'height'=>705 , 'crop' => false);		// images for fullscreen masonry
$avia_config['imgSize']['entry_with_sidebar'] 	= array('width'=>845, 'height'=>321);		            	// big images for blog and page entries
$avia_config['imgSize']['entry_without_sidebar']= array('width'=>1210, 'height'=>423 );						// images for fullsize pages and fullsize slider
$avia_config['imgSize'] = apply_filters('avf_modify_thumb_size', $avia_config['imgSize']);


$avia_config['selectableImgSize'] = array(
	'square' 				=> __('Square','avia_framework'),
	'featured'  			=> __('Featured Thin','avia_framework'),
	'featured_large'  		=> __('Featured Large','avia_framework'),
	'portfolio' 			=> __('Portfolio','avia_framework'),
	'gallery' 				=> __('Gallery','avia_framework'),
	'entry_with_sidebar' 	=> __('Entry with Sidebar','avia_framework'),
	'entry_without_sidebar'	=> __('Entry without Sidebar','avia_framework'),
	'extra_large' 			=> __('Fullscreen Sections/Sliders','avia_framework'),
	
);



avia_backend_add_thumbnail_size($avia_config);

if ( ! isset( $content_width ) ) $content_width = $avia_config['imgSize']['featured']['width'];




/*
 * register the layout classes
 *
 */

$avia_config['layout']['fullsize'] 		= array('content' => 'av-content-full alpha', 'sidebar' => 'hidden', 	  	  'meta' => '','entry' => '');
$avia_config['layout']['sidebar_left'] 	= array('content' => 'av-content-small', 	  'sidebar' => 'alpha' ,'meta' => 'alpha', 'entry' => '');
$avia_config['layout']['sidebar_right'] = array('content' => 'av-content-small alpha','sidebar' => 'alpha', 'meta' => 'alpha', 'entry' => 'alpha');





/*
 * These are some of the font icons used in the theme, defined by the entypo icon font. the font files are included by the new aviaBuilder
 * common icons are stored here for easy retrieval
 */
 
 $avia_config['font_icons'] = apply_filters('avf_default_icons', array(
 
    //post formats +  types
    'standard' 		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue836'),
    'link'    		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue822'),
    'image'    		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue80f'),
    'audio'    		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue801'),
    'quote'   		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue833'),
    'gallery'   	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue80e'),
    'video'   		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue80d'),
    'portfolio'   	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue849'),
    'product'   	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue859'),
    				
    //social		
    'behance' 		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue915'),
	'dribbble' 		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue8fe'),
	'facebook' 		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue8f3'),
	'flickr' 		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue8ed'),
	'gplus' 		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue8f6'),
	'linkedin' 		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue8fc'),
	'instagram' 	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue909'),
	'pinterest' 	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue8f8'),
	'skype' 		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue90d'),
	'tumblr' 		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue8fa'),
	'twitter' 		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue8f1'),
	'vimeo' 		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue8ef'),
	'rss' 			=> array( 'font' =>'entypo-fontello', 'icon' => 'ue853'),  
	'youtube'		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue921'),  
	'xing'			=> array( 'font' =>'entypo-fontello', 'icon' => 'ue923'),  
	'soundcloud'	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue913'),  
	'five_100_px'	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue91d'),  
	'vk'			=> array( 'font' =>'entypo-fontello', 'icon' => 'ue926'),  
	'reddit'		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue927'),  
	'digg'			=> array( 'font' =>'entypo-fontello', 'icon' => 'ue928'),  
	'delicious'		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue929'),  
	'mail' 			=> array( 'font' =>'entypo-fontello', 'icon' => 'ue805'),
					
	//woocomemrce    
	'cart' 			=> array( 'font' =>'entypo-fontello', 'icon' => 'ue859'),
	'details'		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue84b'),

	//bbpress    
	'supersticky'	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue808'),
	'sticky'		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue809'),
	'one_voice'		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue83b'),
	'multi_voice'	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue83c'),
	'closed'		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue824'),
	'sticky_closed' => array( 'font' =>'entypo-fontello', 'icon' => 'ue808\ue824'),
	'supersticky_closed' => array( 'font' =>'entypo-fontello', 'icon' => 'ue809\ue824'),
					
	//navigation, slider & controls
	'play' 			=> array( 'font' =>'entypo-fontello', 'icon' => 'ue897'),
	'pause'			=> array( 'font' =>'entypo-fontello', 'icon' => 'ue899'),
	'next'    		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue879'),
    'prev'    		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue878'),
    'next_big'  	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue87d'),
    'prev_big'  	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue87c'),
	'close'			=> array( 'font' =>'entypo-fontello', 'icon' => 'ue814'),
	'reload'		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue891'),
	'mobile_menu'	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue8a5'),
					
	//image hover overlays		
    'ov_external'	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue832'),
    'ov_image'		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue869'),
    'ov_video'		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue897'),
    
					
	//misc			
    'search'  		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue803'),
    'info'    		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue81e'),
	'clipboard' 	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue8d1'),
	'scrolltop' 	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue876'),
	'scrolldown' 	=> array( 'font' =>'entypo-fontello', 'icon' => 'ue877'),
	'bitcoin' 		=> array( 'font' =>'entypo-fontello', 'icon' => 'ue92a'),

));




/*
 * a small array that contains admin notices that can, for example, be called after an update
 * just set the db option avia_admin_notice to contain the key of the notice you want to display
 * eg: update_option('avia_admin_notice', 'performance_update');
 *
 * classes: error, warning, success, info
 * msg: whatever floats your boat :D
 */

$avia_config['admin_notices'] = array(
	
	//default update success
	'update_success' 		=> array('class'=>'success', 'msg' => __('Enfold update was successful! ','avia_framework')),
	
	//update to version 4.3 - performance update. display notice and link to blog post	
	'performance_update' 	=> array('class'=>'info', 	 'msg' => "<strong>Attention:</strong> The last Enfold update added a lot of performance options. Make sure to read more about it <a href='https://kriesi.at/archives/enfold-4-3-performance-update' target='_blank'>here</a><br><br>If you are running a caching plugin please make sure to reset your cached files, since the CSS and JS file structure of the theme changed heavily"
	),	
	
	//update to version 4.4 - gdpr update. display notice and link to blog post	
	'gdpr_update' 	=> array('class'=>'info', 	 'msg' => "<strong>Attention:</strong> Enfold was updated for GDPR compliance. Make sure to read more about it <a href='https://kriesi.at/archives/enfold-4-4-and-the-gdpr-general-data-protection-regulation' target='_blank'>here</a>"
	),	
	
	
	//more to come...
);





add_theme_support( 'automatic-feed-links' );

##################################################################
# Frontend Stuff necessary for the theme:
##################################################################



/*
 * Register frontend javascripts:
 */
if(!function_exists('avia_register_frontend_scripts'))
{
	if(!is_admin()){
		add_action('wp_enqueue_scripts', 'avia_register_frontend_scripts');
	}

	function avia_register_frontend_scripts()
	{
		global $avia_config;
		
		$theme = wp_get_theme();
		if( false !== $theme->parent() )
		{
			$theme = $theme->parent();
		}
		$vn = $theme->get( 'Version' );
		
		$options = avia_get_option();
		
		$template_url 		= get_template_directory_uri();
		$child_theme_url 	= get_stylesheet_directory_uri();

		//register js
		wp_enqueue_script( 'avia-compat', $template_url.'/js/avia-compat.js' , array(), $vn, false ); //needs to be loaded at the top to prevent bugs
		wp_enqueue_script( 'avia-default', $template_url.'/js/avia.js', array('jquery'), $vn, true );
		wp_enqueue_script( 'avia-shortcodes', $template_url.'/js/shortcodes.js', array('jquery','avia-default'), $vn, true );

		wp_enqueue_script( 'jquery' );


		


		//register styles
		wp_register_style( 'avia-style' ,  $child_theme_url."/style.css", array(), 		$vn, 'all' ); //only include in childthemes. has no purpose in main theme
		wp_register_style( 'avia-custom',  $template_url."/css/custom.css", array(), 	$vn, 'all' );
																						 
		wp_enqueue_style( 'avia-grid' ,   $template_url."/css/grid.css", array(), 		$vn, 'all' );
		wp_enqueue_style( 'avia-base' ,   $template_url."/css/base.css", array('avia-grid'), 		$vn, 'all' );
		wp_enqueue_style( 'avia-layout',  $template_url."/css/layout.css", array('avia-base'), 	$vn, 'all' );
		wp_enqueue_style( 'avia-scs',     $template_url."/css/shortcodes.css", array('avia-layout'), $vn, 'all' );
		
		
		/************************************************************************
		Conditional style and script calling, based on theme options or other conditions
		*************************************************************************/
		
		//lightbox inclusion
		$condition = !empty($avia_config['use_standard_lightbox']) && ( 'disabled' != $avia_config['use_standard_lightbox'] );
		avia_enqueue_style_conditionally(  $condition , 'avia-popup-css', $template_url."/js/aviapopup/magnific-popup.css", array('avia-layout'), $vn, 'screen');
		avia_enqueue_style_conditionally(  $condition , 'avia-lightbox', $template_url."/css/avia-snippet-lightbox.css", array('avia-layout'), $vn, 'screen');
		avia_enqueue_script_conditionally( $condition , 'avia-popup-js' , $template_url.'/js/aviapopup/jquery.magnific-popup.min.js', array('jquery'), $vn, true);
		avia_enqueue_script_conditionally( $condition , 'avia-lightbox-activation', $template_url."/js/avia-snippet-lightbox.js", array('avia-default'), $vn, true);
		
		
		//mega menu inclusion (only necessary with sub menu items)
		$condition = (avia_get_submenu_count('avia') > 0);
		avia_enqueue_script_conditionally( $condition , 'avia-megamenu', $template_url."/js/avia-snippet-megamenu.js", array('avia-default'), $vn, true);
		
		
		//sidebar menu inclusion (only necessary when header position is set to be a sidebar)
		$condition = (isset($options['header_position']) && $options['header_position'] != "header_top");
		avia_enqueue_script_conditionally( $condition , 'avia-sidebarmenu', $template_url."/js/avia-snippet-sidebarmenu.js", array('avia-default'), $vn, true);
		
		
		//sticky header with header size calculator
		$condition  = (isset($options['header_position']) && $options['header_position'] == "header_top");
		$condition2 = (isset($options['header_sticky']) && $options['header_sticky'] == "header_sticky") && $condition;
		avia_enqueue_script_conditionally( $condition2 , 'avia-sticky-header', $template_url."/js/avia-snippet-sticky-header.js", array('avia-default'), $vn, true);
		
		
		//site preloader
		$condition = (isset($options['preloader']) && $options['preloader'] == "preloader");
		avia_enqueue_script_conditionally( $condition , 'avia-siteloader-js', $template_url."/js/avia-snippet-site-preloader.js", array('avia-default'), $vn, true, false);
		avia_enqueue_style_conditionally(  $condition , 'avia-siteloader', $template_url."/css/avia-snippet-site-preloader.css", array('avia-layout'), $vn, 'screen', false);
		
		
		//cookie consent
		$condition = (isset($options['cookie_consent']) && $options['cookie_consent'] == "cookie_consent");
		avia_enqueue_script_conditionally( $condition , 'avia-cookie-js' , $template_url."/js/avia-snippet-cookieconsent.js", array('avia-default'), $vn, true);
		avia_enqueue_style_conditionally(  $condition , 'avia-cookie-css', $template_url."/css/avia-snippet-cookieconsent.css", array('avia-layout'), $vn, 'screen');
		
		
		//load widget assets only if we got active widgets
		$condition = (avia_get_active_widget_count() > 0);
        avia_enqueue_script_conditionally( $condition , 'avia-widget-js' , $template_url."/js/avia-snippet-widget.js", array('avia-default'), $vn, true);
		avia_enqueue_style_conditionally(  $condition , 'avia-widget-css', $template_url."/css/avia-snippet-widget.css", array('avia-layout'), $vn, 'screen');

		
		//load mediaelement js
		$opt_mediaelement = isset( $options['disable_mediaelement'] ) ? $options['disable_mediaelement'] : '';
		
		$condition = true;
		if( 'force_mediaelement' != $opt_mediaelement )
		{
			$condition  = ( $opt_mediaelement != "disable_mediaelement" ) && av_video_assets_required();
		}
		
		/**
		 * Allow to force loading of WP media element for 3rd party plugins. Nedded for wp_enqueue_media() to load properly.
		 * 
		 * @since 4.1.2 
		 * @param boolean $condition 
		 * @param array $options
		 * @return boolean
		 */
		$condition = apply_filters( 'avf_enqueue_wp_mediaelement', $condition, $options );		
		
		$condition2 = ( version_compare( get_bloginfo( 'version' ), '4.9', '>=' ) ) && $condition;
		avia_enqueue_script_conditionally( $condition , 'wp-mediaelement');
		avia_enqueue_style_conditionally( $condition2 , 'wp-mediaelement'); //With WP 4.9 we need to load the stylesheet seperately


		//comment reply script
		global $post;
		$condition = !( isset($options['disable_blog']) && $options['disable_blog'] == "disable_blog" ) && $post && comments_open();
		$condition = ( is_singular() && get_option( 'thread_comments' ) ) && $condition;
		avia_enqueue_script_conditionally( $condition , 'comment-reply');
		


		//rtl inclusion
		avia_enqueue_style_conditionally( is_rtl() , 'avia-rtl',  $template_url."/css/rtl.css", array(), $vn, 'all');
		
		
		//disable jquery migrate if no plugins are active (enfold does not need it) or if user asked for it in optimization options
		$condition = avia_count_active_plugins() == 0 || (isset($options['disable_jq_migrate']) && $options['disable_jq_migrate'] != "disable_jq_migrate");
		if(!$condition) avia_disable_query_migrate();
		
		
		
		//move jquery to footer if no unkown plugins are active
		if(av_count_untested_plugins() == 0 || (isset($options['jquery_in_footer']) && $options['jquery_in_footer'] == "jquery_in_footer") ){ 
			av_move_jquery_into_footer();
		}
		
		
		
		/************************************************************************
		Inclusion of the dynamic stylesheet
		*************************************************************************/
		
		
        global $avia;
		
		$safe_name = avia_backend_safe_string($avia->base_data['prefix']);
		$safe_name = apply_filters('avf_dynamic_stylesheet_filename', $safe_name);

        if( get_option('avia_stylesheet_exists'.$safe_name) == 'true' )
        {
            $avia_upload_dir = wp_upload_dir();
			
			/**
			 * Change the default dynamic upload url
			 * 
			 * @since 4.4
			 */
			$avia_dyn_upload_path = apply_filters('avf_dyn_stylesheet_dir_url',  $avia_upload_dir['baseurl'] . '/dynamic_avia' );
			$avia_dyn_upload_path = trailingslashit( $avia_dyn_upload_path );
			
            if( is_ssl() ) 
			{
				$avia_dyn_upload_path = str_replace( "http://", "https://", $avia_dyn_upload_path );
			}
			
			/**
			 * Change the default dynamic stylesheet name
			 * 
			 * @since 4.4
			 */
			$avia_dyn_stylesheet_url = apply_filters( 'avf_dyn_stylesheet_file_url', $avia_dyn_upload_path . $safe_name . '.css' );

			$version_number = get_option( 'avia_stylesheet_dynamic_version' . $safe_name );
			if( empty( $version_number ) ) 
			{
				$version_number = $vn;
			}
            
            wp_enqueue_style( 'avia-dynamic', $avia_dyn_stylesheet_url, array(), $version_number, 'all' );
        }

		wp_enqueue_style( 'avia-custom');


		if($child_theme_url !=  $template_url)
		{
			wp_enqueue_style( 'avia-style');
		}

	}
}


if(!function_exists('avia_remove_default_video_styling'))
{
	if(!is_admin()){
		add_action('wp_footer', 'avia_remove_default_video_styling', 1);
	}

	function avia_remove_default_video_styling()
	{
		/**
		 * remove default style for videos
		 * 
		 * With WP 4.9 we need to load the stylesheet seperately - therefore we must not remove it
		 */
		if( version_compare( get_bloginfo( 'version' ), '4.9', '<' ) )
		{
			wp_dequeue_style( 'mediaelement' );
		}
		
		// wp_dequeue_script( 'wp-mediaelement' );
		// wp_dequeue_style( 'wp-mediaelement' );
	}
}




/*
 * Activate native wordpress navigation menu and register a menu location
 */
if(!function_exists('avia_nav_menus'))
{
	function avia_nav_menus()
	{
		global $avia_config, $wp_customize;

		add_theme_support('nav_menus');
		
		foreach($avia_config['nav_menus'] as $key => $value)
		{
			//wp-admin\customize.php does not support html code in the menu description - thus we need to strip it
			$name = (!empty($value['plain']) && !empty($wp_customize)) ? $value['plain'] : $value['html'];
			register_nav_menu($key, THEMENAME.' '.$name);
		}
	}

	$avia_config['nav_menus'] = array(	'avia' => array('html' => __('Main Menu', 'avia_framework')),
										'avia2' => array(
													'html' => ''.__('Secondary Menu', 'avia_framework').' <br/><small>('.__('Will be displayed if you selected a header layout that supports a submenu', 'avia_framework').' <a target="_blank" href="'.admin_url('?page=avia#goto_header').'">'.__('here', 'avia_framework').'</a>)</small>',
													'plain'=> __('Secondary Menu - will be displayed if you selected a header layout that supports a submenu', 'avia_framework')),
										'avia3' => array(
													'html' => __('Footer Menu <br/><small>(no dropdowns)</small>', 'avia_framework'),
													'plain'=> __('Footer Menu (no dropdowns)', 'avia_framework'))
									);

	avia_nav_menus(); //call the function immediatly to activate
}










/*
 *  load some frontend functions in folder include:
 */

require_once( 'includes/admin/register-portfolio.php' );		// register custom post types for portfolio entries
require_once( 'includes/admin/register-widget-area.php' );		// register sidebar widgets for the sidebar and footer
require_once( 'includes/loop-comments.php' );					// necessary to display the comments properly
require_once( 'includes/helper-template-logic.php' ); 			// holds the template logic so the theme knows which tempaltes to use
require_once( 'includes/helper-social-media.php' ); 			// holds some helper functions necessary for twitter and facebook buttons
require_once( 'includes/helper-post-format.php' ); 				// holds actions and filter necessary for post formats
require_once( 'includes/helper-markup.php' ); 					// holds the markup logic (schema.org and html5)
require_once( 'includes/helper-assets.php' ); 					// holds asset managment functions


if(current_theme_supports('avia_conditionals_for_mega_menu'))
{
	require_once( 'includes/helper-conditional-megamenu.php' );  // holds the walker for the responsive mega menu
}

require_once( 'includes/helper-responsive-megamenu.php' ); 		// holds the walker for the responsive mega menu




//adds the plugin initalization scripts that add styles and functions
require_once( 'config-gutenberg/class-avia-gutenberg.php' );	//	gutenberg - might be necessary to move when part of WP core

require_once( 'config-bbpress/config.php' );					//compatibility with  bbpress forum plugin
require_once( 'config-templatebuilder/config.php' );			//templatebuilder plugin
require_once( 'config-gravityforms/config.php' );				//compatibility with gravityforms plugin
require_once( 'config-woocommerce/config.php' );				//compatibility with woocommerce plugin
require_once( 'config-wordpress-seo/config.php' );				//compatibility with Yoast WordPress SEO plugin
require_once( 'config-menu-exchange/config.php' );				//compatibility with Zen Menu Logic and Themify_Conditional_Menus plugin

if(!current_theme_supports('deactivate_tribe_events_calendar'))
{
	require_once( 'config-events-calendar/config.php' );		//compatibility with the Events Calendar plugin
}

// if(is_admin())
require_once( 'includes/admin/helper-compat-update.php');	// include helper functions for new versions





/*
 *  dynamic styles for front and backend
 */
if(!function_exists('avia_custom_styles'))
{
	function avia_custom_styles()
	{
		require_once( 'includes/admin/register-dynamic-styles.php' );	// register the styles for dynamic frontend styling
		avia_prepare_dynamic_styles();
	}

	add_action('init', 'avia_custom_styles', 20);
	add_action('admin_init', 'avia_custom_styles', 20);
}




/*
 *  activate framework widgets
 */
if(!function_exists('avia_register_avia_widgets'))
{
	function avia_register_avia_widgets()
	{
		register_widget( 'avia_newsbox' );
		register_widget( 'avia_portfoliobox' );
		register_widget( 'avia_socialcount' );
		register_widget( 'avia_partner_widget' );
		register_widget( 'avia_google_maps' );
		register_widget( 'avia_fb_likebox' );
		register_widget( 'avia_instagram_widget' );
		register_widget( 'avia_combo_widget' );
    register_widget( 'avia_auto_toc' );

	}

	avia_register_avia_widgets(); //call the function immediatly to activate
}



/*
 *  add post format options
 */
add_theme_support( 'post-formats', array('link', 'quote', 'gallery','video','image','audio' ) );



/*
 *  Remove the default shortcode function, we got new ones that are better ;)
 */
add_theme_support( 'avia-disable-default-shortcodes', true);


/*
 * compat mode for easier theme switching from one avia framework theme to another
 */
add_theme_support( 'avia_post_meta_compat');


/*
 * make sure that enfold widgets dont use the old slideshow parameter in widgets, but default post thumbnails
 */
add_theme_support('force-post-thumbnails-in-widget');


/*
 * display page titles via wordpress default output
 * 
 * @since 3.6
 */
function av_theme_slug_setup() 
{
   add_theme_support( 'title-tag' );
}

add_action( 'after_setup_theme', 'av_theme_slug_setup' );

/*title fallback (up to WP 4.1)*/
if ( ! function_exists( '_wp_render_title_tag' ) )
{
    function av_theme_slug_render_title() 
    {
	    echo "<title>" . avia_set_title_tag() ."</title>";
	}
	add_action( 'wp_head', 'av_theme_slug_render_title' );
}
    



/*
 *  register custom functions that are not related to the framework but necessary for the theme to run
 */

require_once( 'functions-enfold.php');


/*
 * add option to edit elements via css class
 */
// add_theme_support('avia_template_builder_custom_css');











