<?php
    if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();
    global $wpdb;
    $wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."co_oils" );
    $wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."co_acids" );
    $wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."co_oils_acids" );
    delete_option("Calc_Oil_Installed");
?>
