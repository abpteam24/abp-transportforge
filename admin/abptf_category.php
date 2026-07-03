<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Category' ) ) {
		class ABPTF_Category {
			public function __construct() {
				add_action( 'abptf_global_category', array( $this, 'global_category' ) );
				add_action( 'abptf_category_update', array( $this, 'update_category' ) );
				add_action( 'wp_ajax_abptf_save_category', array( $this, 'save_category' ) );
				add_action( 'wp_ajax_abptf_delete_category', array( $this, 'delete_category' ) );
				add_action( 'wp_ajax_abptf_add_category', array( $this, 'add_category' ) );
			}

			public function global_category(): void {
				if ( ABPTF_Function::on_off( 'category' ) ) {
					?>
                    <div class="category_list _ov_auto">
						<?php $this->category_list(); ?>
                    </div>
                    <div class="_divider_xs"></div>
					<?php
					ABPTF_Layout::button_popup_global( 'category', __( 'Add New', 'abp-transportforge' ) . ' ' . ABPTF_Function::category_label() );
				}
			}

			public function update_category(): void {
				$taxonomies = ABPTF_Function::get_taxonomy( 'abptf_category' );
				$category   = [];
				if ( ! empty( $taxonomies ) && is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
					foreach ( $taxonomies as $taxonomy ) {
						$category[ $taxonomy->term_id ]['name']        = $taxonomy->name;
						$category[ $taxonomy->term_id ]['description'] = $taxonomy->description;
					}
				}
				ksort( $category );
				update_option( 'abptf_category', $category );
			}

			public function save_category(): void {
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
				$cat_term_id   = $post_int( 'cat_term_id' );
				$name          = $post_val( 'name' );
				$slug          = $post_slug( 'slug' );
				$description   = $post_textarea( 'description' );
				$abptf_post_id = $post_int( 'abptf_post_id' );
				if ( empty( $name ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Category Name cannot be blank!', 'abp-transportforge' ) ], 400 );
				}
				if ( $cat_term_id > 0 ) {
					$result = wp_update_term( $cat_term_id, 'abptf_category', [
						'name'        => $name,
						'slug'        => $slug,
						'description' => $description,
					] );
				} else {
					$result = wp_insert_term( $name, 'abptf_category', [
						'slug'        => $slug,
						'description' => $description,
					] );
				}
				$this->update_category();
				ob_start();
				if ( $abptf_post_id > 0 ) {
					$_category = ABPTF_Function::get_post_info( $abptf_post_id, 'abptf_category' );
					self::category_selection( $_category );
				} else {
					$this->category_list();
				}
				$html = ob_get_clean();
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'html' => $html, 'msg' => $result->get_error_message() ], 400 );
				}
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'Category Saved Successfully !', 'abp-transportforge' ) ] );
			}

			public function delete_category(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$cat_id = isset( $_POST['cat_id'] ) ? absint( wp_unslash( $_POST['cat_id'] ) ) : 0;
				if ( ! $cat_id ) {
					wp_send_json_error( __( 'Invalid Category ID.', 'abp-transportforge' ), 400 );
				}
				$result = wp_delete_term( $cat_id, 'abptf_category' );
				$this->update_category();
				ob_start();
				$this->category_list();
				$html = ob_get_clean();
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'html' => $html, 'msg' => $result->get_error_message() ], 400 );
				}
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'Category Delete Successfully !', 'abp-transportforge' ) ] );
			}

			public function add_category(): void {
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
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'Category Form Loaded Successfully .....! ', 'abp-transportforge' ) ] );
			}

			public function form( $term_id = '' ): void {
				$name           = $slug = $des = '';
				$category_label = ABPTF_Function::category_label();
				if ( ! empty( $term_id ) ) {
					$term = get_term( $term_id );
					if ( ! empty( $term ) ) {
						$name = $term->name;
						$slug = $term->slug;
						$des  = $term->description;
					}
				}
				?>
                <input type="hidden" name="cat_term_id" value="<?php echo esc_attr( $term_id ); ?>"/>
                <div class="setting_item _mar_b_xs">
                    <label class="_f_equal_f_wrap">
                        <span class="_abp_label"><?php echo esc_html( $category_label ) . ' ' . esc_html__( 'Name', 'abp-transportforge' ); ?><sup class="_color_required">*</sup></span>
                        <input class="_form_control" name="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'abp-transportforge' ); ?>" required/>
                    </label>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'cat_name' ); ?>
                </div>
                <div class="setting_item _mar_b_xs">
                    <label class="_f_equal_f_wrap">
                        <span class="_abp_label"><?php echo esc_html( $category_label ) . ' ' . esc_html__( 'Slug (Optional)', 'abp-transportforge' ); ?></span>
                        <input class="_form_control" name="slug" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'Slug', 'abp-transportforge' ); ?>"/>
                    </label>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'cat_slug' ); ?>
                </div>
                <div class="setting_item _mar_b_xs">
                    <label class="_f_equal_f_wrap">
                        <span class="_abp_label"><?php echo esc_html( $category_label ) . ' ' . esc_html__( 'Description(Optional)', 'abp-transportforge' ); ?></span>
                        <textarea class="_form_control" name="description" placeholder="<?php esc_attr_e( 'Description', 'abp-transportforge' ); ?>"><?php echo esc_html( $des ); ?></textarea>
                    </label>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'cat_des' ); ?>
                </div>
                <div class="_divider_xs"></div>
                <button type="button" class="_btn_theme_xs save_category"><span class="_mar_r_xxs">💾</span><?php echo ( ! empty( $term_id ) ? esc_html__( 'Update', 'abp-transportforge' ) : esc_html__( 'Save', 'abp-transportforge' ) ) . ' ' . esc_html( $category_label ); ?></button>
				<?php
			}

			public function category_list(): void {
				$all_categories = ABPTF_Function::get_option( 'abptf_category' );
				$count          = 1;
				if ( ! empty( $all_categories ) && is_array( $all_categories ) && sizeof( $all_categories ) > 0 ) { ?>
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'SI', 'abp-transportforge' ) ?></th>
                            <th class="_min_200"><?php esc_html_e( 'Category Title', 'abp-transportforge' ) ?></th>
                            <th><?php esc_html_e( 'ID', 'abp-transportforge' ) ?></th>
                            <th class="_min_150"><?php esc_html_e( 'Description', 'abp-transportforge' ) ?></th>
                            <th class="_w_250"><?php esc_html_e( 'Shortcode Post', 'abp-transportforge' ) ?></th>
                            <th class="_w_100"><?php esc_html_e( 'Action', 'abp-transportforge' ) ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php foreach ( $all_categories as $term_id => $category ) {
							$name        = $category['name'] ?? '';
							$description = $category['description'] ?? '';
							?>
                            <tr>
                                <th><?php echo esc_html( $count ); ?>.</th>
                                <th class="_text_left"><a href="<?php echo esc_url( get_term_link( $term_id ) ); ?>" target="_blank" class="_abp_fs_h5_color_theme"><?php echo esc_html( $name ); ?></a></th>
                                <th><?php echo esc_html( $term_id ); ?></th>
                                <td><?php echo esc_html( $description ); ?></td>
                                <th><code> [abptf-post cat_id="<?php echo esc_attr( $term_id ); ?>"]</code></th>
                                <th>
                                    <div class="_group_content">
                                        <button type="button" class="_btn_light_yellow_mar_r_xxs" data-id="<?php echo esc_attr( $term_id ); ?>" data-target-popup="#abptf_global_popup" data-type="category" title="<?php echo esc_attr__( 'Edit : ', 'abp-transportforge' ) . ' ' . esc_attr( $name ); ?>">✍️</button>
                                        <button type="button" class="_btn_light_danger_xxs delete_category" data-cat_id="<?php echo esc_attr( $term_id ); ?>" title="<?php echo esc_attr__( 'Trash : ', 'abp-transportforge' ) . ' ' . esc_attr( $name ); ?>">❌</button>
                                    </div>
                                </th>
                            </tr>
							<?php $count ++;
						} ?>
                        </tbody>
                    </table>
				<?php } else {
					ABPTF_Layout::layout_warning_info( 'no_category' );
				}
			}

			public static function category_selection( $_category = '' ): void {
				$all_categories = ABPTF_Function::get_option( 'abptf_category' );
				if ( ! empty( $all_categories ) && is_array( $all_categories ) && sizeof( $all_categories ) > 0 ) { ?>
                    <div class="custom_radio _f_wrap_gap_xs">
                        <input type="hidden" name="abptf_category" value="<?php echo esc_attr( $_category ); ?>"/>
						<?php foreach ( $all_categories as $key => $category ) {
							$name = $category['name'] ?? ''; ?>
                            <div class="radio_item">
                                <button type="button" class="_btn_light_info_xs <?php echo esc_attr( $_category == $key ? 'abp_active' : '' ); ?>" data-radio="<?php echo esc_attr( $key ); ?>" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                    <span data-icon class="_mar_r_xs <?php echo esc_attr( $_category == $key ? 'far fa-check-circle' : 'far fa-circle' ); ?>"></span><span class="_text_left_fs_label"><?php echo esc_html( $name ); ?></span>
                                </button>
                            </div>
						<?php } ?>
                    </div>
				<?php } else { ?>
                    <p><?php echo esc_html( ABPTF_Status::array_info( 'no_category' ) ); ?></p>
					<?php
				}
			}
		}
		new ABPTF_Category();
	}