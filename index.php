<?php
/**
 * Plugin Name: cms-delivery-area-plugin
 * Description: cms-delivery-area-plugin
 * Version: 1.0
 */
 
add_action( 'the_content', 'hello_world_text' );

function hello_world_text ( $content ) {
	if(is_page('checkout')) {
		return $content .= '<p>Hello Plugin!</p>';
    }
} 
