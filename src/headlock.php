<?php
/**
 * Plugin Name:     Headlock - Security Headers
 * Plugin URI:      https://timnash.co.uk
 * Description:     Provides set of standard "Security Headers" and basic defaults
 * Author:          Tim Nash
 * Author URI:      https://timnash.co.uk
 * Text Domain:     headlock
 * Domain Path:     /languages
 * Version:         0.9.0
 *
 * @package         headlock
 */

// Direct access is pointless
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Auto loader for header files
 * Loads the individual headers stored in /headers folder along with their helper functions
 */
function headlock_auto_loader() {
	// Prepare list of files
	$files = glob( plugin_dir_path( __FILE__ ) . 'headers/*.php' );
	// Assume we hace a file
	if ( ! empty( $files ) ) {
		foreach ( $files as $file ) {
			// require this file
			require_once $file;
		}
	}
}
add_action( 'plugins_loaded', 'headlock_auto_loader' );

/*
 * Enables and Disabled Security Headers and sets them
 * Specifies which headers are available and provides opportunity to modify them before setting
 * @note outputs the actual headers.
 * @return null - writes string to headers
 */
function headlock_enabled_security_headers() {

	$enabled_security_headers = headlock_available_security_headers();

	if ( is_array( $enabled_security_headers ) ) {
		foreach ( $enabled_security_headers as $security_header ) {

			// Fix for earlier versions, which used a different format for the array
			$security_header = strtolower( $security_header );
			$security_header = str_replace( '-', '_', $security_header );

			// Generate our calls to our function
			$function_call = 'headlock_' . $security_header;
			if ( function_exists( $function_call ) ) {

				// Get the final header from function
				$header = $function_call();

				// Filter the final Output string should we want to.
				$header = apply_filters( $function_call . '_output', $header );
				// Set header
				header( $header );

				// Do any additional actions
				// Indivdual headers hook
				do_action( $function_call . '_additional', $header );
				// Generic Hook
				do_action( 'headlock_security_headers', $security_header, $header );
			}
		}
	}
}

add_action( 'send_headers', 'headlock_enabled_security_headers' );

function headlock_available_security_headers() {
	// Enable Safe to use headers
	$enabled_security_headers = array(
		'x_frame_options',
		'x_xss_protection',
		'x_content_type_options',
		'referrer_policy',
	);

	// Filter to enable additional options such as HSTS or CSP
	return apply_filters( 'headlock_enabled_security_headers', $enabled_security_headers );
}

/*
 * Debug notice, to allow developers to be notified of failing filters
 * Only enabled when WP_DEBUG is set to true.
 * @param string $filter - name of the filter
 * @param string $filter - Debug message
 * @return null - writes to error_log
 */
function _headlock_debug_helper( ?string $filter = 'Headlock', ?string $debug, $data = false ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
		if ( isset( $data ) ) {
			$data = wp_json_encode( $data );
		} else {
			$data = '';
		}
		$str = 'Headlock Warning - ' . $filter . ' - ' . $debug . ' ' . $data;
		return error_log( $str );
	}
}

/*
 * Helper function for validating and formatting values
 *
 * @params sting|null $value, array $sources
 * @return string - Formatted String
 *
 */
function _headlock_encode_values( ?string $value, array $sources ) {
	if ( ! empty( $value ) && in_array( $value, $sources, true ) ) {
		if ( false === filter_var( $value, FILTER_VALIDATE_URL ) ) {
            if(false === strpos($value, '*' ) ){
                $value = "'" . $value . "'";
            }
		}
		return $value;
	}
}
