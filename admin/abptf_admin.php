<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_ADMIN' ) ) {
		class ABPTF_ADMIN {
			public function __construct() {
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'abptf_load_global', array( $this, 'load_global' ) );
			}

			public function admin_menu(): void {
				$label = ABPTF_Function::label();
				$slug  = ABPTF_Function::slug();
				$icon  = ABPTF_Function::icon_wp();
				add_menu_page( $label, $label, 'manage_options', $slug, array( $this, 'load_main_page' ), $icon, 50 );
			}

			public function load_main_page(): void {
				remove_all_actions( 'user_admin_notices' );
				remove_all_actions( 'admin_notices' );
				remove_all_actions( 'all_admin_notices' );
				remove_all_actions( 'network_admin_notices' );
				add_filter( 'wp_dependency_installer_errors', '__return_false' );
				$abptf_info   = ABPTF_Query::get_info();
				$label        = ABPTF_Function::label();
				$icon         = ABPTF_Function::icon();
				$total_post   = $abptf_info['total_post'] ?? 0;
				$total_order  = $abptf_info['total_order'] ?? 0;
				$allowed_tabs = [ 'dashboard', 'posts', 'orders', 'global', 'configuration', 'status', 'documentation', 'admin_order' ];
				$active_tab   = 'posts';
				if ( isset( $_GET['_abptf_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_abptf_nonce'] ) ), 'abptf_url_action' ) ) {
					$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'posts';
				}
				if ( ! in_array( $active_tab, $allowed_tabs, true ) ) {
					$active_tab = 'posts';
				}
				if ( ABPTF_WC < 2 ) {
					$active_tab = 'status';
				}
				?>
                <div class="abptf_area  abptf_admin">
                    <div class="admin_head _fj_between">
                        <div class="head_brand _d_flex">
                            <div class="brand_icon _all_center"><?php ABPTF_Layout::image_icon( $icon ); ?></div>
                            <div class="_fd_column">
                                <h4 class="_abp"><?php echo esc_html( $label ); ?></h4>
                                <span class="brand_version"><?php echo esc_html( ABPTF_VERSION ); ?></span>
                            </div>
                        </div>
						<?php if ( ABPTF_WC == 2 ) { ?>
                            <div class="_group_content">
                                <button type="button" class="_btn_white" data-href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . ABPTF_Function::get_cpt() ) ); ?>" data-blank="_blank"><span class="_mar_r_xs">➕</span><?php esc_html_e( 'Add New Post', 'abp-transportforge' ); ?></button>
								<?php if ( ABPTF_Function::on_off( 'category' ) ) { ?>
                                    <button type="button" class="_btn_white" data-target-popup="#abptf_global_popup" data-type="category"><span class="_mar_r_xs">➕</span><?php echo esc_html__( 'Add New', 'abp-transportforge' ) . ' ' . esc_html( ABPTF_Function::category_label() ); ?></button>
								<?php } ?>

                                    <button type="button" class="_btn_white" data-target-popup="#abptf_global_popup" data-type="location"><span class="_mar_r_xs">➕</span><?php esc_html_e( 'Add New Location', 'abp-transportforge' ); ?></button>

                            </div>
						<?php } ?>
                    </div>
                    <div class="admin_menu">
                        <div class="menu_list">
                            <!--                            <a href="--><?php //echo esc_url( add_query_arg( 'tab', 'dashboard' ) ); ?><!--" class="_btn_light_info --><?php //echo esc_attr( $active_tab == 'dashboard' ? 'abp_active' : '' ); ?><!--"><span class="_mar_r_xs">📊</span>--><?php //esc_html_e( 'Dashboard', 'abp-transportforge' ); ?><!--</a>-->
                            <a href="<?php echo esc_url( ABPTF_Function::build_url( 'posts' ) ); ?>" class="_btn_info post_tab <?php echo esc_attr( $active_tab == 'posts' ? 'abp_active' : '' ); ?>"><?php ABPTF_Layout::image_icon( $icon ); ?><?php esc_html_e( 'Post Lists', 'abp-transportforge' ); ?><sup class="_mar_l_xs_circle_icon_xs"><?php echo esc_html( $total_post ); ?></sup></a>
                            <a href="<?php echo esc_url( ABPTF_Function::build_url( 'orders' ) ); ?>" class="_btn_info <?php echo esc_attr( $active_tab == 'orders' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">📋</span><?php esc_html_e( 'Orders', 'abp-transportforge' ); ?><sup class="_mar_l_xs_circle_icon_xs"><?php echo esc_html( $total_order ); ?></sup></a>
							<?php do_action( 'abptf_add_admin_menu_tab_middle', $active_tab ); ?>
                            <a href="<?php echo esc_url( ABPTF_Function::build_url( 'global' ) ); ?>" class="_btn_info <?php echo esc_attr( $active_tab == 'global' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">🌐</span><?php esc_html_e( 'Global Data', 'abp-transportforge' ); ?></a>
                            <a href="<?php echo esc_url( ABPTF_Function::build_url( 'configuration' ) ); ?>" class="_btn_info <?php echo esc_attr( $active_tab == 'configuration' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">⚙️</span><?php esc_html_e( 'Configuration', 'abp-transportforge' ); ?></a>
                            <a href="<?php echo esc_url( ABPTF_Function::build_url( 'status' ) ); ?>" class="_btn_info <?php echo esc_attr( $active_tab == 'status' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">🛡️</span><?php esc_html_e( 'Status', 'abp-transportforge' ); ?></a>
							<?php do_action( 'abptf_add_admin_menu_tab', $active_tab ); ?>
                        </div>
                    </div>
                    <div class="dashboard_content">
						<?php do_action( 'abptf_load_' . $active_tab, $abptf_info ); ?>
                    </div>
					<?php ABPTF_Layout::load_admin_globally(); ?>
                </div>
				<?php
			}

			public function load_global( $abptf_info ): void {
				$allowed_tabs = [ 'dates', 'additional', 'client_form', 'seat_plan', 'resource', 'category','organizer', 'location', 'feature', 'brand', 'discount' ];
				$active_tab   = 'dates';
				if ( isset( $_GET['_abptf_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_abptf_nonce'] ) ), 'abptf_url_action' ) ) {
					$active_tab = isset( $_GET['global'] ) ? sanitize_text_field( wp_unslash( $_GET['global'] ) ) : 'dates';
				}
				if ( ! in_array( $active_tab, $allowed_tabs, true ) ) {
					$active_tab = 'dates';
				}
				?>
                <div class="_abp_panel_max_1200_mar_auto">
                    <div class="_panel_head">
                        <div class="_group_content_w_full">
                            <a href="<?php echo esc_url( ABPTF_Function::build_url( 'global', [ 'global' => 'dates' ] ) ); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr( $active_tab == 'dates' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">🗓️</span> <?php esc_html_e( 'Dates', 'abp-transportforge' ); ?></a>
							<?php if ( ABPTF_Function::on_off( 'additional_info' ) ) { ?>
                                <a href="<?php echo esc_url( ABPTF_Function::build_url( 'global', [ 'global' => 'additional' ] ) ); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr( $active_tab == 'additional' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">💰</span> <?php esc_html_e( 'Additional services', 'abp-transportforge' ); ?></a>
							<?php } ?>
							<?php if ( ABPTF_Function::on_off( 'client_info' ) ) { ?>
                                <a href="<?php echo esc_url( ABPTF_Function::build_url( 'global', [ 'global' => 'client_form' ] ) ); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr( $active_tab == 'client_form' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">📋</span> <?php esc_html_e( 'Client Form', 'abp-transportforge' ); ?></a>
							<?php } ?>
							<?php if ( ABPTF_Function::on_off( 'sp' ) ) { ?>
                                <a href="<?php echo esc_url( ABPTF_Function::build_url( 'global', [ 'global' => 'seat_plan' ] ) ); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr( $active_tab == 'seat_plan' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">💺</span> <?php esc_html_e( 'Seat Plan', 'abp-transportforge' ); ?></a>
							<?php } ?>
							<?php do_action( 'abptf_add_admin_global_tab', $active_tab ); ?>
							<?php if ( ABPTF_Function::on_off( 'tc' ) || ABPTF_Function::on_off( 'faq' ) ) { ?>
                                <a href="<?php echo esc_url( ABPTF_Function::build_url( 'global', [ 'global' => 'resource' ] ) ); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr( $active_tab == 'resource' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">📚</span><?php esc_html_e( 'Resources', 'abp-transportforge' ); ?></a>
							<?php } ?>
							<?php if ( ABPTF_Function::on_off( 'category' ) ) { ?>
                                <a href="<?php echo esc_url( ABPTF_Function::build_url( 'global', [ 'global' => 'category' ] ) ); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr( $active_tab == 'category' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">🏘️</span><?php echo esc_html( ABPTF_Function::category_label() ); ?></a>
							<?php } ?>
							<?php if ( ABPTF_Function::on_off( 'organizer' ) ) { ?>
                                <a href="<?php echo esc_url( ABPTF_Function::build_url( 'global', [ 'global' => 'organizer' ] ) ); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr( $active_tab == 'organizer' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">🏢</span><?php echo esc_html( ABPTF_Function::organizer_label() ); ?></a>
							<?php } ?>

                                <a href="<?php echo esc_url( ABPTF_Function::build_url( 'global', [ 'global' => 'location' ] ) ); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr( $active_tab == 'location' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">📍</span><?php echo esc_html( ABPTF_Function::location_label() ); ?></a>

							<?php if ( ABPTF_Function::on_off( 'brand' ) ) { ?>
                                <a href="<?php echo esc_url( ABPTF_Function::build_url( 'global', [ 'global' => 'brand' ] ) ); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr( $active_tab == 'brand' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">🏷️</span><?php echo esc_html( ABPTF_Function::brand_label() ) ?></a>
							<?php } ?>
                            <a href="<?php echo esc_url( ABPTF_Function::build_url( 'global', [ 'global' => 'feature' ] ) ); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr( $active_tab == 'feature' ? 'abp_active' : '' ); ?>"><span class="_mar_r_xxs">🔗</span><?php esc_html_e( 'Feature', 'abp-transportforge' ); ?></a>
                        </div>
                    </div>
                    <div class="_panel_body  _bg_white">
						<?php do_action( 'abptf_global_' . $active_tab, $abptf_info ); ?>
                    </div>
                </div>
				<?php
			}
		}
		new ABPTF_ADMIN();
	}