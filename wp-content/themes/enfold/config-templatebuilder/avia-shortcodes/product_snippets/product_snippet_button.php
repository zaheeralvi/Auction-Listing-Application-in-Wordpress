<?php
/**
 * Product Purchase Button
 * 
 * Display the "Add to cart" button for the current product
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( !class_exists( 'woocommerce' ) )
{
	add_shortcode('av_product_button', 'avia_please_install_woo');
	return;
}

if ( !class_exists( 'avia_sc_produc_button' ) )
{
	class avia_sc_produc_button extends aviaShortcodeTemplate
	{
		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['self_closing']	=	'yes';
			
			$this->config['name']		= __('Product Purchase Button', 'avia_framework' );
			$this->config['tab']		= __('Plugin Additions', 'avia_framework' );
			$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-button.png";
			$this->config['order']		= 20;
			$this->config['target']		= 'avia-target-insert';
			$this->config['shortcode'] 	= 'av_product_button';
			$this->config['tooltip'] 	= __('Display the "Add to cart" button for the current product', 'avia_framework' );
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
			$params['innerHtml'].= 		__( 'Display the &quot;Add to cart&quot; button including prices and variations but no product description.', 'avia_framework' );
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
			if(!is_object($woocommerce) || !is_object($woocommerce->query) || empty($product) || is_admin() ) return;

			/**
			 * @since WC 3.0
			 */
			$wc_structured_data = isset( WC()->structured_data ) ? WC()->structured_data : null;
			
			/**
			 *	Remove default WC actions
			 */
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

			// produces an endless loop because $wc_structured_data uses 'content' filter and do_shortcode !!
			if( ! is_null( $wc_structured_data ) )
			{
				remove_action( 'woocommerce_single_product_summary', array( $wc_structured_data, 'generate_product_data' ), 60 );
			}
			
			// $product = wc_get_product();
			$output .= "<div class='av-woo-purchase-button ".$meta['el_class']."'>";
			
			/**
			 * Fix for plugin German Market that outputs the price (not a clean solution but easier to maintain). Can alos be placed in shortcode.css.
			 */
			$output .= '<style>';
			$output .=		'#top .av-woo-purchase-button > div > p.price {display: none;}';
			$output .= '</style>';
			
			$output .=		'<p class="price">' . $product->get_price_html() . '</p>';
			
			ob_start();
			wc_clear_notices();
			
			/**
			 * hooked by: add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			 */
			do_action( 'woocommerce_single_product_summary' );
			
			$output .= ob_get_clean();
			
			$output .= "</div>";
			
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
			if( ! is_null( $wc_structured_data ) )
			{
				add_action( 'woocommerce_single_product_summary', array( $wc_structured_data, 'generate_product_data' ), 60 );
			}
			
			return $output;
		}
	}
}



