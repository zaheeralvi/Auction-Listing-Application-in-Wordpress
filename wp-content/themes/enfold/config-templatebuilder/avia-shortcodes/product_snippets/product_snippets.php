<?php
/**
 * WooCommerce Product Snippets are called from this file.
 * They are grouped within this folder for better theme structure
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

include_once( 'product_snippet_button.php' );
include_once( 'product_snippet_info.php' );
include_once( 'product_snippet_review.php' );
include_once( 'product_snippet_tabs.php' );
include_once( 'product_snippet_upsells.php' );
include_once( 'product_snippet_meta.php' );
include_once( 'product_snippet_price.php' );
