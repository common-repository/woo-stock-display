<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 *	Class WSD_stockdisplay
 *
 *	Main FinalRope class initializes the plugin
 *
 *	@class       WSD_stockdisplay
 *	@version     1.1.0
 *	@author      Ravikas kamboj
 */
class WSD_finalropeAdmin {


	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {

		// Add checkbox to general products panel
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'WSD_generalProductDataTab' ) );

		// Save checkbox from general products panel
		add_action( 'woocommerce_process_product_meta_simple', array( $this, 'WSD_processProductMeta' ) );
		add_action( 'woocommerce_process_product_meta_variable', array( $this, 'WSD_processProductMeta' ) );

	}


	/**
	 * Add checkbox.
	 *
	 * Add checkbox to the general products data tab (when variable).
	 *
	 * @since 1.1.0
	 */
	public function WSD_generalProductDataTab() {

		?><div class='options_group show_if_simple show_if_variable'>

			<div class='wac_stock'><?php

				woocommerce_wp_checkbox( array(
					'id' 			=> 'WSD_stockShow',
					'wrapper_class' => 'show_if_simple show_if_variable',
					'label' 		=> 'Display stock',
					'description' 	=> ''
				) );

			?></div><?php

		?></div><?php

	}


	/**
	 * Save setting.
	 *
	 * Save the display stock setting.
	 *
	 * @since 1.1.0
	 */
	public function WSD_processProductMeta( $post_id ) {

		if ( ! empty( $_POST['WSD_stockShow'] ) ) :
			update_post_meta( $post_id, 'WSD_stockShow', 'yes' );
		else :
			update_post_meta( $post_id, 'WSD_stockShow', 'no' );
		endif;

	}

}
