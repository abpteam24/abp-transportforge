<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_display_cart_item_template', function ( $cart_item = [] ) {
		$start_time      = $cart_item['start_time'] ?? '';
		$end_time        = $cart_item['end_time'] ?? '';
		$duration        = $cart_item['duration'] ?? '';
		$location        = $cart_item['location'] ?? '';
		$ticket_infos    = $cart_item['ticket_info'] ?? [];
		$additional_info = $cart_item['additional_info'] ?? [];
		$attendee_infos  = $cart_item['pass_info'] ?? [];
		if ( ! empty( $ticket_infos ) && is_array( $ticket_infos ) ) {
			?>
            <div class="abptf_area">
                <div class="_section_xs">
                    <h6 class="_abp _color_theme"><?php esc_html_e( 'Booking Information : ', 'abp-transportforge' ); ?></h6>
                    <div class="_divider_xxs"></div>
                    <ul class="_abp cart_list">
                        <li>
                            <span class="fas fa-calendar-check _mar_r_xs"></span>
                            <span class="_fs_label"><?php esc_html_e( 'Rent Start : ', 'abp-transportforge' ); ?></span>&nbsp;<?php echo esc_html( ABPTF_Function::date_format( $start_time ) ); ?>
                        </li>
                        <li>
                            <span class="fas fa-calendar-check _mar_r_xs"></span>
                            <span class="_fs_label"><?php esc_html_e( 'Rent End : ', 'abp-transportforge' ); ?></span>&nbsp;<?php echo esc_html( ABPTF_Function::date_format( $end_time ) ); ?>
                        </li>
                        <li>
                            <span class="fas fa-business-time _mar_r_xs"></span>
                            <span class="_fs_label"><?php esc_html_e( 'Duration : ', 'abp-transportforge' ); ?></span>&nbsp;<?php echo esc_html( $duration ); ?>
                        </li>
						<?php if (  ! empty( $location ) ) { ?>
                            <li>
                                <span class="fas fa-location _mar_r_xs"></span>
                                <span class="_fs_label"><?php esc_html_e( 'Location : ', 'abp-transportforge' ); ?></span>&nbsp;<?php echo esc_html( ABPTF_Function::location_value( $location ) ); ?>
                            </li>
						<?php } ?>
                    </ul>
                </div>
                <div class="_section_xs">
                    <h6 class="_abp _color_theme"><?php esc_html_e( 'Property Information : ', 'abp-transportforge' ); ?></h6>
					<?php
						foreach ( $ticket_infos as $ticket_info ) {
							if ( ! is_array( $ticket_info ) ) {
								continue;
							}
							$ticket_name = $ticket_info['name'] ?? '';
							$ticket_qty  = $ticket_info['qty'] ?? 0;
							$brand       = $ticket_info['brand'] ?? '';
							$price       = $ticket_info['price'] ?? 0;
							$price_html  = $price > 0 ? wc_price( $price ) : __( 'FREE', 'abp-transportforge' );
							$deposit     = $ticket_info['deposit'] ?? '';
							?>
                            <div class="_divider_xxs"></div>
                            <ul class="_abp cart_list">
                                <li><span class="_fs_label"><?php esc_html_e( 'Name : ', 'abp-transportforge' ); ?></span><?php echo esc_html( $ticket_name ); ?></li>
                                <li><span class="_fs_label"><?php esc_html_e( 'Quantity : ', 'abp-transportforge' ); ?></span><?php echo esc_html( $ticket_qty ); ?></li>
                                <li><span class="_fs_label"><?php esc_html_e( 'Rent : ', 'abp-transportforge' ); ?></span><?php echo wp_kses_post( $price_html ); ?></li>
								<?php if ( ABPTF_Function::on_off( 'deposit' ) && ! empty( $deposit ) ) { ?>
                                    <li><span class="_fs_label"><?php esc_html_e( 'Deposit : ', 'abp-transportforge' ); ?></span><?php echo wp_kses_post( wc_price( $deposit ) ); ?></li>
								<?php } ?>
								<?php if ( ! empty( $brand ) && ABPTF_Function::on_off( 'brand' ) ) { ?>
                                    <li><span class="_fs_label"><?php echo esc_html( ABPTF_Function::brand_label() ); ?></span><?php echo esc_html( ABPTF_Function::brand_value( $brand ) ); ?></li>
								<?php } ?>
                            </ul>
						<?php } ?>
                </div>
				<?php if ( ABPTF_Function::on_off( 'additional_info' ) && ! empty( $additional_info ) && is_array( $additional_info ) ) { ?>
                    <div class="_section_xs">
                        <h6 class="_abp _color_theme"><?php esc_html_e( 'Additional Information : ', 'abp-transportforge' ); ?></h6>
                        <div class="_divider_xxs"></div>
                        <ul class="_abp cart_list">
							<?php
								foreach ( $additional_info as $additional ) {
									if ( ! is_array( $additional ) || empty( $additional ) ) {
										continue;
									}
									$icon_image = $additional['icon'] ?? '';
									$name       = $additional['name'] ?? '';
									$qty        = $additional['qty'] ?? 1;
									$price      = $additional['price'] ?? 0;
									$price_text = $price > 0 ? wc_price( $price ) : __( 'FREE', 'abp-transportforge' );
									$ex_price   = $price > 0 ? wc_price( $price * $qty ) : __( 'FREE', 'abp-transportforge' );
									?>
                                    <li class="_f_wrap">
										<?php ABPTF_Layout::image_icon( $icon_image); ?>
										<?php echo esc_html( $name . __( ' : ', 'abp-transportforge' ) ); ?>
										<?php echo wp_kses_post( $price_text ) . ' X ' . esc_html( $qty ) . ' = ' . wp_kses_post( $ex_price ); ?>
                                    </li>
								<?php } ?>
                        </ul>
                    </div>
				<?php } ?>

				<?php if ( ABPTF_Function::on_off( 'client_info' ) && ! empty( $attendee_infos ) && is_array( $attendee_infos ) ) { ?>
                    <div class="_section_xs">
                        <h6 class="_abp _color_theme"><?php esc_html_e( 'Client Information : ', 'abp-transportforge' ); ?></h6>
                        <div class="_divider_xxs"></div>
                        <ul class=" _abp cart_list">
							<?php
								foreach ( $attendee_infos as $attendee_info ) {
									if ( ! is_array( $attendee_info ) ) {
										continue;
									}
									$label = $attendee_info['label'] ?? '';
									$value = $attendee_info['value'] ?? '';
									if ( ! empty( $label ) && ! empty( $value ) ) { ?>
                                        <li>
                                            <span class="_abp_label"><?php echo esc_html( $label . __( ' : ', 'abp-transportforge' ) ); ?></span>
											<?php echo esc_html( $value ); ?>
                                        </li>
										<?php
									}
								}
							?>
                        </ul>
                    </div>
				<?php } ?>
            </div>
			<?php
		}
	}, 10, 2 );