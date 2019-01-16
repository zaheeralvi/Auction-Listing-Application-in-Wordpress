<?php

if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( class_exists( 'GFForms' ) )
{
	add_action( 'wp_enqueue_scripts', 'avia_add_gravity_scripts', 500 );
}


function avia_add_gravity_scripts()
{
	$theme = wp_get_theme();
	if( false !== $theme->parent() )
	{
		$theme = $theme->parent();
	}
	$vn = $theme->get( 'Version' );
	
	wp_register_style( 'avia-gravity', get_template_directory_uri() . "/config-gravityforms/gravity-mod.css", array(), $vn, 'screen' );
	wp_enqueue_style( 'avia-gravity');
}

/**
 * was removed with version 4.2.3 - this file is always included and filter always returns 'avia_ajax_form'. 
 * Moved this value to framework\php\class-form-generator.php because this filter is not used elsewhere in Enfold
 */
//add_filter( 'avf_ajax_form_class', 'avia_change_ajax_form_class', 10, 3 );
//function avia_change_ajax_form_class( $class, $formID, $form_params )
//{
//	return 'avia_ajax_form';
//}


/*add the gravityforms button to the ajax popup editor*/
add_filter( 'gform_display_add_form_button', 'avia_add_gf_button_to_editor', 10, 1 );
function avia_add_gf_button_to_editor( $is_post_edit_page )
{
	if( ! empty( $_POST['ajax_fetch'] ) )
	{
		$is_post_edit_page = true;
	}
	
    return $is_post_edit_page;
}
