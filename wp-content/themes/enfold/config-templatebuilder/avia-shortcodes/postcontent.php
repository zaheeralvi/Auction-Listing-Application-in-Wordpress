<?php
/**
 * Post/Page Content
 *
 * Display the content of another entry in a fullwidth area. Content of the selected page is integrated in the content flow
 * 
 * Element was in Beta till 4.2.6 and by default disabled. Todo: test with layerslider elements. currently throws error bc layerslider is only included if layerslider element is detected which is not the case with the post/page element
 * 
 * @since 4.2.7
 * @modified_by Günter
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


//if ( ! class_exists( 'avia_sc_postcontent' ) && current_theme_supports( 'experimental_postcontent' ) )
if ( ! class_exists( 'avia_sc_postcontent' ) )
{
	class avia_sc_postcontent extends aviaShortcodeTemplate
	{
			/**
			 * Create the config array for the shortcode button
			 * 
			 * @since 4.2.7
			 */
			public function shortcode_insert_button()
			{
				$this->config['self_closing']			=	'yes';
				$this->config['forced_load_objects']	=	array( 'layerslider' );			//	we must load layerslider because content might contain one
				
				$this->config['name']		= __('Page Content', 'avia_framework' );
				$this->config['tab']		= __('Content Elements', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-postcontent.png";
				$this->config['order']		= 30;
				$this->config['target']		= 'avia-target-insert';
				$this->config['shortcode'] 	= 'av_postcontent';
//				$this->config['modal_data'] = array('modal_class' => 'flexscreen');
				$this->config['tooltip'] 	= __('Display the content of another page (fullwidth)', 'avia_framework' );
				$this->config['drag-level'] = 1;
				$this->config['drop-level'] = 1;
				$this->config['preview'] = false;
			}

			/**
			 * Popup Elements
			 *
			 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
			 * opens a modal window that allows to edit the element properties
			 *
			 * @since 4.2.7
			 * @return void
			 */
			public function popup_elements()
			{
//				$itemcount = array('All'=>'-1');
//				for($i = 1; $i<101; $i++) $itemcount[$i] = $i;

				$this->elements = array(

					 array(
							"name" 	=> __("Which Entry?", 'avia_framework' ),
							"desc" 	=> __("Select the Entry that should be displayed", 'avia_framework' ),
							"id" 	=> "link",
							"fetchTMPL"	=> true,
							"type" 	=> "linkpicker",
							"subtype"  => array(	__('Single Entry', 'avia_framework' ) =>'single'),
							"posttype" => array('page','portfolio'),
							"std" 	=> "page",
						 	'hierarchical'	=> 'yes',					//	'yes' ( =default) | 'no'
							'post_status'	=> 'publish,private, draft'	//	array | string  (default = publish)
					 	)
					);
			}

			
			/**
			 * @since 4.2.7
			 */
			public function extra_assets()
			{
				add_filter('avia_builder_precompile', array( $this, 'handler_avia_builder_precompile' ), 1 );
			}


			/**
			 * Scan content for av_postcontent and replace it with the content of the page.
			 * As this inserted page might also contain this shortcode and so on we have to do thar in a loop
			 * 
			 * @since 4.2.7
			 * @param string $content
			 * @return string
			 */
			public function handler_avia_builder_precompile( $content )
			{
				global $shortcode_tags;

	 			//in case we got none/more than one postcontent elements make sure that replacement doesnt get executed/onle gets executed once
				
				/**
				 * In case we have no av_postcontent we can return
				 */
	 			if( strpos( $content, '[' . $this->config['shortcode'] ) === false ) 
				{
					return $content;
				}
				
				/**
				 * save the "real" shortcode array and limit execution to the shortcode of this class only
				 */
	 			$old_sc = $shortcode_tags;
				$shortcode_tags = array( $this->config['shortcode'] => array( $this, 'shortcode_handler' ) );
		
				while( false !== strpos( $content, $this->config['shortcode'] ) )
				{
					$content = do_shortcode( $content );
				}
				
				/**
				 * Restore the original shortcode pattern
				 */
	 			$shortcode_tags = $old_sc;
				
				/**
				 * Update the shortcode tree to reflect the current page structure.
				 * Prior make sure that shortcodes are balanced.
				 */
				Avia_Builder()->get_shortcode_parser()->set_builder_save_location( 'none' );
				$content = ShortcodeHelper::clean_up_shortcode( $content, 'balance_only' );
				ShortcodeHelper::$tree = ShortcodeHelper::build_shortcode_tree( $content );
				
				

	 			//$content = preg_replace("!\[av_postcontent.*?\]!","",$content);

	 			//now we need to re calculate the shortcode tree so that all elements that are pulled from different posts also get the correct location
//	 			$pattern = str_replace('av_postcontent','av_psprocessed', ShortcodeHelper::get_fake_pattern());
//
//	 			preg_match_all("/".$pattern."/s", $content, $matches);
//	 			ShortcodeHelper::$tree = ShortcodeHelper::build_shortcode_tree($matches);
			
				return $content;
			}


			/**
			 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
			 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
			 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
			 *
			 *
			 * @since 4.2.7
			 * @param array $params this array holds the default values for $content and $args.
			 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
			 */
			public function editor_element($params)
			{
				$link = isset( $params['args']['link'] ) ? $params['args']['link'] : '';
				$entry = AviaHelper::get_entry( $link );
				
				$title = '';
				if( $entry instanceof WP_Post )
				{
					$title = esc_html( $entry->post_title ) . " ({$entry->post_type}, {$entry->ID} )";
				}
				
				$update_template =	'<span class="av-postcontent-headline">{{link}}</span>';
				$update	= $this->update_template( 'link', $update_template );
				
				$template = str_replace('{{link}}', $title, $update_template );
				
				$params['innerHtml'] = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
				$params['innerHtml'].= "<div class='avia-element-label'>".$this->config['name']."</div>";
				$params['innerHtml'].= "<div class='avia-element-description'>".__('Allows you to display the content of a different entry', 'avia_framework' )."</div>";
				
				$params['innerHtml'].=	'<div class="av-postcontent" data-update_object="all-elements" ' . $update . '>';
				$params['innerHtml'].=		$template;
				$params['innerHtml'].=	'</div>';
						
				return $params;
			}



			/**
			 * Frontend Shortcode Handler
			 * 
			 * This handler is called only within a precompile handler and returns the unmodified content (including shortcodes)
			 * of the requested page 
			 *
			 * @since 4.2.7
			 * @param array $atts array of attributes
			 * @param string $content text within enclosing form of shortcode element
			 * @param string $shortcodename the shortcode found, when == callback name
			 * @param array $meta
			 * @return string $output returns the modified html string
			 */
			public function shortcode_handler($atts, $content = "", $shortcodename = "", $meta = '' )
			{
				global $shortcode_tags;
				
				extract( shortcode_atts( array(
											'link' => ''
										), $atts, $this->config['shortcode'] ) );

				$output  = "";
				$post_id = function_exists( 'avia_get_the_id' ) ? avia_get_the_id() : get_the_ID();
				$entry   = AviaHelper::get_entry( $link );
				
				$cm = isset( $meta['custom_markup'] ) ? $meta['custom_markup'] : '';

				if( ! empty( $entry ) )
				{
					if( $entry->ID == $post_id )
					{
						$output .= '<article style = "padding:20px;text-align:center;" class="entry-content main_color" ' . avia_markup_helper( array( 'context' => 'entry','echo' => false, 'id' => $entry->ID, 'custom_markup' => $cm ) ) . '>';
						$output .= "You added a Post/Page Content Element to this entry that tries to display itself. This would result in an infinite loop. Please select a different entry or remove the element";
						$output .= '</article>';
					}
					else
					{
						/**
						 * Remove this shortcode - nesting of same named shortcode is not supported by WP. We must take care of this in a loop outside
						 */
						$old_tags = $shortcode_tags;
						$shortcode_tags = array();
						
						$builder_stat = Avia_Builder()->get_alb_builder_status( $entry->ID );
						
						if( ( 'active' == $builder_stat ) && ! is_preview() )
						{
							$content = Avia_Builder()->get_posts_alb_content( $entry->ID );
						}
						else
						{
							$content = $entry->post_content;
						}
						
						
//						$output .=	'<div class="entry-content" ' . avia_markup_helper( array( 'context' => 'entry', 'echo' => false, 'id' => $entry->ID, 'custom_markup' => $cm ) ) . '>';
						$output .=		$content;
//						$output .=	'</div>';
						
						$shortcode_tags = $old_tags;
					}
				}
				
				
				
				/**
				 * Can be removed, because we removed all shortcodes from $shortcode_tags except this one
				 */
//				return do_shortcode($output);
				
				return $output;
			}

	}
}
