<?php
if ( !class_exists('GP_Aloha') ):

	class GP_Aloha
	{
		var $options = array();
		var $meta_key = '_gp_aloha_show_welcome_on_next_page_load';
		
		/*
		 * Creates a new Aloha object
		 * 
		 */
		function __construct( $options )
		{
			$this->options = $this->merge_with_defaults( $options );			
			$this->set_meta_key();
			$this->init();
		}
		
		/*
		 * Runs any startup actions. This is the main function of the library.
		 * 
		 */
		function init()
		{
			// disable on network admin and bulk activates
			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
				return;
			}
		
			$this->add_hooks();			
		}
		
		/*
		 * Sets the meta key member variable's value, which should include
		 * the top_level_menu key, in order to avoid conflicts between various
		 * Gold Plugins.
		 * 
		 */
		function set_meta_key()
		{
			$this->meta_key = sprintf( '_gp_aloha_%s_show_welcome', 
									   $this->options['top_level_menu'] );
		}

		/*
		 * Add hooks and actions
		 * 
		 */
		function add_hooks()
		{
			add_action( 'admin_enqueue_scripts', array($this, 'setup_scripts') );
			add_action( 'admin_menu', array($this, 'add_menu_pages') );
			add_action( 'admin_init', array($this, 'maybe_send_user_to_welcome_screen') );
		}

		/*
		 * Adds scripts and stylesheets
		 * 
		 */
		function setup_scripts()
		{
			
			$css_url = plugins_url( 'assets/css/gp_aloha.css' , __FILE__ );
			wp_register_style('gp-aloha', $css_url);
			wp_enqueue_style('gp-aloha');			
		}
		
		/*
		 * Checks for the 'should send user to welcome screen' flag, and if set
		 * redirects the user to the welcome screen and turns the flag off.
		 * 
		 */
		function maybe_send_user_to_welcome_screen()
		{
			if ( $this->user_should_see_welcome() ) {
				$this->set_user_should_see_welcome( false );
				$url = menu_page_url( $this->options['top_level_menu'] . '_aloha', false );
				wp_safe_redirect( $url );
			}
		}
		
		/*
		 * Determines whether the user has already seen the welcome screen
		 *
		 * @return bool true if the user has already seen the welcome screen, 
		 *  			false if not
		 */
		function user_should_see_welcome()
		{
			$user_id = get_current_user_id();
			if ( empty($user_id) ) {
				return false;
			}
			$val =  get_user_meta( $user_id, $this->meta_key, true);
			$filter_key = $this->get_filter_key('gp_aloha_user_should_see_welcome');
			$val = apply_filters($filter_key, $val, $user_id);
			return !empty($val);
		}

		/*
		 * Sets a flag for whether the user should see the welcome screen on
		 * next page load
		 *
		 * @param bool whether or not WP's update action succeeded.
		 */
		function set_user_should_see_welcome( $new_value )
		{
			$user_id = get_current_user_id();
			if ( empty($user_id) ) {
				return false;
			}
			$new_val = !empty($new_value) ? '1': '';					  
			return update_user_meta( $user_id, $this->meta_key, $new_val );
		}

		/*
		 * Reset welcome screen flag, so that the user sees the welcome screen
		 * again on the next page load.
		 */
		function reset_welcome_screen()
		{
			$this->set_user_should_see_welcome( true );
		}

		/*
		 * Adds menus and submenus
		 * 
		 */
		function add_menu_pages()
		{
			add_submenu_page(
				$this->options['top_level_menu'],
				$this->options['page_title'],
				$this->options['menu_label'],
				'administrator',
				$this->options['top_level_menu'] . '_aloha',
				array($this, 'output_welcome_page')
			);

		}
				
		/*
		 * Merges provided options with the defaults. 
		 * 
		 * @param array $options The specified options to override the defaults.
		 *						 Requires 'top_level_menu', all other keys are 
		 * 						 optional.
		 * 
		 * @throws InvalidArgumentException if 'content and 'css_selector' keys
		 * 		   are not present in the $options array
		 * 
		 * @return array An array of valid options.
		 * 
		 */
		function merge_with_defaults($options)
		{
			if ( !is_array($options) 
				 || empty($options['top_level_menu']) ) {
				throw new InvalidArgumentException("First parameter must be an array that includes a non-empty 'top_level_menu' key.");
			}
			
			$default_options = array(
				'menu_label' => __('Welcome'),
				'page_title' => __('Welcome'),
				'tagline' => '',
				'top_level_menu' => '',
				'welcome_page_content' => '',
				'wrapper_class' => '',
			);
			
			return array_merge($default_options, $options);
		}
		
		/*
		 * Returns the filter 
		 * the top_level_menu key, in order to avoid conflicts between various
		 * Gold Plugins.
		 * 
		 */
		function get_filter_key( $base )
		{
			$base = rtrim($base, '_');
			return sprintf( $base . '_%s', $this->options['top_level_menu'] );
		}
		
		/*
		 * Outputs the welcome screen content. Run as a callback from the 
		 * add_menu_page function.
		 * 
		 * The page content is loaded from the 'gp_aloha_welcome_page_content'
		 * hook - attach to this hook with add_filter and return the welcome
		 * page content.
		 * 
		 */
		function output_welcome_page()
		{
			// load page content from 'gp_aloha_welcome_page_content' hook
			$filter_key = $this->get_filter_key('gp_aloha_welcome_page_content');
			$content = apply_filters( $filter_key, '' );			
			
			printf( '<div class="gp_aloha %s">', $this->options['wrapper_class'] );
			printf( '<h1 class="aloha_header">%s</h1>', $this->options['page_title'] );
			if ( !empty($this->options['tagline']) ) {
				printf( '<p class="aloha_tagline">%s</p>', $this->options['tagline'] );
			}
			printf( '<div class="aloha_body">%s</div>', $content );
			print( '</div>' );
		}
	}
	
endif;//class_exists