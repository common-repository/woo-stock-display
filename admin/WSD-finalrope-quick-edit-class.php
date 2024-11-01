<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 *	Class WSD_finalropeAdminQuickEdit.
 *
 *	Add an option to the quick edit settings.
 *
 *	@class       WSD_finalropeAdminQuickEdit
 *	@version     1.1.0
 *	@author      Ravikas kamboj
 */
class WSD_finalropeAdminQuickEdit {

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {

		// Add select to bulk edit
		add_action( 'woocommerce_product_quick_edit_end', array( $this, 'WSD_quickEditStockShow' ) );

		// Save bulk edit display stock setting
		add_action( 'woocommerce_product_quick_edit_save', array( $this, 'WSD_quickEditSave' ) );

	}


	/**
	 * Quick edit.
	 *
	 * Add option to quick edit.
	 *
	 * @since 1.1.0
	 */
	public function WSD_quickEditStockShow() {

		?><div class="display-stock-field">
			<label class="alignleft">
			    <span class="title"><?php _e( 'Display stock', 'WSD_woocommerceStockShow' ); ?></span>
			    <span class="input-text-wrap">
			    	<select class="display-stock" name="WSD_stockShow"><?php

						$options = array(
							''		=> '— No Change —',
							'yes'	=> 'Display stock',
							'no'	=> 'Don\'t display Stock',
						);
						foreach ( $options as $key => $value 	) {
							echo '<option value="' . esc_attr( $key ) . '">'. $value .'</option>';
						}

					?></select>
				</span>
			</label>
		</div><?php

	}


	/**
	 * Save quick edit.
	 *
	 * Save the quick edit, only when variable.
	 *
	 * @since 1.1.0
	 *
	 * @param object $product Product object.
	 */
	public function WSD_quickEditSave( $product ) {

		if ( $product->is_type( 'variable' ) ) :
			if ( ! empty( $_REQUEST['WSD_stockShow'] ) ) :
				update_post_meta( $product->id, 'WSD_stockShow', wc_clean( $_REQUEST['WSD_stockShow'] ) );
			endif;
		endif;

	}


}
