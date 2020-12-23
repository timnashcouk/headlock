<?php
// Direct access is pointless
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
