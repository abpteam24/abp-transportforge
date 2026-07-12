<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_client_form_template', function ( $post_id, $post_infos = [] ) {
		if ( ABPTF_Function::on_off( 'client_info' ) ) {
			$post_id = absint( $post_id );
			if ( $post_id <= 0 ) {
				return;
			}
			$display       = $post_infos['display_client_form'] ?? ABPTF_Function::get_post_info( $post_id, 'display_client_form', 'on' );
			$active_global = $post_infos['active_global_form'] ?? ABPTF_Function::get_post_info( $post_id, 'active_global_form', 'on' );
			if ( $active_global === 'on' ) {
				$forms = ABPTF_Function::get_option( 'abptf_forms' );
			} else {
				$forms = $post_infos['abptf_forms'] ?? ABPTF_Function::get_post_info( $post_id, 'abptf_forms', [] );
			}
			if ( $display === 'on' && ! empty( $forms ) && is_array( $forms ) ) {
				?>
                <div class="client_info_area">
                    <div class="item_box_1 attendee_item">
                        <h5 class=" _abp_title">
							<?php esc_html_e( 'Client Info : ', 'abp-transportforge' ); ?>&nbsp;<span class="_color_theme attendee_seat_name"></span>
                        </h5>
						<?php
							foreach ( $forms as $id => $form ) {
								ABPTF_Layout::create_client_form( $form, $id );
							}
						?>
                    </div>
                </div>
				<?php
			}
		}
	}, 10, 2 );