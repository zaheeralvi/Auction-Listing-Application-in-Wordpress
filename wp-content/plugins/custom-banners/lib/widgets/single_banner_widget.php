<?php
/*
This file is part of Custom Banners.

Custom Banners is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Custom Banners is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Custom Banners.  If not, see <http://www.gnu.org/licenses/>.

Shout out to http://www.makeuseof.com/tag/how-to-create-wordpress-widgets/ for the help
*/

class singleBannerWidget extends WP_Widget
{
	function __construct(){
		$widget_ops = array('classname' => 'singleBannerWidget', 'description' => 'Displays a specified banner.' );
		parent::__construct('singleBannerWidget', 'Single Banner Widget', $widget_ops);
	}

	function singleBannerWidget(){
		$this->__construct();
	}

	function form($instance)
	{
		$ip = isValidCBKey();
		
		$defaults = array (
			'title' => '', 
			'bannerid' => null,
			'caption_position' => 'bottom',
			'use_image_tag' => true,
			'link_entire_banner' => false,
			'open_link_in_new_window' => false,
			'show_caption' => true,
			'show_cta_button' => true,
			'banner_height' => 'auto',
			'banner_height_px' => '',
			'banner_width' => 'auto',
			'banner_width_px' => '',
			'theme' => ''
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		$title = $instance['title'];
		$bannerid = $instance['bannerid'];
		$caption_position = $instance['caption_position'];
		$use_image_tag = $instance['use_image_tag'];
		$link_entire_banner = $instance['link_entire_banner'];
		$open_link_in_new_window = $instance['open_link_in_new_window'];
		$show_caption = $instance['show_caption'];
		$show_cta_button = $instance['show_cta_button'];
		$default_banner_width	= get_option('custom_banners_default_width', '');
		$default_banner_height 	= get_option('custom_banners_default_height', '');
		$banner_height = $instance['banner_height'];
		$banner_height_px= intval($instance['banner_height_px']) > 0 ? intval($instance['banner_height_px']) : $default_banner_height;
		$banner_width = $instance['banner_width'];
		$banner_width_px = intval($instance['banner_width_px']) > 0 ? intval($instance['banner_width_px']) : $default_banner_width;
		$theme = isset($instance['theme']) ? $instance['theme'] : get_option('custom_banners_theme', '');
		
		$args = array( 'post_type' => 'banner', 'posts_per_page' => -1 );
		$banners = get_posts($args);		
		?>
			<div class="gp_widget_form_wrapper">
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>">Widget Title:</label><br />
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('bannerid'); ?>">Banner to Display: </label><br />
					<select id="<?php echo $this->get_field_id('bannerid'); ?>" name="<?php echo $this->get_field_name('bannerid'); ?>" data-shortcode-key="id">
					<?php if($banners) : foreach ( $banners as $banner  ) : ?>
						<option value="<?php echo $banner->ID; ?>"  <?php if($bannerid == $banner->ID): ?> selected="SELECTED" <?php endif; ?>><?php echo $banner->post_title; ?></option>
					<?php endforeach; endif;?>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('caption_position'); ?>">Caption Position: </label><br />
					<select id="<?php echo $this->get_field_id('caption_position'); ?>" name="<?php echo $this->get_field_name('caption_position'); ?>">
						<option value="left"  <?php if($caption_position == "left"): ?> selected="SELECTED" <?php endif; ?>>Left</option>
						<option value="right"  <?php if($caption_position == "right"): ?> selected="SELECTED" <?php endif; ?>>Right</option>
						<option value="top"  <?php if($caption_position == "top"): ?> selected="SELECTED" <?php endif; ?>>Top</option>
						<option value="bottom"  <?php if($caption_position == "bottom"): ?> selected="SELECTED" <?php endif; ?>>Bottom</option>
					</select>
				</p>
									
				<p>
					<label for="<?php echo $this->get_field_id('theme'); ?>">Theme:</label><br/>
					<?php 
						$cb_cfg = new CustomBanners_Config();
						$cb_cfg->output_theme_selector($this->get_field_id('theme'), $this->get_field_name('theme'), $theme, $ip);
					?>
					<?php if (!$ip): ?>
					<br />
					<em><a target="_blank" href="http://goldplugins.com/our-plugins/custom-banners/upgrade-to-custom-banners-pro/?utm_source=wp_widgets&utm_campaign=widget_transitions">Upgrade To Unlock All 50+ Themes!</a></em>
					<?php endif; ?>
				</p>
				
				<fieldset class="radio_text_input">
					<legend>Height:</legend> &nbsp;

					<div class="bikeshed bikeshed_radio">
						<div class="radio_wrapper">
							<p class="radio_option"><label><input class="tog" name="<?php echo $this->get_field_name('banner_height'); ?>" type="radio" value="auto" <?php if($banner_height=='auto'){ ?>checked <?php } ?> data-shortcode-key="height"> Auto</label></p>
							<p class="radio_option"><label><input class="tog" name="<?php echo $this->get_field_name('banner_height'); ?>" type="radio" value="specify" <?php if($banner_height=='specify'){ ?>checked="checked" <?php } ?>> Specify: <input name="<?php echo $this->get_field_name('banner_height_px'); ?>" type="text" value="<?php echo $banner_height_px; ?>" data-shortcode-key="height">px</label></p>
						</div>
					</div>
				</fieldset>

				<fieldset class="radio_text_input">
					<legend>Width:</legend> &nbsp;

					<div class="bikeshed bikeshed_radio">
						<div class="radio_wrapper">
							<p class="radio_option"><label><input class="tog" name="<?php echo $this->get_field_name('banner_width'); ?>" type="radio" value="auto" <?php if($banner_width=='auto'){ ?>checked="checked"<?php } ?>  data-shortcode-key="width" > Auto</label></p>
							<p class="radio_option"><label><input class="tog" name="<?php echo $this->get_field_name('banner_width'); ?>" type="radio" value="100_percent" <?php if($banner_width=='100_percent'){ ?>checked="checked"<?php } ?> > 100%</label></p>
							<p class="radio_option"><label><input class="tog" name="<?php echo $this->get_field_name('banner_width'); ?>" type="radio" value="specify" <?php if($banner_width=='specify'){ ?>checked="checked"<?php } ?> > Specify: <input name="<?php echo $this->get_field_name('banner_width_px'); ?>" type="text" value="<?php echo $banner_width_px; ?>" data-shortcode-key="width"></label></p>
						</div>
					</div>
				</fieldset>
			
				<fieldset class="radio_text_input">
					<legend>Advanced Options:</legend> &nbsp;
					<div class="bikeshed bikeshed_radio">
						<p>
							<input class="widefat" id="<?php echo $this->get_field_id('show_caption'); ?>" name="<?php echo $this->get_field_name('show_caption'); ?>" type="checkbox" value="1" <?php if($show_caption){ ?>checked="checked"<?php } ?>/>
							<label for="<?php echo $this->get_field_id('show_caption'); ?>">Show Caption Box</label>
						</p>
						<p>
							<input class="widefat" id="<?php echo $this->get_field_id('show_cta_button'); ?>" name="<?php echo $this->get_field_name('show_cta_button'); ?>" type="checkbox" value="1" <?php if($show_cta_button){ ?>checked="checked"<?php } ?>/>
							<label for="<?php echo $this->get_field_id('show_cta_button'); ?>">Show Button</label>
						</p>
						<p>
							<input class="widefat" id="<?php echo $this->get_field_id('link_entire_banner'); ?>" name="<?php echo $this->get_field_name('link_entire_banner'); ?>" type="checkbox" value="1" <?php if($link_entire_banner){ ?>checked="checked"<?php } ?>/>
							<label for="<?php echo $this->get_field_id('link_entire_banner'); ?>">Link Entire Banner</label>
						</p>
						<p>
							<input class="widefat" id="<?php echo $this->get_field_id('open_link_in_new_window'); ?>" name="<?php echo $this->get_field_name('open_link_in_new_window'); ?>" type="checkbox" value="1" <?php if($open_link_in_new_window){ ?>checked="checked"<?php } ?>/>
							<label for="<?php echo $this->get_field_id('open_link_in_new_window'); ?>">Open Link In New Window</label>
						</p>
						<p>
							<input class="widefat" id="<?php echo $this->get_field_id('use_image_tag'); ?>" name="<?php echo $this->get_field_name('use_image_tag'); ?>" type="checkbox" value="1" <?php if($use_image_tag){ ?>checked="checked"<?php } ?>/>
							<label for="<?php echo $this->get_field_id('use_image_tag'); ?>">Use Image Tag</label>
						</p>
					</div>
				</fieldset>
			</div>
			<?php
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['bannerid'] = $new_instance['bannerid'];
		$instance['caption_position'] = $new_instance['caption_position'];
		$instance['use_image_tag'] = $new_instance['use_image_tag'];
		$instance['link_entire_banner'] = $new_instance['link_entire_banner'];
		$instance['open_link_in_new_window'] = $new_instance['open_link_in_new_window'];
		$instance['show_caption'] = $new_instance['show_caption'];
		$instance['show_cta_button'] = $new_instance['show_cta_button'];
		$instance['banner_height'] = $new_instance['banner_height'];
		$instance['banner_height_px'] = $new_instance['banner_height_px'];
		$instance['banner_width'] = $new_instance['banner_width'];
		$instance['banner_width_px'] = $new_instance['banner_width_px'];
		$instance['theme'] = $new_instance['theme'];
		return $instance;
	}

	function widget($args, $instance){
		global $ebp;
		
		//defaults
		$atts = array(	'id' => '',
						'group' => '',
						'caption_position' => 'bottom',
						'transition' => 'none',
						'count' => 1,
						'timer' => 4000,
						'use_image_tag' => false,
						'link_entire_banner' => false,
						'show_caption' => false,
						'show_cta_button' => false,
						'banner_height' => 'auto',
						'banner_height_px' => '',
						'banner_width' => 'auto',
						'banner_width_px' => '',
						'hide' => false,
						'theme' => '');

		
		extract($args, EXTR_SKIP);

		echo $before_widget;
		$title 								= empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$bannerid 							= empty($instance['bannerid']) ? null : $instance['bannerid'];
		$atts['caption_position'] 			= empty($instance['caption_position']) ? null : $instance['caption_position'];
		$atts['use_image_tag'] 				= empty($instance['use_image_tag']) ? null : $instance['use_image_tag'];
		$atts['link_entire_banner'] 		= empty($instance['link_entire_banner']) ? null : $instance['link_entire_banner'];
		$atts['open_link_in_new_window'] 	= empty($instance['open_link_in_new_window']) ? null : $instance['open_link_in_new_window'];
		$atts['show_caption'] 				= !empty($instance['show_caption']) ? true : false;
		$atts['show_cta_button'] 			= !empty($instance['show_cta_button']) ? true : false;
		$atts['banner_height'] 				= !empty($instance['banner_height']) ? $instance['banner_height'] : 'auto';
		$atts['banner_height_px']			= !empty($instance['banner_height_px']) ? $instance['banner_height_px'] : '';
		$atts['banner_width'] 				= !empty($instance['banner_width']) ? $instance['banner_width'] : 'auto';
		$atts['banner_width_px'] 			= !empty($instance['banner_width_px']) ? $instance['banner_width_px'] : '';
		$atts['theme'] 						= isset($instance['theme']) ? $instance['theme'] : get_option('custom_banners_theme', '');
		
		if (!empty($title)){
			echo $before_title . $title . $after_title;;
		}
			
		if (!empty($bannerid)) {
			$banner = get_post($bannerid);
			echo $ebp->buildBannerHTML($banner, $bannerid, $atts);
		}

		echo $after_widget;
	} 
}
?>