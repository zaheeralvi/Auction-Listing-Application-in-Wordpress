<?php
/**
 * Button Row
 *
 * Displays a set of buttons with links
 * Each button can be styled individually
 * 
 *
 * @author tinabillinger
 * @since 4.3
 */



if (!class_exists('avia_sc_buttonrow')) {
    class avia_sc_buttonrow extends aviaShortcodeTemplate
    {
        /**
         * Create the config array for the shortcode button
         */
        function shortcode_insert_button()
        {
            $this->config['self_closing']	=	'no';

            $this->config['name'] = __('Button Row', 'avia_framework');
            $this->config['tab'] = __('Content Elements', 'avia_framework');
            $this->config['icon'] = AviaBuilder::$path['imagesURL'] . "sc-buttonrow.png";
            $this->config['order'] = 84;
            $this->config['target'] = 'avia-target-insert';
            $this->config['shortcode'] = 'av_buttonrow';
            $this->config['shortcode_nested'] = array('av_buttonrow_item');
            $this->config['tooltip'] = __('Displays multiple buttons beside each other', 'avia_framework');
            $this->config['preview'] = true;
			$this->config['disabling_allowed'] = true;
        }

        function extra_assets()
        {
            //load css
			wp_enqueue_style( 'avia-module-button' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/buttons/buttons.css' , array('avia-layout'), false );
            wp_enqueue_style( 'avia-module-buttonrow' , AviaBuilder::$path['pluginUrlRoot'].'avia-shortcodes/buttonrow/buttonrow.css' , array('avia-layout'), false );
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
                    "type" => "tab_container", 'nodescription' => true
                ),

                    array(
                        "type" => "tab",
                        "name" => __("Content", 'avia_framework'),
                        'nodescription' => true
                    ),

                    array(
                        "name" => __("Add/Edit Buttons", 'avia_framework'),
                        "desc" => __("Here you can add, remove and edit buttons.", 'avia_framework'),
                        "type" => "modal_group",
                        "id" => "content",
                        "modal_title" => __("Edit Button", 'avia_framework'),
                        "std" =>
                            array(
                                array('label' => __('Click me', 'avia_framework'), 'icon' => '4'),
                                array('label' => __('Call to Action', 'avia_framework'), 'icon' => '5'),
                                array('label' => __('Click me', 'avia_framework'), 'icon' => '6'),
                            ),

                        'subelements' => array(

                            array(
                                "type" => "tab_container", 'nodescription' => true
                            ),

                                array(
                                    "type" => "tab",
                                    "name" => __("Content", 'avia_framework'),
                                    'nodescription' => true
                                ),

                                array(
                                    "name" => __("Button Label", 'avia_framework'),
                                    "desc" => __("This is the text that appears on your button.", 'avia_framework'),
                                    "id" => "label",
                                    "type" => "input",
                                    "std" => __("Click me", 'avia_framework')
                                ),

                                array(
                                    "name" => __("Button Link?", 'avia_framework'),
                                    "desc" => __("Where should your button link to?", 'avia_framework'),
                                    "id" => "link",
                                    "type" => "linkpicker",
                                    "fetchTMPL" => true,
                                    "subtype" => array(
                                        __('Set Manually', 'avia_framework') => 'manually',
                                        __('Single Entry', 'avia_framework') => 'single',
                                        __('Taxonomy Overview Page', 'avia_framework') => 'taxonomy',
                                    ),
                                    "std" => ""
                                ),

                                array(
                                    "name" => __("Open Link in new Window?", 'avia_framework'),
                                    "desc" => __("Select here if you want to open the linked page in a new window", 'avia_framework'),
                                    "id" => "link_target",
                                    "type" => "select",
                                    "std" => "",
                                    "subtype" => AviaHtmlHelper::linking_options()
                                ),

                                array(
                                    "name" => __("Button Size", 'avia_framework'),
                                    "desc" => __("Choose the size of your button here", 'avia_framework'),
                                    "id" => "size",
                                    "type" => "select",
                                    "std" => "small",
                                    "subtype" => array(
                                        __('Small', 'avia_framework') => 'small',
                                        __('Medium', 'avia_framework') => 'medium',
                                        __('Large', 'avia_framework') => 'large',
                                        __('X Large', 'avia_framework') => 'x-large',
                                    )
                                ),
                                
								array(	
									"name" 	=> __("Button Label display", 'avia_framework' ),
									"desc" 	=> __("Select how to display the label", 'avia_framework' ),
									"id" 	=> "label_display",
									"type" 	=> "select",
									"std" 	=> "",
									"subtype" => array(
										__('Always display',  'avia_framework' ) => '' ,	
										__('Display on hover',  'avia_framework' ) =>'av-button-label-on-hover',
								)),
								
                                array(
                                    "name" => __("Button Icon", 'avia_framework'),
                                    "desc" => __("Should an icon be displayed at the left side of the button", 'avia_framework'),
                                    "id" => "icon_select",
                                    "type" => "select",
                                    "std" => "yes",
                                    "subtype" => array(
                                        __('No Icon', 'avia_framework') => 'no',
                                        __('Yes, display Icon to the left', 'avia_framework') => 'yes',
                                        __('Yes, display Icon to the right', 'avia_framework') => 'yes-right-icon',
                                    )
                                ),

                                array(
                                    "name" => __("Icon Visibility", 'avia_framework'),
                                    "desc" => __("Check to only display icon on hover", 'avia_framework'),
                                    "id" => "icon_hover",
                                    "type" => "checkbox",
                                    "std" => "",
                                    "required" => array('icon_select', 'not_empty_and', 'no')
                                ),

                                array(
                                    "name" => __("Button Icon", 'avia_framework'),
                                    "desc" => __("Select an icon for your Button below", 'avia_framework'),
                                    "id" => "icon",
                                    "type" => "iconfont",
                                    "std" => "",
                                    "required" => array('icon_select', 'not_empty_and', 'no')
                                ),

                                // close tab content
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
                                    "name" => __("Button Color", 'avia_framework'),
                                    "desc" => __("Choose a color for your button here", 'avia_framework'),
                                    "id" => "color",
                                    "type" => "select",
                                    "std" => "theme-color",
                                    "subtype" => array(
                                        __('Translucent Buttons', 'avia_framework') => array(
                                            __('Light Transparent', 'avia_framework') => 'light',
                                            __('Dark Transparent', 'avia_framework') => 'dark',
                                        ),
                                        __('Colored Buttons', 'avia_framework') => array(
                                            __('Theme Color', 'avia_framework') => 'theme-color',
                                            __('Theme Color Highlight', 'avia_framework') => 'theme-color-highlight',
                                            __('Theme Color Subtle', 'avia_framework') => 'theme-color-subtle',
                                            __('Blue', 'avia_framework') => 'blue',
                                            __('Red', 'avia_framework') => 'red',
                                            __('Green', 'avia_framework') => 'green',
                                            __('Orange', 'avia_framework') => 'orange',
                                            __('Aqua', 'avia_framework') => 'aqua',
                                            __('Teal', 'avia_framework') => 'teal',
                                            __('Purple', 'avia_framework') => 'purple',
                                            __('Pink', 'avia_framework') => 'pink',
                                            __('Silver', 'avia_framework') => 'silver',
                                            __('Grey', 'avia_framework') => 'grey',
                                            __('Black', 'avia_framework') => 'black',
                                            __('Custom Color', 'avia_framework') => 'custom',
                                        )
                                    ),
                                ),

                                array(
                                    "name" => __("Custom Background Color", 'avia_framework'),
                                    "desc" => __("Select a custom background color for your Button here", 'avia_framework'),
                                    "id" => "custom_bg",
                                    "type" => "colorpicker",
                                    "std" => "#444444",
                                    "required" => array('color', 'equals', 'custom')
                                ),

                                array(
                                    "name" => __("Custom Font Color", 'avia_framework'),
                                    "desc" => __("Select a custom font color for your Button here", 'avia_framework'),
                                    "id" => "custom_font",
                                    "type" => "colorpicker",
                                    "std" => "#ffffff",
                                    "required" => array('color', 'equals', 'custom')
                                ),

                                // close tab colors
                                array(
                                    "type" => "close_div",
                                    'nodescription' => true
                                ),

                            // close tab-container
                            array(
                                "type" => "close_div",
                                'nodescription' => true
                            ),
                        ),
                    ), /*modal group */

                    array(
                        "name" => __("Align Buttons", 'avia_framework'),
                        "desc" => __("Choose the alignment of your buttons here", 'avia_framework'),
                        "id" => "alignment",
                        "type" => "select",
                        "std" => "center",
                        "subtype" =>
                            array(
                                __('Align Left', 'avia_framework') => 'left',
                                __('Align Center', 'avia_framework') => 'center',
                                __('Align Right', 'avia_framework') => 'right',
                            )
                    ),

                    array(
                        "name" => __("Space between buttons", 'avia_framework'),
                        "desc" => __("Define the space between the buttons", 'avia_framework'),
                        "id" => "button_spacing",
                        "container_class" => 'av_half',
                        "type" => "input",
                        "std" => "5"
                   ),

                    array(
                        "name" => __("Unit", 'avia_framework'),
                        "desc" => __("Unit for the spacing", 'avia_framework'),
                        "id" => "button_spacing_unit",
                        "container_class" => 'av_half',
                        "type" => "select",
                        "std" => "px",
                        "subtype" =>
                        array(
                            __('px', 'avia_framework') => 'px',
                            __('%', 'avia_framework') => '%',
                            __('em', 'avia_framework') => 'em',
                            __('rem', 'avia_framework') => 'rem',
                        )
                    ),


                    // close tab content
                    array(
                        "type" => "close_div",
                        'nodescription' => true
                    ),

                    array(
                        "type" => "tab",
                        "name" => __("Screen Options", 'avia_framework'),
                        'nodescription' => true
                    ),

                    array(
                        "name" => __("Element Visibility", 'avia_framework'),
                        "desc" => __("Set the visibility for this element, based on the device screensize.", 'avia_framework'),
                        "type" => "heading",
                        "description_class" => "av-builder-note av-neutral",
                    ),

                    array(
                        "desc" => __("Hide on large screens (wider than 990px - eg: Desktop)", 'avia_framework'),
                        "id" => "av-desktop-hide",
                        "std" => "",
                        "container_class" => 'av-multi-checkbox',
                        "type" => "checkbox"
                    ),

                    array(
                        "desc" => __("Hide on medium sized screens (between 768px and 989px - eg: Tablet Landscape)", 'avia_framework'),
                        "id" => "av-medium-hide",
                        "std" => "",
                        "container_class" => 'av-multi-checkbox',
                        "type" => "checkbox"
                    ),

                    array(
                        "desc" => __("Hide on small screens (between 480px and 767px - eg: Tablet Portrait)", 'avia_framework'),
                        "id" => "av-small-hide",
                        "std" => "",
                        "container_class" => 'av-multi-checkbox',
                        "type" => "checkbox"
                    ),

                    array(
                        "desc" => __("Hide on very small screens (smaller than 479px - eg: Smartphone Portrait)", 'avia_framework'),
                        "id" => "av-mini-hide",
                        "std" => "",
                        "container_class" => 'av-multi-checkbox',
                        "type" => "checkbox"
                    ),

                    // close tab screen-options
                    array(
                        "type" => "close_div",
                        'nodescription' => true
                    ),

                // close tab-container
                array(
                    "type" => "close_div",
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
            $template = $this->update_template("label", __("Button", 'avia_framework') . ": {{label}}");

            extract(av_backend_icon($params)); // creates $font and $display_char if the icon was passed as param "icon" and the font as "font"

            $params['innerHtml'] = "";
            $params['innerHtml'] .= "<div class='avia_title_container'>";
            $params['innerHtml'] .= "<span " . $this->class_by_arguments('font', $font) . ">";
            $params['innerHtml'] .= "<span data-update_with='icon_fakeArg' class='avia_tab_icon'>" . $display_char . "</span>";
            $params['innerHtml'] .= "</span>";
            $params['innerHtml'] .= "<span {$template} >" . __("Button", 'avia_framework') . ": " . $params['args']['label'] . "</span></div>";

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

            extract(shortcode_atts(
                    array(
                        'alignment' => 'center',
                        'button_spacing' => '5',
                        'button_spacing_unit' => 'px'
                    ), $atts, $this->config['shortcode'])
            );

            $this->alignment = $alignment;
            $this->spacing = $button_spacing;
            $this->spacing_unit = $button_spacing_unit;

            $output = "";
            $output .= "<div class='avia-buttonrow-wrap avia-buttonrow-{$this->alignment} {$av_display_classes} ".$meta['el_class']."'>";
            $output .= ShortcodeHelper::avia_remove_autop($content, true);
            $output .= '</div>';

            return $output;
        }

        function av_buttonrow_item($atts, $content = "", $shortcodename = "")
        {
            extract($this->screen_options); //return $av_font_classes, $av_title_font_classes and $av_display_classes

            $atts = shortcode_atts(
                array(
                    'label' => 'Click me',
                    'link' => '',
                    'link_target' => '',
                    'color' => 'theme-color',
                    'custom_bg' => '#444444',
                    'custom_font' => '#ffffff',
                    'size' => 'small',
                    'position' => 'center',
                    'icon_select' => 'yes',
                    'icon' => '',
                    'font' => '',
                    'icon_hover' => '',
			        'label_display'=>'',
                ),
                $atts, 'av_buttonrow_item');

            $display_char = av_icon($atts['icon'], $atts['font']);
            $extraClass = $atts['icon_hover'] ? "av-icon-on-hover" : "";
            $spacing = $this->spacing;
            $spacing_unit = $this->spacing_unit;

            if ($atts['icon_select'] == "yes") $atts['icon_select'] = "yes-left-icon";

            $style = "";
            if ($atts['color'] == "custom") {
                $style .= AviaHelper::style_string($atts, 'custom_bg', 'background-color');
                $style .= AviaHelper::style_string($atts, 'custom_bg', 'border-color');
                $style .= AviaHelper::style_string($atts, 'custom_font', 'color');
            }

            if ($spacing) {

                $atts['margin-bottom'] = $spacing . $spacing_unit;
                $atts['margin-left'] = $spacing . $spacing_unit;
                $atts['margin-right'] = $spacing . $spacing_unit;

                $style .= AviaHelper::style_string($atts, 'margin-bottom');

                if ($this->alignment == "left") {
                    $style .= AviaHelper::style_string($atts, 'margin-right','margin-right',"");
                }

                if ($this->alignment == "right") {
                    $style .= AviaHelper::style_string($atts, 'margin-left');
                }

                if ($this->alignment == "center") {
                    $spacingval = round($spacing / 2);
                    $atts['margin-left'] = $spacingval;
                    $atts['margin-right'] = $spacingval;
                    $style .= AviaHelper::style_string($atts, 'margin-left', 'margin-left',$spacing_unit);
                    $style .= AviaHelper::style_string($atts, 'margin-right', 'margin-right', $spacing_unit);

                }
            }

            $style  = AviaHelper::style_string($style);

            $blank = strpos($atts['link_target'], '_blank') !== false ? ' target="_blank" ' : "";
            $blank .= strpos($atts['link_target'], 'nofollow') !== false ? ' rel="nofollow" ' : "";

            $link = AviaHelper::get_url($atts['link']);
            $link = (($link == "http://") || ($link == "manually")) ? "" : $link;
			
			$data = "";
			if(!empty($atts['label_display']) && $atts['label_display'] == "av-button-label-on-hover") 
			{
				$extraClass .= " av-button-label-on-hover ";
				$data = "data-avia-tooltip='".htmlspecialchars($atts['label'])."'";
				$atts['label'] = "";
			}
			
			if(empty($atts['label'])) $extraClass .= " av-button-notext ";	
					
            $content_html = "";
            if ('yes-left-icon' == $atts['icon_select']) $content_html .= "<span class='avia_button_icon avia_button_icon_left ' {$display_char}></span>";
            $content_html .= "<span class='avia_iconbox_title' >" . $atts['label'] . "</span>";
            if ('yes-right-icon' == $atts['icon_select']) $content_html .= "<span class='avia_button_icon avia_button_icon_right' {$display_char}></span>";

            $output = "";
            $output .= "<a href='{$link}' {$data} class='avia-button {$extraClass} " . $this->class_by_arguments('icon_select, color, size', $atts, true) . "' {$blank} {$style} >";
            $output .= $content_html;
            $output .= "</a>";

            return $output;

        }
    }
}
