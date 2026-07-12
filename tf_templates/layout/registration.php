<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_registration_template', function ( $post_infos = [] ) {
		$post_id = absint( $post_infos['post_id'] ?? 0 );
		if ( $post_id <= 0 ) {
			return;
		}
		$rent_rule = $post_infos['rent_rule'] ?? 'hourly';
		$location  = $post_infos['location'] ?? '';
		$template  = $post_infos['abptf_template'] ?? 'grid';
		$filter_arg = [
			'post_id' => $post_id,
			'sale_continue' => 'on',
			'status' => 'publish',
		];
		if ( ! empty( $rent_rule ) ) {
			$filter_arg['rent_rule'] = $rent_rule;
		}
		if ( ! empty( $location ) ) {
			$filter_arg['location'] = $location;
		}
		$properties = ABPTF_Query::get_property( $filter_arg );
		?>
        <div class="abptf_booking">
            <form action="" method="post">
				<?php
					wp_nonce_field( 'abptf_registration_nonce' );
					do_action( 'abptf_admin_order', $post_id );
					if ( $template === 'grid' || empty( $template ) ) {
						?>
                        <div class="post_top_filter">
                            <h3 class="_abp"><?php esc_html_e( 'Available Property', 'abp-transportforge' ); ?></h3>
                            <div class="_group_content">
                                <button type="button" class="_btn_light_info_xs_fs_h6 grid_view abp_active">
                                    <span class="fas fa-table-cells"></span>
                                </button>
                                <button type="button" class="_btn_light_info_xs_fs_h6 list_view">
                                    <span class="fas fa-list"></span>
                                </button>
                            </div>
                        </div>
						<?php if ( ! empty( $properties ) && is_array( $properties ) ) { ?>
                            <div class="property_item_area abptf_grid item_3">
								<?php
									foreach ( $properties as $property ) {
										do_action( 'abptf_property_item', $post_infos, $property );
									}
								?>
                            </div>
						<?php } else {
							ABPTF_Layout::layout_warning_info( 'no_property_found' );
						} ?>
					<?php } else { ?>
                        <div class="group_property item_box_1">
                            <h5 class="_abp_title"><?php esc_html_e( 'Available Property', 'abp-transportforge' ); ?></h5>
                            <div class="property_item_area">
								<?php if ( ! empty( $properties ) && is_array( $properties ) ) {
									do_action( 'abptf_property_item_group', $post_infos, $properties );
								} else {
									ABPTF_Layout::layout_warning_info( 'no_property_found' );
								} ?>
                            </div>
                        </div>
					<?php } ?>
                <div class="property_others"></div>
            </form>
        </div>
		<?php
	}, 10, 2 );