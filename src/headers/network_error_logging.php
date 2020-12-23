<?php
// Direct access is pointless
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    NEL: { "report_to": "nel",
       "max_age": 31556952 }
*/
function headlock_nel(){
    $name = "NEL";
    $nel_policy = array();
    $nel_policy = apply_filters( 'headlock_nel', $nel_policy );
    if( is_array( $nel_policy ) ){
        // Check the name is already in the Report-To list
        if( !is_string($nel_policy[0]) || !in_array($nel_policy[0], headlock_available_report_groups() ) ){
            _headlock_debug_helper(
                $name,
                json_encode($nel_policy[0]).'Not found as a Report-to Group',
                $nel_policy
            );
            return false;
        }
        // Check max_age is an Integer
        if( !is_numeric( $nel_policy[1] ) ){
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
            'max_age'   => $nel_policy[1]
        );
        $header = json_encode( $header );
        return 'NEL: '.$header;
    }else{
        _headlock_debug_helper(
            $name,
            'Filter should be array',
            $nel_policy
        );
        return false;
    }
}
