## WooCommerce Remove Product Sorting
 - Use this plugin on your site? [Make a donation!](https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal@skyverge.com&item_name=Donation+for+WooCommerce+Remove+Product+Sorting) 
 - Requires WordPress at least: 4.4
 - Tested up to: 4.9.6
 - Requires WooCommerce at least: 2.6.14
 - Tested WooCommerce up to: 3.4.2
 - Stable Tag: 1.2.0
 - License: GPLv3
 - License URI: http://www.gnu.org/licenses/gpl-3.0.html

Remove any core WooCommerce product sorting option from the shop template and admin.

### Description

WooCommerce Remove Product Sorting Options lets you select which WooCommerce sorting options you'd like to remove from your shop, such as the "Sort by popularity" option.

> **Requires: WooCommerce 2.6.14+**, Compatible with WooCommerce 3.4+

### Features
Lets you remove one or more of the core WooCommerce sorting options.

Really, that's it :)

### Troubleshooting

The only possible way this plugin doesn't work is if your theme overrides the default WooCommerce sorting dropdown. If you activate this and your sorting options are not removed, chances are your theme is trying to create a "fancy" dropdown instead of the default one, and it has hard-coded the default WooCommerce options.

Get in touch with your theme author, and ask them to properly generate the dropdown from the WooCommerce options rather than hard-coding them into the theme.

### Want to add sorting options?
We've got a plugin for that, too. Check out the [WooCommerce Extra Product Sorting Options](http://wordpress.org/plugins/woocommerce-extra-product-sorting-options/) plugin.

### More Details
 - See the [product page](http://www.skyverge.com/product/woocommerce-remove-product-sorting/) for full details.
 - View more of SkyVerge's [free WooCommerce extensions](https://www.skyverge.com/downloads/category/free/)
 - View all [SkyVerge WooCommerce extensions](http://www.skyverge.com/shop/)

### Installation

**Important**: Do not install a zip of this repo as a plugin! You must bundle dependencies with composer if you do so using `$ composer install` to complete the build.

 1. Be sure you're running WooCommerce 2.6.14+ in your shop.
 1. Download the plugin zip from [SkyVerge.com](https://www.skyverge.com/product/woocommerce-remove-product-sorting/) (this also gives you a key for one-click updates)
 1. Upload the entire `woocommerce-remove-product-sorting` folder to the `/wp-content/plugins/` directory, or upload the .zip file with the plugin under **Plugins &gt; Add New &gt; Upload**
 1. Activate the plugin through the **Plugins** menu in WordPress
 1. Go to **Appearance &gt; Customize &gt; WooCommerce** and click the "Product catalog" section. The new settings are added after "Default Product Sorting".
    - If you use an older version of WooCommerce, you'll see setting under WooCommerce &gt; Settings &gt; Products &gt; Display
 1. Select which options to remove and save your settings.

### Frequently Asked Questions

**Will this permanently remove these options from my shop?**

Only while the plugin is active or you have these sorting options selected under the settings. You can get them back by removing them from the "Remove Sorting Options" setting or disabling the plugin.

**This is handy! Can I contribute?**

Yes you can! Join in on our [GitHub repository](https://github.com/bekarice/woocommerce-remove-product-sorting/) and submit a pull request :)

### Screenshots
1. Plugin Settings under **WooCommerce &gt; Settings &gt; Products**
2. Sorting options on the shop page with some removed

### Changelog

2018.06.05 - version 1.2.0
 * Tweak - Remove the sorting dropdown if all sorting options are removed
 * Misc - Require PHP 5.3
 * Misc - Require WooCommerce 2.6.14

2018.02.09 - version 1.1.1
 * Fix - PHP warnings for themes that don't support WooCommerce product column and row settings

2018.02.08 - version 1.1.0
 * Misc - Add support for WooCommerce 3.3
 
2014.09.18 - version 1.0.0
 * Initial Release
