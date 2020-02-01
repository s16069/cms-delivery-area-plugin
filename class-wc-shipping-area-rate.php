<?php

defined( 'ABSPATH' ) || exit;

/**
 * WC_Shipping_Area_Rate class.
 */
class WC_Shipping_Area_Rate extends WC_Shipping_Method {

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Shipping method instance ID.
	 */
	public function __construct( $instance_id = 0 ) {

		$this->id                    = 'area_rate';
		$this->instance_id           = absint( $instance_id );
		$this->method_title          = __( 'Area Rate Shipping Method' );
		$this->method_description    = __( 'Area Rate Shipping method' );
		$this->title                 = __( 'Area Rate Shipping method' );
		$this->supports              = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		$this->init();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Init user set variables.
	 */
	public function init() {
		$this->instance_form_fields = include 'settings-area-rate.php';
		$this->backend_key          = $this->get_option( 'backend_key' );
		$this->frontend_key         = $this->get_option( 'frontend_key' );
		$this->zone_1_distance      = intval( $this->get_option( 'zone_1_distance' ) );
		$this->zone_1_cost          = $this->get_option( 'zone_1_cost' );
		$this->zone_2_distance      = intval( $this->get_option( 'zone_2_distance' ) );
		$this->zone_2_cost          = $this->get_option( 'zone_2_cost' );
		$this->zone_3_distance      = intval( $this->get_option( 'zone_3_distance' ) );
		$this->zone_3_cost          = $this->get_option( 'zone_3_cost' );
	}


	/**
	 * Calculate the shipping costs.
	 *
	 * @param array $package Package of items from cart.
	 */
	public function calculate_shipping( $package = array() ) {
		
		$store_address = $this->get_store_address();
		$client_address = $this->get_client_address( $package['destination'] );
		if ($client_address === null) {
			return;
		}

		$data = $this->google_distance( $store_address, $client_address, $this->backend_key );
		if ($data === null) {
			return;
		}

		$distance_val = $data->distance->value;
		$distance_text = $data->distance->text;

		$cost = $this->default_cost;
		$label = '';
		$can_deliver = true;

		if ( $distance_val < $this->zone_1_distance) {
			$cost = $this->zone_1_cost;
			$label = 'Zone 1';
		} else if ( $distance_val < $this->zone_2_distance) {
			$cost = $this->zone_2_cost;
			$label = 'Zone 2';
		} else if ( $distance_val < $this->zone_3_distance) {
			$cost = $this->zone_3_cost;
			$label = 'Zone 3';
		} else {
			$can_deliver = false;
		}

		if ($can_deliver) {
			$rate = array(
				'id'      => $this->get_rate_id(),
				'label'   => $label . ': ' . $distance_text,
				'cost'    => $cost,
				'package' => $package,
			);
			$this->add_rate( $rate );
		}
	}

	/**
	 * {
	 *   "distance" : {
	 *     "text" : "4,5 km",
	 *     "value" : 4532
	 *   },
	 *   "duration" : {
	 *     "text" : "11 min",
	 *     "value" : 675
	 *   },
	 *   "status" : "OK"
	 * }
	 */
	private function google_distance($store_address, $client_address, $key) {
		if ( empty( $key ) ) {
			return null; // TODO
		}

		$url = $this->prepare_url( $store_address, $client_address, $key );

		$request = wp_remote_get( $url );

		if( is_wp_error( $request ) ) {
			return null;	// TODO
		}

		$body = wp_remote_retrieve_body( $request );

		$data = json_decode( $body );

		if ( empty( $data ) ) {
			return null; // TODO
		}

		if ( $data->status != 'OK' ) {
			return null; // TODO
		}

		$rows = $data->rows;
		if ( count( $rows ) != 1 ) {
			return null; // TODO
		}
		
		$elements = $rows[0]->elements;
		if ( count( $elements ) != 1 ) {
			return null; // TODO
		}

		$element = $elements[0];
		if ( $element->status != 'OK' ) {
			return null; // TODO
		}

		return $element;
	}

	private function get_client_address( $destination ) {
		$city        = $destination['city'];
		$address_1   = $destination['address_1'];
		$address_2   = $destination['address_2'];
		$postcode    = $destination['postcode'];

		if ( empty($city) || empty($address_1) ) {
			return null;	// TODO
		}

		return $city . ', ' . $address_1 . ' ' . $address_2;
	}

	private function get_store_address() {
		$city        = get_option( 'woocommerce_store_city' );
		$address_1   = get_option( 'woocommerce_store_address' );
		$address_2   = get_option( 'woocommerce_store_address_2' );
		$postcode    = get_option( 'woocommerce_store_postcode' );
		$raw_country = get_option( 'woocommerce_default_country' );

		return $city . ', ' . $address_1 . ' ' . $address_2;
	}

	private function prepare_url($store_address, $client_address, $key) {
		$store_address = urlencode( trim( $store_address ) );
		$client_address = urlencode( trim( $client_address ) );
		$key = urlencode( trim( $key ) );

		$url = 'https://maps.googleapis.com/maps/api/distancematrix/json';

		return $url . '?origins=' . $store_address . '&destinations=' . $client_address .'&language=pl-PL&key=' . $key;
	}

	/**
	 * {
   *  "lat" : 52.223802,
   *  "lng" : 20.9940479
   * }
	 */
	private function google_coordinates($address, $key) {
		if ( empty( $key ) ) {
			return null; // TODO
		}

		$address = urlencode( trim( $address ) );
		$key = urlencode( trim( $key ) );

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . $key;

		$request = wp_remote_get( $url );

		if( is_wp_error( $request ) ) {
			return null;	// TODO
		}

		$body = wp_remote_retrieve_body( $request );

		$data = json_decode( $body );

		if ( empty( $data ) ) {
			return null; // TODO
		}

		if ( $data->status != 'OK' ) {
			return null; // TODO
		}

		$results = $data->results;
		if ( count( $results ) != 1 ) {
			return null; // TODO
		}
		
		$result = $results[0];

		return $result->geometry->location;
	}

	/**
	 * Sanitize the cost field.
	 *
	 * @since 3.4.0
	 * @param string $value Unsanitized value.
	 * @return string
	 */
	public function sanitize_cost( $value ) {
		$value = is_null( $value ) ? '' : $value;
		$value = wp_kses_post( trim( wp_unslash( $value ) ) );
		$value = str_replace( array( get_woocommerce_currency_symbol(), html_entity_decode( get_woocommerce_currency_symbol() ) ), '', $value );
		return $value;
	}

	public function get_admin_options_html() {
		
		$options_html = parent::get_admin_options_html();
		$map_html = $this->get_map_html();
		
		return '<div class="shipping-area-options"><div>' . $options_html . '</div><div>' . $map_html . '</div></div>';

		return $html;
	}

	private function get_map_html() {
		if ( empty( $this->backend_key ) || empty( $this->frontend_key ) ) {
			return 'Enter Api Keys to show map';
		} else {

			$coordinates = $this->google_coordinates( $this->get_store_address(), $this->backend_key );

			if ( $coordinates === null ) {
				return 'Store location not found';
			}

			$key = urlencode( trim( $this->frontend_key ) );
			$url = 'https://maps.googleapis.com/maps/api/js?key=' . $key;

			wp_enqueue_script( 'cms-delivery-area-plugin-maps', $url, array('cms-delivery-area-plugin-admin-script') );

			return '<div id="google-map"></div>' .
				'<script>(function() { initAreas(); initMap({lat: ' . $coordinates->lat . ', lng: ' . $coordinates->lng .'}); })();</script>';
		}
	}
}
