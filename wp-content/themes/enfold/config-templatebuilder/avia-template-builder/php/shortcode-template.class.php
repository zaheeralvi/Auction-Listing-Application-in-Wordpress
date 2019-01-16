<?php
/**
* Central Shortcode Template Class
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if ( !class_exists( 'aviaShortcodeTemplate' ) ) {

	abstract class aviaShortcodeTemplate
	{
		/**
		 *
		 * @var AviaBuilder 
		 */
		public $builder;
		
		/**
		 *
		 * @var array 
		 */
		public $config;
		
		/**
		 *
		 * @var array 
		 */
		public $elements;

		/**
		 * 
		 * @param AviaBuilder $builder
		 */
		public function __construct( $builder ) 
		{
			$this->builder = $builder;
			$this->elements = array();
			
			/**
			 * Needed to check and repair shortcode structure
			 * We define all defaults for content elements here, which are the most common elements.
			 * 
			 * first_in_row:		set a string that is added/removed from the element attributes if the element is the first/not the first in a line, e.g. first or foo='bar'
			 * layout_children:		shortcodes that must be included to have a valid element when we need to repair the content of a page. 
			 *						Nested shortcodes must be added, if they are part of the empty element. 
			 *						E.g. tab_section need tab_sub_sections but these are not nested !
			 * forced_load_objects	array of string: e.g. layerslider must be loaded right after init hook, but when we cannot know if we need it because an element is loaded dynamically
			 *						(e.g. postcontent) we can add a unique string to tell layerslider to load, when this shortcode is found in content
			 */
			$this->config = array(
							'type'					=>	'content',		//		'layout' | 'content'   needed in syntax error correction
							'self_closing'			=>	'',				//		'yes' | 'no'	if empty base class scans for id="content" in first level of $elements (a fallback for third party elements)
							'contains_text'			=>	'yes',			//		'yes' | 'no'	is plain text allowed in content area of shortcode
							'contains_layout'		=>	'no',			//		'yes' | 'no'	are layout elements allowed in content area of shortcode
							'contains_content'		=>	'no',			//		'yes' | 'no'	are content elements allowed in content area of shortcode
							'first_in_row'			=>	'',				//		'' | attribute to add/remove if first element in a layout line
							'auto_repair'			=>	'yes',			//		'yes' | 'no'	disable for nested parent element if structure of element complex (more than 1 subelement and nested again like av_table)
							'layout_children'		=>	array(),		
							'shortcode_nested'		=>	array(),
							'forced_load_objects'	=>	array()			//		"name" of external objects that must be included when we find this shortcode in content
						);
			
			
			$this->shortcode_insert_button();
			$this->extra_config();
			
			/**
			 * Add the unique ID field
			 */
			$this->elements[] = array(	
							"name" 	=> __("Unique ID for ALB element",'avia_framework' ),
							"desc" 	=> __("stores the unique ID for the element",'avia_framework' ),
							"id" 	=> aviaElementManager::ELEMENT_UID,
							"type" 	=> "hidden",
							"std" 	=> ""
						);
		
		}
		
		/**
		 * @since 4.2
		 */
		public function __destruct()
		{
			unset( $this->builder );
			unset( $this->config );
			unset( $this->elements );
		}

		//init function is executed in AviaBuilder::createShortcode if the shortcode is allowed
		public function init()
		{
			$this->create_asset_array();
			$this->actions_and_filters();
			
			if(is_admin() || AviaHelper::is_ajax())
			{
				$this->admin_assets();
			}
			
			$this->register_shortcodes(); 
			
			//set up loading of assets. wait until post id is known
			add_action('wp', array(&$this, 'extra_asset_check') , 10 );
		}


		/**
		*   shortcode_insert_button: creates the shortcode button for the backend canvas

		*	create the config array. eg:

		*	$this->config['name'] = __('Text', 'avia_framework' ); //defines the name of the button that is displayed below the icon

		*   $this->config['tab'] = __('Layout Elements', 'avia_framework' ); //tab that should hold the button

		*   $this->config['icon'] = $this->builder->imagesURL."full.png"; //icon for the button

		*   $this->config['shortcode'] = 'one_full'; //the shortcode name. this would be the [one_full] shortcode

		*   $this->config['tooltip'] = __('This is a tooltip', 'avia_framework' ); //the tooltip that appears when hovering above the shortcode icon

		*	$this->config['order'] = 40; //order of the button. higher numbers are displayed first

		*	$this->config['target'] = "avia-target-insert"; //if target mode is "avia-target-insert" item will not be added instantly when clicked. other option is avia-section-drop which allos dropping on sections

		*   $this->config['modal_data'] = array('modal_class' => 'mediumscreen'); // data that gets passed to the modal window. eg the class that controlls the modal size

		*	$this->config['modal_on_load'] = array("js", "functions"); //javascript function that should be executed once the modal window has opened

        *   $this->config['shortcode_nested'] = array('av_tab'); // nested shortcodes. needs to be defined if a modal group is used as popup element

		*	$this->config['tinyMCE'] = array('tiny_only'=>true,'instantInsert' => "[asdf]1[/asdf]", 'disable' => true); // show only in tiny mce / do an instant insert instead of modal / disable element in tinymce

		*	$this->config['invisible'] = true; // used to hide the element in builder tab. used for columns eg: 2/5, 3/5 etc

		*	$this->config['html_renderer'] 	= false; //function that renderes the backend editor element.
													 //if set to false no function is used and the output has to be passed by the
													 //"editor_element" function. if not set at all the default function
													 //"create_sortable_editor_element" is used

		* $this->config['drag-level'] = 2; // sets the drag level for an element. drag level must be higher than the drop level of the target, otherwise you cant drop the element onto the other

		* $this->config['drop-level'] = 2; // set the drop level for an element. set drop level to -1 if element shouldnt be dropable


		*/

		abstract function shortcode_insert_button();





		/**
		* holds the function that generates the html code for the frontend
		*/
		abstract function shortcode_handler($atts, $content = "", $shortcodename = "", $meta = "");





		/**
		* function that gets executed if the shortcode is allowed. allows shortcode to load extra assets like css or javascript files in the admin area
		*/

		public function admin_assets(){}
		
		
		/**
		* function that checks if an asset is disabled and if not loads all extra assets
		*/

		public function extra_asset_check()
		{
			//generic check if the assets for this element should be loaded
			if(!is_admin() && ( empty( $this->builder->disabled_assets[ $this->config['shortcode'] ]) || empty( $this->config['disabling_allowed'] ) ) )
			{
				$this->extra_assets();
			}
		}
		
		
		
		/**
		* function that gets executed if the shortcode is allowed. allows shortcode to load extra assets like css or javascript files
		*/

		public function extra_assets(){}


		/**
		 * Scans the $this->elements array recursivly and returns the first element where key = "$element_id"
		 * 
		 * @param string $element_id
		 * @param array $source
		 * @return array|false
		 * @since 4.1.3
		 */
		public function get_popup_element_by_id( $element_id, array &$source = null )
		{
			if( empty( $source ) )
			{
				$source = &$this->elements;
			}
			
			foreach( $source as &$element )
			{
				if( isset( $element['id'] ) && ( $element_id == $element['id'] ) )
				{
					return $element;
				}
				
				if( isset( $element['subelements'] ) && ! empty( $element['subelements'] ) )
				{
					$found = $this->get_popup_element_by_id( $element_id, $element['subelements'] );
					if( false !== $found )
					{
						return $found;
					}
				}
			}
			
			return false;
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
		public function editor_element($params)
		{
		    $params['innerHtml'] = "";
		    if(isset($this->config['icon']))
		    {
                $params['innerHtml'] .= "<img src='".$this->config['icon']."' title='".$this->config['name']."' alt='' />";
			}
			$params['innerHtml'].= "<div class='avia-element-label'>".$this->config['name']."</div>";
			return $params;
		}


		/**
		* function that creates the sets the elements for the popup editor. Not defined by this class but executed if the child class got it
		*/

		# function popup_elements(){}


		/**
		* function that creates the popup editor. only used in classes that have a config array defined by the set_elements class
		* a child class that has the function declared automaticaly gets an edit button in the admin section
		*/
		public function popup_editor($var)
		{

			if( empty( $this->elements ) )	{ die(); }
			
			if( ( 1 == count( $this->elements ) ) && isset( $this->elements[0]['id'] ) && ( aviaElementManager::ELEMENT_UID == $this->elements[0]['id'] ) )
			{
				die();
			}
			
			if(current_theme_supports('avia_template_builder_custom_css'))
			{
				$this->elements = $this->avia_custom_class_for_element($this->elements);
			}
			
			if( !empty($this->config['preview']) )
			{
				$this->elements = $this->avia_custom_preview_bg($this->elements);
			}
			
			$elements = apply_filters('avf_template_builder_shortcode_elements', $this->elements);

			//if the ajax request told us that we are fetching the subfunction iterate over the array elements and
			if(!empty($_POST['params']['subelement']))
			{
				foreach($elements as $element)
				{
					if(isset($element['subelements']))
					{
						$elements = $element['subelements'];
						break;
					}
				}
			}
			

			$elements = $this->set_default_values($elements);
			echo AviaHtmlHelper::render_multiple_elements($elements, $this);
			
			
			die();
		}

		/**
		* function that sets some internal variables and counters, then calls the actual shortcode handling function
		*/
		public function shortcode_handler_prepare($atts, $content = "", $shortcodename = "", $fake = false)
		{
			//dont use any shortcodes in backend
			$meta = array();

			if(empty($this->config['inline'])) //inline shortcodes like dropcaps are basically nested shortcodes and should therefore not be counted
			{
			    $meta    = array( 'el_class' => " avia-builder-el-".ShortcodeHelper::$shortcode_index." ",
			    				  'index' => ShortcodeHelper::$shortcode_index,
			                      'this'  => ShortcodeHelper::find_tree_item(ShortcodeHelper::$shortcode_index),

								'siblings'=>array(
			                          'next'  => ShortcodeHelper::find_tree_item(ShortcodeHelper::$shortcode_index, 1),
			                          'prev'  => ShortcodeHelper::find_tree_item(ShortcodeHelper::$shortcode_index, -1)
			                      )

			              );

				if(!empty($meta['siblings']['prev']['tag']))
				{
					$meta['el_class'] .= " el_after_".$meta['siblings']['prev']['tag']." ";
				}
				
				if(!empty($meta['siblings']['next']['tag']))
				{
					$meta['el_class'] .= " el_before_".$meta['siblings']['next']['tag']." ";
				}


				$fullwidth = AviaBuilder::$full_el;

				if(!empty($meta['this']['tag']) && !in_array( $meta['this']['tag'] , $fullwidth))
				{
					if(!empty( $meta['siblings']['next']['tag']) && in_array( $meta['siblings']['next']['tag'] , $fullwidth) )
					{
						$meta['siblings']['next'] = false;
					}

					if(!empty( $meta['siblings']['prev']['tag']) &&  in_array( $meta['siblings']['prev']['tag'] , $fullwidth) )
					{
						$meta['siblings']['prev'] = false;
					}
				}


				//single element without siblings

				if(empty($meta['siblings']['next']) && empty($meta['siblings']['prev']))
				{
					 $meta['el_class'] .= " avia-builder-el-no-sibling ";
				}
				else if(empty($meta['siblings']['next'])) //last element within section, column or page
				{
					$meta['el_class'] .= " avia-builder-el-last ";
				}
				else if(empty($meta['siblings']['prev'])) //first element within section, column or page
				{
					$meta['el_class'] .= " avia-builder-el-first ";
				}

				//if the shortcode was added without beeing a builder element (eg button within layerslider) reset all styles for that shortcode and make sure it is marked as a fake element
				if( empty($meta['this']['tag'] ) || $shortcodename != $meta['this']['tag'] || ShortcodeHelper::$is_direct_call || $fake )
				{
						//	increment theme shortcodes only, because these are in the shorcode tree
					if( in_array( $shortcodename, ShortcodeHelper::$allowed_shortcodes ) )
					{
						ShortcodeHelper::$direct_calls++;
					}
					
					$fake = true;
					$meta = array('el_class'=>'');
				}
				
	            //fake is set when we manually call one shortcode inside another
	            if(!$fake) ShortcodeHelper::$shortcode_index ++;
    		}
			
			if(isset($atts['custom_class'])) 
			{
				$meta['el_class'] .= " ". $atts['custom_class'];
				$meta['custom_class'] = $atts['custom_class'];
			}
			
			if(!isset($meta['custom_markup'])) $meta['custom_markup'] = "";
			
			
			$meta = apply_filters('avf_template_builder_shortcode_meta', $meta, $atts, $content, $shortcodename);
			
			
			//if the element is disabled do load a notice for admins but do not show the info for other visitors)
			
			if(empty( $this->builder->disabled_assets[ $this->config['shortcode'] ]) || empty( $this->config['disabling_allowed'] ) )
			{
				$content = $this->shortcode_handler($atts, $content, $shortcodename, $meta);
			}
			else if (current_user_can('edit_posts'))
			{
				$content = "";
				$default_msg = 	'<strong>'.__('Admin notice for:' )."</strong><br>".
								$this->config['name']."<br><br>".
								__('This element was disabled in your theme settings. You can activate it here:' )."<br>".
							   '<a target="_blank" href="'.admin_url('admin.php?page=avia#goto_performance').'">'.__("Performance Settings",'avia_framework' )."</a>";
				
				$msg 		= isset($this->config['shortcode_disabled_msg']) ? $this->config['shortcode_disabled_msg'] : $default_msg;
				$content 	= "<span class='av-shortcode-disabled-notice'>{$msg}</span>";
			}
			else
			{
				$content 	= "";
			}

            return $content;
		}



		/**
		* additional config vars that are set automatically
		*/
		protected function extra_config()
		{
			$this->config['php_class'] = get_class($this);

			if(empty($this->config['drag-level'])) $this->config['drag-level'] = 3;
			if(empty($this->config['drop-level'])) $this->config['drop-level'] = -1;


			//if we got elements for the popup editor activate it
			if(method_exists($this, 'popup_elements') && is_admin())
			{
				$this->popup_elements();

				if(!empty($this->elements))
				{
					$this->config['popup_editor'] = true;

					$this->extra_config_element_iterator($this->elements);

					if(!empty($this->config['modal_on_load']))
					{
						//remove any duplicate values
						$this->config['modal_on_load'] = array_unique($this->config['modal_on_load']);
					}
				}
			}

		}


		/**
		* register shortcode and if available nested shortcode
		*/
		protected function register_shortcodes()
		{
			if(isset($_REQUEST['params']['_ajax_nonce'])) $_REQUEST['_ajax_nonce'] = $_REQUEST['params']['_ajax_nonce'];
			
			//the check is only necessary when $_REQUEST['text'] is set which means we want to show a preview that could be manipulated from outside
			if(!is_admin() || empty($_REQUEST['text']) || ( !empty($_POST['avia_request']) && check_ajax_referer('avia_nonce_loader', '_ajax_nonce' ) ) )
			{
				add_shortcode( $this->config['shortcode'], array(&$this, 'shortcode_handler_prepare'));
				
				if(!empty($this->config['shortcode_nested']))
				{
					foreach($this->config['shortcode_nested'] as $nested)
					{
						if( method_exists($this, $nested) )
						{
							add_shortcode( $nested, array(&$this, $nested));
						}
						else if(!shortcode_exists($nested))
						{
							add_shortcode( $nested, '__return_false'); /*wordpress 4.0.1 fix that. without the shortcode registered to a function the attributes get messed up*/
						}
						
					}
				}
			}
		}



		/**
		* helper function to iterate recursively over element and subelement trees.
		*/
		protected function extra_config_element_iterator($elements)
		{
			//check for js functions that need to be executed on popup window load
			foreach($elements as $element)
			{
				switch($element['type'])
				{
					case "mailchimp_list":  $this->config['modal_on_load'][] = 'modal_load_mailchimp'; break;
					case "multi_input": 	$this->config['modal_on_load'][] = 'modal_load_multi_input'; break;
					case "tab_container": 	$this->config['modal_on_load'][] = 'modal_load_tabs'; break;
					case "tiny_mce": 		$this->config['modal_on_load'][] = 'modal_load_tiny_mce'; break;
					case "colorpicker": 	$this->config['modal_on_load'][] = 'modal_load_colorpicker'; break;
					case "datepicker": 		$this->config['modal_on_load'][] = 'modal_load_datepicker'; break;
					case "table": 			$this->config['modal_on_load'][] = 'modal_load_tablebuilder'; break;
					case "modal_group":
											$this->config['modal_on_load'][] = 'modal_start_sorting';
											$this->config['modal_on_load'][] = 'modal_tab_functions';
											$this->config['modal_on_load'][] = 'modal_hotspot_helper';
											$this->extra_config_element_iterator($element['subelements']);
					break;
				}
				
				if(!empty($element['modal_on_load'])) //manually load a script
				{
					$this->config['modal_on_load'][] = $element['modal_on_load'];
				}
			}
		}



		/**
		* filter and action hooks
		*/
		protected function actions_and_filters()
		{
			add_filter('avia_show_shortcode_button', array($this,'add_backend_button'));

			//ajax action for elements with modal window editor
			if(!empty($this->config['popup_editor']))
			{
				add_action('wp_ajax_avia_ajax_'.$this->config['shortcode'], array($this,'popup_editor'));

				if(!empty($this->config['shortcode_nested']))
				{
					foreach($this->config['shortcode_nested'] as $sc)
					{
						add_action('wp_ajax_avia_ajax_'.$sc, array($this,'popup_editor'));
					}
				}
			}


		}


		/**
		* function that checks the popup_elements configuration array of a shortcode and sets an array that tells the builder class which resources to load
		*/
		protected function create_asset_array()
		{
			if(!empty($this->elements))
			{
				foreach ($this->elements as $element)
				{
					if( $element['type'] == 'iconfont')
					{
						AviaBuilder::$resources_to_load['font'] = $element;
					}
				}
			}
		}

		/**
		* add buttons for the backend
		*/
		public function add_backend_button($buttons)
		{
			$buttons[] = $this->config;
			return $buttons;
		}


		/**
		* function that sets the default values and passes them to the user defined editor element
		* which in turn returns the array with the properties to render a new AviaBuilder Canvas Element
		*/
		public function prepare_editor_element($content = false, $args = array())
		{
			//set the default content unless it was already passed
			if($content === false)
			{
				$content = $this->get_default_content($content);
			}

			//set the default arguments unless they were already passed
			if(empty($args))
			{
				$args = $this->get_default_args($args);
			}

			if(isset($args['content'])) unset($args['content']);

			$params['content']   = $content;
			$params['args']      = $args;
			$params['data']      = isset($this->config['modal_data']) ? $this->config['modal_data'] : array();
			
		
			/**
			 * Fetch the parameter array from the child classes editor_element function which should describe the html code.
			 * Some elements can return a string value
			 */
			$params =  $this->editor_element($params);

			/**
			 * Since 4.2.1 we have $this->config['self_closing'] = 'yes'|'no'
			 * Now we can use this to remove any content here and do not need to do this in each element seperatly in $this->editor_element 
			 */
			if( is_array( $params ) && $this->is_self_closing() )
			{
				$params['content'] = null;
			}

			// pass the parameters to the create_sortable_editor_element unless a different function for execution was set.
			// if the function is set to "false" we asume that the output is final
			if(!isset($this->config['html_renderer']))
			{
				$this->config['html_renderer'] = "create_sortable_editor_element";
			}

			if($this->config['html_renderer'] != false)
			{
				$output = call_user_func(array($this, $this->config['html_renderer']) , $params );
			}
			else
			{
				$output = $params;
			}

			return $output;
		}
		
		/**
		* add a custom css class to each element
		*/
		public function avia_custom_class_for_element($elements)
		{
			$elements[] = array(	
				"name" 	=> __("Custom Css Class",'avia_framework' ),
				"desc" 	=> __("Add a custom css class for the element here. Make sure to only use allowed characters (latin characters, underscores, dashes and numbers)",'avia_framework' ),
				"id" 	=> "custom_class",
				"type" 	=> "input",
				"std" 	=> "");
		
			return $elements;
		}
		
		/**
		* add a custom field for the background of the preview
		*/
		public function avia_custom_preview_bg($elements)
		{
			$elements[] = array(	
				"id" 	=> "admin_preview_bg",
				"type" 	=> "hidden",
				"std" 	=> "");
		
			return $elements;
		}


			



		/**
		* default code to create a sortable item for your editor
		*/
		public function create_sortable_editor_element($params)
		{

			$extraClass = "";
			$defaults = array('class'=>'avia_default_container', 'innerHtml'=>'');
			$params = array_merge($defaults, $params);
			extract($params);
			
			if(empty($data)) $data = array();
			
			$data['shortcodehandler'] 	= $this->config['shortcode'];
			$data['modal_title'] 		= $this->config['name'];
			$data['modal_ajax_hook'] 	= $this->config['shortcode'];
			$data['dragdrop-level']		= $this->config['drag-level'];
			$data['allowed-shortcodes'] = $this->config['shortcode'];
            $data['preview'] 			= ! empty( $this->config['preview'] ) ? $this->config['preview'] : 0;
            $data['preview_scale'] 		= ! empty( $this->config['preview_scale'] ) ? $this->config['preview_scale'] : 'noscale';
			$data['closing_tag']		= $this->is_self_closing() ? 'no' : 'yes';
			
			if(isset($this->config['shortcode_nested']))
			{
				$data['allowed-shortcodes']	= $this->config['shortcode_nested'];
				$data['allowed-shortcodes'][] = $this->config['shortcode'];
				$data['allowed-shortcodes'] = implode(",",$data['allowed-shortcodes']);
			}

			if(!empty($this->config['modal_on_load']))
			{
				$data['modal_on_load'] 	= $this->config['modal_on_load'];
			}

			$data		 = apply_filters('avb_backend_editor_element_data_filter', $data);
			$dataString  = AviaHelper::create_data_string($data);

			$output  = "<div class='avia_sortable_element avia_pop_class ".$class." ".$this->config['shortcode']." av_drag' ".$dataString.">";
			$output .= "<div class='avia_sorthandle menu-item-handle'>";

			if(!empty($this->config['popup_editor']))
			{
				$extraClass = 'avia-edit-element';
				$output .= "<a class='$extraClass'  href='#edit-element' title='".__('Edit Element','avia_framework' )."'>edit</a>";
			}

			$output .= "<a class='avia-save-element'  href='#save-element' title='".__('Save Element as Template','avia_framework' )."'>+</a>";
			$output .= "<a class='avia-delete'  href='#delete' title='".__('Delete Element','avia_framework' )."'>x</a>";
			$output .= "<a class='avia-clone'  href='#clone' title='".__('Clone Element','avia_framework' )."' >".__('Clone Element','avia_framework' )."</a></div>";

			$output .= "<div class='avia_inner_shortcode $extraClass'>";
			$output .= $innerHtml;
			
			
			
			$output .= "<textarea data-name='text-shortcode' cols='20' rows='4'>".ShortcodeHelper::create_shortcode_by_array($this->config['shortcode'], $content, $args)."</textarea>";
			$output .= "</div></div>";

			return $output;
		}



		/**
		 * helper function executed by aviaShortcodeTemplate::popup_editor that extracts the attributes from the shortcode and then merges the values into the options array
		 *
		 * @param array $elements
		 * @return array $elements
		 */
		public function set_default_values($elements)
		{
			$shortcode = !empty($_POST['params']['shortcode']) ? $_POST['params']['shortcode'] : "";
			

			if($shortcode)
			{
				//will extract the shortcode into $_POST['extracted_shortcode']
				$this->builder->text_to_interface($shortcode);
				
				//the main shortcode (which is always the last array item) will be stored in $extracted_shortcode
				$extracted_shortcode = end($_POST['extracted_shortcode']);

				//if the $_POST['extracted_shortcode'] has more than one items we are dealing with nested shortcodes
				$multi_content = count($_POST['extracted_shortcode']);

				//proceed if the main shortcode has either arguments or content
				if(!empty($extracted_shortcode['attr']) || !empty($extracted_shortcode['content']))
				{
					if(empty($extracted_shortcode['attr'])) $extracted_shortcode['attr'] = array();
					if(isset($extracted_shortcode['content'])) $extracted_shortcode['attr']['content'] = $extracted_shortcode['content'];

					//iterate over each array item and check if we already got a value
					foreach($elements as &$element)
					{
						if(isset($element['id']) && isset($extracted_shortcode['attr'][$element['id']]))
						{
							//make sure that each element of the popup can access the other values of the shortcode. necessary for hidden elements
							$element['shortcode_data'] = $extracted_shortcode['attr'];
						
							//if the item has subelements the std value has to be an array
							if(isset($element['subelements']))
							{
								$element['std'] = array();

								for ($i = 0; $i < $multi_content - 1; $i++)
								{
									$element['std'][$i] = $_POST['extracted_shortcode'][$i]['attr'];
									$element['std'][$i]['content'] = $_POST['extracted_shortcode'][$i]['content'];
								}
							}
							else
							{
								$element['std'] = stripslashes($extracted_shortcode['attr'][$element['id']]);
							}



						}
						else
						{
							if($element['type'] == "checkbox") $element['std'] = "";
						}
					}
				}
			}

			return $elements;
		}


		/**
		 * helper function executed that extracts the std values from the options array and creates a shortcode argument array
		 *
		 * @param array $args
		 * @return array $args
		 */
		public function get_default_args($args = array())
		{
			/**
			 * PHP 7.0 fix: ensure we have an array, otherwise we recieve notices
			 * if shortcode is used without params (e.g. for a fallback situation) we get an empty string and not an array
			 */
			if( ! is_array( $args) )
			{
				$args = array();
			}
			
			if(!empty($this->elements))
			{
				foreach($this->elements as $element)
				{
					if(isset($element['std']) && isset($element['id']))
					{
						$args[$element['id']] = $element['std'];
					}
				}

				$this->default_args = $args;
			}
			return $args;
		}

		/**
		 * helper function that gets the default value of the content element
		 *
		 * @param array $content
		 * @return array $args
		 */
		public function get_default_content($content = "")
		{
			if(!empty($this->elements))
			{
				//if we didnt iterate over the arguments array yet do it now
				if(empty($this->default_args))
				{
					$this->get_default_args();
				}

				//if there is a content element already thats the value. if not try to fetch the std value
				if(!isset($this->default_args['content']))
				{
					foreach($this->elements as $element)
					{
						if(isset($element['std']) && isset($element['id']) && $element['id'] == "content")
						{
							$content = $element['std'];
						}
					}
				}
				else
				{
					$content = $this->default_args['content'];
				}
			}

			//if the content is an array we got a nested shortcode
			if(is_array($content))
			{
				$string_content = "";

				foreach($content as $c)
				{
					$content = $this->is_nested_self_closing( $this->config['shortcode_nested'][0] ) ? null : '';
					$string_content .= trim( ShortcodeHelper::create_shortcode_by_array( $this->config['shortcode_nested'][0], $content, $c ) )."\n";
				}

				$content =  $string_content;
			}


			return $content;
		}
		
		/**
		 * Returns the width of the element.
		 * Override in derived class if the element does not have fullwidth (currently only supported for layout elements avia_sc_columns and avia_sc_cell.
		 * All other elements are full screen.
		 * 
		 * @since 4.2.1
		 * @return float
		 */
		public function get_element_width()
		{
			return 1.0;
		}
		
		
		/**
		 * Returns if an element needs a closing tag or is self closing.
		 * The implementation is for backwards compatibility and also for third party elements.
		 *  
		 * @since 4.2.1
		 * @return boolean
		 */
		public function is_self_closing()
		{
			/**
			 * If property is set return this value
			 */
			if( ! empty( $this->config['self_closing'] ) && in_array( $this->config['self_closing'], array( 'yes', 'no' ) ) )
			{
				return $this->config['self_closing'] == 'yes';
			}
			
			/**
			 * Elements should return NULL for content when self closing
			 */
			$params = $this->editor_element( array() );
			$this->config['self_closing'] =  array_key_exists( 'content',  $params ) && is_null( $params['content'] )  ? 'yes' : 'no';
			
			return $this->config['self_closing'] == 'yes';
		}
		
		/**
		 * Returns false by default.
		 * Override in a child class if you need to change this behaviour.
		 * 
		 * @since 4.2.1
		 * @param string $shortcode
		 * @return boolean
		 */
		public function is_nested_self_closing( $shortcode )
		{
			return false;
		}

		/**
		 * helper function for the editor_element function that creates the correct classnames
		 * and data attributes for an AviaBuilder Canvas element in your backend
		 *
		 * @param string $classNames a string with classnames separated by coma
		 * @param array $args
		 * @return string
		 */
		public function class_by_arguments($classNames, $args, $classNamesOnly = false)
		{
			$classNames = str_replace(" ","",$classNames);
			$dataString = "data-update_class_with='$classNames' ";
			$classNames = explode(',',$classNames);
			$classString = "class='";
			$classes = "";

			foreach($classNames as $class)
			{
				$replace  = is_array($args) ? $args[$class] : $args;
				$classes .= "avia-$class-".str_replace(" ","_",$replace)." ";
			}

			if($classNamesOnly) return $classes;
			return $classString .$classes."' ".$dataString;
		}



		/**
		 * helper function for the editor_element function that tells the javascript were to insert the returned content
		 * you need to provide a "key" and a template
		 *
		 * @param string $key a string with argument or content key eg: img_src
		 * @param string $template a template that tells which content to insert. eg: <img src='{{img_src}}' />
		 * @return string
		 */

		function update_template($key, $template)
		{
			$data = "data-update_with='$key' data-update_template='".htmlentities($template, ENT_QUOTES, get_bloginfo( 'charset' ))."'";
			return $data;
		}





	} // end class

} // end if !class_exists
