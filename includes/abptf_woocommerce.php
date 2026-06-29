<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Woocommerce' ) ) {
		class ABPTF_Woocommerce {
			public function __construct() {
				add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 90, 3 );
				add_action( 'woocommerce_before_calculate_totals', array( $this, 'before_calculate_totals' ), 90 );
				add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'cart_item_thumbnail' ), 90, 3 );
				add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 90, 2 );
				//=============================//
				add_action( 'woocommerce_after_checkout_validation', array( $this, 'after_checkout_validation' ) );
				add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'checkout_create_order_line_item' ), 90, 4 );
				add_action( 'woocommerce_checkout_order_processed', array( $this, 'checkout_order_processed' ) );
				add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'api_checkout_order_processed' ) );
				add_filter( 'woocommerce_order_status_changed', array( $this, 'order_status_changed' ), 90, 4 );
			}
			public function add_cart_item_data( $cart_item, $product_id ) {
				$linked_id = ABPTF_Function::get_post_info( $product_id, 'abptf_link_id', $product_id );
				$post_id   = is_string( get_post_status( $linked_id ) ) ? $linked_id : $product_id;
				if ( get_post_type( $post_id ) == ABPTF_Function::get_cpt() && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'abptf_registration_nonce' ) ) {
					$post_val                       = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
					$start_time                     = $post_val( 'start_time' );
					$end_time                       = $post_val( 'end_time' );
					$rent_rule                      = $post_val( 'rent_rule' );
					$location                       = $post_val( 'location' );
					$date_info                      = ABPTF_Function::get_date_time_difference( $start_time, $end_time, $rent_rule );
					$abptf_infos['post_id']         = $post_id;
					$abptf_infos['start_time']      = $start_time;
					$abptf_infos['end_time']        = $end_time;
					$abptf_infos['rent_rule']       = $rent_rule;
					$abptf_infos['location']        = $location;
					$abptf_infos['date_info']       = $date_info;
					$ticket_info                    = self::get_ticket_info( $abptf_infos );
					$additional_infos               = self::get_additional_info( $post_id );
					$rent_price                     = self::get_price( $ticket_info );
					$ex_price                       = self::get_additional_price( $additional_infos );
					$deposit                        = self::get_deposit_price( $ticket_info );
					$total_price                    = $rent_price + $ex_price + $deposit;
					$cart_item['post_id']           = $post_id;
					$cart_item['start_time']        = $start_time;
					$cart_item['end_time']          = $end_time;
					$cart_item['rent_rule']         = $rent_rule;
					$cart_item['location']          = $location;
					$cart_item['duration']          = $date_info['text'] ?? '';
					$cart_item['ticket_info']       = $ticket_info;
					$cart_item['additional_info']   = $additional_infos;
					$cart_item['pass_info']         = self::get_passenger_info( $post_id );
					$cart_item['rent']              = $rent_price;
					$cart_item['ex_price']          = $ex_price;
					$cart_item['deposit']           = $deposit;
					$cart_item['total_price']       = $total_price;
					$cart_item['line_total']        = $total_price;
					$cart_item['line_subtotal']     = $total_price;
					$cart_item                      = apply_filters( 'abptf_add_cart_item_data', $cart_item, $post_id );
					$_SESSION['abptf_cart_success'] = get_the_title( $post_id ) . ' ' . __( 'Add to cart successfully!', 'abp-transportforge' );
				}

				//echo '<pre>';print_r( $cart_item);					echo '</pre>';die();
				return $cart_item;
			}
			public function before_calculate_totals( $cart_object ): void {
				foreach ( $cart_object->cart_contents as $value ) {
					$post_id = $value['post_id'] ?? 0;
					if ( get_post_type( $post_id ) == ABPTF_Function::get_cpt() ) {
						$total_price = $value['total_price'] ?? 0;
						$value['data']->set_price( $total_price );
						$value['data']->set_regular_price( $total_price );
						$value['data']->set_sale_price( $total_price );
						$value['data']->set_sold_individually( 'yes' );
						$value['data']->get_price();
					}
				}
			}
			public function cart_item_thumbnail( $thumbnail, $cart_item, $item_key ) {
				$post_id = $cart_item['post_id'] ?? 0;
				if ( get_post_type( $post_id ) == ABPTF_Function::get_cpt() ) {
					$url = ABPTF_Function::get_image_url( $post_id ) ?: ABPTF_BLANK_IMG_URL;
					if ( ! empty( $url ) ) {
						$thumbnail = '<div class="abptf_area"><img class="_img_control" src="' . $url . '" data-href="' . get_the_permalink( $post_id ) . '" alt="#"></div>';
					}
				}

				return $thumbnail;
			}
			public function get_item_data( $item_data, $cart_item ) {
				$post_id = $cart_item['post_id'] ?? 0;
				if ( get_post_type( $post_id ) == ABPTF_Function::get_cpt() ) {
					global $post;
					$is_block_cart     = false;
					$is_block_checkout = false;
					if ( is_a( $post, 'WP_Post' ) ) {
						$is_block_cart     = has_block( 'woocommerce/cart', $post->ID );
						$is_block_checkout = has_block( 'woocommerce/checkout', $post->ID );
					}
					if ( is_checkout() && $is_block_checkout ) {
						$item_data = $this->display_cart_item_block( $cart_item );
					} elseif ( is_cart() && $is_block_cart ) {
						$item_data = $this->display_cart_item_block( $cart_item );
					} else {
						ob_start();
						do_action( 'abptf_display_cart_item', $cart_item );
						$content = ob_get_clean();
						if ( ! empty( $content ) ) {
							$item_data[] = array(
								'name' => __( 'Booking Details', 'abp-transportforge' ),
								'value' => $content
							);
						}
					}
				}

				return $item_data;
			}
			public static function get_ticket_info( $abptf_infos = [] ) {
				$booking_info = [];
				if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'abptf_registration_nonce' ) ) {
					$post_int_array = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'absint', wp_unslash( $_POST[ $key ] ) ) : [];
					$post_array     = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) ) : [];
					$post_id        = $abptf_infos['post_id'] ?? '';
					$start_time     = $abptf_infos['start_time'] ?? '';
					$end_time       = $abptf_infos['end_time'] ?? '';
					$rent_rule      = $abptf_infos['rent_rule'] ?? '';
					$date_info      = $abptf_infos['date_info'] ?? [];
					$property_ids   = $post_int_array( 'property_id' );
					$property_check = $post_array( 'property_check' );
					$property_qty   = $post_int_array( 'property_qty' );
					if ( ! empty( $start_time ) && ! empty( $end_time ) && ! empty( $rent_rule ) && ! empty( $date_info ) && sizeof( $date_info ) > 0 && ! empty( $property_ids ) && ! empty( $property_check ) && ! empty( $property_qty ) && ! empty( $post_id ) ) {
						if ( sizeof( $property_check ) > 0 ) {
							foreach ( $property_check as $key => $check ) {
								$property_id = $property_ids[ $key ] ?? '';
								$qty         = $property_qty[ $key ] ?? '';
								if ( ! empty( $check ) && ! empty( $property_id ) && ! empty( $qty ) ) {
									$property = current( ABPTF_Query::get_property( [ 'property_id' => $property_id ] ) );
									if ( ! empty( $property ) ) {
										$abptf_infos['property_id']              = $property_id;
										$abptf_infos['qty']                      = $qty;
										$price                                   = ABPTF_Function::get_price( $abptf_infos, $property );
										$abptf_infos['price']                    = $price;
										$booking_info[ $property_id ]['name']    = $property['name'] ?? '';
										$booking_info[ $property_id ]['price']   = $price;
										$booking_info[ $property_id ]['deposit'] = ABPTF_Function::get_deposit_price( $abptf_infos, $property );
										$booking_info[ $property_id ]['qty']     = $qty;
										$booking_info[ $property_id ]['brand']   = $property['brand'] ?? '';
									}
								}
							}
						}
					}
				}

				return apply_filters( 'abptf_cart_booking_info_filter', $booking_info );
			}
			public static function get_price( $ticket_infos ) {
				$price = 0;
				if ( is_array( $ticket_infos ) && sizeof( $ticket_infos ) > 0 ) {
					foreach ( $ticket_infos as $ticket_info ) {
						$ticket_price = $ticket_info['price'] ?? 0;
						$price        = $price + $ticket_price;
					}
				}

				return $price;
			}
			public static function get_deposit_price( $ticket_infos ) {
				$price = 0;
				if ( is_array( $ticket_infos ) && sizeof( $ticket_infos ) > 0 ) {
					foreach ( $ticket_infos as $ticket_info ) {
						$ticket_price = $ticket_info['deposit'] ?? 0;
						$price        = $price + $ticket_price;
					}
				}

				return $price;
			}
			public static function get_additional_price( $services ) {
				$price = 0;
				if ( is_array( $services ) && sizeof( $services ) > 0 ) {
					foreach ( $services as $service ) {
						$qty = $service['qty'] ?? '';
						if ( ! empty( $qty ) && $qty > 0 ) {
							$ticket_price = $service['price'] ?? 0;
							$price        = $price + $ticket_price * $qty;
						}
					}
				}

				return $price;
			}
			public static function get_additional_info( $post_id ): array {
				$infos = array();
				if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'abptf_registration_nonce' ) ) {
					$display                  = ABPTF_Function::get_post_info( $post_id, 'display_additional_services', 'on' );
					$active_global_additional = ABPTF_Function::get_post_info( $post_id, 'active_global_additional', 'on' );
					if ( $active_global_additional == 'on' ) {
						$services = ABPTF_Function::get_option( 'abptf_additional' );
					} else {
						$services = ABPTF_Function::get_post_info( $post_id, 'abptf_additional', [] );
					}
					if ( $display == 'on' && sizeof( $services ) > 0 ) {
						$abptf_infos['display_additional_services'] = $display;
						$abptf_infos['active_global_additional']    = $active_global_additional;
						$abptf_infos['abptf_additional']            = $services;
						foreach ( $services as $id => $service ) {
							$name     = isset( $_POST[ 'name_' . $id ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'name_' . $id ] ) ) : '';
							$quantity = isset( $_POST[ 'qty_' . $id ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'qty_' . $id ] ) ) : '';
							if ( ! empty( $name ) && ! empty( $quantity ) && $quantity > 0 ) {
								$infos[ $id ]['name']       = $name;
								$infos[ $id ]['qty']        = $quantity;
								$infos[ $id ]['price']      = ABPTF_Function::get_additional_price( $post_id, $name, $abptf_infos );
								$infos[ $id ]['icon']       = $service['icon'] ?? '';
								$infos[ $id ]['returnable'] = $service['returnable'] ?? 'no';
							}
						}
					}
				}

				return $infos;
			}
			public static function get_passenger_info( $post_id ): array {
				$pass_info = [];
				if (ABPTF_Function::on_off( 'client_info' ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'abptf_registration_nonce' ) ) {
					$display       = ABPTF_Function::get_post_info( $post_id, 'display_client_form', 'on' );
					$active_global = ABPTF_Function::get_post_info( $post_id, 'active_global_form', 'on' );
					if ( $active_global == 'on' ) {
						$forms = ABPTF_Function::get_option( 'abptf_forms' );
					} else {
						$forms = ABPTF_Function::get_post_info( $post_id, 'abptf_forms', [] );
					}
					if ( $display == 'on' && sizeof( $forms ) > 0 ) {
						foreach ( $forms as $id => $form ) {
							$info                      = isset( $_POST[ $id ] ) ? sanitize_text_field( wp_unslash( $_POST[ $id ] ) ) : '';
							$pass_info[ $id ]['label'] = $form['label'] ?? '';
							$pass_info[ $id ]['value'] = $info;
						}
					}
				}

				return $pass_info;
			}
			public function display_cart_item_block( $cart_item ): array {
				$start_time      = $cart_item['start_time'] ?? '';
				$end_time        = $cart_item['end_time'] ?? '';
				$duration        = $cart_item['duration'] ?? '';
				$location        = $cart_item['location'] ?? '';
				$ticket_infos    = $cart_item['ticket_info'] ?? [];
				$additional_info = $cart_item['additional_info'] ?? [];
				$attendee_infos  = $cart_item['pass_info'] ?? [];
				$item_data       = [];
				if ( ! empty( $ticket_infos ) && sizeof( $ticket_infos ) > 0 ) {
					$item_data[] = array( 'name' => __( 'Booking Information', 'abp-transportforge' ), 'value' => '<br />' );
					$item_data[] = array( 'name' => __( 'Rent Start', 'abp-transportforge' ), 'value' => ABPTF_Function::date_format( $start_time ) . '<br />' );
					$item_data[] = array( 'name' => __( 'Rent End', 'abp-transportforge' ), 'value' => ABPTF_Function::date_format( $end_time ) . '<br />' );
					$item_data[] = array( 'name' => __( 'Duration', 'abp-transportforge' ), 'value' => $duration . '<br />' );
					if (  ! empty( $location ) ) {
						$item_data[] = array( 'name' => __( 'Location', 'abp-transportforge' ), 'value' => ABPTF_Function::location_value( $location ) . '<br />' );
					}
					$item_data[] = array( 'name' => __( 'Property Information', 'abp-transportforge' ), 'value' => '<br />' );
					foreach ( $ticket_infos as $key => $ticket_info ) {
						$item_data[] = array( 'name' => __( 'Name', 'abp-transportforge' ), 'value' => $ticket_info['name'] . '<br />' );
						$item_data[] = array( 'name' => __( 'Quantity', 'abp-transportforge' ), 'value' => $ticket_info['qty'] . '<br />' );
						$price       = $ticket_info['price'] ?? 0;
						$price       = $price > 0 ? wc_price( $price ) : __( 'FREE', 'abp-transportforge' );
						$item_data[] = array( 'name' => __( 'Rent', 'abp-transportforge' ), 'value' => $price . '<br />' );
						$deposit     = $ticket_info['deposit'] ?? '';
						if (ABPTF_Function::on_off( 'deposit' ) &&  ! empty( $deposit ) ) {
							$item_data[] = array( 'name' => __( 'Deposit', 'abp-transportforge' ), 'value' => wc_price( $deposit ) . '<br />' );
						}
						$brand = $ticket_info['brand'] ?? '';
						if ( ! empty( $brand ) && ABPTF_Function::on_off( 'brand' ) ) {
							$item_data[] = array( 'name' => ABPTF_Function::brand_label(), 'value' => ABPTF_Function::brand_value( $brand ) . '<br />' );
						}
						$item_data = apply_filters( 'abptf_cart_property_info_block', $item_data, $cart_item, $key );
					}
					if (ABPTF_Function::on_off( 'additional_info' ) && ! empty( $additional_info ) && sizeof( $additional_info ) > 0 ) {
						$item_data[] = array( 'name' => __( 'Additional Information', 'abp-transportforge' ), 'value' => '<br />' );
						foreach ( $additional_info as $additional ) {
							if ( is_array( $additional ) ) {
								$qty         = $additional['qty'] ?? 1;
								$price       = $additional['price'] ?? 0;
								$price_text  = $price > 0 ? wc_price( $price ) : __( 'FREE', 'abp-transportforge' );
								$ex_price    = $price > 0 ? wc_price( $price * $qty ) : __( 'FREE', 'abp-transportforge' );
								$item_data[] = array( 'name' => $additional['name'] ?? '', 'value' => $price_text . ' X ' . $qty . '  = ' . $ex_price . '<br />' );
							}
						}
					}
					if (ABPTF_Function::on_off( 'client_info' ) && ! empty( $attendee_infos ) && sizeof( $attendee_infos ) > 0 ) {
						$item_data[] = array( 'name' => __( 'Client Information', 'abp-transportforge' ), 'value' => '<br />' );
						foreach ( $attendee_infos as $attendee_info ) {
							$label = $attendee_info['label'] ?? '';
							$value = $attendee_info['value'] ?? '';
							if ( $label && $value ) {
								$item_data[] = array( 'name' => $label, 'value' => $value . '<br />' );
							}
						}
					}
				}

				return $item_data;
			}
			//=============================//
			public function after_checkout_validation(): void {
				global $woocommerce;
				$cart_items = $woocommerce->cart->get_cart();
				foreach ( $cart_items as $cart_item ) {
					$post_id = $cart_item['post_id'] ?? 0;
					if ( get_post_type( $post_id ) == ABPTF_Function::get_cpt() ) {
						$location                  = $cart_item['location'] ?? '';
						$rent_rule                 = $cart_item['rent_rule'] ?? '';
						$abptf_infos['post_id']    = $cart_item['post_id'] ?? '';
						$abptf_infos['rent_rule']  = $rent_rule;
						$abptf_infos['start_time'] = $cart_item['start_time'] ?? '';
						$abptf_infos['end_time']   = $cart_item['end_time'] ?? '';
						$abptf_infos['location']   = $location;
						$ticket_infos              = $cart_item['ticket_info'] ?? [];
						if ( sizeof( $ticket_infos ) > 0 && ABPTF_Function::check_date_exit( $abptf_infos ) ) {
							foreach ( $ticket_infos as $id => $ticket_info ) {
								$qty = $ticket_info['qty'] ?? '';
								if ( ! empty( $qty ) && $qty > 0 ) {
									$abptf_infos['property_id'] = $id;
									$sold_qty                   = ABPTF_Query::get_sold_qty( $abptf_infos );
									$property                   = current( ABPTF_Query::get_property( [ 'property_id' => $id ] ) );
									$price_qty_info             = json_decode( $property['price_qty_info'] ?? '', true ) ?: [];
									$price_qty_info             = ( ! empty( $location ) && isset( $price_qty_info[ $location ] ) ) ? $price_qty_info[ $location ] : $price_qty_info;
									$price_info                 = $price_qty_info[ $rent_rule ] ?? [];
									$total_qty                  = $price_info['qty'] ?? 0;
									$reserve_qty                = $price_info['reserve'] ?? 0;
									$min_qty                    = ( ( $price_info['min_qty'] ?? 0 ) > 0 ) ? $price_info['min_qty'] : 1;
									$max_qty                    = ( ( $price_info['max_qty'] ?? 0 ) > 0 ) ? $price_info['max_qty'] : 0;
									$available_qty              = $total_qty - $reserve_qty - $sold_qty;
									$available_qty              = $max_qty > 0 ? min( $max_qty, $available_qty ) : $available_qty;
									if ( $qty < $min_qty || $qty > $available_qty ) {
										$woocommerce->cart->empty_cart();
										wc_add_notice( __( "Oh ! We are Sorry, Your Selected Item Already Booked by another . please Try another Item.", 'abp-transportforge' ), 'error' );
									}
								}
							}
						} else {
							$woocommerce->cart->empty_cart();
							wc_add_notice( __( "Oh ! We are Sorry, Something Wrong. please Try another Time.", 'abp-transportforge' ), 'error' );
						}
					}
				}
			}
			public function checkout_create_order_line_item( $item, $key, $cart_item ): void {
				$post_id = $cart_item['post_id'] ?? 0;
				if ( get_post_type( $post_id ) == ABPTF_Function::get_cpt() ) {
					$rent_rule        = $cart_item['rent_rule'] ?? '';
					$start_time       = $cart_item['start_time'] ?? '';
					$end_time         = $cart_item['end_time'] ?? '';
					$book_from        = ( $rent_rule == 'daily' || $rent_rule == 'monthly' ) ? $start_time : ABPTF_Function::booking_buffer( $start_time );
					$book_to          = ( $rent_rule == 'daily' || $rent_rule == 'monthly' ) ? $end_time : ABPTF_Function::booking_buffer( $end_time, true );
					$start_time       = $cart_item['start_time'] ?? '';
					$end_time         = $cart_item['end_time'] ?? '';
					$duration         = $cart_item['duration'] ?? '';
					$location         = $cart_item['location'] ?? '';
					$ticket_infos     = $cart_item['ticket_info'] ?? [];
					$additional_infos = $cart_item['additional_info'] ?? [];
					$attendee_infos   = $cart_item['pass_info'] ?? [];
					if ( ! empty( $ticket_infos ) && sizeof( $ticket_infos ) > 0 ) {
						$item->add_meta_data( __( 'Booking Information', 'abp-transportforge' ), '' );
						$item->add_meta_data( __( 'Rent Start', 'abp-transportforge' ), ABPTF_Function::date_format( $start_time ) );
						$item->add_meta_data( __( 'Rent End', 'abp-transportforge' ), ABPTF_Function::date_format( $end_time ) );
						$item->add_meta_data( __( 'Duration', 'abp-transportforge' ), $duration );
						if ( ! empty( $location ) ) {
							$item->add_meta_data( ABPTF_Function::location_label(), ABPTF_Function::location_value( $location ) );
						}
						$item->add_meta_data( __( 'Property Information', 'abp-transportforge' ), '' );
						$all_brand = '';
						foreach ( $ticket_infos as $ticket_info ) {
							$item->add_meta_data( __( 'Property Name', 'abp-transportforge' ), $ticket_info['name'] );
							$item->add_meta_data( __( 'Quantity', 'abp-transportforge' ), $ticket_info['qty'] );
							$price = $ticket_info['price'] ?? 0;
							$price = $price > 0 ? wc_price( $price ) : __( 'FREE', 'abp-transportforge' );
							$item->add_meta_data( __( 'Rent', 'abp-transportforge' ), $price );
							$deposit = $ticket_info['deposit'] ?? '';
							if (ABPTF_Function::on_off( 'deposit' ) &&  ! empty( $deposit ) ) {
								$item->add_meta_data( __( 'Deposit', 'abp-transportforge' ), wc_price( $deposit ) );
							}
							$brand     = $ticket_info['brand'] ?? '';
							$all_brand = ! empty( $all_brand ) ? $all_brand . ',' . $brand : $brand;
							if ( ! empty( $brand ) && ABPTF_Function::on_off( 'brand' ) ) {
								$item->add_meta_data( ABPTF_Function::brand_label(), ABPTF_Function::brand_value( $brand ) );
							}
						}
						if (ABPTF_Function::on_off( 'additional_info' ) &&  ! empty( $additional_infos ) && sizeof( $additional_infos ) > 0 ) {
							$item->add_meta_data( __( 'Additional Information', 'abp-transportforge' ), '' );
							foreach ( $additional_infos as $additional ) {
								$name       = $additional['name'] ?? '';
								$qty        = $additional['qty'] ?? 1;
								$price      = $additional['price'] ?? 0;
								$price_text = $price > 0 ? wc_price( $price ) : __( 'FREE', 'abp-transportforge' );
								if ( ! empty( $name ) && $qty > 0 ) {
									$ex_price = $price > 0 ? wc_price( $price * $qty ) : __( 'FREE', 'abp-transportforge' );
									$item->add_meta_data( $name, '  ( ' . $price_text . ' X ' . $qty . ') = ' . $ex_price );
								}
							}
						}
						if (ABPTF_Function::on_off( 'client_info' ) && ! empty( $attendee_infos ) && sizeof( $attendee_infos ) > 0 ) {
							$item->add_meta_data( __( 'Client Information', 'abp-transportforge' ), '' );
							foreach ( $attendee_infos as $attendee_info ) {
								$label = $attendee_info['label'] ?? '';
								$value = $attendee_info['value'] ?? '';
								if ( ! empty( $label ) && ! empty( $value ) ) {
									$item->add_meta_data( $label, $value );
								}
							}
						}
						//=============================//
						$item_info = [
							'post_id' => $post_id,
							'user_id' => get_current_user_id(),
							'start_time' => $start_time,
							'end_time' => $end_time,
							'book_from' => $book_from,
							'book_to' => $book_to,
							'duration' => $duration,
							'location' => $location,
							'brand' => $all_brand,
							'rent_rule' => $rent_rule,
							'ticket_info' => $ticket_infos,
							'additional_info' => $additional_infos,
							'pass_info' => $attendee_infos,
							'rent' => $cart_item['rent'] ?? '',
							'ex_price' => $cart_item['ex_price'] ?? '',
							'deposit' => $cart_item['deposit'] ?? '',
							'item_total' => $cart_item['total_price'] ?? '',
						];
						$item_info = apply_filters( 'abptf_checkout_create_order_line_item', $item_info, $cart_item );
						$item->add_meta_data( '_abptf_items', $item_info, true );
					}
				}
			}
			public static function save_custom_data( $order_id ): void {
				if ( $order_id ) {
					$order               = wc_get_order( $order_id );
					$order_status        = $order->get_status();
					$order_meta          = get_post_meta( $order_id );
					$payment_method      = $order_meta['_payment_method_title'][0] ?? '';
					$user_id             = $order_meta['_customer_user'][0] ?? '';
					$_billing_first_name = $order_meta['_billing_first_name'][0] ?? '';
					$_billing_last_name  = $order_meta['_billing_last_name'][0] ?? '';
					$billing_email       = $order_meta['_billing_email'][0] ?? '';
					$billing_phone       = $order_meta['_billing_phone'][0] ?? '';
					$_billing_address_1  = $order_meta['_billing_address_1'][0] ?? '';
					$_billing_address_2  = $order_meta['_billing_address_2'][0] ?? '';
					$billing_name        = $_billing_first_name . ' ' . $_billing_last_name;
					$billing_address     = $_billing_address_1 . ' ' . $_billing_address_2;
					$booked_status       = ABPTF_Function::booking_status();
					$booked_status       = $booked_status ? explode( ',', $booked_status ) : [];
					if ( $order_status != 'failed' ) {
						$total_order = ABPTF_Query::get_booking_query( [ 'order_id' => $order_id ], 0, 0, true );
						if ( $total_order == 0 ) {
							foreach ( $order->get_items() as $item_id => $item ) {
								$item_info = wc_get_order_item_meta( $item_id, '_abptf_items' );
								if ( ! empty( $item_info ) && is_array( $item_info ) && sizeof( $item_info ) > 0 ) {
									$post_id = $item_info['post_id'] ?? '';
									if ( ! empty( $post_id ) && get_post_type( $post_id ) == ABPTF_Function::get_cpt() ) {
										$ticket_infos    = $item_info['ticket_info'] ?? [];
										$start_time      = $item_info['start_time'] ?? '';
										$end_time        = $item_info['end_time'] ?? '';
										$book_from       = $item_info['book_from'] ?? '';
										$book_to         = $item_info['book_to'] ?? '';
										$additional_info = $item_info['additional_info'] ?? '';
										global $wpdb;
										$table_name = $wpdb->prefix . 'abptf_orders';
										if ( ! empty( $ticket_infos ) && sizeof( $ticket_infos ) > 0 ) {
											$property_id = $ex_id = [];
											foreach ( $ticket_infos as $key => $ticket_info ) {
												$property_id[] = $key;
											}
											if ( ! empty( $additional_info ) && sizeof( $additional_info ) > 0 ) {
												foreach ( $additional_info as $key => $additional ) {
													$ex_id[] = $key;
												}
											}
											$price_info['rent']       = $item_info['rent'] ?? '';
											$price_info['ex_price']   = $item_info['ex_price'] ?? '';
											$price_info['deposit']    = $item_info['deposit'] ?? '';
											$price_info['item_total'] = $item_info['item_total'] ?? '';
											$others['rent_rule']      = $item_info['rent_rule'] ?? '';
											$others['duration']       = $item_info['duration'] ?? '';
											$_order_status             = 'wc-' . $order_status;
											$data                     = [
												'order_id' => intval( $order_id ),
												'item_id' => intval( $item_id ),
												'post_id' => intval( $post_id ),
												'user_id' => intval( $user_id ),
												'property_id' => wp_json_encode( $property_id ),
												'ex_id' => wp_json_encode( $ex_id ),
												'pick_up' => sanitize_text_field( $item_info['pick_up'] ?? '' ),
												'start_time' => sanitize_text_field( $start_time ),
												'drop_off' => sanitize_text_field( $item_info['drop_off'] ?? '' ),
												'end_time' => sanitize_text_field( $end_time ),
												'book_from' => sanitize_text_field( $book_from ),
												'book_to' => sanitize_text_field( $book_to ),
												'category' => sanitize_text_field( get_post_meta( $post_id, 'category', true ) ),
												'location' => sanitize_text_field( $item_info['location'] ?? '' ),
												'brand' => sanitize_text_field( $item_info['brand'] ?? '' ),
												'price_info' => wp_json_encode( $price_info ),
												'property_info' => wp_json_encode( $ticket_infos ),
												'ex_info' => wp_json_encode( $additional_info ),
												'pass_info' => wp_json_encode( $item_info['pass_info'] ?? [] ),
												'delivery_option' => 0,
												'book_status' => in_array( $_order_status, $booked_status ) ? 1 : 0,
												'order_status' => sanitize_text_field( $_order_status ),
												'payment_method' => sanitize_text_field( $payment_method ),
												'billing_name' => sanitize_text_field( $billing_name ),
												'billing_email' => sanitize_text_field( $billing_email ),
												'billing_phone' => sanitize_text_field( $billing_phone ),
												'billing_address' => sanitize_text_field( $billing_address ),
												'others' => wp_json_encode( $others ),
												'created_at' => current_time( 'Y-m-d H:i' ),
												'updated_at' => current_time( 'Y-m-d H:i' )
											];
											// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
											$wpdb->insert( $table_name, $data );
										}
									}
								}
							}
						}
					}
				}
			}
			public function checkout_order_processed( $order_id ): void {
				self::save_custom_data( $order_id );
			}
			public function api_checkout_order_processed( $order ): void {
				$this->checkout_order_processed( $order->get_id() );
			}
			public function order_status_changed( $order_id ): void {
				if ( ! empty( $order_id ) && $order_id > 0 ) {
					global $wpdb;
					$table_name   = $wpdb->prefix . 'abptf_orders';
					$order        = wc_get_order( $order_id );
					$order_status = $order->get_status();
					foreach ( $order->get_items() as $item_id => $item_values ) {
						if ( $item_id ) {
							$order_infos = ABPTF_Query::get_booking_query( [ 'item_id' => $item_id ] );
							if ( ! empty( $order_infos ) && sizeof( $order_infos ) > 0 ) {
								$order_info = current( $order_infos );
								$others     = $order_info['others'] ?? '';
								if ( ! empty( $others ) ) {
									$others               = json_decode( $others, true );
									$user_id              = get_current_user_id();
									$others['updated_by'] = $user_id;
									$data                 = [
										'others' => wp_json_encode( $others ),
										'order_status' => 'wc-' . $order_status,
										'updated_at' => current_time( 'Y-m-d H:i' )
									];
									$where                = [ 'item_id' => $item_id ];
									// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
									$wpdb->update( $table_name, $data, $where, [ '%s', '%s', '%s' ], [ '%d' ] );
									$mail_send = apply_filters( 'abptf_send_mail', false, $item_id );
								}
							}
						}
					}
				}
			}
		}
		new ABPTF_Woocommerce();
	}
