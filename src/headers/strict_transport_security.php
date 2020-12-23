<?php
// Direct access is pointless
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets the HSTS Security Header
 * Default Args: max-age 1 year, includesubdomains true, preload true.
 * Filter: headlock_strict_transport_security
 *
 * If testing recommend as an inital configuration
 *  'max-age'           => 30,
 *  'includeSubDomains' => false,
 *  'preload'           => false
 *
 * @return string
 */
function headlock_strict_transport_security() {
	// Standard Defaults for production
	$strict_transport_security_args = array(
		'max-age'           => 31536000, //1 Year
		'includeSubDomains' => true,
		'preload'           => true,
	);

	// Filter arguments for development and testing
	$strict_transport_security_args = apply_filters( 'headlock_strict_transport_security', $strict_transport_security_args );

	//Begin setting header string
	$header = 'strict-transport-security: ';

	// Check we have a max-age and it's a numeric in nature
	if ( isset( $strict_transport_security_args['max-age'] ) &&
		is_numeric( $strict_transport_security_args['max-age'] )
		) {
			$header = $header . 'max-age=' . $strict_transport_security_args['max-age'] . '; ';
	} else {
		// max-age is a requirement for this header
		return;
	}

	// Include if we wish to include sub domains
	if ( isset( $strict_transport_security_args['includeSubDomains'] ) &&
		true === $strict_transport_security_args['includeSubDomains'] ) {
		$header = $header . 'includeSubDomains; ';
	}

	// Include if we wish to include preload (this is a requirement for Browser preload)
	if ( isset( $strict_transport_security_args['preload'] ) &&
		true === $strict_transport_security_args['preload'] ) {
		$header = $header . 'preload';
	}

	return $header;
}
