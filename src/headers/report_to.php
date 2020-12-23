<?php
// Direct access is pointless
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets the Report-To header
 * Default Args: none
 * Filter: headlock_reporting_groups to adjust default
 *
 * @return string
 */
function headlock_report_to() {
	$header        = array();
	$header_string = '';
	$groups        = _headlock_reporting_groups();
	if ( is_array( $groups ) && ! empty( $groups ) ) {
		foreach ( $groups as $group ) {
			$g              = array();
			$g['group']     = $group[0];
			$g['max_age']   = $group[1];
			$g['endpoints'] = $group[2];
			if ( isset( $group[3] ) ) {
				$g['include_subdomains'] = $group[3];
			}
			$header[] = $g;
		}
	}
	if ( ! empty( $header ) && is_array( $header ) ) {
		$header_string = wp_json_encode( $header );
		return 'Report-To: ' . $header_string;
	}
}

/*
 * Available Groups name
 * Public Helper function to get groups in other filters
 * @return mixed (array/false)
 */
function headlock_available_report_groups() {
	$available_groups = array();
	$groups           = _headlock_reporting_groups();
	foreach ( $groups as $group ) {
		$available_groups[] = $group[0];
	}
	if ( ! empty( $available_groups ) ) {
		return $available_groups;
	}
	return false;

}


/*
 * Helper function for setting and validating reporting groups
 *
 * @return mixed (array/false) - Multidimensional array or false if no groups available
 *
 */
function _headlock_reporting_groups() {
	$name   = 'REPORT-TO';
	$groups = array();
	/*
	 * Reporting API is fiddly but requires:
	 * group [string], max-age [int], endpoints [array], includesubdomain [bool]
	 *
	 */
	$groups[] = apply_filters( 'headlock_reporting_to', $groups );
	if ( is_array( $groups ) ) {
		foreach ( $groups as $key => $group ) {
			/*
			 * Validating the apply_filter
			 */
			if ( is_array( $group ) ) {
				if ( ! is_string( $group[0] ) ) {
					_headlock_debug_helper(
						$name,
						'Group name should be a string',
						$group
					);
					unset( $groups[ $key ] );
					continue;
				}
				if ( ! is_numeric( $group[1] ) ) {
					_headlock_debug_helper(
						$name,
						'max-age should be an int',
						$group
					);
					unset( $groups[ $key ] );
					continue;
				}
				if ( ! is_array( $group[2] ) ) {
					_headlock_debug_helper(
						$name,
						'Endpoints should be an array of endpoints',
						$group
					);
					unset( $groups[ $key ] );
					continue;
				}
				//Optionally check if subdomains is set this isn't required
				if ( isset( $group[3] ) && ! is_bool( $group[3] ) ) {
					//Remove this entry as it's not valid
					_headlock_debug_helper( $name, 'includeSubDomains is set but not a boolean' );
					unset( $groups[ $key ] );
					continue;
				}
				// Check the endpoints and validate
				foreach ( $group[2] as $endpoint ) {
					if ( is_array( $endpoint ) ) {
						if ( 2 === count( $endpoint )
						&& is_string( $endpoint[0] )
						&& is_string( $endpoint[1] )
						) {
							continue;
						} else {
							//Remove this entry as it's not valid
							_headlock_debug_helper( $name, 'Endpoint Pair is not valid', $endpoint );
							unset( $groups[ $key ] );
							continue;
						}
					} else {
						//Remove this entry as it's not valid
						_headlock_debug_helper( $name, 'Endpoint not an array', $endpoint );
						unset( $groups[ $key ] );
						continue;
					}
				}
			} else {
				//Remove this entry as it's not valid
				_headlock_debug_helper( $name, 'Group is not an array', $group );
				unset( $groups[ $key ] );
				continue;
			}
		}
	}
	if ( empty( $groups ) ) {
		return false;
	} else {
		return $groups;
	}
}
