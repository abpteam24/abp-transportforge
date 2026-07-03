<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Orders' ) ) {
		class ABPTF_Orders {
			public function __construct() {
				add_action( 'abptf_load_orders', [ $this, 'load_orders' ] );
				add_action( 'wp_ajax_abptf_load_order_list', [ $this, 'load_order_list' ] );
				add_action( 'wp_ajax_abptf_item_cancel', [ $this, 'item_cancel' ] );
			}
			public function load_orders(): void {
				?>
                <div class="abptf_orders _abp_panel">
                    <div class="_panel_head_ov_auto">
                        <h4 class="_abp"><span class="_mar_r_xxs">📋</span> <?php esc_html_e( 'Order Filter', 'abp-transportforge' ); ?></h4>
                    </div>
                    <div class="_panel_body_ov_initial">
                        <form class="load_order_list" method="post" action="">
                            <div class="_form_inline">
								<?php
									ABPTF_Layout::filter_post_list();
									ABPTF_Layout::filter_booking_date_between();
									ABPTF_Layout::filter_booking_date();
									ABPTF_Layout::filter_order_date();
									ABPTF_Layout::filter_user_id();

										ABPTF_Layout::filter_location();

									ABPTF_Layout::filter_order_id();
									ABPTF_Layout::filter_order_date_between();
									ABPTF_Layout::filter_bill_name();
									ABPTF_Layout::filter_bill_email();
									ABPTF_Layout::filter_bill_phone();
								?>
                            </div>
                            <div class="_form_inline_mar_t_xs">
                                <div class="_input_item">
                                    <button type="submit" class="_btn_theme_xs_w_full">
                                        <span class="_mar_r_xs">🔎</span><?php esc_html_e( 'Search', 'abp-transportforge' ); ?>
                                    </button>
                                </div>
                                <div class="_input_item">
                                    <button class="_btn_theme_xs _w_full" title="<?php esc_attr_e( 'More Options', 'abp-transportforge' ); ?>" type="button" data-collapse-target="#view_more_filter_option"
                                            data-close-text="👁️ <?php esc_attr_e( 'More Options', 'abp-transportforge' ); ?>" data-open-text="🙈  <?php esc_attr_e( 'Close Options', 'abp-transportforge' ); ?>"
                                    >
                                        <span data-text>👁️ <?php esc_html_e( 'More Options', 'abp-transportforge' ); ?></span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="order_list">
						<?php $this->order_lists(); ?>
                    </div>
                </div>
				<?php
			}
			public function order_lists( $filter_args = [] ): void {
				$page_number           = isset( $filter_args['page_number'] ) && is_numeric( $filter_args['page_number'] ) ? (int) $filter_args['page_number'] : 1;
				$limit                 = isset( $filter_args['page_item'] ) && is_numeric( $filter_args['page_item'] ) ? (int) $filter_args['page_item'] : ABPTF_Function::get_option( 'abptf_per_page_item', 20 );
				$post_id               = isset( $filter_args['post_id'] ) && is_numeric( $filter_args['post_id'] ) ? (int) $filter_args['post_id'] : 0;
				$data_status           = ! empty( $filter_args['status'] ) ? sanitize_text_field( $filter_args['status'] ) : '';
				$si                    = ( $page_number - 1 ) * $limit + 1;
				$offset                = $si - 1;
				$booking_lists         = ABPTF_Query::get_booking_query( $filter_args, $limit, $offset );
				$total_order           = ABPTF_Query::get_booking_query( $filter_args, 0, 0, true );
				$filter_args['status'] = 'all';
				$label                 = ABPTF_Function::label();
				$brand_icon            = ABPTF_Function::icon();
				$booked_status         = ABPTF_Function::booking_status();
				$booked_status         = $booked_status ? explode( ',', $booked_status ) : [];
				$_filter_args          = $filter_args;
				$total_rent            = 0;
				$total_additional      = 0;
				$total_deposit         = 0;
				$total_sale            = 0;
				//echo '<pre>';print_r($filter_args);echo '</pre>';
				$count_foot_left_col  = 5;
				$count_foot_right_col = 3;
				?>
                <div class="_panel_head_xs_ov_auto _fj_between">
                    <div class="_group_content order_status_menu">
                        <button class="_btn_white_xs_text_nowrap <?php echo esc_attr( $data_status === 'all' ? 'abp_active' : '' ); ?>" type="button" data-status="all" title="<?php esc_attr_e( 'All Booking', 'abp-transportforge' ); ?>">
							<?php echo esc_html( __( 'All Booking', 'abp-transportforge' ) . ' (' . ABPTF_Query::get_booking_query( $filter_args, 0, 0, true ) . ' )' ) ?>
                        </button>
                        <button class="_btn_white_xs_text_nowrap <?php echo esc_attr( ! $data_status ? 'abp_active' : '' ); ?>" type="button" data-status="" title="<?php esc_attr_e( 'Booking Completed', 'abp-transportforge' ); ?>">
							<?php
								$filter_args['status'] = '';
								echo esc_html( __( 'Booking Completed', 'abp-transportforge' ) . ' (' . ABPTF_Query::get_booking_query( $filter_args, 0, 0, true ) . ' )' );
							?>
                        </button>
						<?php
							$all_status = wc_get_order_statuses();
							if ( ! empty( $all_status ) && is_array( $all_status ) ) {
								foreach ( $all_status as $key => $status ) {
									?>
                                    <button class="_btn_white_xs_text_nowrap <?php echo esc_attr( $data_status === $key ? 'abp_active' : '' ); ?>" type="button" data-status="<?php echo esc_attr( $key ); ?>">
										<?php
											$filter_args['status'] = sanitize_key( $key );
											echo esc_html( $status . ' (' . ABPTF_Query::get_booking_query( $filter_args, 0, 0, true ) . ')' );
										?>
                                    </button>
									<?php
								}
							}
						?>
                    </div>
					<?php do_action( 'abptf_order_tab_action', $_filter_args ); ?>
                </div>
                <div class="_panel_body">
					<?php if ( ! empty( $booking_lists ) && is_array( $booking_lists ) ) { ?>
                        <table class=" _abp">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'Action', 'abp-transportforge' ); ?></th>
                                <th><?php esc_html_e( 'Order ID/ Date', 'abp-transportforge' ); ?></th>
								<?php if ( $post_id === 0 ) { ?>
                                    <th><?php ABPTF_Layout::image_icon( $brand_icon ); ?><?php echo esc_html( $label ); ?></th>
								<?php } else {
									$count_foot_left_col --;
								} ?>
                                <th class="_min_150"><?php esc_html_e( 'Rent Time', 'abp-transportforge' ); ?></th>
								<?php
									$count_foot_left_col ++; ?>
                                    <th><?php echo esc_html( ABPTF_Function::location_label() ); ?></th>

                                <th><?php esc_html_e( 'Property Info', 'abp-transportforge' ); ?></th>
								<?php if ( ABPTF_Function::on_off( 'additional_info' ) ) {
									$count_foot_left_col ++; ?>
                                    <th><?php esc_html_e( 'Additional Info', 'abp-transportforge' ); ?></th>
								<?php } ?>
                                <th><?php esc_html_e( 'Rent ', 'abp-transportforge' ); ?></th>
								<?php if ( ABPTF_Function::on_off( 'additional_info' ) ) { ?>
                                    <th><?php esc_html_e( 'Additional ', 'abp-transportforge' ); ?></th>
								<?php } ?>
								<?php if ( ABPTF_Function::on_off( 'deposit' ) ) { ?>
                                    <th><?php esc_html_e( 'Deposit ', 'abp-transportforge' ); ?></th>
								<?php } ?>
                                <th><?php esc_html_e( 'Total ', 'abp-transportforge' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'abp-transportforge' ); ?></th>
                                <th><?php esc_html_e( 'Payment Method', 'abp-transportforge' ); ?></th>
                                <th><?php esc_html_e( 'Billing Info', 'abp-transportforge' ); ?></th>
								<?php if ( ABPTF_Function::on_off( 'client_info' ) ) {
									$count_foot_right_col ++; ?>
                                    <th><?php esc_html_e( 'Client Info', 'abp-transportforge' ); ?></th>
								<?php } ?>
                            </tr>
                            </thead>
                            <tbody>
							<?php foreach ( $booking_lists as $booking_list ) {
								$item_id          = $booking_list['item_id'] ?? '';
								$_post_id         = $booking_list['post_id'] ?? '';
								$order_id         = $booking_list['order_id'] ?? '';
								$status           = $booking_list['order_status'] ?? '';
								$order_time       = $booking_list['created_at'] ?? '';
								$price_info       = json_decode( $booking_list['price_info'] ?? '', true ) ?: [];
								$total_price      = $price_info['item_total'] ?? 0;
								$rent             = $price_info['rent'] ?? 0;
								$ex_price         = $price_info['ex_price'] ?? 0;
								$deposit          = $price_info['deposit'] ?? 0;
								$total_sale       = $total_sale + (int) ( $total_price );
								$total_rent       = $total_rent + (int) ( $rent );
								$total_additional = $total_additional + (int) ( $ex_price );
								$total_deposit    = $total_deposit + (int) ( $deposit );
								$ticket_infos     = json_decode( $booking_list['property_info'] ?? '', true ) ?: [];
								$passenger_infos  = json_decode( $booking_list['pass_info'] ?? '', true ) ?: [];
								$additional_infos = json_decode( $booking_list['ex_info'] ?? '', true ) ?: [];
								$others           = json_decode( $booking_list['others'] ?? '', true ) ?: [];
								$start_time       = $booking_list['start_time'] ?? '';
								$start_date       = ! empty( $start_time ) ? gmdate( 'Y-m-d', strtotime( $start_time ) ) : '';
								$end_time         = $booking_list['end_time'] ?? '';
								$end_date         = ! empty( $end_time ) ? gmdate( 'Y-m-d', strtotime( $end_time ) ) : '';
								$end_time_format  = strtotime( $start_date ) === strtotime( $end_date ) ? 'time' : 'full';
								?>
                                <tr>
                                    <th>
                                        <div class="_group_content">
											<?php do_action( 'abptf_order_action', $item_id );
												if ( in_array( $status, $booked_status, true ) ) { ?>
                                                    <button class="_btn_light_danger_xxs abptf_item_cancel" data-item_id="<?php echo esc_attr( $item_id ); ?>" title="<?php esc_attr_e( 'Rent Cancel', 'abp-transportforge' ); ?>" type="button"><span class="fas fa-times"></span></button>
												<?php } ?>
                                        </div>
                                    </th>
                                    <th class="_text_left">
                                        <p class="_abp"><?php echo esc_html( $si . '. #' . $order_id ); ?></p>
                                        <p class="_abp_fs_label_color_theme"><?php echo esc_html( ABPTF_Function::date_format( $order_time ) ); ?></p>
                                    </th>
									<?php if ( $post_id === 0 ) { ?>
                                        <th class="_text_left"><?php ABPTF_Layout::title( $_post_id ); ?></th>
									<?php } ?>
                                    <td>
										<?php echo esc_html( ABPTF_Function::date_format( $start_time, 'full' ) . '-' . ABPTF_Function::date_format( $end_time, $end_time_format ) ); ?>
                                        <p class="_abp_fs_label_color_theme"><?php echo esc_html( $others['duration'] ?? '' ) . '-' . esc_html( ABPTF_Layout::rent_rules( $others['rent_rule'] ?? '' ) ); ?></p>
                                    </td>

                                        <td><?php echo esc_html( ABPTF_Function::location_value( $booking_list['location'] ?? '' ) ); ?></td>

                                    <th><?php ABPTF_Layout::ticket_info( $ticket_infos ); ?></th>
									<?php if ( ABPTF_Function::on_off( 'additional_info' ) ) { ?>
                                        <td><?php ABPTF_Layout::additional_info( $additional_infos ); ?></td>
									<?php } ?>
                                    <th><?php echo $rent > 0 ? wp_kses_post( wc_price( $rent ) ) : esc_html__( 'FREE', 'abp-transportforge' ); ?></th>
									<?php if ( ABPTF_Function::on_off( 'additional_info' ) ) { ?>
                                        <th><?php echo $ex_price > 0 ? wp_kses_post( wc_price( $ex_price ) ) : esc_html__( 'FREE', 'abp-transportforge' ); ?></th>
									<?php } ?>
									<?php if ( ABPTF_Function::on_off( 'deposit' ) ) { ?>
                                        <th><?php echo $deposit > 0 ? wp_kses_post( wc_price( $deposit ) ) : esc_html__( 'FREE', 'abp-transportforge' ); ?></th>
									<?php } ?>
                                    <th><?php echo $total_price > 0 ? wp_kses_post( wc_price( $total_price ) ) : esc_html__( 'FREE', 'abp-transportforge' ); ?></th>
                                    <th class="_text_capitalize">
                                        <p class="_abp <?php echo esc_attr( ABPTF_Layout::status_text( $status ) ); ?>"> <?php echo esc_html( ABPTF_Layout::status_text( $status ) ); ?></p>
                                        <p class="_abp <?php echo esc_attr( $booking_list['book_status'] ?? '' ); ?>">
											<?php
												$book_status = $booking_list['book_status'] ?? '';
												$book_status = ABPTF_Layout::get_book_status( $order_id, $start_time, $end_time, $book_status );
												echo esc_html( ABPTF_Layout::book_status_text( $book_status ) ); ?>
                                        </p>
                                    </th>
                                    <th class="_text_capitalize"><?php echo esc_html( $booking_list['payment_method'] ?? '' ); ?></th>
                                    <td>
                                        <div class="info_text load_more">
                                            <?php ABPTF_Layout::billing_info( $booking_list ); ?>
                                            <span class="load_more_action" data-less="<?php esc_attr_e( '....Less ', 'abp-transportforge' ); ?>" data-more="<?php esc_attr_e( '....More', 'abp-transportforge' ); ?>"><?php esc_html_e( '.... More', 'abp-transportforge' ); ?></span>
                                        </div>
                                    </td>
									<?php if ( ABPTF_Function::on_off( 'client_info' ) ) { ?>
                                        <td>
											<?php if ( ! empty( $passenger_infos ) ) { ?>
                                                <div class="info_text load_more">
                                                    <?php ABPTF_Layout::client_info( $passenger_infos ); ?>
                                                    <span class="load_more_action" data-less="<?php esc_html_e( '....Less ', 'abp-transportforge' ); ?>" data-more="<?php esc_html_e( '.... More', 'abp-transportforge' ); ?>"><?php esc_html_e( '.... More', 'abp-transportforge' ); ?></span>
                                                </div>
											<?php } ?>
                                        </td>
									<?php } ?>
                                </tr>
								<?php $si ++;
							} ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th colspan="<?php echo esc_attr( $count_foot_left_col ); ?>"><?php esc_html_e( 'Total Summary', 'abp-transportforge' ); ?></th>
                                <th><?php echo ( ! empty( $total_rent ) && $total_rent > 0 ) ? wp_kses_post( wc_price( $total_rent ) ) : esc_html__( 'FREE', 'abp-transportforge' ); ?></th>
								<?php if ( ABPTF_Function::on_off( 'additional_info' ) ) { ?>
                                    <th><?php echo ( ! empty( $total_additional ) && $total_additional > 0 ) ? wp_kses_post( wc_price( $total_additional ) ) : esc_html__( 'FREE', 'abp-transportforge' ); ?></th>
								<?php } ?>
								<?php if ( ABPTF_Function::on_off( 'deposit' ) ) { ?>
                                    <th><?php echo ( ! empty( $total_deposit ) && $total_deposit > 0 ) ? wp_kses_post( wc_price( $total_deposit ) ) : esc_html__( 'FREE', 'abp-transportforge' ); ?></th>
								<?php } ?>
                                <th><?php echo ( ! empty( $total_sale ) && $total_sale > 0 ) ? wp_kses_post( wc_price( $total_sale ) ) : esc_html__( 'FREE', 'abp-transportforge' ); ?></th>
                                <th colspan="<?php echo esc_attr( $count_foot_right_col ); ?>"></th>
                            </tr>
                            </tfoot>
                        </table>
					<?php } else {
						ABPTF_Layout::layout_warning_info( 'no_order_found' );
					}
						do_action( 'abptf_pagination', [ 'page_item' => $limit, 'page_number' => $page_number, 'total' => $total_order, 'style' => 'ajax' ] ); ?>
                </div>
				<?php
			}
			public function load_order_list(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ),'type'=>'warn'  ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ),'type'=>'warn'  ], 403 );
				}
				ob_start();
				$filter_args              = isset( $_POST ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) : [];
				$limit                    = isset( $filter_args['page_item'] ) ? (int) $filter_args['page_item'] : 20;
				$data_limit               = (int) ABPTF_Function::get_option( 'abptf_per_page_item', 20 );
				$filter_args['page_item'] = $limit > 0 ? $limit : $data_limit;
				if ( $limit > 0 && $data_limit !== $limit ) {
					update_option( 'abptf_per_page_item', $limit );
				}
				$this->order_lists( $filter_args );
				$html = ob_get_clean();
				wp_send_json_success( [ 'html' => $html, 'msg' => esc_html__( 'Order Loaded Successfully !', 'abp-transportforge' ) , 'type' => 'success'] );
			}
			public function item_cancel(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ), 'type' => 'warn'  ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ), 'type' => 'warn'  ], 403 );
				}
				$item_id = isset( $_POST['item_id'] ) ? sanitize_text_field( wp_unslash( $_POST['item_id'] ) ) : '';
				if ( ! empty( $item_id ) ) {
					global $wpdb;
					$table_name    = $wpdb->prefix . 'abptf_orders';
					$booking_lists = ABPTF_Query::get_booking_query( [ 'item_id' => $item_id ] );
					if ( ! empty( $booking_lists ) && is_array( $booking_lists ) ) {
						$value  = current( $booking_lists );
						$others = $value['others'] ?? '';
						if ( ! empty( $others ) ) {
							$others              = json_decode( $others, true ) ?: [];
							$user_id             = get_current_user_id();
							$others['cancel_by'] = $user_id;
							$data                = [
								'others' => wp_json_encode( $others ),
								'book_status' => 5,
								'order_status' => 'wc-cancelled',
								'updated_at' => current_time( 'Y-m-d H:i:s' )
							];
							$where               = [ 'item_id' => (int) $item_id ];
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$wpdb->update( $table_name, $data, $where, [ '%s', '%s', '%s' ], [ '%d' ] );
						}
					}
					wp_send_json_success( [ 'html' => '', 'msg' => esc_html__( 'Deleted Successfully !', 'abp-transportforge' ), 'type' => 'success' ] );
				}
				wp_send_json_error( [ 'html' => '', 'msg' => esc_html__( 'Something Error Occurred !', 'abp-transportforge' ), 'type' => 'warn'  ] );
			}
		}
		new ABPTF_Orders();
	}