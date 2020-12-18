# Headlock - Security Headers
Contributors: tnash
Tags: Security
Requires: 5.5

**This is framework not a drop in and work plugin**
Headlock provides some basic out of the box security headers and lots of additional customisation options.
Note this has no GUI and is meant to be customised via add_filter option

Out of the box it provides sane defaults for:
- x-frame-options
- x-xss-protection
- x-content-type-options
- referrer-policy

With optional support for
- HSTS
- Content Security Policy

## Installation
Download and activate as normal WordPress Plugin
Note this repo also includes a tn-security-headers-examples plugin as well in the same folder, you will most likely want to move that to another folder.

You will need to configure the plugin, for anything but the most basic security headers

## Configuration

The headlock-examples.php plugin has a more detailed set of examples, but common things you might want to do:

To enable HSTS Header and CSP you will need to:
```
function headlock_filter_enabled_security_header( $security_headers ){
    array_push($security_headers, 'content-security-policy', 'strict-transport-security' );
    return $security_headers;
 }
add_filter( 'headlock_enabled_security_headers', 'headlock_filter_enabled_security_header', 1 );
```

Each filter has a default policy which should be a _reasonable_ default in production with the exception of Content Security Policy which is set to be REPORT only.

### Testing HSTS
For development sites, staging and preparing for live sites, you will want to set more safe defaults for HSTS I recommend the following:

```
function headlock_filter_strict_transport_security( $hsts ){
    $hsts = array(
            'max-age' 			=> 60,
            'includeSubDomains' => false,
            'preload' 			=> false
        );
   return $hsts;
}
add_filter( 'headlock_strict_transport_security', 'headlock_filter_strict_transport_security', 1 );
```

Which will allow you to confirm it is working, without accidentally, making a site not running over HTTPS inaccessible for a year.

Remember ONLY enable HSTS on sites with HTTPS and if including subdomains ALL Subdomains will need to be over HTTPS including ones like dev.

## Filters & Actions
The following Filters and actions are available

### Global
#### Actions
 - *headlock_security_headers* Runs post applying individual header, contains name of header ran, and the final string.
#### Filters
- *headlock_enabled_security_headers* Filters an array of enabled security headers, to add or remove

### Per Security Header
Each security header can be filtered all filters start headlock_ with each filter matching the headers name with - (dash) replace with _ (underscore).
#### Actions
 - *headlock_{specific_header}_additional* Runs post applying the individual header for example `headlock_content_security_policy_additional` would run after processing Content Security Policy header
#### Filters
 - *headlock_s{specific_header}* Provides a filter for that headers specific outputs as an array for example `headlock_strict_transport_security` provides filter of options for HSTS header.
 - *headlock_s{specific_header}_output* Provides a filter for that headers final string output.

 #### Specific security headers
 - *headlock_referrer_policy_types* Provides a filter of the different referrer policy types, that are validated against
 - *headlock_content_security_policy_sources* Provides a filter of the different sources that can be applied to a content security policy.
