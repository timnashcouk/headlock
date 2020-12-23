<?php
// Direct access is pointless
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
		'unsafe-url',
	);

	// Filter Policy Types should you wish to remove less strict options or add new ones
	$referrer_policy_types = apply_filters( 'headlock_referrer_policy_types', $referrer_policy_types );

	if ( in_array( $referrer_policy, $referrer_policy_types, true ) ) {
		return 'Referrer-Policy: ' . $referrer_policy;
	} else {
		// Not a valid type, return without header
		return;
	}

}
