<?php

defined( 'ABSPATH' ) || exit;

$settings = array(
	'backend_key'      => array(
		'title'             => __( 'Backend API Key' ),
		'type'              => 'text',
		'placeholder'       => '',
		'default'           => '',
	),
	'frontend_key'   	 => array(
		'title'             => __( 'Frontend API Key' ),
		'type'              => 'text',
		'placeholder'       => '',
		'default'           => '',
	),
	'zone_1_distance'  => array(
		'title'             => __( 'Zone 1 distance (m)' ),
		'type'              => 'number',
		'placeholder'       => '',
		'default'           => '0',
	),
	'zone_1_cost'      => array(
		'title'             => __( 'Zone 1 Cost'),
		'type'              => 'text',
		'placeholder'       => '',
		'default'           => '0',
		'sanitize_callback' => array( $this, 'sanitize_cost' ),
	),
	'zone_2_distance'  => array(
		'title'             => __( 'Zone 2 distance (m)' ),
		'type'              => 'number',
		'placeholder'       => '',
		'default'           => '0',
	),
	'zone_2_cost'      => array(
		'title'             => __( 'Zone 2 Cost' ),
		'type'              => 'text',
		'placeholder'       => '',
		'default'           => '0',
		'sanitize_callback' => array( $this, 'sanitize_cost' ),
	),
	'zone_3_distance'  => array(
		'title'             => __( 'Zone 3 distance (m)' ),
		'type'              => 'number',
		'placeholder'       => '',
		'default'           => '0',
	),
	'zone_3_cost'      => array(
		'title'             => __( 'Zone 3 Cost' ),
		'type'              => 'text',
		'placeholder'       => '',
		'default'           => '0',
		'sanitize_callback' => array( $this, 'sanitize_cost' ),
	),

);

return $settings;
