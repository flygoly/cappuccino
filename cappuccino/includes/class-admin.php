<?php
/**
 * Admin settings page.
 *
 * @package Cappuccino
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin settings in WordPress admin.
 */
class Cappuccino_Admin {

	/**
	 * Option key.
	 */
	const OPTION_KEY = 'capp_settings';

	/**
	 * Legacy option key from previous plugin slug.
	 */
	const LEGACY_OPTION_KEY = 'wpd_settings';

	/**
	 * Legacy option key from earlier plugin slug.
	 */
	const LEGACY_OPTION_KEY_V1 = 'wpad_settings';

	/**
	 * Initialize admin hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Add settings page under Settings menu.
	 */
	public static function add_menu_page() {
		add_options_page(
			__( 'Cappuccino 设置', 'cappuccino' ),
			__( 'Cappuccino', 'cappuccino' ),
			'manage_options',
			'cappuccino',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register settings and fields.
	 */
	public static function register_settings() {
		register_setting(
			'capp_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
				'default'           => self::get_defaults(),
			)
		);
	}

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'enabled'    => true,
			'image_id'   => 0,
			'image_url'  => '',
			'show_hint'  => true,
			'hint_text'  => '如果觉得文章对你有帮助，欢迎赞赏支持',
			'link_url'   => '',
			'post_types' => array( 'post' ),
			'align'      => 'center',
			'max_width'  => 300,
		);
	}

	/**
	 * Get merged settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$settings = get_option( self::OPTION_KEY, array() );

		if ( empty( $settings ) ) {
			foreach ( array( self::LEGACY_OPTION_KEY, self::LEGACY_OPTION_KEY_V1 ) as $legacy_key ) {
				$legacy = get_option( $legacy_key, array() );
				if ( ! empty( $legacy ) ) {
					$settings = self::migrate_legacy_settings( $legacy );
					update_option( self::OPTION_KEY, $settings );
					break;
				}
			}
		}

		$settings = wp_parse_args( $settings, self::get_defaults() );

		if ( empty( $settings['hint_text'] ) && ! empty( $settings['title'] ) ) {
			$settings['hint_text'] = $settings['title'];
		}

		return $settings;
	}

	/**
	 * Migrate settings from a legacy plugin option key.
	 *
	 * @param array $legacy Legacy settings.
	 * @return array
	 */
	private static function migrate_legacy_settings( $legacy ) {
		$settings = wp_parse_args( $legacy, self::get_defaults() );

		if ( ! empty( $legacy['title'] ) && empty( $settings['hint_text'] ) ) {
			$settings['hint_text'] = $legacy['title'];
		}

		unset( $settings['title'] );

		return $settings;
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param array $input Raw input.
	 * @return array
	 */
	public static function sanitize_settings( $input ) {
		$defaults = self::get_defaults();
		$output   = array();

		$output['enabled'] = ! empty( $input['enabled'] );
		$output['show_hint'] = ! empty( $input['show_hint'] );

		$output['image_id'] = isset( $input['image_id'] ) ? absint( $input['image_id'] ) : 0;

		if ( $output['image_id'] > 0 ) {
			$output['image_url'] = wp_get_attachment_url( $output['image_id'] ) ?: '';
		} else {
			$output['image_url'] = isset( $input['image_url'] ) ? esc_url_raw( $input['image_url'] ) : '';
		}

		$output['hint_text'] = isset( $input['hint_text'] )
			? sanitize_textarea_field( $input['hint_text'] )
			: $defaults['hint_text'];

		$output['link_url'] = isset( $input['link_url'] ) ? esc_url_raw( $input['link_url'] ) : '';

		$allowed_aligns      = array( 'left', 'center', 'right' );
		$output['align']     = isset( $input['align'] ) && in_array( $input['align'], $allowed_aligns, true )
			? $input['align']
			: $defaults['align'];
		$output['max_width'] = isset( $input['max_width'] ) ? max( 50, min( 800, absint( $input['max_width'] ) ) ) : $defaults['max_width'];

		$available_types      = array_keys( get_post_types( array( 'public' => true ), 'names' ) );
		$selected_types       = isset( $input['post_types'] ) && is_array( $input['post_types'] ) ? $input['post_types'] : array();
		$output['post_types'] = array_values(
			array_intersect(
				array_map( 'sanitize_key', $selected_types ),
				$available_types
			)
		);

		if ( empty( $output['post_types'] ) ) {
			$output['post_types'] = array( 'post' );
		}

		return $output;
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue_scripts( $hook ) {
		if ( 'settings_page_cappuccino' !== $hook ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style(
			'capp-admin',
			CAPP_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			CAPP_VERSION
		);
		wp_enqueue_script(
			'capp-admin',
			CAPP_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			CAPP_VERSION,
			true
		);
	}

	/**
	 * Render settings page.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings   = self::get_settings();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$image_url  = $settings['image_url'];

		if ( $settings['image_id'] > 0 ) {
			$attachment_url = wp_get_attachment_url( $settings['image_id'] );
			if ( $attachment_url ) {
				$image_url = $attachment_url;
			}
		}
		?>
		<div class="wrap capp-admin-wrap">
			<h1><?php esc_html_e( 'Cappuccino 设置', 'cappuccino' ); ?></h1>
			<p class="description">
				<?php esc_html_e( '上传赞赏图片（如微信/支付宝收款码），它将被自动插入到所选文章类型的底部。', 'cappuccino' ); ?>
			</p>

			<form method="post" action="options.php">
				<?php settings_fields( 'capp_settings_group' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( '启用赞赏', 'cappuccino' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[enabled]" value="1" <?php checked( $settings['enabled'] ); ?> />
								<?php esc_html_e( '在文章底部显示赞赏图片', 'cappuccino' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( '赞赏图片', 'cappuccino' ); ?></th>
						<td>
							<div class="capp-image-field">
								<input type="hidden" id="capp-image-id" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[image_id]" value="<?php echo esc_attr( $settings['image_id'] ); ?>" />
								<input type="hidden" id="capp-image-url" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[image_url]" value="<?php echo esc_attr( $settings['image_url'] ); ?>" />

								<div class="capp-image-preview" id="capp-image-preview">
									<?php if ( $image_url ) : ?>
										<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php esc_attr_e( '赞赏图片预览', 'cappuccino' ); ?>" />
									<?php else : ?>
										<span class="capp-no-image"><?php esc_html_e( '尚未选择图片', 'cappuccino' ); ?></span>
									<?php endif; ?>
								</div>

								<p>
									<button type="button" class="button" id="capp-upload-btn">
										<?php esc_html_e( '选择图片', 'cappuccino' ); ?>
									</button>
									<button type="button" class="button" id="capp-remove-btn" <?php echo $image_url ? '' : 'style="display:none"'; ?>>
										<?php esc_html_e( '移除图片', 'cappuccino' ); ?>
									</button>
								</p>
							</div>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( '提示文字', 'cappuccino' ); ?></th>
						<td>
							<label style="display:block;margin-bottom:10px;">
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[show_hint]" value="1" <?php checked( $settings['show_hint'] ); ?> />
								<?php esc_html_e( '显示提示文字', 'cappuccino' ); ?>
							</label>
							<textarea
								id="capp-hint-text"
								class="large-text"
								name="<?php echo esc_attr( self::OPTION_KEY ); ?>[hint_text]"
								rows="3"
								placeholder="<?php esc_attr_e( '如果觉得文章对你有帮助，欢迎赞赏支持', 'cappuccino' ); ?>"
							><?php echo esc_textarea( $settings['hint_text'] ); ?></textarea>
							<p class="description"><?php esc_html_e( '显示在赞赏图片上方的说明文字，支持多行。取消勾选上方选项可隐藏提示文字。', 'cappuccino' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="capp-link-url"><?php esc_html_e( '点击链接', 'cappuccino' ); ?></label>
						</th>
						<td>
							<input type="url" id="capp-link-url" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[link_url]" value="<?php echo esc_attr( $settings['link_url'] ); ?>" placeholder="https://" />
							<p class="description"><?php esc_html_e( '可选。点击图片时跳转的链接（如赞赏页面），留空则图片不可点击。', 'cappuccino' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( '显示位置', 'cappuccino' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( '显示位置', 'cappuccino' ); ?></legend>
								<?php foreach ( $post_types as $post_type ) : ?>
									<label style="display:block;margin-bottom:6px;">
										<input
											type="checkbox"
											name="<?php echo esc_attr( self::OPTION_KEY ); ?>[post_types][]"
											value="<?php echo esc_attr( $post_type->name ); ?>"
											<?php checked( in_array( $post_type->name, $settings['post_types'], true ) ); ?>
										/>
										<?php echo esc_html( $post_type->labels->name ); ?>
										<code><?php echo esc_html( $post_type->name ); ?></code>
									</label>
								<?php endforeach; ?>
							</fieldset>
							<p class="description"><?php esc_html_e( '选择在哪些文章类型底部显示赞赏图片。', 'cappuccino' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="capp-align"><?php esc_html_e( '对齐方式', 'cappuccino' ); ?></label>
						</th>
						<td>
							<select id="capp-align" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[align]">
								<option value="left" <?php selected( $settings['align'], 'left' ); ?>><?php esc_html_e( '左对齐', 'cappuccino' ); ?></option>
								<option value="center" <?php selected( $settings['align'], 'center' ); ?>><?php esc_html_e( '居中', 'cappuccino' ); ?></option>
								<option value="right" <?php selected( $settings['align'], 'right' ); ?>><?php esc_html_e( '右对齐', 'cappuccino' ); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="capp-max-width"><?php esc_html_e( '图片最大宽度', 'cappuccino' ); ?></label>
						</th>
						<td>
							<input type="number" id="capp-max-width" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[max_width]" value="<?php echo esc_attr( $settings['max_width'] ); ?>" min="50" max="800" step="10" />
							<span>px</span>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
