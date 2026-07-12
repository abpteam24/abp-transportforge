<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_total_price_template', function ( $post_infos = [] ) {
		$post_id = absint( $post_infos['post_id'] ?? 0 );
		if ( $post_id <= 0 ) {
			return;
		}
		$wc_link = absint( ABPTF_Function::get_post_info( $post_id, 'link_wc_id', 0 ) );
		if ( $wc_link <= 0 ) {
			return;
		}
		$display_additional = $post_infos['display_additional_services'] ?? ABPTF_Function::get_post_info( $post_id, 'display_additional_services', 'on' );
		?>
        <div class="total_continue_area">
            <div class="total_continue item_box_1">
                <div class="_fd_column_max_500">
                    <h5 class="_abp _f_equal">
                        <span><?php esc_html_e( 'Property Rent : ', 'abp-transportforge' ); ?>&nbsp;</span>
                        <span class="item_total _color_theme_text_right"></span>
                    </h5>
					<?php if ( ABPTF_Function::on_off( 'additional_info' ) && $display_additional === 'on' ) { ?>
                        <h5 class="_abp _f_equal">
                            <span><?php esc_html_e( 'Additional : ', 'abp-transportforge' ); ?>&nbsp;</span>
                            <span class="additional_total _color_theme_text_right"></span>
                        </h5>
					<?php } ?>
					<?php if ( ABPTF_Function::on_off( 'deposit' ) ) { ?>
                        <h5 class="_abp _f_equal">
                            <span><?php esc_html_e( 'Deposit : ', 'abp-transportforge' ); ?>&nbsp;</span>
                            <span class="deposit_total _color_theme_text_right"></span>
                        </h5>
					<?php } ?>
                    <div class="_divider_xs"></div>
                    <h5 class="_abp _f_equal">
                        <span><?php esc_html_e( 'Total : ', 'abp-transportforge' ); ?>&nbsp;</span>
                        <span class="abptf_total _color_theme_text_right"></span>
                    </h5>
                </div>
				<?php if ( is_admin() && str_contains( wp_get_referer(), 'admin_order' ) ) { ?>
                    <input type="submit" class="_d_none" name="add-admin-order" value="<?php echo esc_attr( $wc_link ); ?>"/>
				<?php } else { ?>
                    <input type="submit" class="_d_none" name="add-to-cart" value="<?php echo esc_attr( $wc_link ); ?>"/>
				<?php } ?>
                <button class="_btn_light_theme abptf_book_continue" type="button" data-alert="<?php esc_attr_e( 'No property Selected ! Please Select property', 'abp-transportforge' ); ?>" data-msg="<?php esc_attr_e( 'Added to Cart Successfully', 'abp-transportforge' ); ?>">
					<?php esc_html_e( 'Continue', 'abp-transportforge' ); ?>
                    <span class="fas fa-angle-double-right _mar_l_xs"></span>
                </button>
            </div>
        </div>
		<?php
	}, 10, 2 );