<?php
/**
 * Created by PhpStorm.
 * User: Zaheer.Abbas
 * Date: 12/12/2018
 * Time: 3:15 PM
 */
global $avia_config, $post;

//var_dump($post); die;
function add_my_post_types_to_query( $query ) {
//    if ( is_home() && $query->is_main_query() )
    $query->set( 'post_type', array( 'post', 'movies' ) );
    return $query;
}