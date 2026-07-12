<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_property_item_template', function ( $post_infos, $property = [] ) {
		//echo '<pre>';print_r($property);echo '</pre>';
		if ( is_array( $property ) && sizeof( $property ) > 0 ) {
			$location       = $post_infos['location'] ?? '';
			$start_time     = $post_infos['start_time'] ?? '';
			$end_time       = $post_infos['end_time'] ?? '';
			$post_id        = $property['post_id'] ?? '';
			$cat_id         = ABPTF_Function::get_post_info( $post_id, 'abptf_category' );
			$loc_id         = ABPTF_Function::get_post_info( $post_id, 'abptf_location' );
			$rent_rule      = $property['rent_rule'] ?? '';
			$price_qty_info = json_decode( $property['price_qty_info'] ?? '', true ) ?: [];
			if ( ! empty( $location ) && isset( $price_qty_info[ $location ] ) ) {
				$price_qty_info = $price_qty_info[ $location ];
			}
			$price_info = $price_qty_info[ $rent_rule ] ?? [];
			$others     = json_decode( $property['others'] ?? '', true ) ?: [];
			if ( ! empty( $rent_rule ) && ! empty( $price_info ) ) {
				$time_duration                = ABPTF_Function::time_duration( $post_infos, $price_info );
				$total_price                  = ABPTF_Function::get_price( $post_infos, $property, $time_duration );
				$property_name                = $property['name'] ?? '';
				$post_infos['property_name'] = $property_name;
				$post_infos['property_id']   = $property['id'] ?? '';
				//echo '<pre>';print_r($property );echo '</pre>';
				?>
                <div class="property_item item_box_1 <?php echo esc_attr( $post_infos['add_class'] ?? '' ); ?>" data-cat_id="<?php echo esc_attr( $cat_id ); ?>" data-loc_id="<?php echo esc_attr( $loc_id ); ?>">
                    <div class="item_head">
						<?php ABPTF_Layout::image_icon( ($others['icon'] ?? ''),'' ); ?>
                    </div>
                    <div class="item_body">
                        <div>
                            <h5 class="_abp list_title">
								<?php echo esc_html( $property_name ); ?>
								<?php if ( ($property['brand'] ?? '') && ABPTF_Function::on_off( 'brand' )  ) { ?>
                                    <small class="_abp_color_theme"><?php echo esc_html( ABPTF_Function::brand_value( $property['brand'] ?? '' ) ); ?></small>
								<?php } ?>
                            </h5>
							<?php ABPTF_Layout::item_feature( $property['features'] ?? '' ); ?>
                            <div class="pricing_box">
                                <div class="price_row">
									<?php ABPTF_Layout::item_price( $post_id, $rent_rule, $price_info ); ?>
                                </div>
                                <div class="item_condition">
									<?php echo esc_html( ABPTF_Layout::item_condition( $rent_rule, $price_info ) ); ?>
                                </div>
								<?php ABPTF_Layout::item_deposit( $price_info );
									if ( ! empty( $start_time ) && ! empty( $end_time ) ) {
										ABPTF_Layout::item_cost( $post_infos, $price_info, $total_price, $time_duration );
									}
								?>
                            </div>
                        </div>
						<?php
							if ( ! empty( $time_duration ) && ! empty( $start_time ) && ! empty( $end_time ) ) {
								ABPTF_Layout::item_select_property( $post_infos, $price_info, $total_price );
							}
							if ( $post_infos['add_class'] ?? '' ) { ?>
                                <div>
                                    <div class="_divider_xs"></div>
                                    <button type="button" class="_btn_theme_xs" data-href="<?php echo esc_url( get_the_permalink( $post_id ) ); ?>" data-blank="_blank">
										<?php esc_html_e( 'Book Now', 'abp-transportforge' ); ?>
                                    </button>
                                </div>
							<?php } ?>
                    </div>
                </div>
				<?php
			}
		}
	}, 10, 2 );