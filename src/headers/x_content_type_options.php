<?php
// Direct access is pointless
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

	return 'X-Content-Type-Options: ' . $content_type_option;
}
