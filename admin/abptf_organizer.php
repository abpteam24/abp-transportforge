<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Organizer' ) ) {
		class ABPTF_Organizer {
			public function __construct() {
				add_action( 'abptf_global_organizer', array( $this, 'global_organizer' ) );
				add_action( 'abptf_organizer_update', array( $this, 'update_organizer' ) );
				add_action( 'wp_ajax_abptf_save_organizer', array( $this, 'save_organizer' ) );
				add_action( 'wp_ajax_abptf_delete_organizer', array( $this, 'delete_organizer' ) );
				add_action( 'wp_ajax_abptf_add_organizer', array( $this, 'add_organizer' ) );
			}

			public function global_organizer(): void {
				if ( ABPTF_Function::on_off( 'organizer' ) ) {
					?>
                    <div class="organizer_list _ov_auto">
						<?php $this->organizer_list(); ?>
                    </div>
                    <div class="_divider_xs"></div>
					<?php
					ABPTF_Layout::button_popup_global( 'organizer', __( 'Add New', 'abp-transportforge' ) . ' ' . ABPTF_Function::organizer_label() );
				}
			}

			public function update_organizer(): void {
				$taxonomies = ABPTF_Function::get_taxonomy( 'abptf_organizer' );
				$organizer  = [];
				if ( ! empty( $taxonomies ) && is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
					foreach ( $taxonomies as $taxonomy ) {
						$organizer[ $taxonomy->term_id ]['name']        = $taxonomy->name;
						$organizer[ $taxonomy->term_id ]['description'] = $taxonomy->description;
					}
				}
				ksort( $organizer );
				update_option( 'abptf_organizer', $organizer );
			}

			public function save_organizer(): void {
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
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Organizer Name cannot be blank!', 'abp-transportforge' ) ], 400 );
				}
				if ( $cat_term_id > 0 ) {
					$result = wp_update_term( $cat_term_id, 'abptf_organizer', [
						'name'        => $name,
						'slug'        => $slug,
						'description' => $description,
					] );
				} else {
					$result = wp_insert_term( $name, 'abptf_organizer', [
						'slug'        => $slug,
						'description' => $description,
					] );
				}
				$this->update_organizer();
				ob_start();
				if ( $abptf_post_id > 0 ) {
					$_organizer = ABPTF_Function::get_post_info( $abptf_post_id, 'abptf_organizer' );
					self::organizer_selection( $_organizer );
				} else {
					$this->organizer_list();
				}
				$html = ob_get_clean();
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'html' => $html, 'msg' => $result->get_error_message() ], 400 );
				}
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'organizer Saved Successfully !', 'abp-transportforge' ) ] );
			}

			public function delete_organizer(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$cat_id = isset( $_POST['cat_id'] ) ? absint( wp_unslash( $_POST['cat_id'] ) ) : 0;
				if ( ! $cat_id ) {
					wp_send_json_error( __( 'Invalid Organizer ID.', 'abp-transportforge' ), 400 );
				}
				$result = wp_delete_term( $cat_id, 'abptf_organizer' );
				$this->update_organizer();
				ob_start();
				$this->organizer_list();
				$html = ob_get_clean();
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'html' => $html, 'msg' => $result->get_error_message() ], 400 );
				}
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'organizer Delete Successfully !', 'abp-transportforge' ) ] );
			}

			public function add_organizer(): void {
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
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'organizer Form Loaded Successfully .....! ', 'abp-transportforge' ) ] );
			}

			public function form( $term_id = '' ): void {
				$name            = $slug = $des = '';
				$organizer_label = ABPTF_Function::organizer_label();
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
                        <span class="_abp_label"><?php echo esc_html( $organizer_label ) . ' ' . esc_html__( 'Name', 'abp-transportforge' ); ?><sup class="_color_required">*</sup></span>
                        <input class="_form_control" name="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'abp-transportforge' ); ?>" required/>
                    </label>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'org_name' ); ?>
                </div>
                <div class="setting_item _mar_b_xs">
                    <label class="_f_equal_f_wrap">
                        <span class="_abp_label"><?php echo esc_html( $organizer_label ) . ' ' . esc_html__( 'Slug (Optional)', 'abp-transportforge' ); ?></span>
                        <input class="_form_control" name="slug" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'Slug', 'abp-transportforge' ); ?>"/>
                    </label>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'org_slug' ); ?>
                </div>
                <div class="setting_item _mar_b_xs">
                    <label class="_f_equal_f_wrap">
                        <span class="_abp_label"><?php echo esc_html( $organizer_label ) . ' ' . esc_html__( 'Description(Optional)', 'abp-transportforge' ); ?></span>
                        <textarea class="_form_control" name="description" placeholder="<?php esc_attr_e( 'Description', 'abp-transportforge' ); ?>"><?php echo esc_html( $des ); ?></textarea>
                    </label>
                    <div class="_divider_xs"></div>
					<?php ABPTF_Layout::info_text( 'org_des' ); ?>
                </div>
                <div class="_divider_xs"></div>
                <button type="button" class="_btn_theme save_organizer"><span class="_mar_r_xxs">💾</span><?php echo ( ! empty( $term_id ) ? esc_html__( 'Update', 'abp-transportforge' ) : esc_html__( 'Save', 'abp-transportforge' ) ) . ' ' . esc_html( $organizer_label ); ?></button>
				<?php
			}

			public function organizer_list(): void {
				$all_organizer = ABPTF_Function::get_option( 'abptf_organizer' );
				$count         = 1;
				if ( ! empty( $all_organizer ) && is_array( $all_organizer ) && sizeof( $all_organizer ) > 0 ) { ?>
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'SI', 'abp-transportforge' ) ?></th>
                            <th class="_min_200"><?php esc_html_e( 'Organizer Title', 'abp-transportforge' ) ?></th>
                            <th><?php esc_html_e( 'ID', 'abp-transportforge' ) ?></th>
                            <th class="_min_150"><?php esc_html_e( 'Description', 'abp-transportforge' ) ?></th>
                            <th class="_w_250"><?php esc_html_e( 'Shortcode Post', 'abp-transportforge' ) ?></th>
                            <th class="_w_100"><?php esc_html_e( 'Action', 'abp-transportforge' ) ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php foreach ( $all_organizer as $term_id => $organizer ) {
							$name        = $organizer['name'] ?? '';
							$description = $organizer['description'] ?? '';
							?>
                            <tr>
                                <th><?php echo esc_html( $count ); ?>.</th>
                                <th class="_text_left"><a href="<?php echo esc_url( get_term_link( $term_id ) ); ?>" target="_blank" class="_abp_fs_h5_color_theme"><?php echo esc_html( $name ); ?></a></th>
                                <th><?php echo esc_html( $term_id ); ?></th>
                                <td><?php echo esc_html( $description ); ?></td>
                                <th><code> [abptf-post cat_id="<?php echo esc_attr( $term_id ); ?>"]</code></th>
                                <th>
                                    <div class="_group_content">
                                        <button type="button" class="_btn_light_yellow_mar_r_xxs" data-id="<?php echo esc_attr( $term_id ); ?>" data-target-popup="#abptf_global_popup" data-type="organizer" title="<?php echo esc_attr__( 'Edit : ', 'abp-transportforge' ) . ' ' . esc_attr( $name ); ?>">✍️</button>
                                        <button type="button" class="_btn_light_danger_xxs delete_organizer" data-cat_id="<?php echo esc_attr( $term_id ); ?>" title="<?php echo esc_attr__( 'Trash : ', 'abp-transportforge' ) . ' ' . esc_attr( $name ); ?>">❌</button>
                                    </div>
                                </th>
                            </tr>
							<?php $count ++;
						} ?>
                        </tbody>
                    </table>
				<?php } else {
					ABPTF_Layout::layout_warning_info( 'no_organizer' );
				}
			}

			public static function organizer_selection( $_organizer = '' ): void {
				$all_organizer = ABPTF_Function::get_option( 'abptf_organizer' );
				if ( ! empty( $all_organizer ) && is_array( $all_organizer ) && sizeof( $all_organizer ) > 0 ) { ?>
                    <label>
                        <select class="_form_control" name="abptf_organizer">
                            <option value="" selected><?php esc_html_e( 'Please Select Organizer', 'abp-transportforge' ); ?></option>
							<?php foreach ( $all_organizer as $key => $organizer ) { ?>
                                <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $key == $_organizer ? 'selected' : '' ); ?>><?php echo esc_html( $organizer['name'] ?? '' ); ?></option>
							<?php } ?>
                        </select>
                    </label>
				<?php } else { ?>
                    <p><?php echo esc_html( ABPTF_Layout::array_info( 'no_organizer' ) ); ?></p>
					<?php
				}
			}
		}
		new ABPTF_Organizer();
	}