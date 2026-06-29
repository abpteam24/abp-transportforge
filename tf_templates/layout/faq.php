<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_faq_template', function ( $abptf_infos = [], $type = '' ) {
		if ( ABPTF_Function::on_off( 'faq' ) ) {
			$faq_infos = [];
			if ( ! empty( $abptf_infos ) ) {
				$display           = $abptf_infos['display_faq'] ?? 'on';
				$active_global_faq = $abptf_infos['active_global_faq'] ?? 'on';
				if ( $display === 'on' ) {
					$faq_infos = ( $active_global_faq === 'on' )
						? ABPTF_Function::get_option( 'abptf_faqs' )
						: ( $abptf_infos['abptf_faqs'] ?? [] );
				}
			} elseif ( $type === 'global' ) {
				$faq_infos = ABPTF_Function::get_option( 'abptf_faqs' );
			}
			if ( empty( $faq_infos ) || ! is_array( $faq_infos ) ) {
				return;
			}
			?>
            <div class="_abp_panel faq_area">
                <div class="_panel_head">
                    <h4 class="_abp">
                        <span class="_mar_r_xxs">❓</span>
						<?php esc_html_e( 'Frequently Asked Questions', 'abp-transportforge' ); ?>
                    </h4>
                </div>
                <div class="_panel_body_xs faq_list">
					<?php
						foreach ( $faq_infos as $faq ) {
							$title       = $faq['title'] ?? '';
							$description = $faq['des'] ?? '';
							if ( $description !== '' ) {
								$description = html_entity_decode( $description );
							}
							if ( empty( $title ) ) {
								continue;
							}
							?>
                            <div class="faq_item">
                                <div class="faq_question faq_target">
                                    <h5 class="_abp"><?php echo esc_html( $title ); ?></h5>
                                    <span class="faq_icon"></span>
                                </div>
                                <div class="faq_answer">
                                    <div class="faq_answer_content">
										<?php
											// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
											echo wp_kses_post( apply_filters( 'the_content', $description ) );
										?>
                                    </div>
                                </div>
                            </div>
							<?php
						}
					?>
                </div>
            </div>
			<?php
		}
	}, 10, 2 );