<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_duration_template', function ( $post_infos = [] ) {
		$date_infos = $post_infos['date_info'] ?? [];
		if ( ! empty( $date_infos ) && is_array( $date_infos ) ) {
			$dif_text   = $date_infos['text'] ?? '';
			$start_time = $post_infos['start_time'] ?? '';
			$end_time   = $post_infos['end_time'] ?? '';
			?>
            <div class="duration_area">
                <h5 class="_abp"><?php esc_html_e( 'Total Rental Duration', 'abp-transportforge' ); ?></h5>
                <h2 class="_abp"><?php echo esc_html( $dif_text ); ?></h2>
                <h6 class="_abp">
					<?php echo esc_html( ABPTF_Function::date_format( $start_time ) ) . '  →  ' . esc_html( ABPTF_Function::date_format( $end_time ) ); ?>
                </h6>
            </div>
			<?php
		}
	}, 10, 2 );