<?php
/**
 * Builds the front-end HTML. Everything printed is escaped here.
 *
 * Legacy class names (rfw-class, rfw_dock, rfw_slider, feed_img, text_div,
 * rfw2) are kept so existing custom CSS and Chameleon skins keep working.
 *
 * @package RSS_Feed_Widget
 */

defined( 'ABSPATH' ) || exit;

class RFW_Renderer {

	/**
	 * Register the front-end assets (safe to call repeatedly).
	 */
	public static function register_assets() {
		if ( ! wp_style_is( 'rfw-frontend', 'registered' ) ) {
			wp_register_style( 'rfw-frontend', RFW_URL . 'assets/css/frontend.css', array(), RFW_VERSION );
		}
		if ( ! wp_script_is( 'rfw-frontend', 'registered' ) ) {
			wp_register_script( 'rfw-frontend', RFW_URL . 'assets/js/frontend.js', array(), RFW_VERSION, true );
			wp_localize_script(
				'rfw-frontend',
				'rfwFront',
				array( 'goTo' => __( 'Go to slide', 'rss-feed-widget' ) )
			);
		}
	}

	private static function enqueue( $args ) {
		self::register_assets();
		wp_enqueue_style( 'rfw-frontend' );
		wp_enqueue_script( 'rfw-frontend' );

		// Optional Chameleon skin chosen in an older version.
		global $wpc_assets_loaded, $wpc_dir, $wpc_url;
		$style = sanitize_key( (string) get_option( 'rfw_style', '' ) );
		if ( $style && ! empty( $wpc_assets_loaded['rfw'][ $style ]['styles'][ $style ] ) && $wpc_dir && $wpc_url ) {
			$css = str_replace( $wpc_dir, $wpc_url, $wpc_assets_loaded['rfw'][ $style ]['styles'][ $style ] );
			wp_enqueue_style( 'rfw-' . $style . '-style', esc_url_raw( $css ), array( 'rfw-frontend' ), RFW_VERSION );
		}

		do_action( 'rfw_enqueue', $args );
	}

	/**
	 * A short message, shown only to people who can edit the site.
	 */
	private static function notice( $message ) {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return '';
		}
		return '<p class="rfw-notice">' . esc_html( 'RSS Feed Widget: ' . $message ) . '</p>';
	}

	/**
	 * Gather, filter, order and trim the items for one feed block.
	 *
	 * @param array $args   Sanitised arguments.
	 * @param array $errors Receives WP_Error objects from failed feeds.
	 * @return array
	 */
	public static function collect( $args, &$errors = array() ) {
		$items = array();
		foreach ( $args['urls'] as $url ) {
			$feed = RFW_Feed::fetch( $url, $args['rfw_cache'] );
			if ( is_wp_error( $feed ) ) {
				$errors[] = $feed;
				continue;
			}
			foreach ( $feed['items'] as $item ) {
				$item['feed_url'] = $url;
				$item['source']   = $feed['title'];
				$items[]          = $item;
			}
		}

		$items = apply_filters( 'rfw_feed_items', $items, $args );
		$items = self::order( $items, $args['content_order'], count( $args['urls'] ) > 1 );

		return array_slice( $items, 0, $args['number'] );
	}

	private static function order( $items, $order, $multi ) {
		switch ( $order ) {
			case 'aa':
				usort( $items, function ( $a, $b ) { return strcasecmp( $a['title'], $b['title'] ); } );
				break;
			case 'ad':
				usort( $items, function ( $a, $b ) { return strcasecmp( $b['title'], $a['title'] ); } );
				break;
			case 'date_aa':
				usort( $items, function ( $a, $b ) { return $a['date'] <=> $b['date']; } );
				break;
			case 'date_ad':
				usort( $items, function ( $a, $b ) { return $b['date'] <=> $a['date']; } );
				break;
			case 'rand':
				shuffle( $items );
				break;
			default:
				if ( $multi ) { // Several feeds: newest first so they interleave sensibly.
					usort( $items, function ( $a, $b ) { return $b['date'] <=> $a['date']; } );
				}
		}
		return $items;
	}

	/**
	 * Render a feed block.
	 *
	 * @param array  $raw     Widget instance, shortcode atts or block attributes.
	 * @param string $context widget|shortcode|block.
	 * @return string HTML (already escaped).
	 */
	public static function render( $raw, $context = 'shortcode' ) {
		$args = RFW_Settings::sanitize_args( $raw );

		if ( empty( $args['urls'] ) ) {
			if ( ! empty( $args['has_input'] ) ) {
				return self::notice( __( 'The feed URL is not valid. Facebook Page IDs are no longer supported; use a feed URL.', 'rss-feed-widget' ) );
			}
			return self::notice( __( 'Please add a feed URL.', 'rss-feed-widget' ) );
		}

		$errors = array();
		$items  = self::collect( $args, $errors );

		if ( empty( $items ) ) {
			if ( ! empty( $errors ) ) {
				return self::notice( $errors[0]->get_error_message() );
			}
			return self::notice( __( 'The feed has no items to show.', 'rss-feed-widget' ) );
		}

		self::enqueue( $args );

		$id      = wp_unique_id( 'rfw-feed-' );
		$layout  = $args['list_type'];
		$classes = array( 'rfw-feed', 'rfw-class', 'rfw-layout-' . $layout );
		$style   = sanitize_html_class( (string) get_option( 'rfw_style', '' ) );
		if ( '' !== $style ) {
			$classes[] = $style;
		}
		$classes = array_map( 'sanitize_html_class', apply_filters( 'rfw_wrapper_classes', $classes, $args ) );

		$attrs = array(
			'id'                => $id,
			'class'             => implode( ' ', array_filter( $classes ) ),
			'data-rfw-layout'   => $layout,
			'data-rfw-speed'    => $args['speed'],
			'data-rfw-pause'    => $args['pause'],
			'data-rfw-autoplay' => $args['autoplay'] ? '1' : '0',
		);
		$attrs = apply_filters( 'rfw_wrapper_attrs', $attrs, $args );

		$attr_html = '';
		foreach ( $attrs as $name => $value ) {
			$attr_html .= ' ' . sanitize_key( $name ) . '="' . esc_attr( $value ) . '"';
		}

		$list_style = '' !== $args['content_height'] ? ' style="height:' . esc_attr( $args['content_height'] ) . ';overflow-y:auto"' : '';

		$html = '<div' . $attr_html . '>';
		if ( 'widget' !== $context && '' !== $args['title'] ) {
			$html .= '<h3 class="widget-title rfw-title">' . esc_html( $args['title'] ) . '</h3>';
		}
		$html .= '<ul class="rfw_dock rfw_' . esc_attr( $layout ) . ' rfw-list"' . $list_style . '>';
		foreach ( $items as $item ) {
			$html .= '<li class="rfw-item">' . apply_filters( 'rfw_item_html', self::item_html( $item, $args ), $item, $args ) . '</li>';
		}
		$html .= '</ul></div>';

		return $html;
	}

	private static function link_open( $url, $args ) {
		$target = $args['new_tab'] ? ' target="_blank"' : '';
		return '<a href="' . esc_url( $url ) . '"' . $target . ' rel="nofollow noopener noreferrer">';
	}

	private static function item_html( $item, $args ) {
		$display    = $args['content_display'];
		$linked     = ( $args['keep_feed_link'] || 'linked_title' === $display ) && '' !== $item['link'];
		$show_title = ( $args['show_feed_title'] || in_array( $display, array( 'title_only', 'linked_title' ), true ) ) && '' !== $item['title'];
		$show_text  = in_array( $display, array( 'default', 'text_only' ), true );
		$show_image = in_array( $display, array( 'default', 'image_only' ), true );
		$out        = '';

		if ( $show_image ) {
			$image = self::pick_image( $item, $args['img_size'] );
			if ( '' !== $image ) {
				if ( is_ssl() ) {
					$image = set_url_scheme( $image, 'https' );
				}
				$img = '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $show_title ? '' : $item['title'] ) . '" loading="lazy" decoding="async" />';
				$out .= '<div class="feed_img rfw-item-image">' . ( $linked ? self::link_open( $item['link'], $args ) . $img . '</a>' : $img ) . '</div>';
			}
		}

		if ( $show_title ) {
			$tag   = tag_escape( apply_filters( 'rfw_title_tag', 'h3' ) );
			$title = $linked ? self::link_open( $item['link'], $args ) . esc_html( $item['title'] ) . '</a>' : esc_html( $item['title'] );
			$out  .= '<' . $tag . ' class="rfw2 rfw-item-title">' . $title . '</' . $tag . '>';
		}

		$meta = '';
		if ( $args['show_date'] && $item['date'] > 0 ) {
			$meta .= '<time class="rfw-item-date" datetime="' . esc_attr( gmdate( 'c', $item['date'] ) ) . '">' . esc_html( wp_date( get_option( 'date_format' ), $item['date'] ) ) . '</time> ';
		}
		if ( $args['show_source'] && '' !== $item['source'] ) {
			$meta .= '<span class="rfw-item-source">' . esc_html( $item['source'] ) . '</span>';
		}
		if ( '' !== $meta ) {
			$out .= '<div class="rfw-item-meta">' . $meta . '</div>';
		}

		if ( $show_text ) {
			$text = self::pick_text( $item, $args );
			if ( '' !== $text ) {
				$out .= '<div class="text_div rfw-item-text">' . esc_html( $text ) . '</div>';
			}
		}

		return $out;
	}

	private static function pick_image( $item, $pick ) {
		if ( '' !== $item['image'] ) {
			return $item['image'];
		}
		$images = $item['images'];
		if ( empty( $images ) ) {
			return '';
		}
		if ( 'large' === $pick ) {
			return end( $images );
		}
		$index = ( '2' === $pick ) ? 1 : 0;
		return isset( $images[ $index ] ) ? $images[ $index ] : $images[0];
	}

	private static function pick_text( $item, $args ) {
		switch ( $args['text_to_display'] ) {
			case 'content':
				$text = '' !== $item['content'] ? $item['content'] : $item['excerpt'];
				break;
			case 'description':
				$text = '' !== $item['excerpt'] ? $item['excerpt'] : $item['content'];
				break;
			default:
				$text = strlen( $item['content'] ) > strlen( $item['excerpt'] ) ? $item['content'] : $item['excerpt'];
		}

		$phrases = RFW_Settings::muted_phrases();
		if ( $phrases ) {
			$text = str_ireplace( $phrases, '', $text );
		}

		$censor = array();
		if ( '' !== $args['rfw_censorship'] ) {
			foreach ( explode( ',', $args['rfw_censorship'] ) as $word ) {
				$word = function_exists( 'mb_strtolower' ) ? mb_strtolower( trim( $word ) ) : strtolower( trim( $word ) );
				if ( '' !== $word ) {
					$censor[] = $word;
				}
			}
		}

		return self::limit_words( $text, $args['feed_words'], $censor );
	}

	private static function limit_words( $text, $limit, $censor ) {
		$text  = trim( str_replace( 'Read More', '', $text ) );
		$words = preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
		if ( empty( $words ) ) {
			return '';
		}
		if ( $censor ) {
			$words = array_values(
				array_filter(
					$words,
					function ( $word ) use ( $censor ) {
						$lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $word ) : strtolower( $word );
						foreach ( $censor as $bad ) {
							if ( 0 === strpos( $lower, $bad ) ) {
								return false;
							}
						}
						return true;
					}
				)
			);
		}
		$more  = count( $words ) > $limit;
		$words = array_slice( $words, 0, $limit );
		return implode( ' ', $words ) . ( $more ? '…' : '' );
	}
}
