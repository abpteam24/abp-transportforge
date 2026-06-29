<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	add_action( 'abptf_post_filter_template', function ( $params ) {
		if ( ! is_array( $params ) ) {
			return;
		}
		$style      = $params['style'] ?? 'grid';
		$post_ids   = $params['all_post'] ?? [];
		$cat_id     = $params['cat_id'] ?? null;
		$loc_id     = $params['loc_id'] ?? null;
		$categories = [];
		if ( empty( $cat_id ) && is_array( $post_ids ) && ! empty( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				$category = ABPTF_Function::get_post_info( $post_id, 'abptf_category' );
				if ( ! empty( $category ) ) {
					$categories[] = $category;
				}
			}
			$categories = array_unique( $categories );
		}
		$locations = [];
		if ( empty( $loc_id ) && is_array( $post_ids ) && ! empty( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				$location_array = ABPTF_Function::get_post_info( $post_id, 'abptf_location' );
				if ( ! empty( $location_array ) ) {
					$location_array = explode( ',', $location_array );
					$locations      = array_merge( $locations, $location_array );
				}
			}
			$locations = array_unique( $locations );
		}
		$cat_count = count( $categories );
		$loc_count = count( $locations );
		if ( $cat_count > 1 || $loc_count > 1 || $style === 'grid' || $style === 'list' ) {
			?>
            <div class="post_top_filter">
				<?php
					if ( $cat_count > 1 ) {
						if ( $cat_count > 4 && is_array( ABPTF_Category ) ) {
							?>
                            <label>
                                <select class="_form_control" name="cat_id">
                                    <option value="" selected><?php echo esc_html__( 'All ', 'abp-transportforge' ) . ' ' . esc_html( ABPTF_Function::category_label() ); ?></option>
									<?php
										foreach ( $categories as $current_cat_id ) {
											$name = ABPTF_Category[ $current_cat_id ]['name'] ?? '';
											if ( $name !== '' ) {
												?>
                                                <option value="<?php echo esc_attr( $current_cat_id ); ?>"><?php echo esc_html( $name ); ?></option>
												<?php
											}
										}
									?>
                                </select>
                            </label>
							<?php
						} else {
							?>
                            <div class="custom_radio _group_content">
                                <input type="hidden" name="cat_id" value=""/>
                                <div class="radio_item">
                                    <button type="button" class="_btn_light_info_xs_fs_h6 abp_active" data-radio="" data-open-icon="fa-check-circle" data-close-icon="fa-circle">
                                        <span data-icon class="_mar_r_xs far fa-check-circle"></span><?php echo esc_html__( 'All ', 'abp-transportforge' ) . ' ' . esc_html( ABPTF_Function::category_label() ); ?>
                                    </button>
                                </div>
								<?php
									foreach ( $categories as $current_cat_id ) {
										$name = ABPTF_Category[ $current_cat_id ]['name'] ?? '';
										if ( $name !== '' ) {
											?>
                                            <div class="radio_item">
                                                <button type="button" class="_btn_light_info_xs_fs_h6" data-radio="<?php echo esc_attr( $current_cat_id ); ?>" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                                    <span data-icon class="_mar_r_xxs far fa-circle"></span><?php echo esc_html( $name ); ?>
                                                </button>
                                            </div>
											<?php
										}
									}
								?>
                            </div>
							<?php
						}
					}
					if ( $loc_count > 1 ) {
						if ( $loc_count > 4 && is_array( ABPTF_Locations ) ) {
							?>
                            <label>
                                <select class="_form_control" name="loc_id">
                                    <option value="" selected><?php esc_html_e( 'All Location', 'abp-transportforge' ); ?></option>
									<?php
										foreach ( $locations as $current_loc_id ) {
											$name = ABPTF_Locations[ $current_loc_id ]['name'] ?? '';
											if ( $name !== '' ) {
												?>
                                                <option value="<?php echo esc_attr( $current_loc_id ); ?>"><?php echo esc_html( $name ); ?></option>
												<?php
											}
										}
									?>
                                </select>
                            </label>
							<?php
						} else {
							?>
                            <div class="custom_radio _group_content">
                                <input type="hidden" name="loc_id" value=""/>
                                <div class="radio_item">
                                    <button type="button" class="_btn_light_info_xs_fs_h6 abp_active" data-radio="" data-open-icon="fa-check-circle" data-close-icon="fa-circle">
                                        <span data-icon class="_mar_r_xs far fa-check-circle"></span><?php esc_html_e( 'All Location', 'abp-transportforge' ); ?>
                                    </button>
                                </div>
								<?php
									foreach ( $locations as $current_loc_id ) {
										$name = ABPTF_Locations[ $current_loc_id ]['name'] ?? '';
										if ( $name !== '' ) {
											?>
                                            <div class="radio_item">
                                                <button type="button" class="_btn_light_info_xs_fs_h6" data-radio="<?php echo esc_attr( $current_loc_id ); ?>" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                                    <span data-icon class="_mar_r_xxs far fa-circle"></span><?php echo esc_html( $name ); ?>
                                                </button>
                                            </div>
											<?php
										}
									}
								?>
                            </div>
							<?php
						}
					}
					if ( $style === 'grid' || $style === 'list' ) {
						?>
                        <div class="_group_content">
                            <button type="button" class="_btn_light_info_xs_fs_h6 grid_view <?php echo esc_attr( $style === 'grid' ? 'abp_active' : '' ); ?>"><span class="fas fa-table-cells"></span></button>
                            <button type="button" class="_btn_light_info_xs_fs_h6 list_view <?php echo esc_attr( $style === 'list' ? 'abp_active' : '' ); ?>"><span class="fas fa-list"></span></button>
                        </div>
						<?php
					}
				?>
            </div>
			<?php
		}
	} );