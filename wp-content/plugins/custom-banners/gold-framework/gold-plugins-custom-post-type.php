<?php 
/* 
This file is part of A Gold Plugin

Gold Plugins are free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

A Gold Plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with a Gold Plugin.  If not, see <http://www.gnu.org/licenses/>.
*/
if(!class_exists("GoldPlugins_CustomPostType")):
class GoldPlugins_CustomPostType
{
	var $customFields = false;
	var $customPostTypeName = 'custompost';
	var $customPostTypeSingular = 'customPost';
	var $customPostTypePlural = 'customPosts';
	var $prefix = '_ikcf_';
	
	function setupCustomPostType($postType)
	{
		$singular = ucwords($postType['name']);
		$plural = isset($postType['plural']) ? ucwords($postType['plural']) : $singular . 's';

		$this->customPostTypeName = strtolower($singular);
		$this->customPostTypeSingular = $singular;
		$this->customPostTypePlural = $plural;

		if ($this->customPostTypeName != 'post' && $this->customPostTypeName != 'page')
		{		
			$labels = array
			(
				'name' => _x($plural, 'post type general name'),
				'singular_name' => _x($singular, 'post type singular name'),
				'add_new' => _x('Add New ' . $singular, strtolower($singular)),
				'add_new_item' => __('Add New ' . $singular),
				'edit_item' => __('Edit ' . $singular),
				'new_item' => __('New ' . $singular),
				'view_item' => __('View ' . $singular),
				'search_items' => __('Search ' . $plural),
				'not_found' =>  __('No ' . strtolower($plural) . ' found'),
				'not_found_in_trash' => __('No ' . strtolower($plural) . ' found in Trash'), 
				'parent_item_colon' => ''
			);
			
			$args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true, 
				'query_var' => true,
				'rewrite' => array( 'slug' => $postType['slug'], 'with_front' => (strlen($postType['slug'])>0) ? false : true),
				'capability_type' => 'post',
				'hierarchical' => false,
				'supports' => array('title','editor','author','thumbnail','excerpt','comments','custom-fields'),
				'menu_icon' => !empty( $postType['menu_icon'] ) ? $postType['menu_icon'] : '',
			); 
			$this->customPostTypeArgs = $args;
	
			// register hooks
			add_action( 'init', array( &$this, 'registerPostTypes' ), 0 );
		}
	}

	function registerPostTypes()
	{
	  register_post_type($this->customPostTypeName,$this->customPostTypeArgs);
	}
	
	function setupCustomFields($fields)
	{
		$this->customFields = array();
		foreach ($fields as $f)
		{
			$this->customFields[] = array
			(
				"name"			=> $f['name'],
				"title"			=> $f['title'],
				"description"	=> isset($f['description']) ? $f['description'] : '',
				"type"			=> isset($f['type']) ? $f['type'] : "text",
				"scope"			=>	array( $this->customPostTypeName ),
				"capability"	=> "edit_posts"
			);
		}
		// register hooks
		add_action( 'admin_menu', array( &$this, 'createCustomFields' ) );
		add_action( 'save_post', array( &$this, 'saveCustomFields' ), 1, 2 );
	}
		
	/**
	* Create the new Custom Fields meta box
	*/
	function createCustomFields() 
	{
		if ( function_exists( 'add_meta_box' ) ) 
		{
			//add_meta_box( 'my-custom-fields', 'Custom Fields', array( &$this, 'displayCustomFields' ), 'page', 'normal', 'high' );
			//add_meta_box( 'my-custom-fields', 'Custom Fields', array( &$this, 'displayCustomFields' ), 'post', 'normal', 'high' );
			add_meta_box( 'my-custom-fields'.md5(serialize($this->customFields)), $this->customPostTypeSingular . ' Information', array( &$this, 'displayCustomFields' ), $this->customPostTypeName, 'normal', 'high' );//RWG
		}
	}

	/**
	* Display the new Custom Fields meta box
	*/
	function displayCustomFields() {
		global $post;
		?>
		<div class="form-wrap">
			<?php
			wp_nonce_field( 'my-custom-fields', 'my-custom-fields_wpnonce', false, true );
			foreach ( $this->customFields as $customField ) {
				// Check scope
				$scope = $customField[ 'scope' ];
				$output = false;
				foreach ( $scope as $scopeItem ) {
					switch ( $scopeItem ) {
						case "post": {
							// Output on any post screen
							if ( basename( $_SERVER['SCRIPT_FILENAME'] )=="post-new.php" || $post->post_type=="post" )
								$output = true;
							break;
						}
						case "page": {
							// Output on any page screen
							if ( basename( $_SERVER['SCRIPT_FILENAME'] )=="page-new.php" || $post->post_type=="page" )
								$output = true;
							break;
						}
						default:{//RWG
							if ($post->post_type==$scopeItem )
								$output = true;
							break;
						}
					}
					if ( $output ) break;
				}
				// Check capability
				if ( !current_user_can( $customField['capability'], $post->ID ) )
					$output = false;
				// Output if allowed
				if ( $output ) { ?>
					<div class="form-field form-required">
						<?php
						switch ( $customField[ 'type' ] ) {
							case "checkbox": {
								// Checkbox
								echo '<label for="' . $this->prefix . $customField[ 'name' ] .'" style="display:inline;"><b>' . $customField[ 'title' ] . '</b></label>&nbsp;&nbsp;';
								echo '<input type="checkbox" name="' . $this->prefix . $customField['name'] . '" id="' . $this->prefix . $customField['name'] . '" value="yes"';
								if ( get_post_meta( $post->ID, $this->prefix . $customField['name'], true ) == "yes" )
									echo ' checked="checked"';
								echo '" style="width: auto;" />';
								break;
							}
							case "textarea": {
								// Text area
								echo '<label for="' . $this->prefix . $customField[ 'name' ] .'"><b>' . $customField[ 'title' ] . '</b></label>';
								echo '<textarea name="' . $this->prefix . $customField[ 'name' ] . '" id="' . $this->prefix . $customField[ 'name' ] . '" columns="30" rows="3">' . htmlspecialchars( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) ) . '</textarea>';
								break;
							}
							default: {
								// Plain text field
								echo '<label for="' . $this->prefix . $customField[ 'name' ] .'"><b>' . $customField[ 'title' ] . '</b></label>';
								echo '<input type="text" name="' . $this->prefix . $customField[ 'name' ] . '" id="' . $this->prefix . $customField[ 'name' ] . '" value="' . htmlspecialchars( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) ) . '" />';
								break;
							}
						}
						?>
						<?php if ( $customField[ 'description' ] ) echo '<p>' . $customField[ 'description' ] . '</p>'; ?>
					</div>
				<?php
				}
			} ?>
		</div>
		<?php
	}

	/**
	* Save the new Custom Fields values
	*/
	function saveCustomFields( $post_id, $post ) {
		if ( isset($_POST[ 'my-custom-fields_wpnonce' ]) && !wp_verify_nonce( $_POST[ 'my-custom-fields_wpnonce' ], 'my-custom-fields' ) ){
			return;
		}
		if ( !current_user_can( 'edit_post', $post_id ) ){
			return;
		}
		// handle the case when the custom post is quick edited
		// otherwise all custom meta fields are cleared out
		if (isset($_POST['_inline_edit']) && wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce') || isset($_REQUEST['bulk_edit'])){
			  return;
		}
		foreach ( $this->customFields as $customField ) {
			if ( current_user_can( $customField['capability'], $post_id ) ) {
				if ( isset( $_POST[ $this->prefix . $customField['name'] ] ) && trim( $_POST[ $this->prefix . $customField['name'] ] ) ) {
					update_post_meta( $post_id, $this->prefix . $customField[ 'name' ], $_POST[ $this->prefix . $customField['name'] ] );
				} else {
					delete_post_meta( $post_id, $this->prefix . $customField[ 'name' ] );
				}
			}
		}
	}
	
	function get_metabox_id()
	{
		return 'my-custom-fields'.md5(serialize($this->customFields));
	}

	function __construct($postType, $customFields = false, $removeDefaultCustomFields = false)
	{
		$this->setupCustomPostType($postType);
		
		if ($customFields)
		{
			$this->setupCustomFields($customFields);
		}				
	}
}
endif;
?>