<?php
if ( !class_exists('GP_Sajak') ):

	class GP_Sajak
	{
		var $_tabs = array();
		
		function __construct( $options = array() )
		{
			$this->set_options($options);
			add_action( 'admin_enqueue_scripts', array($this, 'setup_scripts') );
		}
		
		function set_options($options)
		{
			$defaults = array(
				'header_label' => 'Settings',
				'settings_field_key' => '',
				'extra_buttons_header' => array(),
				'extra_buttons_footer' => array(),
				'show_save_button' => true
			);
			$this->options = array_merge($defaults, $options);
		}
		
		function setup_scripts()
		{
			wp_register_script(
				'gp_sajak',
				plugins_url('assets/js/gp_sajak.js', __FILE__),
				array( 'jquery', 'jquery-ui-tabs' ),
				false,
				true
			);
			wp_enqueue_script('gp_sajak');
			
			$css_url = plugins_url( 'assets/css/gp_sajak.css' , __FILE__ );
			wp_register_style('gp-sajak', $css_url);
			wp_enqueue_style('gp-sajak');			

			$css_url = plugins_url( 'assets/font-awesome/css/font-awesome.min.css' , __FILE__ );
			wp_register_style('gp-sajak-font-awesome', $css_url);
			wp_enqueue_style('gp-sajak-font-awesome');
		}
		
		function add_tab($id, $label, $callback = false, $options = array())
		{
			$this->_tabs[] = compact('id', 'label', 'options', 'callback');
		}

		function display()
		{
			echo $this->header();
			echo $this->sidebar();
			echo '<div class="gp_sajak_main">';
			foreach($this->_tabs as $tab)			
			{
				$tab_content = '';
				if ( is_callable($tab['callback']) ) 
				{
					ob_start();
					call_user_func($tab['callback']);
					$tab_content = ob_get_contents();
					ob_end_clean();							   					
				}
				$add_class = !empty($tab['options']) && !empty($tab['options']['class'])
							 ? $tab['options']['class']
							 : '';

				$show_save_button = !empty($tab['options']) && isset($tab['options']['show_save_button'])
									? $tab['options']['show_save_button']
									: true;
									
				$show_save_attr = !$show_save_button 
								  ? 'data-show-save-button="0"'
								  : '';

				$tmpl = '<div id="tab-%s" class="gp_sajak_tab %s" %s>
					<div class="gp_sajak_tab_body">%s</div>
				</div>';
				printf($tmpl, $tab['id'], $add_class, $show_save_attr, $tab_content);
			}
			echo '</div>';
			echo $this->footer();
		}

		function header()
		{
			// WordPress attaches admin notices below the first <h2> it finds.
			// Our <h2> is inside our layout, so we need to give it a blank <h2>
			// that it can target instead. Else the flash messages will end up
			// inside the Sajak layout!			
			echo '<h2 style="display:none"></h2>';
			
			echo '<form method="post" action="options.php" enctype="multipart/form-data">';

			// This prints out all hidden setting fields
			if ( !empty($this->options['settings_field_key']) ) {
				$field_keys = is_array($this->options['settings_field_key'])
							  ? $this->options['settings_field_key']
							  : array( $this->options['settings_field_key'] );
				foreach ($field_keys as $field_key)
				{
					settings_fields( $field_key );
				}
			}

			echo '<div class="gp_sajak">';// closed in footer()
			echo '<div class="gp_sajak_header">';

			echo '<div class="gp_sajak_buttons">';

			if ( !empty($this->options['extra_buttons_header']) ) {
				$this->output_extra_buttons( $this->options['extra_buttons_header'] );
			}
			
			if ( $this->options['show_save_button'] ) {
				echo '<div class="gp_sajak_save_button">';
				submit_button();
				echo '</div>';
			}

			echo '</div>'; // end gp_sajak_buttons

			if ( !empty($this->options['header_label']) ) {
				printf( '<h2>%s</h2>', $this->options['header_label'] );
			}
			
			echo '</div>';
			echo '<div class="gp_sajak_body">';
		}

		function sidebar()
		{
			$out = '';
			$out .= '<div class="gp_sajak_sidebar">';
				$out .= '<ul class="gp_sajak_menu">';
				foreach($this->_tabs as $tab)			
				{
					$add_class = !empty($tab['options']) && !empty($tab['options']['class'])
								 ? $tab['options']['class']
								 : '';			

					// add font-awesome icon class if one is specified
					$icon = '';
					if ( !empty($tab['options']) && !empty($tab['options']['icon']) ) {
						$icon = sprintf('<span class="fa fa-%s"></span>', $tab['options']['icon']);
					}
					
					$tmpl = '<li id="gp_sajak_menu_label-%s" class="gp_sajak_menu_label %s">
								<a href="#tab-%s">%s<span class="label_text">%s</span></a>
							</li>';
					$out .= sprintf($tmpl, $tab['id'], $add_class, $tab['id'], $icon, $tab['label']);
				}
				$out .= '</ul>';
			$out .= '</div>';
			return $out;
		}

		function footer()
		{
			echo '</div><!-- end .gp_sajak_body -->'; // opened in header()
			echo '<div class="gp_sajak_footer">';

			echo '<div class="gp_sajak_buttons">';

			if ( !empty($this->options['extra_buttons_footer']) ) {
				$this->output_extra_buttons( $this->options['extra_buttons_footer'] );
			}
			
			if ( $this->options['show_save_button'] ) {
				echo '<div class="gp_sajak_save_button">';
				submit_button();
				echo '</div>';
			}

			echo '</div>'; // end gp_sajak_buttons

			echo '</div>';
			echo '</div><!-- end .gp_sajak -->'; // opened in header()

			echo '</form>';
		}
		
		function output_extra_buttons($buttons)
		{
			$button_defaults = array(
				'class' => '',
				'label' => '',
				'url' => '',
				'target' => '_blank'
			);

			foreach($buttons as $btn)
			{
				$btn = array_merge($button_defaults, $btn);
				echo '<div class="gp_sajak_button">';
				printf('<a class="%s button" href="%s" target="%s">%s</a>',
					$btn['class'],
					$btn['url'],
					$btn['target'],
					$btn['label']
				);
				echo '</div>';
			}
			
		}
		
	}
	
endif;//class_exists