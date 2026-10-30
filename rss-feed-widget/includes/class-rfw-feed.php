<?php
/**
 * Fetches, normalises and caches feeds. Only plain, already-sanitised data is
 * cached, never SimplePie objects, so rendering can't introduce markup.
 *
 * @package RSS_Feed_Widget
 */

defined( 'ABSPATH' ) || exit;

class RFW_Feed {

	/**
	 * Validate a feed URL. http(s) only. With $strict the URL must also pass
	 * WordPress' SSRF checks (no private or reserved addresses).
	 *
	 * @param string $url    URL.
	 * @param bool   $strict Run wp_http_validate_url (does a DNS lookup).
	 * @return string Clean URL or ''.
	 */
	public static function validate_url( $url, $strict = false ) {
		if ( ! is_string( $url ) ) {
			return '';
		}
		$url = trim( $url );
		if ( 0 === stripos( $url, 'feed://' ) ) {
			$url = 'https://' . substr( $url, 7 );
		}
		$url = esc_url_raw( $url, array( 'http', 'https' ) );
		if ( '' === $url || ! wp_parse_url( $url, PHP_URL_HOST ) ) {
			return '';
		}
		if ( $strict && ! wp_http_validate_url( $url ) ) {
			return '';
		}
		return $url;
	}

	private static function cache_key( $url ) {
		return 'rfw_' . md5( get_option( 'rfw_cache_ver', 1 ) . '|' . $url );
	}

	/**
	 * Get a feed: array( title, items, fetched, url ) or WP_Error.
	 *
	 * A stale copy is served if a refresh fails, and failures are remembered
	 * for five minutes so a broken feed can't slow every page load.
	 *
	 * @param string $url   Feed URL.
	 * @param int    $ttl   Seconds to cache. 0 uses the global default.
	 * @param bool   $force Bypass the cache (used by the feed tester).
	 * @return array|WP_Error
	 */
	public static function fetch( $url, $ttl = 0, $force = false ) {
		$url = self::validate_url( $url, true );
		if ( '' === $url ) {
			return new WP_Error( 'rfw_bad_url', __( 'The feed URL is not valid or is not allowed.', 'rss-feed-widget' ) );
		}

		$ttl    = max( 60, $ttl > 0 ? (int) $ttl : RFW_Settings::default_cache() );
		$key    = self::cache_key( $url );
		$stored = get_transient( $key );

		if ( ! $force ) {
			if ( is_array( $stored ) && isset( $stored['fetched'] ) && ( $stored['fetched'] + $ttl ) > time() ) {
				return $stored;
			}
			$known_error = get_transient( $key . '_e' );
			if ( is_string( $known_error ) ) {
				return is_array( $stored ) ? $stored : new WP_Error( 'rfw_failed', $known_error );
			}
		}

		$fresh = self::download( $url, $force );

		if ( is_wp_error( $fresh ) ) {
			set_transient( $key . '_e', $fresh->get_error_message(), 5 * MINUTE_IN_SECONDS );
			return ( ! $force && is_array( $stored ) ) ? $stored : $fresh;
		}

		delete_transient( $key . '_e' );
		set_transient( $key, $fresh, WEEK_IN_SECONDS );
		return $fresh;
	}

	/**
	 * Download and normalise a feed.
	 *
	 * @return array|WP_Error
	 */
	private static function download( $url, $nocache ) {
		if ( ! function_exists( 'fetch_feed' ) ) {
			require_once ABSPATH . WPINC . '/feed.php';
		}

		// Options apply to this request only and are removed straight after.
		$options = function ( $feed ) use ( $nocache ) {
			$feed->force_feed( true ); // Tolerate feeds served with the wrong content type.
			$feed->set_timeout( 8 );
			if ( $nocache ) {
				$feed->enable_cache( false );
			}
		};
		$lifetime = function () {
			return 60; // Our own cache does the long-term work.
		};
		add_action( 'wp_feed_options', $options, 10, 1 );
		add_filter( 'wp_feed_cache_transient_lifetime', $lifetime );
		$feed = fetch_feed( $url );
		remove_action( 'wp_feed_options', $options, 10 );
		remove_filter( 'wp_feed_cache_transient_lifetime', $lifetime );

		if ( is_wp_error( $feed ) ) {
			return $feed;
		}

		$title = self::plain( (string) $feed->get_title() );
		$items = array();
		foreach ( (array) $feed->get_items( 0, RFW_Settings::MAX_FETCH ) as $rss_item ) {
			$item = self::normalize( $rss_item, $title );
			if ( $item ) {
				$items[] = $item;
			}
		}

		return array(
			'title'   => $title,
			'items'   => $items,
			'fetched' => time(),
			'url'     => $url,
		);
	}

	/**
	 * Plain text from HTML: tags removed, entities decoded, whitespace folded.
	 */
	public static function plain( $html ) {
		$text = wp_strip_all_tags( (string) $html, true );
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = wp_check_invalid_utf8( $text, true );
		$text = preg_replace( '/\s+/u', ' ', $text );
		return trim( (string) $text );
	}

	private static function cap( $text, $length ) {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $text, 0, $length );
		}
		return substr( $text, 0, $length );
	}

	private static function clean_image_url( $src ) {
		$src = html_entity_decode( trim( (string) $src ), ENT_QUOTES, 'UTF-8' );
		if ( 0 === strpos( $src, '//' ) ) {
			$src = 'https:' . $src;
		}
		return esc_url_raw( $src, array( 'http', 'https' ) );
	}

	private static function images_from_html( $html ) {
		$out = array();
		if ( '' === $html || false === stripos( $html, '<img' ) ) {
			return $out;
		}
		if ( preg_match_all( '/<img\b[^>]*?\bsrc\s*=\s*(["\'])(.*?)\1/is', $html, $matches ) ) {
			foreach ( $matches[2] as $src ) {
				$src = self::clean_image_url( $src );
				// Skip tracking pixels and spacers.
				if ( '' !== $src && ! preg_match( '#(feeds\.feedburner\.com/~[rf]/|/pixel|1x1|spacer\.)#i', $src ) ) {
					$out[] = $src;
				}
			}
		}
		return array_slice( array_values( array_unique( $out ) ), 0, 10 );
	}

	private static function enclosure_image( $rss_item ) {
		$enclosures = $rss_item->get_enclosures();
		if ( empty( $enclosures ) || ! is_array( $enclosures ) ) {
			return '';
		}
		foreach ( $enclosures as $enclosure ) {
			$link   = (string) $enclosure->get_link();
			$type   = strtolower( (string) $enclosure->get_type() );
			$medium = strtolower( (string) $enclosure->get_medium() );
			if ( '' !== $link && ( 0 === strpos( $type, 'image' ) || 'image' === $medium || preg_match( '/\.(jpe?g|png|gif|webp|avif)(\?.*)?$/i', $link ) ) ) {
				return self::clean_image_url( $link );
			}
			$thumbs = $enclosure->get_thumbnails();
			if ( is_array( $thumbs ) && ! empty( $thumbs ) ) {
				$thumb = reset( $thumbs );
				if ( is_string( $thumb ) && '' !== $thumb ) {
					return self::clean_image_url( $thumb );
				}
			}
		}
		return '';
	}

	/**
	 * Convert a SimplePie item into a plain array.
	 *
	 * @return array|null
	 */
	private static function normalize( $rss_item, $feed_title ) {
		$title = self::plain( (string) $rss_item->get_title() );
		$link  = esc_url_raw( (string) $rss_item->get_permalink(), array( 'http', 'https' ) );
		$desc_html    = (string) $rss_item->get_description();
		$content_html = (string) $rss_item->get_content();

		if ( '' === $title && '' === $link ) {
			return null;
		}

		$date = $rss_item->get_date( 'U' );

		$item = array(
			'id'      => md5( $link . '|' . $title ),
			'title'   => self::cap( $title, 300 ),
			'link'    => $link,
			'date'    => $date ? (int) $date : 0,
			'excerpt' => self::cap( self::plain( $desc_html ), 6000 ),
			'content' => self::cap( self::plain( $content_html ), 6000 ),
			'image'   => self::enclosure_image( $rss_item ),
			'images'  => self::images_from_html( $desc_html . ' ' . $content_html ),
			'source'  => $feed_title,
		);

		return apply_filters( 'rfw_normalize_item', $item, $rss_item );
	}
}
