<?php

class CustomBanners_Config
{
	function all_themes($flatten = false)
	{
		$themes = array_merge(self::free_themes(), self::pro_themes());
		if ($flatten) {
			$old_keys = array_keys($themes);
			$themes = self::array_flatten($themes);
			foreach( $old_keys as $old_key ) {
				if ( isset($themes[$old_key]) ) {
					unset($themes[$old_key]);
				}
			}
		}
		return $themes;
	}

	function free_themes()
	{
		//array of free themes that are available
		//includes names
		return array(
			'free_themes' => array(
				'free_themes' => 'Basic Themes',
				'default_style' => 'Default Theme',
			),
			'standard' => array(
				'standard' => 'Standard Theme',
				'standard-white' => 'Standard Theme - White',
				'standard-black' => 'Standard Theme - Black',
				'standard-yellow' => 'Standard Theme - Yellow',
				'standard-pink' => 'Standard Theme - Pink',
				'standard-blue' => 'Standard Theme - Blue',
			)
		);		
	}
	
	function pro_themes()
	{	
		$pro_themes = array();
	
		return apply_filters('cb_theme_array',$pro_themes);
	}
	
	function array_flatten($array)
	{
		$return = array();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$return = array_merge($return, self::array_flatten($value));
			} else {
				$return[$key] = $value;
			}
		}
		return $return;
	}

	function output_theme_selector($field_id, $field_name, $current = '')
	{
?>		
		<select class="widefat" id="<?php echo $field_id ?>" name="<?php echo $field_name; ?>">
			<?php
				$themes = self::all_themes();
				foreach ($themes as $group_slug => $group_themes)
				{
					$skip_next = true;
					foreach ($group_themes as $theme_slug => $theme_name) {
						if ($skip_next) {
							printf('<optgroup label="%s">', $theme_name);
							$skip_next = false;
							continue;
						}
						$selected = ( strcmp($theme_slug, $current) == 0 ) ? 'selected="selected"' : '';
						printf('<option value="%s" %s>%s</option>', $theme_slug, $selected, $theme_name);
					}
					echo '</optgroup>';
				}
				?>
			</select>
<?php
	}
	
}