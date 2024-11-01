<?php
/**
 * WP Media File Type Manger
 *
 * Uninstalling WP Media File Type Manger deletes options.
 *
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

/* Delete all data created by plugin, such as mftype posts and any options that were added to the options table. */
$wpmftm_default_types = new WP_Query( array(
        'post_type'      => 'mftype',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'key'            => 'file_residential_type',
            'compare'        => '>=',
            'value'          => 'Defult'
        )
    )
);

if( ! empty( $wpmftm_default_types ) ) {
    foreach( $wpmftm_default_types->posts as $wpmftm_post ) {
        delete_post_meta( $wpmftm_post->ID, 'file_residential_type' );
    }
}

delete_option( 'seerox_wpmftm_activated' );
delete_option( 'seerox_wpmftm_deactivated' );

// Clear any cached data that has been removed
wp_cache_flush();
