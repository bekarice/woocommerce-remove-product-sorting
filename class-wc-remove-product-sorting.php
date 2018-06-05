<?php
/**
 * WooCommerce Remove Product Sorting
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Remove Product Sorting to newer
 * versions in the future. If you wish to customize WooCommerce Remove Product Sorting for your
 * needs please refer to http://skyverge.com/products/woocommerce-remove-product-sorting/ for more information.
 *
 * @package   WC-Remove-Product-Sorting
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\RemoveProductSorting;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\PluginUpdater as Updater;

/**
 * Class Plugin.
 * Sets up the main plugin class.
 *
 * @since 1.1.0
 */

class Plugin {


	const VERSION = '1.2.0';

	/** @var \SkyVerge\WooCommerce\RemoveProductSorting\Plugin single instance of this plugin */
	protected static $instance;

	/** @var Updater\License $license license class instance */
	protected $license;


	/**
	 * \SkyVerge\WooCommerce\RemoveProductSorting\Plugin constructor. Initializes the plugin.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {

		// load translations
		add_action( 'init', array( $this, 'load_translation' ) );

		// modify sorting options
		add_filter( 'woocommerce_default_catalog_orderby_options', array( $this, 'remove_sorting_from_settings' ) );
		add_filter( 'woocommerce_catalog_orderby', array( $this, 'remove_frontend_sorting_option' ) );

		// add settings to product display settings
		if ( self::is_wc_gte( '3.3' ) ) {
			add_action( 'customize_register', array( $this, 'add_settings' ) );
		} else {
			add_filter( 'woocommerce_product_settings', array( $this, 'legacy_add_settings' ) );
		}

		// unhook the sorting dropdown completely if there are no options
		add_action( 'wp', array( $this, 'maybe_remove_catalog_orderby' ), 99 );

		$this->includes();

		if ( is_admin() && ! is_ajax() ) {

			// add plugin links
			add_filter( 'plugin_action_links_' . plugin_basename( $this->get_plugin_file() ), array( $this, 'add_plugin_links' ) );

			$this->install();
		}
	}


	/**
	 * Loads plugin files.
	 *
	 * @since 1.0.0
	 */
	public function includes() {

		if ( ! class_exists( 'Updater\\License' ) ) {
			require_once( $this->get_plugin_path() . '/lib/skyverge/updater/class-skyverge-plugin-license.php');
		}

		// item ID is from skyverge.com download WP_Post ID
		$this->license = new Updater\License( $this->get_plugin_file(), $this->get_plugin_path(), $this->get_plugin_url(), $this->get_plugin_name(), $this->get_version(), 4589 );
	}


	/** Plugin methods ******************************************************/


	/**
	 * Removes sorting option from the Product Settings
	 *
	 * @since 1.1.0
	 *
	 * @param array $options the settings options
	 * @return array updated options
	 */
	public function remove_sorting_from_settings( $options ) {

		foreach( $this->get_removed_sorting_options() as $remove ) {
			unset( $options[ $remove ] );
		}

		return $options;
	}


	/**
	 * Removes sorting option from the shop template
	 *
	 * @since 1.1.0
	 *
	 * @param array $orderby the orderby options
	 * @return array updated options
	 */
	public function remove_frontend_sorting_option( $orderby ) {

		foreach( $this->get_removed_sorting_options() as $remove ) {
			unset( $orderby[ $remove ] );
		}

		return $orderby;
	}


	/**
	 * Add Settings to WooCommerce Settings > Products page after "Default Product Sorting" setting
	 *
	 * @since 1.1.0
	 *
	 * @param array $settings the settings options
	 * @return array updated settings
	 */
	public function legacy_add_settings( $settings ) {

		$updated_settings = array();

		foreach ( $settings as $setting ) {

			$updated_settings[] = $setting;

			if ( isset( $setting['id'] ) && 'woocommerce_default_catalog_orderby' === $setting['id'] ) {

				$updated_settings[] = array(
					'name'     => __( 'Remove Product Sorting:', 'woocommerce-remove-product-sorting' ),
					'desc_tip' => __( 'Choose sorting options to remove from your shop.', 'woocommerce-remove-product-sorting' ),
					'id'       => 'wc_remove_product_sorting',
					'type'     => 'multiselect',
					'class'    => 'chosen_select wc_enhanced_select',
					'default'  => '',
					'options'  => $this->get_core_sorting_options(),
					'custom_attributes' => array(
						'data-placeholder' => __( 'Choose sorting options to remove from your shop.', 'woocommerce-remove-product-sorting' ),
					),
				);

			}
		}

		return $updated_settings;
	}


	/**
	 * Add Settings to WooCommerce Settings > Products page after "Default Product Sorting" setting.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 */
	public function add_settings( $wp_customize ) {

		// load our custom control type
		require_once( dirname( __FILE__ ) . '/includes/class-wc-remove-sorting-customizer-checkbox-multiple.php' );

		// make sure we can insert our desired controls where we want them {BR 2018-02-10}
		// this is heavy-handed, but WC core doesn't add priorities for us, shikata ga nai ¯\_(ツ)_/¯
		if ( $catalog_columns_control = $wp_customize->get_control( 'woocommerce_catalog_columns' ) ) {
			$catalog_columns_control->priority = 15;
		}

		if ( $catalog_rows_control = $wp_customize->get_control( 'woocommerce_catalog_rows' ) ) {
			$catalog_rows_control->priority = 15;
		}

		$wp_customize->add_setting(
			'wc_remove_product_sorting',
			array(
				'default'           => array(),
				'capability'        => 'manage_woocommerce',
				'sanitize_callback' => array( $this, 'sanitize_option_list' ),
			)
		);

		$wp_customize->add_control(
			new Customize_Checkbox_Multiple(
				$wp_customize,
				'wc_remove_product_sorting',
				array(
					'type'        => 'checkbox-multiple',
					'label'       => __( 'Remove Product Sorting:', 'woocommerce-remove-product-sorting' ),
					'description' => __( 'Choose sorting options to remove from your shop.', 'woocommerce-remove-product-sorting' ),
					'section'     => 'woocommerce_product_catalog',
					'priority'    => 10,
					'choices'     => $this->get_core_sorting_options(),
				)
			)
		);
	}


	/**
	 * Unhooks the sorting dropdown if all options have been removed.
	 *
	 * @since 1.2.0
	 */
	public function maybe_remove_catalog_orderby() {

		$enabled              = array_diff( array_keys( $this->get_core_sorting_options() ), array_values( $this->get_removed_sorting_options() ) );
		$active_plugins       = (array) get_option( 'active_plugins', array() );
		$extra_sorting_plugin = 'woocommerce-extra-product-sorting-options/woocommerce-extra-product-sorting-options.php';

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		// check for our extra sorting options plugin, just in case there are custom options added, too
		if ( in_array( $extra_sorting_plugin, $active_plugins ) || array_key_exists( $extra_sorting_plugin, $active_plugins ) ) {

			$extra_sorting = self::is_wc_gte( '3.3' ) ? get_theme_mod( 'wc_extra_product_sorting_options', array() ) : get_option( 'wc_extra_product_sorting_options', array() );
			$enabled       = array_merge( $enabled, $extra_sorting );
		}


		/**
		 * Filters whether the sorting dropdown should be unhooked from the shop page when there are no core sorting options.
		 *
		 * @since 1.2.0
		 *
		 * @param bool $remove true if the dropdown should be removed
		 */
		if ( empty( $enabled ) && apply_filters( 'wc_remove_sorting_options_hide_dropdown', true ) ) {

			// WC core output
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

			// Storefront theme
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 10 );
			remove_action( 'woocommerce_after_shop_loop',  'woocommerce_catalog_ordering', 10 );
		}
	}


	/**
	 * Helper to get WooCommerce core sorting options.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	protected function get_core_sorting_options() {

		// the woocommerce text domain here is intentional
		// we're also not filtering this given we probably don't need to remove *custom* sorting for people
		return array(
			'menu_order' => __( 'Default sorting (custom ordering + name)', 'woocommerce' ),
			'popularity' => __( 'Popularity (sales)', 'woocommerce' ),
			'rating'     => __( 'Average rating', 'woocommerce' ),
			'date'       => __( 'Sort by most recent', 'woocommerce' ),
			'price'      => __( 'Sort by price (asc)', 'woocommerce' ),
			'price-desc' => __( 'Sort by price (desc)', 'woocommerce' ),
		);
	}


	/**
	 * Sanitize the default sorting callback.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $values the option value
	 * @return string[]
	 */
	public function sanitize_option_list( $values ) {

		$multi_values = ! is_array( $values ) ? explode( ',', $values ) : $values;

		return ! empty( $multi_values ) ? array_map( 'sanitize_text_field', $multi_values ) : array();
	}


	/**
	 * Gets the sorting options that should be removed.
	 *
	 * @since 1.2.0
	 *
	 * @return array options removed
	 */
	private function get_removed_sorting_options() {

		return self::is_wc_gte( '3.3' ) ? get_theme_mod('wc_remove_product_sorting', array() ) : get_option( 'wc_remove_product_sorting', array() );
	}


	/** Helper methods ******************************************************/


	/**
	 * Gets the updater class instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Updater\License
	 */
	public function get_license_instance() {
		return $this->license;
	}


	/**
	 * Main Remove Sorting Instance, ensures only one instance is/can be loaded.
	 *
	 * @since 1.1.0
	 * @see wc_remove_product_sorting()
	 *
	 * @return \SkyVerge\WooCommerce\RemoveProductSorting\Plugin
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Cloning instances is forbidden due to singleton pattern.
	 *
	 * @since 1.1.0
	 */
	public function __clone() {
		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot clone instances of %s.', 'woocommerce-remove-product-sorting' ), 'WooCommerce Remove Product Sorting' ), '1.1.0' );
	}


	/**
	 * Unserializing instances is forbidden due to singleton pattern.
	 *
	 * @since 1.1.0
	 */
	public function __wakeup() {
		/* translators: Placeholders: %s - plugin name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'You cannot unserialize instances of %s.', 'woocommerce-remove-product-sorting' ), 'WooCommerce Remove Product Sorting' ), '1.1.0' );
	}


	/**
	 * Helper to get the plugin URL.
	 *
	 * @since 1.1.0
	 *
	 * @return string the plugin URL
	 */
	public function get_plugin_url() {
		return untrailingslashit( plugins_url( '/', $this->get_file() ) );
	}


	/**
	 * Helper to return the plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @return string plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce Remove Product Sorting', 'woocommerce-remove-product-sorting' );
	}


	/**
	 * Helper to get the plugin path.
	 *
	 * @since 1.0.0
	 *
	 * @return string the plugin path
	 */
	public function get_plugin_path() {
		return untrailingslashit( plugin_dir_path( $this->get_file() ) );
	}


	/**
	 * Helper to get the plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @return string the plugin version
	 */
	public function get_file() {
		return __FILE__;
	}


	/**
	 * Gets the main plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_plugin_file() {

		$slug = dirname( plugin_basename( $this->get_file() ) );
		return trailingslashit( $slug ) . $slug . '.php';
	}


	/**
	 * Helper to get the plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @return string the plugin version
	 */
	public function get_version() {
		return self::VERSION;
	}


	/**
	 * Adds plugin page links.
	 *
	 * @since 1.1.0
	 *
	 * @param array $links all plugin links
	 * @return array $links all plugin links + our custom links (i.e., "Settings")
	 */
	public function add_plugin_links( $links ) {

		if ( self::is_wc_gte( '3.3' ) ) {
			$configure_url = admin_url( 'customize.php?url=' . wc_get_page_permalink( 'shop' ) . '&autofocus[section]=woocommerce_product_catalog' );
		} else {
			$configure_url = admin_url( 'admin.php?page=wc-settings&tab=products&section=display' );
		}

		$license_text = $this->get_license_instance()->is_license_valid() ? __( 'License', 'woocommerce-memberships-directory-shortcode' ) : __( 'Get updates', 'woocommerce-memberships-directory-shortcode' );

		$plugin_links = array(
			'<a href="' . esc_url( $configure_url ) . '">' . __( 'Configure', 'woocommerce-extra-product-sorting-options' ) . '</a>',
			'<a href="http://www.skyverge.com/product/woocommerce-remove-product-sorting/">'. __( 'FAQ', 'woocommerce-extra-product-sorting-options' ) . '</a>',
			'<a href="' . $this->get_license_instance()->get_license_settings_url() . '">' . esc_html( $license_text ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}


	/**
	 * Load Translations
	 *
	 * @since 1.1.0
	 */
	public function load_translation() {
		// localization
		load_plugin_textdomain( 'woocommerce-remove-product-sorting', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
	}


	/**
	 * Checks if WooCommerce is greater than a specific version.
	 *
	 * @internal
	 *
	 * @since 1.1.0
	 *
	 * @param string $version version number
	 * @return bool true if > version
	 */
	public static function is_wc_gte( $version ) {
		return defined( 'WC_VERSION' ) && WC_VERSION && version_compare( WC_VERSION, $version, '>=' );
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Run every time.  Used since the activation hook is not executed when updating a plugin.
	 *
	 * @since 2.0.0
	 */
	private function install() {

		// get current version to check for upgrade
		$installed_version = get_option( 'wc_remove_product_sorting_version' );

		// force upgrade to 1.1.0, prior versions did not have version option set
		if ( ! $installed_version && ! get_option( 'wc_remove_product_sorting_version' ) ) {
			$this->upgrade( '1.1.0' );
		}

		// upgrade if installed version lower than plugin version
		if ( -1 === version_compare( $installed_version, self::VERSION ) ) {
			$this->upgrade( $installed_version );
		}

	}


	/**
	 * Perform any version-related changes.
	 *
	 * @since 1.1.0
	 *
	 * @param string $installed_version the currently installed version of the plugin
	 */
	private function upgrade( $installed_version ) {

		// upgrade to 1.1.0; migrate option to theme mod
		if ( '1.1.0' === $installed_version ) {
			set_theme_mod( 'wc_remove_product_sorting', get_option( 'wc_remove_product_sorting', array() ) );
		}

		// update the installed version option
		update_option( 'wc_remove_product_sorting_version', self::VERSION );
	}


}
