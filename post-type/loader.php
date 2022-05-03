<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Test that DT_Module_Base has loaded
 */
if ( ! class_exists( 'DT_Module_Base' ) ) {
    dt_write_log( 'Disciple.Tools System not loaded. Cannot load custom post type.' );
    return;
}

/**
 * Add any modules required or added for the post type
 */
add_filter( 'dt_post_type_modules', function( $modules ){

    /**
     * @todo Update the starter in the array below 'prayer_request_base'. Follow the pattern.
     * @todo Add more modules by adding a new array element. i.e. 'prayer_request_base_two'.
     */
    $modules["prayer_request_base"] = [
        "name" => __( "Prayer Request", "disciple-tools-prayer-requests" ),
        "enabled" => true,
        "locked" => true,
        "prerequisites" => [ "contacts_base" ],
        "post_type" => "prayer_request",
        "description" => __( "Prayer Request", 'disciple-tools-prayer-requests' )
    ];

    return $modules;
}, 20, 1 );

require_once 'module-base.php';
Disciple_Tools_Prayer_Requests_Base::instance();

/**
 * @todo require_once and load additional modules
 */
