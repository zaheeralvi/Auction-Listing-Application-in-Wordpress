<?php
/**
 * Base class that handles upload and storage of Fonts (icon-fonts and type-fonts) in backend
 * and provides functions for frontend
 *
 * @author		Günter
 * @since		4.3
 */

if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( class_exists( 'aviaFontManagementBase' ) )
{
	return;
}


abstract class aviaFontManagementBase 
{

	/**
	 *
	 * @since 4.3
	 * @var string 
	 */
	protected $font_option_key;
	
	/**
	 * 
	 * @since 4.3
	 * @var array 
	 */
	protected $upload_paths;
	
	
	/**
	 *
	 * @since 4.3
	 * @var array 
	 */
	protected $default_paths;
	
	/**
	 * All allowed file extensions for font files 
	 * 
	 * @since 4.3
	 * @var array 
	 */
	protected $file_ext;
	
	/**
	 * Files that must be included - like definition files for type fonts
	 * 
	 * @since 4.3
	 * @var array 
	 */
	protected $include_files;

	/**
	 * Holds the font name if user wants to change it via filename 
	 * (can only be used when a single font can be uploaded, not for multiple fonts)
	 * 
	 * @since 4.3
	 * @var string 
	 */
	protected $font_name;


	/**
	 * A zip filename prefix that will be removed to allow to define a custom font name
	 * 
	 * @since 4.3
	 * @var string 
	 */
	protected $custom_font_prefix;
	
	
	/**
	 *
	 * @since 4.3
	 * @var string 
	 */
	protected $ajax_error_prefix;

	/**
	 *
	 * @since 4.3
	 * @var array 
	 */
	protected $response;
	
	
	/**
	 * Set to true when a zip file can contain multiple fonts.
	 * In that case an own subfolder is created in the temp upload folder
	 * 
	 * @since 4.3
	 * @var boolean 
	 */
	protected $multiple_fonts;
	
	
	/**
	 * Holds a list of all subfolders in upload temp directory
	 * 
	 * @since 4.3
	 * @var array 
	 */
	protected $subfolders;
	
	
	/**
	 * Array of fonts that could not be uploaded
	 * 
	 * @since 4.3
	 * @var array 
	 */
	protected $not_uploaded_files;


	/**
	 * 
	 * @since 4.3
	 * @param string $upload
	 * @param string $default
	 */
	public function __construct( array $upload, array $default = array() ) 
	{
		if( is_ssl() )
		{
			$upload['baseurl'] = str_replace( 'http://', 'https://', $upload['baseurl'] );
			
			if( isset( $default['baseurl'] ) )
			{
				$default['baseurl'] = str_replace( 'http://', 'https://', $default['baseurl'] );
			}
		}
		
		
		$upload['temp']  	= trailingslashit( $upload['fonts'] ) . 'avia_temp';
		$upload['fontdir']	= trailingslashit( $upload['basedir'] ) . $upload['fonts'];
		$upload['tempdir']	= trailingslashit( $upload['basedir'] ) . $upload['temp'];
		$upload['fonturl']	= trailingslashit( $upload['baseurl'] ) . $upload['fonts'];
		$upload['tempurl']	= trailingslashit( $upload['baseurl'] ) . trailingslashit( $upload['temp'] );
		
		if( isset( $default['baseurl'] ) )
		{
			$default['temp']  	= trailingslashit( $default['fonts'] ) . 'avia_temp';
			$default['fontdir']	= trailingslashit( $default['basedir'] ) . $default['fonts'];
			$default['tempdir']	= trailingslashit( $default['basedir'] ) . $default['temp'];
			$default['fonturl']	= trailingslashit( $default['baseurl'] ) . $default['fonts'];
			$default['tempurl']	= trailingslashit( $default['baseurl'] ) . trailingslashit( $default['temp'] );
		}
		
		$this->font_option_key = 'avia_font_management';	
		$this->upload_paths = $upload;
		$this->default_paths = $default;
		$this->file_ext = array();
		$this->include_files = array();
		$this->font_name = 'unknown';
		$this->custom_font_prefix = '';
		$this->ajax_error_prefix = __( 'Couldn\'t add/remove the font.<br/>The script returned the following error: <br/><br/>', 'avia_framework' );
		$this->response = array();
		$this->multiple_fonts = false;
		$this->subfolders = array();
		$this->not_uploaded_files = array();
		
		$this->init();
		
		add_filter( 'avf_file_upload_extra', array( $this, 'handler_add_font_manager_upload'), 10, 2 );
	}
	
	
	/**
	 * 
	 * @since 4.3
	 */
	public function __destruct() 
	{
		unset( $this->upload_paths );
		unset( $this->default_paths );
		unset( $this->file_ext );
		unset( $this->include_files );
		unset( $this->response );
		unset( $this->subfolders );
		unset( $this->not_uploaded_files );
	}
	
	/*******************************************************************************************************************************************
	 ************		 ABSTRACT FUNCTIONS DECLARATIONS
	 *******************************************************************************************************************************************/
	
	/**
	 * Initialise base class and derived class members
	 * 
	 * @since 4.3
	 */
	abstract protected function init();
	
	
	/**
	 * Called when the font had been extracted to the temp upload directory. Only necessary files have been copied.
	 * 
	 * @since 4.3
	 * @param boolean $config_only
	 */
	abstract protected function integrate_uploaded_fonts( $config_only = false );
	
	
	/**
	 * Remove the font from font list and from server - remove the directory
	 * 
	 * @since 4.3
	 * @param string $font_id
	 * @return boolean
	 */
	abstract protected function remove_font( $font_id );
	
	
	/**
	 * Add additional info below standard upload button
	 * 
	 * @since 4.3
	 * @param string $output
	 * @param array $element
	 */
	abstract public function handler_add_font_manager_upload( $output, array $element );
	
	
	/**
	 * Returns the saved font list from database
	 * 
	 * @since 4.3
	 * @param mixed $default
	 * @return mixed
	 */
	public function get_font_list( $default = array() )
	{
		return get_option( $this->font_option_key, $default );
	}

	
	/**
	 * Updates the font list in database
	 * 
	 * @since 4.3
	 * @param mixed $data 
	 */
	public function update_font_list( $data )
	{
		update_option( $this->font_option_key, $data );
	}

	
	/**
	 * Add content of an uploaded zip font file to the temp upload folder 
	 * and prepare for integrating into options
	 * 
	 * @since 4.3
	 */
	public function handler_add_zipped_fonts()
	{
		/**
		 * check if referer is ok
		 */
		check_ajax_referer( 'avia_nonce_save_backend' );
		
		header( "Content-Type: application/json" );
		
		$this->response = array();
		$this->response['success'] = false;
		$this->response['message'] = '';
		$this->response['error'] = $this->ajax_error_prefix;
		
		/**
		 * check if capability is ok
		 */
		$cap = apply_filters( 'avf_file_upload_capability', 'update_plugins', get_class() );
		if( ! current_user_can( $cap ) ) 
		{
			$this->response['error'] .= __( 'Using this feature is reserved for Super Admins. You unfortunately don\'t have the necessary permissions.', 'avia_framework' );
			echo json_encode( $this->response );
			exit;
		}
		
		/**
		 * get the file path of the zip file and extract needed files to temp folder
		 */
		$attachment = $_POST['values'];
		$path 		= realpath( get_attached_file( $attachment['id'] ) );
		$unzipped 	= $this->extract_zip_file( $path );
		
	
		/**
		 * if we were able to unzip the file and save it to our temp folder integrate it in database
		 * Add result to $this->response for ajax callback
		 */
		if( $unzipped )
		{
			$this->integrate_uploaded_fonts();		//	$this->create_config() in original source class
		}
		
		/**
		 * Remove temp folder in any case
		 */
		avia_backend_delete_folder( $this->upload_paths['tempdir'] );
		
		/**
		 * if we got an error delete temp folder and report to user
		 */
		if( count( $this->not_uploaded_files ) > 0 )
		{
			$this->response['error'] .= __( 'Following fonts could not be uploaded: ', 'avia_framework' ) . "<br />" . implode( "<br />", $this->not_uploaded_files );
			echo json_encode( $this->response );
			exit;
		}
		
		$this->response ['success'] = true;
		echo json_encode( $this->response );
		exit;
	}
	
	
	/**
	 * Remove an uploaded font 
	 * 
	 * @since 4.3
	 */
	public function handler_remove_zipped_font()
	{
		/**
		 * check if referer is ok
		 */
		check_ajax_referer( 'avia_nonce_save_backend' );
		
		header( "Content-Type: application/json" );
		
		$this->response = array();
		$this->response['success'] = false;
		$this->response['message'] = '';
		$this->response['error'] = $this->ajax_error_prefix;
		
		/**
		 * check if capability is ok
		 */
		$cap = apply_filters( 'avf_file_upload_capability', 'update_plugins', get_class() );
		if( ! current_user_can( $cap ) ) 
		{
			$this->response['error'] .= __( 'Using this feature is reserved for Super Admins. You unfortunately don\'t have the necessary permissions.', 'avia_framework' );
			echo json_encode( $this->response );
			exit;
		}
		
		$font_id = $_POST['del_font'];
		
		
		if( ! $this->remove_font( $font_id ) )
		{
			echo json_encode( $this->response );
			exit;
		}
		
		$this->response ['success'] = true;
		echo json_encode( $this->response );
		exit;
	}

	

	/**
	 * Supports 2 szenarios:
	 * 
	 * $this->multiple_fonts = false (intended for fontello icon fonts - not implemented yet - see AviaBuilder)
	 * 
	 *		Extract the zip file to a flat folder and remove the files that are not needed
	 *		and copies the required files to a temp directory in destination upload folder
	 * 
	 * $this->multiple_fonts = true  (intended for Google type fonts downloaded from https://fonts.google.com )
	 * 
	 *		- contains subdirectories: each subdirectory is an own font with possible subdirectories, any files in main folder are skipped
	 *		- no subdirectories: a single font, all files in main directory are checked
	 * 
	 * @param string $zipfile
	 * @return boolean
	 */
	protected function extract_zip_file ( $zipfile ) 
	{ 	
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );
		
		/**
		 * If a temp dir already exists remove it and create a new one
		 */
		if( is_dir( $this->upload_paths['tempdir'] ) ) 
		{
			avia_backend_delete_folder( $this->upload_paths['tempdir'] );
		}
		
		$tempdir = avia_backend_create_folder( $this->upload_paths['tempdir'], false );
		if( ! $tempdir ) 
		{
			$this->response['error'] .= __('Wasn\'t able to create temp folder', 'avia_framework' );
			echo json_encode( $this->response );
			exit;
		}
		
	    $zip = new ZipArchive(); 
	    if ( ! $zip->open( $zipfile ) ) 
		{
	    	$this->response['error'] .= __( 'Wasn\'t able to work with Zip Archive', 'avia_framework' );
			echo json_encode( $this->response );
			exit;
	    }	
		
		
		$zip_paths = pathinfo( $zipfile );

		/**
		 * check name scheme if user wants to rename the files and font name (intended only for icon fonts)
		 */
		if( ! $this->multiple_fonts && ( $this->custom_font_prefix != '' ) )
		{
			if( isset( $zip_paths['filename'] ) && strpos( $zip_paths['filename'], $this->custom_font_prefix ) === 0 )
			{
				$this->font_name = str_replace( $this->custom_font_prefix, '', $zip_paths['filename'] );
			}
		}
		
		$filter = array_map( function( $value ) { return '\.' . $value; }, $this->file_ext );
		
		
		/**
		 * Check zip file structure and create folder name array
		 *			'folder_name'	=>  array ( $index )
		 */
		$this->subfolders = array();
		
		if( $this->multiple_fonts )
		{
			for( $i = 0; $i < $zip->numFiles; $i++ )
			{
				$entry_original = $zip->getNameIndex( $i );
				$entry_check = str_replace( '\\', '/', $entry_original );
				
				/**
				 * Skip only folders
				 */
				if ( substr( $entry_check, -1 ) == '/' )
				{
					continue;
				}
				
				/**
				 * skip a file in main folder
				 */
				$pos = strpos( $entry_check, '/' );
				if( false === $pos )
				{
					continue;
				}
				
				$folder = substr( $entry_original, 0, $pos );
				if( empty( $folder ) )
				{
					continue;
				}
			
				if( ! isset( $this->subfolders[ $folder ] ) )
				{
					$this->subfolders[ $folder ] = array( $i );
				}
				else
				{
					$this->subfolders[ $folder ][] = $i;
				}
			}
		}
		
		
		if( $this->multiple_fonts && ( ! empty( $this->subfolders ) ) )
		{
			foreach( $this->subfolders as $folder => $index ) 
			{
				if( empty( $index ) )
				{
					unset( $this->subfolders[ $folder ] );
					continue;
				}
				
				$path = trailingslashit( trailingslashit( $this->upload_paths['tempdir'] ) . strtolower( $folder ) );
				$tempdir = avia_backend_create_folder( $path, false );
				if( ! $tempdir ) 
				{
					$zip->close(); 
					$this->response['error'] .= __('Wasn\'t able to create temp folder', 'avia_framework' );
					echo json_encode( $this->response );
					exit;
				}
			}
		}
		
		/**
		 * When we have no subfolders we take all entries
		 */
		if( empty( $this->subfolders ) )
		{
			$this->subfolders['*'] = array();
			for( $i = 0; $i < $zip->numFiles; $i++ )
			{
				$this->subfolders['*'][] = $i;
			}
		}
		
		/**
		 * Set the default
		 */
		if( isset( $this->subfolders['*'] ) )
		{
			if( 'unknown' == $this->font_name )
			{
				$this->font_name = str_replace( array( '-', '_' ), ' ', $zip_paths['filename'] );
			}
		}
		
		/**
		 * Now scan all entries and copy content to main temp folder or to subfolders
		 */
		foreach( $this->subfolders as $folder => $index ) 
		{
			$dir = trailingslashit( $this->upload_paths['tempdir'] );
			$destination = ( '*' == $folder ) ? $dir : trailingslashit( $dir . strtolower( $folder ) );
			
			foreach ( $index as $i ) 
			{ 
				$entry_original = $zip->getNameIndex( $i ); 
				$entry = strtolower( $entry_original );
				$entry_check = str_replace( '\\', '/', $entry );
				
				/**
				 * Skip folders
				 */
				if ( substr( $entry_check, -1 ) == '/' )
				{
					continue;
				}

				$skip = true;
			
				/**
				 * Check for special files to include
				 */
				if( ! empty( $this->include_files ) )
				{
					foreach ( $this->include_files as $check ) 
					{
						$found = strripos( $entry, $check );
						if( false === $found )
						{
							continue;
						}

						if( $found == ( strlen( $entry ) - strlen( $check ) ) )
						{
							$skip = false;
							break;
						}
					}
				}

				/**
				 * Check filter to include file
				 */
				if( ! empty( $filter ) && $skip )
				{
					$matches = array();

					foreach( $filter as $regex )
					{
						/**
						 * Checks that filter is at end of filename
						 */
						preg_match( '!' . $regex . '$!', $entry , $matches );

						if( ! empty( $matches ) )
						{
							$skip = false;
							break;
						}
					}
				}

				/**
				 * skip non matching files
				 */
				if( $skip ) 
				{
					continue;
				}
				
				$this->copy_file_content( $zip, $entry_original, $destination . basename( $entry ) );
			}
		} 

		$zip->close(); 
	    return true; 
	}
	
	
	/**
	 * Copy file content from zip file to a destination file.
	 * Exits in case of an error.
	 * 
	 * @since 4.3
	 * @param ZipArchive $zip
	 * @param string $zip_file
	 * @param string $dest_file
	 */
	protected function copy_file_content( ZipArchive $zip, $zip_file, $dest_file )
	{
		
		$fp = $zip->getStream( $zip_file ); 
		if( ! $fp ) 
		{
			$zip->close();
			$this->response['error'] .= __( 'Unable to extract the file.', 'avia_framework' );
			echo json_encode( $this->response );
			exit;
		}

		$ofp = fopen( $dest_file, 'w' ); 
		if( false === $ofp )
		{
			fclose( $fp );
			$zip->close(); 
			$this->response['error'] .= __( 'Unable to create the file.', 'avia_framework' );
			echo json_encode( $this->response );
			exit;
		}

		while( ! feof( $fp ) )
		{
			$content = fread( $fp, 8192 );
			if( false !== $content )
			{
				if( false !== fwrite( $ofp, $content ) ) 
				{
					continue;
				}
			}
			fclose( $fp );
			fclose( $ofp );
			$zip->close(); 
			$this->response['error'] .= __( 'Unable to copy the file content.', 'avia_framework' );
			echo json_encode( $this->response );
			exit;
		}

		fclose( $fp ); 
		fclose( $ofp ); 		
	}
	
	/**
	 * Rename the temp upload folder. Remove an existing one before
	 * 
	 * @since 4.3
	 * @param string $old_folder_name
	 * @param string $new_folder_name
	 * @return boolean
	 */
	protected function rename_temp_folder( $old_folder_name, $new_folder_name )
	{
		$new_folder = trailingslashit( $this->upload_paths['fontdir'] ) . $new_folder_name;
		
		avia_backend_delete_folder( $new_folder );
		
		return rename( $old_folder_name, $new_folder );
	}
	

}
