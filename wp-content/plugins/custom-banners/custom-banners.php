<?php
/*
Plugin Name: Custom Banners
Plugin Script: custom-banners.php
Plugin URI: http://goldplugins.com/our-plugins/custom-banners/
Description: Allows you to create custom banners, which consist of an image, text, a link, and a call to action.  Custom banners are easily output via shortcodes. Each visitor to the website is then shown a random custom banner.
Version: 3.1.1
Author: Gold Plugins
Author URI: http://goldplugins.com/

*/

require_once( plugin_dir_path( __FILE__ ) . "gold-framework/plugin-base.php" );
require_once( plugin_dir_path( __FILE__ ) . "lib/lib.php" );
require_once( plugin_dir_path( __FILE__ ) . "lib/GP_Sajak/gp_sajak.class.php" );
require_once( plugin_dir_path( __FILE__ ) . "lib/custom_banners_options.php" );
require_once( plugin_dir_path( __FILE__ ) . "lib/BikeShed/bikeshed.php" );
require_once( plugin_dir_path( __FILE__ ) . "lib/cbp_expiration_date.class.php" );
require_once( plugin_dir_path( __FILE__ ) . "lib/GP_Media_Button/gold-plugins-media-button.class.php" );
require_once( plugin_dir_path( __FILE__ ) . "lib/GP_Janus/gp-janus.class.php" );
require_once( plugin_dir_path( __FILE__ ) . "lib/GP_Aloha/gp_aloha.class.php" );
require_once( plugin_dir_path( __FILE__ ) . "include/tgmpa/init.php" );
require_once( plugin_dir_path( __FILE__ ) . "include/Custom_Banners_Update_Notices.php" );

class CustomBannersPlugin extends CBP_GoldPlugin
{
	var $transitions = array(
		'fade' => 'Fade',
		'scrollHorz' => 'Horizontal Scroll'
	);
	
	function __construct()
	{
		// create subclasses
		$this->ExpirationDate = new CBP_ExpirationDate($this, __FILE__);		
		
		$defaults = array(
			'plugin_name' => 'Pro',
			'pitch' => '',
			'learn_more_url' => 'https://goldplugins.com/',
			'upgrade_url' => 'https://goldplugins.com/',
			'text_domain' => 'wordpress',
			'testimonial' => false,			
		);
		
		$this->Update_Notices = new Custom_Banners_Update_Notices();
		
		$this->is_pro = isValidCBKey();
		
		$this->add_hooks();
		$this->create_post_types();
		$this->register_taxonomies();
		$this->add_shortcodes();
		$this->add_stylesheets_and_scripts();
		
		//register sidebar widgets
		add_action( 'widgets_init', array($this, 'custom_banners_register_widgets' ));
				
		add_filter('manage_banner_posts_columns', array($this, 'custom_banners_column_head'), 10);  
		add_action('manage_banner_posts_custom_column', array($this, 'custom_banners_columns_content'), 10, 2); 
			
		add_filter('manage_edit-banner_groups_columns', array($this, 'custom_banners_cat_column_head'), 10);  
		add_action('manage_banner_groups_custom_column', array($this, 'custom_banners_cat_columns_content'), 10, 3); 
			
		$custom_banners_options = new customBannersOptions();
		
		//add Custom CSS
		add_action( 'wp_head', array($this, 'cb_setup_custom_css'));
		
		// add extra class to admin menu to allow CSS to target
		add_action( 'admin_init', array($this, 'add_extra_classes_to_admin_menu') );
		
		//add our custom links for Settings and Support to various places on the Plugins page
		$plugin = plugin_basename(__FILE__);
		add_filter( "plugin_action_links_{$plugin}", array($this, 'add_settings_link_to_plugin_action_links') );
		add_filter( 'plugin_row_meta', array($this, 'add_custom_links_to_plugin_description'), 10, 2 );	
		
		//add single shortcode metabox to banner add/edit screen
		add_action( 'admin_menu', array($this,'add_meta_boxes')); // add our custom meta boxes
		add_action( 'admin_menu', array($this,'add_settings_link')); // add a link to the settings page under the banners menu
		
		// add media buttons to admin
		$cur_post_type = ( isset($_GET['post']) ? get_post_type(intval($_GET['post'])) : '' );
		if( is_admin() && ( empty($_REQUEST['post_type']) || $_REQUEST['post_type'] !== 'banner' ) && ($cur_post_type !== 'banner') )
		{
			global $CustomBanners_MediaButton;
			$banner_shortcode = get_option("custom_banners_banner_shortcode", 'banner');
			
			$CustomBanners_MediaButton = new Gold_Plugins_Media_Button('Banners', 'images-alt');
			$CustomBanners_MediaButton->add_button('Single Banner Widget', $banner_shortcode, 'singlebannerwidget', 'images-alt');
			$CustomBanners_MediaButton->add_button('List of Banners Widget', $banner_shortcode, 'bannerlistwidget', 'images-alt');
			$CustomBanners_MediaButton->add_button('Rotating Banner Widget',  $banner_shortcode, 'rotatingbannerwidget', 'images-alt');
		}
		
		//only load Aloha and Janus on Admin screens
		if( is_admin() ){	
			// load Janus
			if (class_exists('GP_Janus')) {
				$cb_janus = new GP_Janus();
			}
			
			// load Aloha
			$plugin_title = 'Custom Banners';
			$aloha_page_title = __('Welcome To ') . $plugin_title;			
			$aloha_config = array(
				'menu_label' => __('About Plugin'),
				'page_title' => $aloha_page_title,
				'top_level_menu' => 'custom-banners-settings',
				'tagline' => 'Custom Banners helps you create and manage Banners on your website. Its the best way to display your own banners, or rotate through a group of sponsors, without having to update your code.'
			);
			
			$this->Aloha = new GP_Aloha($aloha_config);
			add_filter( 'gp_aloha_welcome_page_content_custom-banners-settings', array($this, 'get_welcome_template') );
		}
				
		//run activation steps
		register_activation_hook( __FILE__, array($this, 'custom_banners_activation_hook' ));
		
		parent::__construct();
	}
	
	function get_available_transitions()
	{
		return apply_filters('custom_banners_available_transitions', $this->transitions);
	}
	
	//things to do on plugin activation
	function custom_banners_activation_hook() {			
		// make sure the welcome screen gets seen again
		if ( !empty($this->Aloha) ) {
			$this->Aloha->reset_welcome_screen();
		}
	}
	
	//loads the welcome page template for aloha
	function get_welcome_template()
	{
		$base_path = plugin_dir_path( __FILE__ );
		$template_path = $base_path . '/include/content/welcome.php';
		$is_pro = $this->is_pro;
		$plugin_title = 'Custom Banners';
		$content = file_exists($template_path)
				   ? include($template_path)
				   : '';
		return $content;
	}

	//add Custom CSS
	function cb_setup_custom_css() 
	{
		echo '<style type="text/css" media="screen">' . get_option('custom_banners_custom_css') . "</style>";
	}
	
	function add_hooks()
	{
		global $post;
		
		// admin javascript
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_js') );
				
		parent::add_hooks();
	}
	
	function enqueue_js($hook)
	{
		if ( $hook != 'custom-banners-settings_page_custom-banners-themes' ) {
			return;
		}
		if ( is_admin() ) {
			wp_enqueue_script(
				'gp-custom_banners_theme_selector',
				plugins_url('assets/js/gp-custom_banners_theme_selector.js', __FILE__),
				array( 'jquery' ),
				false,
				true
			);			
		}
	}
	
	function create_post_types()
	{
		$postType = array(
			'name' => 'Banner',
			'plural' => 'Banners',
			'slug' => 'banners',
			'menu_icon' => 'dashicons-images-alt'
		);
		
		$customFields = array();
		$customFields[] = array('name' => 'target_url', 'title' => 'Target URL', 'description' => 'Where a user should be sent when they click on the banner or the call to action button', 'type' => 'text');	
		$customFields[] = array('name' => 'cta_text', 'title' => 'Call To Action Text', 'description' => 'The "Call To Action" (text) of the button. Leave this field blank to hide the call to action button.', 'type' => 'text');
		$customFields[] = array('name' => 'css_class', 'title' => 'CSS Class', 'description' => 'Any extra CSS classes that you would like applied to this banner.', 'type' => 'text');
		$this->add_custom_post_type($postType, $customFields);

		//load list of current posts that have featured images	
		$supportedTypes = get_theme_support( 'post-thumbnails' );
		
		//none set, add them just to our type
		if( $supportedTypes === false ){
			add_theme_support( 'post-thumbnails', array( 'banner' ) );       
			//for the banner images    
		}
		//specifics set, add our to the array
		elseif( is_array( $supportedTypes ) ){
			$supportedTypes[0][] = 'banner';
			add_theme_support( 'post-thumbnails', $supportedTypes[0] );
			//for the banner images
		}
	
		//move featured image box to main column
		add_action('add_meta_boxes', array($this,'custom_banner_edit_screen'));		
		
		//remove unused meta boxes
		add_action( 'admin_init', array($this,'custom_banners_unused_meta'));

		// move the post editor under the other metaboxes
		add_action( 'add_meta_boxes', array($this, 'reposition_editor_metabox'), 0 );
		
		// enforce correct order of metaboxes
		add_action('admin_init', array($this, 'set_metabox_order'));
	}
	
	function reposition_editor_metabox() {
		global $_wp_post_type_features;
		if (isset($_wp_post_type_features['banner']['editor']) && $_wp_post_type_features['banner']['editor']) {
			unset($_wp_post_type_features['banner']['editor']);
			add_meta_box(
			'banner_caption',
			__('Banner Caption'),
			array($this, 'output_banner_caption_metabox'),
			'banner', 'normal', 'low'
			);
		}
	}
	
	function output_banner_caption_metabox( $post ) {
		echo '<div class="wp-editor-wrap">';
		wp_editor($post->post_content, 'content', array('dfw' => true, 'tabindex' => 1, 'textarea_rows' => 3) );
		echo '</div>';
	}	
	
	function set_metabox_order() {
		global $wpdb, $user_ID, $posts_widgets_order_hash;
		
		// check to see if its already set correctly
		$check_val = get_user_option('_custom_banners_meta_box_order', $user_ID);
		$correct_val = "metaboxes_v1";
		
		// if the metabox order is incorrect or not set, reset it now
		if ($check_val !== $correct_val)
		{
			$metabox_order = get_user_option('meta-box-order_banner', $user_ID);
			if (empty($metabox_order)) {
				$metabox_order = array();
			}
			$banner_info_metabox_id = $this->customPostTypes['banners']->get_metabox_id();
			$metabox_order['normal'] = 'postimagediv,banner_caption,'.$banner_info_metabox_id.'';
			update_user_option($user_ID, 'meta-box-order_banner', $metabox_order, true);
			update_user_option($user_ID, '_custom_banners_meta_box_order', $correct_val, true);
		}
	}
	
	//remove unused meta boxes
	function custom_banners_unused_meta() {
		remove_post_type_support( 'banner', 'excerpt' );
		remove_post_type_support( 'banner', 'custom-fields' );
		remove_post_type_support( 'banner', 'comments' );
		remove_post_type_support( 'banner', 'author' );
	}

	//remove featured image from the sidebar and add it to the main column
	function custom_banner_edit_screen() {
		// remove the Featured Image metabox, and replace it with our own (slightly modified) version, now residing in the main column
		remove_meta_box( 'postimagediv', 'banner', 'side' );
		add_meta_box('postimagediv', __('Banner Image'), array($this, 'custom_banners_post_thumbnail_html'), 'banner', 'normal', 'high');
	}
	
	//custom banner image html callback
	function custom_banners_post_thumbnail_html( $post ) {
		$thumbnail_id = get_post_meta( $post->ID, '_thumbnail_id', true );

        $upload_iframe_src = esc_url( get_upload_iframe_src('image', $post->ID ) );
        $set_thumbnail_link = '<p class="hide-if-no-js"><a title="' . esc_attr__( 'Set featured image' ) . '" href="%s" id="set-post-thumbnail" class="thickbox">%s</a></p>';
        $content = sprintf( $set_thumbnail_link, $upload_iframe_src, esc_html__( 'Set featured image' ) );

        if ( $thumbnail_id && get_post( $thumbnail_id ) ) {
                $thumbnail_html = wp_get_attachment_image( $thumbnail_id, "full" );
						
                if ( !empty( $thumbnail_html ) ) {
                        $ajax_nonce = wp_create_nonce( 'set_post_thumbnail-' . $post->ID );
                        $content = sprintf( $set_thumbnail_link, $upload_iframe_src, $thumbnail_html );
                        $content .= '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail" onclick="WPRemoveThumbnail(\'' . $ajax_nonce . '\');return false;">' . esc_html__( 'Remove featured image' ) . '</a></p>';
                }           
        }
		
		// output the finalized HTML
        echo apply_filters( 'admin_post_thumbnail_html', $content, $post->ID );
	}
	
	function register_taxonomies()
	{
		$this->add_taxonomy('banner_groups', 'banner', 'Banner Group', 'Banner Groups');
	}
	
	function add_shortcodes()
	{
		//load and register the banner shortcode
		$banner_shortcode = get_option("custom_banners_banner_shortcode", 'banner');
		add_shortcode($banner_shortcode, array($this, 'banner_shortcode'));
	}
	
	function banner_shortcode($atts, $content = '')
	{
		// load the shortcodes attributes and merge with our defaults
		$defaults = array(	'id' => '',
							'group' => '',
							'caption_position' => 'bottom',
							'transition' => 'none',
							'pager' => false,
							'count' => -1,
							'timer' => 4000,
							'use_image_tag' => true,
							'show_pager_icons' => false,
							'use_pager_thumbnails' => false,
							'hide' => false,
							'width' => get_option('custom_banners_default_width', ''), // 'auto', '100_percent', or integer (width in px)
							'height' => get_option('custom_banners_default_height', ''), // 'auto', or integer (height in px)
							'pause_on_hover' => false,
							'auto_height' => false,
							'prev_next' => false,
							'paused' => false,
							'banner_height' => '', // (only for widget) used to override height with a px value
							'banner_height_px' =>get_option('custom_banners_default_height', ''),
							'banner_width' => '', // (only for widget) used to override width with a px value
							'banner_width_px' =>  get_option('custom_banners_default_width', ''),
							'link_entire_banner' => get_option('custom_banners_use_big_link', 0),
							'open_link_in_new_window' => get_option('custom_banners_open_link_in_new_window', 0),
							'show_caption' => !get_option('custom_banners_never_show_captions', 1),
							'show_cta_button' => !get_option('custom_banners_never_show_cta_buttons', 1),
							'theme' => get_option('custom_banners_theme')
						);

		$atts = shortcode_atts($defaults, $atts);
		$banner_id = intval($atts['id']);
		
		// if theme is not available, fallback to default style
		$atts['theme'] = $this->filter_theme_name($atts['theme'], $banner_id);

		// card theme requires image tags
		if ( strpos($atts['theme'], 'card-') !== false ) {
			$atts['use_image_tag'] = true;
		}
				
		// Generate the HTML for the requested banners (could be a single banner, or many)
		if( $banner_id > 0 ) {
			// A single banner ID was specified, so just load it directly
			$banner = $this->get_banner_by_id($banner_id);
			if ($banner !== false) {
				$html = $this->buildBannerHTML($banner, $banner_id, $atts);
			}
		}
		else {
			// choose a banner based on the other attributes 	
			$banners = $this->get_banners_by_atts($atts);
			$atts['slideshow'] = $this->atts_contain_valid_slideshow_transition($atts);
			$is_slideshow = $atts['slideshow'];
			
			//generate a random number to have a unique wrapping class on each slideshow
			//this should prevent controls that effect more than one slideshow on a page
			$target = rand();

			$banners_html = '';
			// build the HTML for each banner, concatenating its output to $html
			foreach($banners as $index => $banner) {
				// If we are outputting a slideshow, hide all but the first banner
				$atts['hide'] = ( $is_slideshow && ($index > 0) );
				
				// Add this banner's HTML to the output
				$banners_html .= $this->buildBannerHTML($banner, $banner_id, $atts);
			}

			/*
			 * If its a slideshow, wrap the banners in the slideshow HTML now
			*/
			
			if( $is_slideshow ) {
				
				if ($atts['banner_width'] == 'specify') { //if the width was specified via the widget, use it here, otherwise go with global option
					$atts['width'] = intval($atts['banner_width_px']);
				}
				
				// start the slideshow's HTML (if required). This returns a <div> with attributes 
				// reflecting the shortcode settings, and instructions for cycle2			
				$wrapper_html = $this->get_opening_tag_for_slideshow($target, $atts);

				// insert list of banners into t he middle of the slideshow
				$wrapper_html .= $banners_html;
								
				// add a pager and close the slideshow's HTML (if requested)
				$wrapper_html .= $this->get_slideshow_controls_html( $atts, $target );
				
				$wrapper_html .= '</div><!-- end slideshow -->';
				$html = $wrapper_html;
			} else {
				$html = $banners_html;
			}
		}
		
		// return the generated HTML
		return $html;
	}
	
	function  filter_theme_name($theme, $banner_id, $fallback = 'default_style')
	{
		$cfg = new CustomBanners_Config();
		$available_themes = $cfg->all_themes(true);
		$available_theme_ids = array_keys($available_themes);
		$theme = strtolower( trim($theme) );
		$theme_name = in_array( $theme, $available_theme_ids )
					  ? $theme
					  : $fallback;
		return apply_filters('custom_banners_banner_theme', $theme_name, $banner_id, $fallback);
	}
	
	
	function atts_contain_valid_slideshow_transition($atts)
	{
		$transitions = $this->get_available_transitions();
		$transition_ids = array_keys($transitions);
		return in_array( $atts['transition'], $transition_ids );
	}
	
	function get_opening_tag_for_slideshow( $target, $atts )
	{
		if ($atts['banner_width'] == 'specify') {//if the width was specified via the widget, use it here, otherwise go with global option
			$atts['width'] = intval($atts['banner_width_px']);
		}

		$style_string = $this->build_slideshow_wrapper_css($atts);
		$theme_classes = $this->get_banner_slideshow_classes($atts['theme']);	
		$theme_classes_str = implode(' ', $theme_classes);
		
		//determine auto height setting
		//true is calc (height of slideshow is set to height of tallest slide)
		//false is container (height of slideshow is set to the height of the currently visible slide)
		$auto_height = ($atts['auto_height']) ? "calc" : "container";

		// add attributes that are always present
		$div_atts = array(
			'style' => $style_string,
			'data-cycle-auto-height' => $auto_height,
			'class' => 'custom-banners-cycle-slideshow cycle-slideshow custom-b-' . $target . ' ' . $theme_classes_str,
			'data-cycle-fx' => $atts['transition'],
			'data-cycle-timeout' => $atts['timer'],
			'data-cycle-pause-on-hover' => $atts['pause_on_hover'],
			'data-cycle-slides' => '> div.banner_wrapper',
			'data-cycle-paused' => $atts['paused']			
		);
		
		// possibly add prev / next selectors (for cycle2)
		if ($atts['prev_next']) {
			$div_atts['data-cycle-prev'] = '.custom-b-'. $target .' .custom-b-cycle-prev';
			$div_atts['data-cycle-next'] = '.custom-b-'. $target .' .custom-b-cycle-next';
		}

		
		// possibly add pager attributes (for cycle2)
		if( $atts['pager'] || $atts['show_pager_icons'] ) {
			$div_atts['data-cycle-pager'] = '.custom-b-' . $target . ' .custom-b-cycle-pager';
			
			//output image thumbnails or regular pagers
			if( $atts['use_pager_thumbnails'] ) {
				if( $atts['auto_height']) {
					$div_atts['data-cycle-pager-template'] = '<span><a href=\'#\'><img src=\'{{firstChild.firstChild.firstChild.src}}\'></a></span>';
				} else {
					$div_atts['data-cycle-pager-template'] = '<span><a href=\'#\'><img src=\'{{firstChild.firstChild.src}}\'></a></span>';
				}				
			} else {
				$div_atts['data-cycle-pager-template'] = '<span><a href="#">{{slideNum}}</a></span>';
			}
		}
		

		// start opening <div> tag with our list of attributes
		$atts_str = '';
		foreach ( $div_atts as $key => $val ) {
			$atts_str .= sprintf(' %s="%s"', $key, htmlentities($val) );
		}
		
		// return completed opening tag
		$opening_tag = sprintf( '<div %s>', trim($atts_str) );
		return apply_filters('custom_banners_slideshow_opening_tag', $opening_tag, $atts, $target);
	}
	
	function  get_banner_slideshow_controls_classes($theme)
	{
		$theme_classes = array();
		
		// add class for basename of the theme
		$spot = strpos($theme, '-');
		if ($spot !== FALSE) {
			$theme_basename = substr($theme, 0, $spot);
			$theme_classes[] = sprintf('custom-banners-controls-theme-%s', $theme_basename);
		}
		
		// add class for complete name of the theme
		$theme_classes[] = sprintf('custom-banners-controls-theme-%s', $theme);		
		
		return $theme_classes;
	}
	
	function  get_banner_slideshow_classes($theme)
	{
		$theme_classes = array();
		
		// add class for basename of the theme
		$spot = strpos($theme, '-');
		if ($spot !== FALSE) {
			$theme_basename = substr($theme, 0, $spot);
			$theme_classes[] = sprintf('custom-banners-cycle-slideshow-theme-%s', $theme_basename);
		}
		
		// add class for complete name of the theme
		$theme_classes[] = sprintf('custom-banners-cycle-slideshow-%s', $theme);		
		
		return $theme_classes;
	}
	
	function get_slideshow_controls_html( $atts )
	{
		$theme = $atts['theme'];				
		$theme_classes = $this->get_banner_slideshow_controls_classes($theme);
		$theme_classes_str = implode(' ', $theme_classes);
		$html = sprintf('<div class="custom-b-cycle-controls %s">', $theme_classes_str);
		
		if($atts['prev_next']) {
			$html .= '<div class="custom-b-cycle-prev">&lt;&lt;</div>';
		}
		if($atts['pager'] || $atts['show_pager_icons'] ){
			$html .= '<div class="custom-b-cycle-pager"></div>';
		}
		if($atts['prev_next']) {
			$html .= '<div class="custom-b-cycle-next">&gt;&gt;</div>';
		}
		$html .= '</div>';
		return $html;
	}
	
	function get_banner_by_id($id, $respect_expiration = true)
	{
		$args = array(
			'posts_per_page' => 1,
			'p' => $id,
			'post_type'=> 'banner',
		);

		/* Restrict by expiration date (optional) */
		if ($respect_expiration) {
			$args['meta_query'] = $this->ExpirationDate->get_meta_query();
		}

		$banners_query = new WP_Query( $args );
		$banner = !empty($banners_query->posts) ? $banners_query->posts[0] : false;
		return $banner;
	}
	
	function get_banners_by_atts($atts)
	{
		$args = array(
			'posts_per_page' => $atts['count'],
			'orderby' => 'rand',
			'post_type'=> 'banner',
			'banner_groups' => $atts['group'],
			'nopaging' => ($atts['count'] == '-1'), // turn paging off posts_per_page is unlimited
			'meta_query' => $this->ExpirationDate->get_meta_query()
		);

		$banners_query = new WP_Query( $args );
		$banners = !empty($banners_query->posts) ? $banners_query->posts : array();
		return $banners;
	}
	
	function buildBannerHTML($banner, $banner_id, $atts)
	{
		if($banner_id == ''){			
			$banner_id = $banner->ID;		
		}
	
		$post_thumbnail_id 	= get_post_thumbnail_id( $banner_id );
		$cta 				= $this->get_option_value($banner_id, 'cta_text', '');
		$target_url 		= $this->get_option_value($banner_id, 'target_url', '#');
		$css_class 			= $this->get_option_value($banner_id, 'css_class', '');	
		$use_big_link 		= isset($atts['link_entire_banner']) ? $atts['link_entire_banner'] : get_option('custom_banners_use_big_link');
		$open_in_window 	= isset($atts['open_link_in_new_window']) ? $atts['open_link_in_new_window'] : get_option('custom_banners_open_link_in_new_window');
		$show_captions 		= isset($atts['show_caption']) ? $atts['show_caption'] : !get_option('custom_banners_never_show_captions', 0);
		$show_cta_buttons 	= isset($atts['show_cta_button']) ? ($atts['show_cta_button'] == 1) : !get_option('custom_banners_never_show_cta_buttons', 0);
		$width  			= isset($atts['width']) ? $atts['width'] : 'auto';
		$height  			= isset($atts['height']) ? $atts['height'] : 'auto';
		$banner_width  		= isset($atts['banner_width']) ? $atts['banner_width'] : 'auto';
		$banner_width_px  	= !empty($atts['banner_width_px']) && intval($atts['banner_width_px']) > 0 ? intval($atts['banner_width_px']) : '';
		$banner_height  	= isset($atts['banner_height']) ? $atts['banner_height'] : 'auto';
		$banner_height_px  	= !empty($atts['banner_height_px']) && intval($atts['banner_height_px']) > 0 ? intval($atts['banner_height_px']) : '';
		$theme = isset($atts['theme']) ? $atts['theme'] : get_option('custom_banners_theme');
		$legacy_cta_position = ($theme == 'default_style') ? true : false;//if using the original ("default") theme, order the HTML according to the old style.  Otherwise, new style.
		$slideshow  		= isset($atts['slideshow']) ? $atts['slideshow'] : false;
		
		// filters
		$target_url = apply_filters('custom_banners_target_url', $target_url, $banner_id);
		$css_class = apply_filters('custom_banners_banner_class', $css_class, $banner_id);
		// TODO: add filters for the other attributes
		
		// if no CTA is present but a target URL is, then link the entire banner
		if ( empty($cta) && !empty($target_url) ) {
			$use_big_link = true;
		}
		
		// placeholder variables
		$html = '';
		$img_html = '';
		$banner_style = '';

		// add any extra CSS classes to the banner
		$extra_classes = array($css_class, 'banner-' . $banner_id);
		if (strlen($cta) > 0) {
			$extra_classes[] = 'has_cta';
			
			if($legacy_cta_position){
				$extra_classes[] = 'legacy_cta_position';
			}
		}
		if ($atts['caption_position'] == 'left') {
			$extra_classes[] = 'left';
			$extra_classes[] = 'horiz';
		}
		else if ($atts['caption_position'] == 'right') {
			$extra_classes[] = 'right';
			$extra_classes[] = 'horiz';
		}
		else if ($atts['caption_position'] == 'top') {
			$extra_classes[] = 'top';
			$extra_classes[] = 'vert';
		}
		else if ($atts['caption_position'] == 'bottom') {
			$extra_classes[] = 'bottom';
			$extra_classes[] = 'vert';
		}
		
		//get theme name and theme basename
		//add both via extra classes
		$spot = strpos($theme, '-');
		if ($spot !== FALSE) {
			$theme_basename = substr($theme, 0, $spot);
			$extra_classes[] = sprintf('custom-banners-theme-%s', $theme_basename);
		}
		$extra_classes[] = sprintf('custom-banners-theme-%s', $theme);
		
		$extra_classes_str = implode(' ', $extra_classes);
		
		// we can use either a background image on the banner div, or an <img> tag inside the banner div instead
		$option_use_image_tag = isset($atts['use_image_tag']) ? $atts['use_image_tag'] : false;
		
		// we must force image tags on slideshows, however, or cycle2 wont work in our current configuration
		if ( !empty($atts['slideshow']) ) {
			$option_use_image_tag = true;
		}
		
		// load the featured image, of one was specified
		if ($post_thumbnail_id !== '' && $post_thumbnail_id > 0)
		{
			if (!$option_use_image_tag) 
			{
				$img_src = wp_get_attachment_image_src($post_thumbnail_id, 'full');
				$banner_style = "background-image: url('" . $img_src[0] . "');";
				$img_html = '';
			}
			else {
				$img_style = '';
				$frame_style = '';
				$img_meta = wp_get_attachment_metadata($post_thumbnail_id, 'full');

				if ($banner_width == 'specify') {
					$img_style .= sprintf('width: %spx;', $banner_width_px);
				} else if ( is_numeric($width) ) {
					$img_style .= sprintf( 'width: %spx;', $width );
					$frame_style .= sprintf( 'width: %spx;', $width );
				} else if ( $width == '100_percent' ) {
					$img_style .= 'width: 100%;';
					$frame_style .= 'width: 100%;';
				}
				
				if ($banner_height == 'specify') {
					$img_style .= sprintf('height: %spx;', $banner_height_px);
				} else if ( is_numeric($height) ) {
					$frame_style .= sprintf( 'height: %spx;', $height );
					$img_style .= sprintf( 'height: %spx;', $height );
				}	

				if (strlen($img_style) > 0) {
					$img_atts = array('style' => $img_style);
				} else {
					$img_atts = array();
				}
				
				if ($banner_width == 'specify' && $banner_height == 'specify') {
					$size = array($banner_width_px, $banner_height_px);
				}
				else {
					$size = 'fullsize';
				}
				
				// add frame if slideshow
				$img_html = '';
				if ($slideshow && $atts['auto_height']) {
					$img_html .= sprintf('<div class="banner_frame" style="%s">', $frame_style);
				}
				$img_html .= wp_get_attachment_image($post_thumbnail_id, 'full', $size, $img_atts);
				if ($slideshow && $atts['auto_height']) {
					$img_html .= '</div>';
				}
			}			
		}		
		
		
		if($atts['hide']){
			$banner_display = 'style="display:none; %s"';
		} else {
			$banner_display = 'style="%s"';
		}
		$banner_wrapper_style = sprintf($banner_display, $this->build_banner_wrapper_css($atts));
		
		if($open_in_window){
			$link_target = ' target="_blank" ';
		} else {
			$link_target = '';
		}
		
		$banner_style .= $this->build_banner_css($atts);
		
		// generate the html now
		$html .= '<div class="banner_wrapper" '. $banner_wrapper_style .'>';
			$html .= '<div class="banner ' . $extra_classes_str . '" style="' . $banner_style . '">';
				$html .= $img_html;
				if($use_big_link && !empty($target_url) ){
					$html .= '<a class="custom_banners_big_link" ' . $link_target . ' href="' . $target_url . '"></a>';
				}
				$caption = $banner->post_content;
				if ( $show_captions && (strlen($caption) > 0 || strlen($cta) > 0) )
				{
					$style_str = $this->build_caption_css();
					$html .= '<div class="banner_caption" style="' . $style_str . '">';
						
						//this is the old style of CTA html placement
						//only do this if $legacy_cta_position is true (ie, we're using the 'default' theme)
						if ( $show_cta_buttons && strlen($cta) > 0 && $legacy_cta_position) {
							$html .= $this->get_banner_caption_html($banner_id, $target_url, $link_target, $cta);
						}
						
						$inner_style_str = apply_filters('custom_banners_caption_style', '', $banner_id);
						$html .= '<div class="banner_caption_inner">';
						$html .= '<div class="banner_caption_text" style="' . $inner_style_str . '">';
						$html .= $caption;
						$html .= '</div>';
						
						//this is the new style of CTA html placement
						//only do this if $legacy_cta_position is false (ie, we're not using the 'default' theme)
						if ( $show_cta_buttons && strlen($cta) > 0 && !$legacy_cta_position) {
							$html .= $this->get_banner_caption_html($banner_id, $target_url, $link_target, $cta);
						}
						
						$html .= '</div>';
					$html .= '</div>'; //<!--.banner_caption-->
				}
			$html .= '</div>'; //<!--.banner -->
		$html .= '</div>'; //<!--.banner_wrapper-->
		
		// apply a filter to the completed banner HTML
		$banner_html = apply_filters('custom_banners_banner_html', $html, $banner_id, $atts);
		
		// allow the user to inject before and after HTML via filters
		$before_banner = apply_filters('custom_banners_before_banner', '', $banner_id, $atts);
		$after_banner = apply_filters('custom_banners_after_banner', '', $banner_id, $atts);
		
		// add it all together and return
		return $before_banner . $banner_html . $after_banner;
	}
	
	function get_banner_caption_html($banner_id, $target_url, $link_target, $cta)
	{
		$style_str = apply_filters('custom_banners_cta_button_style', '', $banner_id);
		return sprintf( '<div class="banner_call_to_action"><a href="%s" %s class="banner_btn_cta" style="%s">%s</a></div>',
					    $target_url,
						$link_target,
						$style_str,
						htmlspecialchars($cta)
					  );
	}
	
	function add_stylesheets_and_scripts()
	{
		$cssUrl = plugins_url( 'assets/css/wp-banners.css' , __FILE__ );
		$this->add_stylesheet('wp-banners-css',  $cssUrl);
		
		//theme stylesheets to admin for theme preview section
		$this->add_admin_stylesheet('wp-banners-css',  $cssUrl);
		
		//need to include cycle2 this way, for compatibility with our other plugins
		$jsUrl = plugins_url( 'assets/js/jquery.cycle2.min.js' , __FILE__ );
		$this->add_script('gp_cycle2',  $jsUrl, array( 'jquery' ),	false, true);
		//enqueue it again, on the admin side, for use in the theme preview tool
		$this->add_admin_script('gp_cycle2',  $jsUrl, array( 'jquery' ),	false, true);

		$cb_js_url = plugins_url( 'assets/js/custom-banners.js' , __FILE__ );
		$this->add_script('custom-banners-js',  $cb_js_url, array( 'jquery' ), false, true);
	}
	
	//this is the heading of the new column we're adding to the banner posts list
	function custom_banners_column_head($defaults) {  
		$defaults = 
			array_slice($defaults, 0, 1, true) +
			array("cbp_banner_preview" => "Thumbnail") +
			array_slice($defaults, 1, 1, true) +
			array("single_shortcode" => "Shortcode") +
			array_slice($defaults, 2, count($defaults)-2, true);

		return apply_filters('custom_banners_admin_columns_head', $defaults);
	}  

	//this content is displayed in the banner post list
	function custom_banners_columns_content($column_name, $post_ID)
	{
		if ($column_name == 'cbp_banner_preview') {
			$thumb_html = get_the_post_thumbnail( $post_ID, 'post-thumbnail', array('style' => 'max-width: 100px; height: auto') );
			printf( '<a href="%s">%s</a>', get_edit_post_link($post_ID), $thumb_html );
		}  
		else if ($column_name == 'single_shortcode') {
			$banner_shortcode = get_option("custom_banners_banner_shortcode", 'banner');
			$my_shortcode = sprintf('[%s id="%d"]', $banner_shortcode, $post_ID);
			printf('<textarea rows="1" onclick="this.select();" class="gp_code" style="height: 2em; margin-top: 10px; max-width:100%%">%s</textarea>', $my_shortcode);
		}
		
		do_action('custom_banners_admin_columns_content', $column_name, $post_ID);
	} 

	//this is the heading of the new column we're adding to the banner category list
	function custom_banners_cat_column_head($defaults) {  
		$defaults = array_slice($defaults, 0, 2, true) +
		array("single_shortcode" => "Shortcode") +
		array_slice($defaults, 2, count($defaults)-2, true);
		return $defaults;  
	}  

	//this content is displayed in the banner category list
	function custom_banners_cat_columns_content($value, $column_name, $tax_id) {  

		$category = get_term_by('id', $tax_id, 'banner_groups');
		
		return "<code>[banner group='{$category->slug}']</code>"; 
	}

	//register any widgets here
	function custom_banners_register_widgets() {
		include('lib/widgets/single_banner_widget.php');
		include('lib/widgets/rotating_banner_widget.php');
		include('lib/widgets/banner_list_widget.php');
		
		register_widget( 'singleBannerWidget' );
		register_widget( 'rotatingBannerWidget' );
		register_widget( 'bannerListWidget' );
	}
	
	//add an inline link to the settings page, before the "deactivate" link
	function add_settings_link_to_plugin_action_links($links) {
	  $settings_link = '<a href="admin.php?page=custom-banners-settings">Settings</a>';
	  array_unshift($links, $settings_link); 
	  return $links; 
	}

	// add inline links to our plugin's description area on the Plugins page
	function add_custom_links_to_plugin_description($links, $file) { 

		/** Get the plugin file name for reference */
		$plugin_file = plugin_basename( __FILE__ );
	 
		/** Check if $plugin_file matches the passed $file name */
		if ( $file == $plugin_file )
		{		
			$new_links['settings_link'] = '<a href="admin.php?page=custom-banners-settings">Settings</a>';
			$new_links['support_link'] = '<a href="http://goldplugins.com/contact/?utm-source=plugin_menu&utm_campaign=support" target="_blank">Get Support</a>';
				
			if(!$this->is_pro){
				$new_links['upgrade_to_pro'] = '<a href="http://goldplugins.com/our-plugins/custom-banners/upgrade-to-custom-banners-pro/?utm_source=plugin_menu&utm_campaign=upgrade" target="_blank">Upgrade to Pro</a>';
			}
			
			$links = array_merge( $links, $new_links);
		}
		return $links; 
	}
	
		
	/* Displays a meta box with the shortcodes to display the current banner */
	function display_shortcodes_meta_box() {
		global $post;
		echo "Copy &amp; Paste this shortcode into any post or page to display this banner:<br />";
		$banner_shortcode = get_option("custom_banners_banner_shortcode", 'banner');			
		$my_shortcode = sprintf('[%s id="%d"]', $banner_shortcode, $post->ID);
		printf('<textarea rows="1" onclick="this.select();" class="gp_code" style="width: 100%%; height: 2em; margin-top: 10px;">%s</textarea>', $my_shortcode);
	}

	function add_meta_boxes(){
		add_meta_box( 'banner_shortcodes', 'Shortcodes', array($this, 'display_shortcodes_meta_box'), 'banner', 'side', 'default' );
	}
	
	
	/*
	 * Builds a CSS string for the banner wrapper. Primarily controls height and width
	 *
	 * @param	$atts		Attributes from the shortcode
	 *
	 * @returns	string		The completed CSS string, with the values inlined
	 */
	function build_banner_css($atts)
	{	
		$option_use_image_tag = isset($atts['use_image_tag']) ? $atts['use_image_tag'] : false;
		
		$defaults = array(
						'width' => get_option('custom_banners_default_width', ''),
						'height' => get_option('custom_banners_default_height', ''),
						'banner_width' 		=> 'auto',
						'banner_width_px' 	=> '',
						'banner_height' 	=> 'auto',
						'banner_height_px' 	=> '',						
					);
		$atts = array_merge($defaults, $atts);	
		
		$banner_width  		= isset($atts['banner_width']) ? $atts['banner_width'] : 'auto';
		$banner_width_px  	= !empty($atts['banner_width_px']) && intval($atts['banner_width_px']) > 0 ? intval($atts['banner_width_px']) : '';
		$banner_height  	= isset($atts['banner_height']) ? $atts['banner_height'] : 'auto';
		$banner_height_px  	= !empty($atts['banner_height_px']) && intval($atts['banner_height_px']) > 0 ? intval($atts['banner_height_px']) : '';

		$slideshow  		= isset($atts['slideshow']) ? $atts['slideshow'] : false;
		if ( $slideshow ) {
			$banner_width = 'auto';
			$banner_width_px = '';
			$banner_height = 'auto';
			$banner_height_px = '';
			$atts['width'] = '';
			$atts['height'] = '';
		}
		
		if ($banner_width == 'specify') {
			$atts['width'] = $banner_width_px;
		}
		
		if ($banner_height == 'specify') {
			$atts['height'] = $banner_height_px;
		}						
		
		$css_rule_template = ' %s: %s;';
		$output = '';
		
		/* 
		 * Width
		 */
		$option_val = $atts['width'];		
		if (!empty($option_val) || $banner_width == '100_percent' || $banner_width == 'auto') {
			if ($banner_width == 'auto' && $option_use_image_tag) {
				$option_val = 'auto';
				$output .= sprintf($css_rule_template, 'width', $option_val);
			}
			else if ($banner_width == '100_percent') {
				$option_val = '100%';
				$output .= sprintf($css_rule_template, 'width', $option_val);
			}
			else if ( is_numeric($option_val) ) {
				$option_val .= 'px';
				$output .= sprintf($css_rule_template, 'width', $option_val);
			}
		}
		
		/* 
		 * Height
		 */
		$option_val = $atts['height'];
		if (!empty($option_val) || $banner_height == 'auto') {
			if ($banner_height == 'auto' && $option_use_image_tag) {
				$option_val = 'auto';
				$output .= sprintf($css_rule_template, 'height', $option_val);
			}
			else if ( is_numeric($option_val)&& !$option_use_image_tag ) {
				$option_val .= 'px';
				$output .= sprintf($css_rule_template, 'height', $option_val);
			}			
		}		
		
		// return the completed CSS string
		return trim($output);
	}
	
	/*
	 * Builds a CSS string for the slideshow wrapper. Primarily controls height and width
	 *
	 * @param	$atts		Attributes from the shortcode
	 *
	 * @returns	string		The completed CSS string, with the values inlined
	 */
	function build_slideshow_wrapper_css($atts)
	{	
		$option_use_image_tag = isset($atts['use_image_tag']) ? $atts['use_image_tag'] : false;
		
		$defaults = array(
						'width' => get_option('custom_banners_default_width', ''),
						'height' => get_option('custom_banners_default_height', ''),
						'banner_width' 		=> 'auto',
						'banner_width_px' 	=> '',
						'banner_height' 	=> 'auto',
						'banner_height_px' 	=> '',						
					);
		$atts = array_merge($defaults, $atts);	
		
		$width  			= isset($atts['width']) ? $atts['width'] : 'auto';
		$height  			= isset($atts['height']) ? $atts['height'] : 'auto';
		$banner_width  		= isset($atts['banner_width']) ? $atts['banner_width'] : 'auto';
		$banner_width_px  	= !empty($atts['banner_width_px']) && intval($atts['banner_width_px']) > 0 ? intval($atts['banner_width_px']) : '';
		$banner_height  	= isset($atts['banner_height']) ? $atts['banner_height'] : 'auto';
		$banner_height_px  	= !empty($atts['banner_height_px']) && intval($atts['banner_height_px']) > 0 ? intval($atts['banner_height_px']) : '';
		$slideshow  		= true;
		
		if ($banner_width == 'specify') {
			$atts['width'] = $banner_width_px;
		} else if ( is_numeric($width) ) {
			$atts['width'] = $width;
		} else if ( $width == '100_percent' ) {
			$atts['width'] = '100%';
		}
		
		if ($banner_height == 'specify') {
			$atts['height'] = $banner_height_px;
		} else if ( is_numeric($height) ) {
			$atts['height'] = $height;
		}

		$css_rule_template = ' %s: %s;';
		$output = '';
		
		/* 
		 * Width
		 */
		$option_val = $atts['width'];		
		if (!empty($option_val) || $banner_width == '100_percent' || $banner_width == 'auto') {
			if ($banner_width == 'auto' && $option_use_image_tag) {
				$option_val = 'auto';
				$output .= sprintf($css_rule_template, 'width', $option_val);
			}
			else if ($banner_width == '100_percent') {
				$option_val = '100%';
				$output .= sprintf($css_rule_template, 'width', $option_val);
			}
			else if ( is_numeric($option_val) ) {
				$option_val .= 'px';
				$output .= sprintf($css_rule_template, 'width', $option_val);
			}
		}
		
		/* 
		 * Height
		 */
		$option_val = $atts['height'];
		if (!empty($option_val) || $banner_height == 'auto') {
			if ($banner_height == 'auto' && $option_use_image_tag) {
				$option_val = 'auto';
				$output .= sprintf($css_rule_template, 'height', $option_val);
			}
			else if ( is_numeric($option_val) ) {
				$option_val .= 'px';
				$output .= sprintf($css_rule_template, 'height', $option_val);
			}			
		}		

		// return the completed CSS string
		return trim($output);
	}
	
	/*
	 * Builds a CSS string for the banner itself. Primarily controls height and width
	 *
	 * @param	$atts		Attributes from the shortcode
	 *
	 * @returns	string		The completed CSS string, with the values inlined
	 */
	function build_banner_wrapper_css($atts)
	{
		$defaults = array(
						'width' 			=> get_option('custom_banners_default_width', ''),
						'height'			=> get_option('custom_banners_default_height', ''),
						'banner_width' 		=> '',
						'banner_width_px' 	=> '',
						'banner_height' 	=> '',
						'banner_height_px' 	=> '',
						
					);
		$atts = shortcode_atts($defaults, $atts);
		$banner_width  		= isset($atts['banner_width']) ? $atts['banner_width'] : '';
		$banner_width_px  	= !empty($atts['banner_width_px']) && intval($atts['banner_width_px']) > 0 ? intval($atts['banner_width_px']) : '';
		$banner_height  	= isset($atts['banner_height']) ? $atts['banner_height'] : '';
		$banner_height_px  	= !empty($atts['banner_height_px']) && intval($atts['banner_height_px']) > 0 ? intval($atts['banner_height_px']) : '';

		if ($banner_width == 'specify') {
			$atts['width'] = $banner_width_px;
		}
		
		if ($banner_height == 'specify') {
			$atts['height'] = $banner_height_px;
		}		
				
		
		$css_rule_template = ' %s: %s;';
		$output = '';

		/* 
		 * Width
		 */
		if ($banner_width != 'auto')
		{
			$option_val = $atts['width'];		
			if (!empty($option_val) || $banner_width == '100_percent') {
				if ($banner_width == '100_percent') {
					$option_val = '100%';
					$output .= sprintf($css_rule_template, 'width', $option_val);
				}
				else if ( is_numeric($option_val) ) {
					$option_val .= 'px';
					$output .= sprintf($css_rule_template, 'width', $option_val);
				}
			}
		}
		
		/* 
		 * Height
		 */
		if ($banner_height != 'auto') // TODO: maybe disable this altogether			
		{
			$option_val = $atts['height'];		
			if (!empty($option_val)) {
				if ( is_numeric($option_val) ) {
					$option_val .= 'px';
					$output .= sprintf($css_rule_template, 'height', $option_val);
				}
			}		
		}

		// return the completed CSS string
		return trim($output);
	}
	
	/*
	 * Builds a CSS string for the banner's caption
	 *
	 * @param	$atts		Attributes from the shortcode
	 *
	 * @returns	string		The completed CSS string, with the values inlined
	 */
	function build_caption_css($atts = array())
	{
		$css_rule_template = ' %s: %s;';
		$output = '';
		/* 
		 * Background Color + Opacity
		 */
		$color_val = get_option('custom_banners_caption_background_color', '');
		$opacity_val = get_option('custom_banners_caption_background_opacity', '');
		
		if (!empty($color_val)) {
			// convert the hex string into an "rgba()" string
			$opacity = !empty($opacity_val) ? ($opacity_val / 100) : 1;		
			$rgba = $this->hex2rgba($color_val, $opacity);
			$output .= sprintf($css_rule_template, 'background-color', $rgba);
		}		
		
		/* 
		 * Background Opacity
		 */
		if (!empty($option_val)) {
			$output .= sprintf($css_rule_template, 'opacity', $option_val);
		}		
		
		/* 
		 * Remove Background image if Background Color / Opacity was specified
		 */
		if (!empty($output)) {
			$output .= sprintf($css_rule_template, 'background-image', 'none');
		}
		// return the completed CSS string
		return trim($output);	
	}
	
	 
	function hex2rgba($color, $opacity = false) { 
		$default = 'rgb(0,0,0)';
 
		//Return default if no color provided
		if(empty($color)) {
			return $default;
		}
 
		//Sanitize $color if "#" is provided 
		if ($color[0] == '#' ) {
			$color = substr( $color, 1 );
		}
 
		//Check if color has 6 or 3 characters and get values
		if (strlen($color) == 6) {
				$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
				$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
				return $default;
		}
 
		//Convert hexadec to rgb
		$rgb =  array_map('hexdec', $hex);
 
		//Check if opacity is set(rgba or rgb)
		if($opacity){
			if(abs($opacity) > 1)
				$opacity = 1.0;
			$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
		} else {
			$output = 'rgb('.implode(",",$rgb).')';
		}
 
		//Return rgb(a) color string
		return $output;
	}
	
	function add_settings_link()
	{
		do_action('custom_banners_before_add_settings_link');
		$hook_suffix = add_submenu_page( 'edit.php?post_type=banner', 'Settings', 'Settings', 'administrator', 'custom-banners-settings-redirect', array($this, 'settings_link_redirect') ); 
		add_action("load-$hook_suffix", array($this, 'settings_link_redirect'));
		do_action('custom_banners_after_add_settings_link');
	}
	
	function settings_link_redirect()
	{
		$settings_page_url = admin_url('admin.php?page=custom-banners-settings');
		wp_redirect($settings_page_url);
		exit();
	}
		
	function add_extra_classes_to_admin_menu() 
	{
		global $menu;
		if ( !empty($menu) ) {
			foreach( $menu as $key => $value ) {
				if( 'Custom Banners Settings' == $value[0] ) {
					$extra_classes = 'custom_banners_admin_menu';
					$extra_classes .= $this->is_pro
									  ? ' custom_banners_pro_admin_menu'
									  : ' custom_banners_free_admin_menu';
					$menu[$key][4] .= ' ' . $extra_classes;
				}
			}
		}
	}		
}
$ebp = new CustomBannersPlugin();

// Initialize any addons now
do_action('custom_banners_bootstrap');