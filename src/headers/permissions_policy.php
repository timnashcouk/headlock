<?php
// Direct access is pointless
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function headlock_permissions_policy() {
	$name                       = 'Permissions Policy';
	$permissions                = array();
	$permissions                = apply_filters( 'headlock_permissions_policy', $permissions );
	$header                     = 'Permissions-Policy: ';
	$permissions_policy_sources = array(
		'',
		'self',
		'*',
		get_home_url(),
	);

	// Use Filter to apply third party site
	$permissions_policy_sources = apply_filters( 'headlock_permissions_policy_sources', $permissions_policy_sources );
	if ( is_array( $permissions ) ) {
		$header = $header . _generate_permission_header( $permissions, $permissions_policy_sources );
		//trim the whitespace, and then the last , shouldn't cause any issues.
		return rtrim( rtrim( $header ), ',' );
	} else {
		_headlock_debug_helper(
			$name,
			'Permissions should be array',
			$permissions
		);
		return false;
	}
}

/*
 * Helper function for generating Permission Policy Headers from our Policies array
 *
 * @params array $policies, array $sources
 * @return string - Header String
 *
 */
function _generate_permission_header( array $policies, array $sources ) {
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
				$r[] = _headlock_permission_encode_values( $source, $sources );
			}
			$content = implode( ' ', $r );
		} else {
			// Hopefully we have a single string
			$content = _headlock_permission_encode_values( $value, $sources );
		}

		// Is the Policy set, and is it in our sources list
		if ( '*' !== $content ) {
			$header = $header . $policy . '(' . $content . '), ';
		} else {
			$header = $header . $policy . ' ' . $content . ', ';
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
function _headlock_permission_encode_values( ?string $value, array $sources ) {
	if ( false === $value || 'none' === $value ) {
		return '';
	}
	if ( ! empty( $value ) && in_array( $value, $sources, true ) ) {
		if ( '' === $value || '*' === $value || 'self' === $value ) {
			return $value;
		}
		if ( false === filter_var( $value, FILTER_VALIDATE_URL ) ) {
			return "'" . $value . "'";
		}
		return '"' . $value . '"';
	}
}
