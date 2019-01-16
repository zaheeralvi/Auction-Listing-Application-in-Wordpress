<?php
/*
This file is part of Custom Banners.

Custom Banners is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Custom Banners is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with The Custom Banners.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once("custom_banners_config.php");

class customBannersOptions
{
	var $textdomain = '';
	
	function __construct(){
		//may be running in non WP mode (for example from a notification)
		if(function_exists('add_action')){
			//add a menu item
			add_action( 'admin_menu', array($this, 'add_admin_menu_items') );	
			add_action( 'admin_init', array( $this, 'admin_scripts' ) );
			add_action( 'admin_head', array($this, 'admin_css') );
			add_action( 'custom_banners_admin_settings_page_top', array($this, 'settings_page_top') );
			//add_action( 'custom_banners_admin_settings_page_bottom', array($this, 'settings_page_bottom') );
		}
		
		//instantiate Sajak so we get our JS and CSS enqueued
		new GP_Sajak();		
		
		$this->shed = new Custom_Banners_GoldPlugins_BikeShed();

		add_filter('update_option_custom_banners_registered_key', array($this, 'recheck_key') );
		add_action( 'admin_menu', array($this, 'add_upgrade_to_pro_link'), 20 ); // add late, to end of list
	}
	
	function add_admin_menu_items()
	{
		$title = "Custom Banners Settings";
		$page_title = "Custom Banners Settings";
		$top_level_slug = "custom-banners-settings";
		
		$submenu_pages = array(
			array(
				'parent_slug' => $top_level_slug,
				'page_title' => 'Basic Options',
				'menu_title' => 'Basic Options',
				'capability' => 'administrator',
				'menu_slug' => $top_level_slug,
				'callback' =>  array($this, 'basic_settings_page')
			),
			array(
				'parent_slug' => $top_level_slug,
				'page_title' => 'Theme Options',
				'menu_title' => 'Theme Options',
				'capability' => 'administrator',
				'menu_slug' => 'custom-banners-themes',
				'callback' => array($this, 'themes_page')
			),
			array(
				'parent_slug' => $top_level_slug,
				'page_title' => 'Help & Instructions',
				'menu_title' => 'Help & Instructions',
				'capability' => 'administrator',
				'menu_slug' => 'custom-banners-help',
				'callback' => array($this, 'help_page')
			),
		);
		
		$submenu_pages = apply_filters("custom_banners_admin_settings_submenu_pages", $submenu_pages, $top_level_slug);
		
		//create new top-level menu
		add_menu_page($page_title, $title, 'administrator', $top_level_slug, array($this, 'basic_settings_page'));
		
		foreach($submenu_pages as $submenu_page){
			add_submenu_page($submenu_page['parent_slug'] , $submenu_page['page_title'], $submenu_page['menu_title'], $submenu_page['capability'],$submenu_page['menu_slug'], $submenu_page['callback']);
		}

		//call register settings function
		add_action( 'admin_init', array($this, 'register_settings'));	
	}	
	
	function add_upgrade_to_pro_link()
	{
		$top_level_slug = "custom-banners-settings";
		if ( !isValidCBKey() ) {
			add_submenu_page(
				$top_level_slug,
				__('Upgrade To Pro'),
				__('Upgrade To Pro'),
				'administrator',
				'custom-banners-upgrade-to-pro',
				array($this, 'render_upgrade_page')
			);
		}
	}
	
	function get_submenu_pages($top_level_slug)
	{
		$submenu_pages = array();
		
		$submenu_pages[] = array(
			'parent_slug' => $top_level_slug,
			'page_title' => 'Basic Options',
			'menu_title' => 'Basic Options',
			'capability' => 'administrator',
			'menu_slug' => $top_level_slug,
			'callback' => array($this, 'basic_settings_page')
		);
				
		$submenu_pages[] = array(
			'parent_slug' => $top_level_slug,
			'page_title' => 'Help &amp; Instructions',
			'menu_title' => 'Help &amp; Instructions',
			'capability' => 'administrator',
			'menu_slug' => 'custom-banners-help',
			'callback' => array($this, 'help_page')
		);
		
		return apply_filters('custom_banners_admin_settings_submenu_pages', $submenu_pages, $top_level_slug);
	}

	function get_admin_tabs($top_level_slug)
	{
		$submenu_pages = $this->get_submenu_pages($top_level_slug);
		$tabs = array();
		foreach ($submenu_pages as $page) {
			$slug = $page['menu_slug'];
			if ( empty($slug) ) {
				$slug = $top_level_slug;
			}
			$tabs[$slug] = $page['menu_title'];
		}
		return apply_filters('custom_banners_admin_tabs', $tabs, $top_level_slug);
	}

	function register_settings(){
		//register our settings
		register_setting( 'custom-banners-settings-group', 'custom_banners_custom_css' );
		register_setting( 'custom-banners-settings-group', 'custom_banners_use_big_link' );
		register_setting( 'custom-banners-settings-group', 'custom_banners_open_link_in_new_window' );
		register_setting( 'custom-banners-settings-group', 'custom_banners_never_show_captions' );
		register_setting( 'custom-banners-settings-group', 'custom_banners_never_show_cta_buttons' );
		register_setting( 'custom-banners-settings-group', 'custom_banners_default_width' );
		register_setting( 'custom-banners-settings-group', 'custom_banners_default_height' );
		register_setting( 'custom-banners-settings-group', 'custom_banners_banner_shortcode' );
		
		register_setting( 'custom-banners-settings-group', 'custom_banners_registered_name' );
		register_setting( 'custom-banners-settings-group', 'custom_banners_registered_url' );
		register_setting( 'custom-banners-settings-group', 'custom_banners_registered_key' );
		register_setting( 'custom-banners-settings-group', 'custom_banners_cache_buster', array($this, 'bust_options_cache') );

		register_setting( 'custom-banners-theme-settings-group', 'custom_banners_theme' );
		register_setting( 'custom-banners-theme-settings-group', 'custom_banners_preview_window_background' );

		register_setting( 'custom-banners-style-settings-group', 'custom_banners_caption_background_color' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_caption_background_opacity' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_cta_background_color' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_cta_border_color' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_cta_border_radius' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_cta_button_font_size' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_cta_button_font_style' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_cta_button_font_family' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_cta_button_font_color' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_caption_font_size' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_caption_font_style' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_caption_font_family' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_caption_font_color' );
		register_setting( 'custom-banners-style-settings-group', 'custom_banners_cache_buster', array($this, 'bust_options_cache') );
	}
	
	//function to produce tabs on admin screen
	function admin_tabs($current = 'homepage' ) {
		$tabs = array( 	
			array(
				'menu_slug' => 'custom-banners-settings',
				'menu_title' => __('Basic Options', $this->textdomain),
			),
			array(
				'menu_slug' => 'custom-banners-themes',
				'menu_title' =>	__('Theme Options', $this->textdomain),
			),
			array(
				'menu_slug' => 'custom-banners-help',
				'menu_title' => __('Help &amp; Instructions', $this->textdomain),
			)
		);
				
		$tabs = apply_filters('custom_banners_admin_settings_tabs', $tabs);
						
		echo '<div id="icon-themes" class="icon32"><br></div>';
		echo '<h2 class="nav-tab-wrapper">';
			foreach( $tabs as $tab){
				$class = ( $tab['menu_slug'] == $current ) ? ' nav-tab-active' : '';
				echo "<a class='nav-tab{$class}' href='?page={$tab['menu_slug']}'>{$tab['menu_title']}</a>";
			}
		echo '</h2>';
	}
	
	function admin_scripts()
	{
		wp_enqueue_script(
			'gp-admin_v2',
			plugins_url('../assets/js/gp-admin_v2.js', __FILE__),
			array( 'jquery' ),
			false,
			true
		);	
	}
		
	function admin_css()
	{
		if(is_admin()) {
			$admin_css_url = plugins_url( '../assets/css/admin_style.css' , __FILE__ );
			wp_register_style('custom-banners-admin', $admin_css_url);
			wp_enqueue_style('custom-banners-admin');
		}	
	}

	function settings_page_top(){
		$title = "Custom Banners Settings";
		$message = "Custom Banners Settings Updated.";
		
		global $pagenow;
	?>
	<div class="wrap gold_plugins_settings">
		<h2><?php echo $title; ?></h2>
		
		<p class="cb_need_help">Need Help? <a href="http://goldplugins.com/documentation/custom-banners-documentation/" target="_blank">Click here</a> to read instructions, see examples, and find more information on how to add, edit, update, and output your custom banners.</p>
		
		<?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') : ?>
		<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
		<?php endif;
		
		$this->get_and_output_current_tab($pagenow);
	}
	
	function get_and_output_current_tab($pagenow){
		$tab = $_GET['page'];
		
		$this->admin_tabs($tab); 
				
		return $tab;
	}	
	
	//this function outputs the Basic settings tab and everything within it.
	function basic_settings_page()
	{	
		//instantiate tabs object for output basic settings page tabs
		$tabs = new GP_Sajak( array(
			'header_label' => 'Basic Options',
			'settings_field_key' => 'custom-banners-settings-group' // can be an array	  	
		) );
	
		$this->settings_page_top();
		$tabs->add_tab(
			'general_options', // section id, used in url fragment
			'General Settings', // section label
			array($this, 'output_general_settings'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'gear' // icons here: http://fontawesome.io/icons/
			)
		);
	
		$tabs->add_tab(
			'shortcode_options', // section id, used in url fragment
			'Shortcode Settings', // section label
			array($this, 'output_shortcode_settings'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'gear' // icons here: http://fontawesome.io/icons/
			)
		);
		
		$tabs->display();
	} // end basic_settings_page function	
	
	//output the options found in the General Settings tab
	function output_general_settings(){
		?>					
			<h3>General Options</h3>
			<p>These options control the banner default settings.</p>
		
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="custom_banners_custom_css">Custom CSS</a></th>
					<td><textarea name="custom_banners_custom_css" id="custom_banners_custom_css"><?php echo get_option('custom_banners_custom_css'); ?></textarea>
					<p class="description">Input any Custom CSS you want to use here.  The plugin will work without you placing anything here - this is useful in case you need to edit any styles for it to work with your theme, though.<br/> For a list of available classes, click <a href="http://goldplugins.com/documentation/custom-banners-documentation/html-css-information-for-custom-banners/" target="_blank">here</a>.</p></td>
				</tr>
			</table>
			
			<table class="form-table">
			<?php
				$this->text_input('custom_banners_default_width', 'Default Banner Width', 'Enter a default width for your banners, in pixels. If not specified, the banner will try to fit its container.', '');
				$this->text_input('custom_banners_default_height', 'Default Banner Height', 'Enter a default height for your banners, in pixels. If not specified, the banner will try to fit its container.', '');
			?>
			</table>

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="custom_banners_use_big_link">Link Entire Banner</label></th>
					<td><input type="checkbox" name="custom_banners_use_big_link" id="custom_banners_use_big_link" value="1" <?php if(get_option('custom_banners_use_big_link')){ ?> checked="CHECKED" <?php } ?>/>
					<p class="description">If checked, the entire banner will be linked to the Target URL - not just the CTA.</p>
					</td>
				</tr>
			</table>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="custom_banners_open_link_in_new_window">Open Link in New Window</label></th>
					<td><input type="checkbox" name="custom_banners_open_link_in_new_window" id="custom_banners_open_link_in_new_window" value="1" <?php if(get_option('custom_banners_open_link_in_new_window')){ ?> checked="CHECKED" <?php } ?>/>
					<p class="description">If checked, the Banner Link / CTA will open in a New Window.</p>
					</td>
				</tr>
			</table>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="custom_banners_never_show_captions">Never Show Captions</label></th>
					<td><input type="checkbox" name="custom_banners_never_show_captions" id="custom_banners_never_show_captions" value="1" <?php if(get_option('custom_banners_never_show_captions')){ ?> checked="CHECKED" <?php } ?>/>
					<p class="description">If checked, your banners will not show their captions, even if you enter one.</p>
					</td>
				</tr>
			</table>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="custom_banners_never_show_cta_buttons">Never Show CTA Buttons</label></th>
					<td><input type="checkbox" name="custom_banners_never_show_cta_buttons" id="custom_banners_never_show_cta_buttons" value="1" <?php if(get_option('custom_banners_never_show_cta_buttons')){ ?> checked="CHECKED" <?php } ?>/>
					<p class="description">If checked, your banners will not show their buttons, even if you have entered a call to action.</p>
					</td>
				</tr>
			</table>
		<?php
	}
	
	//output the options found in the Shortcode Settings tab
	function output_shortcode_settings(){
		?>					
			<h3>Shortcode Options</h3>
			<p>These options control the registered banner shortcode.  You can change this value to address potential conflicts with other plugins or themes that use the same shortcodes.</p>
			<table class="form-table">
			<?php
				$this->text_input('custom_banners_banner_shortcode', 'Banner Shortcode', 'Default Value: banner.', 'banner');
			?>
			</table>
			<?php
	}
	
	//this function outputs the Help tab with Galahad and everything within it.
	function help_page()
	{				
		//instantiate tabs object for output basic settings page tabs
		$tabs = new GP_Sajak( array(
			'header_label' => 'Help &amp; Instructions',
			'settings_field_key' => 'custom-banners-help', // can be an array	
			'show_save_button' => false, // hide save buttons for all panels   		
		) );
		
		$help_tabs[] = array(
			'id' => 'help', 
			'label' => __('Help Center', 'custom-banners'),
			'callback' => array($this, 'output_help_page'),
			'options' => array('icon' => 'life-buoy')
		);
		
		$help_tabs = apply_filters('custom_banners_admin_help_tabs', $help_tabs);
		
		foreach( $help_tabs as $help_tab ) {
			$tabs->add_tab(
				$help_tab['id'], // section id, used in url fragment
				$help_tab['label'], // section label
				$help_tab['callback'], // display callback
				$help_tab['options']
			);
		}		
	
		$this->settings_page_top();
		$tabs->display();
		
	} // end help_page function
		
	function output_help_page()
	{
		?>
		<h3>Help Center</h3>
		<div class="help_box">
			<h4>Have a Question?  Check out our FAQs!</h4>
			<p>Our FAQs contain answers to our most frequently asked questions.  This is a great place to start!</p>
			<p><a class="custom_banners_support_button" target="_blank" href="https://goldplugins.com/documentation/custom-banners-documentation/custom-banners-faqs/?utm_source=help_page">Click Here To Read FAQs</a></p>
		</div>
		<div class="help_box">
			<h4>Looking for Instructions? Check out our Documentation!</h4>
			<p>For a good start to finish explanation of how to add Banners and then display them on your site, check out our Documentation!</p>
			<p><a class="custom_banners_support_button" target="_blank" href="https://goldplugins.com/documentation/custom-banners-documentation/?utm_source=help_page">Click Here To Read Our Docs</a></p>
		</div>
		<?php		
	}
	
	
	/* 
	 * Output the upgrade page
	 */
	function render_upgrade_page()
	{
		//setup coupon box
		$upgrade_page_settings = array(
			'plugin_name' 		=> 'Custom Banners Pro',
			'pitch' 			=> "When you upgrade, you'll instantly unlock advanced features including Click Tracking, Professionally Designed Themes, Advanced Transitions, and more!",
			'learn_more_url' 	=> 'https://goldplugins.com/our-plugins/custom-banners-pro/?utm_source=cpn_box&utm_campaign=upgrade&utm_banner=learn_more',
			'upgrade_url' 		=> 'https://goldplugins.com/our-plugins/custom-banners-pro/upgrade-to-custom-banners-pro/?utm_source=plugin_menu&utm_campaign=upgrade',
			'upgrade_url_promo' => 'https://goldplugins.com/purchase/custom-banners-pro/single?promo=newsub10',
			'text_domain' => 'custom-banners',
			'testimonial' => array(
				'title' => 'Service is second to none',
				'body' => 'I can highly recommend Gold Plugins, their service is second to none. They looked after me when I needed to transfer the license from the developers details to mine. They promptly answered all of my questions to a great standard.',
				'name' => 'Matthew Edwards',
			)
		);
		$img_base_url = plugins_url('../assets/img/upgrade/', __FILE__);
		?>		
		<div class="custom_banners_admin_wrap">
			<div class="gp_upgrade">
				<h1 class="gp_upgrade_header">Upgrade To Custom Banners Pro</h1>
				<div class="gp_upgrade_body">
				
					<div class="header_wrapper">
						<div class="gp_slideshow">
							<ul>
								<li class="slide"><img src="<?php echo $img_base_url . 'banner-style-example.png'; ?>" alt="Example Banner using Classic Tile Style" /><div class="caption">Choose from over 50 professionally designed themes</div></li>
								<li class="slide"><img src="<?php echo $img_base_url . 'reports-graph-1.png'; ?>" alt="Screenshot of an example report" /><div class="caption">Advanced reports show Clicks, Impressions, and CTR for each of your banners</div></li>
								<li class="slide"><img src="<?php echo $img_base_url . 'tracking-options.png'; ?>" alt="Screenshot of the Tracking Options screen" /><div class="caption">Control tracking options and exclude visitors as needed</div></li>
								<li class="slide"><img src="<?php echo $img_base_url . 'contact-support.png'; ?>" alt="Screenshot of the Contact Support form" /><div class="caption">Contact Support directly from within the plugin!</div></li>
							</ul>
							<a href="#" class="control_next">></a>
							<a href="#" class="control_prev"><</a>							
						</div>

						<script type="text/javascript">
							jQuery(function () {
								if (typeof(gold_plugins_init_upgrade_slideshow) == 'function') {
									gold_plugins_init_upgrade_slideshow();
								}
							});
						</script>						
						<div class="customer_testimonial">
								<div class="stars">
									<span class="dashicons dashicons-star-filled"></span>
									<span class="dashicons dashicons-star-filled"></span>
									<span class="dashicons dashicons-star-filled"></span>
									<span class="dashicons dashicons-star-filled"></span>
									<span class="dashicons dashicons-star-filled"></span>
								</div>
								<p class="customer_testimonial_title"><strong><?php echo $upgrade_page_settings['testimonial']['title']; ?></strong></p>
								“<?php echo $upgrade_page_settings['testimonial']['body']; ?>”
								<p class="author">— <?php echo $upgrade_page_settings['testimonial']['name']; ?></p>
						</div>
					</div>
					<div style="clear:both;"></div>
					<p class="upgrade_intro"><strong>Custom Banners Pro</strong> is the professional edition of Custom Banners. It adds 50+ new themes, conversion tracking, reports, and advanced styling options to Custom Banners, and is a great choice for anyone who wants to optimize their banners' performance.</p>
					<div class="upgrade_left_col">
						<div class="upgrade_left_col_inner">
							<h3>Custom Banners Pro Adds Powerful New Features, Including:</h3>
							<ul>
								<li>50+ Professionally Designed Themes</li>
								<li>Click and Impression Tracking</li>
								<li>Performance reports for all of your banners</li>
								<li>Choose any Fonts and Colors for your Captions and Call to Action Buttons</li>
								<li>Advanced Transitions, such as the Shuffle</li>
								<li>Outstanding support from our developers</li>
								<li>A full year of technical support & automatic updates</li>
							</ul>

							<p class="all_features_link">And many more! <a href="https://goldplugins.com/downloads/custom-banners-pro/?utm_source=custom_banners_upgrade_page_plugin&amp;utm_campaign=see_all_features">Click here for a full list of features included in Custom Banners Pro</a>.</p>
							<p class="upgrade_button"><a href="https://goldplugins.com/special-offers/upgrade-to-custom-banners-pro/?utm_source=custom_banners_free_plugin&utm_campaign=upgrade_page_button">Learn More</a></p>
						</div>
					</div>
					<div class="bottom_cols">
						<div class="how_to_upgrade">
							<h4>How To Upgrade:</h4>
							<ol>
								<li><a href="https://goldplugins.com/special-offers/upgrade-to-custom-banners-pro/?utm_source=custom_banners_free_plugin&utm_campaign=how_to_upgrade_steps">Purchase an API Key from GoldPlugins.com</a></li>
								<li>Install and Activate the Custom Banners Pro plugin.</li>
								<li>Go to Custom Banners Settings &raquo; License Options menu, enter your API key, and click Activate.</li>
							</ol>
							<p class="upgrade_more">That's all! Upgrading takes just a few moments, and won't affect your data.</p>
						</div>
						<div class="questions">	<h4>Have Questions?</h4>
							<p class="questions_text">We can help. <a href="https://goldplugins.com/contact/">Click here to Contact Us</a>.</p>
							<p class="all_plans_include_support">All plans include a full year of technical support.</p>
						</div>
					</div>
				</div>
				
				<div id="signup_wrapper" class="upgrade_sidebar">
					<div id="mc_embed_signup">
						<div class="save_now">
							<h3>Save 10% Now!</h3>
							<p class="pitch">Subscribe to our newsletter now, and we’ll send you a coupon for 10% off your upgrade to the Pro version.</p>
						</div>
						<form action="https://goldplugins.com/atm/atm.php?u=403e206455845b3b4bd0c08dc&amp;id=a70177def0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate="">
							<div class="fields_wrapper">
								<label for="mce-NAME">Your Name (optional)</label>
								<input value="golden" name="NAME" class="name" id="mce-NAME" placeholder="Your Name" type="text">
								<label for="mce-EMAIL">Your Email</label>
								<input value="services@illuminatikarate.com" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required="" type="email">
								<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
								<div style="position: absolute; left: -5000px;"><input name="b_403e206455845b3b4bd0c08dc_6ad78db648" tabindex="-1" value="" type="text"></div>
							</div>
							<div class="clear"><input value="Send My Coupon" name="subscribe" id="mc-embedded-subscribe" class="whiteButton" type="submit"></div>
							<p class="secure"><img src="<?php echo plugins_url( "../assets/img/lock.png", __FILE__ );?>" alt="Lock" width="16px" height="16px">We respect your privacy.</p>
							
							<input id="mc-upgrade-plugin-name" name="mc-upgrade-plugin-name" value="<?php echo htmlentities($upgrade_page_settings['plugin_name']); ?>" type="hidden">
							<input id="mc-upgrade-link-per" name="mc-upgrade-link-per" value="<?php echo $upgrade_page_settings['upgrade_url_promo']; ?>" type="hidden">
							<input id="mc-upgrade-link-biz" name="mc-upgrade-link-biz" value="<?php echo $upgrade_page_settings['upgrade_url_promo']; ?>" type="hidden">
							<input id="mc-upgrade-link-dev" name="mc-upgrade-link-dev" value="<?php echo $upgrade_page_settings['upgrade_url_promo']; ?>" type="hidden">
							<input id="gold_plugins_already_subscribed" name="gold_plugins_already_subscribed" value="0" type="hidden">
						</form>					
					</div>
					
				</div>
			</div>
		</div>
		<script type="text/javascript">
		jQuery(function () {
			if (typeof(cb_gold_plugins_init_coupon_box) == 'function') {
				cb_gold_plugins_init_coupon_box();
			}
		});
		</script>
		<?php
	} 
	
	//this function displays the Theme tab contents
	function themes_page()
	{		
		//add upgrade button if free version
		//instantiate tabs object for output basic settings page tabs
		$tabs = new GP_Sajak( array(
			'header_label' => 'Theme Options',
			'settings_field_key' => 'custom-banners-theme-settings-group' // can be an array	
		) );
	
		$this->settings_page_top();
		$tabs->add_tab(
			'themes', // section id, used in url fragment
			'Theme Settings', // section label
			array($this, 'output_theme_settings'), // display callback
			array(
				'class' => 'extra_li_class', // extra classes to add sidebar menu <li> and tab wrapper <div>
				'icon' => 'paint-brush' // icons here: http://fontawesome.io/icons/
			)
		);
		
		$tabs->display();
	}
	
	//outputs the theme preview / selector interface
	function output_theme_settings()
	{
		wp_enqueue_style( 'custom-banners-admin' );	
		settings_fields( 'custom-banners-theme-settings-group' ); 
		
		?>				
		<h3>Custom Banners Themes</h3>			
		<p>Please select a theme to use with your Banners. This theme will become  your default choice, but you can always specify a different theme for each widget if you like!</p>
		
		<table class="form-table custom-banners-options-table">
			<?php
				$cb_config = new CustomBanners_Config();
			
				$current_theme = get_option('custom_banners_theme');
				$themes = $cb_config->all_themes();
				$desc = 'Select a theme to see how it would look with your Banners.';
				$this->shed->grouped_select( array('name' => 'custom_banners_theme', 'options' => $themes, 'label' =>'Banners Theme', 'value' => $current_theme, 'description' => $desc) );

			?>
		</table>
		
		<div id="custom_banners_theme_preview">			
			<div id="custom_banners_theme_preview_color_picker">
				<table class="form-table">
				<?php
					$cur_prev_bg = get_option('custom_banners_preview_window_background', '#ededed');
					$this->shed->color( array('name' => 'custom_banners_preview_window_background', 'label' =>'Select Your Website\'s Background Color:', 'value' => $cur_prev_bg, 'description' => '') );
				?>
				</table>
			</div>
			<div id="custom_banners_theme_preview_browser"></div>
			<div id="custom_banners_theme_preview_content">
				<?php echo $this->get_demo_slideshow_html(); ?>				
			</div>
		</div>
		<?php
	}
	
	function get_demo_slideshow_html()
	{
		$base_path = plugin_dir_path( __FILE__ );
		$template_path = $base_path . '../include/content/demo_slideshow.html';
		$plugin_title = 'Custom Banners';
		$content = file_exists($template_path)
				   ? file_get_contents($template_path)
				   : '';
		return $content;		
	}
	
	function text_input($name, $label, $description = '', $default_val = '', $disabled = false)
	{
		$val = get_option($name, $default_val);
		if (empty($val)) {
			$val = $default_val;
		}
		$this->shed->text(
			array(
				'name' => $name,
				'label' => $label,
				'value' => $val,
				'description' => $description,
				'disabled' => $disabled
			)
		);
	}
	
	function color_input($name, $label, $default_color = '#000000', $disabled = false)
	{		
		$val = get_option($name, $default_color);
		if (empty($val)) {
			$val = $default_color;
		}
		$this->shed->color(
			array(
				'name' => $name,
				'label' => $label,
				'default_color' => $default_color,
				'value' => $val,
				'disabled' => $disabled
			)
		);
	}
	
	function typography_input($name, $label, $description = '', $default_font_family = '', $default_font_size = '', $default_font_style = '', $default_font_color = '#000080', $disabled = false)
	{
		$options = array(
			'name' => $name,
			'label' => $label,
			'default_color' => $default_font_color,
			'description' => $description,
			'google_fonts' => true,
			'values' => array(),
			'disabled' => $disabled			
		);
		$fields = array(
			'font_size' => $default_font_size,
			'font_family' => $default_font_family,
			'font_color' => $default_font_color,
			'font_style' => $default_font_style
		);
		foreach ($fields as $key => $default_value)
		{
			list($field_name, $field_id) = $this->shed->get_name_and_id($options, $key);
			$val = get_option($field_name, $default_value);
			if (empty($val)) {
				$val = $default_value;
			}			
			$options['values'][$key] = $val;
		}
		$this->shed->typography($options);
	}
	
	function bust_options_cache()
	{
		delete_transient('_custom_bs_webfont_str');
	}
	
	function recheck_key()
	{
		$kc = new GoldPlugins_Key_Checker('custom-banners-pro');
		$license_email = get_option('custom_banners_registered_name');
		$license_key = get_option('custom_banners_registered_key');
		$key_status = $kc->get_key_status($license_email, $license_key);
		$option_key = '_custom_banners_pro_license_status';
		switch ( $key_status ) {
			
			case 'ACTIVE':			
			case 'EXPIRED':						
				update_option( $option_key, $key_status );
				break;
				
			case 'INVALID':
				delete_option( $option_key );
				break;
			
			// do nothing - couldn't reach the activation server 			
			case 'UNKNOWN': 
			default: 
				break;
		}
	}
	
	//loads all options
	//builds array of options matching our prefix
	//returns our array
	private function load_all_options(){
		$my_options = array();
		$all_options = wp_load_alloptions();
		
		$patterns = array(
			'custom_banners_(.*)',
		);
		
		foreach ( $all_options as $name => $value ) {
			if ( $this->preg_match_array( $name, $patterns ) ) {
				$my_options[ $name ] = $value;
			}
		}
		
		return $my_options;
	}
	
	function preg_match_array( $candidate, $patterns )
	{
		foreach ($patterns as $pattern) {
			$p = sprintf('#%s#i', $pattern);
			if ( preg_match($p, $candidate, $matches) == 1 ) {
				return true;
			}
		}
		return false;
	}
} // end class
?>