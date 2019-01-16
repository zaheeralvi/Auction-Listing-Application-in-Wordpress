<?php  
/**
 * Base class to handle Google Maps API functionality.
 * 
 * @since 4.3.2 extended by Günter
 */
if ( ! defined('AVIA_FW')) { exit('No direct script access allowed'); }


if( ! class_exists( 'av_google_maps' ) )
{
			
	class av_google_maps
	{
			//	maintain URL and version number here for all objects using this element
		const API_URL			=	'https://maps.googleapis.com/maps/api/js';
		const API_VERSION		=	'3.30';				
		const MAPS_SEARCH_URL	=	'https://www.google.com/maps/search/';
		
		const AJAX_NONCE		=	'av_google_maps_nonce';
		
		/**
		 * Holds the instance of this class
		 * 
		 * @since 4.3.2
		 * @var av_google_maps 
		 */
		static private $_instance = null;
		
		
		/**
		 * Google Maps API key
		 * 
		 * @var string 
		 */
		protected $key;
		
		
		/**
		 * Number of maps used on the page in frontend
		 * 
		 * @since 4.3.2
		 * @var int 
		 */
		protected $usage_count;
		
		/**
		 * Number of maps to be displayed immediatly on pageload
		 * 
		 * @since 4.3.2
		 * @var int
		 */
		protected $unconditionally_count;
		
		
		/**
		 * Save all map data to render to footer
		 * 
		 * @since 4.3.2
		 * @var array 
		 */
		protected $maps_array;

		/**
		 * true if loading of google script was canceled with filter 'avf_load_google_map_api_prohibited'
		 * 
		 * @since 4.3.2
		 * @var boolean 
		 */
		protected $loading_prohibited;

		/**
		 * Return the instance of this class
		 * 
		 * @since 4.3.2
		 * @return av_google_maps
		 */
		static public function instance()
		{
			if( is_null( av_google_maps::$_instance ) )
			{
				av_google_maps::$_instance = new av_google_maps();
			}
			
			return av_google_maps::$_instance;
		}		
		

		/**
		 * 
		 * @param string $key
		 */
		protected function __construct( $key = '' )
		{
			$this->key = '';
			$this->usage_count = 0;
			$this->unconditionally_count = 0;
			$this->maps_array = array();
			$this->loading_prohibited = false;
			
			
			add_action( 'init', array( $this, 'handler_wp_register_scripts' ), 20 );
			add_action( 'wp_enqueue_scripts', array( $this, 'handler_wp_enqueue_scripts' ), 500 );
			add_action( 'admin_enqueue_scripts', array( $this, 'handler_wp_admin_enqueue_scripts' ), 500 );
			
			add_action('wp_footer', array( $this, 'handler_wp_footer' ), 999999 );
			add_action('admin_footer', array( $this, 'handler_wp_admin_footer' ), 999999 );
		}
		
		/**
		 * @since 4.3.2
		 */
		public function __destruct() 
		{
			unset( $this->maps_array );
		}
		
		/**
		 * @since 4.3.2
		 */
		public function handler_wp_register_scripts()
		{
			$vn = avia_get_theme_version();
			
			$api_key = $this->get_key();
			$api_url = av_google_maps::api_url( $api_key );
			
			wp_register_script( 'avia-google-maps-api', $api_url, array( 'jquery' ), NULL, true );
			
			wp_register_script( 'avia_google_maps_front_script' , AVIA_JS_URL . 'conditional_load/avia_google_maps_front.js', array( 'jquery' ), $vn, true );
			wp_register_script( 'avia_google_maps_api_script' , AVIA_JS_URL . 'conditional_load/avia_google_maps_api.js', array( 'jquery' ), $vn, true );
			wp_register_script( 'avia_google_maps_widget_admin_script' , AVIA_JS_URL . 'conditional_load/avia_google_maps_widget_admin.js', array( 'jquery', 'avia_google_maps_api_script' ,'media-upload' ,'media-views' ), $vn, true );
		}
		
		
		/**
		 * Frontend we load conditionally. This script checks after pageload if loading of main scripts are necessary. 
		 * 
		 * @since 4.3.2
		 */
		public function handler_wp_enqueue_scripts()
		{		
			$this->loading_prohibited = apply_filters( 'avf_load_google_map_api_prohibited', false );
			if( $this->loading_prohibited )
			{
				return;
			}
			
			wp_enqueue_script( 'avia_google_maps_front_script' );
		}
		
		/**
		 * 
		 * @since 4.3.2
		 */
		public function handler_wp_admin_enqueue_scripts()
		{
			$this->loading_prohibited = apply_filters( 'avf_load_google_map_api_prohibited', false );
			if( $this->loading_prohibited )
			{
				return;
			}
			
			wp_enqueue_script( 'avia-google-maps-api' );
			wp_enqueue_script( 'avia_google_maps_api_script' );
			
			$is_widget_edit_page = in_array( basename( $_SERVER['PHP_SELF'] ), array( 'widgets.php' ) );
            if( $is_widget_edit_page )
            {
				wp_enqueue_script( 'avia_google_maps_widget_admin_script' );
				
				$args = array(
	                'toomanyrequests'	=> __( "Too many requests at once, please refresh the page to complete geocoding", 'avia_framework' ),
	                'latitude'			=> __( "Latitude and longitude for", 'avia_framework' ),
	                'notfound'			=> __( "couldn't be found by Google, please add them manually", 'avia_framework' ),
	                'insertaddress' 	=> __( "Please insert a valid address in the fields above", 'avia_framework' )
	            );

				wp_localize_script( 'avia-google-maps-api', 'AviaMapTranslation', $args );
			}
		}
		
		
		/**
		 * true if loading of google script was canceled with filter 'avf_load_google_map_api_prohibited'
		 * 
		 * @since 4.3.2
		 * @return boolean
		 */
		public function is_loading_prohibited()
		{
			return $this->loading_prohibited;
		}

		/**
		 * Increment internal counters
		 * 
		 * @since 4.3.2
		 * @param string $unconditionally			'unconditionally' | 'delayed'
		 */
		public function add_usage( $unconditionally = 'unconditionally' )
		{
			$this->usage_count ++;
			
			if( 'unconditionally' == $unconditionally )
			{
				$this->unconditionally_count ++;
			}
		}

		/**
		 * Returns the script source of GoogleMapsApi with the correct version 
		 * Use this function to allow easy maintanance of URL and version number
		 * User may filter the parameters - a fallback to the set values if user removes needed values by default settings
		 * 
		 * @param string $api_key			API key generated by Google
		 * @param string $callback			callback function when API is loaded
		 * @return string
		 */
		static public function api_url( $api_key = '', $callback = '' )
		{
			$args = array();
			$api_src = array(
							'source'	=>	av_google_maps::API_URL,
							'version'	=>	av_google_maps::API_VERSION
						);
			
			$api_src = apply_filters( 'avf_google_maps_source', $api_src );
			
			$api_url = ! empty( $api_src['source'] ) ? $api_src['source'] : av_google_maps::API_URL;
			$args['v'] = ! empty( $api_src['version'] ) ? $api_src['version'] : av_google_maps::API_VERSION;
			
			if( $api_key != '' )
			{
				$args['key'] = $api_key;
			}
			
			if( $callback != '' )
			{
				$args['callback'] = $callback;
			}
			
			if( ! empty( $args ) )
			{
				$api_url = add_query_arg( $args, $api_url );
			}
				
			return $api_url;
		}
		
		/**
		 * Returns the URL to Google Maps page with latitude/longitude coordinates
		 * 
		 * @since 4.3.2
		 * @param float|string $latitude
		 * @param float|string $longitude
		 * @return string
		 */
		static public function api_destination_url( $latitude, $longitude )
		{
			if( is_numeric( $latitude ) )
			{
				$adr = trim( $latitude ) . ',' . trim( $longitude );
			}
			else
			{
				$adr = trim( $latitude );
			}
			
			$latitude = ( is_numeric( $latitude ) ) ? $latitude : '0.0';
			$longitude = ( is_numeric( $longitude ) ) ? $longitude : '0.0';
			
			$url = esc_url( av_google_maps::MAPS_SEARCH_URL . '?api=1&query=' . $adr );
			
			return $url;
		}

		/**
		 * Backwards comp. only
		 * 
		 * @deprecated since version 4.3.2
		 */
		static public function gmap_js_globals()
		{
			_deprecated_function( 'gmap_js_globals', '4.3.2', 'handler_wp_footer');
			
			$api = Av_Google_Maps();
			$api->handler_wp_footer();
		}
		
		
		/**
		 * Output global data and maps in js - if necessary
		 * 
		 * @since 4.3.2
		 */
		public function handler_wp_footer()
		{
			if( count( $this->maps_array ) == 0 )
			{
				return;
			}
			
			$this->handler_wp_admin_footer();
			
			$maps = array(
				'av_google_map'	=> $this->maps_array
			);
			
			/**
			 * Filter Google Maps before output
			 * 
			 * @param array $output
			 * @return array
			 */
			$maps = apply_filters( 'avf_gmap_vars', $maps );
			
			AviaHelper::print_javascript( $maps );
		}
		
		/**
		 * Output global variables needed by elements to access google maps API
		 * 
		 * @since 4.3.2
		 */
		public function handler_wp_admin_footer()
		{
			$api_key = $this->get_key();
		
			$api_source = av_google_maps::api_url( $api_key );
			$api_builder = av_google_maps::api_url( $api_key, 'av_builder_maps_loaded' );
			$api_builder_backend = av_google_maps::api_url( '', 'av_backend_maps_loaded' );
			$api_maps_loaded = av_google_maps::api_url( $api_key, 'aviaOnGoogleMapsLoaded' );
			$avia_api = AVIA_JS_URL . 'conditional_load/avia_google_maps_api.js';
			
			//if there is a map, always load in frontend. otherwise we just got a js error and no feedback what might be wrong
			//also localhost often works without key
			if( $this->get_maps_count() || is_admin() ) 
			//if( ! empty( $api_key ) ) 
			{
				echo "
<script id='avia_gmaps_framework_globals' type='text/javascript'>
 /* <![CDATA[ */  
var avia_framework_globals = avia_framework_globals || {};
	avia_framework_globals.gmap_api = '" .$api_key . "';
	avia_framework_globals.gmap_maps_loaded = '".$api_maps_loaded."';
	avia_framework_globals.gmap_builder_maps_loaded = '" . $api_builder . "';
	avia_framework_globals.gmap_backend_maps_loaded = '" . $api_builder_backend . "';
	avia_framework_globals.gmap_source = '" . $api_source . "';
	avia_framework_globals.gmap_avia_api = '" . $avia_api . "';
/* ]]> */ 
</script>	
";
			}
		}
				
		
		/**
		 * 
		 * @return boolean
		 */
		protected function check_api_key()
		{
			$valid = false;
			//function that checks if the value of $this->key is a valid api key
		
		
			return $valid;
		}
		
		/**
		 * Returns the stored API key
		 * 
		 * @since 4.3.2
		 * @return string
		 */
		public function get_key()
		{
			if( empty( $this->key ) )
			{
				$this->key = get_option( 'av_gmaps_api_key', '' );
				
				// fallback. not sure why the db field for storing has changed. broke all demo maps. 
				// need to inquire about that since a user exporting his theme settings will not export a default get_option field
				if(empty($this->key))
				{
					$this->key = avia_get_option( 'gmap_api', '' );
				}
			}
			
			return $this->key;
		}

		/**
		 * Saves the new API key
		 * 
		 * @param string $key
		 */
		protected function store_key( $key )
		{
			$this->key = trim( $key );
			update_option( 'av_gmaps_api_key', $this->key );
			
			//fallback. keep the old db field up to date as well
			avia_update_option( 'gmap_api', $this->key );
		}
		
		/**
		 * Delete the API key
		 */
		protected function delete_key()
		{
			delete_option( 'av_gmaps_api_key' );
			
			//fallback. keep the old db field up to date as well
			avia_update_option( 'gmap_api' );
		}
		
		
		/**
		 * Adds map data to the maps array for output and returns a unique id
		 * 
		 * @since 4.3.2
		 * @param array $data
		 * @param string $add					'unconditionally' | 'delayed'
		 * @param string $id
		 * @return string
		 */
		public function add_map( array $data, $add, $id = '' )
		{
			$id = 'av_gmap_' . count( $this->maps_array );
			
			$this->maps_array[ $id ] = $data;
			$this->add_usage( $add );
			
			return $id;
		}
		
		
		/**
		 * Returns number of currently stored maps on a page
		 * 
		 * @since 4.3.2
		 * @return int
		 */
		public function get_maps_count()
		{
			return count( $this->maps_array );
		}

		/**
		 * Output options page backend HTML or the key verification message
		 * 
		 * Google API 3.30 loads and allows geocoder to return valid results if key = ''.
		 * Therefore we have to check in backend for this.
		 * 
		 * @param string $api_key
		 * @param boolean $ajax
		 * @param string|boolean $valid_key
		 * @return string
		 */
		public function backend_html( $api_key = "", $ajax = true, $valid_key = false )
		{
			
			$api_key = trim( $api_key );
			$valid_key  = $valid_key == "true" && ! empty( $api_key ) ? true : false;
			
			$response_text  = __( "Could not connect to Google Maps with this API Key.", 'avia_framework' );
			$response_class = "av-notice-error";
			
			$content_default  =			'<h4>' . esc_html__( 'Troubleshooting:', 'avia_framework' ) . '</h4>';
			$content_default .=			'<ol>';
			$content_default .=				'<li>';
			$content_default .=					esc_html__( 'Check if you typed the key correctly.', 'avia_framework' );
			$content_default .=				'</li>';
			$content_default .=				'<li>';
			$content_default .=					esc_html__( 'If you use the restriction setting on Google try to remove that, wait a few minutes for google to apply your changes and then check again if the key works here. If it does, you probably have a syntax error in your referrer url', 'avia_framework' );
			$content_default .=				'</li>';
			$content_default .=				'<li>';
			$content_default .=					esc_html__( 'If none of this helps: deactivate all plugins and then check if the API works by using the button above. If thats the case then one of your plugins is interfering. ', 'avia_framework' );
			$content_default .=				'</li>';
			$content_default .=			'</ol>';
			
			
		
			//if called by user pressing the ajax check button
			if( $ajax )
			{	
				if( $valid_key )
				{	
					$this->store_key( $api_key );
					
					$response_class = "";
					$response_text  = __("We were able to properly connect to google maps with your API key",'avia_framework');
					
					
					//will be stripped from the final output but tells the ajax script to save the page after the check was performed
					$response_text .= " avia_trigger_save"; 				
				}
				else
				{
					$this->delete_key();
				}
			}
			else // is called on a normal page load. in this case we either show the stored result or if we got no stored result we show nothing
			{
				$valid_key = $this->get_key();
				
				if( $valid_key )
				{
					$response_class = "";
					$response_text  = __("Last time we checked we were able to connected to google maps with your API key",'avia_framework');
				}
			}
			
			if( $valid_key )
			{
				$content_default  = __("If you ever change your API key or the URL restrictions of the key please verify the key here again, to test if it works properly",'avia_framework');
			}
			
			$output  = "<div class='av-verification-response-wrapper'>";
			$output .= "<div class='av-text-notice {$response_class}'>";
			$output .= $response_text;
			$output .= "</div>";
			$output .= "<div class='av-verification-cell'>".$content_default."</div>";
			$output .= "</div>";
			
			return $output;
		}
		
	}
	
	
	/**
	 * Returns the main instance of av_google_maps to prevent the need to use globals
	 * 
	 * @since 4.3.2
	 * @return av_google_maps
	 */
	function Av_Google_Maps() 
	{
		return av_google_maps::instance();
	}
	
}

Av_Google_Maps();

if( ! function_exists( 'av_maps_api_check' ) )
{
	/**
	 * Callback function:
	 *		- ajax callback from verification butto
	 *		- php callback when creating output on option page
	 * 
	 * @param string $value
	 * @param boolean $ajax
	 * @param string|null $js_value
	 * @return string
	 */
	function av_maps_api_check( $value, $ajax = true, $js_value = NULL )
	{
		$api = Av_Google_Maps();
		return $api->backend_html( $value, $ajax, $js_value );
	}

}