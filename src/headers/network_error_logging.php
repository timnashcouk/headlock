<?php
// Direct access is pointless
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Sets the Network Error Logging
 * Default Args: none
 * Filter: headlock_nel
 * Note you need to have the REPORT-TO Header also set, to define the groups
 *
 * If testing recommend as an inital configuration
 * 	'max-age' => 60,
 *
 * @return string
 */
function headlock_nel() {
	$name       = 'NEL';
	$nel_policy = array();
	$nel_policy = apply_filters( 'headlock_nel', $nel_policy );
	if ( is_array( $nel_policy ) ) {
		// Check the name is already in the Report-To list
		if ( ! is_string( $nel_policy[0] ) || ! in_array( $nel_policy[0], headlock_available_report_groups(), true ) ) {
			_headlock_debug_helper(
				$name,
				wp_json_encode( $nel_policy[0] ) . 'Not found as a Report-to Group',
				$nel_policy
			);
			return false;
		}
		// Check max_age is an Integer
		if ( ! is_numeric( $nel_policy[1] ) ) {
			_headlock_debug_helper(
				$name,
				'max_age should be an int',
				$nel_policy
			);
			return false;
		}
		// Ok let's add the header
		$header = array(
			'report_to' => $nel_policy[0],
			'max_age'   => $nel_policy[1],
		);
		$header = wp_json_encode( $header );
		return 'NEL: ' . $header;
	} else {
		_headlock_debug_helper(
			$name,
			'Filter should be array',
			$nel_policy
		);
		return false;
	}
}
