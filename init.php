<?php
/**
 * Plugin Name: YITH WooCommerce Advanced Reviews
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-advanced-reviews/
 * Description: <code><strong>YITH WooCommerce Advanced Reviews</strong></code> extends the basic functionality of WooCommerce reviews and add a histogram table to the reviews of your products, such as you see in most trendy e-commerce sites. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce on <strong>YITH</strong></a>.
 * Author: YITH
 * Text Domain: yith-woocommerce-advanced-reviews
 * Version: 1.7.0
 * WC requires at least: 5.3
 * WC tested up to: 5.8
 * Author URI: https://yithemes.com/
 *
 * @package yith-woocommerce-advanced-reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
if ( ! function_exists( 'yith_ywar_install_woocommerce_admin_notice' ) ) {

	/**
	 * Yith_ywar_install_woocommerce_admin_notice
	 *
	 * @return void
	 */
	function yith_ywar_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'YITH WooCommerce Advanced Reviews is enabled but not effective. It requires WooCommerce in order to work.', 'yit' ); ?></p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'yith_ywar_install_free_admin_notice' ) ) {
	/**
	 * Yith_ywar_install_free_admin_notice
	 *
	 * @return void
	 */
	function yith_ywar_install_free_admin_notice() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'You can\'t activate the free version of YITH WooCommerce Advanced Reviews while you are using the premium one.', 'yit' ); ?></p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );


require_once plugin_dir_path( __FILE__ ) . 'functions.php';
yith_define( 'YITH_YWAR_FREE_INIT', plugin_basename( __FILE__ ) );
yith_define( 'YITH_YWAR_VERSION', '1.7.0' );
yith_define( 'YITH_YWAR_FILE', __FILE__ );
yith_define( 'YITH_YWAR_DIR', plugin_dir_path( __FILE__ ) );
yith_define( 'YITH_YWAR_URL', plugins_url( '/', __FILE__ ) );
yith_define( 'YITH_YWAR_ASSETS_URL', YITH_YWAR_URL . 'assets' );
yith_define( 'YITH_YWAR_TEMPLATE_PATH', YITH_YWAR_DIR . 'templates' );
yith_define( 'YITH_YWAR_TEMPLATES_DIR', YITH_YWAR_DIR . '/templates/' );
yith_define( 'YITH_YWAR_ASSETS_IMAGES_URL', YITH_YWAR_ASSETS_URL . '/images/' );
defined( 'YITH_YWAR_VIEWS_PATH' ) || define( 'YITH_YWAR_VIEWS_PATH', YITH_YWAR_DIR . 'views/' );
! defined( 'YITH_YWAR_SLUG' ) && define( 'YITH_YWAR_SLUG', 'yith-woocommerce-advanced-reviews' );


/* Plugin Framework Version Check */
if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_YWAR_DIR . 'plugin-fw/init.php' ) ) {
	require_once YITH_YWAR_DIR . 'plugin-fw/init.php';
}
yit_maybe_plugin_fw_loader( YITH_YWAR_DIR );

/**
 * Yith_ywar_init
 *
 * @return void
 */
function yith_ywar_init() {

	/**
	 * Load text domain and start plugin
	 */
	load_plugin_textdomain( 'yith-woocommerce-advanced-reviews', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	require_once YITH_YWAR_DIR . 'class.yith-woocommerce-advanced-reviews.php';

	global $YWAR_AdvancedReview;// phpcs:ignore WordPress.NamingConventions
	$YWAR_AdvancedReview = YITH_WooCommerce_Advanced_Reviews::get_instance();// phpcs:ignore WordPress.NamingConventions
}

add_action( 'yith_ywar_init', 'yith_ywar_init' );

/**
 * Yith_ywar_install
 *
 * @return void
 */
function yith_ywar_install() {

	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'yith_ywar_install_woocommerce_admin_notice' );
	} elseif ( defined( 'YITH_YWAR_PREMIUM' ) ) {
		add_action( 'admin_notices', 'yith_ywar_install_free_admin_notice' );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} else {
		do_action( 'yith_ywar_init' );
	}
}

add_action( 'plugins_loaded', 'yith_ywar_install', 11 );
