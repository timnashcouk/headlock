<?php
// Direct access is pointless
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
