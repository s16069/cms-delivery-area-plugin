<?php
/**
 * Plugin Name: cms-delivery-area-plugin
 * Description: cms-delivery-area-plugin
 * Version: 1.0
 */
 
add_action( 'the_content', 'hello_world_text' );

function hello_world_text ( $content ) {
    return $content .= '<p>Hello Plugin!</p>';
} 
