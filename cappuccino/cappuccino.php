<?php
/**
 * Plugin Name:       Cappuccino
 * Plugin URI:        https://github.com/example/cappuccino
 * Description:       在文章底部插入赞赏图片，支持自定义提示文字、链接和文章类型。
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Cappuccino
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cappuccino
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CAPP_VERSION', '1.0.0' );
define( 'CAPP_PLUGIN_FILE', __FILE__ );
define( 'CAPP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAPP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CAPP_PLUGIN_DIR . 'includes/class-admin.php';
require_once CAPP_PLUGIN_DIR . 'includes/class-frontend.php';

/**
 * Plugin bootstrap.
 */
final class Cappuccino {

	/**
	 * Singleton instance.
	 *
	 * @var Cappuccino|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Cappuccino
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		register_activation_hook( CAPP_PLUGIN_FILE, array( $this, 'activate' ) );

		if ( is_admin() ) {
			Cappuccino_Admin::init();
		}

		Cappuccino_Frontend::init();
	}

	/**
	 * Set default options on activation.
	 */
	public function activate() {
		$defaults = array(
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

		if ( false === get_option( Cappuccino_Admin::OPTION_KEY ) ) {
			add_option( Cappuccino_Admin::OPTION_KEY, $defaults );
		}
	}
}

Cappuccino::instance();
