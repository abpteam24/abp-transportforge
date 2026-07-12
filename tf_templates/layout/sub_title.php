<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_sub_title_template', function ( $post_id, $post_infos = [] ) {
		if ( ABPTF_Function::on_off( 'sub_title' ) ) {
			$post_id = absint( $post_id );
			if ( $post_id <= 0 ) {
				return;
			}
			$sub_title         = $post_infos['sub_title'] ?? ABPTF_Function::get_post_info( $post_id, 'sub_title' );
			if (!empty($sub_title) ) {
				?>
                <p class="_abp sub_title"><?php echo esc_html( $sub_title ); ?></p>
				<?php
			}
		}
	}, 10, 2 );