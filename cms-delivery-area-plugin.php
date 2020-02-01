<?php
/**
 * Plugin Name: cms-delivery-area-plugin
 * Description: cms-delivery-area-plugin
 * Version: 1.0
 */

add_action( 'admin_init', 'check_woocommerce' );
function check_woocommerce() {
	if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		add_action( 'admin_notices', 'woocommerce_notice' );

		deactivate_plugins( plugin_basename( __FILE__ ) ); 

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}

function woocommerce_notice() {
  ?><div class="error"><p>Sorry, but this plugin requires WooCommerce to be installed and active.</p></div><?php
}

add_action( 'admin_enqueue_scripts', 'add_admin_scripts' );
function add_admin_scripts() {
	wp_enqueue_style( 'cms-delivery-area-plugin-admin-style', plugin_dir_url( __FILE__ ) . 'css/admin.css' );
	wp_enqueue_script( 'cms-delivery-area-plugin-admin-script', plugin_dir_url( __FILE__ ) . 'js/admin.js' );
}


add_filter( 'woocommerce_shipping_methods', 'add_shipping_method' );
function add_shipping_method( $shipping_methods ) {
	require_once plugin_dir_path( __FILE__ ) . 'class-wc-shipping-area-rate.php';

	$shipping_methods['area_rate'] = 'WC_Shipping_Area_Rate';

	return $shipping_methods;
}
