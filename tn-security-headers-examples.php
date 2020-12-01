<?php
/**
 * Plugin Name:     Security Headers Examples
 * Plugin URI:      https://timnash.co.uk
 * Description:     Example for tn-security-headers framwork
 * Author:          Tim Nash
 * Author URI:      https://timnash.co.uk
 * Version:         0.2.0
 **/

 /*
  * Example of Enabling additional Security Headers
  *
  * @param array - Existing Headers
  * @return array - Security Headers enabled
  */
function tn_filter_enabled_security_header( $security_headers ){
    // Append our headers to the list
    array_push($security_headers, 'content-security-policy', 'strict-transport-security' );
    return $security_headers;
 }
add_filter( 'tn_enabled_security_headers', 'tn_filter_enabled_security_header', 1 );

 /*
  * Example of Modifying HSTS for Development environments
  * Sets HSTS to be just 60s and not include subdomain 
  * 
  * @param array - Existing Headers
  * @return array - Security Headers enabled
  */
function tn_filter_strict_transport_security( $hsts ){
    $hsts = array(
            'max-age' 			=> 60, 
            'includeSubDomains' => false,
            'preload' 			=> false
        );
   return $hsts;
}
add_filter( 'tn_strict_transport_security', 'tn_filter_strict_transport_security', 1 );

 /*
  * Example of Adding a new source to CSP
  * In this case we are allowing requests from https://timnash.co.uk to be available for use in the policy 
  * 
  * @param array - Existing Sources
  * @return array - Modified Source Array
  */
function tn_filter_content_security_policy_sources( $sources ){
    array_push($sources, 'https://timnash.co.uk' );
    return $sources;
}
add_filter( 'tn_content_security_policy_sources', 'tn_filter_content_security_policy_sources', 1 );

 /*
  * Example of Adding a new section to a CSP
  * In this case we are allowing scripts & fonts to run from https://timnash.co.uk or 'self' within our CSP 
  * https://timnash.co.uk will be filtered out if we haven't also modified our sources.
  * note self doesn't have '' wrapped, these are added.
  * 
  * @param array - Existing CSP
  * @return array - Modified CSP
  */
function tn_filter_content_security_policy( $csp ){
    // Example adding self, and https://timnash.co.uk to allowed scripts using Array
    $csp['fetch']['script-src'] = array('self','https://timnash.co.uk');
    // Example adding self, and https://timnash.co.uk to allowed for fonts using a string
    $csp['fetch']['font-src'] = 'self https://timnash.co.uk';

    return $csp;
}
add_filter( 'tn_content_security_policy', 'tn_filter_content_security_policy', 1 );

 /*
  * Example of using sha256 hash on an inline script
  * Please don't use it, its a terrible example
  * 
  */
class hashExample{
    //Our script and holder for hash
    private     $sha256;
    private     $script = '<script>
                        alert("Hello! I am an alert box!");
                        </script>';

    public function __construct(){
        // Hash the script
        $this->sha256 = 'sha256-'.hash( 'sha256', $this->script );
        // Add the hash to sources
        add_filter( 'tn_content_security_policy_sources', array( $this, 'content_security_policy_sources' ), 1 );
        // Add the hash to the policy
        add_filter( 'tn_content_security_policy', array( $this, 'content_security_policy' ), 1 );
    }

    public function content_security_policy_sources( $sources ){
        $sources[] = $this->sha256;
        return $sources;
    }

    public function content_security_policy( $csp ){
        $csp['fetch']['script-src'][] = $this->sha256;
        return $csp;
    }
}
new hashExample();