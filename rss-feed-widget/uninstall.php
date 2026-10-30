<?php
/**
 * Removes plugin data when the plugin is deleted from the Plugins screen.
 *
 * @package RSS_Feed_Widget
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Delete this plugin's options and cached feeds on the current site.
 */
function rfw_uninstall_site() {
	global $wpdb;

	$options = array(
		'rfw_version',
		'rfw_default_cache',
		'rfw_cache_ver',
		'rfw_mutes',
		'rfw_rss_image_size',
		'rfw_sc_google_api_key',
		'rfw_sc_ids',
		'rfw_style',
		'rfw_custom_tag_patterns',
		'rfw_short_code_settings',
		'rfw_short_code_list',
		'rsfw_options',
		'rfw_pro_import',
		'rfw_pro_image_tag',
	);
	foreach ( $options as $option ) {
		delete_option( $option );
	}

	wp_clear_scheduled_hook( 'rfw_pro_import_event' );

	// Cached feeds and YouTube lookups (stored as transients).
	$like_a = $wpdb->esc_like( '_transient_rfw_' ) . '%';
	$like_b = $wpdb->esc_like( '_transient_timeout_rfw_' ) . '%';
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", $like_a, $like_b ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids', 'number' => 0 ) ) as $rfw_site_id ) {
		switch_to_blog( $rfw_site_id );
		rfw_uninstall_site();
		restore_current_blog();
	}
} else {
	rfw_uninstall_site();
}
