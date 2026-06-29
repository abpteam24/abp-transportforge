<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_missionary_template', function ( $params = [] ) {
		//echo '<pre>';print_r($params);echo '</pre>';
		$global_order = $abptf_infos['global_order'] ?? '';
		$post_ids     = $params['all_post'] ?? [];
		if ( ! empty( $post_ids ) && sizeof( $post_ids ) > 0 ) {
			$column            = $params['column'] ?? 3;
			$show_post         = absint( ( $params['show'] ?? 0 ) ?: ( $column * 3 ) );
			$post_count        = 0;
			$args['total']     = sizeof( $post_ids );
			$args['page_item'] = $show_post;
			asort( $post_ids );
			?>
            <div class=" abptf_missionary  item_<?php echo esc_attr( $column ); ?>">
				<?php foreach ( $post_ids as $post_id ) {
					$cat_id = ABPTF_Function::get_post_info( $post_id, 'abptf_category' );
					$loc_id = ABPTF_Function::get_post_info( $post_id, 'abptf_location' );
					$post_count ++;
					$title = get_the_title( $post_id ); ?>
                    <div class="pagination_item item_box_1  <?php echo esc_attr( $show_post >= $post_count ? '' : 'abp_close' ); ?>" data-cat_id="<?php echo esc_attr( $cat_id ); ?>" data-loc_id="<?php echo esc_attr( $loc_id ); ?>">
                        <div class="item_head">
							<?php do_action( 'abptf_category', $post_id, 'ribbon' ); ?>
							<?php ABPTF_Layout::image( $post_id ); ?>
							<?php if ( ! empty( $global_order ) ) { ?>
                                <button type="button" class="_btn_theme_xs_w_full select_post" data-post_id="<?php echo esc_attr( $post_id ); ?>">
									<?php if ( ABPTF_Function::on_off( 'post_icon' ) ) {
										ABPTF_Layout::image_icon( ABPTF_Function::get_post_info( $post_id, 'post_icon' ));
									}
										echo esc_html( $title ); ?>
                                </button>
							<?php } else { ?>
                                <a class="_abp list_title" href="<?php echo esc_url( get_the_permalink( $post_id ) ); ?>" target="_blank"><?php echo esc_html( $title ); ?></a>
							<?php } ?>
                        </div>
                    </div>
				<?php } ?>
            </div>
			<?php do_action( 'abptf_pagination', $args ); ?>
			<?php
		} else {
			ABPTF_Layout::layout_warning_info( 'not_found' );
		}
	}, 10, 2 );