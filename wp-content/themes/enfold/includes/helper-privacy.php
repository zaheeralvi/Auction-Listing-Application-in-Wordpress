<?php
	
	/**
	* File was added with version 4.4 and holds functions that are related to the user privacy
	*
	* @since 4.4
	* @added_by Kriesi
	*
	* Shortcodes used: 
	* [av_privacy_link page_id="your custom page id"]your custom page title[/av_privacy_link] -> returns a link to the wordpress privacy policy page (requires WP 4.9.6) or a custom page
	* [av_privacy_google_tracking] -> to disable google tracking
	* [av_privacy_google_webfonts] -> to disable google webfonts
	* [av_privacy_google_maps] -> to disable google maps
	* [av_privacy_video_embeds] -> to disable video embeds
	*
	*/



if(!class_exists('av_privacy_class'))
{
	class av_privacy_class
	{
		/**
		 * Holds the instance of this class
		 * 
		 * @since 4.4.1
		 * @var av_privacy_class 
		 */
		static private $_instance = null;
		
		/**
		 *
		 * @since 4.4.1
		 * @var string 
		 */
		static protected $default_privacy_message = '';
		
		
		/**
		 * @since 4.4
		 * @var array 
		 */
		protected $toggles;
		
		
		/**
		 * Return the instance of this class
		 * 
		 * @since 4.4.1
		 * @return AviaBuilder
		 */
		static public function instance()
		{
			if( is_null( av_privacy_class::$_instance ) )
			{
				av_privacy_class::$_instance = new av_privacy_class();
			}
			
			return av_privacy_class::$_instance;
		}
		
		
		/**
		 * @since 4.4
		 */
		protected function __construct()
		{
			$this->toggles = array();
			
			//shortcode related stuff
			add_shortcode( 'av_privacy_link', array($this, 'av_privacy_policy_link') );
			add_shortcode( 'av_privacy_google_tracking', array($this, 'av_privacy_disable_google_tracking') );
			add_shortcode( 'av_privacy_google_webfonts', array($this, 'av_privacy_disable_google_webfonts') );
			add_shortcode( 'av_privacy_google_maps', array($this, 'av_privacy_disable_google_maps') );
			add_shortcode( 'av_privacy_video_embeds', array($this, 'av_privacy_disable_video_embeds') );
			
			add_action('wp_footer', array($this, 'footer_script') , 1000);
			
		
			//hook into commentform if enabled in backend
			if(avia_get_option('privacy_message_commentform_active') == "privacy_message_commentform_active")
			{
				add_filter( 'comment_form_fields', array($this, 'av_privacy_comment_checkbox')  );
				add_filter( 'preprocess_comment', array($this, 'av_privacy_verify_comment_checkbox')  );
			}
			
			//hook into contactform if enabled in backend
			if(avia_get_option('privacy_message_contactform_active') == "privacy_message_contactform_active")
			{
				add_filter( 'avf_sc_contact_form_elements', array($this, 'av_privacy_contactform_checkbox'), 10, 2  );
			}
			
			//hook into mailchimpform if enabled in backend
			if(avia_get_option('privacy_message_mailchimp_active') == "privacy_message_mailchimp_active")
			{
				add_filter( 'avf_sc_mailchimp_form_elements', array($this, 'av_privacy_mailchimp_checkbox') , 10 , 2 );
			}
			
			//hook into login/registration forms if enabled in backend
			if(avia_get_option('privacy_message_login_active') == "privacy_message_login_active")
			{
				add_action( 'login_form', array($this, 'av_privacy_login_extra') , 10 );
				add_filter( 'wp_authenticate_user', array($this,'av_authenticate_user_acc'), 99999, 2 );
			}
			
			//hook into registration forms if enabled in backend
			if( avia_get_option('privacy_message_registration_active') == "privacy_message_registration_active" )
			{
				add_action( 'register_form', array( $this, 'av_privacy_register_extra') , 10 );
				add_filter( 'registration_errors', array( $this,'av_registration_errors'), 99999, 3 );
			}
			
		}
		
		/**
		 * 
		 * @since 4.4.1
		 */
		public function __destruct() 
		{
			unset( $this->toggles );
		}
		
		/**
		 * Returns a filterable default privacy message for checkboxes
		 * 
		 * @since 4.4.1
		 * @added_by Günter
		 * @return string
		 */
		static public function get_default_privacy_message()
		{
			if( empty( av_privacy_class::$default_privacy_message ) )
			{
				av_privacy_class::$default_privacy_message = apply_filters( 'avf_default_privacy_message',	__( "I agree to the terms and conditions laid out in the [av_privacy_link]Privacy Policy[/av_privacy_link]", 'avia_framework' ) );
			}
			
			return av_privacy_class::$default_privacy_message;
		}


		/**
		 * Toggle that allows to set/unset a cookie that can then be used for privacy options
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		function av_privacy_toggle( $cookie , $content )
		{	
			$output = "";
	
			$this->toggles[$cookie] = true;
			
			$output .= '<div class="av-switch-'.$cookie.' av-toggle-switch">';
			$output .= '<label>';
			$output .= '<input type="checkbox" checked id="'.$cookie.'" class="'.$cookie.' " name="'.$cookie.'">';
			$output .= '<span class="toggle-track"></span>';
			$output .= '<span class="toggle-label-content">'.$content."</span>";
			$output .= '</label>';
			$output .= '</div>';
			
			return $output;
			
		}
		
		/**
		 * Shortcode that allows to disable google analytics tracking
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		function av_privacy_disable_google_tracking( $atts = array() , $content = "")
		{	
			$content = !empty($content) ?  $content : __('Click to enable/disable google analytics tracking.', 'avia_framework');
			$cookie  = "aviaPrivacyGoogleTrackingDisabled";
			
			return $this->av_privacy_toggle( $cookie , $content );
		}
		
		
		/**
		 * Shortcode that allows to disable google webfonts loading
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		function av_privacy_disable_google_webfonts( $atts = array() , $content = "")
		{	
			$content = !empty($content) ?  $content : __('Click to enable/disable google webfonts.', 'avia_framework');
			$cookie  = "aviaPrivacyGoogleWebfontsDisabled";
			
			return $this->av_privacy_toggle( $cookie , $content );
		}
		
		/**
		 * Shortcode that allows to disable google maps loading
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		function av_privacy_disable_google_maps( $atts = array() , $content = "")
		{	
			$content = !empty($content) ?  $content : __('Click to enable/disable google maps.', 'avia_framework');
			$cookie  = "aviaPrivacyGoogleMapsDisabled";
			
			return $this->av_privacy_toggle( $cookie , $content );
		}
		
		
		/**
		 * Shortcode that allows to disable video embeds
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		function av_privacy_disable_video_embeds( $atts = array() , $content = "")
		{	
			$content = !empty($content) ?  $content : __('Click to enable/disable video embeds.', 'avia_framework');
			$cookie  = "aviaPrivacyVideoEmbedsDisabled";
			
			return $this->av_privacy_toggle( $cookie , $content );
		}
		
		/**
		 * Shortcode for a link to the privacy policy page. Requires wp 4.9.6.
		 * 
		 * @since 4.4
		 * @since 4.4.1				custom page id added
		 * @added_by Kriesi
		 * @return string
		 */
		function av_privacy_policy_link( $atts = array() , $content = "", $shortcodename = "" )
		{	
			$atts = shortcode_atts(	array(		
							'page_id'	=> ''
						), $atts, $shortcodename );
			
			$url = false;
			
			if( ! empty( $atts['page_id'] ) )
			{
				$url = get_permalink( $atts['page_id'] );
			}
			
			if( false === $url )
			{
				$page_id = get_option( 'wp_page_for_privacy_policy' );
				$url	 = get_permalink( $page_id );
			}
			
			if( false === $url )
			{
				$link = $content;
			}
			else
			{
				$content = ! empty( $content ) ? $content : get_the_title( $page_id );
				$link	 = "<a href='{$url}' target='_blank'>{$content}</a>";
			}
			
			return $link;
		}

		/**
		 * Javascript that gets appended to pages that got a privacy shortcode toggle
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		
		function footer_script()
		{
			if(empty($this->toggles)) return;
			
			$output  = "";
			$output .= "
			<script>
			
				function av_privacy_cookie_setter( cookie_name ) {
					
					var toggle = jQuery('.' + cookie_name);
					toggle.each(function()
					{
						if(document.cookie.match(cookie_name)) this.checked = false;
					});
					
					jQuery('.' + 'av-switch-' + cookie_name).each(function()
					{
						this.className += ' active ';
					});
					
					toggle.on('click', function()
					{
						if(this.checked)
						{
							document.cookie = cookie_name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
						}
						else
						{
							var theDate = new Date();
							var oneYearLater = new Date( theDate.getTime() + 31536000000 );
							document.cookie = cookie_name + '=true; Path=/; Expires='+oneYearLater.toGMTString()+';';
						}
					});
				};
			";
			
			foreach($this->toggles as $toggles => $val)
			{
				$output .= " av_privacy_cookie_setter('{$toggles}'); ";
			}
			
			$output .= "</script>";
			
			
			$output = preg_replace('/\r|\n|\t/', '', $output);
			echo $output;
		}
		
		
		/**
		 * Appends a checkbox to the comment form that needs to be checked in order to comment
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @param array $comment_field
		 * @return array
		 */
		function av_privacy_comment_checkbox( $comment_field = array() )
		{
			$args = array(
							'id'			=> 'comment-form-av-privatepolicy',
							'content'		=> avia_get_option( 'privacy_message' ),
							'extra_class'	=> ''
					);
			
			$comment_field['comment-form-av-privatepolicy'] = $this->privacy_checkbox_field( $args );
			
			return $comment_field ;
		}
		
		/**
		 * Creates the checkbox html 
		 * 
		 * To be able to support 3rd party plugins with custom login forms that do not use standard WP hooks we
		 * add hidden field fake-comment-form-av-privatepolicy we can check if our form was included at all. 
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @param array $args 
		 * @return string
		 */
		public function privacy_checkbox_field( array $args = array() )
		{
			$args = wp_parse_args( $args, array(
							'id'			=> '',
							'content'		=> '',			//	shortcode are executed in this function
							'extra_class'	=> '',
							'attributes'	=> ''
					) );
			
			extract( $args );
			
			if( empty( $id ) )
			{
				return '';
			}
			
			if( empty( $content ) ) 
			{
				$content = av_privacy_class::get_default_privacy_message();
			}
			
			$content = do_shortcode( $content );
			
			$output = '<p class="form-av-privatepolicy ' . $id . ' ' . $extra_class . '" style="margin: 10px 0;">
						<input id="' . $id . '" name="' . $id . '" type="checkbox" value="yes" ' . $attributes . '>
						<label for="' . $id . '">' . $content . '</label>
						<input type="hidden" name="fake-' . $id . '" value="fake-val">
					  </p>';
			
			return $output ;
		}
		
		/**
		 * Checks if the user accepted the privacy policy. If not tell him that he has to if he wants to comment
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		function av_privacy_verify_comment_checkbox( $commentdata ) 
		{
		    if ( ! is_user_logged_in() && isset( $_POST['fake-comment-form-av-privatepolicy'] ) && ! isset( $_POST['comment-form-av-privatepolicy'] ) )
		    {
			    $error_message = apply_filters( 'avf_privacy_comment_checkbox_error_message', __( 'Error: You must agree to our privacy policy to comment on this site...' , 'avia_framework' ) );
			    wp_die( $error_message );
		    }
		
		    return $commentdata;
		}
		
		/**
		 * Adds a checkbox field to contact forms
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		function av_privacy_contactform_checkbox( $fields , $atts )
		{
			$content = avia_get_option('privacy_message_contact');
			if( empty( $content ) )
			{
				$content = av_privacy_class::get_default_privacy_message();
			}
      		$content = do_shortcode( $content );
      		
			$fields['av_privacy_agreement'] = array(
				'label' 	=> $content,
				'type' 		=> 'checkbox',
				'options' 	=> '',
				'check' 	=> 'is_empty',
				'width' 	=> '',
				'av_uid' 	=> '',
				'class'		=> 'av_form_privacy_check av_contact_privacy_check',
			);
			
			return $fields ;
		}

		/**
		 * Adds a checkbox field to mailchimp forms. bit more complicated than appending since we need to add the checkbox before the button
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		function av_privacy_mailchimp_checkbox( $fields , $atts )
		{
			$keys = array_keys($fields);
			foreach($keys as $pos => $key)
			{
				if(strpos($key, 'av-button') === 0) break;
			}
			
			$content = avia_get_option('privacy_message_mailchimp');
			if( empty( $content ) )
			{
				$content = av_privacy_class::get_default_privacy_message();
			}
      		$content = do_shortcode( $content );
			
			$new_fields['av_privacy_agreement'] = array(
				'label' 	=> $content,
				'type' 		=> 'checkbox',
				'options' 	=> '',
				'check' 	=> 'is_empty',
				'width' 	=> '',
				'av_uid' 	=> '',
				'class'		=> 'av_form_privacy_check av_mailchimp_privacy_check',
			);
			
			$fields = array_merge(
	            array_slice($fields, 0, $pos),
	            $new_fields,
	            array_slice($fields, $pos)
	        );
			
			return $fields ;
		}

		
		/**
		 * Adds a checkbox field to the registration form
		 * 
		 * @since 4.4.1
		 * @added_by Günter
		 */
		function av_privacy_register_extra()
		{
			$args = array(
							'id'			=> 'registration-form-av-privatepolicy',
							'content'		=> avia_get_option( 'privacy_message_registration' ),
							'extra_class'	=> ''
					);
			
			
			echo $this->privacy_checkbox_field( $args );
		}
		
		/**
		 * Adds a checkbox field to the login form
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 */
		function av_privacy_login_extra()
		{
			$args = array(
							'id'			=> 'login-form-av-privatepolicy',
							'content'		=> avia_get_option( 'privacy_message_login' ),
							'extra_class'	=> 'forgetmenot'
					);
			
			
			echo $this->privacy_checkbox_field( $args );
		}
		
		/**
		 * Authenticate the extra checkbox in the user login screen
		 * 
		 * @since 4.4
		 * @added_by Kriesi
		 * @return string
		 */
		function av_authenticate_user_acc( $user, $password )
		{
			//	Check if our checkbox was displayed at all
			if ( ! isset(  $_REQUEST['fake-login-form-av-privatepolicy'] ) )
			{
				return $user;
			}
					
			// See if the checkbox #login_accept was checked
		    if( isset( $_REQUEST['login-form-av-privatepolicy'] ) ) 
			{
		        // Checkbox on, allow login
		        return $user;
		    } 
			else 
			{
		        // Did NOT check the box, do not allow login
				$error_message = apply_filters( 'avf_privacy_login_checkbox_error_message', __( 'You must acknowledge and agree to the privacy policy' , 'avia_framework' ) );
				
		        $error = new WP_Error();
		        $error->add('did_not_accept', $error_message );
		        return $error;
		    }
		}
		
		/**
		 * Authenticate the extra checkbox in the registration login screen
		 * 
		 * @since 4.4.1
		 * @added_by Günter
		 * @param WP_Error $errors
		 * @param string $sanitized_user_login
		 * @param string $user_email
		 * @return \WP_Error
		 */
		public function av_registration_errors( WP_Error $errors, $sanitized_user_login, $user_email )
		{
			//	Check if our checkbox was displayed at all
			if ( ! isset(  $_REQUEST['fake-registration-form-av-privatepolicy'] ) )
			{
				return $errors;
			}
			
			// See if the checkbox #login_accept was checked
		    if ( isset( $_REQUEST['registration-form-av-privatepolicy'] ) ) 
			{
		        // Checkbox on, allow login
		        return $errors;
		    } 
			else 
			{
		        // Did NOT check the box, do not allow login
				$error_message = apply_filters( 'avf_privacy_registration_checkbox_error_message', __( 'You must acknowledge and agree to the privacy policy to register' , 'avia_framework' ) );
				
		        $error = new WP_Error();
		        $error->add('did_not_accept', $error_message );
		        return $error;
		    }
			
			return $errors;
		}



		
	}
}




/**
 * Returns the single instance of class av_privacy_helper - avoids the use of globals
 * 
 * @return av_privacy_class
 */
function av_privacy_helper()
{
	return av_privacy_class::instance();
}

add_action('init', 'av_privacy_helper', 20);
