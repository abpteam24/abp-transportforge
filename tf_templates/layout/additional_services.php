<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_additional_template', function ( $post_id, $post_infos = [] ) {
		if ( ABPTF_Function::on_off( 'additional_info' ) ) {
			$post_id = absint( $post_id );
			if ( $post_id <= 0 ) {
				return;
			}
			$display                  = $post_infos['display_additional_services'] ?? ABPTF_Function::get_post_info( $post_id, 'display_additional_services', 'on' );
			$active_global_additional = $post_infos['active_global_additional'] ?? ABPTF_Function::get_post_info( $post_id, 'active_global_additional', 'on' );
			if ( $active_global_additional === 'on' ) {
				$additional_services = ABPTF_Function::get_option( 'abptf_additional' );
			} else {
				$additional_services = $post_infos['abptf_additional'] ?? ABPTF_Function::get_post_info( $post_id, 'abptf_additional', [] );
			}
			if ( $display === 'on' && ! empty( $additional_services ) && is_array( $additional_services ) ) {
				$ex_count = 0;
				?>
                <div class="additional_service_area">
                    <div class="item_box_1 additional_service">
                        <h5 class="_abp_title"><?php esc_html_e( 'Additional services ( Optional ) : ', 'abp-transportforge' ); ?></h5>
						<?php foreach ( $additional_services as $id => $service ) {
							if ( ! is_array( $service ) ) {
								continue;
							}
							$icon_image  = $service['icon'] ?? '';
							$name        = $service['name'] ?? '';
							$price       = $service['price'] ?? '';
							$tax_price   = ( ! empty( $price ) && $price > 0 ) ? ABPTF_Function::tax_with_price( $post_id, $price ) : 0;
							$qty         = $service['qty'] ?? '';
							$max_qty     = $service['max_qty'] ?? '';
							$returnable  = $service['returnable'] ?? 'no';
							$description = $service['description'] ?? '';
							if ( empty( $qty ) ) {
								$qty = ! empty( $max_qty ) ? $max_qty : 9999;
							}
							$sold_qty = 0;
							if ( $qty < 9999 ) {
								$post_infos['ex_id'] = $id;
								$sold_qty             = ABPTF_Query::get_sold_qty_ex( $post_infos );
							}
							$available = $qty - $sold_qty;
							$max_qty   = ! empty( $max_qty ) ? min( $max_qty, $available ) : $available;
							if ( $ex_count > 0 ) { ?>
                                <div class="_divider_xs"></div>
							<?php }
							$ex_count ++; ?>
                            <div class="service_item _d_flex">
                                <div class="_w_100 _fs_h3_all_center">
									<?php ABPTF_Layout::image_icon( $icon_image,'' ); ?>
                                </div>
                                <div class="_fd_column_w_full">
                                    <div class="_fj_between">
                                        <h6 class="_abp_fa_center"><?php echo esc_html( $name ); ?></h6>
										<?php if ( $available > 0 ) { ?>
                                            <input type="hidden" name="name_<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $name ); ?>"/>
											<?php
											$input_info = [
												'name' => 'qty_' . $id,
												'price' => $tax_price,
												'min_qty' => 0,
												'max_qty' => $max_qty,
												'class' => 'ex_price_calculate',
											];
											ABPTF_Layout::quantity_input( $input_info );
										} else { ?>
                                            <span class="_color_warning"><?php esc_html_e( 'Not Available !', 'abp-transportforge' ); ?></span>
										<?php } ?>
                                    </div>
                                    <h5 class="_abp_color_theme">
										<?php
											if ( $tax_price > 0 ) {
												echo wp_kses_post( wc_price( $tax_price ) );
											} else {
												esc_html_e( 'Free', 'abp-transportforge' );
											}
											if ( $returnable === 'yes' ) { ?>
                                                <span class="trash"><?php esc_html_e( 'Returnable', 'abp-transportforge' ); ?></span>
											<?php } else { ?>
                                                <span class="publish"><?php esc_html_e( 'Nor-Returnable', 'abp-transportforge' ); ?></span>
											<?php } ?>
                                    </h5>
                                    <p class="_abp"><?php echo esc_html( $description ); ?></p>
                                </div>
                            </div>
						<?php } ?>
                    </div>
                </div>
				<?php
			}
		}
	}, 10, 2 );