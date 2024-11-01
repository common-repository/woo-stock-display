<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 *	Class WSD_finalropeAdminBulkEdit
 *
 *	Add an option to the bulk edit settings.
 *
 *	@class       WSD_finalropeAdminBulkEdit
 *	@version     1.1.0
 *	@author      Ravikas kamboj
 */
class WSD_finalropeAdminBulkEdit {


	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {

		// Add select to bulk edit
		add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'WSD_bulkEditStockShow' ) );

		// Save bulk edit display stock setting
		add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'WSD_bulkEditSave' ) );

	}


	/**
	 * Bulk edit.
	 *
	 * Add option to bulk edit.
	 *
	 * @since 1.1.0
	 */
	public function WSD_bulkEditStockShow() {

		?><div class="display-stock-field">
			<label>
			    <span class="title"><?php _e( 'Display stock', 'WSD_woocommerceStockShow' ); ?></span>
			    <span class="input-text-wrap">
			    	<select class="display-stock" name="WSD_stockShow"><?php

						$options = array(
							''		=> '— No Change —',
							'yes'	=> 'Display stock',
							'no'	=> 'Don\'t display Stock',
						);
						foreach ( $options as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '">'. $value .'</option>';
						}

					?></select>
				</span>
			</label>
		</div><?php

	}


	/**
	 * Save bulk edit.
	 *
	 * Save the bulk edit, only when variable.
	 *
	 * @since 1.1.0
	 *
	 * @param $product WC_Product
	 */
	public function WSD_bulkEditSave( $product ) {

		if ( $product->is_type( 'variable' ) ) :
			if ( ! empty( $_REQUEST['WSD_stockShow'] ) ) :
				update_post_meta( $product->id, 'WSD_stockShow', wc_clean( $_REQUEST['WSD_stockShow'] ) );
			endif;
		endif;

	}


}
