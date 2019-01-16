<?php
/**
 * Icon Grid Shortcode
 *
 * @author tinabillinger
 * @since 4.5
 * Creates an icon grid with toolips or flip content
 */
if ( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if ( !class_exists( 'avia_sc_icongrid' ) )
{
	class avia_sc_icongrid extends aviaShortcodeTemplate
	{
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['self_closing']	=	'no';
				
				$this->config['name']		= __('Icon Grid', 'avia_framework' );
				$this->config['tab']		= __('Content Elements', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-icongrid.png";
				$this->config['order']		= 90;
				$this->config['target']		= 'avia-target-insert';
				$this->config['shortcode'] 	= 'av_icongrid';
				$this->config['shortcode_nested'] = array('av_icongrid_item');
                $this->config['tooltip'] 	= __('Creates an icon grid with toolips or flip content', 'avia_framework' );
				$this->config['preview'] 	= false;
				$this->config['disabling_allowed'] = true;

			}
			
			function extra_assets()
			{
				wp_enqueue_style( 'avia-module-icon' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/icon/icon.css' , array('avia-layout'), false );
				wp_enqueue_style( 'avia-module-icongrid' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/icongrid/icongrid.css' , array('avia-layout'), false );
				
				wp_enqueue_script( 'avia-module-icongrid' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/icongrid/icongrid.js' , array('avia-shortcodes'), false, TRUE );

			}

			/**
			 * Popup Elements
			 *
			 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
			 * opens a modal window that allows to edit the element properties
			 *
			 * @return void
			 */
			function popup_elements()
			{
				$this->elements = array(
					array(
							"type" 	=> "tab_container", 'nodescription' => true
						),
						
					array(
							"type" 	=> "tab",
							"name"  => __("Content" , 'avia_framework'),
							'nodescription' => true
						),

						array(
							"name" => __("Add/Edit Grid items", 'avia_framework' ),
							"desc" => __("Here you can add, remove and edit the items of your item grid.", 'avia_framework' ),
							"type" 			=> "modal_group",
							"id" 			=> "content",
							"modal_title" 	=> __("Edit Grid Item", 'avia_framework' ),
							"std"			=> array(
													array('title'=>__('Grid Title 1', 'avia_framework' ), 'icon'=>'43', 'content'=>'Enter content here'),
													array('title'=>__('Grid Title 2', 'avia_framework' ), 'icon'=>'25', 'content'=>'Enter content here'),
													array('title'=>__('Grid Title 3', 'avia_framework' ), 'icon'=>'64', 'content'=>'Enter content here'),
                            ),


							'subelements' 	=> array(
                                array(
                                    "type" => "tab_container", 'nodescription' => true
                                ),

                                    array(
                                        "type" => "tab",
                                        "name" => __("Content", 'avia_framework'),
                                        'nodescription' => true
                                    ),

                                array(
                                    "name" 	=> __("Grid Item Title", 'avia_framework' ),
                                    "desc" 	=> __("Enter the grid item title here (Better keep it short)", 'avia_framework' ) ,
                                    "id" 	=> "title",
                                    "std" 	=> "Grid Title",
                                    "type" 	=> "input"),


                                array(
                                        "name" 	=> __("Title Link?", 'avia_framework' ),
                                        "desc" 	=> __("Do you want to apply  a link to the title?", 'avia_framework' ),
                                        "id" 	=> "link",
                                        "type" 	=> "linkpicker",
                                        "fetchTMPL"	=> true,
                                        "std"	=> "",
                                        "subtype" => array(
                                            __('No Link', 'avia_framework' ) =>'',
                                            __('Set Manually', 'avia_framework' ) =>'manually',
                                            __('Single Entry', 'avia_framework' ) =>'single',
                                            __('Taxonomy Overview Page',  'avia_framework' )=>'taxonomy',
                                        ),
                                        "std" 	=> ""),

                                    array(
                                        "name" 	=> __("Open in new window", 'avia_framework' ),
                                        "desc" 	=> __("Do you want to open the link in a new window", 'avia_framework' ),
                                        "id" 	=> "linktarget",
                                        "required" 	=> array('link', 'not', ''),
                                        "type" 	=> "select",
                                        "std" 	=> "no",
                                        "subtype" => AviaHtmlHelper::linking_options()),

                                    array(
                                        "name" 	=> __("Grid Item Sub-Title", 'avia_framework' ),
                                        "desc" 	=> __("Enter the grid item sub-title here", 'avia_framework' ) ,
                                        "id" 	=> "subtitle",
                                        "std" 	=> "Grid Sub-Title",
                                        "type" 	=> "input"),

                                    array(
                                        "name" 	=> __("Grid Item Icon",'avia_framework' ),
                                        "desc" 	=> __("Select an icon for your grid item below",'avia_framework' ),
                                        "id" 	=> "icon",
                                        "type" 	=> "iconfont",
                                        "std" 	=> "",
                                    ),


                                    array(
                                        "name" 	=> __("Grid Item Content", 'avia_framework' ),
                                        "desc" 	=> __("Enter some content here", 'avia_framework' ) ,
                                        "id" 	=> "content",
                                        "type" 	=> "tiny_mce",
                                        "std" 	=> __("Grid Content goes here", 'avia_framework'),
                                    ),


                                    array(
                                            "type" => "close_div",
                                            'nodescription' => true
                                        ),


                                    array(
                                        "type" => "tab",
                                        "name" => __("Colors", 'avia_framework'),
                                        'nodescription' => true
                                    ),

                                    array(
                                        "name" 	=> __("Font Colors", 'avia_framework' ),
                                        "desc" 	=> __("Either use the themes default colors or apply some custom ones", 'avia_framework' ),
                                        "id" 	=> "item_font_color",
                                        "type" 	=> "select",
                                        "std" 	=> "",
                                        "subtype" => array(
                                            __('Default', 'avia_framework' )=>'',
                                            __('Define Custom Colors', 'avia_framework' )=>'custom'),
                                    ),

                                    array(
                                        "name" 	=> __("Custom Icon Font Color", 'avia_framework' ),
                                        "desc" 	=> __("Select a custom font color. Leave empty to use the default", 'avia_framework' ),
                                        "id" 	=> "item_custom_icon",
                                        "type" 	=> "colorpicker",
                                        "std" 	=> "",
                                        "container_class" => 'av_half av_half_first',
                                        "required" => array('item_font_color','equals','custom')
                                    ),

                                array(
                                    "name" 	=> __("Custom Title Font Color", 'avia_framework' ),
                                    "desc" 	=> __("Select a custom font color. Leave empty to use the default", 'avia_framework' ),
                                    "id" 	=> "item_custom_title",
                                    "type" 	=> "colorpicker",
                                    "std" 	=> "",
                                    "container_class" => 'av_half',
                                    "required" => array('item_font_color','equals','custom')
                                ),

                                array(
                                    "name" 	=> __("Custom Sub-Title Font Color", 'avia_framework' ),
                                    "desc" 	=> __("Select a custom font color. Leave empty to use the default", 'avia_framework' ),
                                    "id" 	=> "item_custom_subtitle",
                                    "type" 	=> "colorpicker",
                                    "std" 	=> "",
                                    "container_class" => 'av_half',
                                    "required" => array('item_font_color','equals','custom')
                                ),

                                array(
                                        "name" 	=> __("Custom Content Font Color", 'avia_framework' ),
                                        "desc" 	=> __("Select a custom font color. Leave empty to use the default", 'avia_framework' ),
                                        "id" 	=> "item_custom_content",
                                        "type" 	=> "colorpicker",
                                        "std" 	=> "",
                                        "container_class" => 'av_half',
                                        "required" => array('item_font_color','equals','custom')

                                    ),

                                    array(
                                        "name" 	=> __("Background Colors", 'avia_framework' ),
                                        "desc" 	=> __("Either use the themes default colors or apply some custom ones", 'avia_framework' ),
                                        "id" 	=> "item_bg_color",
                                        "type" 	=> "select",
                                        "std" 	=> "",
                                        "subtype" => array(
                                            __('Default', 'avia_framework' )=>'',
                                            __('Define Custom Colors', 'avia_framework' )=>'custom'),
                                    ),

                                    array(
                                        "name" 	=> __("Custom Background Color Front", 'avia_framework' ),
                                        "desc" 	=> __("Select a custom background color. Leave empty to use the default", 'avia_framework' ),
                                        "id" 	=> "item_custom_front_bg",
                                        "type" 	=> "colorpicker",
                                        "std" 	=> "",
                                        "container_class" => 'av_half av_half_first',
                                        "required" => array('item_bg_color','equals','custom')
                                    ),

                                    array(
                                        "name" 	=> __("Custom Background Color Back / Tooltip", 'avia_framework' ),
                                        "desc" 	=> __("Select a custom background color. Leave empty to use the default", 'avia_framework' ),
                                        "id" 	=> "item_custom_back_bg",
                                        "type" 	=> "colorpicker",
                                        "std" 	=> "",
                                        "container_class" => 'av_third av_half',
                                        "required" => array('item_bg_color','equals','custom')
                                    ),


                                    array(
                                            "type" => "close_div",
                                            'nodescription' => true
                                        ),

                                array(
                                    "type" => "close_div",
                                    'nodescription' => true
                                ),

						)
					),


                    array(
                        "name" 	=> __("Icon Grid Styling", 'avia_framework' ),
                        "desc" 	=> __("Change the styling of your icon grid", 'avia_framework' ),
                        "id" 	=> "icongrid_styling",
                        "type" 	=> "select",
                        "std" 	=> "flipbox",
                        "subtype" => array(
                            __('Content appears in Flip Box', 'avia_framework' )  =>'flipbox',
                            __('Content appears in Tooltip', 'avia_framework' ) =>'tooltip',
                        )),

                    array(
                        "name" 	=> __("Columns", 'avia_framework' ),
                        "desc" 	=> __("Define the number of columns, depending on the amount of text you want to add.", 'avia_framework' ),
                        "id" 	=> "icongrid_numrow",
                        "type" 	=> "select",
                        "std" 	=> "3",
                        "subtype" => array(
                            __('3 Items', 'avia_framework' ) =>'3',
                            __('4 Items', 'avia_framework' ) =>'4',
                            __('5 Items', 'avia_framework' ) =>'5',
                        )),

                    array(
                        "name" 	=> __("Grid Borders", 'avia_framework' ),
                        "desc" 	=> __("Define the appearence of the grid borders here.", 'avia_framework' ),
                        "id" 	=> "icongrid_borders",
                        "type" 	=> "select",
                        "std" 	=> "3",
                        "subtype" => array(
                            __('No Borders', 'avia_framework' ) =>'none',
                            __('Borders between elements', 'avia_framework' ) =>'between',
                            __('All Borders', 'avia_framework' ) =>'all',
                        )),


                    array(
                        "name" 	=> __("Title Font Size", 'avia_framework' ),
                        "desc" 	=> __("Select a custom font size. Leave empty to use the default", 'avia_framework' ),
                        "id" 	=> "custom_title_size",
                        "type" 	=> "select",
                        "std" 	=> "",
                        "container_class" => 'av_half',
                        "subtype" => AviaHtmlHelper::number_array(10,50,1, array( __("Default Size", 'avia_framework' )=>''), 'px'),
                    ),
                    array(
                        "name" 	=> __("Sub-Title Font Size", 'avia_framework' ),
                        "desc" 	=> __("Select a custom font size. Leave empty to use the default", 'avia_framework' ),
                        "id" 	=> "custom_subtitle_size",
                        "type" 	=> "select",
                        "std" 	=> "",
                        "container_class" => 'av_half',
                        "subtype" => AviaHtmlHelper::number_array(10,50,1, array( __("Default Size", 'avia_framework' )=>''), 'px'),
                    ),

					array(	
						"name" 	=> __("Content Font Size", 'avia_framework' ),
						"desc" 	=> __("Select a custom font size. Leave empty to use the default", 'avia_framework' ),
						"id" 	=> "custom_content_size",
						"type" 	=> "select",
                        "container_class" => 'av_half',
						"std" 	=> "",
						"subtype" => AviaHtmlHelper::number_array(10,50,1, array( __("Default Size", 'avia_framework' )=>''), 'px'),
						),

                    array(
                        "name" 	=> __("Icon Font Size", 'avia_framework' ),
                        "desc" 	=> __("Select a custom font size. Leave empty to use the default", 'avia_framework' ),
                        "id" 	=> "custom_icon_size",
                        "type" 	=> "select",
                        "container_class" => 'av_half',
                        "std" 	=> "",
                        "subtype" => AviaHtmlHelper::number_array(10,50,1, array( __("Default Size", 'avia_framework' )=>''), 'px'),
                    ),


					array(
							"type" 	=> "close_div",
							'nodescription' => true
						),
					
					array(
							"type" 	=> "tab",
							"name"	=> __("Colors",'avia_framework' ),
							'nodescription' => true
						),

                    array(
                        "name" 	=> __("Font Colors", 'avia_framework' ),
                        "desc" 	=> __("Either use the themes default colors or apply some custom ones", 'avia_framework' ),
                        "id" 	=> "font_color",
                        "rgba"  => true,
                        "type" 	=> "select",
                        "std" 	=> "",
                        "subtype" => array(
                            __('Default', 'avia_framework' )=>'',
                            __('Define Custom Colors', 'avia_framework' )=>'custom'),
                    ),

                    array(
                        "name" 	=> __("Custom Icon Font Color", 'avia_framework' ),
                        "desc" 	=> __("Select a custom font color. Leave empty to use the default", 'avia_framework' ),
                        "id" 	=> "custom_icon",
                        "rgba"  => true,
                        "type" 	=> "colorpicker",
                        "std" 	=> "",
                        "container_class" => 'av_half av_half_first',
                        "required" => array('font_color','equals','custom')
                    ),

                    array(
                        "name" 	=> __("Custom Title Font Color", 'avia_framework' ),
                        "desc" 	=> __("Select a custom font color. Leave empty to use the default", 'avia_framework' ),
                        "id" 	=> "custom_title",
                        "rgba"  => true,
                        "type" 	=> "colorpicker",
                        "std" 	=> "",
                        "container_class" => 'av_half',
                        "required" => array('font_color','equals','custom')
                    ),

                    array(
                        "name" 	=> __("Custom Sub-Title Font Color", 'avia_framework' ),
                        "desc" 	=> __("Select a custom font color. Leave empty to use the default", 'avia_framework' ),
                        "id" 	=> "custom_subtitle",
                        "rgba"  => true,
                        "type" 	=> "colorpicker",
                        "std" 	=> "",
                        "container_class" => 'av_half',
                        "required" => array('font_color','equals','custom')
                    ),

                    array(
                        "name" 	=> __("Custom Content Font Color", 'avia_framework' ),
                        "desc" 	=> __("Select a custom font color. Leave empty to use the default", 'avia_framework' ),
                        "id" 	=> "custom_content",
                        "rgba"  => true,
                        "type" 	=> "colorpicker",
                        "std" 	=> "",
                        "container_class" => 'av_half',
                        "required" => array('font_color','equals','custom')

                    ),

                    array(
                        "name" 	=> __("Background Colors", 'avia_framework' ),
                        "desc" 	=> __("Either use the themes default colors or apply some custom ones", 'avia_framework' ),
                        "id" 	=> "bg_color",
                        "rgba"  => true,
                        "type" 	=> "select",
                        "std" 	=> "",
                        "subtype" => array(
                            __('Default', 'avia_framework' )=>'',
                            __('Define Custom Colors', 'avia_framework' )=>'custom'),
                    ),

                    array(
                        "name" 	=> __("Custom Background Color Front", 'avia_framework' ),
                        "desc" 	=> __("Select a custom background color. Leave empty to use the default", 'avia_framework' ),
                        "id" 	=> "custom_front_bg",
                        "rgba"  => true,
                        "type" 	=> "colorpicker",
                        "std" 	=> "",
                        "container_class" => 'av_half av_half_first',
                        "required" => array('bg_color','equals','custom')
                    ),

                    array(
                        "name" 	=> __("Custom Background Color Back / Tooltip", 'avia_framework' ),
                        "desc" 	=> __("Select a custom background color. Leave empty to use the default", 'avia_framework' ),
                        "id" 	=> "custom_back_bg",
                        "rgba"  => true,
                        "type" 	=> "colorpicker",
                        "std" 	=> "",
                        "container_class" => 'av_third av_half',
                        "required" => array('bg_color','equals','custom')
                    ),

                    array(
                        "name" 	=> __("Grid Border", 'avia_framework' ),
                        "desc" 	=> __("Either use the themes default colors or apply some custom ones", 'avia_framework' ),
                        "id" 	=> "grid_color",
                        "rgba"  => true,
                        "type" 	=> "select",
                        "std" 	=> "",
                        "subtype" => array(
                            __('Default', 'avia_framework' )=>'',
                            __('Define Custom Colors', 'avia_framework' )=>'custom'),
                    ),

                    array(
                        "name" 	=> __("Custom Grid Border Color", 'avia_framework' ),
                        "desc" 	=> __("Select a custom grid color. Leave empty to use the default", 'avia_framework' ),
                        "id" 	=> "custom_grid",
                        "type" 	=> "colorpicker",
                        "rgba"  => true,
                        "std" 	=> "",
                        "required" => array('grid_color','equals','custom')
                    ),

					array(
							"type" 	=> "close_div",
							'nodescription' => true
						),
						
						
					array(
									"type" 	=> "tab",
									"name"	=> __("Screen Options",'avia_framework' ),
									'nodescription' => true
								),
								
								
								array(
								"name" 	=> __("Element Visibility",'avia_framework' ),
								"desc" 	=> __("Set the visibility for this element, based on the device screensize.", 'avia_framework' ),
								"type" 	=> "heading",
								"description_class" => "av-builder-note av-neutral",
								),
							
								array(	
										"desc" 	=> __("Hide on large screens (wider than 990px - eg: Desktop)", 'avia_framework'),
										"id" 	=> "av-desktop-hide",
										"std" 	=> "",
										"container_class" => 'av-multi-checkbox',
										"type" 	=> "checkbox"),
								
								array(	
									
										"desc" 	=> __("Hide on medium sized screens (between 768px and 989px - eg: Tablet Landscape)", 'avia_framework'),
										"id" 	=> "av-medium-hide",
										"std" 	=> "",
										"container_class" => 'av-multi-checkbox',
										"type" 	=> "checkbox"),
										
								array(	
									
										"desc" 	=> __("Hide on small screens (between 480px and 767px - eg: Tablet Portrait)", 'avia_framework'),
										"id" 	=> "av-small-hide",
										"std" 	=> "",
										"container_class" => 'av-multi-checkbox',
										"type" 	=> "checkbox"),
										
								array(	
									
										"desc" 	=> __("Hide on very small screens (smaller than 479px - eg: Smartphone Portrait)", 'avia_framework'),
										"id" 	=> "av-mini-hide",
										"std" 	=> "",
										"container_class" => 'av-multi-checkbox',
										"type" 	=> "checkbox"),
									
								
									
								array(
									"name" 	=> __("Heading Font Size",'avia_framework' ),
									"desc" 	=> __("Set the font size for the heading, based on the device screensize.", 'avia_framework' ),
									"type" 	=> "heading",
									"description_class" => "av-builder-note av-neutral",
									),
										
									array(	"name" 	=> __("Font Size for medium sized screens (between 768px and 989px - eg: Tablet Landscape)", 'avia_framework' ),
						            "id" 	=> "av-medium-font-size-title",
						            "type" 	=> "select",
						            "subtype" => AviaHtmlHelper::number_array(10,120,1, array( __("Default", 'avia_framework' )=>'' , __("Hidden", 'avia_framework' )=>'hidden' ), "px"),
						            "std" => ""),
						            
						            array(	"name" 	=> __("Font Size for small screens (between 480px and 767px - eg: Tablet Portrait)", 'avia_framework' ),
						            "id" 	=> "av-small-font-size-title",
						            "type" 	=> "select",
						            "subtype" => AviaHtmlHelper::number_array(10,120,1, array( __("Default", 'avia_framework' )=>'', __("Hidden", 'avia_framework' )=>'hidden'), "px"),
						            "std" => ""),
						            
									array(	"name" 	=> __("Font Size for very small screens (smaller than 479px - eg: Smartphone Portrait)", 'avia_framework' ),
						            "id" 	=> "av-mini-font-size-title",
						            "type" 	=> "select",
						            "subtype" => AviaHtmlHelper::number_array(10,120,1, array( __("Default", 'avia_framework' )=>'', __("Hidden", 'avia_framework' )=>'hidden'), "px"),
						            "std" => ""),
						            
						            
						        array(
									"name" 	=> __("Content Font Size",'avia_framework' ),
									"desc" 	=> __("Set the font size for the content, based on the device screensize.", 'avia_framework' ),
									"type" 	=> "heading",
									"description_class" => "av-builder-note av-neutral",
									),
										
									array(	"name" 	=> __("Font Size for medium sized screens (between 768px and 989px - eg: Tablet Landscape)", 'avia_framework' ),
						            "id" 	=> "av-medium-font-size",
						            "type" 	=> "select",
						            "subtype" => AviaHtmlHelper::number_array(10,120,1, array( __("Default", 'avia_framework' )=>'', __("Hidden", 'avia_framework' )=>'hidden'), "px"),
						            "std" => ""),
						            
						            array(	"name" 	=> __("Font Size for small screens (between 480px and 767px - eg: Tablet Portrait)", 'avia_framework' ),
						            "id" 	=> "av-small-font-size",
						            "type" 	=> "select",
						            "subtype" => AviaHtmlHelper::number_array(10,120,1, array( __("Default", 'avia_framework' )=>'', __("Hidden", 'avia_framework' )=>'hidden'), "px"),
						            "std" => ""),
						            
									array(	"name" 	=> __("Font Size for very small screens (smaller than 479px - eg: Smartphone Portrait)", 'avia_framework' ),
						            "id" 	=> "av-mini-font-size",
						            "type" 	=> "select",
						            "subtype" => AviaHtmlHelper::number_array(10,120,1, array( __("Default", 'avia_framework' )=>'', __("Hidden", 'avia_framework' )=>'hidden'), "px"),
						            "std" => ""),    
				
							
								
							array(
									"type" 	=> "close_div",
									'nodescription' => true
								),
					
						
					array(
							"type" 	=> "close_div",
							'nodescription' => true
						),

				);


			}

			/**
			 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
			 * Works in the same way as Editor Element
			 * @param array $params this array holds the default values for $content and $args.
			 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
			 */
			function editor_sub_element($params)
			{
				$template = $this->update_template("title", __("Element", 'avia_framework' ). ": {{title}}");

				extract(av_backend_icon($params)); // creates $font and $display_char if the icon was passed as param "icon" and the font as "font" 

				$params['innerHtml']  = "";
				$params['innerHtml'] .= "<div class='avia_title_container'>";
				$params['innerHtml'] .= "<span ".$this->class_by_arguments('font' ,$font).">";
				$params['innerHtml'] .= "<span data-update_with='icon_fakeArg' class='avia_tab_icon'>".$display_char."</span>";
				$params['innerHtml'] .= "</span>";
				$params['innerHtml'] .= "<span {$template} >".__("Element", 'avia_framework' ).": ".$params['args']['title']."</span></div>";

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
				$this->screen_options = AviaHelper::av_mobile_sizes($atts);
				extract($this->screen_options); //return $av_font_classes, $av_title_font_classes and $av_display_classes
				
				extract(shortcode_atts(array(
				    'font_color' => '',
				    'custom_icon' => '',
                    'custom_title' => '',
                    'custom_subtitle' => '',
				    'custom_content' => '',
				    'bg_color' => '',
				    'custom_front_bg' => '',
                    'custom_back_bg' => '',
                    'grid_color' => '',
                    'custom_grid' => '',
                    'icongrid_styling' => '',
                    'icongrid_numrow' => '',
                    'icongrid_borders' => ''

				), $atts, $this->config['shortcode']));

				$this->icon_styling = array();
                $this->title_styling = array();
                $this->subtitle_styling = array();
				$this->content_styling = array();
				$this->flipbox_front_styling = array();
                $this->flipbox_back_styling = array();
                $this->wrapper_styling = array();
                $this->list_styling = array();

                $this->icongrid_styling 	= "avia-icongrid-".$icongrid_styling;
                $this->icongrid_numrow   	= "avia-icongrid-numrow-".$icongrid_numrow;
                $this->icongrid_borders   	= "avia-icongrid-borders-".$icongrid_borders;

                if ($font_color == 'custom') {
                    if ( !empty($custom_icon ) ) $this->icon_styling['color'] = $custom_icon;
                    if ( !empty($custom_title) ) $this->title_styling['color'] = $custom_title;
                    if ( !empty($custom_subtitle) ) $this->subtitle_styling['color'] = $custom_subtitle;
                    if ( !empty($custom_content) ) $this->content_styling['color'] = $custom_content;
                }

                if ($bg_color == 'custom') {
                    if ( !empty($custom_front_bg) ) $this->flipbox_front_styling['background_color'] = $custom_front_bg;
                    if ( !empty($custom_back_bg) ) $this->flipbox_back_styling['background_color'] = $custom_back_bg;
                }

                if ($grid_color == 'custom'){
                    if ( !empty($custom_grid) ) $this->wrapper_styling['color'] = $custom_grid;
                    if ( !empty($custom_grid) ) $this->list_styling['border-color'] = $custom_grid;
                }

                $list_styling_str = "";
                if (!empty($this->list_styling)) {
                    if (array_key_exists('border-color',$this->list_styling)){
                        $list_styling_str = AviaHelper::style_string($this->list_styling, 'border-color', 'border-color');
                    }
                }
                $list_styling_str = ($list_styling_str !== "") ? AviaHelper::style_string($list_styling_str) : "";

                $output	 = "";
				$output .= "<div class='avia-icon-grid-container {$av_display_classes} ".$meta['el_class']."'>";
				$output .= "<ul id='avia-icongrid-".uniqid()."' class='clearfix avia-icongrid {$this->icongrid_styling} {$this->icongrid_numrow} {$this->icongrid_borders} avia_animate_when_almost_visible' {$list_styling_str}>";
				$output .= ShortcodeHelper::avia_remove_autop( $content, true );
				$output .= "</ul>";
				$output .= "</div>";

				return $output;
			}

			function av_icongrid_item($atts, $content = "", $shortcodename = "")
			{
				extract($this->screen_options); //return $av_font_classes, $av_title_font_classes and $av_display_classes
				
                $atts =  shortcode_atts(
                    array(
                        'title' => '',
                        'subtitle' => '',
                        'link' => '',
                        'icon' => '',
                        'font' =>'',
                        'linktarget' => '',
                        'custom_markup' => '',
                        'item_font_color' => '',
                        'item_custom_icon' => '',
                        'item_custom_title' => '',
                        'item_custom_subtitle' => '',
                        'item_custom_content' => '',
                        'item_bg_color' => '',
                        'item_custom_front_bg' => '',
                        'item_custom_back_bg' => '',

                    ), $atts, 'av_icongrid_item');


                $icon_styling = array();
                if ( !empty($this->icon_styling) ) $icon_styling = array_merge( $icon_styling, $this->icon_styling );

                $title_styling = array();
                if ( !empty($this->title_styling) ) $title_styling = array_merge( $title_styling, $this->title_styling );

                $subtitle_styling = array();
                if ( !empty($this->subtitle_styling) ) $subtitle_styling = array_merge( $subtitle_styling, $this->subtitle_styling );

                $content_styling = array();
                if ( !empty($this->content_styling) ) $content_styling = array_merge( $content_styling, $this->content_styling );

                $flipbox_front_styling = array();
                if ( !empty($this->flipbox_front_styling) ) $flipbox_front_styling = array_merge( $flipbox_front_styling, $this->flipbox_front_styling );

                $flipbox_back_styling = array();
                if ( !empty($this->flipbox_back_styling) ) $flipbox_back_styling = array_merge( $flipbox_back_styling, $this->flipbox_back_styling );


                $icon_styling_str = "";
                $title_styling_str = "";
                $subtitle_styling_str = "";
                $content_styling_str = "";
                $flipbox_front_str = "";
                $item_bg_str = "";
                $flipbox_back_str = "";
                $wrapper_styling_str = "";

                /* item specific styling */
                if ($atts['item_font_color'] == 'custom') {
                    if ( !empty($atts['item_custom_icon'] ) ) $icon_styling['color'] = $atts['item_custom_icon'];
                    if ( !empty($atts['item_custom_title']) ) $title_styling['color'] = $atts['item_custom_title'];
                    if ( !empty($atts['item_custom_subtitle']) ) $subtitle_styling['color'] = $atts['item_custom_subtitle'];
                    if ( !empty($atts['item_custom_content']) ) $content_styling['color'] = $atts['item_custom_content'];
                }

                if ($atts['item_bg_color'] == 'custom') {
                    if ( !empty($atts['item_custom_front_bg']) ) $flipbox_front_styling['background_color'] = $atts['item_custom_front_bg'];
                    if ( !empty($atts['item_custom_back_bg']) ) $flipbox_back_styling['background_color'] = $atts['item_custom_back_bg'];
                }

                if (!empty($icon_styling)) {
                    if (array_key_exists('color',$icon_styling)) {
                        $icon_styling_str = AviaHelper::style_string($icon_styling, 'color', 'color');
                    }
                }

                if (!empty($title_styling)) {
                    if (array_key_exists('color',$title_styling)) {
                        $title_styling_str = AviaHelper::style_string($title_styling, 'color', 'color');
                    }
                }

                if (!empty($subtitle_styling)) {
                    if (array_key_exists('color',$subtitle_styling)) {
                        $subtitle_styling_str = AviaHelper::style_string($subtitle_styling, 'color', 'color');
                    }
                }

                if (!empty($content_styling)) {
                    if (array_key_exists('color',$content_styling)) {
                        $content_styling_str = AviaHelper::style_string($content_styling, 'color', 'color');
                    }
                }

                if (!empty($flipbox_front_styling)){
                    if ($this->icongrid_styling == 'avia-icongrid-flipbox' && array_key_exists('background_color', $flipbox_front_styling)){
                        $flipbox_front_str = AviaHelper::style_string($flipbox_front_styling, 'background_color', 'background-color');
                    }
                    if ($this->icongrid_styling == 'avia-icongrid-tooltip' && array_key_exists('background_color', $flipbox_front_styling)){
                        $item_bg_str = AviaHelper::style_string($flipbox_front_styling, 'background_color', 'background-color');
                    }
                }

                if (!empty($flipbox_back_styling)){
                    if (array_key_exists('background_color', $flipbox_back_styling)){
                        $flipbox_back_str = AviaHelper::style_string($flipbox_back_styling, 'background_color', 'background-color');
                    }
                }

                if (!empty($this->wrapper_styling)) {
                    if (array_key_exists('color',$this->wrapper_styling)){
                        $wrapper_styling_str = AviaHelper::style_string($this->wrapper_styling, 'color', 'color');
                    }
                }

                /* element wide styling */
                $icon_styling_str = ($icon_styling_str !== "") ? AviaHelper::style_string($icon_styling_str) : "";
                $title_styling_str = ($title_styling_str !== "") ? AviaHelper::style_string($title_styling_str) : "";
                $subtitle_styling_str = ($subtitle_styling_str !== "") ? AviaHelper::style_string($subtitle_styling_str) : "";
                $content_styling_str = ($content_styling_str !== "") ? AviaHelper::style_string($content_styling_str) : "";
                $flipbox_front_styling_str = ($flipbox_front_str !== "") ? AviaHelper::style_string($flipbox_front_str) : "";

                $item_bg_str = ($item_bg_str !== "") ? AviaHelper::style_string($item_bg_str) : "";
                $flipbox_back_styling_str = ($flipbox_back_str !== "") ? AviaHelper::style_string($flipbox_back_str) : "";
                $wrapper_styling_str = ($wrapper_styling_str !== "") ? AviaHelper::style_string($wrapper_styling_str) : "";


                $display_char = av_icon($atts['icon'], $atts['font']);
				$display_char_wrapper = array();

				$blank = (strpos($atts['linktarget'], '_blank') !== false || $atts['linktarget'] == 'yes') ? ' target="_blank" ' : "";
				$blank .= strpos($atts['linktarget'], 'nofollow') !== false ? ' rel="nofollow" ' : "";

                $avia_icongrid_wrapper = array(
                    'start' => 'div',
                    'end' => 'div'
                );


                if(!empty($atts['link']))
                {
					$atts['link'] = aviaHelper::get_url($atts['link']);

                    if(!empty($atts['link']))
                    {
                        $linktitle = $atts['title'];

                        $avia_icongrid_wrapper['start'] = "a href='{$atts['link']}' title='".esc_attr($linktitle)."' {$blank}";
                        $avia_icongrid_wrapper['end'] = 'a';

                    }
                }


                $contentClass = "";
                if(trim($content) == "")
                {
                	$contentClass = "av-icongrid-empty";
                }

                $title_el = "h4";
                $subtitle_el = "h6";
                $icongrid_title = "";
                $icongrid_subtitle = "";
                $touch_js = " ontouchstart='this.classList.toggle(\"av-flip\");'";

				$output  = "";
				$output .= "<li><{$avia_icongrid_wrapper['start']} class='avia-icongrid-wrapper' {$wrapper_styling_str}>";
                $output .= '<article '.$item_bg_str.' class="article-icon-entry '.$contentClass.'" '.avia_markup_helper(array('context' => 'entry','echo'=>false, 'custom_markup'=>$atts['custom_markup'])).'>';
                $output .= "<div class='avia-icongrid-front' {$flipbox_front_styling_str}>";
                $output .= "<div class='avia-icongrid-inner'>";
                $output .= "<div {$icon_styling_str} class='avia-icongrid-icon  avia-font-".$atts['font']."'><span class='icongrid-char ' {$display_char}></span></div>";
                $output .= '<header class="entry-content-header">';
                $markup = avia_markup_helper(array('context' => 'entry_title','echo'=>false, 'custom_markup'=>$atts['custom_markup']));
                $submarkup = avia_markup_helper(array('context' => 'entry_subtitle','echo'=>false, 'custom_markup'=>$atts['custom_markup']));
                if(!empty($atts['title'])) $output .="<{$title_el} class='av_icongrid_title icongrid_title{$icongrid_title} {$av_title_font_classes}' {$markup} {$title_styling_str}>".$atts['title']."</{$title_el}>";
                if(!empty($atts['subtitle'])) $output .="<{$subtitle_el} class='av_icongrid_subtitle icongrid_subtitle{$icongrid_subtitle} {$av_title_font_classes}' {$submarkup} {$subtitle_styling_str}>".$atts['subtitle']."</{$subtitle_el}>";
                $output .= "</header>";
                $output .= "</div></div>";
                $output .= "<div class='avia-icongrid-content' {$flipbox_back_styling_str}>";
                $output .= "<div class='avia-icongrid-inner' {$content_styling_str}>";
                $markup  = avia_markup_helper(array('context' => 'entry_content','echo'=>false, 'custom_markup'=>$atts['custom_markup']));
                $output .= "<div class='avia-icongrid-text {$av_font_classes}' {$markup}>".ShortcodeHelper::avia_apply_autop(ShortcodeHelper::avia_remove_autop( $content ) )."</div>";
                $output .= '</div></div>';
                $output .= '</article>';
                $output .= "</{$avia_icongrid_wrapper['end']}></li>";

				return $output;
			}

	}
}
