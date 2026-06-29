<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Feature' ) ) {
		class ABPTF_Feature {
			public function __construct() {
				add_action( 'abptf_global_feature', array( $this, 'global_feature' ) );
				add_action( 'wp_ajax_abptf_save_feature', array( $this, 'save_feature' ) );
				add_action( 'wp_ajax_abptf_delete_feature', array( $this, 'delete_feature' ) );
				add_action( 'wp_ajax_abptf_add_feature', array( $this, 'add_feature' ) );
			}

			public function global_feature(): void {
				?>
                <div class="feature_area">
                    <div class="feature_list _group_list">
						<?php $this->feature_list(); ?>
                    </div>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::button_popup_global( 'feature', __( 'Add New Feature', 'abp-transportforge' ) ); ?>
                </div>
				<?php
			}

			public function feature_list(): void {
				$features = ABPTF_Function::get_option( 'abptf_feature' );
				//echo '<pre>';				print_r( $features );				echo '</pre>';
				if ( sizeof( $features ) > 0 ) {
					foreach ( $features as $key => $feature ) {
						$label = $feature['label'] ?? '';
						$value = $feature['value'] ?? '';
						if ( ! empty( $label ) ) { ?>
                            <div class="_list_item">
                                <h6 class="_abp">
									<?php ABPTF_Layout::image_icon( $feature['icon'] ?? '' );
										echo esc_html( $label . ' ' . ( ! empty( $value ) ? '-' . $value : '' ) ); ?>
                                </h6>
                                <div class="_group_content">
                                    <button type="button" class="_btn_light_yellow_xxs" data-id="<?php echo esc_attr( $key ); ?>" data-target-popup="#abptf_global_popup" data-type="feature" title="<?php echo esc_attr__( 'Edit : ', 'abp-transportforge' ) . ' ' . esc_attr( $label ); ?>">✍️</button>
                                    <button type="button" class="_btn_light_danger_xxs delete_feature" data-fec_id="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr__( 'Trash : ', 'abp-transportforge' ) . ' ' . esc_attr( $label ); ?>">❌</button>
                                </div>
                            </div>
							<?php
						}
					}
				} else {
					ABPTF_Layout::layout_warning_info( 'no_feature' );
				}
			}

			public function save_feature(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$post_int       = fn( $key, $default = 0 ) => isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
				$post_array     = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) ) : [];
				$old_features   = ABPTF_Function::get_option( 'abptf_feature' );
				$old_features   = is_array( $old_features ) ? $old_features : [];
				$feature_ids    = $post_array( 'feature_id' );
				$feature_names  = $post_array( 'feature_name' );
				$feature_values = $post_array( 'feature_value' );
				$feature_icon   = $post_array( 'feature_icon' );
				$abptf_post_id = $post_int( 'abptf_post_id' );
				if ( ! empty( $feature_names ) ) {
					foreach ( $feature_names as $key => $feature_name ) {
						$feature_val = $feature_values[ $key ] ?? '';
						if ( $feature_name !== '' ) {
							$old_id = isset( $feature_ids[ $key ] ) ? (int) $feature_ids[ $key ] : '';
							if ( ! empty( $old_id ) && isset( $old_features[ $old_id ] ) ) {
								$id = $old_id;
							} else {
								$id = 1;
								while ( isset( $old_features[ $id ] ) ) {
									$id ++;
								}
							}
							$old_features[ $id ] = [
								'label' => $feature_name,
								'value' => $feature_val,
								'icon'  => $feature_icon[ $key ] ?? '',
							];
						}
					}
				}
				update_option( 'abptf_feature', $old_features );
				self::update_feature_js();
				$html = '';
				if ( $abptf_post_id <= 0 ) {
					ob_start();
					$this->feature_list();
					$html = ob_get_clean();
				}
				wp_send_json_success( [
					'html'       => $html,
					'feature_js' => ABPTF_Function::get_option( 'abptf_feature_js' ),
					'msg'        => __( 'Feature Saved Successfully..........!!', 'abp-transportforge' ),
				] );
			}

			public function delete_feature(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$fec_id   = isset( $_POST['fec_id'] ) ? sanitize_text_field( wp_unslash( $_POST['fec_id'] ) ) : '';
				$features = ABPTF_Function::get_option( 'abptf_feature' );
				$features = is_array( $features ) ? $features : [];
				if ( ! empty( $fec_id ) && isset( $features[ $fec_id ] ) ) {
					unset( $features[ $fec_id ] );
					update_option( 'abptf_feature', $features );
					self::update_feature_js();
				}
				ob_start();
				$this->feature_list();
				$html = ob_get_clean();
				wp_send_json_success( [
					'html' => $html,
					'msg'  => __( 'Feature Deleted Successfully!', 'abp-transportforge' ),
				] );
			}

			public function add_feature(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				ob_start();
				$fec_id   = isset( $_POST['tax_id'] ) ? absint( wp_unslash( $_POST['tax_id'] ) ) : 0;
				$features = ABPTF_Function::get_option( 'abptf_feature' );
				$features = is_array( $features ) ? $features : [];
				$feature  = $features[ $fec_id ] ?? [];
				?>
                <div class="configuration_content">
                    <table class="_abp ">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Icon', 'abp-transportforge' ); ?></th>
                            <th><?php esc_html_e( 'Label', 'abp-transportforge' ); ?><sup class="_color_required">*</sup></th>
                            <th><?php esc_html_e( 'Value', 'abp-transportforge' ); ?></th>
                            <th class="_w_10"><?php esc_html_e( 'Action', 'abp-transportforge' ); ?></th>
                        </tr>
                        </thead>
                        <tbody class="insertable_area sortable_area">
						<?php self::form_feature( $feature, $fec_id ); ?>
                        </tbody>
                    </table>
                    <div class="_divider_xs"></div>
                    <div class="_fj_between">
						<?php ABPTF_Layout::button_add_xs( __( 'Add New Feature Item', 'abp-transportforge' ) ); ?>
                        <button type="button" class="_btn_theme_xs  save_feature"><span class="_mar_r_xxs">💾</span><?php esc_html_e( 'Save Feature', 'abp-transportforge' ); ?></button>
                    </div>
                    <div class="abptf_d_none">
                        <table class="_abp">
                            <tbody class="hidden_content">
							<?php self::form_feature(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
				<?php
				$html = ob_get_clean();
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'Feature Form Loaded Successfully .....! ', 'abp-transportforge' ) ] );
			}

			public static function update_feature_js(): void {
				$features   = ABPTF_Function::get_option( 'abptf_feature' );
				$features   = is_array( $features ) ? $features : [];
				$feature_js = [];
				if ( sizeof( $features ) > 0 ) {
					foreach ( $features as $key => $feature ) {
						$feature_js[] = [ 'id' => $key, 'icon' => ( $feature['icon'] ?? '' ), 'label' => ( $feature['label'] ?? '' ), 'value' => ( $feature['value'] ?? '' ) ];
					}
					update_option( 'abptf_feature_js', $feature_js );
				}
			}

			public static function form_feature( $feature = [], $id = '' ): void {
				$label = $feature['label'] ?? '';
				$value = $feature['value'] ?? '';
				$icon  = $feature['icon'] ?? '';
				?>
                <tr class="delete_area">
                    <th><?php do_action( 'abptf_add_icon', 'feature_icon[]', $icon ); ?></th>
                    <th>
                        <label>
                            <input type="hidden" name="feature_id[]" value="<?php echo esc_attr( $id ); ?>"/>
                            <input type="text" class="_form_control validation_name" name="feature_name[]" placeholder="<?php esc_attr_e( 'EX: Feature Title', 'abp-transportforge' ); ?>" value="<?php echo esc_attr( $label ); ?>" required/>
                        </label>
                    </th>
                    <th>
                        <label>
                            <input type="text" class="_form_control validation_name" name="feature_value[]" placeholder="<?php esc_attr_e( 'EX: Feature Value', 'abp-transportforge' ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
                        </label>
                    </th>
                    <td><?php ABPTF_Layout::button_delete_sort(); ?></td>
                </tr>
				<?php
			}
		}
		new ABPTF_Feature();
	}