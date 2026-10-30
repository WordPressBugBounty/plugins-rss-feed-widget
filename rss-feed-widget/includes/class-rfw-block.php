<?php
/**
 * Gutenberg block "RSS Feed" (server-side rendered, no build step needed).
 *
 * @package RSS_Feed_Widget
 */

defined( 'ABSPATH' ) || exit;

class RFW_Block {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		RFW_Renderer::register_assets();
		register_block_type(
			RFW_PATH . 'blocks/rss-feed',
			array(
				'style'           => 'rfw-frontend',
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	public static function render( $attributes ) {
		$html = RFW_Renderer::render( is_array( $attributes ) ? $attributes : array(), 'block' );
		if ( '' === $html ) {
			return '';
		}
		return '<div ' . get_block_wrapper_attributes() . '>' . $html . '</div>';
	}
}
