<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_title_template', function ( $post_id, $abptf_infos = [] ) {
		if ( ! empty( $post_id ) && $post_id > 0 ) {
			$display_sku = $abptf_infos['display_sku'] ?? ABPTF_Function::get_post_info( $post_id, 'display_sku', 'off' );
			$post_sku    = $abptf_infos['post_sku'] ?? ABPTF_Function::get_post_info( $post_id, 'post_sku' );
			?>
            <h1 class="_abp_color_theme">
				<?php
					if ( ABPTF_Function::on_off( 'post_icon' ) ) {
						ABPTF_Layout::image_icon( ( $abptf_infos['post_icon'] ?? ABPTF_Function::get_post_info( $post_id, 'post_icon' ) ) );
					}
					echo esc_html( get_the_title( $post_id ) ); ?>
				<?php if ( ! empty( $post_sku ) && $display_sku == 'on' && ABPTF_Function::on_off( 'sku' ) ) { ?>
                    <small class="_abp_color_gray">&nbsp;(<?php echo esc_html( $post_sku ); ?>)</small>
				<?php } ?>
            </h1>
			<?php
		}
	}, 10, 2 );

