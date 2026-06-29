<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Function' ) ) {
		class ABPTF_Function {
			public function __construct() {
			}

			public static function get_cpt(): string { return 'abptf_post'; }

			public static function get_post_info( $post_id, $key, $default = '' ) {
				$data = get_post_meta( $post_id, $key, true ) ?: $default;

				return self::data_sanitize( $data );
			}

			public static function data_sanitize( $data ) {
				$data = maybe_unserialize( $data );
				if ( is_string( $data ) ) {
					$data = maybe_unserialize( $data );
					if ( is_array( $data ) ) {
						$data = self::data_sanitize( $data );
					} else {
						$data = sanitize_text_field( stripslashes( wp_strip_all_tags( $data ) ) );
					}
				} elseif ( is_array( $data ) ) {
					foreach ( $data as &$value ) {
						if ( is_array( $value ) ) {
							$value = self::data_sanitize( $value );
						} else {
							$value = sanitize_text_field( stripslashes( wp_strip_all_tags( $value ) ) );
						}
					}
				}

				return $data;
			}

			public static function get_all_meta( $post_id = 0 ): array {
				$all_data = [];
				if ( $post_id > 0 ) {
					$all_data['post_title'] = get_the_title( $post_id );
					$all_data['post_id']    = $post_id;
					$metas                  = get_post_meta( $post_id );
					if ( ! empty( $metas ) && sizeof( $metas ) > 0 ) {
						foreach ( $metas as $key => $meta ) {
							$all_data[ $key ] = self::data_sanitize( $meta[0] );
						}
					}
				}

				return $all_data;
			}

			public static function get_taxonomy( $name ): array|WP_Error|string {
				return get_terms( array( 'taxonomy' => $name, 'hide_empty' => false ) );
			}

			public static function get_all_term_data( $term_name ): array {
				$all_data   = [];
				$taxonomies = self::get_taxonomy( $term_name );
				if ( ! empty( $taxonomies ) && is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
					foreach ( $taxonomies as $taxonomy ) {
						$all_data[ $taxonomy->term_id ] = $taxonomy->name;
					}
				}

				return $all_data;
			}

			public static function get_option( $option, $default = [] ) {
				$option_data = get_option( sanitize_key( $option ) );

				return ! empty( $option_data ) ? $option_data : $default;
			}

			public static function get_options( $option, $key, $default = '' ) {
				$options = get_option( sanitize_key( $option ) );
				if ( isset( $options[ $key ] ) && $options[ $key ] ) {
					$default = $options[ $key ];
				}

				return $default;
			}

			public static function booking_status() { return ( ABPTF_Configuration['booked_status'] ?? null ) ?: 'wc-processing,wc-completed'; }

			public static function label() { return ( ABPTF_Configuration['label'] ?? null ) ?: __( 'TransportForge', 'abp-transportforge' ); }

			public static function slug() { return ( ABPTF_Configuration['slug'] ?? null ) ?: 'transport-forge'; }

			public static function icon_wp() { return ( ABPTF_Configuration['icon'] ?? null ) ?: 'dashicons-tickets'; }

			public static function icon() { return ( ABPTF_Configuration['brand_icon'] ?? null ) ?: '🚐'; }

			public static function brand_label() { return ( ABPTF_Configuration['brand_label'] ?? null ) ?: __( 'Brand', 'abp-transportforge' ); }

			public static function brand_slug() { return ( ABPTF_Configuration['brand_slug'] ?? null ) ?: 'brand'; }

			public static function brand_value( $id ) { return ( ABPTF_Brands[ $id ]['name'] ?? null ) ?: $id; }

			public static function category_label() { return ( ABPTF_Configuration['category_label'] ?? null ) ?: __( 'Category', 'abp-transportforge' ); }

			public static function category_slug() { return ( ABPTF_Configuration['cat_slug'] ?? null ) ?: 'category'; }

			public static function category_value( $id ) { return ( ABPTF_Category[ $id ]['name'] ?? null ) ?: $id; }
			public static function organizer_label() { return ( ABPTF_Configuration['organizer_label'] ?? null ) ?: __( 'Organizer', 'abp-transportforge' ); }

			public static function organizer_slug() { return ( ABPTF_Configuration['org_slug'] ?? null ) ?: 'organizer'; }

			public static function organizer_value( $id ) { return ( ABPTF_Organizer[ $id ]['name'] ?? null ) ?: $id; }

			public static function location_label() { return ( ABPTF_Configuration['location_label'] ?? null ) ?: __( 'Location', 'abp-transportforge' ); }

			public static function location_slug() { return ( ABPTF_Configuration['location_slug'] ?? null ) ?: 'location'; }

			public static function location_value( $id ) { return ( ABPTF_Locations[ $id ]['name'] ?? null ) ?: $id; }

			public static function on_off( $key ): bool {
				$value = ( ABPTF_On_Off[ $key ] ?? 'on' ) ?: 'on';

				return $value !== 'off';
			}

			public static function array_to_string( $array ) {
				$ids = '';
				if ( sizeof( $array ) > 0 ) {
					foreach ( $array as $data ) {
						if ( $data ) {
							$ids = $ids ? $ids . ',' . $data : $data;
						}
					}
				}

				return $ids;
			}

			public static function build_url( $value = '', $extra_args = [] ): string {
				$default_args = [
					'page'         => 'transport-forge',
					'tab'          => $value,
					'_abptf_nonce' => wp_create_nonce( 'abptf_url_action' ),
				];
				$final_args   = array_merge( $default_args, $extra_args );

				return add_query_arg( $final_args, admin_url( 'admin.php' ) );
			}

			public static function get_image_url( $post_id = '', $image_id = '', $size = 'full' ): bool|string {
				$image_id = $post_id && $post_id > 0 ? get_post_thumbnail_id( $post_id ) : $image_id;

				return wp_get_attachment_image_url( $image_id, $size );
			}

			public static function get_page_by_slug( $slug ): bool|WP_Post {
				if ( $pages = get_pages() ) {
					foreach ( $pages as $page ) {
						if ( $slug === $page->post_name ) {
							return $page;
						}
					}
				}

				return false;
			}

			public static function get_id_by_slug( $page_slug ): ?int {
				$page = get_page_by_path( $page_slug );

				return $page?->ID;
			}

			public static function check_wc(): int {
				if ( class_exists( 'WooCommerce' ) || is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
					return 2;
				}
				$wc_dir = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce';
				if ( is_dir( $wc_dir ) ) {
					return 1;
				}

				return 0;
			}

			public static function already_in_cart( $post_id, $bp, $dp, $bp_date, $seat_name ): int {
				$count = 0;
				if ( is_admin() && str_contains( wp_get_referer(), 'admin_order' ) ) {
					return $count;
				}
				$formatted_bp_date = $bp_date ? gmdate( 'Y-m-d', strtotime( $bp_date ) ) : '';
				if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
					return $count;
				}
				$cart_items = WC()->cart->get_cart();
				if ( ! is_array( $cart_items ) || empty( $cart_items ) ) {
					return $count;
				}
				$routes = self::get_post_info( $post_id, 'route_direction', [] );
				if ( ! is_array( $routes ) || empty( $routes ) ) {
					return $count;
				}
				$sp          = array_search( $bp, $routes, true );
				$ep          = array_search( $dp, $routes, true );
				$target_seat = strtolower( $seat_name );
				foreach ( $cart_items as $cart_item ) {
					$cart_post_id = absint( $cart_item['post_id'] ?? 0 );
					$cart_date    = $cart_item['bp_time'] ?? '';
					$cart_date    = $cart_date ? gmdate( 'Y-m-d', strtotime( $cart_date ) ) : '';
					if ( $cart_post_id !== absint( $post_id ) || $cart_date !== $formatted_bp_date ) {
						continue;
					}
					$cart_bp     = $cart_item['bp'] ?? '';
					$cart_dp     = $cart_item['dp'] ?? '';
					$is_bp_valid = in_array( $cart_bp, array_slice( $routes, 0, $ep ), true );
					$is_dp_valid = in_array( $cart_dp, array_slice( $routes, $sp + 1 ), true );
					if ( ! $is_bp_valid || ! $is_dp_valid ) {
						continue;
					}
					$seat_infos = $cart_item['ticket_info'] ?? [];
					if ( ! is_array( $seat_infos ) || empty( $seat_infos ) ) {
						continue;
					}
					foreach ( $seat_infos as $seat_info ) {
						$current_seat = strtolower( $seat_info['seat'] ?? '' );
						if ( $current_seat === $target_seat ) {
							$count += absint( $cart_item['qty'] ?? 1 );
						}
					}
				}

				return $count;
			}

			public static function get_user_role( $user_ID ): string {
				global $wp_roles;
				$user_role_list = '';
				$user_data      = get_userdata( $user_ID );
				$user_role_slug = $user_data->roles;
				if ( is_array( $user_role_slug ) && sizeof( $user_role_slug ) > 0 ) {
					$user_count = 0;
					foreach ( $user_role_slug as $user_role ) {
						$user_count ++;
						if ( $user_count > 1 ) {
							$user_role_list .= ", ";
						}
						$user_role_list .= translate_user_role( $wp_roles->roles[ $user_role ]['name'] );
					}
				}

				return $user_role_list;
			}

			//=========== Template Related==================//
			public static function details_template_path( $post_id ): string {
				$post_id       = $post_id ?? get_the_id();
				$template_name = self::get_post_info( $post_id, 'abptf_template', 'grid' );
				$file_name     = 'details_theme/' . $template_name . '.php';
				$dir           = ABPTF_DIR . 'tf_templates/' . $file_name;
				if ( ! file_exists( $dir ) ) {
					$file_name = 'details_theme/grid.php';
				}

				return self::template_path( $file_name );
			}

			public static function template_path( $file_name ): string {
				$file_path   = wp_normalize_path( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . '/tf_templates/' . $file_name );
				$default_dir = wp_normalize_path( ABPTF_DIR . 'tf_templates/' . $file_name );

				return file_exists( $file_path ) ? $file_path : $default_dir;
			}

			//============= Date function================//
			public static function check_date_exit( $abptf_infos = [] ): bool {
				$post_id         = absint( $abptf_infos['post_id'] ?? 0 );
				$start_date_time = $abptf_infos['start_time'] ?? '';
				$end_date_time   = $abptf_infos['end_time'] ?? '';
				$rent_rule       = $abptf_infos['rent_rule'] ?? self::get_post_info( $post_id, 'rent_rule' );
				if ( $post_id <= 0 || empty( $start_date_time ) || empty( $end_date_time ) || empty( $rent_rule ) ) {
					return false;
				}
				$start         = gmdate( 'Y-m-d', strtotime( $start_date_time ) );
				$end           = gmdate( 'Y-m-d', strtotime( $end_date_time ) );
				$all_dates     = self::get_start_dates( $post_id );
				$all_end_dates = ( $rent_rule === 'hourly' ) ? $all_dates : self::get_end_dates( $post_id, $start, $all_dates );
				if ( ! in_array( $start, $all_dates, true ) || ! in_array( $end, $all_end_dates, true ) ) {
					return false;
				}
				if ( $rent_rule !== 'hourly' && $rent_rule !== 'multi_day' ) {
					return true;
				}
				$time_list = self::get_time( $post_id );
				if ( empty( $time_list ) ) {
					return false;
				}
				$start_time     = gmdate( 'H:i', strtotime( $start_date_time ) );
				$end_time       = gmdate( 'H:i', strtotime( $end_date_time ) );
				$start_day_name = strtolower( gmdate( 'l', strtotime( $start ) ) );
				$time_slots     = $time_list[ $start ] ?? $time_list[ $start_day_name ] ?? $time_list['slot'] ?? '';
				if ( empty( $time_slots ) ) {
					return false;
				}
				if ( $rent_rule === 'hourly' ) {
					return self::check_time_slot_exit( $time_slots, $start_time ) && self::check_time_slot_exit( $time_slots, $end_time );
				}
				if ( ! self::check_time_slot_exit( $time_slots, $start_time ) ) {
					return false;
				}
				$end_day_name   = strtolower( gmdate( 'l', strtotime( $end ) ) );
				$end_time_slots = $time_list[ $end ] ?? $time_list[ $end_day_name ] ?? $time_list['slot'] ?? '';
				if ( ! empty( $end_time_slots ) && self::check_time_slot_exit( $end_time_slots, $end_time ) ) {
					return true;
				}

				return false;
			}

			public static function get_start_dates( $post_id ): array {
				$all_dates = [];
				if ( ! empty( $post_id ) && $post_id > 0 ) {
					$active_global_dates = self::get_post_info( $post_id, 'active_global_dates', 'on' );
					if ( $active_global_dates == 'on' ) {
						$post_id   = 'global';
						$all_dates = json_decode( get_transient( 'abptf_date_global' ), true );
					} else {
						$all_dates = json_decode( get_transient( 'abptf_date_' . $post_id ), true );
					}
					if ( empty( $all_dates ) ) {
						self::update_dates( $post_id );
						$all_dates = json_decode( get_transient( 'abptf_date_' . $post_id ), true );
					}
				}

				return $all_dates;
			}

			public static function get_end_dates( $post_id, $_start_date = '', $all_dates = [] ): array {
				$all_dates    = empty( $all_dates ) ? self::get_start_dates( $post_id ) : $all_dates;
				$all_end_date = [];
				$_start_date  = gmdate( 'Y-m-d', strtotime( $_start_date ) );
				if ( ! empty( $post_id ) && $post_id > 0 && ! empty( $_start_date ) && in_array( $_start_date, $all_dates ) ) {
					$mm_time             = ABPTF_Function::get_option( 'abptf_mm_time' );
					$mm                  = $mm_time[ $post_id ] ?? [];
					$active_global_dates = self::get_post_info( $post_id, 'active_global_dates', 'on' );
					if ( $active_global_dates == 'on' ) {
						$date_infos = self::get_option( 'abptf_dates' );
					} else {
						$date_infos = self::get_post_info( $post_id, 'abptf_dates', [] );
					}
					$min       = ( $mm['min'] ?? null ) ?: 1;
					$max       = ( $mm['max'] ?? null ) ?: 100;
					$rent_rule = ABPTF_Function::get_post_info( $post_id, 'rent_rule' );
					if ( $rent_rule == 'monthly' ) {
						$_date = new DateTime( $_start_date );
						$_date->modify( 'first day of this month' );
						if ( $min > 1 ) {
							$_date->modify( '+' . ( $min - 1 ) . ' months' );
						}
						$start_date_obj = clone $_date;
						$start_date_obj->modify( 'last day of this month' );
						$_start_date = $start_date_obj->format( 'Y-m-d' );
						if ( $min == $max ) {
							$_end_date = $_start_date;
						} else {
							if ( $max > $min ) {
								$months_to_add = $max - $min;
								$_date->modify( '+' . $months_to_add . ' months' );
								$_date->modify( 'last day of this month' );
								$_end_date = $_date->format( 'Y-m-d' );
							}
						}
					} else {
						$_start_date = $min > 1 ? gmdate( 'Y-m-d', strtotime( $_start_date . ' +' . ( $min - 1 ) . ' day' ) ) : $_start_date;
						$_end_date   = gmdate( 'Y-m-d', strtotime( $_start_date . ' +' . ( $max - 1 ) . ' day' ) );
					}
					if ( ! empty( $_end_date ) ) {
						$date_type = ( $date_infos['date_type'] ?? null ) ?: 'periodic_date';
						if ( $date_type == 'specific_date' ) {
							$specific_dates = $date_infos['specific_dates'] ?? [];
							if ( is_array( $specific_dates ) && sizeof( $specific_dates ) > 0 ) {
								foreach ( $specific_dates as $specific_date ) {
									$date_item = $specific_date['date'] ?? '';
									if ( ! empty( $date_item ) ) {
										$date_item = gmdate( 'Y-m-d', strtotime( $date_item ) );
										if ( strtotime( $date_item ) >= strtotime( $_start_date ) && strtotime( $date_item ) <= strtotime( $_end_date ) ) {
											$all_end_date[] = $date_item;
										}
									}
								}
							}
						} else {
							$start_date = $date_infos['periodic_start_date'] ?? '';
							$start_date = $start_date ?: $_start_date;
							$end_date   = $date_infos['periodic_end_date'] ?? '';
							$end_date   = ! empty( $end_date ) ? gmdate( 'Y-m-d', strtotime( $end_date ) ) : $_end_date;
							if ( strtotime( $_start_date ) >= strtotime( $start_date ) ) {
								$start_date = $_start_date;
							}
							if ( strtotime( $end_date ) >= strtotime( $_end_date ) ) {
								$end_date = $_end_date;
							}
							$all_end_date = self::repeated_date_list_modify( $start_date, $end_date, $date_infos );
						}
					}
					if ( ! empty( $all_end_date ) ) {
						$all_end_date = array_unique( $all_end_date );
						usort( $all_end_date, "ABPTF_Function::sort_date" );
					}
				}

				return $all_end_date;
			}

			public static function get_start_month( $post_id, $dateList = [] ): array {
				$cut_off_date = self::get_post_info( $post_id, 'cut_off_date', 10 );
				sort( $dateList );
				$month_list      = [];
				$processedMonths = [];
				foreach ( $dateList as $dateStr ) {
					$timestamp = strtotime( $dateStr );
					$monthKey  = gmdate( 'Y-m', $timestamp );
					if ( in_array( $monthKey, $processedMonths ) ) {
						continue;
					}
					$day = (int) gmdate( 'd', $timestamp );
					if ( $day <= $cut_off_date ) {
						$month_list[] = [
							'value' => $dateStr,
							'label' => gmdate( 'F Y', $timestamp )
						];
					}
					$processedMonths[] = $monthKey;
				}

				return $month_list;
			}

			public static function get_end_month( $post_id = '', $start_date = '' ): array {
				$dateList   = self::get_end_dates( $post_id, $start_date );
				$month_list = [];
				if ( ! empty( $dateList ) ) {
					$processedMonths = [];
					foreach ( $dateList as $dateStr ) {
						$timestamp = strtotime( $dateStr );
						$monthKey  = gmdate( 'Y-m', $timestamp );
						if ( in_array( $monthKey, $processedMonths ) ) {
							continue;
						}
						$actualLastDate    = gmdate( 'Y-m-t', $timestamp );
						$month_list[]      = [
							'value' => $actualLastDate,
							'label' => gmdate( 'F Y', $timestamp )
						];
						$processedMonths[] = $monthKey;
					}
				}

				return $month_list;
			}

			public static function get_time( $post_id, $type = 'time' ): array {
				$option_name = $type == 'js' ? 'abptf_time_info_js' : 'abptf_time_info';
				$time_info   = ABPTF_Function::get_option( $option_name );
				$info        = [];
				if ( ! empty( $post_id ) ) {
					$info = $time_info[ $post_id ] ?? ( $time_info['global'] ?? [] );
				}

				return $info;
			}

			public static function update_dates( $post_id ): void {
				if ( ! empty( $post_id ) ) {
					if ( $post_id == 'global' ) {
						$date_infos = self::get_option( 'abptf_dates' );
					} else {
						$active_global_dates = self::get_post_info( $post_id, 'active_global_dates', 'on' );
						if ( $active_global_dates == 'on' ) {
							$date_infos = self::get_option( 'abptf_dates' );
							$post_id    = 'global';
						} else {
							$date_infos = self::get_post_info( $post_id, 'abptf_dates', [] );
						}
					}
					$all_dates = [];
					$date_type = $date_infos['date_type'] ?? 'periodic_date';
					$now       = current_time( 'Y-m-d' );
					if ( $date_type == 'specific_date' ) {
						$specific_dates = $date_infos['specific_dates'] ?? [];
						if ( is_array( $specific_dates ) && sizeof( $specific_dates ) > 0 ) {
							foreach ( $specific_dates as $date_item ) {
								if ( ! empty( $date_item ) ) {
									$date_item = gmdate( 'Y-m-d', strtotime( $date_item ) );
									if ( strtotime( $date_item ) >= strtotime( $now ) ) {
										$all_dates[] = $date_item;
									}
								}
							}
						}
					} else {
						$start_date    = $date_infos['periodic_start_date'] ?? '';
						$start_date    = $start_date ?: $now;
						$sale_end_date = $date_infos['periodic_end_date'] ?? '';
						$sale_end_date = $sale_end_date ? gmdate( 'Y-m-d', strtotime( $sale_end_date ) ) : '';
						$active_days   = ( ABPTF_Date_Config['advance_date_number'] ?? null ) ?: 28;
						if ( strtotime( $now ) >= strtotime( $start_date ) ) {
							$start_date = $now;
						}
						$end_date = gmdate( 'Y-m-d', strtotime( $start_date . ' +' . $active_days . ' day' ) );
						if ( $sale_end_date && strtotime( $sale_end_date ) < strtotime( $end_date ) ) {
							$end_date = $sale_end_date;
						}
						$all_dates = self::repeated_date_list_modify( $start_date, $end_date, $date_infos );
					}
					$all_dates = sizeof( $all_dates ) > 1 ? array_unique( $all_dates ) : $all_dates;
					usort( $all_dates, "ABPTF_Function::sort_date" );
					set_transient( 'abptf_date_' . $post_id, wp_json_encode( $all_dates ), HOUR_IN_SECONDS );
				}
			}

			public static function repeated_date_list_modify( $start_date, $end_date, $date_infos ): array {
				$all_dates = [];
				if ( strtotime( $start_date ) <= strtotime( $end_date ) ) {
					$now             = current_time( 'Y-m-d' );
					$off_dates       = [];
					$date_rule       = $date_infos['date_rule'] ?? '';
					$date_rule_array = $date_rule ? explode( ',', $date_rule ) : [];
					if ( in_array( 'off_date_range', $date_rule_array ) ) {
						$off_date_range = $date_infos['off_date_range'] ?? [];
						if ( is_array( $off_date_range ) && sizeof( $off_date_range ) > 0 ) {
							foreach ( $off_date_range as $off_date ) {
								if ( is_array( $off_date ) && ( $off_date['from'] ?? '' ) !== '' && ( $off_date['to'] ?? '' ) !== '' ) {
									$from_date      = gmdate( 'Y-m-d', strtotime( $off_date['from'] ) );
									$to_date        = gmdate( 'Y-m-d', strtotime( $off_date['to'] ) );
									$off_date_lists = self::date_separate_period( $from_date, $to_date );
									foreach ( $off_date_lists as $off_date_list ) {
										$off_dates[] = $off_date_list->format( 'Y-m-d' );
									}
								}
							}
						}
					}
					if ( in_array( 'specific_of_date', $date_rule_array ) ) {
						$particular_off_dates = $date_infos['specific_off_dates'] ?? [];
						if ( is_array( $particular_off_dates ) && sizeof( $particular_off_dates ) > 0 ) {
							foreach ( $particular_off_dates as $particular_off_date ) {
								$particular_off_date = gmdate( 'Y-m-d', strtotime( $particular_off_date ) );
								$off_dates[]         = $particular_off_date;
							}
						}
					}
					$off_dates     = array_unique( $off_dates );
					$off_day_array = [];
					if ( in_array( 'weekend', $date_rule_array ) ) {
						$off_days      = $date_infos['weekend'] ?? '';
						$off_day_array = $off_days ? explode( ',', $off_days ) : [];
					}
					$repeat = $date_infos['periodic_after'] ?? 1;
					$dates  = self::date_separate_period( $start_date, $end_date, $repeat );
					foreach ( $dates as $date ) {
						$date = $date->format( 'Y-m-d' );
						if ( strtotime( $date ) >= strtotime( $now ) ) {
							$day = strtolower( gmdate( 'l', strtotime( $date ) ) );
							if ( ! in_array( $date, $off_dates ) && ! in_array( $day, $off_day_array ) ) {
								$all_dates[] = $date;
							}
						}
					}
					//==================//
					if ( in_array( 'special_on_dates', $date_rule_array ) ) {
						$special_on_dates = $date_infos['special_on_dates'] ?? [];
						if ( is_array( $special_on_dates ) && sizeof( $special_on_dates ) > 0 ) {
							foreach ( $special_on_dates as $date_item ) {
								if ( ! empty( $date_item ) ) {
									$date_item = gmdate( 'Y-m-d', strtotime( $date_item ) );
									if ( strtotime( $date_item ) >= strtotime( $now ) ) {
										$all_dates[] = $date_item;
									}
								}
							}
						}
					}
				}

				return $all_dates;
			}

			public static function update_time_slot( $post_id = '' ): void {
				$all_slots    = ABPTF_Function::get_option( 'abptf_time_info' );
				$all_js_slots = ABPTF_Function::get_option( 'abptf_time_info_js' );
				$date_infos   = ABPTF_Dates;
				$key          = 'global';
				if ( ! empty( $post_id ) ) {
					$active_global_dates = self::get_post_info( $post_id, 'active_global_dates', 'on' );
					if ( $active_global_dates !== 'on' ) {
						$date_infos = self::get_post_info( $post_id, 'abptf_dates', [] );
						$key        = $post_id;
					}
				}
				if ( is_array( $date_infos ) ) {
					$slots             = self::get_time_slot( $date_infos );
					$all_slots[ $key ] = $slots;
					$js_slots          = [];
					if ( ! empty( $slots ) ) {
						foreach ( $slots as $count => $slot ) {
							if ( ! empty( $slot ) ) {
								$slot_info  = explode( '-', $slot );
								$start_time = $slot_info[0] ?? '';
								$end_time   = $slot_info[1] ?? '';
								if ( ! empty( $start_time ) && ! empty( $end_time ) ) {
									$slot_data = self::generate_time_slot( $start_time, $end_time );
									if ( ! empty( $slot_data ) ) {
										$js_slots[ $count ] = $slot_data;
									}
								}
							}
						}
					}
					$all_js_slots[ $key ] = $js_slots;
					update_option( 'abptf_time_info', $all_slots );
					update_option( 'abptf_time_info_js', $all_js_slots );
				}
			}

			public static function get_time_slot( $date_infos = [] ): array {
				if ( ! is_array( $date_infos ) ) {
					return [];
				}
				$all_slots = [];
				$date_type = $date_infos['date_type'] ?? 'periodic_date';
				if ( $date_type === 'specific_date' ) {
					$specific_dates = $date_infos['specific_dates'] ?? [];
					if ( is_array( $specific_dates ) && ! empty( $specific_dates ) ) {
						foreach ( $specific_dates as $specific_date ) {
							if ( ! is_array( $specific_date ) ) {
								continue;
							}
							$date_item = $specific_date['date'] ?? '';
							if ( $date_item === '' ) {
								continue;
							}
							$start_time = $specific_date['start'] ?? '';
							$end_time   = $specific_date['end'] ?? '';
							if ( $start_time !== '' && $end_time !== '' && strtotime( $start_time ) < strtotime( $end_time ) ) {
								$all_slots[ $date_item ] = $start_time . '-' . $end_time;
							}
						}
					}
				} else {
					$date_rule       = $date_infos['date_rule'] ?? '';
					$date_rule_array = $date_rule ? explode( ',', $date_rule ) : [];
					if ( in_array( 'special_on_dates', $date_rule_array, true ) ) {
						$special_on_dates = $date_infos['special_on_dates'] ?? [];
						if ( is_array( $special_on_dates ) && ! empty( $special_on_dates ) ) {
							foreach ( $special_on_dates as $special_on_date ) {
								if ( ! is_array( $special_on_date ) ) {
									continue;
								}
								$date_item = $special_on_date['date'] ?? '';
								if ( $date_item === '' ) {
									continue;
								}
								$start_time = $special_on_date['start'] ?? '';
								$end_time   = $special_on_date['end'] ?? '';
								if ( $start_time !== '' && $end_time !== '' && strtotime( $start_time ) < strtotime( $end_time ) ) {
									$all_slots[ $date_item ] = $start_time . '-' . $end_time;
								}
							}
						}
					}
					$operation_times = $date_infos['day_wise_time'] ?? [];
					if ( in_array( 'day_wise_time', $date_rule_array, true ) && is_array( $operation_times ) && ! empty( $operation_times ) ) {
						$days = ABPTF_Layout::week_day();
						foreach ( $days as $key => $day ) {
							$times = $operation_times[ $key ] ?? [];
							if ( is_array( $times ) ) {
								$start_time = $times['start'] ?? '';
								$end_time   = $times['end'] ?? '';
								if ( $start_time !== '' && $end_time !== '' && strtotime( $start_time ) < strtotime( $end_time ) ) {
									$all_slots[ $key ] = $start_time . '-' . $end_time;
								}
							}
						}
					}
				}

				return $all_slots;
			}

			public static function generate_time_slot( $start_time, $end_time, $interval = 60 ): string {
				$slots = [];
				if ( ! empty( $start_time ) && ! empty( $end_time ) ) {
					$start  = new DateTime( $start_time );
					$end    = new DateTime( $end_time );
					$minute = (int) $start->format( 'i' );
					if ( $minute > 0 && $minute % $interval !== 0 ) {
						$diff = $interval - ( $minute % $interval );
						$start->modify( "+$diff minutes" );
					}
					$start->setTime( (int) $start->format( 'H' ), (int) $start->format( 'i' ), 0 );
					while ( $start < $end ) {
						$current_slot_start = $start->format( 'H:i' );
						$start->add( new DateInterval( "PT{$interval}M" ) );
						$slots[] = $current_slot_start . '--' . self::date_format( $current_slot_start, 'time' );
					}
				}

				return implode( '##', $slots );
			}

			public static function date_format( $date, $format = '' ): string {
				if ( ! empty( $date ) ) {
					if ( empty( $format ) ) {
						$format = ABPTF_Function::check_time_exit_date( $date ) ? 'full' : 'date';
					}
					$date_format = self::date_format_php();
					$time_format = ABPTF_Time_Format;
					$wp_settings = $date_format . '  ' . $time_format;
					//$timezone = wp_timezone_string();
					$timestamp = strtotime( $date );
					if ( $format == 'date' ) {
						$date = date_i18n( $date_format, $timestamp );
					} elseif ( $format == 'time' ) {
						$date = date_i18n( $time_format, $timestamp );
					} elseif ( $format == 'full' ) {
						$date = date_i18n( $wp_settings, $timestamp );
					} elseif ( $format == 'day' ) {
						$date = date_i18n( 'd', $timestamp );
					} elseif ( $format == 'month' ) {
						$date = date_i18n( 'M', $timestamp );
					} elseif ( $format == 'year' ) {
						$date = date_i18n( 'Y', $timestamp );
					} else {
						$date = date_i18n( $format, $timestamp );
					}
				}

				return $date;
			}

			public static function date_format_php(): string {
				$formats = [
					'yy/mm/dd'   => 'Y/m/d',
					'yy-dd-mm'   => 'Y-d-m',
					'yy/dd/mm'   => 'Y/d/m',
					'dd-mm-yy'   => 'd-m-Y',
					'dd/mm/yy'   => 'd/m/Y',
					'mm-dd-yy'   => 'm-d-Y',
					'mm/dd/yy'   => 'm/d/Y',
					'd M , yy'   => 'j M , Y',
					'D d M , yy' => 'D j M , Y',
					'M d , yy'   => 'M  j, Y',
					'D M d , yy' => 'D M  j, Y',
				];

				return $formats[ ABPTF_JS_Date_Format ] ?? 'Y-m-d';
			}

			public static function date_format_js() { return ( ABPTF_Date_Config['date_format'] ?? null ) ?: 'D d M , yy'; }

			public static function date_separate_period( $start_date, $end_date, $repeat = 1 ): DatePeriod {
				$repeat    = max( $repeat, 1 );
				$_interval = "P" . $repeat . "D";
				$end_date  = gmdate( 'Y-m-d', strtotime( $end_date . ' +1 day' ) );

				return new DatePeriod( new DateTime( $start_date ), new DateInterval( $_interval ), new DateTime( $end_date ) );
			}

			public static function check_time_exit_date( $date ): bool {
				if ( $date ) {
					$parse_date = date_parse( $date );
					if ( ( $parse_date['hour'] && $parse_date['hour'] > 0 ) || ( $parse_date['minute'] && $parse_date['minute'] > 0 ) || ( $parse_date['second'] && $parse_date['second'] > 0 ) ) {
						return true;
					}
				}

				return false;
			}

			public static function sort_date( $a, $b ): int { return strtotime( $a ) - strtotime( $b ); }

			public static function sort_date_array( $a, $b ): int {
				$dateA = strtotime( $a['time'] );
				$dateB = strtotime( $b['time'] );
				if ( $dateA == $dateB ) {
					return 0;
				} elseif ( $dateA > $dateB ) {
					return 1;
				} else {
					return - 1;
				}
			}

			public static function get_date_time_difference( $start_time, $end_time, $rent_rule ): array {
				$text = '';
				$info = [];
				if ( ! empty( $start_time ) && ! empty( $end_time ) && strtotime( $start_time ) <= strtotime( $end_time ) ) {
					if ( ! empty( $rent_rule ) && $rent_rule == 'monthly' ) {
						$start_time = gmdate( 'Y-m-01', strtotime( $start_time ) );
					}
					$date1 = date_create( $start_time );
					$date2 = date_create( $end_time );
					if ( date_format( $date1, 'H:i:s' ) === '00:00:00' && date_format( $date2, 'H:i:s' ) === '00:00:00' ) {
						date_modify( $date2, '+1 day' );
					}
					$diff    = date_diff( $date1, $date2 );
					$years   = $diff->y;
					$months  = $diff->m;
					$days    = $diff->d;
					$hours   = $diff->h;
					$minutes = $diff->i;
					$seconds = $diff->s;
					if ( $years > 0 ) {
						$text          .= sprintf(
						/* translators: %s =Years */
							_n( ' %s Year', ' %s Years', $years, 'abp-transportforge' ), $years );
						$info['month'] = $years * 12;
					}
					if ( $months > 0 ) {
						$text          .= sprintf(
						/* translators: %s = Months */
							_n( ' %s Month', ' %s Months', $months, 'abp-transportforge' ), $months );
						$exit_month    = $info['month'] ?? 0;
						$info['month'] = $exit_month + $months;
					}
					if ( $days > 0 ) {
						$text        .= sprintf(
						/* translators: %s = Days */
							_n( ' %s Day', ' %s Days', $days, 'abp-transportforge' ), $days );
						$info['day'] = $days;
					}
					if ( $hours > 0 ) {
						$text         .= sprintf(
						/* translators: %s = Hours */
							_n( ' %s Hour', ' %s Hours', $hours, 'abp-transportforge' ), $hours );
						$info['hour'] = $hours;
					}
					if ( $minutes > 0 ) {
						$text        .= sprintf(
						/* translators: %s = Minutes */
							_n( ' %s Minute', ' %s Minutes', $minutes, 'abp-transportforge' ), $minutes );
						$info['min'] = $minutes;
					}
					if ( $seconds > 0 ) {
						$text        .= sprintf(
						/* translators: %s = Seconds */
							_n( ' %s Second', ' %s Seconds', $seconds, 'abp-transportforge' ), $seconds );
						$info['sec'] = $seconds;
					}
					$info['text']     = $text;
					$info['duration'] = abs( strtotime( $end_time ) - strtotime( $start_time ) );
				}

				return $info;
			}

			public static function check_time_slot_exit( $main_slots, $input_slots ): bool {
				if ( ! empty( $main_slots ) && ! empty( $input_slots ) ) {
					$main_slots = explode( '-', $main_slots );
					if ( isset( $main_slots[0] ) && isset( $main_slots[1] ) ) {
						$main_start  = strtotime( $main_slots[0] );
						$main_end    = strtotime( $main_slots[1] );
						$input_slots = strtotime( $input_slots );
						if ( $main_start <= $input_slots && $main_end >= $input_slots ) {
							return true;
						}
					}
				}

				return false;
			}

			public static function time_duration( $abptf_infos = [], $price_info = [] ) {
				$rent_rule = $abptf_infos['rent_rule'] ?? '';
				$date_info = $abptf_infos['date_info'] ?? [];
				$dif       = $dif_exit = 0;
				if ( ! empty( $rent_rule ) && ! empty( $date_info ) ) {
					$duration = $date_info['duration'] ?? 0;
					$min      = $price_info['min'] ?? 1;
					$max      = $price_info['max'] ?? '';
					if ( $rent_rule == 'hourly' ) {
						$dif = $duration / 3600;
						$dif = ceil( $dif );
						$dif = max( 1, $dif );
					} elseif ( $rent_rule == 'daily' ) {
						$dif = $date_info['day'] ?? 0;
					} elseif ( $rent_rule == 'multi_day' ) {
						$dif = $duration / 86400;
					} elseif ( $rent_rule == 'monthly' ) {
						$dif = $date_info['month'] ?? 0;
					} elseif ( $rent_rule == 'multi_month' ) {
						$dif = ( $date_info['month'] ?? 0 ) . '.' . ( $date_info['day'] ?? 0 );
					}
					if ( ! empty( $max ) ) {
						$dif_exit = $min <= $dif && $max >= $dif ? 1 : 0;
					} else {
						$dif_exit = $min <= $dif ? 1 : 0;
					}
				}

				return $dif_exit > 0 ? $dif : false;
			}

			public static function booking_buffer( $time, $end = '' ): string {
				$date_infos = ABPTF_Dates;
				if ( ! empty( $end ) ) {
					$buffer_time = $date_infos['sale_close_after'] ?? 0;
					$buffer_time = $buffer_time * 60;
					$time        = gmdate( 'Y-m-d H:i', strtotime( $time ) + $buffer_time );
				} else {
					$buffer_time = $date_infos['sale_close_before'] ?? 0;
					$buffer_time = $buffer_time * 60;
					$time        = gmdate( 'Y-m-d H:i', strtotime( $time ) - $buffer_time );
				}

				return $time;
			}

			//=============Price Function================//
			public static function tax_with_price( $post_id, $price ): string {
				$num_of_decimal = get_option( 'woocommerce_price_num_decimals', 2 );
				$_product       = self::get_post_info( $post_id, 'link_wc_id', $post_id );
				$product        = wc_get_product( $_product );
				$tax_display    = get_option( 'woocommerce_tax_display_shop' );
				if ( '' === $price ) {
					return '';
				}
				$return_price = (float) $price;
				if ( $product && $product->is_taxable() ) {
					$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
					if ( ! empty( $tax_rates ) ) {
						$taxes     = WC_Tax::calc_tax( $return_price, $tax_rates, false );
						$tax_total = 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ? array_sum( $taxes ) : array_sum( array_map( 'wc_round_tax_total', $taxes ) );
						if ( ! empty( WC()->customer ) && WC()->customer->get_is_vat_exempt() ) {
							$return_price = round( $return_price, $num_of_decimal );
						} else {
							$return_price = $tax_display === 'excl' ? round( $return_price, $num_of_decimal ) : round( $return_price + $tax_total, $num_of_decimal );
						}
					}
				}

				return $return_price;
			}

			public static function get_price( $abptf_infos = [], $property = [], $time_duration = '' ): int|string {
				$price       = 0;
				$post_id     = $abptf_infos['post_id'] ?? 0;
				$rent_rule   = $abptf_infos['rent_rule'] ?? '';
				$qty         = $abptf_infos['qty'] ?? 1;
				$property_id = $abptf_infos['property_id'] ?? 0;
				if ( empty( $property ) ) {
					$db_property = ABPTF_Query::get_property( [ 'property_id' => $property_id ] );
					$property    = is_array( $db_property ) ? current( $db_property ) : [];
				}
				$property       = is_array( $property ) ? $property : [];
				$price_qty_info = $property['price_qty_info'] ?? '';
				$price_qty_info = ! empty( $price_qty_info ) ? json_decode( $price_qty_info, true ) : [];
				$location       = $abptf_infos['location'] ?? '';
				$price_qty_info = ( ! empty( $price_qty_info ) && ! empty( $location ) && isset( $price_qty_info[ $location ] ) ) ? $price_qty_info[ $location ] : $price_qty_info;
				$price_info     = $price_qty_info[ $rent_rule ] ?? [];
				$time_duration  = ! empty( $time_duration ) ? $time_duration : self::time_duration( $abptf_infos, $price_info );
				if ( ! empty( $rent_rule ) && ! empty( $time_duration ) && ! empty( $price_info ) ) {
					$price_info = apply_filters( 'abptf_filter_discount_price_info', $price_info, $abptf_infos );
					$rate       = ( is_array( $price_info ) ? ( $price_info['price'] ?? 0 ) : 0 ) ?: 0;
					if ( in_array( $rent_rule, [ 'hourly', 'daily', 'monthly' ], true ) ) {
						$price = $rate * $time_duration * $qty;
					}
					if ( $rent_rule === 'multi_day' ) {
						$price_multi = $price_info['price_multi'] ?? 0;
						$threshold   = ABPTF_Function::get_post_info( $post_id, 'hour_threshold', 24 );
						$days        = floor( $time_duration );
						$hours       = ( $time_duration - $days ) * 24;
						if ( ! empty( $threshold ) && $threshold <= $hours ) {
							$days ++;
							$hours -= $threshold;
						}
						$price = ( $rate * $days * $qty ) + ( $price_multi * $hours * $qty );
					}
					if ( $rent_rule === 'multi_month' ) {
						$price_multi = $price_info['price_multi'] ?? 0;
						$threshold   = ABPTF_Function::get_post_info( $post_id, 'day_threshold', 30 );
						$parts       = explode( '.', (string) $time_duration );
						$month_num   = (int) $parts[0];
						$day_num     = isset( $parts[1] ) ? (int) $parts[1] : 0;
						if ( ! empty( $threshold ) && $threshold <= $day_num ) {
							$month_num ++;
							$day_num -= $threshold;
						}
						$price = ( $rate * $month_num * $qty ) + ( $price_multi * $day_num * $qty );
					}
					$discount = ( is_array( $price_info ) ? ( $price_info['discount'] ?? 0 ) : 0 ) ?: 0;
					$price    = $price - ( $price * $discount / 100 );
				}

				return $price > 0 ? self::tax_with_price( $post_id, $price ) : 0;
			}

			public static function get_deposit_price( array $abptf_infos = [], array $property = [] ): int|float {
				$price = 0;
				if ( empty( $abptf_infos ) ) {
					return $price;
				}
				$property_id = $abptf_infos['property_id'] ?? 0;
				$rent_rule   = $abptf_infos['rent_rule'] ?? '';
				$qty         = $abptf_infos['qty'] ?? 0;
				if ( empty( $property ) ) {
					$db_property = ABPTF_Query::get_property( [ 'property_id' => $property_id ] );
					$property    = is_array( $db_property ) ? current( $db_property ) : [];
				}
				$property       = is_array( $property ) ? $property : [];
				$price_qty_info = $property['price_qty_info'] ?? '';
				$price_qty_info = ! empty( $price_qty_info ) ? json_decode( $price_qty_info, true ) : [];
				$location       = $abptf_infos['location'] ?? '';
				$price_qty_info = ( ! empty( $price_qty_info ) && ! empty( $location ) && isset( $price_qty_info[ $location ] ) ) ? $price_qty_info[ $location ] : $price_qty_info;
				$price_info     = ( ! empty( $rent_rule ) && is_array( $price_qty_info ) ) ? ( $price_qty_info[ $rent_rule ] ?? [] ) : [];
				$deposit_info   = $price_info['deposit'] ?? [];
				$deposit_type   = $deposit_info['type'] ?? '';
				$deposit_value  = $deposit_info['value'] ?? '';
				if ( ! empty( $deposit_type ) && ! empty( $deposit_value ) && $qty > 0 && ! empty( $property ) ) {
					if ( $deposit_type === 'fixed' ) {
						$price = $deposit_value;
					} elseif ( $deposit_type === 'percent' ) {
						$base_price = $abptf_infos['price'] ?? 0;
						$price      = ( $base_price * $deposit_value ) / 100;
					} else {
						$price = $qty * $deposit_value;
					}
				}

				return $price;
			}

			public static function get_additional_price( int|string $post_id, string $service_name, array $abptf_infos = [] ): int|string {
				$display                  = $abptf_infos['display_additional_services'] ?? ABPTF_Function::get_post_info( $post_id, 'display_additional_services', 'on' );
				$active_global_additional = $abptf_infos['active_global_additional'] ?? ABPTF_Function::get_post_info( $post_id, 'active_global_additional', 'on' );
				if ( $active_global_additional === 'on' ) {
					$services = ABPTF_Function::get_option( 'abptf_additional' );
				} else {
					$services = $abptf_infos['abptf_additional'] ?? ABPTF_Function::get_post_info( $post_id, 'abptf_additional', [] );
				}
				$price = 0;
				if ( $display === 'on' && ! empty( $services ) && is_array( $services ) ) {
					foreach ( $services as $service ) {
						$ex_name = $service['name'] ?? '';
						if ( $ex_name === $service_name ) {
							$price = $service['price'] ?? 0;
							break;
						}
					}
				}

				return $price > 0 ? self::tax_with_price( $post_id, $price ) : 0;
			}

			//=============================//
			public static function update_global_data( $post_id = '' ): void {
				$post_id = absint( $post_id );
				if ( $post_id <= 0 ) {
					return;
				}
				$arg        = [
					'sale_continue' => 'on',
					'status'        => 'publish',
					'post_id'       => $post_id,
				];
				$properties = ABPTF_Query::get_property( $arg );
				if ( empty( $properties ) || ! is_array( $properties ) ) {
					return;
				}
				$img_info    = [];
				$fec_info    = [];
				$brand_names = [];
				$min_price   = ABPTF_Function::get_option( 'abptf_min_price' );
				$min_price   = is_array( $min_price ) ? $min_price : [];
				$mm_time     = ABPTF_Function::get_option( 'abptf_mm_time' );
				$mm_time     = is_array( $mm_time ) ? $mm_time : [];
				$count       = 0;
				$rent_rule   = ABPTF_Function::get_post_info( $post_id, 'rent_rule' );
				$title       = get_the_title( $post_id );
				$rate        = [];
				$min_time    = [];
				$max_time    = [];
				foreach ( $properties as $property ) {
					if ( ! is_array( $property ) ) {
						continue;
					}
					$slider = $property['gallery'] ?? '';
					$name   = $property['name'] ?? '';
					$brand  = $property['brand'] ?? '';
					if ( ! empty( $brand ) ) {
						$brand_names[] = $brand;
					}
					if ( ! empty( $slider ) ) {
						$image_ids = explode( ',', $slider );
						foreach ( $image_ids as $id ) {
							if ( $id !== '' ) {
								$img_info[ $count ] = [
									'id'    => $id,
									'post'  => $title,
									'label' => $name,
								];
								$count ++;
							}
						}
					}
					$features = $property['features'] ?? '';
					if ( ! empty( $features ) ) {
						$feature_array = explode( ',', $features );
						$fec_info      = array_merge( $fec_info, $feature_array );
					}
					$price_qty_info = $property['price_qty_info'] ?? '';
					$price_qty_info = ( $price_qty_info !== '' ) ? json_decode( $price_qty_info, true ) : [];
					$price_info     = ( $rent_rule !== '' && is_array( $price_qty_info ) ) ? ( $price_qty_info[ $rent_rule ] ?? [] ) : [];
					$rate[]         = floatval( $price_info['price'] ?? 0 );
					$_min_time      = $price_info['min'] ?? '';
					if ( $rent_rule === 'multi_month' ) {
						if ( $_min_time !== '' ) {
							$_min_time = absint( $_min_time ) * 30;
						}
					} else {
						if ( $_min_time === '' ) {
							$_min_time = 1;
						}
					}
					$min_time[] = absint( $_min_time );
					$_max_time  = absint( $price_info['max'] ?? 0 );
					if ( $_max_time < 1 ) {
						$_max_time = match ( $rent_rule ) {
							'hourly' => 24,
							'daily' => 100,
							'multi_day' => 100,
							'monthly' => 12,
							'multi_month' => 365,
							default => ( $_max_time * 30 ),
						};
					}
					$max_time[] = $_max_time;
				}
				$fec_info              = implode( ',', array_unique( $fec_info ) );
				$brand_info            = implode( ',', array_unique( $brand_names ) );
				$min_price[ $post_id ] = ! empty( $rate ) ? min( $rate ) : 0;
				$mm_time[ $post_id ]   = [
					'min' => ! empty( $min_time ) ? min( $min_time ) : 0,
					'max' => ! empty( $max_time ) ? max( $max_time ) : 0,
				];
				update_post_meta( $post_id, 'abptf_sliders', $img_info );
				update_post_meta( $post_id, 'abptf_features', $fec_info );
				update_post_meta( $post_id, 'abptf_brand', $brand_info );
				update_option( 'abptf_min_price', $min_price );
				update_option( 'abptf_mm_time', $mm_time );
			}

			public static function related_info_js( $_post_id = '' ): array {
				$all_info = [];
				if ( ! empty( $_post_id ) && $_post_id > 0 ) {
					$all_post_ids = ABPTF_Query::get_post_id();
					if ( ! empty( $all_post_ids ) ) {
						$active_icon = ABPTF_Function::on_off( 'post_icon' );
						foreach ( $all_post_ids as $post_id ) {
							if ( $post_id !== $_post_id ) {
								$icon       = $active_icon ? ABPTF_Function::get_post_info( $post_id, 'post_icon' ) : '';
								$all_info[] = [ 'id' => $post_id, 'icon' => $icon, 'label' => get_the_title( $post_id ) ];
							}
						}
					}
				}

				return $all_info;
			}
		}
		new ABPTF_Function();
	}