<?php

class CBP_ExpirationDate
{
	var $root;
	var $base_file;
	var $post_type = 'banner';
	
	function __construct($root, $base_file)
	{
		$this->root = $root;
		$this->base_file = $base_file;
		$this->add_stylesheets_and_scripts();
		$this->add_hooks();
	}
	
	function add_stylesheets_and_scripts()
	{				
		$cssUrl = plugins_url( 'assets/css/custom-banners-admin-ui.css' , $this->base_file );
		$this->root->add_admin_stylesheet('custom-banners-admin-ui',  $cssUrl);
	}
	
	function add_hooks()
	{
		global $post;		
		add_action( 'admin_enqueue_scripts', array($this, 'localize_scripts') );
		add_action( 'post_submitbox_misc_actions', array($this, 'add_expiration_date') );
		add_action( 'save_post', array($this, 'update_expiration_time') );		
	}
	
	
	function localize_scripts($hook)
	{
		global $post;
		// only run on our own settings pages or add/edit banner screens
		if ( (strpos($hook, 'custom_banners') !== false) 
			 || (strpos($hook, 'custom-banners') !== false) 
			 || ( (strpos($hook, 'post.php') !== false) && !empty($post->post_type) && $post->post_type == 'banner' )		 
			 || ( (strpos($hook, 'post-new.php') !== false) && !empty($post->post_type) && $post->post_type == 'banner' )		 
		 ) {
				
			// include admin UI scripts
			$jsUrl = plugins_url( 'assets/js/custom-banners-admin-ui.js' , $this->base_file );
			wp_register_script( 'custom-banners-admin-ui', $jsUrl, array('jquery'), false, true );
			wp_enqueue_script ( 'custom-banners-admin-ui' );				
			wp_localize_script( 'custom-banners-admin-ui', 'cbp_expires_L10n', array(
				'expires' => __('Expires:')
			) );
		 }
	}	
	
	function add_expiration_date()
	{
		global $post;
		if ($post->post_type !== $this->post_type) {
			return;
		}

		$cur_time = get_post_meta($post->ID, '_cbp_expiration_timestamp', true);		
		$datef = __( 'M j, Y @ H:i' );
		if ( !empty($cur_time) ) {
			// TODO: change the wording of $stamp to "Expired on" if date is in the past
			$stamp = __('Expires: <b>%1$s</b>');
			$date = date_i18n( $datef, strtotime( $cur_time ) );
			
		} else {
			$stamp = __('Expires: <b>Never</b>');
			$date = date_i18n( $datef, strtotime( current_time('mysql') ) );
		}
		
		echo '<div class="misc-pub-section curtime">';
			echo '<span id="cbp-expiration-timestamp">&nbsp;';
			printf($stamp, $date);
			echo '</span>' . "\n";
			echo '<a class="edit-expiration-timestamp hide-if-no-js" href="#edit_expiration_timestamp"><span aria-hidden="true">Edit</span> <span class="screen-reader-text">Edit date and time</span></a>';
			echo '<div id="cbp-expiration-timestampdiv" class="hide-if-js">';
				$this->expiration_time_input($cur_time);
			echo '</div>';
		echo '</div>';
	}
	
	function update_expiration_time($post_id)
	{
		global $post;
		if (empty($post) || ($post->post_type !== $this->post_type)) {
			return;
		}

		if ($this->check_reset_to_never_flag() ) {
			delete_post_meta($post_id, '_cbp_expiration_timestamp');
			return;
		}
		
		$new_expiration = $this->get_new_expiration_date();
		if ( $new_expiration && !is_wp_error($new_expiration) )
		{
			// a valid date was POSTed, and its different than before. 
			// Thus, let's save it as a custom field
			update_post_meta($post_id, '_cbp_expiration_timestamp', $new_expiration);
		}
	}
	
	function check_reset_to_never_flag()
	{
		return ( !empty($_POST['cbp-reset-to-never']) && $_POST['cbp-reset-to-never'] == '1' );
	}
	
	function get_new_expiration_date($post_data = false)
	{
		$new_expiration_date = false;
		
		if ($post_data === false) {
			$post_data = &$_POST;
		}
		
		// see if the expiration date has been changed, and thus must be updated
		foreach ( array('aa', 'mm', 'jj', 'hh', 'mn') as $timeunit ) {
			if ( !empty( $post_data['cbp-expiration-hidden_' . $timeunit] ) && 
				$post_data['cbp-expiration-hidden_' . $timeunit] != $post_data['cbp-expiration-' . $timeunit] ) {
				$post_data['edit_expiration_date'] = '1';
				break;
			}
		}
		
		// if the expiration date has changed, validate it now
		if ( !empty( $post_data['edit_expiration_date'] ) ) {
			// collect data
			$aa = $post_data['cbp-expiration-aa'];
			$mm = $post_data['cbp-expiration-mm'];
			$jj = $post_data['cbp-expiration-jj'];
			$hh = $post_data['cbp-expiration-hh'];
			$mn = $post_data['cbp-expiration-mn'];
			$ss = $post_data['cbp-expiration-ss'];
			
			// validate / normalize collected data
			$aa = ($aa <= 0 ) ? date('Y') : $aa;
			$mm = ($mm <= 0 ) ? date('n') : $mm;
			$jj = ($jj > 31 ) ? 31 : $jj;
			$jj = ($jj <= 0 ) ? date('j') : $jj;
			$hh = ($hh > 23 ) ? $hh -24 : $hh;
			$mn = ($mn > 59 ) ? $mn -60 : $mn;
			$ss = ($ss > 59 ) ? $ss -60 : $ss;			
			
			$new_expiration_date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $aa, $mm, $jj, $hh, $mn, $ss );
			$valid_date = wp_checkdate( $mm, $jj, $aa, $new_expiration_date );
			if ( !$valid_date ) {
				return new WP_Error( 'invalid_date', __( 'Whoops, the provided expiration date is invalid.' ) );
			}
			$post_data['expiration_date_gmt'] = get_gmt_from_date( $new_expiration_date );
		}
		
		// $new_expiration_date is now either false or a valid date
		return $new_expiration_date;
	}
	
	/**
	 * Returns an array that can be used as the meta_query argument to WP_Query,
	 * to restrict posts by expiration date
	 */
	function get_meta_query()
	{
		return array(
			'relation' => 'OR',
			array(
				'key' => '_cbp_expiration_timestamp',
				'value' => 'dummy', // prior to 3.9, SOME value is required even when using "NOT EXISTS"
				'compare' => 'NOT EXISTS', // 'NOT EXISTS' required WP >= 3.5
			),
			array(
				'key' => '_cbp_expiration_timestamp',
				'value' => current_time('mysql'),
				'compare' => '>',
			)
		);
	}
	
	/**
	 * Print out HTML form time + date elements for editing a banner's expiration date.
	 * Based on WordPress' touch_time function
	 *
	 * @param array $post_date    Accepts a starting time. Defaults to current time.
	 */
	function expiration_time_input( $post_date = '' )
	{
		if ( empty($post_date) ) {
			$post_date = current_time('mysql');
		}
		$edit = 1;
		$for_post = 1;
		$tab_index = 0;
		$multi = 0;	
		
		global $wp_locale, $comment;
		$post = get_post();
		
		if ( $for_post ) {
			$edit = ! ( in_array($post->post_status, array('draft', 'pending') ) && (!$post->post_date_gmt || '0000-00-00 00:00:00' == $post->post_date_gmt ) );
		}
		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 ) {
			$tab_index_attribute = " tabindex=\"$tab_index\"";
		}
		
		// todo: Remove this?
		// echo '<label for="timestamp" style="display: block;"><input type="checkbox" class="checkbox" name="edit_date" value="1" id="timestamp"'.$tab_index_attribute.' /> '.__( 'Edit timestamp' ).'</label><br />';
		$time_adj = current_time('timestamp');
		//$post_date = ($for_post) ? $post->post_date : $comment->comment_date;

		$jj = ($edit) ? mysql2date( 'd', $post_date, false ) : gmdate( 'd', $time_adj );
		$mm = ($edit) ? mysql2date( 'm', $post_date, false ) : gmdate( 'm', $time_adj );
		$aa = ($edit) ? mysql2date( 'Y', $post_date, false ) : gmdate( 'Y', $time_adj );
		$hh = ($edit) ? mysql2date( 'H', $post_date, false ) : gmdate( 'H', $time_adj );
		$mn = ($edit) ? mysql2date( 'i', $post_date, false ) : gmdate( 'i', $time_adj );
		$ss = ($edit) ? mysql2date( 's', $post_date, false ) : gmdate( 's', $time_adj );
		$cur_jj = gmdate( 'd', $time_adj );
		$cur_mm = gmdate( 'm', $time_adj );
		$cur_aa = gmdate( 'Y', $time_adj );
		$cur_hh = gmdate( 'H', $time_adj );
		$cur_mn = gmdate( 'i', $time_adj );
		$month = '<label for="mm" class="screen-reader-text">' . __( 'Month' ) . '</label><select ' . ( $multi ? '' : 'id="cbp-expiration-mm" ' ) . 'name="cbp-expiration-mm"' . $tab_index_attribute . ">\n";
		
		for ( $i = 1; $i < 13; $i = $i +1 ) {
			$monthnum = zeroise($i, 2);
			$month .= "\t\t\t" . '<option value="' . $monthnum . '" ' . selected( $monthnum, $mm, false ) . '>';
			/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
			$month .= sprintf( __( '%1$s-%2$s' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
		}
		$month .= '</select>';
		$day = '<label for="cbp-expiration-jj" class="screen-reader-text">' . __( 'Day' ) . '</label><input type="text" ' . ( $multi ? '' : 'id="cbp-expiration-jj" ' ) . 'name="cbp-expiration-jj" value="' . $jj . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
		$year = '<label for="cbp-expiration-aa" class="screen-reader-text">' . __( 'Year' ) . '</label><input type="text" ' . ( $multi ? '' : 'id="cbp-expiration-aa" ' ) . 'name="cbp-expiration-aa" value="' . $aa . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" />';
		$hour = '<label for="cbp-expiration-hh" class="screen-reader-text">' . __( 'Hour' ) . '</label><input type="text" ' . ( $multi ? '' : 'id="cbp-expiration-hh" ' ) . 'name="cbp-expiration-hh" value="' . $hh . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
		$minute = '<label for="cbp-expiration-mn" class="screen-reader-text">' . __( 'Minute' ) . '</label><input type="text" ' . ( $multi ? '' : 'id="cbp-expiration-mn" ' ) . 'name="cbp-expiration-mn" value="' . $mn . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
		echo '<div class="cbp-expiration-timestamp-wrap">';
		/* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
		printf( __( '%1$s %2$s, %3$s @ %4$s : %5$s' ), $month, $day, $year, $hour, $minute );
		echo '</div><input type="hidden" id="cbp-expiration-ss" name="cbp-expiration-ss" value="' . $ss . '" />';
		if ( $multi ) return;
		echo "\n\n";
		$map = array(
			'mm' => array( $mm, $cur_mm ),
			'jj' => array( $jj, $cur_jj ),
			'aa' => array( $aa, $cur_aa ),
			'hh' => array( $hh, $cur_hh ),
			'mn' => array( $mn, $cur_mn ),
		);
		foreach ( $map as $timeunit => $value ) {
			list( $unit, $curr ) = $value;
			echo '<input type="hidden" id="cbp-expiration-hidden_' . $timeunit . '" name="cbp-expiration-hidden_' . $timeunit . '" value="' . $unit . '" />' . "\n";
			$cur_timeunit = 'cur_' . $timeunit;
			echo '<input type="hidden" id="cbp-expiration-' . $cur_timeunit . '" name="cbp-expiration-' . $cur_timeunit . '" value="' . $curr . '" />' . "\n";
		}
	?>
	<input type="hidden" id="cbp-reset-to-never" name="cbp-reset-to-never" value="0" />
	<p>
		<a href="#edit_timestamp" class="expiration-save-timestamp hide-if-no-js button"><?php _e('OK'); ?></a>
		<a href="#edit_timestamp" class="expiration-cancel-timestamp hide-if-no-js button-expiration-cancel"><?php _e('Cancel'); ?></a>
		<a href="#reset_timestamp" class="cbp-reset-expiration hide-if-no-js">Reset</a>
	</p>
	<?php
	} // end expiration_time_input()
}