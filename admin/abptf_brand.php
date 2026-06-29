<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Brand' ) ) {
		class ABPTF_Brand {
			public function __construct() {
				add_action( 'abptf_global_brand', array( $this, 'global_brand' ) );
				add_action( 'abptf_brand_update', array( $this, 'update_brand' ) );
				add_action( 'wp_ajax_abptf_save_brand', array( $this, 'save_brand' ) );
				add_action( 'wp_ajax_abptf_delete_brand', array( $this, 'delete_brand' ) );
				add_action( 'wp_ajax_abptf_add_brand', array( $this, 'add_brand' ) );
			}

			public function global_brand(): void {
				if ( ABPTF_Function::on_off( 'brand' ) ) {
					?>
                    <div class="tab_item brand_area" data-tabs="#abptf_global_brand">
                        <div class="brand_list _ov_auto">
							<?php $this->brand_list(); ?>
                        </div>
                        <div class="_divider_xs"></div>
                    </div>
					<?php
					ABPTF_Layout::button_popup_global( 'brand', __( 'Add New', 'abp-transportforge' ) . ' ' . ABPTF_Function::brand_label() );
				}
			}

			public function update_brand(): void {
				$taxonomies = ABPTF_Function::get_taxonomy( 'abptf_brand' );
				$category   = [];
				if ( ! empty( $taxonomies ) && is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
					foreach ( $taxonomies as $taxonomy ) {
						$category[ $taxonomy->term_id ]['name']        = $taxonomy->name;
						$category[ $taxonomy->term_id ]['description'] = $taxonomy->description;
					}
				}
				ksort( $category );
				update_option( 'abptf_brand', $category );
			}

			public function save_brand(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$post_int      = fn( $key, $default = 0 ) => isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
				$post_val      = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
				$post_textarea = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : $default;
				$post_slug     = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_title( wp_unslash( $_POST[ $key ] ) ) : $default;
				$tax_id        = $post_int( 'brand_id' );
				$name          = $post_val( 'name' );
				$slug          = $post_slug( 'slug' );
				$description   = $post_textarea( 'description' );
				$abptf_post_id = $post_int( 'abptf_post_id' );
				if ( empty( $name ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Brand Name cannot be blank!', 'abp-transportforge' ) ], 400 );
				}
				if ( $tax_id > 0 ) {
					$result = wp_update_term( $tax_id, 'abptf_brand', [
						'name'        => $name,
						'slug'        => $slug,
						'description' => $description,
					] );
				} else {
					$result = wp_insert_term( $name, 'abptf_brand', [
						'slug'        => $slug,
						'description' => $description,
					] );
				}
				$this->update_brand();
				ob_start();
				if ( $abptf_post_id > 0 ) {
					self::brand_selection();
				} else {
					$this->brand_list();
				}
				$html = ob_get_clean();
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'html' => $html, 'msg' => $result->get_error_message() ], 400 );
				}
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'Brand Saved Successfully !', 'abp-transportforge' ) ] );
			}

			public function delete_brand(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$brand_id = isset( $_POST['brand_id'] ) ? absint( wp_unslash( $_POST['brand_id'] ) ) : 0;
				if ( $brand_id <= 0 ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid Brand ID ..... !!', 'abp-transportforge' ) ], 400 );
				}
				$result = wp_delete_term( $brand_id, 'abptf_brand' );
				if ( is_wp_error( $result ) ) {
					ob_start();
					$this->brand_list();
					$html = ob_get_clean();
					wp_send_json_error( [ 'html' => $html, 'msg' => $result->get_error_message() ], 400 );
				}
				$brands = ABPTF_Function::get_option( 'abptf_brand' );
				if ( is_array( $brands ) && isset( $brands[ $brand_id ] ) ) {
					unset( $brands[ $brand_id ] );
					update_option( 'abptf_brand', $brands );
				}
				ob_start();
				$this->brand_list();
				$html = ob_get_clean();
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'Brand Delete Successfully !', 'abp-transportforge' ) ] );
			}

			public function add_brand(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$cat_id = isset( $_POST['tax_id'] ) ? absint( wp_unslash( $_POST['tax_id'] ) ) : 0;
				ob_start();
				$this->form( $cat_id );
				$html = ob_get_clean();
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'Brand Form Loaded Successfully .....! ', 'abp-transportforge' ) ] );
			}

			public function brand_list(): void {
				$abptf_brands = ABPTF_Function::get_option( 'abptf_brand' );
				$count        = 1;
				if ( ! empty( $abptf_brands ) && is_array( $abptf_brands ) && sizeof( $abptf_brands ) > 0 ) { ?>
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'SI', 'abp-transportforge' ) ?></th>
                            <th class="_min_200"><?php esc_html_e( 'Brand Title', 'abp-transportforge' ) ?></th>
                            <th><?php esc_html_e( 'ID', 'abp-transportforge' ) ?></th>
                            <th class="_min_150"><?php esc_html_e( 'Description', 'abp-transportforge' ) ?></th>
                            <th class="_w_250"><?php esc_html_e( 'Shortcode Property', 'abp-transportforge' ) ?></th>
                            <th class="_w_100"><?php esc_html_e( 'Action', 'abp-transportforge' ) ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php foreach ( $abptf_brands as $term_id => $brand ) {
							$name        = $brand['name'] ?? '';
							$description = $brand['description'] ?? '';
							?>
                            <tr>
                                <th><?php echo esc_html( $count ); ?>.</th>
                                <th class="_text_left"><a href="<?php echo esc_url( get_term_link( $term_id ) ); ?>" target="_blank" class="_abp_fs_h5 _color_theme"><?php echo esc_html( $name ); ?></a></th>
                                <th><?php echo esc_html( $term_id ); ?></th>
                                <td><?php echo esc_html( $description ); ?></td>
                                <th><code> [abptf-property brand_id="<?php echo esc_attr( $term_id ); ?>"]</code></th>
                                <th>
                                    <div class="_group_content">
                                        <button type="button" class="_btn_light_yellow_xxs" data-id="<?php echo esc_attr( $term_id ); ?>" data-target-popup="#abptf_global_popup" data-type="brand" title="<?php echo esc_attr__( 'Edit : ', 'abp-transportforge' ) . ' ' . esc_attr( $name ); ?>">✍️</button>
                                        <button type="button" class="_btn_light_danger_xxs delete_brand" data-id="<?php echo esc_attr( $term_id ); ?>" title="<?php echo esc_attr__( 'Trash : ', 'abp-transportforge' ) . ' ' . esc_attr( $name ); ?>">❌</button>
                                    </div>
                                </th>
                            </tr>
							<?php $count ++;
						} ?>
                        </tbody>
                    </table>
				<?php } else {
					ABPTF_Layout::layout_warning_info( 'no_brand' );
				}
			}

			public static function brand_selection( $_brand = '' ): void {
				$brands      = ABPTF_Function::get_option( 'abptf_brand' );
				if ( ! empty( $brands ) && is_array( $brands ) && sizeof( $brands ) > 0 ) { ?>
                    <label>
                        <select class="_form_control" name="abptf_brand">
                            <option value="" selected><?php esc_html_e( 'Please Select Brand', 'abp-transportforge' ); ?></option>
							<?php foreach ( $brands as $key => $brand ) { ?>
                                <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $key==$_brand ? 'selected' : '' ); ?>><?php echo esc_html( $brand['name'] ?? '' ); ?></option>
							<?php } ?>
                        </select>
                    </label>
				<?php } else { ?>
                    <p class="_abp"><?php echo esc_html( ABPTF_Layout::array_info( 'no_brand' ) ); ?></p>
				<?php }
			}

			public function form( $term_id = '' ): void {
				$name  = $slug = $des = '';
				$label = ABPTF_Function::brand_label();
				if ( ! empty( $term_id ) ) {
					$term = get_term( $term_id );
					if ( ! empty( $term ) ) {
						$name = $term->name;
						$slug = $term->slug;
						$des  = $term->description;
					}
				}
				?>
                <input type="hidden" name="brand_id" value="<?php echo esc_attr( $term_id ); ?>"/>
                <div class="setting_item _mar_b_xs">
                    <label class="_f_equal_f_wrap">
                        <span class="_abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Name', 'abp-transportforge' ); ?><sup class="_color_required">*</sup></span>
                        <input class="_form_control" name="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'abp-transportforge' ); ?>" required/>
                    </label>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'cat_name' ); ?>
                </div>
                <div class="setting_item _mar_b_xs">
                    <label class="_f_equal_f_wrap">
                        <span class="_abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Slug (Optional)', 'abp-transportforge' ); ?></span>
                        <input class="_form_control" name="slug" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'Slug', 'abp-transportforge' ); ?>"/>
                    </label>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'cat_slug' ); ?>
                </div>
                <div class="setting_item _mar_b_xs">
                    <label class="_f_equal_f_wrap">
                        <span class="_abp_label"><?php echo esc_html( $label ) . ' ' . esc_html__( 'Description(Optional)', 'abp-transportforge' ); ?></span>
                        <textarea class="_form_control" name="description" placeholder="<?php esc_attr_e( 'Description', 'abp-transportforge' ); ?>"><?php echo esc_html( $des ); ?></textarea>
                    </label>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'cat_des' ); ?>
                </div>
                <div class="_divider_xs"></div>
                <button type="button" class="_btn_theme save_brand"><span class="_mar_r_xxs">💾</span><?php echo ( ! empty( $term_id ) ? esc_html__( 'Update', 'abp-transportforge' ) : esc_html__( 'Save', 'abp-transportforge' ) ) . ' ' . esc_html( $label ); ?></button>
				<?php
			}

		}
		new ABPTF_Brand();
	}