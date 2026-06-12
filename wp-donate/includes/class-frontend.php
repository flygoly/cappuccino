<?php
/**
 * Frontend display logic.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Appends donation image to post content.
 */
class WPD_Frontend {

	/**
	 * Initialize frontend hooks.
	 */
	public static function init() {
		add_filter( 'the_content', array( __CLASS__, 'append_donate_image' ), 99 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
	}

	/**
	 * Enqueue frontend styles.
	 */
	public static function enqueue_styles() {
		if ( ! self::should_display() ) {
			return;
		}

		wp_enqueue_style(
			'wpd-frontend',
			WPD_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			WPD_VERSION
		);
	}

	/**
	 * Check if donate block should display on current page.
	 *
	 * @return bool
	 */
	private static function should_display() {
		if ( is_admin() || ! is_singular() ) {
			return false;
		}

		$settings = WPD_Admin::get_settings();

		if ( empty( $settings['enabled'] ) ) {
			return false;
		}

		$post_type = get_post_type();
		if ( ! $post_type || ! in_array( $post_type, $settings['post_types'], true ) ) {
			return false;
		}

		$image_url = self::get_image_url( $settings );
		if ( empty( $image_url ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Resolve image URL from settings.
	 *
	 * @param array $settings Plugin settings.
	 * @return string
	 */
	private static function get_image_url( $settings ) {
		if ( ! empty( $settings['image_id'] ) ) {
			$url = wp_get_attachment_url( (int) $settings['image_id'] );
			if ( $url ) {
				return $url;
			}
		}

		return ! empty( $settings['image_url'] ) ? $settings['image_url'] : '';
	}

	/**
	 * Build hint text HTML.
	 *
	 * @param array $settings Plugin settings.
	 * @return string
	 */
	private static function get_hint_html( $settings ) {
		if ( empty( $settings['show_hint'] ) || empty( $settings['hint_text'] ) ) {
			return '';
		}

		$lines = array_filter( array_map( 'trim', explode( "\n", $settings['hint_text'] ) ) );
		if ( empty( $lines ) ) {
			return '';
		}

		$paragraphs = array_map(
			static function ( $line ) {
				return sprintf( '<p class="wpd-donate-hint-line">%s</p>', esc_html( $line ) );
			},
			$lines
		);

		return sprintf(
			'<div class="wpd-donate-hint">%s</div>',
			implode( '', $paragraphs )
		);
	}

	/**
	 * Append donate image HTML to content.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function append_donate_image( $content ) {
		if ( ! self::should_display() ) {
			return $content;
		}

		$settings  = WPD_Admin::get_settings();
		$image_url = self::get_image_url( $settings );
		$align     = sanitize_html_class( $settings['align'] );
		$max_width = (int) $settings['max_width'];
		$alt_text  = ! empty( $settings['hint_text'] )
			? wp_strip_all_tags( $settings['hint_text'] )
			: __( '赞赏支持', 'wp-donate' );

		$image_html = sprintf(
			'<img src="%s" alt="%s" class="wpd-donate-image" style="max-width:%dpx" loading="lazy" />',
			esc_url( $image_url ),
			esc_attr( $alt_text ),
			$max_width
		);

		if ( ! empty( $settings['link_url'] ) ) {
			$image_html = sprintf(
				'<a href="%s" class="wpd-donate-link" target="_blank" rel="noopener noreferrer">%s</a>',
				esc_url( $settings['link_url'] ),
				$image_html
			);
		}

		$donate_html = sprintf(
			'<div class="wpd-donate-box wpd-align-%s">%s<div class="wpd-donate-image-wrap">%s</div></div>',
			esc_attr( $align ),
			self::get_hint_html( $settings ),
			$image_html
		);

		return $content . $donate_html;
	}
}
