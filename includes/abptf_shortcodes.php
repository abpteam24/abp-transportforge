<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Shortcodes' ) ) {
		class ABPTF_Shortcodes {
			public function __construct() {
				add_shortcode( 'abptf-booking', array( $this, 'booking' ) );
				add_shortcode( 'abptf-post', array( $this, 'post_list' ) );
				add_shortcode( 'abptf-gallery', array( $this, 'gallery' ) );
			}

			public function booking( $attribute ): bool|string {
				$defaults = $this->default_attribute();
				$params   = shortcode_atts( $defaults, $attribute );
				$post_id  = $params['post_id'] ?? '';
				ob_start();
				if ( ! empty( $post_id ) ) {
					do_action( 'abptf_load_details_template', $post_id );
				} else {
					$params['all_post']     = ABPTF_Query::get_post_id( $params );
					$params['global_order'] = 'yes';
					$style                  = ( $params['style'] ?? 'grid' ) ?: 'grid';
					$file                   = ABPTF_Function::template_path( 'list/' . $style . '.php' );
					?>
                    <div class="abptf_area">
                        <div class="abptf_container">
                            <div class="global_form"><?php do_action( 'abptf_search_form', $params ); ?></div>
                            <div class="abptf_global_registration tf_pagination">
								<?php
									do_action( 'abptf_post_filter', $params );
									if ( is_file( $file ) ) {
										include_once $file;
										do_action( 'abptf_' . $style . '_template', $params );
									} else {
										include_once ABPTF_Function::template_path( 'list/default.php' );
										do_action( 'abptf_default_template', $params );
									} ?>
                                <div class="tf_no_results _d_none">
									<?php ABPTF_Layout::layout_warning_info( 'not_match' ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php
				}

				return ob_get_clean();
			}

			public function post_list( $attribute ): bool|string {
				$defaults = $this->default_attribute();
				$params   = shortcode_atts( $defaults, $attribute );
				$post_id  = $params['post_id'] ?? '';
				//echo '<pre>';print_r($params);echo '</pre>';
				ob_start();
				if ( ! empty( $post_id ) ) {
					do_action( 'abptf_load_details_template', $post_id );
				} else {
					$params['all_post'] = ABPTF_Query::get_post_id( $params );
					$style              = ( $params['style'] ?? 'grid' ) ?: 'grid';
					$file               = ABPTF_Function::template_path( 'list/' . $style . '.php' );
					?>
                    <div class="abptf_area">
                        <div class="abptf_container tf_pagination">
							<?php
								do_action( 'abptf_post_filter', $params );
								if ( is_file( $file ) ) {
									include_once $file;
									do_action( 'abptf_' . $style . '_template', $params );
								} else {
									include_once ABPTF_Function::template_path( 'list/default.php' );
									do_action( 'abptf_default_template', $params );
								} ?>
                            <div class="tf_no_results _d_none">
								<?php ABPTF_Layout::layout_warning_info( 'not_match' ); ?>
                            </div>
                        </div>
                    </div>
					<?php
				}

				return ob_get_clean();
			}

			public function gallery( $attribute ): bool|string {
				$defaults = $this->default_attribute();
				$params   = shortcode_atts( $defaults, $attribute );
				$post_id  = $params['post_id'] ?? '';
				ob_start();
				?>
                <div class="abptf_area">
                    <div class="abptf_container global_slider tf_pagination">
						<?php
							if ( ! empty( $post_id ) ) {
								$img_infos = ABPTF_Function::get_post_info( $post_id, 'abptf_sliders', [] );
								do_action( 'abptf_slider', $img_infos, $params );
							} else {
								$post_ids  = ABPTF_Query::get_post_id( $params );
								$img_infos = [];
								if ( ! empty( $post_ids ) && sizeof( $post_ids ) > 0 ) {
									foreach ( $post_ids as $post_id ) {
										$info      = ABPTF_Function::get_post_info( $post_id, 'abptf_sliders', [] );
										$img_infos = array_merge( $img_infos, $info );
									}
									do_action( 'abptf_slider', $img_infos, $params );
								}
							}
						?>
                    </div>
                </div>
				<?php
				return ob_get_clean();
			}

			public function default_attribute(): array {
				return array(
					"post_id"          => '',
					"cat_id"           => '',
					"loc_id"           => '',
					"brand_id"         => '',
					"rent_rule"        => '',
					"style"            => 'grid',
					"slider_style"     => 'gallery',
					"show"             => '',
					"column"           => 3,
					'sort'             => 'ASC',
					"pagination"       => "yes",
					"pagination-style" => "live",
					'form'             => 'inline',
				);
			}
		}
		new ABPTF_Shortcodes();
	}