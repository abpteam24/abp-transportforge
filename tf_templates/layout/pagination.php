<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_pagination_template', function ( $args = [] ) {
		$total    = $args['total'] ?? 0;
		$page     = $args['page_number'] ?? 1;
		$per_page = $args['page_item'] ?? $total;
		$style    = ( $args['style'] ?? 'live' ) ?: 'live';
		if ( $total > $per_page ) { ?>
            <div class="pagination_area _all_center">
				<?php if ( $style == 'live' ) { ?>
                    <button type="button" class="_btn_theme_min_200 live_pagination" data-load-more="0"><?php esc_html_e( 'View More', 'abp-transportforge' ); ?></button>
                    <input type="hidden" name="page_item" value="<?php echo esc_attr( $per_page ); ?>"/>
				<?php } else {
					$page_mod   = $total % $per_page;
					$total_page = (int) ( $total / $per_page ) + ( $page_mod > 0 ? 1 : 0 );
					$start_page = max( $page - 5, 1 );
					$end_page   = min( $start_page + 10, $total_page );
					?>
                    <label class="_mar_r _max_200">
                        <span class="_mar_r_xs"><?php esc_html_e( 'Showing Item', 'abp-transportforge' ); ?></span>
                        <input type="number" class="_form_control_h_min_auto_w_min_auto  validation_number" name="page_item" value="<?php echo esc_attr( $per_page ); ?>" data-min="1" data-max="100"/>
                    </label>
                    <div class="_group_content">
						<?php if ( $total_page > 1 ) { ?>
                            <button class="_btn_tp_xs" type="button" data-page="<?php echo esc_attr( $page - 1 > 0 ? $page - 1 : $total_page ); ?>" title="<?php esc_attr_e( 'Previous Page', 'abp-transportforge' ); ?>"><span class="fas fa-angle-left"></span></button>
						<?php }
							if ( $total_page > 11 && $page > 6 ) { ?>
                                <button class="_btn_tp_xs" type="button" disabled><span class="fas fa-ellipsis-h "></span></button>
							<?php }
							for ( $i = $start_page; $i <= $end_page; $i ++ ) { ?>
                                <button class="_btn_tp_xs <?php echo esc_attr( $i == $page ? 'abp_active' : '' ); ?>" type="button" data-page="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></button>
							<?php }
							if ( $total_page > 11 && $page < $total_page - 5 ) { ?>
                                <button class="_btn_tp_xs" type="button" disabled><span class="fas fa-ellipsis-h"></span></button>
							<?php }
							if ( $total_page > 1 ) { ?>
                                <button class="_btn_tp_xs" type="button" data-page="<?php echo esc_attr( $page < $total_page ? $page + 1 : 1 ); ?>" title="<?php esc_attr_e( 'Next Page', 'abp-transportforge' ); ?>"><span class="fas fa-angle-right"></span></button>
							<?php } ?>
                    </div>
				<?php } ?>
            </div>
		<?php } else { ?>
            <input type="hidden" name="page_item" value="<?php echo esc_attr( $per_page ); ?>"/>
		<?php }
	}, 10, 2 );
