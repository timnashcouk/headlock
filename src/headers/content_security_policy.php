<?php
// Direct access is pointless
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Sets the  Content Security Policy Header
 * Filter: headlock_content_security_policy
 *
 * @return string
 */
function headlock_content_security_policy() {
	/*
	 * Note unlike other Headers this one defaults to reporting only!
	 * You need to configure for live
	 *
	 */
	$content_security_policy_args = array(
		'report_only' => true, //Sets which header we are using
		'report_uri'  => false,
		'fetch'       => array(
			'default_src'  => 'self',
			'connect_src'  => false,
			'font-src'     => false,
			'frame-src'    => false,
			'img-src'      => false,
			'manifest-src' => false,
			'media-src'    => false,
			'object-src'   => false,
			'script-src'   => false,
			'style-src'    => false,
		),
		'document'    => array(
			'base-uri'     => false,
			'plugin-types' => false,
			'sandbox'      => false,
		),
		'navigation'  => array(
			'form-action'     => false,
			'frame-ancestors' => false,
		),
		'other'       => array(
			'block-all-mixed-content'   => false,
			'upgrade-insecure-requests' => false,
		),
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
		'report-sample',
		get_home_url(),
	);

	// Use Filter to apply Nonce and Other Sources
	$content_security_policy_sources = apply_filters( 'headlock_content_security_policy_sources', $content_security_policy_sources );

	// Set which Content Security Header to use
	if ( isset( $content_security_policy_args['report_only'] ) &&
		true === $content_security_policy_args['report_only']
	) {
		$header = 'Content-Security-Policy-Report-Only: ';
	} else {
		$header = 'Content-Security-Policy: ';
	}

	if ( isset( $content_security_policy_args['fetch'] ) &&
		is_array( $content_security_policy_args['fetch'] )
	) {
		$header = $header . _generate_csp_header(
			$content_security_policy_args['fetch'],
			$content_security_policy_sources
		);
	}

	if ( isset( $content_security_policy_args['document'] ) &&
		is_array( $content_security_policy_args['document'] )
	) {
		$header = $header . _generate_csp_header(
			$content_security_policy_args['document'],
			$content_security_policy_sources
		);
	}

	if ( isset( $content_security_policy_args['navigation'] ) &&
		is_array( $content_security_policy_args['navigation'] )
	) {
		$header = $header . _generate_csp_header(
			$content_security_policy_args['navigation'],
			$content_security_policy_sources
		);
	}

	if ( isset( $content_security_policy_args['other'] ) &&
		is_array( $content_security_policy_args['other'] )
	) {
		foreach ( $content_security_policy_args['other'] as $policy => $value ) {
			if ( isset( $value ) && true === $value ) {
				$header = $header . $policy . '; ';
			}
		}
	}

	if ( isset( $content_security_policy_args['report_uri'] ) &&
		filter_var( $content_security_policy_args['report_uri'], FILTER_VALIDATE_URL )
	) {
		$header = $header . 'report-uri ' . $content_security_policy_args['report_uri'] . ';';
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
function _generate_csp_header( array $policies, array $sources ) {
		$header = '';
	foreach ( $policies as $policy => $value ) {
		$contents = '';

		//Check if we have a string and our String contains multiple sources
		if ( is_string( $value ) && false !== strpos( $value, ' ' ) ) {
			$value = explode( ' ', trim( $value ) );
		}

		// If array, then circle back on ourselves and do the sub array first
		if ( is_array( $value ) ) {
			$r = array();
			foreach ( $value as $source ) {
				$r[] = _headlock_encode_values( $source, $sources );
			}
			$content = implode( ' ', $r );
		} else {
			// Hopefully we have a single string
			$content = _headlock_encode_values( $value, $sources );
		}

		// Is the Policy set, and is it in our sources list
		if ( ! empty( $content ) ) {
			$header = $header . $policy . ' ' . $content . '; ';
		}
	}

		return $header;
}
