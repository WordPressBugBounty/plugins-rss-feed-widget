<?php
/**
 * [rfw-feed-widget] and [rfw-youtube-videos].
 *
 * @package RSS_Feed_Widget
 */

defined( 'ABSPATH' ) || exit;

class RFW_Shortcodes {

	public static function init() {
		add_shortcode( 'rfw-feed-widget', array( __CLASS__, 'feed' ) );
		add_shortcode( 'rfw-youtube-videos', array( __CLASS__, 'youtube' ) );
	}

	/**
	 * [rfw-feed-widget rss_url="https://example.com/feed/" number="5"]
	 * Accepts every widget setting as an attribute.
	 */
	public static function feed( $atts ) {
		if ( is_admin() || is_feed() ) {
			return '';
		}
		// Older versions defaulted these two to "off" in shortcodes. Keep that.
		$defaults = array_merge(
			RFW_Settings::defaults(),
			array(
				'show_feed_title' => false,
				'keep_feed_link'  => false,
			)
		);
		$atts = shortcode_atts( $defaults, $atts, 'rfw-feed-widget' );
		return RFW_Renderer::render( $atts, 'shortcode' );
	}

	/* ---------------------------------------------------------------- *
	 * YouTube
	 * ---------------------------------------------------------------- */

	/**
	 * Turn one line from the settings page into a list of video IDs.
	 * Accepts video IDs, watch/short/embed URLs, and channel IDs (needs an API key).
	 */
	private static function resolve_line( $line, $limit ) {
		$line = trim( $line );
		if ( '' === $line ) {
			return array();
		}

		if ( preg_match( '#^https?://#i', $line ) ) {
			$host  = (string) wp_parse_url( $line, PHP_URL_HOST );
			$path  = (string) wp_parse_url( $line, PHP_URL_PATH );
			$query = (string) wp_parse_url( $line, PHP_URL_QUERY );
			parse_str( $query, $params );
			if ( isset( $params['v'] ) && is_string( $params['v'] ) ) {
				$line = $params['v'];
			} elseif ( preg_match( '#^/(?:embed/|shorts/|live/)?([A-Za-z0-9_-]{11})/?$#', $path, $m ) && false !== stripos( $host, 'youtu' ) ) {
				$line = $m[1];
			} elseif ( preg_match( '#^/channel/(UC[A-Za-z0-9_-]{22})#', $path, $m ) ) {
				$line = $m[1];
			}
		}

		if ( preg_match( '/^UC[A-Za-z0-9_-]{22}$/', $line ) ) {
			return self::channel_videos( $line, $limit );
		}
		if ( preg_match( '/^[A-Za-z0-9_-]{11}$/', $line ) ) {
			return array( $line );
		}
		return array();
	}

	/**
	 * Latest videos of a channel. Cached for six hours to protect API quota.
	 */
	private static function channel_videos( $channel_id, $limit ) {
		$key = (string) get_option( 'rfw_sc_google_api_key', '' );
		if ( '' === $key ) {
			return array();
		}
		$cache_key = 'rfw_yt_' . md5( get_option( 'rfw_cache_ver', 1 ) . $channel_id . $limit );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$url = add_query_arg(
			array(
				'key'        => $key,
				'channelId'  => $channel_id,
				'part'       => 'id',
				'type'       => 'video',
				'order'      => 'date',
				'maxResults' => min( 25, max( 1, (int) $limit ) ),
			),
			'https://www.googleapis.com/youtube/v3/search'
		);
		$response = wp_safe_remote_get( $url, array( 'timeout' => 8 ) );
		$ids      = array();
		if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
				foreach ( $data['items'] as $row ) {
					if ( isset( $row['id']['videoId'] ) && preg_match( '/^[A-Za-z0-9_-]{11}$/', $row['id']['videoId'] ) ) {
						$ids[] = $row['id']['videoId'];
					}
				}
			}
		}
		set_transient( $cache_key, $ids, empty( $ids ) ? 15 * MINUTE_IN_SECONDS : 6 * HOUR_IN_SECONDS );
		return $ids;
	}

	private static function css_length( $value ) {
		$value = trim( (string) $value );
		return preg_match( '/^\d{1,4}(\.\d+)?(px|em|rem|vh|vw|%)$/', $value ) ? $value : ( ctype_digit( $value ) && '' !== $value ? $value . 'px' : '' );
	}

	/**
	 * [rfw-youtube-videos number="3" width="560px" height="315px" bgcolor="#000000" margin="10px" ids="id1,id2"]
	 */
	public static function youtube( $atts ) {
		if ( is_admin() || is_feed() ) {
			return '';
		}
		$atts = shortcode_atts(
			array(
				'number'     => 3,
				'width'      => '',
				'height'     => '',
				'margin'     => '',
				'bgcolor'    => '',
				'ids'        => '',
				'fullscreen' => 'false',
			),
			$atts,
			'rfw-youtube-videos'
		);

		$limit = min( 25, max( 1, absint( $atts['number'] ) ) );

		$lines = '' !== trim( $atts['ids'] )
			? preg_split( '/[\s,]+/', $atts['ids'], -1, PREG_SPLIT_NO_EMPTY )
			: preg_split( '/\r\n|\r|\n/', (string) get_option( 'rfw_sc_ids', '' ), -1, PREG_SPLIT_NO_EMPTY );

		$videos = array();
		foreach ( (array) $lines as $line ) {
			foreach ( self::resolve_line( $line, $limit ) as $video_id ) {
				$videos[ $video_id ] = $video_id;
			}
		}
		if ( empty( $videos ) ) {
			return current_user_can( 'edit_theme_options' ) ? '<p class="rfw-notice">' . esc_html__( 'RSS Feed Widget: no valid YouTube videos found.', 'rss-feed-widget' ) . '</p>' : '';
		}

		RFW_Renderer::register_assets();
		wp_enqueue_style( 'rfw-frontend' );
		wp_enqueue_script( 'rfw-frontend' );

		$wrapper_style = '';
		$bg = sanitize_hex_color( $atts['bgcolor'] );
		if ( $bg ) {
			$wrapper_style = ' style="background-color:' . esc_attr( $bg ) . '"';
		}
		$inner = '';
		foreach ( array( 'width' => 'width', 'height' => 'height', 'margin' => 'margin' ) as $att => $prop ) {
			$len = self::css_length( $atts[ $att ] );
			if ( '' !== $len ) {
				$inner .= $prop . ':' . $len . ';';
			}
		}
		$inner_style = '' !== $inner ? ' style="' . esc_attr( $inner ) . '"' : '';
		$fullscreen  = wp_validate_boolean( $atts['fullscreen'] ) ? ' data-rfw-fullscreen="1"' : '';

		$html = '<div class="rfw-yt-items"' . $wrapper_style . $fullscreen . '>';
		foreach ( array_slice( array_values( $videos ), 0, 50 ) as $video_id ) {
			$html .= '<iframe' . $inner_style . ' src="' . esc_url( 'https://www.youtube-nocookie.com/embed/' . $video_id ) . '" title="' . esc_attr__( 'YouTube video player', 'rss-feed-widget' ) . '" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>';
		}
		return $html . '</div>';
	}
}
