<?php
/**
 * Settings screen (Settings > RSS Feed Widget). Uses the Settings API, so
 * every save is nonce- and capability-checked by WordPress itself.
 *
 * @package RSS_Feed_Widget
 */

defined( 'ABSPATH' ) || exit;

class RFW_Admin {

	const PAGE = 'rfw_options';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( RFW_FILE ), array( __CLASS__, 'action_links' ) );
		add_action( 'wp_ajax_rfw_test_feed', array( __CLASS__, 'ajax_test_feed' ) );
		add_action( 'admin_post_rfw_clear_cache', array( __CLASS__, 'handle_clear_cache' ) );
	}

	public static function menu() {
		add_options_page(
			__( 'RSS Feed Widget', 'rss-feed-widget' ),
			__( 'RSS Feed Widget', 'rss-feed-widget' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	public static function register_settings() {
		register_setting( 'rfw_general', 'rfw_default_cache', array( 'type' => 'integer', 'sanitize_callback' => array( 'RFW_Settings', 'sanitize_cache' ), 'default' => HOUR_IN_SECONDS ) );
		register_setting( 'rfw_filters', 'rfw_mutes', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field', 'default' => '' ) );
		register_setting( 'rfw_images', 'rfw_rss_image_size', array( 'type' => 'string', 'sanitize_callback' => array( 'RFW_Settings', 'sanitize_image_size' ), 'default' => 'post-thumbnail' ) );
		register_setting( 'rfw_youtube', 'rfw_sc_google_api_key', array( 'type' => 'string', 'sanitize_callback' => array( 'RFW_Settings', 'sanitize_api_key' ), 'default' => '' ) );
		register_setting( 'rfw_youtube', 'rfw_sc_ids', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field', 'default' => '' ) );
	}

	public static function assets( $hook ) {
		if ( 'settings_page_' . self::PAGE !== $hook ) {
			return;
		}
		wp_enqueue_style( 'rfw-admin', RFW_URL . 'assets/css/admin.css', array(), RFW_VERSION );
		wp_enqueue_script( 'rfw-admin', RFW_URL . 'assets/js/admin.js', array(), RFW_VERSION, true );
		wp_localize_script(
			'rfw-admin',
			'rfwAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'rfw_admin' ),
				'i18n'    => array(
					'testing'  => __( 'Testing…', 'rss-feed-widget' ),
					'failed'   => __( 'The test could not be completed.', 'rss-feed-widget' ),
					'feed'     => __( 'Feed title', 'rss-feed-widget' ),
					'items'    => __( 'Items found', 'rss-feed-widget' ),
					'latest'   => __( 'Latest items', 'rss-feed-widget' ),
					'copied'   => __( 'Copied!', 'rss-feed-widget' ),
					'copy'     => __( 'Copy shortcode', 'rss-feed-widget' ),
				),
			)
		);
	}

	public static function action_links( $links ) {
		$mine = array( '<a href="' . esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'Settings', 'rss-feed-widget' ) . '</a>' );
		if ( ! RFW_HAS_PRO ) {
			$mine[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=' . self::PAGE . '&tab=pro' ) ) . '">' . esc_html__( 'Go Pro', 'rss-feed-widget' ) . '</a>';
		}
		return array_merge( $mine, $links );
	}

	/* ---------------------------------------------------------------- *
	 * Actions
	 * ---------------------------------------------------------------- */

	public static function ajax_test_feed() {
		check_ajax_referer( 'rfw_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rss-feed-widget' ) ), 403 );
		}
		$url  = isset( $_POST['url'] ) ? RFW_Feed::validate_url( wp_unslash( $_POST['url'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated by RFW_Feed::validate_url().
		$feed = RFW_Feed::fetch( $url, 0, true );
		if ( is_wp_error( $feed ) ) {
			wp_send_json_error( array( 'message' => $feed->get_error_message() ) );
		}
		wp_send_json_success(
			array(
				'title'  => $feed['title'],
				'count'  => count( $feed['items'] ),
				'titles' => array_map( 'strval', array_slice( wp_list_pluck( $feed['items'], 'title' ), 0, 5 ) ),
			)
		);
	}

	public static function handle_clear_cache() {
		check_admin_referer( 'rfw_clear_cache' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'rss-feed-widget' ), 403 );
		}
		RFW_Settings::clear_cache();
		wp_safe_redirect( add_query_arg( array( 'page' => self::PAGE, 'tab' => 'tools', 'cache-cleared' => 1 ), admin_url( 'options-general.php' ) ) );
		exit;
	}

	/* ---------------------------------------------------------------- *
	 * Page
	 * ---------------------------------------------------------------- */

	private static function tabs() {
		$tabs = array(
			'general'   => __( 'General', 'rss-feed-widget' ),
			'shortcode' => __( 'Shortcode builder', 'rss-feed-widget' ),
			'filters'   => __( 'Filters', 'rss-feed-widget' ),
			'images'    => __( 'Images', 'rss-feed-widget' ),
			'youtube'   => __( 'YouTube', 'rss-feed-widget' ),
			'tools'     => __( 'Tools', 'rss-feed-widget' ),
			'pro'       => RFW_HAS_PRO ? __( 'Pro', 'rss-feed-widget' ) : __( 'Go Pro', 'rss-feed-widget' ),
		);
		return apply_filters( 'rfw_admin_tabs', $tabs );
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'rss-feed-widget' ), 403 );
		}
		$tabs   = self::tabs();
		$active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only.
		if ( ! isset( $tabs[ $active ] ) ) {
			$active = 'general';
		}

		echo '<div class="wrap rfw-admin">';
		echo '<h1>' . esc_html__( 'RSS Feed Widget', 'rss-feed-widget' ) . ' <span class="rfw-version">' . esc_html( RFW_VERSION ) . ( RFW_HAS_PRO ? ' Pro' : '' ) . '</span></h1>';

		if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'rss-feed-widget' ) . '</p></div>';
		}
		if ( isset( $_GET['cache-cleared'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Feed cache cleared.', 'rss-feed-widget' ) . '</p></div>';
		}

		echo '<nav class="nav-tab-wrapper">';
		foreach ( $tabs as $slug => $label ) {
			$url = add_query_arg( array( 'page' => self::PAGE, 'tab' => $slug ), admin_url( 'options-general.php' ) );
			printf( '<a href="%s" class="nav-tab%s">%s</a>', esc_url( $url ), $slug === $active ? ' nav-tab-active' : '', esc_html( $label ) );
		}
		echo '</nav><div class="rfw-panel">';

		$method = 'tab_' . $active;
		if ( method_exists( __CLASS__, $method ) ) {
			self::$method();
		} else {
			do_action( 'rfw_admin_tab_' . $active );
		}
		echo '</div></div>';
	}

	private static function form_open( $group ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'options.php' ) ) . '">';
		settings_fields( $group );
	}

	private static function form_close() {
		submit_button();
		echo '</form>';
	}

	private static function tab_general() {
		$cache = RFW_Settings::default_cache();
		$steps = array(
			900   => __( '15 minutes', 'rss-feed-widget' ),
			1800  => __( '30 minutes', 'rss-feed-widget' ),
			3600  => __( '1 hour', 'rss-feed-widget' ),
			10800 => __( '3 hours', 'rss-feed-widget' ),
			21600 => __( '6 hours', 'rss-feed-widget' ),
			43200 => __( '12 hours', 'rss-feed-widget' ),
			86400 => __( '24 hours', 'rss-feed-widget' ),
		);
		?>
		<h2><?php esc_html_e( 'Getting started', 'rss-feed-widget' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'Widget: Appearance > Widgets, add "RSS Feed Widget" and paste a feed URL.', 'rss-feed-widget' ); ?></li>
			<li><?php esc_html_e( 'Block: in the editor, add the "RSS Feed" block.', 'rss-feed-widget' ); ?></li>
			<li><?php esc_html_e( 'Shortcode: use the Shortcode builder tab to generate one.', 'rss-feed-widget' ); ?></li>
		</ol>
		<?php self::form_open( 'rfw_general' ); ?>
		<table class="form-table" role="presentation"><tr>
			<th scope="row"><label for="rfw_default_cache"><?php esc_html_e( 'Default cache time', 'rss-feed-widget' ); ?></label></th>
			<td>
				<select id="rfw_default_cache" name="rfw_default_cache">
					<?php foreach ( $steps as $seconds => $label ) : ?>
						<option value="<?php echo esc_attr( $seconds ); ?>" <?php selected( $cache, $seconds ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
					<?php if ( ! isset( $steps[ $cache ] ) ) : ?>
						<option value="<?php echo esc_attr( $cache ); ?>" selected="selected"><?php echo esc_html( sprintf( /* translators: %d: seconds */ __( '%d seconds', 'rss-feed-widget' ), $cache ) ); ?></option>
					<?php endif; ?>
				</select>
				<p class="description"><?php esc_html_e( 'How long a feed is kept before it is fetched again. If a feed is down, the last good copy keeps being shown.', 'rss-feed-widget' ); ?></p>
			</td>
		</tr></table>
		<?php
		self::form_close();
	}

	private static function tab_shortcode() {
		$fields = array(
			array( 'rss_url', __( 'Feed URL', 'rss-feed-widget' ), 'text', '', '' ),
			array( 'number', __( 'Number of items', 'rss-feed-widget' ), 'number', '6', '6' ),
			array( 'feed_words', __( 'Words to show', 'rss-feed-widget' ), 'number', '60', '60' ),
			array( 'content_height', __( 'Height (e.g. 400px)', 'rss-feed-widget' ), 'text', '', '' ),
			array( 'rfw_cache', __( 'Cache (seconds, 0 = default)', 'rss-feed-widget' ), 'number', '0', '0' ),
		);
		$selects = array(
			array( 'list_type', __( 'Layout', 'rss-feed-widget' ), RFW_Settings::layouts(), 'slider' ),
			array( 'content_display', __( 'Display', 'rss-feed-widget' ), RFW_Settings::displays(), 'default' ),
			array( 'content_order', __( 'Sort order', 'rss-feed-widget' ), RFW_Settings::orders(), 'default' ),
			array( 'img_size', __( 'Image pick', 'rss-feed-widget' ), RFW_Settings::image_picks(), 'small' ),
		);
		$checks = array(
			array( 'show_feed_title', __( 'Show item titles', 'rss-feed-widget' ), '0', true ),
			array( 'keep_feed_link', __( 'Link items to the original', 'rss-feed-widget' ), '0', true ),
			array( 'show_date', __( 'Show date', 'rss-feed-widget' ), '0', false ),
			array( 'show_source', __( 'Show feed name', 'rss-feed-widget' ), '0', false ),
			array( 'new_tab', __( 'Open links in a new tab', 'rss-feed-widget' ), '1', true ),
		);
		?>
		<h2><?php esc_html_e( 'Shortcode builder', 'rss-feed-widget' ); ?></h2>
		<p><?php esc_html_e( 'Pick your options, copy the shortcode and paste it into any post, page or text widget.', 'rss-feed-widget' ); ?></p>
		<div id="rfw-builder" class="rfw-builder">
			<table class="form-table" role="presentation">
				<?php foreach ( $fields as $f ) : ?>
					<tr><th scope="row"><label for="rfwb-<?php echo esc_attr( $f[0] ); ?>"><?php echo esc_html( $f[1] ); ?></label></th>
					<td><input class="regular-text" id="rfwb-<?php echo esc_attr( $f[0] ); ?>" type="<?php echo esc_attr( $f[2] ); ?>" data-attr="<?php echo esc_attr( $f[0] ); ?>" data-default="<?php echo esc_attr( $f[4] ); ?>" value="<?php echo esc_attr( $f[3] ); ?>" <?php echo 'rss_url' === $f[0] ? 'placeholder="https://example.com/feed/"' : ''; ?> /></td></tr>
				<?php endforeach; ?>
				<?php foreach ( $selects as $s ) : ?>
					<tr><th scope="row"><label for="rfwb-<?php echo esc_attr( $s[0] ); ?>"><?php echo esc_html( $s[1] ); ?></label></th>
					<td><select id="rfwb-<?php echo esc_attr( $s[0] ); ?>" data-attr="<?php echo esc_attr( $s[0] ); ?>" data-default="<?php echo esc_attr( $s[3] ); ?>">
						<?php foreach ( $s[2] as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $s[3], (string) $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select></td></tr>
				<?php endforeach; ?>
				<tr><th scope="row"><?php esc_html_e( 'Options', 'rss-feed-widget' ); ?></th><td>
					<?php foreach ( $checks as $c ) : ?>
						<label><input type="checkbox" data-attr="<?php echo esc_attr( $c[0] ); ?>" data-default="<?php echo esc_attr( $c[2] ); ?>" <?php checked( $c[3] ); ?> /> <?php echo esc_html( $c[1] ); ?></label><br />
					<?php endforeach; ?>
				</td></tr>
			</table>
			<p><textarea id="rfw-builder-output" class="large-text code" rows="3" readonly="readonly"></textarea></p>
			<p><button type="button" class="button button-primary" id="rfw-builder-copy"><?php esc_html_e( 'Copy shortcode', 'rss-feed-widget' ); ?></button></p>
		</div>
		<?php
	}

	private static function tab_filters() {
		self::form_open( 'rfw_filters' );
		?>
		<h2><?php esc_html_e( 'Mute words and phrases', 'rss-feed-widget' ); ?></h2>
		<p><?php esc_html_e( 'Anything listed here (one per line) is removed from the text of every feed item on your site.', 'rss-feed-widget' ); ?></p>
		<textarea name="rfw_mutes" class="large-text code" rows="10"><?php echo esc_textarea( (string) get_option( 'rfw_mutes', '' ) ); ?></textarea>
		<p class="description"><?php esc_html_e( 'To hide words in a single widget, use the "Hide words starting with" field in its Advanced section.', 'rss-feed-widget' ); ?></p>
		<?php
		self::form_close();
	}

	private static function tab_images() {
		$current = (string) get_option( 'rfw_rss_image_size', 'post-thumbnail' );
		$sizes   = array_merge( array( 'none', 'post-thumbnail' ), get_intermediate_image_sizes() );
		self::form_open( 'rfw_images' );
		?>
		<h2><?php esc_html_e( "Featured image in your site's own feed", 'rss-feed-widget' ); ?></h2>
		<p><?php esc_html_e( 'Adds the featured image to the items of this website\'s RSS feed, so other readers and widgets can show it.', 'rss-feed-widget' ); ?></p>
		<select name="rfw_rss_image_size">
			<?php foreach ( array_unique( $sizes ) as $size ) : ?>
				<option value="<?php echo esc_attr( $size ); ?>" <?php selected( $current, $size ); ?>><?php echo esc_html( 'none' === $size ? __( 'Do not add an image', 'rss-feed-widget' ) : $size ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
		self::form_close();
	}

	private static function tab_youtube() {
		self::form_open( 'rfw_youtube' );
		?>
		<h2><?php esc_html_e( 'YouTube videos shortcode', 'rss-feed-widget' ); ?></h2>
		<p><?php esc_html_e( 'Use [rfw-youtube-videos number="3"] to show the videos below. Videos load from youtube-nocookie.com.', 'rss-feed-widget' ); ?></p>
		<table class="form-table" role="presentation">
			<tr><th scope="row"><label for="rfw_sc_google_api_key"><?php esc_html_e( 'Google API key', 'rss-feed-widget' ); ?></label></th>
			<td><input type="text" class="regular-text" autocomplete="off" id="rfw_sc_google_api_key" name="rfw_sc_google_api_key" value="<?php echo esc_attr( (string) get_option( 'rfw_sc_google_api_key', '' ) ); ?>" />
			<p class="description"><?php esc_html_e( 'Only needed for channel IDs (UC…), to look up their latest videos. Results are cached for six hours.', 'rss-feed-widget' ); ?></p></td></tr>
			<tr><th scope="row"><label for="rfw_sc_ids"><?php esc_html_e( 'Videos or channels', 'rss-feed-widget' ); ?></label></th>
			<td><textarea id="rfw_sc_ids" name="rfw_sc_ids" class="large-text code" rows="8"><?php echo esc_textarea( (string) get_option( 'rfw_sc_ids', '' ) ); ?></textarea>
			<p class="description"><?php esc_html_e( 'One per line: a video ID, a YouTube video URL, or a channel ID / channel URL.', 'rss-feed-widget' ); ?></p></td></tr>
		</table>
		<?php
		self::form_close();
	}

	private static function tab_tools() {
		?>
		<h2><?php esc_html_e( 'Test a feed', 'rss-feed-widget' ); ?></h2>
		<p><?php esc_html_e( 'Check that a feed can be read from this server before you use it.', 'rss-feed-widget' ); ?></p>
		<p><input type="url" id="rfw-test-url" class="regular-text" placeholder="https://example.com/feed/" />
		<button type="button" class="button" id="rfw-test-btn"><?php esc_html_e( 'Test feed', 'rss-feed-widget' ); ?></button></p>
		<div id="rfw-test-result" aria-live="polite"></div>

		<h2><?php esc_html_e( 'Cache', 'rss-feed-widget' ); ?></h2>
		<p><?php esc_html_e( 'Feeds are cached so your pages stay fast. Clear the cache to fetch every feed again.', 'rss-feed-widget' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="rfw_clear_cache" />
			<?php wp_nonce_field( 'rfw_clear_cache' ); ?>
			<?php submit_button( __( 'Clear feed cache', 'rss-feed-widget' ), 'secondary', 'submit', false ); ?>
		</form>
		<?php
	}

	private static function tab_pro() {
		if ( RFW_HAS_PRO ) {
			do_action( 'rfw_admin_tab_pro' );
			return;
		}
		$legacy = file_exists( RFW_PATH . 'pro/rss_extended.php' );
		?>
		<h2><?php esc_html_e( 'RSS Feed Widget Pro', 'rss-feed-widget' ); ?></h2>
		<?php if ( $legacy ) : ?>
			<div class="notice notice-warning inline"><p><?php esc_html_e( 'Files from an older Pro version were found. Replace the "pro" folder with the one from the 4.0 Pro package to switch your Pro features back on.', 'rss-feed-widget' ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Pro is a plug-and-play add-on: copy its "pro" folder into this plugin and the features below switch on. Remove it and the free plugin keeps working.', 'rss-feed-widget' ); ?></p>
		<ul class="rfw-feature-list">
			<li><?php esc_html_e( 'Combine up to 10 feeds in one widget, merged by date, with duplicates removed', 'rss-feed-widget' ); ?></li>
			<li><?php esc_html_e( 'Include and exclude keyword filters, and "only the last N days"', 'rss-feed-widget' ); ?></li>
			<li><?php esc_html_e( 'Grid and news-ticker layouts', 'rss-feed-widget' ); ?></li>
			<li><?php esc_html_e( 'Auto-import feed items as WordPress posts on a schedule (with source link)', 'rss-feed-widget' ); ?></li>
			<li><?php esc_html_e( 'Custom image tag support for unusual feeds', 'rss-feed-widget' ); ?></li>
		</ul>
		<p><a class="button button-primary" href="https://shop.androidbubbles.com/product/rss-feed-widget-pro/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get Pro', 'rss-feed-widget' ); ?></a></p>
		<?php
	}
}
