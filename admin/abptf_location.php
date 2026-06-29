<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Location' ) ) {
		class ABPTF_Location {
			public function __construct() {
				add_action( 'abptf_global_location', [ $this, 'global_location' ] );
				add_action( 'abptf_location_update', [ $this, 'update_location' ] );
				add_action( 'wp_ajax_abptf_save_location', [ $this, 'save_location' ] );
				add_action( 'wp_ajax_abptf_delete_location', [ $this, 'delete_location' ] );
				add_action( 'wp_ajax_abptf_add_location', [ $this, 'add_location' ] );
			}

			public function global_location(): void {
				?>
                <div class="location_list _ov_auto">
					<?php $this->location_list(); ?>
                </div>
                <div class="_divider_xs"></div>
				<?php
				ABPTF_Layout::button_popup_global( 'location', __( 'Add New', 'abp-transportforge' ) . ' ' . ABPTF_Function::location_label() );
			}

			public function update_location( $options = [], $id = '' ): void {
				$taxonomies   = ABPTF_Function::get_taxonomy( 'abptf_location' );
				$taxonomies   = is_array( $taxonomies ) ? $taxonomies : [];
				$location     = [];
				$old_location = ABPTF_Function::get_option( 'abptf_location' );
				$old_location = is_array( $old_location ) ? $old_location : [];
				if ( count( $taxonomies ) > 0 ) {
					foreach ( $taxonomies as $taxonomy ) {
						$term_id                             = $taxonomy->term_id;
						$location[ $term_id ]['name']        = $taxonomy->name;
						$location[ $term_id ]['description'] = $taxonomy->description;
						$location[ $term_id ]['slug']        = $taxonomy->slug;
						if ( ! empty( $id ) && ! empty( $options ) && (int) $id === (int) $term_id ) {
							$new_location = $options;
						} else {
							$new_location = $old_location[ $term_id ] ?? [];
						}
						$location[ $term_id ]['display_pickup'] = $new_location['display_pickup'] ?? 'off';
						$location[ $term_id ]['pick_info']      = $new_location['pick_info'] ?? [];
						$location[ $term_id ]['display_drop']   = $new_location['display_drop'] ?? 'off';
						$location[ $term_id ]['drop_info']      = $new_location['drop_info'] ?? [];
					}
				}
				ksort( $location );
				update_option( 'abptf_location', $location );
			}

			public function save_location(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$post_int       = fn( $key, $default = 0 ) => isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
				$post_val       = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
				$post_slug      = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_title( wp_unslash( $_POST[ $key ] ) ) : $default;
				$post_array     = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) ) : [];
				$cat_term_id    = $post_int( 'loc_term_id' );
				$name           = $post_val( 'name' );
				$slug           = $post_slug( 'slug' );
				$description    = $post_val( 'description' );
				$display_pickup = $post_val( 'display_pickup', 'off' );
				$display_drop   = $post_val( 'display_drop', 'off' );
				$abptf_post_id  = $post_int( 'abptf_post_id' );
				$pick_ids       = $post_array( 'pick_id' );
				$pick_names     = $post_array( 'pick_name' );
				$pick_times     = $post_array( 'pick_time' );
				$drop_ids       = $post_array( 'drop_id' );
				$drop_names     = $post_array( 'drop_name' );
				$drop_times     = $post_array( 'drop_time' );
				if ( empty( $name ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Location Name cannot be blank!', 'abp-transportforge' ) ] );
				}
				if ( $cat_term_id > 0 ) {
					$result = wp_update_term( $cat_term_id, 'abptf_location', [
						'name'        => $name,
						'slug'        => $slug,
						'description' => $description,
					] );
				} else {
					$result = wp_insert_term( $name, 'abptf_location', [
						'slug'        => $slug,
						'description' => $description,
					] );
				}
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => $result->get_error_message() ] );
				}
				$term_id = absint( $result['term_id'] ?? 0 );
				if ( $term_id <= 0 ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Failed to resolve location context.', 'abp-transportforge' ) ] );
				}
				$pickup_info = [];
				$drop_info   = [];
				$number      = 0;
				$number_drop = 0;
				if ( ! empty( $pick_names ) ) {
					foreach ( $pick_names as $key => $pick ) {
						if ( ! empty( $pick ) ) {
							$pick_id = $pick_ids[ $key ] ?? '';
							if ( empty( $pick_id ) ) {
								$pick_id = 'pick_id_' . $number;
								while ( isset( $pickup_info[ $pick_id ] ) ) {
									$number ++;
									$pick_id = 'pick_id_' . $number;
								}
							}
							$pickup_info[ $pick_id ]['name'] = $pick;
							$pickup_info[ $pick_id ]['time'] = $pick_times[ $key ] ?? '';
						}
					}
				}
				if ( ! empty( $drop_names ) ) {
					foreach ( $drop_names as $key => $drop ) {
						if ( ! empty( $drop ) ) {
							$drop_id = $drop_ids[ $key ] ?? '';
							if ( empty( $drop_id ) ) {
								$drop_id = 'drop_id_' . $number_drop;
								while ( isset( $drop_info[ $drop_id ] ) ) {
									$number_drop ++;
									$drop_id = 'drop_id_' . $number_drop;
								}
							}
							$drop_info[ $drop_id ]['name'] = $drop;
							$drop_info[ $drop_id ]['time'] = $drop_times[ $key ] ?? '';
						}
					}
				}
				$options = [
					'display_pickup' => $display_pickup,
					'pick_info'      => $pickup_info,
					'display_drop'   => $display_drop,
					'drop_info'      => $drop_info,
				];
				$this->update_location( $options, $term_id );
				$msg = __( 'Location Saved Successfully !', 'abp-transportforge' );
				ob_start();
				if ( $abptf_post_id > 0 ) {
					$_location = ABPTF_Function::get_post_info( $abptf_post_id, 'abptf_location' );
					self::location_selection( $_location );
				} else {
					$this->location_list();
				}
				$html = ob_get_clean();
				wp_send_json_success( [ 'html' => $html, 'msg' => $msg ] );
			}

			public function delete_location(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$loc_id = isset( $_POST['loc_id'] ) ? sanitize_text_field( wp_unslash( $_POST['loc_id'] ) ) : '';
				$result = wp_delete_term( (int) $loc_id, 'abptf_location' );
				$this->update_location();
				ob_start();
				$this->location_list();
				$html = ob_get_clean();
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'html' => $html, 'msg' => $result->get_error_message() ] );
				}
				global $wpdb;
				$table_name = $wpdb->prefix . 'abptf_property';
				$all_ids    = ABPTF_Query::get_post_id( [ 'loc_id' => $loc_id ] );
				if ( count( $all_ids ) > 0 ) {
					foreach ( $all_ids as $id ) {
						$location       = ABPTF_Function::get_post_info( $id, 'location' );
						$location_array = ! empty( $location ) ? explode( ',', $location ) : [];
						if ( in_array( $loc_id, $location_array, true ) ) {
							$location_array   = array_diff( $location_array, [ $loc_id ] );
							$updated_location = ! empty( $location_array ) ? implode( ',', $location_array ) : '';
							update_post_meta( $id, 'location', $updated_location );
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$wpdb->update(
								$table_name,
								[ 'location' => $updated_location ],
								[ 'post_id' => (int) $id ],
								[ '%s' ],
								[ '%d' ]
							);
						}
					}
				}
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'Location Deleted Successfully !', 'abp-transportforge' ) ] );
			}

			public function add_location(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$loc_id    = isset( $_POST['tax_id'] ) ? sanitize_text_field( wp_unslash( $_POST['tax_id'] ) ) : '';
				$locations = ABPTF_Function::get_option( 'abptf_location' );
				$locations = is_array( $locations ) ? $locations : [];
				$location  = $locations[ $loc_id ] ?? [];
				ob_start();
				$this->form( $location, $loc_id );
				$html = ob_get_clean();
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'Location Form Loaded Successfully .....! ', 'abp-transportforge' ) ] );
			}

			public function location_list(): void {
				$all_locations = ABPTF_Function::get_option( 'abptf_location' );
				$all_locations = is_array( $all_locations ) ? $all_locations : [];
				$count         = 1;
				if ( count( $all_locations ) > 0 ) { ?>
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'SI', 'abp-transportforge' ) ?></th>
                            <th><?php esc_html_e( 'ID', 'abp-transportforge' ) ?></th>
                            <th class="_min_150"><?php esc_html_e( 'Location Title', 'abp-transportforge' ) ?></th>
                            <th><?php esc_html_e( 'Pickup Point', 'abp-transportforge' ) ?></th>
                            <th><?php esc_html_e( 'Drop-off Point', 'abp-transportforge' ) ?></th>
                            <th><?php esc_html_e( 'Location Full Address', 'abp-transportforge' ) ?></th>
                            <th><?php esc_html_e( 'Shortcode Post', 'abp-transportforge' ) ?></th>
                            <th><?php esc_html_e( 'Action', 'abp-transportforge' ) ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php foreach ( $all_locations as $term_id => $location ) {
							$name           = $location['name'] ?? '';
							$description    = $location['description'] ?? '';
							$display_pickup = $location['display_pickup'] ?? 'off';
							$pick_info      = $location['pick_info'] ?? [];
							$display_drop   = $location['display_drop'] ?? 'off';
							$drop_info      = $location['drop_info'] ?? [];
							?>
                            <tr>
                                <th><?php echo esc_html( $count ); ?>.</th>
                                <th><?php echo esc_html( $term_id ); ?></th>
                                <th class="_text_left"><a href="<?php echo esc_url( get_term_link( (int) $term_id ) ); ?>" target="_blank" class="_abp_fs_h5_color_theme"><?php echo esc_html( $name ); ?></a></th>
                                <th>
									<?php if ( $display_pickup === 'on' && ! empty( $pick_info ) ) { ?>
										<?php foreach ( $pick_info as $pick ) {
											if ( ! empty( $pick ) ) {
												?>
                                                <div class="_section_xxs">
													<?php echo esc_html( $pick['name'] );
														if ( ! empty( $pick['time'] ) ) {
															echo esc_html( ' ( ' . $pick['time'] . ' Min)' );
														}
													?>
                                                </div>
											<?php }
										} ?>
									<?php } ?>
                                </th>
                                <th>
									<?php if ( $display_drop === 'on' && ! empty( $drop_info ) ) { ?>
										<?php foreach ( $drop_info as $drop ) {
											if ( ! empty( $drop ) ) {
												?>
                                                <div class="_section_xxs">
													<?php echo esc_html( $drop['name'] );
														if ( ! empty( $drop['time'] ) ) {
															echo esc_html( ' ( ' . $drop['time'] . ' Min)' );
														} ?>
                                                </div>
											<?php }
										} ?>
									<?php } ?>
                                </th>
                                <td><?php echo esc_html( $description ); ?></td>
                                <th class="_text_nowrap"><code> [abptf-post loc_id="<?php echo esc_attr( $term_id ); ?>"]</code></th>
                                <td>
                                    <div class="_d_flex">
                                        <button type="button" class="_btn_light_yellow_mar_r_xxs" data-id="<?php echo esc_attr( $term_id ); ?>" data-target-popup="#abptf_global_popup" data-type="location" title="<?php echo esc_attr__( 'Edit : ', 'abp-transportforge' ) . ' ' . esc_attr( $name ); ?>">✍️</button>
                                        <button type="button" class="_btn_light_danger_xxs delete_location" data-loc_id="<?php echo esc_attr( $term_id ); ?>" title="<?php echo esc_attr__( 'Trash : ', 'abp-transportforge' ) . ' ' . esc_attr( $name ); ?>">❌</button>
                                    </div>
                                </td>
                            </tr>
							<?php $count ++;
						} ?>
                        </tbody>
                    </table>
				<?php } else {
					ABPTF_Layout::layout_warning_info( 'no_location' );
				}
			}

			public function form( $location = '', $loc_id = '' ): void {
				$name           = $location['name'] ?? '';
				$des            = $location['description'] ?? '';
				$slug           = $location['slug'] ?? '';
				$display_pickup = $location['display_pickup'] ?? 'off';
				$pick_infos     = $location['pick_info'] ?? [];
				$display_drop   = $location['display_drop'] ?? 'off';
				$drop_infos     = $location['drop_info'] ?? [];
				?>
                <input type="hidden" name="loc_term_id" value="<?php echo esc_attr( $loc_id ); ?>"/>
                <div class="setting_item _mar_b_xs">
                    <label class="_f_equal_f_wrap">
                        <span class="_abp_label"><?php esc_html_e( 'Location Name', 'abp-transportforge' ); ?><sup class="_color_required">*</sup></span>
                        <input class="_form_control" name="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'abp-transportforge' ); ?>" required/>
                    </label>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'loc_name' ); ?>
                </div>
                <div class="setting_item _mar_b_xs">
                    <label class="_f_equal_f_wrap">
                        <span class="_abp_label"><?php esc_html_e( 'Location Slug (Optional)', 'abp-transportforge' ); ?></span>
                        <input class="_form_control" name="slug" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'Slug', 'abp-transportforge' ); ?>"/>
                    </label>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'loc_slug' ); ?>
                </div>
                <div class="setting_item _mar_b_xs">
                    <label class="_f_equal_f_wrap">
                        <span class="_abp_label"><?php esc_html_e( 'Location Full Address(optional)', 'abp-transportforge' ); ?></span>
                        <textarea class="_form_control" name="description" placeholder="<?php esc_attr_e( 'Address', 'abp-transportforge' ); ?>"><?php echo esc_html( $des ); ?></textarea>
                    </label>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'loc_des' ); ?>
                </div>
                <div class="setting_item _mar_b_xs configuration_content">
                    <div class="_fj_between">
                        <div class="_fa_center">
							<?php ABPTF_Layout::switch_checkbox( 'display_pickup', $display_pickup ); ?>
                            <span class="_abp_label"><?php esc_html_e( 'Multiple Pickup Point ?', 'abp-transportforge' ); ?></span>
                        </div>
                        <div data-collapse="#display_pickup" class=" <?php echo esc_attr( $display_pickup === 'on' ? 'abp_active' : '' ); ?>">
							<?php ABPTF_Layout::button_add_xs( __( 'Add New Pickup Point', 'abp-transportforge' ) ); ?>
                        </div>
                    </div>
                    <div data-collapse="#display_pickup" class=" <?php echo esc_attr( $display_pickup === 'on' ? 'abp_active' : '' ); ?>">
                        <div class="_divider_xs"></div>
                        <div class="insertable_area sortable_area _gap_xs_f_wrap">
							<?php if ( ! empty( $pick_infos ) ) {
								foreach ( $pick_infos as $key => $pick_info ) {
									self::pickup_form( $pick_info, $key );
								}
							} ?>
                        </div>
                        <div class="abptf_d_none">
                            <div class="hidden_content">
								<?php self::pickup_form(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'display_pickup' ); ?>
                </div>
                <div class="setting_item configuration_content">
                    <div class="_fj_between">
                        <div class="_fa_center">
							<?php ABPTF_Layout::switch_checkbox( 'display_drop', $display_drop ); ?>
                            <span class="_abp_label"><?php esc_html_e( 'Multiple Drop Point ?', 'abp-transportforge' ); ?></span>
                        </div>
                        <div data-collapse="#display_drop" class="<?php echo esc_attr( $display_drop === 'on' ? 'abp_active' : '' ); ?>">
							<?php ABPTF_Layout::button_add_xs( __( 'Add New Drop Point', 'abp-transportforge' ) ); ?>
                        </div>
                    </div>
                    <div data-collapse="#display_drop" class="<?php echo esc_attr( $display_drop === 'on' ? 'abp_active' : '' ); ?>">
                        <div class="_divider_xs"></div>
                        <div class="insertable_area sortable_area _gap_xs_fd_column">
							<?php if ( ! empty( $drop_infos ) ) {
								foreach ( $drop_infos as $key => $drop_info ) {
									self::drop_form( $drop_info, $key );
								}
							} ?>
                        </div>
                        <div class="abptf_d_none">
                            <div class="hidden_content">
								<?php self::drop_form(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'display_drop' ); ?>
                </div>
                <div class="_divider_xs"></div>
                <button type="button" class="_btn_theme save_location"><span class="_mar_r_xxs">💾</span><?php echo( ! empty( $loc_id ) ? esc_html__( 'Update Location', 'abp-transportforge' ) : esc_html__( 'Save Location', 'abp-transportforge' ) ); ?></button>
				<?php
			}

			public static function pickup_form( $point = [], $key = '' ): void {
				?>
                <div class="delete_area _group_content">
                    <label>
                        <input type="hidden" name="pick_id[]" value="<?php echo esc_attr( $key ); ?>"/>
                        <input type="text" class="_form_control validation_name" name="pick_name[]" placeholder="<?php esc_attr_e( 'EX: Boston', 'abp-transportforge' ); ?>" value="<?php echo esc_attr( $point['name'] ?? '' ); ?>" required/>
                    </label>
                    <label>
                        <input type="text" class="_form_control validation_time_number" name="pick_time[]" placeholder="<?php esc_attr_e( 'EX: +30/-30', 'abp-transportforge' ); ?>" value="<?php echo esc_attr( $point['time'] ?? '' ); ?>" required/>
                    </label>
					<?php ABPTF_Layout::button_delete_sort(); ?>
                </div>
				<?php
			}

			public static function drop_form( $point = [], $key = '' ): void {
				?>
                <div class="delete_area _group_content _mar_b_xxs">
                    <label>
                        <input type="hidden" name="drop_id[]" value="<?php echo esc_attr( $key ); ?>"/>
                        <input type="text" class="_form_control validation_name" name="drop_name[]" placeholder="<?php esc_attr_e( 'EX: Boston', 'abp-transportforge' ); ?>" value="<?php echo esc_attr( $point['name'] ?? '' ); ?>" required/>
                    </label>
                    <label>
                        <input type="text" class="_form_control validation_time_number" name="drop_time[]" placeholder="<?php esc_attr_e( 'EX: +30/-30', 'abp-transportforge' ); ?>" value="<?php echo esc_attr( $point['time'] ?? '' ); ?>" required/>
                    </label>
					<?php ABPTF_Layout::button_delete_sort(); ?>
                </div>
				<?php
			}

			public static function location_selection( $_location = '' ): void {
				$all_location   = ABPTF_Function::get_option( 'abptf_location' );
				$all_location   = is_array( $all_location ) ? $all_location : [];
				$location_array = ! empty( $_location ) ? explode( ',', $_location ) : [];
				if ( count( $all_location ) > 0 ) { ?>
                    <div class="custom_checkbox _fj_end">
                        <input type="hidden" name="abptf_location" value="<?php echo esc_attr( $_location ); ?>"/>
						<?php foreach ( $all_location as $key => $location ) {
							$name = $location['name'] ?? ''; ?>
                            <div class="checkbox_item _min_100">
                                <button type="button" class="_btn_light_info_xs <?php echo esc_attr( in_array( (string) $key, $location_array, true ) ? 'abp_active' : '' ); ?>" data-checked="<?php echo esc_attr( $key ); ?>" data-open-icon="fa-check-square" data-close-icon="fa-square">
                                    <span data-icon class="_mar_r_xs far <?php echo esc_attr( in_array( (string) $key, $location_array, true ) ? 'far fa-check-square' : 'fa-square' ); ?>"></span><?php echo esc_html( $name ); ?>
                                </button>
                            </div>
						<?php } ?>
                        <button type="button" class="_btn_default_xs" data-target-popup="#abptf_global_popup" data-type="location"><span class="_mar_r_xs">➕</span><?php echo esc_html__( 'Add New Location', 'abp-transportforge' ); ?></button>
                    </div>
				<?php } else { ?>
                    <p><?php echo esc_html( ABPTF_Layout::array_info( 'no_location' ) ); ?></p>
                    <button type="button" class="_btn_default_xs" data-target-popup="#abptf_global_popup" data-type="location"><span class="_mar_r_xs">➕</span><?php echo esc_html__( 'Add New Location', 'abp-transportforge' ); ?></button>
					<?php
				}
			}
		}
		new ABPTF_Location();
	}