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
 * needs please refer to http://www.skyverge.com/product/woocommerce-remove-product-sorting/ for more information.
 *
 * @package   WC-Remove-Product-Sorting
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Returns the One True Instance of \SkyVerge\WooCommerce\RemoveProductSorting\Plugin
 *
 * @since 1.0.0
 *
 * @return \SkyVerge\WooCommerce\RemoveProductSorting\Plugin
 */
function wc_remove_product_sorting() {
	return \SkyVerge\WooCommerce\RemoveProductSorting\Plugin::instance();
}
