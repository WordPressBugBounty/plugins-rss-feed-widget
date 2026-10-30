<?php
/**
 * The classic widget. The ID base stays "rfw_dock" so existing widgets keep
 * their settings after the update.
 *
 * @package RSS_Feed_Widget
 */

defined( 'ABSPATH' ) || exit;

class RFW_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'rfw_dock',
			__( 'RSS Feed Widget', 'rss-feed-widget' ),
			array(
				'description'           => __( 'Show an RSS or Atom feed as a list or slider.', 'rss-feed-widget' ),
				'show_instance_in_rest' => true,
			)
		);
	}

	public function widget( $args, $instance ) {
		$html = RFW_Renderer::render( $instance, 'widget' );
		if ( '' === $html ) {
			return;
		}
		$title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) : '';

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme-provided markup.
		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in RFW_Renderer.
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function update( $new_instance, $old_instance ) {
		$new = is_array( $new_instance ) ? $new_instance : array();
		foreach ( RFW_Settings::bool_keys() as $key ) {
			$new[ $key ] = ! empty( $new[ $key ] ); // Unticked boxes are absent from the POST.
		}
		$clean = RFW_Settings::sanitize_args( $new );
		unset( $clean['urls'], $clean['has_input'] ); // Derived at render time.
		return $clean;
	}

	public function form( $instance ) {
		$i = RFW_Settings::sanitize_args( $instance );

		$this->field_text( 'rss_url', __( 'Feed URL', 'rss-feed-widget' ), $i['rss_url'], 'url', 'placeholder="https://example.com/feed/"' );
		$this->field_text( 'title', __( 'Title (optional)', 'rss-feed-widget' ), $i['title'] );
		$this->field_text( 'number', __( 'Number of items', 'rss-feed-widget' ), $i['number'], 'number', 'min="1" max="' . (int) RFW_Settings::MAX_ITEMS . '"' );
		$this->field_select( 'list_type', __( 'Layout', 'rss-feed-widget' ), $i['list_type'], RFW_Settings::layouts() );
		$this->field_select( 'content_display', __( 'Display', 'rss-feed-widget' ), $i['content_display'], RFW_Settings::displays() );
		$this->field_check( 'show_feed_title', __( 'Show item titles', 'rss-feed-widget' ), $i['show_feed_title'] );
		$this->field_check( 'keep_feed_link', __( 'Link items to the original', 'rss-feed-widget' ), $i['keep_feed_link'] );
		$this->field_check( 'new_tab', __( 'Open links in a new tab', 'rss-feed-widget' ), $i['new_tab'] );
		$this->field_check( 'show_date', __( 'Show date', 'rss-feed-widget' ), $i['show_date'] );
		$this->field_check( 'show_source', __( 'Show feed name', 'rss-feed-widget' ), $i['show_source'] );
		$this->field_text( 'feed_words', __( 'Words to show', 'rss-feed-widget' ), $i['feed_words'], 'number', 'min="1" max="500"' );
		$this->field_select( 'content_order', __( 'Sort order', 'rss-feed-widget' ), $i['content_order'], RFW_Settings::orders() );

		echo '<details><summary><strong>' . esc_html__( 'Advanced', 'rss-feed-widget' ) . '</strong></summary>';
		$this->field_select( 'text_to_display', __( 'Text comes from', 'rss-feed-widget' ), $i['text_to_display'], RFW_Settings::text_sources() );
		$this->field_select( 'img_size', __( 'Image pick', 'rss-feed-widget' ), $i['img_size'], RFW_Settings::image_picks() );
		$this->field_text( 'content_height', __( 'Height (e.g. 400px, empty = auto)', 'rss-feed-widget' ), $i['content_height'] );
		$this->field_check( 'autoplay', __( 'Slider: autoplay', 'rss-feed-widget' ), $i['autoplay'] );
		$this->field_text( 'speed', __( 'Slider: transition (ms)', 'rss-feed-widget' ), $i['speed'], 'number', 'min="100" max="5000"' );
		$this->field_text( 'pause', __( 'Slider: time per slide (ms)', 'rss-feed-widget' ), $i['pause'], 'number', 'min="2000" max="60000"' );
		$this->field_text( 'rfw_cache', __( 'Cache (seconds, 0 = site default)', 'rss-feed-widget' ), $i['rfw_cache'], 'number', 'min="0"' );
		$this->field_text( 'rfw_censorship', __( 'Hide words starting with (comma separated)', 'rss-feed-widget' ), $i['rfw_censorship'] );
		echo '</details>';

		/**
		 * Lets Pro add its own fields to the widget form.
		 */
		do_action( 'rfw_widget_form', $this, $i );
	}

	/* Field helpers (public so Pro can reuse them). */

	public function field_text( $key, $label, $value, $type = 'text', $extra = '' ) {
		printf(
			'<p><label for="%1$s">%2$s</label><input class="widefat" id="%1$s" name="%3$s" type="%4$s" value="%5$s" %6$s /></p>',
			esc_attr( $this->get_field_id( $key ) ),
			esc_html( $label ),
			esc_attr( $this->get_field_name( $key ) ),
			esc_attr( $type ),
			esc_attr( $value ),
			$extra // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static strings from this plugin.
		);
	}

	public function field_textarea( $key, $label, $value, $placeholder = '' ) {
		printf(
			'<p><label for="%1$s">%2$s</label><textarea class="widefat" rows="3" id="%1$s" name="%3$s" placeholder="%5$s">%4$s</textarea></p>',
			esc_attr( $this->get_field_id( $key ) ),
			esc_html( $label ),
			esc_attr( $this->get_field_name( $key ) ),
			esc_textarea( $value ),
			esc_attr( $placeholder )
		);
	}

	public function field_select( $key, $label, $value, $options ) {
		printf(
			'<p><label for="%1$s">%2$s</label><select class="widefat" id="%1$s" name="%3$s">',
			esc_attr( $this->get_field_id( $key ) ),
			esc_html( $label ),
			esc_attr( $this->get_field_name( $key ) )
		);
		foreach ( $options as $option_value => $option_label ) {
			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $option_value ),
				selected( (string) $value, (string) $option_value, false ),
				esc_html( $option_label )
			);
		}
		echo '</select></p>';
	}

	public function field_check( $key, $label, $value ) {
		printf(
			'<p><input type="checkbox" class="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> <label for="%1$s">%4$s</label></p>',
			esc_attr( $this->get_field_id( $key ) ),
			esc_attr( $this->get_field_name( $key ) ),
			checked( ! empty( $value ), true, false ),
			esc_html( $label )
		);
	}
}
