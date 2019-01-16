<?php

/*
* Add your own functions here. You can also copy some of the theme functions into this file. 
* Wordpress will use those functions instead of the original functions then.
*/


function custom_post_type() {

// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Movies', 'Post Type General Name', 'twentythirteen' ),
        'singular_name'       => _x( 'Movie', 'Post Type Singular Name', 'twentythirteen' ),
        'menu_name'           => __( 'Movies', 'twentythirteen' ),
        'parent_item_colon'   => __( 'Parent Movie', 'twentythirteen' ),
        'all_items'           => __( 'All Movies', 'twentythirteen' ),
        'view_item'           => __( 'View Movie', 'twentythirteen' ),
        'add_new_item'        => __( 'Add New Movie', 'twentythirteen' ),
        'add_new'             => __( 'Add New', 'twentythirteen' ),
        'edit_item'           => __( 'Edit Movie', 'twentythirteen' ),
        'update_item'         => __( 'Update Movie', 'twentythirteen' ),
        'search_items'        => __( 'Search Movie', 'twentythirteen' ),
        'not_found'           => __( 'Not Found', 'twentythirteen' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'twentythirteen' ),
    );

// Set other options for Custom Post Type

    $args = array(
        'label'               => __( 'Movies', 'twentythirteen' ),
        'description'         => __( 'Movie news and reviews', 'twentythirteen' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        // You can associate this CPT with a taxonomy or custom taxonomy.
        'taxonomies'          => array( 'genres' ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'page',
    );

    // Registering your Custom Post Type
    register_post_type( 'Movies', $args );

}

/* Hook into the 'init' action so that the function
* Containing our post type registration is not 
* unnecessarily executed. 
*/
 
add_action( 'init', 'custom_post_type', 0 );



//[getEvent]
function getEvent_func(){
	$id = $_GET['id'];
	$table = 'wp_lot';
	$results = $GLOBALS['wpdb']->get_results( "SELECT * FROM `$table` where id=$id"); 
	if(isset($results)){
		foreach ($results as $result) {
			echo '<div class="event_details row">';
				echo '<div class="col-md-8">';
					echo '<h2 class="heading">'.$result->title.'</h2>';
					echo '<p class="desc">'.$result->images.'</p>';
					echo '<p class="desc">'.$result->video.'</p>';
				echo '</div>';
				echo '<div class="col-md-4">';
					echo '<span class="text-primary"> Quantity :<strong>'.$result->quantity.'</strong></span><br />'; 
					echo '<span class="text-primary"> Price :<strong>'.$result->price.'</strong></span><br /></div>';
				echo '</div>';
			echo '</div>';
		} 
	}else{
		echo 'Please Contact to Administator for this Issue';	
	}
}

add_shortcode( 'getEvent', 'getEvent_func' );


//[listEvent]
function listEvent_func(){
	
// 	$events = $wpdb->get_row( "SELECT * FROM wp_events" );
	$table = 'wp_auction';
	$events = $GLOBALS['wpdb']->get_results( "SELECT * FROM `$table`");  
	if(isset($events)){
		$events_json= json_encode($events);
		return $events_json;
	}else{
		return 'No Event Found';
	}
}
add_shortcode( 'listEvent', 'listEvent_func' );

//[showEvent]
function show_func(){

	$table = 'wp_auction';
	$events = $GLOBALS['wpdb']->get_results( "SELECT * FROM `wp_auction` WHERE `start` > DATE_FORMAT(NOW(),'%Y-%m-%d') LIMIT 1"); 
	
	foreach ($events as $event){
		return $event->title; 
	}
}
add_shortcode( 'showEvent', 'show_func' );

//[yearEvent]
function yearEvent_func(){

	$table = 'wp_auction';
	$events = $GLOBALS['wpdb']->get_results( "SELECT * FROM `wp_auction` WHERE `start` > DATE_FORMAT(NOW(),'%Y-%m-%d') LIMIT 1"); 
	foreach ($events as $event){
		$date = $event->start;
		$date = explode('-', $date);
		$year  = $date[0];
		return $year; 
	}
}
add_shortcode( 'yearEvent', 'yearEvent_func' );

//[monthEvent]
function monthEvent_func(){

	$table = 'wp_auction';
	$events = $GLOBALS['wpdb']->get_results( "SELECT * FROM `wp_auction` WHERE `start` > DATE_FORMAT(NOW(),'%Y-%m-%d') LIMIT 1"); 
	foreach ($events as $event){
		$date = $event->start;
		$date = explode('-', $date);
		$month  = $date[1];
		return $month; 
	}
}
add_shortcode( 'monthEvent', 'monthEvent_func' );


//[dayEvent]
function dayEvent_func(){

	$table = 'wp_auction';
	$events = $GLOBALS['wpdb']->get_results( "SELECT * FROM `wp_auction` WHERE `start` > DATE_FORMAT(NOW(),'%Y-%m-%d') LIMIT 1"); 
	foreach ($events as $event){
		$date = $event->start;
		$date = explode('-', $date);
		$date = explode(' ', $date[2]);
		$day  = $date[0];
		return $day; 
	}
}
add_shortcode( 'dayEvent', 'dayEvent_func' );


//[idEvent]
function idEvent_func(){

	$table = 'wp_auction';
	$events = $GLOBALS['wpdb']->get_results( "SELECT * FROM `wp_auction` WHERE `start` > DATE_FORMAT(NOW(),'%Y-%m-%d') LIMIT 1"); 
	foreach ($events as $event){
		$id = $event->id;
		return $id; 
	}
}
add_shortcode( 'idEvent', 'idEvent_func' );


//[auctiondate]
function auctiondate_func(){

	$table = 'wp_auction';
	$events = $GLOBALS['wpdb']->get_results( "SELECT * FROM `wp_auction` WHERE `start` > DATE_FORMAT(NOW(),'%Y-%m-%d') LIMIT 1"); 
	
	foreach ($events as $event){
		return $event->id;
	}
}
add_shortcode( 'auctiondate', 'auctiondate_func' );

//[lotlistEvent]
function lotlistEvent_func(){
	$id = $_GET['id'];
	$table = 'wp_lot';
	$events = $GLOBALS['wpdb']->get_results( "SELECT * FROM `$table` where auction_id=$id");  
	if(isset($events)){
		echo '<ol class="lot_list table-responsive">';
			foreach ($events as $event) {
				echo '<li><h3 class="text-primary">'.$event->title.'<a href="http://localhost/ccjk/event-details?id='.$event->id.'" class="pull-right">More Details</a></h3></li>';
			}
		echo '<ol>';
	}else{
		return 'No Details Found Against This Auction';
	}
}
add_shortcode( 'lotlistEvent', 'lotlistEvent_func' );



add_filter('avf_avia_builder_gallery_image_link', 'avia_change_gallery_thumbnail_link', 10, 4);
function avia_change_gallery_thumbnail_link($link, $attachment, $atts, $meta)
{
    $link = wp_get_attachment_image_src($attachment->ID, "full");
    return $link;
}

