<?php
/**
 * ==============================================================
 * !!!!!!!!!!!!!!!!!!    THIS IS A FRAME ONLY     !!!!!!!!!!!!!!!
 * ==============================================================
 * 
 * Icon Fonts handling class.
 * 
 * Currently not used - might become active in future.
 * See config-templatebuilder\avia-template-builder\php\font-manager.class.php for current implementation
 * 
 * 
 * Currntly only support for icon fonts that follow the standards of http://fontello.com/
 * 
 * @author		Günter
 * @since		4.3
 */

if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( class_exists( 'AviaIconFonts' ) )
{
	return;
}

class AviaIconFonts extends aviaFontManagementBase
{

	/**
	 * 
	 * @since 4.3
	 */
	public function __construct() 
	{
		$upload = wp_upload_dir();
		
		$upload['fonts']	= 'avia_fonts';
		$upload['config']	= 'charmap.php';
		
		
		$default = array(
					'path'		=> AVIA_BASE,
					'subdir'	=> '',
					'url'		=> AVIA_BASE_URL,
					'basedir'	=> AVIA_BASE,
					'baseurl'	=> AVIA_BASE_URL
				);
		
		
		$default['fonts']	= 'avia_fonts';
		$default['config']	= 'charmap.php';
		
		parent::__construct( $upload, $default );
		
		/**
		 * font file extract by ajax function
		 */
		add_action( 'wp_ajax_avia_ajax_add_zipped_icon_font', array( $this, 'add_zipped_font' ) );
		add_action( 'wp_ajax_avia_ajax_remove_zipped_icon_font', array( $this, 'remove_zipped_font' ) );
	}
	
	/**
	 * @since 4.3
	 */
	public function __destruct() 
	{
		parent::__destruct();
	}
	
	/**
	 * Initialise base class and derived class members
	 * 
	 * @since 4.3
	 */
	protected function init() 
	{
		$ext = array( 'ttf', 'eot', 'woff', 'woff2', 'svg', 'json' );
		
		$this->file_ext = array_merge( $this->file_ext, $ext );
		
		$this->custom_font_prefix = 'iconset.';
	}
	
	
	/**
	 * Called when the font had been extracted to the temp upload directory. Only necessary files have been copied.
	 * 
	 * @since 4.3
	 * @param boolean $config_only
	 */
	protected function integrate_uploaded_font( $config_only = false )
	{
		//	In original class this is the function create_config()
	}
}
