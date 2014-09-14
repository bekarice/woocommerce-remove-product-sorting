<?php
/**
 * Plugin Name: WooCommerce Remove Product Sorting
 * Plugin URI: http://www.skyverge.com/product/woocommerce-remove-product-sorting/
 * Description: Remove core WooCommerce product sorting options
 * Author: SkyVerge
 * Author URI: http://www.skyverge.com/
 * Version: 1.0.0
 * Text Domain: woocommerce-remove-product-sorting
 *
 * Copyright: (c) 2012-2014 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Remove-Product-Sorting
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2012-2014, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */
 
/**
 * Plugin Description
 * Remove the selected default WooCommerce sorting options
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
/**
 * Add Settings to WooCommerce Settings > Products page after "Default Product Sorting" setting
 *
 * @since 1.0.0
 */
function skyverge_wc_remove_sorting_add_settings( $settings ) {

	$updated_settings = array();

	foreach ( $settings as $setting ) {

		$updated_settings[] = $setting;

		if ( isset( $setting['id'] ) && 'woocommerce_default_catalog_orderby' === $setting['id'] ) {

			$updated_settings[] = array(
					'name'     => __( 'Remove Product Sorting:', 'woocommerce' ),
					'desc_tip' => __( 'Select sorting options to remove from your shop.', 'woocommerce' ),
					'id'       => 'wc_remove_product_sorting',
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => array(
						'menu_order'   	=> __( 'Default Sorting', 'woocommerce' ),
						'popularity'  	=> __( 'Popularity', 'woocommerce' ),
						'rating'  		=> __( 'Average Rating', 'woocommerce' ),
						'date'   		=> __( 'Most Recent', 'woocommerce' ),
						'price'      	=> __( 'Price (Asc)', 'woocommerce' ),
						'price-desc' 	=> __( 'Price (Desc)', 'woocommerce' ),
					),
					'default'  => '',
			);

		}
	}
	return $updated_settings;
}
add_filter( 'woocommerce_product_settings', 'skyverge_wc_remove_sorting_add_settings' );


/**
 * Removes sorting option from the Product Settings
 * 
 * @since 1.0
 */
function skyverge_remove_sorting_from_settings( $options ) {

	$remove_sorting = get_option( 'wc_remove_product_sorting', array() );
	
	foreach( $remove_sorting as $remove ) {
		
		unset( $options[ $remove ] );
		
	}
    
    return $options;
}
add_filter( 'woocommerce_default_catalog_orderby_options', 'skyverge_remove_sorting_from_settings' );


/**
 * Removes sorting option from the shop template
 *
 * @since 1.0
 */
function skyverge_remove_wc_sorting_option( $catalog_orderby_options ) {

	$remove_options = get_option( 'wc_remove_product_sorting', array() );
	
	foreach( $remove_options as $remove ) {
	
		unset( $catalog_orderby_options[ $remove ] );
		
	}
	
	return $catalog_orderby_options;
}
add_filter( 'woocommerce_catalog_orderby', 'skyverge_remove_wc_sorting_option' );