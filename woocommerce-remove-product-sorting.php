<?php
/**
 * Plugin Name: WooCommerce Remove Product Sorting
 * Plugin URI: http://www.skyverge.com/product/woocommerce-remove-product-sorting/
 * Description: Remove core WooCommerce product sorting options
 * Author: SkyVerge
 * Author URI: http://www.skyverge.com/
 * Version: 1.2.0
 * Text Domain: woocommerce-remove-product-sorting
 *
 * Copyright: (c) 2014-2018 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Remove-Product-Sorting
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 * WC requires at least: 2.6.14
 * WC tested up to: 3.4.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * The plugin loader class.
 *
 * @since 1.2.0
 */
class WC_Remove_Product_Sorting_Loader {


	/** minimum PHP version required by this plugin */
	const MINIMUM_PHP_VERSION = '5.3.0';

	/** minimum WordPress version required by this plugin */
	const MINIMUM_WP_VERSION = '4.4';

	/** minimum WooCommerce version required by this plugin */
	const MINIMUM_WC_VERSION = '2.6.14';

	/** the plugin name, for displaying notices */
	const PLUGIN_NAME = 'WooCommerce Remove Product Sorting';

	/** @var WC_Remove_Product_Sorting_Loader single instance of this plugin */
	protected static $instance;

	/** @var array the admin notices to add */
	protected $notices = array();


	/**
	 * WC_Remove_Product_Sorting_Loader constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {

		register_activation_hook( __FILE__, array( $this, 'activation_check' ) );

		add_action( 'admin_init', array( $this, 'check_environment' ) );
		add_action( 'admin_init', array( $this, 'add_plugin_notices' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );

		// if the environment check passes, initialize the plugin
		if ( $this->is_environment_compatible() ) {
			add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
		}
	}


	/**
	 * Cloning instances is forbidden due to singleton pattern.
	 *
	 * @since 1.2.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, sprintf( 'You cannot clone instances of %s.', get_class( $this ) ), '1.2.0' );
	}


	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 *
	 * @since 1.2.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, sprintf( 'You cannot unserialize instances of %s.', get_class( $this ) ), '1.0.0' );
	}


	/**
	 * Initializes the plugin.
	 *
	 * @since 1.2.0
	 */
	public function init_plugin() {

		if ( ! $this->plugins_compatible() ) {
			return;
		}

		// load the functions file
		require_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php' );

		// load the main plugin class
		require_once( plugin_dir_path( __FILE__ ) . 'class-wc-remove-product-sorting.php' );

		// fire it up!
		wc_remove_product_sorting();
	}


	/**
	 * Checks the server environment and other factors and deactivates plugins as necessary.
	 *
	 * Based on http://wptavern.com/how-to-prevent-wordpress-plugins-from-activating-on-sites-with-incompatible-hosting-environments
	 *
	 * @since 1.2.0
	 */
	public function activation_check() {

		if ( ! $this->is_environment_compatible() ) {

			$this->deactivate_plugin();
			wp_die( self::PLUGIN_NAME . ' could not be activated. ' . $this->get_environment_message() );
		}
	}


	/**
	 * Checks the environment on loading WordPress, just in case the environment changes after activation.
	 *
	 * @since 1.2.0
	 */
	public function check_environment() {

		if ( ! $this->is_environment_compatible() && is_plugin_active( plugin_basename( __FILE__ ) ) ) {

			$this->deactivate_plugin();
			$this->add_admin_notice( 'bad_environment', 'error', self::PLUGIN_NAME . ' has been deactivated. ' . $this->get_environment_message() );
		}
	}


	/**
	 * Adds notices for out-of-date WordPress, WooCommerce, and / or Memberships versions.
	 *
	 * @since 1.2.0
	 */
	public function add_plugin_notices() {

		if ( ! $this->is_wp_compatible() ) {

			$this->add_admin_notice( 'update_wordpress', 'error', sprintf(
				'%s is not active, as it requires WordPress version %s or higher. Please %supdate WordPress &raquo;%s',
				'<strong>' . self::PLUGIN_NAME . '</strong>',
				self::MINIMUM_WP_VERSION,
				'<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">', '</a>'
			) );
		}

		if ( ! $this->is_wc_compatible() ) {

			$this->add_admin_notice( 'update_woocommerce', 'error', sprintf(
				'%s is not active, as it requires WooCommerce version %s or higher. Please %supdate WooCommerce &raquo;%s',
				'<strong>' . self::PLUGIN_NAME . '</strong>',
				self::MINIMUM_WC_VERSION,
				'<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">', '</a>'
			) );
		}
	}


	/**
	 * Determines if the required plugins are compatible.
	 *
	 * @since 1.2.0
	 *
	 * @return bool
	 */
	protected function plugins_compatible() {
		return $this->is_wp_compatible() && $this->is_wc_compatible();
	}


	/**
	 * Determines if the WordPress version is compatible.
	 *
	 * @since 1.2.0
	 *
	 * @return bool
	 */
	protected function is_wp_compatible() {

		if ( ! self::MINIMUM_WP_VERSION ) {
			return true;
		}

		return version_compare( get_bloginfo( 'version' ), self::MINIMUM_WP_VERSION, '>=' );
	}


	/**
	 * Determines if the WooCommerce version is compatible.
	 *
	 * @since 1.2.0
	 *
	 * @return bool
	 */
	protected function is_wc_compatible() {

		if ( ! self::MINIMUM_WC_VERSION ) {
			return true;
		}

		return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, self::MINIMUM_WC_VERSION, '>=' );
	}


	/**
	 * Deactivates the plugin.
	 *
	 * @since 1.2.0
	 */
	protected function deactivate_plugin() {

		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}


	/**
	 * Adds an admin notice to be displayed.
	 *
	 * @since 1.2.0
	 *
	 * @param string $slug message slug
	 * @param string $class CSS classes
	 * @param string $message notice message
	 */
	public function add_admin_notice( $slug, $class, $message ) {

		$this->notices[ $slug ] = array(
			'class'   => $class,
			'message' => $message
		);
	}


	/**
	 * Displays any admin notices added with \WC_Remove_Product_Sorting_Loader::add_admin_notice()
	 *
	 * @since 1.2.0
	 */
	public function admin_notices() {

		foreach ( (array) $this->notices as $notice_key => $notice ) {

			echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
			echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) );
			echo "</p></div>";
		}
	}


	/**
	 * Determines if the server environment is compatible with this plugin.
	 *
	 * Override this method to add checks for more than just the PHP version.
	 *
	 * @since 1.2.0
	 *
	 * @return bool
	 */
	protected function is_environment_compatible() {
		return version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=' );
	}


	/**
	 * Gets the message for display when the environment is incompatible with this plugin.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	protected function get_environment_message() {

		$message = sprintf( 'The minimum PHP version required for this plugin is %1$s. You are running %2$s.', self::MINIMUM_PHP_VERSION, PHP_VERSION );
		return $message;
	}


	/**
	 * Gets the main \WC_Remove_Product_Sorting_Loader instance.
	 *
	 * Ensures only one instance can be loaded.
	 *
	 * @since 1.2.0
	 *
	 * @return \WC_Remove_Product_Sorting_Loader
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


}


// fire it up!
WC_Remove_Product_Sorting_Loader::instance();
