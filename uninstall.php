<?php
    if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();
    global $wpdb;
    $wpdb->query( "DROP TABLE IF EXISTS wp_calc_oils" );
    $wpdb->query( "DROP TABLE IF EXISTS wp_calc_oil_acids" );
    delete_option("Calc_Oil_Installed");
?>
