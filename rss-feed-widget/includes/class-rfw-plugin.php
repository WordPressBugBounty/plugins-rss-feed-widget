<?php
/**
 * Bootstrap: hooks, upgrade routine and the optional featured image in the
 * site's own RSS feed.
 *
 * @package RSS_Feed_Widget
 */

defined( 'ABSPATH' ) || exit;

class RFW_Plugin {

	public static function init() {
		add_action( 'widgets_init', array( __CLASS__, 'register_widget' ) );
		add_action( 'wp_enqueue_scripts', array( 'RFW_Renderer', 'register_assets' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_upgrade' ) );

		RFW_Shortcodes::init();
		RFW_Block::init();

		// Featured image in this site's own feeds (existing behaviour, now optional).
		add_filter( 'the_excerpt_rss', array( __CLASS__, 'feed_thumbnail' ) );
		add_filter( 'the_content_feed', array( __CLASS__, 'feed_thumbnail' ) );
	}

	public static function register_widget() {
		register_widget( 'RFW_Widget' );
	}

	/**
	 * Runs once per version. Keeps all existing options and widgets.
	 */
	public static function maybe_upgrade() {
		if ( get_option( 'rfw_version' ) === RFW_VERSION ) {
			return;
		}
		if ( false === get_option( 'rfw_cache_ver', false ) ) {
			add_option( 'rfw_cache_ver', 1, '', 'no' );
		}
		// Anything cached by an older version is ignored from now on.
		RFW_Settings::clear_cache();
		update_option( 'rfw_version', RFW_VERSION, false );
	}

	/**
	 * Prepend the featured image to items in this site's RSS feed.
	 * Choose "none" on the Images tab to turn it off.
	 */
	public static function feed_thumbnail( $content ) {
		$size = (string) get_option( 'rfw_rss_image_size', 'post-thumbnail' );
		if ( 'none' === $size ) {
			return $content;
		}
		$post = get_post();
		if ( $post && has_post_thumbnail( $post->ID ) ) {
			$content = get_the_post_thumbnail( $post->ID, '' === $size ? 'post-thumbnail' : $size, array( 'style' => 'float:left; margin:0 15px 15px 0;' ) ) . $content;
		}
		return $content;
	}
}
