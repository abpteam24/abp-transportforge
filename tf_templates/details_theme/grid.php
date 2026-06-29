<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_details_grid_template', function ( $post_id ) {
		if ( $post_id > 0 ) {
			$abptf_infos         = ABPTF_Function::get_all_meta( $post_id );
			$sale_continue       = $abptf_infos['sale_continue'] ?? 'on';
			$abptf_infos['form'] = 'inline';
			//echo '<pre>';print_r($abptf_infos);echo '</pre>';
			?>
            <div id="abptf_area" class="abptf_area default_details_page">
                <div class="abptf_container">
                    <div class="_abp_row">
                        <div class="_fd_column_mar_b">
							<?php do_action( 'abptf_title', $post_id, $abptf_infos );
								do_action( 'abptf_sub_title', $post_id, $abptf_infos ); ?>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12">
							<?php if ( $sale_continue == 'on' ) {
								do_action( 'abptf_search_form', $abptf_infos );
							} else {
								ABPTF_Layout::layout_warning_info( 'sale_close_msg' );
							} ?>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12">
							<?php do_action( 'abptf_registration', $abptf_infos ); ?>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12">
							<?php do_action( 'abptf_content', $post_id ); ?>
                        </div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12_mar_t"> <?php do_action( 'abptf_slider', ( $abptf_infos['abptf_sliders'] ?? [] ) ); ?></div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12_mar_t"> <?php do_action( 'abptf_faq', $abptf_infos ); ?></div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12_mar_t"> <?php do_action( 'abptf_term_condition', $abptf_infos ); ?></div>
                    </div>
                    <div class="_abp_row">
                        <div class="_col_12_mar_t"> <?php do_action( 'abptf_related_item', ( $abptf_infos['related_item'] ?? '' ) ); ?></div>
                    </div>
                </div>
            </div>
			<?php
		}
	} );
