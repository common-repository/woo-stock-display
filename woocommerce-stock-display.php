<?PHP
/*
 * Plugin Name: Woocommerce Stock Display
 * Plugin URI: https://finalrope.com/final-plugins/woocommerce-stock-display
 * Description: Woocommerce Stock Display plugin would display a nice looking stocks on single product pages for simple and variation products.
 * Author: FinalRope, Ravikas kamboj
 * Author URI: http://finalrope.com
 * Version: 1.1.0
 * Text Domain: woocommerce-stock-display

 * Copyright FinalRope Plugins
 *
 *		This file is part of Woocommerce Stock Display,
 *		a plugin for WordPress.
 *
 *		Woocommerce Stock Display is free software:
 *		You can redistribute it and/or modify it under the terms of the
 *		GNU General Public License as published by the Free Software
 *		Foundation, either version 3 of the License, or (at your option)
 *		any later version.
 *
 *		Woocommerce Stock Display is distributed in the hope that
 *		it will be useful, but WITHOUT ANY WARRANTY; without even the
 *		implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 *		PURPOSE. See the GNU General Public License for more details.
 *
 *		You should have received a copy of the GNU General Public License
 *		along with WordPress. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WSD_stockdisplay.
 *
 * Main FinalRope class initializes the plugin.
 *
 * @class		WSD_stockdisplay
 * @version		1.1.0
 * @author		Ravikas kamboj
 */
class WSD_stockdisplay {


	/**
	 * Plugin version.
	 *
	 * @since 1.1.0
	 * @var string $version Plugin version number.
	 */
	public $version = '1.1.0';


	/**
	 * Plugin file.
	 *
	 * @since 1.1.0
	 * @var string $file Plugin file path.
	 */
	public $file = __FILE__;


	/**
	 * Instance of WSD_stockdisplay.
	 *
	 * @since 1.1.0
	 * @access private
	 * @var object $instance The instance of WSD_stockdisplay.
	 */
	private static $instance;


	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) :
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		endif;

		// Check if WooCommerce is active
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :
			if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) :
				return;
			endif;
		endif;

		$this->init();

	}


	/**
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @since 1.1.0
	 * @return object Instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) :
			self::$instance = new self();
		endif;

		return self::$instance;

	}


	/**
	 * Init.
	 *
	 * Initialize plugin parts.
	 *
	 * @since 1.1.0
	 */
	public function init() {

		if ( is_admin() ) :

			/**
			 * Admin panel
			 */
			require_once plugin_dir_path( __FILE__ ) . 'admin/WSD-finalrope-admin-class.php';
			$this->admin = new WSD_finalropeAdmin();

			/**
			 * Bulk edit Admin panel
			 */
			require_once plugin_dir_path( __FILE__ ) . 'admin/WSD-finalrope-bulk-edit-class.php';
			$this->bulk_edit = new WSD_finalropeAdminBulkEdit();

			/**
			 * Quick edit Admin panel
			 */
			require_once plugin_dir_path( __FILE__ ) . 'admin/WSD-finalrope-quick-edit-class.php';
			$this->quick_edit = new WSD_finalropeAdminQuickEdit();

		endif;

		// Add the display stock
		add_action( 'woocommerce_single_product_summary', array( $this, 'WSD_stockShow' ), 45 );

		// Enqueue style
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style' ) );
		// Enqueue style
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_style' ) );

	}


	/**
	 * Display stock.
	 *
	 * Add the display stock to product page.
	 *
	 * @since 1.1.0
	 *
	 * @global WC_Product_Variable $product Get product object.
	 */
	public function WSD_stockShow() {

		global $product;
		$displayWSD_stockShow = get_post_meta( $product->id, 'WSD_stockShow', true );

		if ( 'no' == $displayWSD_stockShow || empty ( $displayWSD_stockShow ) ) :
			return;
		endif;

		?>
		<h3 class='display-stock-title'><?php _e( 'Available Stocks', 'WSD_woocommerceStockShow' ); ?></h3>
		<div class='display-stock'><?php

			if ( 'variable' == $product->product_type ) :

				// Loop variations
				$available_variations = $product->get_available_variations();
				foreach ( $available_variations as $variation ) :

					$max_stock 	= $product->get_total_stock();
					$var 		= wc_get_product( $variation['variation_id'] );

					if ( true == $var->variation_has_stock ) :

						// Get variation name
						$WSD_variationName = $this->WSD_variationName( $variation['attributes'] );

						// Get an display stock_bar
						$this->WSD_getDisplayStockBar( $variation['variation_id'], $max_stock, $WSD_variationName );

					endif;

				endforeach;

			endif;

			if ( 'simple' == $product->product_type ) :

				$this->WSD_getDisplayStockBar( $product->id, $product->get_total_stock(), $product->get_formatted_name() );

			endif;

		?></div><?php

	}


	/**
	 * Stock stock_bar.
	 *
	 * Get an single stock stock_bar.
	 *
	 * @since 1.1.0
	 *
	 * @param int		$product_id 		ID of the product.
	 * @param int 		$max_stock 			Stock quantity of the variation with the most stock.
	 * @param string 	$WSD_variationName 	Name of the variation.
	 */
	public function WSD_getDisplayStockBar( $product_id, $max_stock, $WSD_variationName ) {
		$saved_values_arr = get_option('stock_options'); 
		$saved_values = $saved_values_arr['stock_field_pill'];
		$stock 		= get_post_meta( $product_id, '_stock', true );
		if ($max_stock>0) {
			$percentage = round( $stock / $max_stock * 100 );
		} else {
			$percentage = 0;
		}
		?><div class='<?php echo $saved_values; ?> stock_bar-wrap'>

			<div class='<?php echo $saved_values; ?> variation-name'><?php echo $WSD_variationName; ?></div>

			<div class='<?php echo $saved_values; ?> stock_bar'>
				<div class='<?php echo $saved_values; ?> stock_fill<?php if ( 0 == $stock ) { echo ' out-of-stock'; } ?>' style='width: <?php echo $percentage; ?>%;'><?php echo (int) $stock; ?></div>
			</div>

		</div><?php

	}


	/**
	 * Variation name.
	 *
	 * Get the variation name based on the attributes.
	 *
	 * @since 1.1.0
	 *
	 * @param 	array 	$attributes 	All the attributes of the variation
	 * @return 	string 					Variation name based on attributes.
	 */
	public function WSD_variationName( $attributes ) {

		$WSD_variationName = '';

		foreach ( $attributes as $attr => $value ) :

			if ( term_exists( $value, str_replace( 'attribute_', '', $attr ) ) ) :

				$term = get_term_by( 'slug', $value, str_replace( 'attribute_', '', $attr ) );
				if ( isset( $term->name ) ) :
					$WSD_variationName .= $term->name . ', ';

				endif;

			else :

				$WSD_variationName .= $value . ', ';

			endif;

		endforeach;

		return rtrim( $WSD_variationName, ', ' );

	}


	/**
	 * Enqueue style.
	 *
	 * @since 1.1.0
	 */
	public function enqueue_style() {
		wp_enqueue_style( 'WSD_woocommerceStockShow', plugins_url( 'assets/css/WSD_woocommerceStockShow.css', __FILE__ ) );
	}
	public function enqueue_admin_style() {
		wp_enqueue_style( 'WSD_woocommerceStockShow', plugins_url( 'assets/css/WSD_woocommerceStockAdmin.css', __FILE__ ) );
	}

}

/**
 * WooCommerce Stock Display options and settings
 */
function WSD_finalStockSettingsInit() {
	 /* register a new setting for "stock" page */
	 register_setting( 'stock', 'stock_options' );
	 
	 /* register a new section in the "stock" page */
	 add_settings_section(
	 'stock_section_developers',
	 __( 'Choose The Design', 'stock' ),
	 'WSD_stockSectionDevelopersCb',
	 'stock'
	 );
	 
	 /* register a new field in the "stock_section_developers" section, inside the "stock" page */
	 add_settings_field(
	 'stock_field_pill', 
	 __( 'Layouts', 'stock' ),
	 'WSD_stockFieldPillCb',
	 'stock',
	 'stock_section_developers',
	 [
	 'label_for' => 'stock_field_pill',
	 'class' => 'stock_row',
	 'stock_custom_data' => 'custom',
	 ]
	 );
}
 
/**
 * register our WSD_finalStockSettingsInit to the admin_init action hook
 */
add_action( 'admin_init', 'WSD_finalStockSettingsInit' );
 
/**
 * custom option and settings:
 * callback functions
 */
function WSD_stockSectionDevelopersCb( $args ) {
	?>
		( Translations, feature requests, ratings and donations are welcome and appreciated )
		&nbsp;<a class="donate_link" target="_blank" href="https://www.paypal.me/finalrope">Donate</a>
	<?php
}
 
/* pill field cb
 
 * field callbacks can accept an $args parameter, which is an array.
 * $args is defined at the add_settings_field() function.
 * wordpress has magic interaction with the following keys: label_for, class.
 * the "label_for" key value is used for the "for" attribute of the <label>.
 * the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * you can add custom key value pairs to be used inside your callbacks. 
*/
function WSD_stockFieldPillCb( $args ) {
	 /* get the value of the setting we've registered with register_setting() */
	 $options = get_option( 'stock_options' );
	 /* output the field */
	 ?>
	 <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
	 data-custom="<?php echo esc_attr( $args['stock_custom_data'] ); ?>"
	 name="stock_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
	 >
		 <option value="simple" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'simple', false ) ) : ( '' ); ?>>
		 <?php esc_html_e( 'Simple', 'stock' ); ?>
		 </option>
		 <option value="graphical" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'graphical', false ) ) : ( '' ); ?>>
		 <?php esc_html_e( 'Graphical', 'stock' ); ?>
		 </option>
	 </select>
	 <?php
}
 
/**
 * top level menu
 */
function WSD_stockOptionsPage() {
	 /* add top level menu page */
	 add_menu_page(
	 'Woocommerce Stock Display Settings',
	 'Stock Options',
	 'manage_options',
	 'stock',
	 'WSD_stocOptionsPageHtml',
	 plugins_url( 'woo-stock-display/assets/images/icon.png' ),
	 56
	 );
}
 
/**
 * register our WSD_stockOptionsPage to the admin_menu action hook
 */
add_action( 'admin_menu', 'WSD_stockOptionsPage' );
 
/**
 * top level menu:
 * callback functions
 */
function WSD_stocOptionsPageHtml() {
	 /* check user capabilities */
	 if ( ! current_user_can( 'manage_options' ) ) {
		return;
	 }
 
	 /* add error/update messages
	 
	 * check if the user have submitted the settings
	 * wordpress will add the "settings-updated" $_GET parameter to the url
	 */
	 if ( isset( $_GET['settings-updated'] ) ) {
	 /* add settings saved message with the class of "updated" */
		add_settings_error( 'stock_messages', 'stock_message', __( 'Settings Saved', 'stock' ), 'updated' );
	 }
 
	 /* show error/update messages */
	 settings_errors( 'stock_messages' );
	 ?>
	 <div class="wrap">
		 <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		 <form action="options.php" method="post">
			 <?php
			 /*  output security fields for the registered setting "stock" */
			 settings_fields( 'stock' );
			 /* output setting sections and their fields
			 * (sections are registered for "stock", each field is registered to a specific section)
			 */
			 do_settings_sections( 'stock' );
			 /* output save settings button */
			 submit_button( 'Save Settings' );
			 ?>
		 </form>
		 
	 </div>
 <?php
}

/* Add settings link on plugin page */
function WSD_finalStockSettingsLink($links) { 
	  $settings_link = '<a href="admin.php?page=stock">Settings</a>'; 
	  array_unshift($links, $settings_link); 
	  return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'WSD_finalStockSettingsLink' );

/**
 * The main function responsible for returning the WSD_stockdisplay object.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php WSD_stockdisplay()->method_name(); ?>
 *
 * @since 1.1.0
 *
 * @return object WSD_stockdisplay class object.
 */
if ( ! function_exists( 'WSD_stockdisplay' ) ) :

 	function WSD_stockdisplay() {
		return WSD_stockdisplay::instance();
	}

endif;

WSD_stockdisplay();