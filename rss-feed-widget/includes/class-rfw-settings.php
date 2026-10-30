<?php
/**
 * Defaults, option access and argument sanitising. Every entry point
 * (widget, shortcode, block) runs its input through sanitize_args().
 *
 * @package RSS_Feed_Widget
 */

defined( 'ABSPATH' ) || exit;

class RFW_Settings {

	const MAX_ITEMS = 50;  // Most items shown by one feed.
	const MAX_FETCH = 50;  // Most items stored per cached feed.

	/**
	 * Layout choices. Pro adds more through the rfw_layouts filter.
	 *
	 * @return array
	 */
	public static function layouts() {
		return apply_filters(
			'rfw_layouts',
			array(
				'slider' => __( 'Slider', 'rss-feed-widget' ),
				'list'   => __( 'List', 'rss-feed-widget' ),
			)
		);
	}

	public static function displays() {
		return array(
			'default'      => __( 'Default (image + text)', 'rss-feed-widget' ),
			'title_only'   => __( 'Title only', 'rss-feed-widget' ),
			'linked_title' => __( 'Linked title only', 'rss-feed-widget' ),
			'text_only'    => __( 'Text only', 'rss-feed-widget' ),
			'image_only'   => __( 'Image only', 'rss-feed-widget' ),
		);
	}

	public static function orders() {
		return array(
			'default' => __( 'Feed order', 'rss-feed-widget' ),
			'date_ad' => __( 'Newest first', 'rss-feed-widget' ),
			'date_aa' => __( 'Oldest first', 'rss-feed-widget' ),
			'aa'      => __( 'Alphabetical A-Z', 'rss-feed-widget' ),
			'ad'      => __( 'Alphabetical Z-A', 'rss-feed-widget' ),
			'rand'    => __( 'Random', 'rss-feed-widget' ),
		);
	}

	public static function text_sources() {
		return array(
			'default'     => __( 'Longest available', 'rss-feed-widget' ),
			'content'     => __( 'Full content', 'rss-feed-widget' ),
			'description' => __( 'Description / summary', 'rss-feed-widget' ),
		);
	}

	public static function image_picks() {
		return array(
			'small' => __( 'Default (first image)', 'rss-feed-widget' ),
			'1'     => __( 'First image', 'rss-feed-widget' ),
			'2'     => __( 'Second image', 'rss-feed-widget' ),
			'large' => __( 'Last image', 'rss-feed-widget' ),
		);
	}

	/**
	 * Keys that are checkboxes in the widget form.
	 *
	 * @return array
	 */
	public static function bool_keys() {
		return array( 'show_feed_title', 'keep_feed_link', 'new_tab', 'show_date', 'show_source', 'autoplay' );
	}

	/**
	 * Default arguments. Pro adds keys through rfw_defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return apply_filters(
			'rfw_defaults',
			array(
				'title'           => '',
				'rss_url'         => '',
				'rss_more'        => '',
				'number'          => 6,
				'content_display' => 'default',
				'text_to_display' => 'default',
				'show_feed_title' => true,
				'keep_feed_link'  => true,
				'new_tab'         => true,
				'show_date'       => false,
				'show_source'     => false,
				'feed_words'      => 60,
				'content_height'  => '',
				'content_order'   => 'default',
				'list_type'       => 'slider',
				'speed'           => 500,
				'pause'           => 8000,
				'autoplay'        => true,
				'img_size'        => 'small',
				'rfw_cache'       => 0,
				'rfw_censorship'  => '',
			)
		);
	}

	/**
	 * Pick a value from an allowed list.
	 */
	private static function choice( $value, $allowed, $default ) {
		$value = is_scalar( $value ) ? (string) $value : '';
		return array_key_exists( $value, $allowed ) ? $value : $default;
	}

	/**
	 * Turn any supported input into a list of valid feed URLs. Accepts a
	 * string (one per line, whitespace or comma separated), an array, or the
	 * serialized array that older versions stored. Never unserializes objects.
	 *
	 * @param mixed $value Raw value.
	 * @return array
	 */
	public static function parse_urls( $value ) {
		if ( is_string( $value ) && is_serialized( $value ) ) {
			$value = @unserialize( $value, array( 'allowed_classes' => false ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}
		if ( is_string( $value ) ) {
			// Split on whitespace, or on a comma that is followed by a new URL.
			$value = preg_split( '/\s+|,(?=\s*https?:\/\/)/i', $value, -1, PREG_SPLIT_NO_EMPTY );
		}
		$out = array();
		foreach ( (array) $value as $url ) {
			if ( ! is_string( $url ) ) {
				continue;
			}
			$url = RFW_Feed::validate_url( rtrim( $url, ',' ) );
			if ( '' !== $url ) {
				$out[] = $url;
			}
		}
		return array_values( array_unique( $out ) );
	}

	/**
	 * Validate and normalise arguments from any source.
	 *
	 * @param mixed $raw Widget instance, shortcode atts or block attributes.
	 * @return array Clean arguments, including a derived "urls" array.
	 */
	public static function sanitize_args( $raw ) {
		$defaults = self::defaults();
		$raw      = wp_parse_args( is_array( $raw ) ? $raw : array(), $defaults );
		$clean    = array();

		$clean['title'] = sanitize_text_field( (string) $raw['title'] );

		$primary = self::parse_urls( $raw['rss_url'] );
		$more    = self::parse_urls( $raw['rss_more'] );
		if ( count( $primary ) > 1 ) { // Legacy multi-feed value stored in rss_url.
			$more    = array_merge( array_slice( $primary, 1 ), $more );
			$primary = array_slice( $primary, 0, 1 );
		}
		$all             = array_values( array_unique( array_merge( $primary, $more ) ) );
		$max             = max( 1, (int) apply_filters( 'rfw_max_feed_urls', 1 ) );
		$clean['urls']   = array_slice( $all, 0, $max );
		$clean['rss_url']  = isset( $primary[0] ) ? $primary[0] : '';
		$clean['rss_more'] = implode( "\n", $more );
		$clean['has_input'] = is_string( $raw['rss_url'] ) && '' !== trim( $raw['rss_url'] );

		$clean['number']          = min( self::MAX_ITEMS, max( 1, absint( $raw['number'] ) ) );
		$clean['content_display'] = self::choice( $raw['content_display'], self::displays(), 'default' );
		$clean['text_to_display'] = self::choice( $raw['text_to_display'], self::text_sources(), 'default' );
		$clean['content_order']   = self::choice( $raw['content_order'], self::orders(), 'default' );
		$clean['list_type']       = self::choice( $raw['list_type'], self::layouts(), 'slider' );
		$clean['img_size']        = self::choice( $raw['img_size'], self::image_picks(), 'small' );

		foreach ( self::bool_keys() as $key ) {
			$clean[ $key ] = wp_validate_boolean( $raw[ $key ] );
		}

		$words                  = absint( $raw['feed_words'] );
		$clean['feed_words']    = $words > 0 ? min( 500, $words ) : 60;

		$height = is_scalar( $raw['content_height'] ) ? trim( (string) $raw['content_height'] ) : '';
		$clean['content_height'] = preg_match( '/^\d{1,4}(\.\d+)?(px|em|rem|vh|%)?$/', $height ) ? ( ctype_digit( $height ) ? $height . 'px' : $height ) : '';

		$speed                 = absint( $raw['speed'] );
		$clean['speed']        = $speed ? min( 5000, max( 100, $speed ) ) : 500;
		$pause                 = absint( $raw['pause'] );
		$clean['pause']        = $pause ? min( 60000, max( 2000, $pause ) ) : 8000;
		$clean['rfw_cache']    = min( 30 * DAY_IN_SECONDS, absint( $raw['rfw_cache'] ) );
		$clean['rfw_censorship'] = sanitize_text_field( (string) $raw['rfw_censorship'] );

		return apply_filters( 'rfw_sanitize_args', $clean, $raw );
	}

	/* ---------------------------------------------------------------- *
	 * Global options
	 * ---------------------------------------------------------------- */

	public static function default_cache() {
		return max( 60, (int) get_option( 'rfw_default_cache', HOUR_IN_SECONDS ) );
	}

	public static function sanitize_cache( $value ) {
		$value = absint( $value );
		return $value > 0 ? min( WEEK_IN_SECONDS, max( 60, $value ) ) : HOUR_IN_SECONDS;
	}

	/**
	 * Global mute phrases, one per line.
	 *
	 * @return array
	 */
	public static function muted_phrases() {
		$raw = (string) get_option( 'rfw_mutes', '' );
		if ( '' === trim( $raw ) ) {
			return array();
		}
		return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ), 'strlen' ) );
	}

	public static function sanitize_image_size( $value ) {
		$value   = sanitize_key( $value );
		$allowed = array_merge( array( 'none', 'post-thumbnail' ), get_intermediate_image_sizes() );
		return in_array( $value, $allowed, true ) ? $value : 'post-thumbnail';
	}

	public static function sanitize_api_key( $value ) {
		return preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $value );
	}

	/**
	 * Bump the cache version. Old cached feeds are ignored and expire.
	 */
	public static function clear_cache() {
		update_option( 'rfw_cache_ver', time(), false );
	}
}
