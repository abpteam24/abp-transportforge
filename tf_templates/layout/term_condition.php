<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	add_action( 'abptf_term_condition_template', function ( $abptf_infos = [], $type = '' ) {
		if ( ABPTF_Function::on_off( 'tc' ) ) {
			$infos = '';
			if ( ! empty( $abptf_infos ) && is_array( $abptf_infos ) ) {
				$display          = $abptf_infos['display_tc'] ?? 'on';
				$active_global_tc = $abptf_infos['active_global_tc'] ?? 'on';
				if ( $display === 'on' ) {
					$infos = ( $active_global_tc === 'on' ) ? ABPTF_Function::get_option( 'abptf_tc', '' ) : ( $abptf_infos['abptf_tc'] ?? '' );
				}
			} elseif ( $type === 'global' ) {
				$infos = ABPTF_Function::get_option( 'abptf_tc', '' );
			}
			if ( ! empty( $infos ) && is_string( $infos ) ) {
				?>
                <div class="_abp_panel">
                    <div class="_panel_head">
                        <h4 class="_abp"><span class="_mar_r_xxs">🤝</span> <?php esc_html_e( 'Term & Conditions', 'abp-transportforge' ); ?></h4>
                    </div>
                    <div class="_panel_body_xs">
						<?php
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
							echo wp_kses_post( apply_filters( 'the_content', $infos ) );
						?>
                    </div>
                </div>
				<?php
			}
		}
	}, 10, 2 );