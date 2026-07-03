<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'ABPTF_Routing' ) ) {
		class ABPTF_Routing {
			public function __construct() {
				add_action( 'abptf_post_content', [ $this, 'routing' ] );
			}

			public function routing( $post_infos = [] ): void {
				$routing_infos = $post_infos['routing_infos'] ?? [];
				?>
                <div class="tab_item abptf_routing" data-tabs="#abptf_routing">
                    <div class="_fj_between_fa_center">
                        <h4 class="_abp_color_theme"><span class="fas fa-route _mar_r_xs"></span><?php esc_html_e( 'Route Configuration', 'abp-transportforge' ); ?></h4>
						<?php ABPTF_Layout::button_popup_global( 'location', __( 'Add New', 'abp-transportforge' ) . ' ' . ABPTF_Function::location_label() ); ?>
                    </div>
					<?php
						ABPTF_Layout::info_text( 'route_config_1' );
						ABPTF_Layout::info_text( 'route_config_2' );
						ABPTF_Layout::info_text( 'route_config_3' );
						ABPTF_Layout::info_text( 'route_config_4' );
					?>
                    <div class="_divider_xs"></div>
                    <div class="configuration_content">
                        <div class="_ov_auto">
                            <table class="_abp">
                                <thead>
                                <tr>
                                    <th class="_w_50"></th>
                                    <th><span class="fas fa-route _mar_r_xs"></span><?php esc_html_e( 'Stops Name', 'abp-wc-transport-manager' ); ?><sup class="_text_required">*</sup></th>
                                    <th><?php esc_html_e( 'Stops Type', 'abp-wc-transport-manager' ); ?><sup class="_text_required">*</sup></th>
                                    <th><?php esc_html_e( 'Time(in min)', 'abp-wc-transport-manager' ); ?><sup class="_text_required">*</sup></th>
									<?php if ( ABPTF_Function::on_off( 'pickup' ) ) { ?>
                                        <th><?php esc_html_e( 'Multi Pickup Points', 'abp-wc-transport-manager' ); ?></th>
									<?php } ?>
									<?php if ( ABPTF_Function::on_off( 'drop' ) ) { ?>
                                        <th><?php esc_html_e( 'Multi Drop-off Points', 'abp-wc-transport-manager' ); ?></th>
									<?php } ?>
                                    <th class="_w_75"><?php esc_html_e( 'Action', 'abp-wc-transport-manager' ); ?></th>
                                </tr>
                                </thead>
                                <tbody class="insertable_area sortable_area route_configuration">
								<?php
									if ( ! empty( $routing_infos ) ) {
										foreach ( $routing_infos as $routing_info ) {
											$this->stop_item( $routing_info );
										}
									}
								?>
                                </tbody>
                            </table>
                        </div>
                        <div class="_divider_xs"></div>
						<?php ABPTF_Layout::button_add_xs( __( 'Add New stops', 'abp-wc-transport-manager' ) ); ?>
                        <div class="abptf_d_none">
                            <table class="_abp">
                                <tbody class="hidden_content">
								<?php $this->stop_item(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
				<?php
			}

			public function stop_item( $field = [] ): void {
				$stop = $field['stop'] ?? '';
				$type = $field['type'] ?? 'bp';
				?>
                <tr class="delete_area data_single_collapse">
                    <th class="_text_table_center "><span class="fas fa-arrow-down"></span></th>
                    <td>
                        <label>
                            <select name="stop_name[]" class='_form_control_w_full'>
                                <option value="" selected><?php esc_html_e( 'Select Stops.', 'abp-wc-transport-manager' ); ?></option>
								<?php if ( ! empty( ABPTF_Locations ) ) {
									foreach ( ABPTF_Locations as $key => $location ) { ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $key == $stop ? 'selected' : '' ); ?>><?php echo esc_html( $location['name'] ?? '' ); ?></option>
										<?php
									}
								} ?>
                            </select>
                        </label>
                    </td>
                    <th>
                        <label>
                            <select name="stop_type[]" class='_form_control_w_full' data-collapse-target data-collapse-target-multi>
                                <option value="bp" <?php echo esc_attr( $type == 'bp' ? 'selected' : '' ); ?> data-option-target-multi="#bp"><?php esc_html_e( 'Boarding', 'abp-wc-transport-manager' ); ?></option>
                                <option value="dp" <?php echo esc_attr( $type == 'dp' ? 'selected' : '' ); ?> data-option-target-multi="#dp"><?php esc_html_e( 'Dropping', 'abp-wc-transport-manager' ); ?></option>
                                <option value="both" <?php echo esc_attr( $type == 'both' ? 'selected' : '' ); ?> data-option-target-multi="#bp #dp"><?php esc_html_e( 'Both', 'abp-wc-transport-manager' ); ?></option>
                            </select>
                        </label>
                    </th>
                    <th>
                        <label>
                            <input type="number" class="_form_control_w_full validation_number" name="stop_time[]" value="<?php echo esc_attr( $field['time'] ?? '' ); ?>"/>
                        </label>
                    </th>
					<?php if ( ABPTF_Function::on_off( 'pickup' ) ) { ?>
                        <td>
                            <div class="<?php echo esc_attr( $type == 'bp' || $type == 'both' ? 'abp_active' : '' ); ?>" data-collapse="#bp">
                                <div class="_all_center">
									<?php ABPTF_Layout::switch_checkbox( 'display_pickup[]', ( $field['display_pickup'] ?? 'on' ) ); ?>
                                </div>
                            </div>
                        </td>
					<?php } ?>
					<?php if ( ABPTF_Function::on_off( 'drop' ) ) { ?>
                        <td>
                            <div class="<?php echo esc_attr( $type == 'dp' || $type == 'both' ? 'abp_active' : '' ); ?>" data-collapse="#dp">
                                <div class="_all_center">
									<?php ABPTF_Layout::switch_checkbox( 'display_drop[]', ( $field['display_drop'] ?? 'on' ) ); ?>
                                </div>
                            </div>
                        </td>
					<?php } ?>
                    <td><?php ABPTF_Layout::button_delete_sort(); ?></td>
                </tr>
				<?php
			}
		}
		new ABPTF_Routing();
	}