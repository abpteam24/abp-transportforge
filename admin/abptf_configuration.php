<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Configuration' ) ) {
		class ABPTF_Configuration {
			public function __construct() {
				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_action( 'abptf_load_configuration', array( $this, 'load_configuration' ) );
				add_action( 'update_option_abptf_configuration', array( $this, 'permalink_flush' ) );
				add_filter( 'pre_update_option_abptf_configuration', array( $this, 'update_sanitize' ), 10, 3 );
				add_filter( 'pre_update_option_abptf_contact', array( $this, 'update_sanitize' ), 10, 3 );
				add_filter( 'pre_update_option_abptf_slider', array( $this, 'update_sanitize' ), 10, 3 );
				add_filter( 'pre_update_option_abptf_css_var', array( $this, 'update_sanitize' ), 10, 3 );
				add_filter( 'pre_update_option_abptf_mail', array( $this, 'update_sanitize' ), 10, 3 );
				add_filter( 'pre_update_option_abptf_pdf', array( $this, 'update_sanitize' ), 10, 3 );
				add_filter( 'pre_update_option_abptf_pdf_list', array( $this, 'update_sanitize' ), 10, 3 );
				add_filter( 'pre_update_option_abptf_csv', array( $this, 'update_sanitize' ), 10, 3 );
			}

			public function admin_init(): void {
				foreach ( $this->configuration_section() as $section ) {
					register_setting( $section['id'], $section['id'], array( $this, 'sanitize_options' ) );
				}
			}

			public function permalink_flush(): void {
				flush_rewrite_rules();
			}

			public function update_sanitize( $new, $old, $option ) {
				$all_fields  = $this->configuration_data();
				$field_infos = $all_fields[ $option ] ?? [];
				$remove_name = [ 'collapse_start', 'collapse_end' ];
				if ( sizeof( $field_infos ) > 0 && is_array( $new ) ) {
					foreach ( $field_infos as $field_info ) {
						$name = $field_info['name'] ?? '';
						if ( ! in_array( $name, $remove_name ) ) {
							$type = $field_info['type'] ?? '';
							if ( $type == 'wp_editor' ) {
								$new[ $name ] = sanitize_text_field( htmlentities( $new[ $name ] ) );
							} else {
								$new[ $name ] = sanitize_text_field( $new[ $name ] );
							}
						}
					}
				}

				return sizeof( $new ) > 0 ? $new : $old;
			}

			public function load_configuration(): void {
				$allowed_tabs = [];
				$all_sections = $this->configuration_section();
				foreach ( $all_sections as $tab ) {
					$tab_id = $tab['id'] ?? '';
					if ( ! empty( $tab_id ) ) {
						$allowed_tabs[] = preg_replace( '/^abptf_/', '', $tab_id );
					}
				}
				$active_tab = 'configuration';
				if ( isset( $_GET['_abptf_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_abptf_nonce'] ) ), 'abptf_url_action' ) ) {
					$active_tab = isset( $_GET['configuration'] ) ? sanitize_text_field( wp_unslash( $_GET['configuration'] ) ) : 'configuration';
				}
				if ( ! in_array( $active_tab, $allowed_tabs, true ) ) {
					$active_tab = 'configuration';
				}
				$all_fields = $this->configuration_data();
				$section_id = 'abptf_' . $active_tab;
				$fields     = $all_fields[ $section_id ] ?? [];
				$current_section=[];
				?>
                <div id="abptf_configuration">
                    <div class="_abp_panel_max_1200_mar_auto">
                        <div class="_panel_head">
                            <div class="_group_content_w_full">
								<?php foreach ( $all_sections as $tab ) {
									$tab_id = $tab['id'] ?? '';
									if ( ! empty( $tab_id ) ) {
										$key = preg_replace( '/^abptf_/', '', $tab_id );
                                        $current_section=$key==$active_tab?$tab:$current_section; ?>
                                        <a href="<?php echo esc_url( ABPTF_Function::build_url( 'configuration', [ 'configuration' => $key ] ) ); ?>" class="_btn_light_green_pale_xs  <?php echo esc_attr( $active_tab == $key ? 'abp_active' : '' ); ?>">
											<?php ABPTF_Layout::image_icon( $tab['icon'] ?? '' );
												echo esc_html( $tab['menu'] ?? '' ); ?>
                                        </a>
									<?php }
								} ?>
                            </div>
                        </div>
                        <div class="_panel_body <?php echo esc_attr( $section_id ); ?>">
							<?php if ( sizeof( $fields ) > 0 ) {
								$title           = $current_section['menu'] ?? ''; ?>
                                <h4 class="_abp"><?php echo esc_html(  $title . ' ' . __( 'Configuration', 'abp-transportforge' ) ); ?></h4>
                                <div class="_divider_xs"></div>
                                <form method="post" action="options.php">
                                    <div class="group_setting">
										<?php
											settings_fields( $section_id );
											$options = ABPTF_Function::get_option( $section_id );
											foreach ( $fields as $option ) {
												$on_off_key = $option['on_off_key'] ?? '';
												$display    = empty( $on_off_key ) || ABPTF_Function::on_off( $on_off_key );
												if ( $display ) {
													$name  = $option['name'] ?? '';
													$type  = $option['type'] ?? '';
													$label = $option['label'] ?? '';
													if ( $name && $type && $label ) {
														$value          = isset( $options[ $name ] ) && $options[ $name ] ? $options[ $name ] : ( $option['default'] ?? '' );
														$collapse       = $option['collapse_data'] ?? [];
														$add_class      = $option['class'] ?? '';
														$section_target = '';
														if ( ! empty( $collapse ) ) {
															$section        = $collapse['option'] ?? '';
															$section_key    = $collapse['key'] ?? '';
															$option_value   = $this->get_option_value( $section, $section_key );
															$add_class      = $option_value == 'on' ? $add_class . ' ' . 'abp_active' : $add_class;
															$section_target = $section . '[' . $section_key . ']';
														}
														$collapse_radio = $option['collapse_radio'] ?? [];
														$radio_pass     = 0;
														if ( ! empty( $collapse_radio ) ) {
															$span_class         = $option['class'] ?? '';
															$radio_section      = $collapse_radio['option'] ?? '';
															$radio_key          = $collapse_radio['key'] ?? '';
															$radio_value        = $collapse_radio['value'] ?? '';
															$radio_option_value = ABPTF_Function::get_options( $radio_section, $radio_key, $value );
															$radio_id           = $radio_section . '_' . $radio_key . '_' . $radio_value;
															if ( ! empty( $radio_id ) ) {
																$radio_pass ++;
																?><div class="<?php echo esc_attr( $radio_option_value == $radio_value ? $span_class . '  ' . 'abp_active' : $span_class ); ?>" data-close="<?php echo esc_attr( '#' . $radio_id ); ?>"><?php
															}
														}
														$option['collapse_target'] = $section_target;
														$option['class']           = $add_class;
														$option['section']         = $section_id;
														$option['key_name']        = $name;
														$option['name']            = $section_id . '[' . $name . ']';
														$option['value']           = $value;
														$this->$type( $option );
														if ( ! empty( $collapse_radio ) && $radio_pass > 0 ) {
															?></div><?php
														}
													}
												}
											}
										?>
                                    </div>
                                    <div class="_divider_xs"></div>
                                    <button type="submit" class="_btn_theme" value="submit"><span class="_mar_r_xxs">💾</span><?php echo esc_html( __( 'Save', 'abp-transportforge' ) . ' ' . $title . ' ' . __( 'Configuration', 'abp-transportforge' ) ); ?></button>
                                </form>
							<?php } ?>
                        </div>
                    </div>
                </div>
				<?php
			}

			public function get_option_value( $section, $section_key ) {
				$option_value = ABPTF_Function::get_options( $section, $section_key );
				if ( empty( $option_value ) ) {
					$all_fields = $this->configuration_data();
					$fields     = $all_fields[ $section ] ?? [];
					if ( sizeof( $fields ) > 0 ) {
						foreach ( $fields as $option ) {
							$name = $option['name'] ?? '';
							if ( ! empty( $name ) && $name == $section_key ) {
								$option_value = $option['default'] ?? '';
							}
						}
					}
				}

				return $option_value;
			}

			public function configuration_section(): array {
				$label         = ABPTF_Function::label();
				$brand_icon    = ABPTF_Function::icon();
				$configuration = apply_filters( 'abptf_configuration_after', array( array( 'id' => 'abptf_configuration', 'icon' => $brand_icon, 'menu' => $label ) ) );
				$contact       = apply_filters( 'abptf_contact_after', array(
					array( 'id' => 'abptf_on_off', 'icon' => 'fa-solid fa-toggle-on', 'menu' => __( 'ON/OFF', 'abp-transportforge' ) ),
					array( 'id' => 'abptf_slider', 'icon' => 'fas fa-photo-video', 'menu' => __( 'Slider', 'abp-transportforge' ) ),
					array( 'id' => 'abptf_contact', 'icon' => 'fas fa-id-card-alt', 'menu' => __( 'Contact Information', 'abp-transportforge' ) ),
					array( 'id' => 'abptf_css_var', 'icon' => 'fas fa-drafting-compass', 'menu' => __( 'CSS Property', 'abp-transportforge' ) ),
				) );

				return array_merge( $configuration, $contact );
			}

			public function configuration_data() {
				return apply_filters( 'abptf_configuration_data_filter', array(
					'abptf_configuration' => apply_filters( 'abptf_configuration_filter', array(
						array(
							'name'    => 'booked_status',
							'label'   => __( 'Booked Status', 'abp-transportforge' ),
							'desc'    => __( 'Select the specific order statuses that will automatically trigger inventory deduction or reserve a seat.', 'abp-transportforge' ),
							'class'   => 'full_width',
							'type'    => 'multi_check',
							'default' => 'wc-processing,wc-completed',
							'options' => in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ? wc_get_order_statuses() : []
						),
						array(
							'name'    => 'label',
							'label'   => __( 'Label', 'abp-transportforge' ),
							'desc'    => __( 'Customize the display label for the plugin menu item in the admin dashboard side navigation.', 'abp-transportforge' ),
							'type'    => 'text',
							'default' => __( 'TransportForge', 'abp-transportforge' ),
						),
						array(
							'name'    => 'slug',
							'label'   => __( 'Slug', 'abp-transportforge' ),
							'desc'    => sprintf(
							/* translators: %s: Permalinks settings page link layout */
								__( 'Define the primary URL slug for rentals. Important: After changing this, you must flush your permalinks by visiting %s and clicking Save Changes.', 'abp-transportforge' ),
								'<strong class="_abp_color_theme">' . __( 'Settings → Permalinks', 'abp-transportforge' ) . '</strong>'
							),
							'type'    => 'text',
							'default' => 'transport-forge'
						),
						array(
							'name'    => 'icon',
							'label'   => __( 'Dashboard Menu Icon', 'abp-transportforge' ),
							'desc'    => sprintf(
							/* translators: %s: Dashicons library link */
								__( 'Choose a custom admin menu icon. Please browse the %s, copy the desired icon class name, and paste it here.', 'abp-transportforge' ),
								'<a class="_abp" href="https://developer.wordpress.org/resource/dashicons/" target="_blank">' . __( 'WordPress Dashicons Library', 'abp-transportforge' ) . '</a>'
							),
							'type'    => 'text',
							'default' => 'dashicons-tickets'
						),
						array(
							'name'    => 'brand_icon',
							'label'   => __( 'TransportForge Icon', 'abp-transportforge' ),
							'desc'    => __( 'Select a global FontAwesome vector icon to act as the primary visual brand identity throughout the plugin panels.', 'abp-transportforge' ),
							'type'    => 'fontawesome',
							'default' => '🚐'
						),
						array(
							'name'    => 'category_label',
							'label'   => __( 'Category Label', 'abp-transportforge' ),
							'desc'    => __( 'If you wish to modify the category label, you can do so here. ', 'abp-transportforge' ),
							'type'    => 'text',
							'default' => ABPTF_Function::category_label(),
							'on_off_key' => 'category',
						),
						array(
							'name'    => 'cat_slug',
							'label'   => __( 'Category Slug', 'abp-transportforge' ),
							'desc'    => sprintf(
							/* translators: %s: Permalinks settings page link layout */
								__( 'Define the custom URL structure for category archives. Remember to update your rewrite rules under %s after any modifications.', 'abp-transportforge' ),
								'<strong class="_abp_color_theme">' . __( 'Settings → Permalinks', 'abp-transportforge' ) . '</strong>'
							),
							'type'    => 'text',
							'default' =>ABPTF_Function::category_slug(),
							'on_off_key' => 'category',
						),
						array(
							'name'    => 'organizer_label',
							'label'   => __( 'Organizer Label', 'abp-transportforge' ),
							'desc'    => __( 'If you wish to modify the Organizer label, you can do so here. ', 'abp-transportforge' ),
							'type'    => 'text',
							'default' => ABPTF_Function::organizer_label(),
							'on_off_key' => 'organizer',
						),
						array(
							'name'    => 'org_slug',
							'label'   => __( 'Organizer Slug', 'abp-transportforge' ),
							'desc'    => sprintf(
							/* translators: %s: Permalinks settings page link layout */
								__( 'Define the custom URL structure for Organizer archives. Remember to update your rewrite rules under %s after any modifications.', 'abp-transportforge' ),
								'<strong class="_abp_color_theme">' . __( 'Settings → Permalinks', 'abp-transportforge' ) . '</strong>'
							),
							'type'    => 'text',
							'default' => ABPTF_Function::organizer_slug(),
							'on_off_key' => 'organizer',
						),
						array(
							'name'    => 'brand_label',
							'label'   => __( 'Brand Label', 'abp-transportforge' ),
							'desc'    => __( 'If you wish to modify the Brand label, you can do so here. ', 'abp-transportforge' ),
							'type'    => 'text',
							'default' => ABPTF_Function::brand_label(),
							'on_off_key' => 'brand',
						),
						array(
							'name'    => 'brand_slug',
							'label'   => __( 'Brand Slug', 'abp-transportforge' ),
							'desc'    => sprintf(
							/* translators: %s: Permalinks settings page link layout */
								__( 'Define the custom URL structure for Brand archives. Remember to update your rewrite rules under %s after any modifications.', 'abp-transportforge' ),
								'<strong class="_abp_color_theme">' . __( 'Settings → Permalinks', 'abp-transportforge' ) . '</strong>'
							),
							'type'    => 'text',
							'default' => ABPTF_Function::brand_slug(),
							'on_off_key' => 'brand',
						),
						array(
							'name'    => 'location_label',
							'label'   => __( 'Stops Label', 'abp-transportforge' ),
							'desc'    => __( 'If you wish to modify the Stops label, you can do so here. ', 'abp-transportforge' ),
							'type'    => 'text',
							'default' => ABPTF_Function::location_label(),
						),
						array(
							'name'    => 'location_slug',
							'label'   => __( 'Stops Slug', 'abp-transportforge' ),
							'desc'    => sprintf(
							/* translators: %s: Permalinks settings page link layout */
								__( 'Define the custom URL structure for Stops archives. Remember to update your rewrite rules under %s after any modifications.', 'abp-transportforge' ),
								'<strong class="_abp_color_theme">' . __( 'Settings → Permalinks', 'abp-transportforge' ) . '</strong>'
							),
							'type'    => 'text',
							'default' => ABPTF_Function::location_slug(),
						),
					) ),
					'abptf_on_off'        => apply_filters( 'abptf_on_off_filter', array(
						array(
							'name'    => 'sku',
							'label'   => __( 'Post SKU', 'abp-transportforge' ),
							'desc'    => __( 'If you do not want to use the Post SKU ID feature, simply turn this switch OFF. Once disabled, the Post SKU field and related SKU options will be completely removed from the site, and no SKU ID will be required or generated for posts.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'post_icon',
							'label'   => __( 'Transport Icon', 'abp-transportforge' ),
							'desc'    => __( 'If you do not want to use Post Icon simply turn this switch OFF. This will completely disable the Post Icon across the entire website, including all related , settings, and functionality. You can re-enable it at any time by turning the switch back ON.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'sub_title',
							'label'   => __( 'Transport Sub Title', 'abp-transportforge' ),
							'desc'    => __( 'If you do not want to use the Post Sub Title , simply turn this switch OFF. Once disabled, the Post Sub Title field and related  options will be completely removed from the site.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'post_des',
							'label'   => __( 'Transport Description', 'abp-transportforge' ),
							'desc'    => __( 'If you do not want to use Post Description simply turn this switch OFF. This will completely disable the Post Description across the entire website, including all related , settings, and functionality. You can re-enable it at any time by turning the switch back ON.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'organizer',
							'label'   => ABPTF_Function::organizer_label(),
							'desc'    => __( 'If you do not want to use Organizer , simply turn this switch OFF. Once disabled, Organizer Title field and related Organizer options will be completely removed from the site.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'brand',
							'label'   => ABPTF_Function::brand_label(),
							'desc'    => __( 'If you do not want to use the Brand Feature, simply turn this switch OFF. Once disabled, the Brand Feature field and related Brand Feature options will be completely removed from the site.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'category',
							'label'   => ABPTF_Function::category_label(),
							'desc'    => __( 'If you do not want to use Category , simply turn this switch OFF. Once disabled, Category Title field and related Category options will be completely removed from the site.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'related',
							'label'   => __( 'Related Post', 'abp-transportforge' ),
							'desc'    => __( 'Turn this switch OFF to completely disable the Related Posts feature. Once disabled, Related Post items will no longer be displayed on any details page throughout the website. Turn it back ON at any time to show related content again.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'feature',
							'label'   => __( 'Features', 'abp-transportforge' ),
							'desc'    => __( 'If you do not want to use Features, simply turn this switch OFF. This will completely disable the Features across the entire website, including all related , settings, and functionality. You can re-enable it at any time by turning the switch back ON.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'ticket_type',
							'label'   => __( 'Multiple Ticket Type', 'abp-transportforge' ),
							'desc'    => __( 'If you do not want to use Multiple Ticket Type, simply turn this switch OFF. This will completely disable the Multiple Ticket Type feature across the entire website, including all related settings and functionality. You can re-enable it at any time by turning the switch back ON.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'sp',
							'label'   => __( 'Seat Plan', 'abp-transportforge' ),
							'desc'    => __( 'If you do not want to use the Seat Plan feature, simply turn this switch OFF. This will completely disable the Seat Plan feature for all transports across your website. If you only want to sell tickets without seat selection, you can use Ticket Types instead. You can re-enable the Seat Plan feature at any time by turning this switch back ON.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'pickup',
							'label'   => __( 'Multiple Pickup Point', 'abp-transportforge' ),
							'desc'    => __( 'If you do not want to use Multi Pickup, simply turn this switch OFF. This will completely disable the Multi Pickup feature across the entire website, including all related settings and functionality. You can re-enable it at any time by turning the switch back ON.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'drop',
							'label'   => __( 'Multiple Drop-Off Point', 'abp-transportforge' ),
							'desc'    => __( 'If you do not want to use Multi  Drop-off, simply turn this switch OFF. This will completely disable the Multi  Drop-off feature across the entire website, including all related settings and functionality. You can re-enable it at any time by turning the switch back ON.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'additional_info',
							'label'   => __( 'Additional services', 'abp-transportforge' ),
							'desc'    => __( 'If you do not want to use Additional services, simply turn this switch OFF. This will completely disable the Additional services feature across the entire website, including all related , settings, and functionality. You can re-enable it at any time by turning the switch back ON.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'tc',
							'label'   => __( 'Term & Conditions', 'abp-transportforge' ),
							'desc'    => __( 'If you do not want to use the Term & Conditions Feature, simply turn this switch OFF. Once disabled, the Term & Conditions Feature field and related Term & Conditions Feature options will be completely removed from the site.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
						array(
							'name'    => 'faq',
							'label'   => __( 'FAQ', 'abp-transportforge' ),
							'desc'    => __( 'If you do not want to use the FAQs Feature, simply turn this switch OFF. Once disabled, the FAQ Feature field and related FAQ Feature options will be completely removed from the site.', 'abp-transportforge' ),
							'type'    => 'button_switch',
							'default' => 'on',
						),
					) ),
					'abptf_contact'       => array(
						array(
							'name'        => 'name',
							'label'       => __( 'Company Name', 'abp-transportforge' ),
							'desc'        => __( 'Enter the official commercial or business name used for invoice branding.', 'abp-transportforge' ),
							'type'        => 'text',
							'default'     => '',
							'placeholder' => __( 'e.g., Transport Forge Ltd.', 'abp-transportforge' ),
						),
						array(
							'name'        => 'address',
							'label'       => __( 'Address', 'abp-transportforge' ),
							'desc'        => __( 'Provide the full corporate physical location or street address to appear on booking logs.', 'abp-transportforge' ),
							'type'        => 'textarea',
							'placeholder' => __( 'e.g., 450 Greene St, New York, NY 10003, USA', 'abp-transportforge' ),
						),
						array(
							'name'        => 'phone',
							'label'       => __( 'Contact Number', 'abp-transportforge' ),
							'desc'        => __( 'Specify the primary telephone or corporate helpline number for client inquiries.', 'abp-transportforge' ),
							'type'        => 'text',
							'default'     => '',
							'placeholder' => __( 'e.g., +1 (234) 567-8901', 'abp-transportforge' ),
						),
						array(
							'name'        => 'email',
							'label'       => __( 'E-Mail', 'abp-transportforge' ),
							'desc'        => __( 'Input the standard commercial email address reserved for direct customer support correspondence.', 'abp-transportforge' ),
							'type'        => 'text',
							'default'     => '',
							'placeholder' => __( 'support@example.com', 'abp-transportforge' ),
						),
					),
					'abptf_slider'        => array(
						array(
							'name'     => 'slider_style',
							'label'    => __( 'Slider Theme', 'abp-transportforge' ),
							'desc'     => __( 'Select the preferred frontend presentation layout style for display item multimedia asset sheets.', 'abp-transportforge' ),
							'collapse' => 'yes',
							'type'     => 'radio',
							'default'  => 'slider',
							'options'  => array(
								'slider'  => __( 'Carousel Slider', 'abp-transportforge' ),
								'gallery' => __( 'Gallery / Masonry', 'abp-transportforge' ),
							),
						),
						array(
							'name'           => 'image_column',
							'label'          => __( 'Images Per Row', 'abp-transportforge' ),
							'desc'           => __( 'Specify the total number of gallery thumbnails to render in a single horizontal grid line row layout.', 'abp-transportforge' ),
							'type'           => 'number',
							'default'        => '3',
							'min'            => '1',
							'max'            => '10',
							'validation'     => 'validation_number',
							'collapse_radio' => array( 'option' => 'abptf_slider', 'key' => 'slider_style', 'value' => 'gallery' ),
						),
						array(
							'name'           => 'indicator_visible',
							'label'          => __( 'Image Indicator?', 'abp-transportforge' ),
							'desc'           => sprintf(
							/* translators: %1$s: HTML markup for OFF layout switch, %2$s: HTML markup for ON layout switch, %3$s: Default status indicator layout */
								__( 'Manage pagination dot indicators on the slide viewport canvas. Toggle %1$s to hide or %2$s to display. (Default: %3$s)', 'abp-transportforge' ),
								ABPTF_Layout::off(),
								ABPTF_Layout::on(),
								ABPTF_Layout::on()
							),
							'type'           => 'button_switch',
							'default'        => 'on',
							'collapse_radio' => array( 'option' => 'abptf_slider', 'key' => 'slider_style', 'value' => 'slider' ),
						),
						array(
							'name'           => 'indication_position',
							'label'          => __( 'Indicator Position', 'abp-transportforge' ),
							'desc'           => __( 'Choose the geometric layout alignment position anchor for the slide tracking dot interface elements.', 'abp-transportforge' ),
							'type'           => 'radio',
							'default'        => 'bottom',
							'options'        => array(
								'top'    => __( 'Top', 'abp-transportforge' ),
								'right'  => __( 'Right', 'abp-transportforge' ),
								'bottom' => __( 'Bottom', 'abp-transportforge' ),
								'left'   => __( 'Left', 'abp-transportforge' )
							),
							'class'          => 'full_width',
							'collapse_radio' => array( 'option' => 'abptf_slider', 'key' => 'slider_style', 'value' => 'slider' ),
							'collapse_data'  => array( 'option' => 'abptf_slider', 'key' => 'indicator_visible' ),
						),
						array(
							'name'    => 'visible_popup',
							'label'   => __( 'Visible Popup?', 'abp-transportforge' ),
							'desc'    => sprintf(
							/* translators: %1$s: HTML markup for OFF layout switch, %2$s: HTML markup for ON layout switch, %3$s: Default status Visible Popup layout */
								__( 'Decide whether clicking on layout thumbnails triggers a full-screen image lightbox popup. Toggle %1$s to disable or %2$s to enable. (Default: %3$s)', 'abp-transportforge' ),
								ABPTF_Layout::off(),
								ABPTF_Layout::on(),
								ABPTF_Layout::on(),
							),
							'type'    => 'button_switch',
							'default' => 'on'
						),
						array(
							'name'          => 'popup_image_indicator',
							'label'         => __( 'Popup Image Indicator', 'abp-transportforge' ),
							'desc'          => sprintf(
							/* translators: %1$s: HTML markup for OFF Popup Image Indicator switch, %2$s: HTML markup for ON layout switch, %3$s: Default status Popup Image Indicator layout */
								__( 'Control navigation pagination tracking assets inside the lightbox modal container view. Toggle %1$s to hide or %2$s to reveal. (Default: %3$s)', 'abp-transportforge' ),
								ABPTF_Layout::off(),
								ABPTF_Layout::on(),
								ABPTF_Layout::on(),
							),
							'type'          => 'button_switch',
							'default'       => 'on',
							'collapse_data' => array( 'option' => 'abptf_slider', 'key' => 'visible_popup' ),
						),
					),
					'abptf_css_var'       => array(
						array(
							'name'    => 'color_theme',
							'label'   => __( 'Base Color', 'abp-transportforge' ),
							'desc'    => __( 'Pick the primary corporate core signature theme color for accents, links, and buttons.', 'abp-transportforge' ),
							'type'    => 'color',
							'default' => '#95951c'
						),
						array(
							'name'    => 'color_theme_alternate',
							'label'   => __( 'Alternate Color', 'abp-transportforge' ),
							'desc'    => __( 'Define a secondary tone reserved mainly for high-contrast foreground text typography layers sitting over a Base Color background layout block.', 'abp-transportforge' ),
							'type'    => 'color',
							'default' => '#fff'
						),
						array(
							'name'    => 'color_default',
							'label'   => __( 'Default Color', 'abp-transportforge' ),
							'desc'    => __( 'Establish the primary body text typography canvas hexadecimal hex color code.', 'abp-transportforge' ),
							'type'    => 'color',
							'default' => '#303030'
						),
						array(
							'name'       => 'br_default',
							'label'      => __( 'Default Border Radius', 'abp-transportforge' ),
							'desc'       => __( 'Specify a global UI layout component corner sharpness curve boundary rounding threshold value in raw pixel units.', 'abp-transportforge' ),
							'type'       => 'number',
							'default'    => '0',
							'validation' => 'validation_number'
						),
						array(
							'name'       => 'fs_default',
							'label'      => __( 'Default Font Size', 'abp-transportforge' ),
							'desc'       => __( 'Set the default baseline layout font scale size applied to normal paragraph blocks across UI views.', 'abp-transportforge' ),
							'type'       => 'number',
							'default'    => '12',
							'validation' => 'validation_number'
						),
						array(
							'name'       => 'fs_label',
							'label'      => __( 'Label Font Size', 'abp-transportforge' ),
							'desc'       => __( 'Configure the responsive text scale sizing applied strictly to forms, field labels, and identifier descriptions.', 'abp-transportforge' ),
							'type'       => 'number',
							'default'    => '14',
							'validation' => 'validation_number'
						),
						array(
							'name'       => 'fs_h1',
							'label'      => __( 'Font Size H1', 'abp-transportforge' ),
							'desc'       => __( 'Enter the typographical scale size target for the primary page-level H1 header assets.', 'abp-transportforge' ),
							'type'       => 'number',
							'default'    => '35',
							'validation' => 'validation_number'
						),
						array(
							'name'       => 'fs_h2',
							'label'      => __( 'Font Size H2', 'abp-transportforge' ),
							'desc'       => __( 'Enter the typographical scale size target for subsection level H2 header assets.', 'abp-transportforge' ),
							'type'       => 'number',
							'default'    => '30',
							'validation' => 'validation_number'
						),
						array(
							'name'       => 'fs_h3',
							'label'      => __( 'Font Size H3', 'abp-transportforge' ),
							'desc'       => __( 'Enter the typographical scale size target for inner panel modular level H3 header assets.', 'abp-transportforge' ),
							'type'       => 'number',
							'default'    => '25',
							'validation' => 'validation_number'
						),
						array(
							'name'       => 'fs_h4',
							'label'      => __( 'Font Size H4', 'abp-transportforge' ),
							'desc'       => __( 'Enter the typographical scale size target for contextual metadata tracking level H4 header assets.', 'abp-transportforge' ),
							'type'       => 'number',
							'default'    => '20',
							'validation' => 'validation_number'
						),
						array(
							'name'       => 'fs_h5',
							'label'      => __( 'Font Size H5', 'abp-transportforge' ),
							'desc'       => __( 'Enter the typographical scale size target for inner card grid elements level H5 header assets.', 'abp-transportforge' ),
							'type'       => 'number',
							'default'    => '17',
							'validation' => 'validation_number'
						),
						array(
							'name'       => 'fs_h6',
							'label'      => __( 'Font Size H6', 'abp-transportforge' ),
							'desc'       => __( 'Enter the lowest micro-metadata element typographical scale sizing target for nested H6 headers.', 'abp-transportforge' ),
							'type'       => 'number',
							'default'    => '15',
							'validation' => 'validation_number'
						),
						array(
							'name'       => 'fs_button',
							'label'      => __( 'Button Font Size', 'abp-transportforge' ),
							'desc'       => __( 'Specify the responsive scaling text size applied directly to interactive call-to-action control items.', 'abp-transportforge' ),
							'type'       => 'number',
							'default'    => '13',
							'validation' => 'validation_number'
						),
						array(
							'name'    => 'color_button',
							'label'   => __( 'Button Text Color', 'abp-transportforge' ),
							'desc'    => __( 'Pick a default foreground color shade for interactive typography labels inside button boundaries.', 'abp-transportforge' ),
							'type'    => 'color',
							'default' => '#FFF'
						),
						array(
							'name'    => 'bg_button',
							'label'   => __( 'Button Background Color', 'abp-transportforge' ),
							'desc'    => __( 'Set the default fill backdrop hexadecimal color value for transactional action trigger containers.', 'abp-transportforge' ),
							'type'    => 'color',
							'default' => '#222'
						),
						array(
							'name'    => 'color_warning',
							'label'   => __( 'Warning Color', 'abp-transportforge' ),
							'desc'    => __( 'Define the system default color block layout rule reserved for error outputs, alerts, or pending notices.', 'abp-transportforge' ),
							'type'    => 'color',
							'default' => '#E67C30'
						),
						array(
							'name'    => 'bg_section',
							'label'   => __( 'Section Background Color', 'abp-transportforge' ),
							'desc'    => __( 'Determine the fallback structural base background color applied to global layout wrapper segment panels.', 'abp-transportforge' ),
							'type'    => 'color',
							'default' => '#FAFCFE'
						),
					)
				) );
			}

			public static function description( $option ): void {
				$desc = $option['desc'] ?? '';
				if ( $desc ) { ?>
                    <div class="_divider_xs"></div>
					<?php
					ABPTF_Layout::info_text( '', $desc );
				}
			}

			public function text( $option ): void {
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <label class="_f_wrap_fj_between_fa_center">
                        <span class="_mar_r_xs"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
                        <input type="text" name="<?php echo esc_attr( $option['name'] ?? '' ); ?>" class="_form_control <?php echo esc_attr( $option['validation'] ?? '' ); ?>" value="<?php echo esc_attr( $option['value'] ?? '' ); ?>" placeholder="<?php echo esc_attr( $option['placeholder'] ?? '' ); ?>"/>
                    </label>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function url( $option ): void {
				$this->text( $option );
			}

			public function number( $option ): void {
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <label class="_f_wrap_fj_between_fa_center">
                        <span class="_mar_r_xs"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
                        <input type="number" name="<?php echo esc_attr( $option['name'] ?? '' ); ?>" class="_form_control  <?php echo esc_attr( $option['validation'] ?? '' ); ?>" value="<?php echo esc_attr( $option['value'] ?? '' ); ?>" placeholder="<?php echo esc_attr( $option['placeholder'] ?? '' ); ?>"
							<?php echo esc_attr( empty( $option['min'] ) ? '' : 'data-min=' . $option['min'] ); ?>
							<?php echo esc_attr( empty( $option['max'] ) ? '' : 'data-max=' . $option['max'] ); ?>
                        />
                    </label>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function password( $option ): void {
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <label class="_f_wrap_fj_between_fa_center">
                        <span class="_mar_r_xs"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
                        <input type="password" name="<?php echo esc_attr( $option['name'] ?? '' ); ?>" class="_form_control <?php echo esc_attr( $option['validation'] ?? '' ); ?>" value="<?php echo esc_attr( $option['value'] ?? '' ); ?>" placeholder="<?php echo esc_attr( $option['placeholder'] ?? '' ); ?>"/>
                    </label>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function file( $option ): void {
				$value          = $option['value'] ?? '';
				$name           = $option['name'] ?? '';
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <div class="_f_wrap_fj_between_fa_center">
                        <span class="_abp_label"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
                        <div><?php do_action( 'abptf_add_image', $name, $value ); ?></div>
                    </div>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function dashicons( $option ): void {
				$value          = $option['value'] ?? '';
				$name           = $option['name'] ?? '';
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <div class="_d_flex">
                        <div class="_f_wrap_fj_between_fa_center">
                            <span class="_fs_label"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
							<?php self::description( $option ); ?>
                        </div>
						<?php do_action( 'abptf_add_icon', $name, $value, 1 ); ?>
                    </div>
                </div>
				<?php
			}

			public function fontawesome( $option ): void {
				$value          = $option['value'] ?? '';
				$name           = $option['name'] ?? '';
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <div class="_f_wrap_fj_between_fa_center">
                        <span class="_fs_label_pad_r_xs"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
                        <div><?php do_action( 'abptf_add_icon', $name, $value ); ?></div>
                    </div>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function datepicker( $option ): void {
				$value          = $option['value'] ?? '';
				$name           = $option['name'] ?? '';
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <div class="_f_wrap_fj_between_fa_center">
                        <span class="_fs_label_pad_r_xs"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
						<?php ABPTF_Layout::input_date( $name, $value ); ?>
                    </div>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function textarea( $option ): void {
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <label class="_fj_between_fa_center">
                        <span class="_mar_r_xs"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
                        <textarea name="<?php echo esc_attr( $option['name'] ?? '' ); ?>" rows="5" cols="55" class="_form_control <?php echo esc_attr( $option['validation'] ?? '' ); ?>" placeholder="<?php echo esc_attr( $option['placeholder'] ?? '' ); ?>"><?php echo esc_html( $option['value'] ?? '' ); ?></textarea>
                    </label>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function select( $option ): void {
				$value          = $option['value'] ?? '';
				$option_data    = $option['options'] ?? [];
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <label class="_f_wrap_fj_between_fa_center">
                        <span class="_mar_r_xs"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
                        <select name="<?php echo esc_attr( $option['name'] ?? '' ); ?>" class="_form_control">
							<?php foreach ( $option_data as $key => $label ) { ?>
                                <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $key == $value ? 'selected' : '' ); ?>><?php echo esc_html( $label ); ?></option>
							<?php } ?>
                        </select>
                    </label>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function radio( $option ): void {
				$value          = $option['value'] ?? '';
				$name           = $option['name'] ?? '';
				$option_data    = $option['options'] ?? [];
				$collapse       = $option['collapse'] ?? '';
				$key_name       = $option['key_name'] ?? '';
				$section        = $option['section'] ?? '';
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <div class="_f_wrap_fj_between_fa_center">
                        <span class="_abp_label"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
                        <div class="custom_radio">
                            <input type="hidden" class="_form_control" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
							<?php foreach ( $option_data as $key => $data ) { ?>
                                <div class="radio_item">
                                    <button type="button" class="_btn_light_info_xs <?php echo esc_attr( $key == $value ? 'abp_active' : '' ); ?>"
										<?php if ( ! empty( $collapse ) ) { ?> data-close-target="#<?php echo esc_attr( $section . '_' . $key_name . '_' . $key ); ?>" <?php } ?>
                                            data-radio="<?php echo esc_attr( $key ); ?>"
                                            data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr( $key == $value ? 'far fa-check-circle' : 'far fa-circle' ); ?>"></span><?php echo esc_html( $data ); ?>
                                    </button>
                                </div>
							<?php } ?>
                        </div>
                    </div>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function checkbox( $option ): void {
				$value          = $option['value'] ?? '';
				$checked        = checked( $value, 'on', false );
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <div class="_f_wrap_fj_between_fa_center">
                        <span class="_abp_label"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
                        <label>
                            <input type="hidden" name="<?php echo esc_attr( $option['name'] ?? '' ); ?>" value="off"/>
                            <input type="checkbox" class="checkbox" name="<?php echo esc_attr( $option['name'] ?? '' ); ?>" value="on" <?php echo esc_attr( $checked ); ?> />
                        </label>
                    </div>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function button_switch( $option ): void {
				$value          = $option['value'] ?? '';
				$name           = $option['name'] ?? '';
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <label><?php ABPTF_Layout::switch_checkbox( $name, $value ); ?><span class="_mar_l_xs"><?php echo esc_html( $option['label'] ?? '' ); ?></span></label>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function multi_check( $option ): void {
				$value          = $option['value'] ?? '';
				$option_data    = $option['options'] ?? [];
				$value_array    = $value ? explode( ',', $value ) : [];
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <div class="_f_wrap_fj_between_fa_center">
                        <span class="_abp_label"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
                        <div class="custom_checkbox">
                            <input type="hidden" name="<?php echo esc_attr( $option['name'] ?? '' ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
							<?php foreach ( $option_data as $key => $label ) { ?>
                                <div class="checkbox_item">
                                    <button type="button" class="_btn_light_info_xs <?php echo esc_attr( in_array( $key, $value_array ) ? 'abp_active' : '' ); ?>" data-checked="<?php echo esc_attr( $key ); ?>" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr( in_array( $key, $value_array ) ? 'far fa-check-square' : 'far fa-square' ); ?>"></span><?php echo esc_html( $label ); ?>
                                    </button>
                                </div>
							<?php } ?>
                        </div>
                    </div>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function color( $option ): void {
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <div class="_f_wrap_fj_between_fa_center">
                        <span class="_abp_label"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
                        <label>
                            <input type="text" name="<?php echo esc_attr( $option['name'] ?? '' ); ?>" disabled class="_form_control abptf_color_picker" value="<?php echo esc_attr( $option['value'] ?? '' ); ?>" data-default-color="<?php echo esc_html( $option['default'] ?? '' ); ?>"/>
                        </label>
                    </div>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function wp_role( $option ): void {
				global $wp_roles;
				$value          = $option['value'] ?? '';
				$value_array    = $value ? explode( ',', $value ) : [];
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <div class="_f_wrap_fj_between_fa_center">
                        <span class="_abp_label"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
                        <div class="custom_checkbox">
                            <input type="hidden" name="<?php echo esc_attr( $option['name'] ?? '' ); ?>" value="<?php echo esc_attr( $option['value'] ?? '' ); ?>"/>
							<?php foreach ( $wp_roles->roles as $key => $label ) { ?>
                                <div class="checkbox_item">
                                    <button type="button" class="_btn_light_info_xs <?php echo esc_attr( in_array( $key, $value_array ) ? 'abp_active' : '' ); ?>" data-checked="<?php echo esc_attr( $key ); ?>" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr( in_array( $key, $value_array ) ? 'far fa-check-square' : 'far fa-square' ); ?>"></span><?php echo esc_html( $label['name'] ); ?>
                                    </button>
                                </div>
							<?php } ?>
                        </div>
                    </div>
                    <div class="_divider_xs"></div>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function wp_editor( $option ): void {
				$name           = $option['name'] ?? '';
				$value          = $option['value'] ?? '';
				$key_name       = $option['key_name'] ?? '';
				$section        = $option['section'] ?? '';
				$option_data    = $option['options'] ?? [];
				$value          = html_entity_decode( $value );
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <div class="_fd_column">
                        <span class="_fs_label_pad_r_xs_max_250"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
						<?php self::description( $option ); ?>
						<?php
							$editor_settings = array(
								'teeny'         => true,
								'textarea_name' => $name,
								'textarea_rows' => 15
							);
							if ( ! empty( $option_data ) ) {
								$editor_settings = array_merge( $editor_settings, $option_data );
							}
							wp_editor( $value, $section . '-' . $key_name, $editor_settings );
						?>
                    </div>
                </div>
				<?php
			}

			public function pages( $option ): void {
				$name           = $option['name'] ?? '';
				$value          = $option['value'] ?? '';
				$section_target = $option['collapse_target'] ?? [];
				?>
                <div class="setting_item <?php echo esc_attr( $option['class'] ?? '' ); ?>" <?php if ( ! empty( $section_target ) ) { ?> data-collapse="#<?php echo esc_attr( $section_target ); ?>"  <?php } ?>>
                    <label class="_f_wrap_fj_between_fa_center">
                        <span class="_mar_r_xs"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
						<?php
							$dropdown = wp_dropdown_pages( array(
								'selected'         => esc_attr( $value ),
								'name'             => esc_attr( $name ),
								'id'               => esc_attr( $name ),
								'class'            => '_form_control',
								'show_option_none' => esc_html__( 'Please Select', 'abp-transportforge' ),
								'echo'             => 0
							) );
							echo wp_kses(
								$dropdown,
								array(
									'select' => array( 'name' => true, 'id' => true, 'class' => true, 'required' => true, ),
									'option' => array( 'value' => true, 'selected' => true, ),
								)
							);
						?>
                    </label>
					<?php self::description( $option ); ?>
                </div>
				<?php
			}

			public function sanitize_options( $options ) {
				if ( ! $options ) {
					return $options;
				}
				foreach ( $options as $option_slug => $option_value ) {
					$sanitize_callback = $this->get_sanitize_callback( $option_slug );
					if ( $sanitize_callback ) {
						$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
					}
				}

				return $options;
			}

			public function get_sanitize_callback( $slug = '' ): callable|bool {
				if ( empty( $slug ) ) {
					return false;
				}
				foreach ( $this->configuration_data() as $options ) {
					foreach ( $options as $option ) {
						if ( $option['name'] != $slug ) {
							continue;
						}

						return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
					}
				}

				return false;
			}
		}
		new  ABPTF_Configuration();
	}