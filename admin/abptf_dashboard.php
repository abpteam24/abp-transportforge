<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Dashboard' ) ) {
		class ABPTF_Dashboard {
			public function __construct() {
				add_action( 'abptf_load_dashboard', [ $this, 'load_dashboard' ] );
			}

			public function load_dashboard( $abptf_info=[] ): void {
				$total_post     =$abptf_info['total_post'] ?? 0;
				$total_property =  $abptf_info['total_property'] ?? 0;
				?>
                <div class="abptf_dashboard">
                    <div class="dashboard_head _f_wrap">
                        <div class="_section_card" data-href="<?php echo esc_url( add_query_arg( 'tab', 'posts' ) ); ?>">
                            <div class="_d_flex">
                                <h2 class="_abp_all_center_mar_r_xs">🏘️</h2>
                                <div class="_fd_column">
                                    <h5 class="_abp"><?php esc_html_e( 'Post', 'abp-transportforge' ); ?></h5>
                                    <h4 class="_abp_color_theme"><?php echo esc_html( $total_post ); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="_section_card" data-href="<?php echo esc_url( add_query_arg( 'tab', 'properties' ) ); ?>">
                            <div class="_d_flex">
                                <h2 class="_abp_all_center_mar_r_xs">🏠</h2>
                                <div class="_fd_column">
                                    <h5 class="_abp"><?php esc_html_e( 'Properties', 'abp-transportforge' ); ?></h5>
                                    <h4 class="_abp_color_theme"><?php echo esc_html( $total_property ); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
		}
		new ABPTF_Dashboard ();
	}