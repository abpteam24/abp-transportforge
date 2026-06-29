<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_search_form_template', function ( $abptf_infos = [] ) {
		$admin_order  = $abptf_infos['admin_order'] ?? '';
		$global_order = $abptf_infos['global_order'] ?? '';
		$post_id      = absint( $abptf_infos['post_id'] ?? 0 );
		$location     = $abptf_infos['abptf_location'] ?? '';
		$params_form  = $abptf_infos['form'] ?? 'inline';
		$brand_icon   = ABPTF_Function::icon();
		$rent_rule    = $abptf_infos['rent_rule'] ?? null;
		if ( empty( $rent_rule ) && $post_id > 0 ) {
			$rent_rule = ABPTF_Function::get_post_info( $post_id, 'rent_rule' );
		}
		if ( isset( $_SESSION['abptf_cart_success'] ) && empty( $admin_order ) ) {
			?>
            <div class="toast_notice" data-type="success">
				<?php echo esc_html( sanitize_text_field( wp_unslash( $_SESSION['abptf_cart_success'] ) ) ); ?>
            </div>
			<?php
			unset( $_SESSION['abptf_cart_success'] );
		}
		$all_dates     = ABPTF_Function::get_start_dates( $post_id );
		$upcoming_date = current( $all_dates );
		$upcoming_date = ! empty( $upcoming_date ) ? gmdate( 'Y-m-d', strtotime( $upcoming_date ) ) : '';
		?>
        <div id="abptf_search_area">
            <h2 class="_abp_mar_b_xs">
                <span class="_mar_r_xxs">📅</span><?php esc_html_e( 'Select Rental Period', 'abp-transportforge' ); ?>
            </h2>
            <form class="abptf_property_form <?php echo esc_attr( $params_form === 'column' ? '_form_column' : '_form_inline' ); ?>" method="post" action="">
				<?php if ( $post_id > 0 && empty( $global_order ) ) { ?>
                    <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>"/>
				<?php } else {
					ABPTF_Layout::filter_post_list( $post_id );
				} ?>
                <input type="hidden" name="rent_rule" value="<?php echo esc_attr( $rent_rule ); ?>"/>
				<?php
					ABPTF_Layout::location_select( $post_id, $location );
					if ( $rent_rule === 'monthly' && ! empty( $all_dates ) ) {
						$all_dates     = ABPTF_Function::get_start_month( $post_id, $all_dates );
						$first_array   = current( $all_dates );
						$upcoming_date = $first_array['value'] ?? '';
						$upcoming_date = ! empty( $upcoming_date ) ? gmdate( 'Y-m-d', strtotime( $upcoming_date ) ) : '';
						?>
                        <div class="start_date _input_item"><?php ABPTF_Layout::rent_start_month( $all_dates ); ?></div>
                        <div class="end_date _input_item"><?php ABPTF_Layout::rent_end_month( $post_id, $upcoming_date ); ?></div>
						<?php
					}
					$is_standard_rule = in_array( $rent_rule, [ 'hourly', 'daily', 'multi_day', 'multi_month' ], true );
					if ( $is_standard_rule || $post_id === 0 ) {
						?>
                        <div class="start_date _input_item">
							<?php ABPTF_Layout::rent_start_date( $all_dates, $upcoming_date, $post_id ); ?>
                        </div>
					<?php } ?>

				<?php if ( $rent_rule === 'hourly' || $rent_rule === 'multi_day' ) { ?>
                    <div class="start_time _input_item">
                        <label>
                            <span><i class="fas fa-calendar-check _mar_r_xxs"></i><?php esc_html_e( 'Pickup Time', 'abp-transportforge' ); ?><sup class="_color_required">*</sup></span>
                            <select class="_form_control" name="start_time"></select>
                        </label>
                    </div>
				<?php } ?>

				<?php if ( $rent_rule === 'daily' || $rent_rule === 'multi_day' || $rent_rule === 'multi_month' || $post_id === 0 ) {
					$all_end_dates = ABPTF_Function::get_end_dates( $post_id, $upcoming_date, $all_dates );
					?>
                    <div class="end_date _input_item">
						<?php ABPTF_Layout::rent_end_date( $all_end_dates, $post_id ); ?>
                    </div>
				<?php } ?>

				<?php if ( $rent_rule === 'hourly' || $rent_rule === 'multi_day' ) { ?>
                    <div class="end_time _input_item">
                        <label>
                            <span><i class="fas fa-calendar-check _mar_r_xxs"></i><?php esc_html_e( 'Drop-off Time', 'abp-transportforge' ); ?><sup class="_color_required">*</sup></span>
                            <select class="_form_control" name="end_time"></select>
                        </label>
                    </div>
				<?php } ?>
                <div class="_input_item_fj_between_fd_column">
                    <span></span>
                    <button type="submit" class="_btn_theme">
						<?php ABPTF_Layout::image_icon( $brand_icon ); ?>
						<?php esc_html_e( 'Check Availability', 'abp-transportforge' ); ?>
                    </button>
                </div>
            </form>
            <div class="date_details"></div>
        </div>
		<?php
	} );