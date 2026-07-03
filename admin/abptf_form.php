<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Form' ) ) {
		class ABPTF_Form {
			public function __construct() {
				add_action( 'abptf_global_client_form', array( $this, 'global_client_form' ) );
				add_action( 'abptf_post_content', [ $this, 'post_client_form' ] );
				add_filter( 'abptf_get_form_array', array( $this, 'get_form_array' ) );
				add_action( 'wp_ajax_abptf_save_client_form', array( $this, 'save_global_client_form' ) );
				add_action( 'wp_ajax_abptf_import_global_form', array( $this, 'import_global_form' ) );
			}

			public function global_client_form(): void {
				if ( ABPTF_Function::on_off( 'client_info' ) ) {
					$abptf_forms = ABPTF_Function::get_option( 'abptf_forms', ABPTF_Status::static_form() );
					?>
                    <form class="save_client_form" method="post" action="">
                        <h4 class="_abp"><span class="_mar_r_xxs">📋</span> <?php esc_html_e( 'Global Client Form Configuration', 'abp-transportforge' ); ?></h4>
						<?php ABPTF_Layout::info_text( 'global_client_forms' ); ?>
                        <div class="_divider_xs"></div>
						<?php $this->passenger_form_settings( $abptf_forms, true ); ?>
                    </form>
					<?php
				}
			}

			public function post_client_form( $abptf_infos ): void {
				if ( ABPTF_Function::on_off( 'client_info' ) ) {
					$client_forms       = $abptf_infos['client_forms'] ?? [];
					$display            = $abptf_infos['display_client_form'] ?? 'off';
					$active_global_form = $abptf_infos['active_global_form'] ?? 'on';
					?>
                    <div class="tab_item abptf_client_form" data-tabs="#abptf_client_form">
                        <h4 class=" _abp_color_theme"><span class="_mar_r_xxs">📋</span> <?php esc_html_e( 'Client Forms Configuration', 'abp-transportforge' ); ?></h4>
                        <div class="_divider_xs"></div>
                        <div class="group_setting">
                            <div class="setting_item">
                                <div class="_f_wrap_fj_between_fa_center">
                                    <div class="_fa_center">
										<?php ABPTF_Layout::switch_checkbox( 'display_client_form', $display ); ?>
                                        <span class="_fs_label_mar_l_xs"><?php esc_html_e( 'Active Client Form ?', 'abp-transportforge' ); ?></span>
                                    </div>
                                </div>
                                <div class="_divider_xs"></div>
								<?php ABPTF_Layout::info_text( 'display_client_form' ); ?>
                            </div>
                            <div data-collapse="#display_client_form" class="setting_item <?php echo esc_attr( $display == 'on' ? 'abp_active' : '' ); ?>">
                                <div class="_fj_between">
                                    <div class="_fa_center">
										<?php ABPTF_Layout::switch_checkbox( 'active_global_form', $active_global_form ); ?>
                                        <span class="_fs_label_mar_lr_xs"><?php esc_html_e( 'Use Global Client Form ?', 'abp-transportforge' ); ?></span>
                                    </div>
                                    <div data-collapse="#active_global_form" class=" <?php echo esc_attr( $active_global_form == 'on' ? '' : 'abp_active' ); ?>">
                                        <button type="button" class="_btn_theme import_global_form"><span class="fas fa-file-upload _mar_r_xs"></span><?php esc_html_e( 'Import Global Client Form', 'abp-transportforge' ); ?></button>
                                    </div>
                                </div>
                                <div class="_divider_xs"></div>
								<?php ABPTF_Layout::info_text( 'active_global_form' ); ?>
                            </div>
                        </div>
                        <div class="<?php echo esc_attr( $active_global_form == 'on' ? '' : 'abp_active' ); ?>" data-collapse="#active_global_form">
                            <div class="client_form_content _mar_t_xs">
								<?php $this->passenger_form_settings( $client_forms ); ?>
                            </div>
                        </div>
                    </div>
					<?php
				}
			}

			public function passenger_form_settings( $passenger_forms, $global = false ): void {
				?>
                <div class="configuration_content">
                    <div class="_ov_auto">
                        <table class=" _abp">
                            <thead>
                            <tr>
                                <th class="_text_table_center"><?php esc_html_e( 'Form Title', 'abp-transportforge' ); ?><sup class="_color_required">*</sup></th>
                                <th class="_text_table_center"><?php esc_html_e( 'Unique ID', 'abp-transportforge' ); ?><sup class="_color_required">*</sup></th>
                                <th class="_text_table_center"><?php esc_html_e( 'Form Type', 'abp-transportforge' ); ?><sup class="_color_required">*</sup></th>
                                <th class="_text_table_center">
									<?php esc_html_e( 'Value Option', 'abp-transportforge' ); ?><sup class="_color_required">*</sup>
									<?php ABPTF_Layout::info_text( 'client_form_option' ); ?>
                                </th>
                                <th class="_text_table_center"><?php esc_html_e( 'Default Value', 'abp-transportforge' ); ?></th>
                                <th class="_w_100_text_table_center"><?php esc_html_e( 'Required', 'abp-transportforge' ); ?><sup class="_color_required">*</sup></th>
                                <th class="_w_75_text_table_center"><?php esc_html_e( 'Action', 'abp-transportforge' ); ?></th>
                            </tr>
                            </thead>
                            <tbody class="insertable_area sortable_area">
							<?php
								if ( $passenger_forms && is_array( $passenger_forms ) && sizeof( $passenger_forms ) > 0 ) {
									foreach ( $passenger_forms as $id => $form ) {
										$this->form_item( $form, $id );
									}
								}
							?>
                            </tbody>
                        </table>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fj_between">
						<?php ABPTF_Layout::button_add_xs( __( 'Add New Form Item', 'abp-transportforge' ) );
							if ( $global ) {
								ABPTF_Layout::button_save_xs( __( 'Save Global Client Form Configuration', 'abp-transportforge' ) );
							} ?>
                    </div>
                    <div class="abptf_d_none">
                        <table class=" _abp">
                            <tbody class="hidden_content">
							<?php $this->form_item(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
				<?php
			}

			public function form_item( $form = [], $id = '' ): void {
				$form         = is_array( $form ) ? $form : [];
				$type         = $form['type'] ?? 'text';
				$required     = $form['required'] ?? 'off';
				$label        = $form['label'] ?? '';
				$options      = $form['option'] ?? '';
				$d_value      = $form['d_value'] ?? '';
				$active_type  = ( $type == 'select' || $type == 'checkbox' || $type == 'radio' ) ? 'abp_active' : '';
				$active_value = $type != 'date' ? 'abp_active' : '';
				$date         = $type == 'date' ? $d_value : '';
				$date_format  = ABPTF_Function::date_format_php();
				$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
				$hidden_date  = $date ? gmdate( 'Y-m-d', strtotime( $date ) ) : '';
				$visible_date = $date ? date_i18n( $date_format, strtotime( $date ) ) : '';
				$active_date  = $type == 'date' ? 'abp_active' : '';
				?>
                <tr class="delete_area data_single_collapse">
                    <td>
                        <label>
                            <input type="text" class="_form_control_min_150 validation_name" name="client_form_title[]" placeholder="<?php esc_attr_e( 'Name', 'abp-transportforge' ); ?>" value="<?php echo esc_attr( $label ); ?>"/>
                        </label>
                    </td>
                    <th class="_text_table_center">
						<?php if ( $id ) { ?>
                            <input type="hidden" value="<?php echo esc_attr( $id ); ?>" name="client_form_id[]" /><?php echo esc_html( $id ); ?>
						<?php } else { ?>
                            <label>
                                <input type="text" class="_form_control_min_150 validation_id" name="client_form_id[]" placeholder="<?php esc_attr_e( 'Unique ID', 'abp-transportforge' ); ?>" value="<?php echo esc_attr( $id ); ?>"/>
                            </label>
						<?php } ?>
                    </th>
                    <td>
                        <label>
                            <select class="_form_control_min_150" name="client_form_type[]" data-collapse-target data-collapse-target-multi>
                                <option value="text" data-option-target-multi="#client_form_value" <?php echo esc_attr( $type == 'text' ? 'selected' : '' ); ?>><?php esc_html_e( 'Text', 'abp-transportforge' ); ?></option>
                                <option value="email" data-option-target-multi="#client_form_value" <?php echo esc_attr( $type == 'email' ? 'selected' : '' ); ?>><?php esc_html_e( 'E-Mail', 'abp-transportforge' ); ?></option>
                                <option value="number" data-option-target-multi="#client_form_value" <?php echo esc_attr( $type == 'number' ? 'selected' : '' ); ?>><?php esc_html_e( 'Number', 'abp-transportforge' ); ?></option>
                                <option value="select" data-option-target-multi="#client_form_type #client_form_value" <?php echo esc_attr( $type == 'select' ? 'selected' : '' ); ?>><?php esc_html_e( 'Select', 'abp-transportforge' ); ?></option>
                                <option value="checkbox" data-option-target-multi="#client_form_type #client_form_value" <?php echo esc_attr( $type == 'checkbox' ? 'selected' : '' ); ?>><?php esc_html_e( 'Checkbox', 'abp-transportforge' ); ?></option>
                                <option value="radio" data-option-target-multi="#client_form_type #client_form_value" <?php echo esc_attr( $type == 'radio' ? 'selected' : '' ); ?>><?php esc_html_e( 'Radio', 'abp-transportforge' ); ?></option>
                                <option value="textarea" data-option-target-multi="#client_form_value" <?php echo esc_attr( $type == 'textarea' ? 'selected' : '' ); ?>><?php esc_html_e( 'Textarea', 'abp-transportforge' ); ?></option>
                                <option value="date" data-option-target-multi="#client_form_type_date" <?php echo esc_attr( $type == 'date' ? 'selected' : '' ); ?>><?php esc_html_e( 'Date', 'abp-transportforge' ); ?></option>
                            </select>
                        </label>
                    </td>
                    <td>
                        <label data-collapse="#client_form_type" class="<?php echo esc_attr( $active_type ); ?>">
                            <input type="text" class="_form_control_min_150 validation_name" name="client_form_option[]" placeholder="<?php esc_attr_e( 'Value Option', 'abp-transportforge' ); ?>" value="<?php echo esc_attr( $options ); ?>"/>
                        </label>
                    </td>
                    <td>
                        <div class="<?php echo esc_attr( $active_value ); ?>" data-collapse="#client_form_value">
                            <label>
                                <input type="text" class="_form_control_min_150 validation_name" name="client_form_value[]" placeholder="<?php esc_attr_e( 'Default Value', 'abp-transportforge' ); ?>" value="<?php echo esc_attr( $d_value ); ?>"/>
                            </label>
                        </div>
                        <div class="<?php echo esc_attr( $active_date ); ?>" data-collapse="#client_form_type_date">
                            <label>
                                <input type="hidden" name="client_form_value_date[]" value="<?php echo esc_attr( $hidden_date ); ?>"/>
                                <input type="text" readonly name="" class="_form_control_min_150 abp_datepicker" value="<?php echo esc_attr( $visible_date ); ?>" placeholder="<?php echo esc_attr( $now ); ?>"/>
                                <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abp-transportforge' ); ?>"></span>
                            </label>
                        </div>
                    </td>
                    <td>
						<?php ABPTF_Layout::switch_checkbox( 'client_form_required[]', $required ); ?>
                    </td>
                    <td><?php ABPTF_Layout::button_delete_sort(); ?></td>
                </tr>
				<?php
			}

			//=============================//
			public function get_form_array( array $form_infos = [] ): array {
				$has_post_nonce = isset( $_POST['abptf_post_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abptf_post_nonce'] ) ), 'abptf_post_nonce' );
				$has_ajax_nonce = check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false );
				if ( ( $has_post_nonce || $has_ajax_nonce ) && current_user_can( 'manage_options' ) ) {
					$post_array          = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) ) : [];
					$post_textarea_array = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_textarea_field', wp_unslash( $_POST[ $key ] ) ) : [];
					$form_ids            = $post_array( 'client_form_id' );
					$form_title          = $post_array( 'client_form_title' );
					$types               = $post_array( 'client_form_type' );
					$option              = $post_array( 'client_form_option' );
					$d_value             = $post_array( 'client_form_value' );
					$required            = $post_array( 'client_form_required' );
					$date_value          = $post_textarea_array( 'client_form_value_date' );
					if ( ! empty( $form_ids ) ) {
						foreach ( $form_ids as $key => $form_id ) {
							$title = $form_title[ $key ] ?? '';
							$type  = $types[ $key ] ?? '';
							if ( $form_id && $title && $type ) {
								$value                  = ( $type === 'date' ) ? ( $date_value[ $key ] ?? '' ) : ( $d_value[ $key ] ?? '' );
								$form_infos[ $form_id ] = [
									'label'    => $title,
									'type'     => $type,
									'option'   => $option[ $key ] ?? '',
									'd_value'  => $value,
									'required' => $required[ $key ] ?? 'off',
								];
							}
						}
					}
				}

				return $form_infos;
			}

			public function save_global_client_form(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$form_infos = $this->get_form_array();
				update_option( 'abptf_forms', $form_infos );
				wp_send_json_success( [ 'msg' => __( 'Client Form Configuration Saved Successfully..... !! ', 'abp-transportforge' ) ] );
			}

			public function import_global_form(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ),'type'=>'warn'  ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ),'type'=>'warn'  ], 403 );
				}
				$default_form = ABPTF_Status::static_form();
				$forms        = ABPTF_Function::get_option( 'abptf_forms', $default_form ) ?? [];
				$forms        = is_array( $forms ) ? $forms : [];
				ob_start();
				$this->passenger_form_settings( $forms );
				$html_content = ob_get_clean();
				wp_send_json_success( [ 'html' => $html_content, 'msg' => __( 'Global Client Form Imported Successfully ..... !! ', 'abp-transportforge' )  ,'type'=>'success' ] );
			}
		}
		new ABPTF_Form();
	}