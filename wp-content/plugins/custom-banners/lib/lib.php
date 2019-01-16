<?php
include("cbkg.php");

function isValidCBKey()
{	
	$option_key = '_custom_banners_pro_license_status';
	$opt_val = get_option($option_key);
	if ($opt_val == 'ACTIVE') {
		return true;
	}
	
	if ( !isset($_GLOBALS['is_valid_cb_result']) )
	{		
		$email = get_option('custom_banners_registered_name');
		$webaddress = get_option('custom_banners_registered_url');
		$key = get_option('custom_banners_registered_key');
		
		$keygen = new CBKG();
		$computedKey = $keygen->computeKey($webaddress, $email);
		$computedKeyEJ = $keygen->computeKeyEJ($email);

		if ($key == $computedKey || $key == $computedKeyEJ) {
			$_GLOBALS['is_valid_cb_result'] = true;
			return true;
		} else {
			$plugin = "custom-banners-pro/custom-banners-pro.php";
			
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			
			if( is_plugin_active($plugin) ) {
				$_GLOBALS['is_valid_cb_result'] = true;
				return true;
			}
			else {
				$_GLOBALS['is_valid_cb_result'] = false;
				return false;
			}
		}		
	} else {
		return $_GLOBALS['is_valid_cb_result'];		
	}
}

function isValidMSCBKey(){
	$plugin = "custom-banners-pro/custom-banners-pro.php";
		
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
	if( is_plugin_active($plugin) && class_exists('customBannersPro') ) {
		return true;
	}
	else {
		return false;
	}
}
?>