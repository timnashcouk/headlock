<?php
/**
 * Plugin Name:     Headlock - Security Headers
 * Plugin URI:      https://timnash.co.uk
 * Description:     Provides set of standard "Security Headers" and basic defaults
 * Author:          Tim Nash
 * Author URI:      https://timnash.co.uk
 * Text Domain:     headlock
 * Domain Path:     /languages
 * Version:         0.3.0
 *
 * @package         headlock
 */

// Direct access is pointless
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function headlock_enabled_security_headers(){

	// Enable Safe to use headers
	$enabled_security_headers = array(
		'x_frame_options',
		'x_xss_protection',
		'x_content_type_options',
		'referrer_policy'
	);

	// Filter to enable additional options such as HSTS or CSP
	$enabled_security_headers = apply_filters( 'headlock_enabled_security_headers', $enabled_security_headers );

	if( is_array($enabled_security_headers ) ){
		foreach( $enabled_security_headers as $security_header ){

			// Fix for earlier versions, which used a different format for the array
			$security_header = strtolower( $security_header );
			$security_header = str_replace( '-', '_', $security_header );

			// Generate our calls to our function
			$function_call = 'headlock_'.$security_header;
			if( function_exists( $function_call ) ){

				// Get the final header from function
				$header = $function_call();

				// Filter the final Output string should we want to.
				$header = apply_filters( $function_call.'_output' , $header );
				// Set header
				header( $header );

				// Do any additional actions
				// Indivdual headers hook
				do_action( $function_call.'_additional', $header );
				// Generic Hook
				do_action( 'headlock_security_headers', $security_header, $header );
			}
		}
	}
}

add_action( 'send_headers', 'headlock_enabled_security_headers' );

/**
 * Sets the HSTS Security Header
 * Default Args: max-age 1 year, includesubdomains true, preload true.
 * Filter: headlock_strict_transport_security
 *
 * If testing recommend as an inital configuration
 * 	'max-age' 			=> 30,
 *	'includeSubDomains' => false,
 *	'preload' 			=> false
 *
 * @return string
 */
function headlock_strict_transport_security() {
	// Standard Defaults for production
	$strict_transport_security_args = array(
		'max-age' 			=> 31536000, //1 Year
		'includeSubDomains' => true,
		'preload' 			=> true
	);

	// Filter arguments for development and testing
	$strict_transport_security_args = apply_filters( 'headlock_strict_transport_security', $strict_transport_security_args );

	//Begin setting header string
	$header = 'strict-transport-security: ';

	// Check we have a max-age and it's a numeric in nature
	if( isset( $strict_transport_security_args['max-age'] ) &&
		is_numeric($strict_transport_security_args['max-age'] )
		){
			$header = $header . 'max-age='.$strict_transport_security_args['max-age'].'; ';
	}else{
		// max-age is a requirement for this header
		return;
	}

	// Include if we wish to include sub domains
	if( isset( $strict_transport_security_args['includeSubDomains'] ) &&
		true === $strict_transport_security_args['includeSubDomains'] ){
		$header = $header . 'includeSubDomains; ';
	}

	// Include if we wish to include preload (this is a requirement for Browser preload)
	if( isset( $strict_transport_security_args['preload'] ) &&
		true === $strict_transport_security_args['preload'] ){
		$header = $header . 'preload';
	}

	return $header;
}

/**
 * Sets the X-FRAME-OPTIONS header
 * Default Args: SAMEORIGIN
 *
 * Filter: headlock_x_frame_options
 * 	Alternative to SAMEORIGIN
 * 		DENY Prevents iFrame of site within itself.
 *
 * @return string
 */
function headlock_x_frame_options() {
	// Set Default as sameorigin
	$frame_option = 'sameorigin';

	// Filter arguments for development and testing
	$frame_option = apply_filters( 'headlock_x_frame_options', $frame_option );

	return 'X-Frame-Options: '. strtoupper( $frame_option );
}


/**
 * Sets the XSS Protection Security Header
 *
 * This header is only required if supporting Internet Explorer
 *
 * Default Args: 1 block
 * Filter: headlock_x_xss_protection
 *
 *
 * @return string
 */
function headlock_x_xss_protection() {
	// Standard Defaults for production
	$xss_protection_args = array(
		'level' 	=> 1,
		'block' 	=> true,
	);

	// Filter arguments for development and testing
	$xss_protection_args = apply_filters( 'headlock_x_xss_protection', $xss_protection_args );

	//Begin setting header string
	$header = 'X-Xss-Protection: ';

	if( isset( $xss_protection_args['level'] ) &&
		is_numeric( $xss_protection_args['level'] )
		){
			$header = $header . $xss_protection_args['level'].'; ';
	}else{
		// level is a requirement for this header
		return;
	}

	// Include if we wish to include block
	if( isset( $xss_protection_args['block'] ) &&
		true === $xss_protection_args['block'] ){
		$header = $header . 'mode=block';
	}

	return $header;
}

/**
 * Sets the X-CONTENT-TYPE-OPTIONS header
 * Default Args: nosniff
 *
 * Filter: headlock_x_content_type_options
 *
 * @return string
 */
function headlock_x_content_type_options() {
	// Set Default as nosniff
	$content_type_option = 'nosniff';

	// Filter arguments for development and testing
	$content_type_option = apply_filters( 'headlock_x_content_type_options', $content_type_option );

	return 'X-Content-Type-Options: '. $content_type_option;
}

/**
 * Sets the Referrer Policy
 * Default Args: strict-origin-when-cross-origin
 * Filter: headlock_referrer_policy to adjust default
 *
 * @return string
 */
function headlock_referrer_policy() {

	// Set Default as strict-origin-when-cross-origin
	$referrer_policy = 'strict-origin-when-cross-origin';
	// Filter arguments for development and testing
	$referrer_policy = apply_filters( 'headlock_referrer_policy', $referrer_policy );

	// Array containing current allowable types
	$referrer_policy_types = array(
		'no-referrer',
		'no-referrer-when-downgrade',
		'origin',
		'origin-when-cross-origin',
		'same-origin',
		'strict-origin',
		'strict-origin-when-cross-origin',
		'unsafe-url'
	);

	// Filter Policy Types should you wish to remove less strict options or add new ones
	$referrer_policy_types = apply_filters( 'headlock_referrer_policy_types', $referrer_policy_types );

	if( in_array( $referrer_policy, $referrer_policy_types, true ) ){
		return 'Referrer-Policy: '. $referrer_policy;
	}else{
		// Not a valid type, return without header
		return;
	}

}


function headlock_content_security_policy() {
	/*
	 * Note unlike other Headers this one defaults to reporting only!
	 * You need to configure for live
	 *
	 */
	$content_security_policy_args = array(
		'report_only' 	=> true, //Sets which header we are using
		'report_uri'  	=> false,
		'fetch' 		=> array(
			'default_src' 				=> 'self',
			'connect_src' 				=> false,
			'font-src'					=> false,
			'frame-src'					=> false,
			'img-src'					=> false,
			'manifest-src'  			=> false,
			'media-src'					=> false,
			'object-src' 				=> false,
			'script-src' 				=> false,
			'style-src'					=> false
		),
		'document' 		=> array(
			'base-uri'					=> false,
			'plugin-types' 				=> false,
			'sandbox'					=> false
		),
		'navigation' 	=> array(
			'form-action' 				=> false,
			'frame-ancestors' 			=> false,
		),
		'other' 		=> array(
			'block-all-mixed-content' 	=> false,
			'upgrade-insecure-requests' => false
		)
	);

	// Add Filter to define a Content Security Policy
	$content_security_policy_args = apply_filters( 'headlock_content_security_policy', $content_security_policy_args );

	$content_security_policy_sources = array(
		'self',
		'unsafe-eval',
		'unsafe-inline',
		'unsafe-hashes',
		'none',
		'strict-dynamic',
		'report-sampple',
		get_home_url()
	);

	// Use Filter to apply Nonce and Other Sources
	$content_security_policy_sources = apply_filters( 'headlock_content_security_policy_sources', $content_security_policy_sources );

	// Set which Content Security Header to use
	if( isset( $content_security_policy_args[ 'report_only' ] ) &&
		true === $content_security_policy_args[ 'report_only' ]
	){
		$header = 'Content-Security-Policy-Report-Only: ';
	}else{
		$header = 'Content-Security-Policy: ';
	}

	if( isset( $content_security_policy_args[ 'fetch' ] ) &&
		is_array( $content_security_policy_args[ 'fetch' ] )
	){
		$header = $header . _generate_csp_header(
								$content_security_policy_args[ 'fetch' ],
								$content_security_policy_sources
							);
	}

	if( isset( $content_security_policy_args[ 'document' ] ) &&
		is_array( $content_security_policy_args[ 'document' ] )
	){
		$header = $header . _generate_csp_header(
								$content_security_policy_args[ 'document' ],
								$content_security_policy_sources
							);
	}

	if( isset( $content_security_policy_args[ 'navigation' ] ) &&
		is_array( $content_security_policy_args[ 'navigation' ] )
	){
		$header = $header . _generate_csp_header(
								$content_security_policy_args[ 'navigation' ],
								$content_security_policy_sources
							);
	}

	if( isset( $content_security_policy_args[ 'other' ] ) &&
		is_array( $content_security_policy_args[ 'other' ] )
	){
		foreach( $content_security_policy_args[ 'other' ] as $policy => $value ){
			if( isset($value) && true === $value ){
				$header = $header . $policy. "; ";
			}
		}
	}

	if( isset($content_security_policy_args[ 'report_uri' ] ) &&
		filter_var( $content_security_policy_args[ 'report_uri' ], FILTER_VALIDATE_URL )
	){
		$header = $header ."report-uri ".$content_security_policy_args[ 'report_uri' ].";";
	}

	return $header;
}

/*
 * Helper function for generating CSP Headers from our Policies array
 *
 * @params array $policies, array $sources
 * @return string - Header String
 *
 */
function _generate_csp_header( array $policies, array $sources ){
		$header = '';
		foreach( $policies as $policy => $value ){
			$contents = '';

			//Check if we have a string and our String contains multiple sources
			if( is_string( $value ) && false !== strpos( $value,' ') ){
				$value = explode(' ', trim( $value ) );
			}

			// If array, then circle back on ourselves and do the sub array first
			if( is_array( $value ) ){
				$r = [];
				foreach( $value as $source ){
					$r[] = _clean_csp_values( $source, $sources );
				}
				$content = implode(' ', $r );
			}
			// Hopefully we have a single string
			else{
				$content = _clean_csp_values( $value, $sources );
			}

			// Is the Policy set, and is it in our sources list
			if( !empty( $content ) ) {
				$header = $header . $policy . " ". $content ."; " ;
			}
		}

		return $header;
}

/*
 * Helper function for validating and formatting values
 *
 * @params sting|null $value, array $sources
 * @return string - Formatted String
 *
 */
function _clean_csp_values( ?string $value, array $sources ){
	if( !empty( $value ) && in_array( $value, $sources, true ) ){
		if( false === filter_var( $value, FILTER_VALIDATE_URL ) ){
			$value = "'" .$value. "'";
		}
		return $value;
	}
	return;
}
