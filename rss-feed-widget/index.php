<?php
/**
 * Plugin Name:       RSS Feed Widget
 * Plugin URI:        https://androidbubble.com/blog/wordpress/widgets/rss-feed-widget
 * Description:       Show any RSS or Atom feed as a clean list or an accessible slider, using a widget, a shortcode or a block. Cached, secure and lightweight.
 * Version:           4.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Fahad Mahmood
 * Author URI:        https://www.androidbubbles.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rss-feed-widget
 * Domain Path:       /languages
 *
 * RSS Feed Widget is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, either version 2 of the License, or any later version.
 *
 * @package RSS_Feed_Widget
 */

defined( 'ABSPATH' ) || exit;

define( 'RFW_VERSION', '4.0.0' );
define( 'RFW_FILE', __FILE__ );
define( 'RFW_PATH', plugin_dir_path( __FILE__ ) );
define( 'RFW_URL', plugin_dir_url( __FILE__ ) );

/*
 * Plug-and-play Pro: if the /pro directory (with pro-loader.php) exists, the
 * Pro features switch on. Remove the directory and the free features keep
 * working on their own. Nothing else in the plugin depends on it.
 */
define( 'RFW_PRO_LOADER', RFW_PATH . 'pro/pro-loader.php' );
define( 'RFW_HAS_PRO', is_readable( RFW_PRO_LOADER ) );

require_once RFW_PATH . 'includes/class-rfw-settings.php';
require_once RFW_PATH . 'includes/class-rfw-feed.php';
require_once RFW_PATH . 'includes/class-rfw-renderer.php';
require_once RFW_PATH . 'includes/class-rfw-widget.php';
require_once RFW_PATH . 'includes/class-rfw-shortcodes.php';
require_once RFW_PATH . 'includes/class-rfw-block.php';
require_once RFW_PATH . 'includes/class-rfw-plugin.php';

if ( RFW_HAS_PRO ) {
	require_once RFW_PRO_LOADER;
}

RFW_Plugin::init();

if ( is_admin() ) {
	require_once RFW_PATH . 'includes/class-rfw-admin.php';
	RFW_Admin::init();
}
