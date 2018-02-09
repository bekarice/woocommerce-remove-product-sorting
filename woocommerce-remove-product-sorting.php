<?php
/**
 * Plugin Name: WooCommerce Remove Product Sorting
 * Plugin URI: http://www.skyverge.com/product/woocommerce-remove-product-sorting/
 * Description: Remove core WooCommerce product sorting options
 * Author: SkyVerge
 * Author URI: http://www.skyverge.com/
 * Version: 1.1.1
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
 */

defined( 'ABSPATH' ) or exit;

/**
 * Plugin Description
 *
 * Remove the selected default WooCommerce sorting options
 */

// Check if WooCommerce is active
if ( ! WC_Remove_Product_Sorting::is_woocommerce_active() ) {
	return;
}

// Make sure we're loaded after WC and fire it up!
add_action( 'plugins_loaded', 'wc_remove_product_sorting' );

/**
 * Class \WC_Remove_Product_Sorting
 * Sets up the main plugin class.
 *
 * @since 1.1.0
 */

class WC_Remove_Product_Sorting {


	const VERSION = '1.1.1';

	/** @var \WC_Remove_Product_Sorting single instance of this plugin */
	protected static $instance;


	/**
	 * WC_Remove_Product_Sorting constructor. Initializes the plugin.
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

		if ( is_admin() && ! is_ajax() ) {

			// add plugin links
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_links' ) );

			$this->install();
		}
	}


	/** Plugin methods ******************************************************/


	/**
	 * Removes sorting option from the Product Settings
	 *
	 * @since 1.1.0
	 *
	 * @param string[] $options the settings options
	 * @return string[] updated options
	 */
	public function remove_sorting_from_settings( $options ) {

		$remove_sorting = self::is_wc_gte( '3.3' ) ? get_theme_mod('wc_remove_product_sorting', array() ) : get_option( 'wc_remove_product_sorting', array() );

		foreach( $remove_sorting as $remove ) {
			unset( $options[ $remove ] );
		}

		return $options;
	}


	/**
	 * Removes sorting option from the shop template
	 *
	 * @since 1.1.0
	 *
	 * @param string[] $orderby the orderby options
	 * @return string[] updated options
	 */
	public function remove_frontend_sorting_option( $orderby ) {

		$remove_options = self::is_wc_gte( '3.3' ) ? get_theme_mod('wc_remove_product_sorting', array() ) : get_option( 'wc_remove_product_sorting', array() );

		foreach( $remove_options as $remove ) {
			unset( $orderby[ $remove ] );
		}

		return $orderby;
	}


	/**
	 * Add Settings to WooCommerce Settings > Products page after "Default Product Sorting" setting
	 *
	 * @since 1.1.0
	 *
	 * @param string[] $settings the settings options
	 * @return string[] updated settings
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
			$catalog_rows_control->priority    = 15;
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
			new WC_Remove_Sorting_Customize_Checkbox_Multiple(
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


	/** Helper methods ******************************************************/


	/**
	 * Main Remove Sorting Instance, ensures only one instance is/can be loaded.
	 *
	 * @since 1.1.0
	 * @see wc_remove_product_sorting()
	 *
	 * @return WC_Remove_Product_Sorting
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
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
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

		$plugin_links = array(
			'<a href="' . esc_url( $configure_url ) . '">' . __( 'Configure', 'woocommerce-extra-product-sorting-options' ) . '</a>',
			'<a href="http://www.skyverge.com/product/woocommerce-remove-product-sorting/">'. __( 'FAQ', 'woocommerce-extra-product-sorting-options' ) . '</a>',
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
	 * Checks if WooCommerce is active.
	 *
	 * @since 1.1.0
	 *
	 * @return bool true if WooCommerce is active, false otherwise
	 */
	public static function is_woocommerce_active() {

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
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


/**
 * Returns the One True Instance of WC Remove Sorting.
 *
 * @since 1.1.0
 *
 * @return WC_Remove_Product_Sorting
 */
function wc_remove_product_sorting() {
	return WC_Remove_Product_Sorting::instance();
}
