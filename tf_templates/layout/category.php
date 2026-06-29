<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_category_template', function ( $post_id, $ribbon = '' ) {
		if ( ABPTF_Function::on_off( 'category' ) ) {
			$post_id = absint( $post_id );
			if ( $post_id <= 0 ) {
				return;
			}
			$display_category = ABPTF_Function::get_post_info( $post_id, 'display_category', 'on' );
			$cat_id           = ABPTF_Function::get_post_info( $post_id, 'abptf_category' );
			$category         = ABPTF_Category[ $cat_id ]['name'] ?? '';
			if ( ! empty( $category ) && $display_category === 'on' ) {
				if ( $ribbon === 'ribbon' ) {
					?>
                    <div class="ribbon publish"><?php echo esc_html( $category ); ?></div>
				<?php } else { ?>
                    <div class="_fs_label">
						<?php echo esc_html( ABPTF_Function::category_label() . ' :  ' . $category ); ?>
                    </div>
					<?php
				}
			}
		}
	}, 10, 2 );