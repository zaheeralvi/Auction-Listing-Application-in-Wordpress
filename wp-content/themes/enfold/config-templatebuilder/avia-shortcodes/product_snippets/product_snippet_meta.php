<?php
/**
 * Product Meta
 * 
 * Display the product meta for the current product
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( !class_exists( 'woocommerce' ) )
{
	add_shortcode('avia_sc_product_meta', 'avia_please_install_woo');
	return;
}

if ( !class_exists( 'avia_sc_product_meta' ) )
{
	class avia_sc_product_meta extends aviaShortcodeTemplate
	{
		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['self_closing']	=	'yes';
			
			$this->config['name']		= __('Product Meta Info', 'avia_framework' );
			$this->config['tab']		= __('Plugin Additions', 'avia_framework' );
			$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-contentslider.png";
			$this->config['order']		= 9;
			$this->config['target']		= 'avia-target-insert';
			$this->config['shortcode'] 	= 'av_product_meta';
			$this->config['tooltip'] 	= __('Display the product meta for the current product', 'avia_framework' );
			$this->config['drag-level'] = 3;
			$this->config['tinyMCE'] 	= array('disable' => "true");
			$this->config['posttype'] 	= array('product',__('This element can only be used on single product pages','avia_framework'));
		}


		/**
		 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
		 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
		 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
		 *
		 *
		 * @param array $params this array holds the default values for $content and $args.
		 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
		 */
		function editor_element($params)
		{
			$params['innerHtml'] = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
			$params['innerHtml'].= "<div class='avia-element-label'>".$this->config['name']."</div>";
			
			$params['innerHtml'].= "<div class='avia-flex-element'>"; 
			$params['innerHtml'].= 		__( 'Display the product meta info such as categories, tags and sku.', 'avia_framework' );
			$params['innerHtml'].= "</div>";
			
			return $params;
		}



		/**
		 * Frontend Shortcode Handler
		 *
		 * @param array $atts array of attributes
		 * @param string $content text within enclosing form of shortcode element
		 * @param string $shortcodename the shortcode found, when == callback name
		 * @return string $output returns the modified html string
		 */
		function shortcode_handler($atts, $content = "", $shortcodename = "", $meta = "")
		{
			$output = "";
			$meta['el_class'];
			
			global $woocommerce, $product;
			if(!is_object($woocommerce) || !is_object($woocommerce->query) || empty($product)) return;
			
			$output .= "<div class='product_meta ".$meta['el_class']."'>";

				ob_start();
				if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : 

					$sku = $product->get_sku() ? $product->get_sku() : __( 'N/A', 'woocommerce' );
					$output .= '<span class="sku_wrapper">'. __( 'SKU:', 'woocommerce' ) .'<span class="sku"> '.  $sku.'</span></span>';

				endif;
				$output .= ob_get_clean();

				$output .= wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '</span>' );

				$output .= wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ', '</span>' );

			$output .= "</div>";
			
			return $output;
		}
	}
}



