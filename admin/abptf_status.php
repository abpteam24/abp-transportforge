<?php
    if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.
    if (!class_exists('ABPTF_Status')) {
        class ABPTF_Status {
            public function __construct() {
                add_action('abptf_load_status', array($this, 'load_status'));
                //=============================//
                add_action('wp_ajax_abptf_install_and_active_wc', array($this, 'install_and_active_wc'));
                add_action('wp_ajax_abptf_active_wc', array($this, 'active_wc'));
                //=============================//
                add_action('wp_ajax_abptf_create_page', array($this, 'create_page'));
                //=============================//
                add_action('wp_ajax_abptf_import_dummy', array($this, 'import_dummy'));
            }
            public function load_status($abptf_info = []): void {
                ?>
                <div class="_abp_panel_max_1200_mar_auto abptf_status">
                    <div class="_panel_head">
                        <h3 class="_abp"><span class="_mar_r_xxs">🛡️</span> <?php esc_html_e('Status  & Information', 'abp-transportforge'); ?></h3>
                    </div>
                    <div class="_panel_body">
                        <?php
                            if (ABPTF_WC < 2) {
                                ABPTF_Layout::layout_warning_info_xs('must_wc');
                                if (ABPTF_WC == 1) { ?>
                                    <button class="_btn_navy_blue_mar_t active_wc" type="button"><span class="fas fa-tasks _mar_r_xxs"></span><?php esc_html_e('Active Now', 'abp-transportforge'); ?></button>
                                <?php } else { ?>
                                    <button class="_btn_navy_blue_mar_t _mar_t install_and_active_wc" type="button"><span class="fas fa-file-download _mar_r_xxs"></span><?php esc_html_e('Install & Active Now', 'abp-transportforge'); ?></button>
                                <?php }
                            }
                            $this->version();
                            $this->wordpress();
                            $this->php();
                            $this->wc();
                            if (ABPTF_WC > 1) {
                                do_action('abptf_add_tools');
                                $this->post_page($abptf_info);
                            }
                        ?>
                    </div>
                </div>
                <?php
            }
            public function version(): void {
                ?>
                <div class="_section_xs_mar_t_xs">
                    <div class="_fa_center_fj_between">
                        <h6 class="_abp"> <?php esc_html_e('TransportForge Version', 'abp-transportforge') ?> </h6>
                        <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php echo esc_html(ABPTF_VERSION); ?></button>
                    </div>
                </div>
                <?php
            }
            public function wordpress(): void {
                $version = get_bloginfo('version');
                ?>
                <div class="_section_xs">
                    <div class="_fa_center_fj_between">
                        <h6 class="_abp"> <?php esc_html_e('WordPress Version', 'abp-transportforge'); ?> </h6>
                        <?php if ($version > 5.5) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php echo esc_html($version); ?></button>
                        <?php } else { ?>
                            <button class="_btn_light_warning_xs_min_125" type="button"><span class="fas fa-exclamation-triangle _mar_r_xxs"></span><?php echo esc_html($version); ?></button>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
            public function php(): void {
                $version = phpversion();
                ?>
                <div class="_section_xs">
                    <div class="_fa_center_fj_between">
                        <h6 class="_abp"> <?php esc_html_e('Php Version', 'abp-transportforge'); ?> </h6>
                        <?php if ($version > 7.4) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php echo esc_html($version); ?></button>
                        <?php } else { ?>
                            <button class="_btn_light_warning_xs_min_125" type="button"><span class="fas fa-exclamation-triangle _mar_r_xxs"></span><?php echo esc_html($version); ?></button>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
            public function wc(): void {
                $title = ABPTF_WC == 2 ? __('Woocommerce Plugin', 'abp-transportforge') : __('Woocommerce need to install and active', 'abp-transportforge');
                $title = ABPTF_WC == 1 ? __('Woocommerce already installed but  not  activated', 'abp-transportforge') : $title;
                $name = get_option('woocommerce_email_from_name');
                $email = get_option('woocommerce_email_from_address');
                ?>
                <div class="_section_xs">
                    <div class="_fa_center_fj_between">
                        <h6 class="_abp"> <?php echo esc_html($title); ?></h6>
                        <?php if (ABPTF_WC == 2) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php esc_html_e('Activated', 'abp-transportforge'); ?></button>
                        <?php } elseif (ABPTF_WC == 1) { ?>
                            <button class="_btn_warning_xs_min_125 active_wc" type="button"><span class="fas fa-tasks _mar_r_xxs"></span><?php esc_html_e('Active Now', 'abp-transportforge'); ?></button>
                        <?php } else { ?>
                            <button class="_btn_warning_xs_min_125 install_and_active_wc" type="button"><span class="fas fa-file-download _mar_r_xxs"></span><?php esc_html_e('Install & Active Now', 'abp-transportforge'); ?></button>
                        <?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <?php if (ABPTF_WC == 2 && defined('WC_VERSION')) { ?>
                        <div class="_fa_center_fj_between">
                            <h6 class="_abp"><?php esc_html_e('Woocommerce Version', 'abp-transportforge'); ?></h6>
                            <?php if (version_compare(WC_VERSION, '8.0', '>')) { ?>
                                <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php echo esc_html(WC_VERSION); ?></button>
                            <?php } else { ?>
                                <button class="_btn_light_warning_xs_min_125" type="button"><span class="fas fa-exclamation-triangle _mar_r_xxs"></span><?php echo esc_html(WC_VERSION); ?></button>
                            <?php } ?>
                        </div>
                        <?php if (!empty($name)) { ?>
                            <div class="_divider_xs"></div>
                            <div class="_fa_center_fj_between">
                                <h6 class="_abp"><?php esc_html_e('Name', 'abp-transportforge'); ?></h6>
                                <button class="_btn_light_success_xs_min_125" type="button"><?php echo esc_html($name); ?></button>
                            </div>
                        <?php } ?>
                        <?php if (!empty($email)) { ?>
                            <div class="_divider_xs"></div>
                            <div class="_fa_center_fj_between">
                                <h6 class="_abp"><?php esc_html_e('Email Address', 'abp-transportforge'); ?></h6>
                                <button class="_btn_light_success_xs_min_125_text_inherit" type="button"><?php echo esc_html($email); ?></button>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="_color_warning"><span class="_mar_r_xxs  fas fa-exclamation-triangle"></span><?php echo esc_html(ABPTF_Status::array_info('must_wc')); ?></div>
                    <?php } ?>
                </div>
                <?php
            }
            public function post_page($abptf_info = []): void {
                $label = ABPTF_Function::label();
                $total = sizeof($abptf_info['post_ids'] ?? ABPTF_Query::get_post_id());
                ?>
                <div class="_section_xs">
                    <div class="_fa_center_fj_between">
                        <h6 class="_abp"><?php echo esc_html($label) . ' ' . esc_html__('Booking Page', 'abp-transportforge'); ?></h6>
                        <?php if (ABPTF_Function::get_page_by_slug('tf_booking')) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php esc_html_e('Activated', 'abp-transportforge'); ?></button>
                        <?php } else { ?>
                            <button class="_btn_warning_xs_min_125 create_page" data-page_type="tf_booking" type="button"><span class="fas fa-plus _mar_r_xxs"></span><?php esc_html_e('Add TransportForge Booking Page', 'abp-transportforge'); ?></button>
                        <?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fa_center_fj_between">
                        <h6 class="_abp"><?php echo esc_html($label) . ' ' . esc_html__('Post List Page', 'abp-transportforge'); ?></h6>
                        <?php if (ABPTF_Function::get_page_by_slug('tf_post')) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php esc_html_e('Activated', 'abp-transportforge'); ?></button>
                        <?php } else { ?>
                            <button class="_btn_warning_xs_min_125 create_page" data-page_type="tf_post" type="button"><span class="fas fa-plus _mar_r_xxs"></span><?php esc_html_e('Add TransportForge List Page', 'abp-transportforge'); ?></button>
                        <?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fa_center_fj_between">
                        <h6 class="_abp"><?php esc_html_e('Gallery Page', 'abp-transportforge'); ?></h6>
                        <?php if (ABPTF_Function::get_page_by_slug('tf_gallery')) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php esc_html_e('Activated', 'abp-transportforge'); ?></button>
                        <?php } else { ?>
                            <button class="_btn_warning_xs_min_125 create_page" data-page_type="tf_gallery" type="button"><span class="fas fa-plus _mar_r_xxs"></span><?php esc_html_e('Add Gallery Page', 'abp-transportforge'); ?></button>
                        <?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fa_center_fj_between">
                        <h6 class="_abp"> <?php esc_html_e('Number of Post', 'abp-transportforge'); ?> </h6>
                        <?php if ($total > 0) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php echo esc_html($total); ?></button>
                        <?php } else { ?>
                            <button class="_btn_light_warning_xs_min_125" type="button"><span class="fas fa-exclamation-triangle _mar_r_xxs"></span><?php esc_html_e('Can Not Find Post', 'abp-transportforge'); ?></button>
                        <?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fa_center_fj_between">
                        <h6 class="_abp"> <?php esc_html_e('Dummy Import', 'abp-transportforge'); ?> </h6>
                        <button class="<?php echo esc_attr($total > 0 ? '_btn_light_success_xs' : '_btn_warning_xs'); ?>_btn_theme_min_125 import_dummy" type="button"><span class="fas fa-plus _mar_r_xxs"></span><?php esc_html_e('Add New Dummy Post', 'abp-transportforge'); ?></button>
                    </div>
                </div>
                <?php
            }
            //=============================//
            public function install_and_active_wc(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
                include_once(ABSPATH . 'wp-admin/includes/file.php');
                include_once(ABSPATH . 'wp-admin/includes/misc.php');
                include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
                $plugin = 'woocommerce';
                $api = plugins_api('plugin_information', array(
                    'slug' => $plugin,
                    'fields' => array(
                        'short_description' => false,
                        'sections' => false,
                        'requires' => false,
                        'rating' => false,
                        'ratings' => false,
                        'downloaded' => false,
                        'last_updated' => false,
                        'added' => false,
                        'tags' => false,
                        'compatibility' => false,
                        'homepage' => false,
                        'donate_link' => false,
                    ),
                ));
                if (is_wp_error($api)) {
                    wp_send_json_error(['html' => '', 'msg' => $api->get_error_message()]);
                }
                $title = 'title';
                $url = 'url';
                $nonce = 'nonce';
                $woocommerce_plugin = new Plugin_Upgrader(new Plugin_Installer_Skin(compact('title', 'url', 'nonce', 'plugin', 'api')));
                $installed = $woocommerce_plugin->install($api->download_link);
                if (is_wp_error($installed)) {
                    wp_send_json_error(['msg' => $installed->get_error_message()]);
                }
                $activated = activate_plugin('woocommerce/woocommerce.php');
                if (is_wp_error($activated)) {
                    wp_send_json_error(['msg' => $activated->get_error_message()]);
                }
                wp_send_json_success(['msg' => esc_html__('WooCommerce installed and activated successfully!', 'abp-transportforge')]);
            }
            public function active_wc(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                if (defined('ABPTF_WC') && ABPTF_WC == 1) {
                    $activated = activate_plugin('woocommerce/woocommerce.php');
                    if (is_wp_error($activated)) {
                        wp_send_json_error(['msg' => $activated->get_error_message()]);
                    }
                    wp_send_json_success(['msg' => esc_html__('WooCommerce activated successfully!', 'abp-transportforge')]);
                }
                wp_send_json_error(['msg' => esc_html__('WooCommerce is either not installed or already active.', 'abp-transportforge')]);
            }
            public function create_page(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $page_type = $post_val('type');
                if (!empty($page_type)) {
                    if (!ABPTF_Function::get_page_by_slug($page_type)) {
                        $label = ABPTF_Function::label();
                        $short_code = '';
                        if ($page_type == 'tf_booking') {
                            $label = __('Booking', 'abp-transportforge');
                            $short_code = '[abptf-booking]';
                        }
                        if ($page_type == 'tf_post') {
                            $short_code = '[abptf-post]';
                        }
                        if ($page_type == 'tf_gallery') {
                            $label = __('Gallery', 'abp-transportforge');
                            $short_code = '[abptf-gallery]';
                        }
                        $page = array(
                            'post_type' => 'page',
                            'post_name' => $page_type,
                            'post_title' => $label,
                            'post_content' => $short_code,
                            'post_status' => 'publish',
                        );
                        $post_id = wp_insert_post($page);
                        if (is_wp_error($post_id) || 0 === $post_id) {
                            wp_send_json_error(['info_type' => 'warn', 'msg' => esc_html__('Failed to create page.', 'abp-transportforge')]);
                        }
                        flush_rewrite_rules();
                        /* translators: %s: Custom rental item type label (e.g., Vehicle, Equipment, Property) */
                        $translated_format = esc_html__('%s Page Created successfully.....', 'abp-transportforge');
                        $msg = sprintf($translated_format, $label);
                        wp_send_json_success(['info_type' => 'success', 'msg' => $msg]);
                    }
                    wp_send_json_error(['info_type' => 'warn', 'msg' => esc_html__('Page already exists.', 'abp-transportforge')]);
                } else {
                    wp_send_json_error(['info_type' => 'warn', 'msg' => esc_html__('Something Wrong...!', 'abp-transportforge')]);
                }
            }
            public function import_dummy(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $category = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';
                try {
                    $this->add_data($category);
                    flush_rewrite_rules();
                    wp_send_json_success([
                        'msg' => esc_html__('Dummy data imported successfully!', 'abp-transportforge')
                    ]);
                } catch (Exception) {
                    wp_send_json_error([
                        'msg' => esc_html__('An error occurred during data import.', 'abp-transportforge')
                    ]);
                }
            }
            public static function add_data($_category): void {
                global $wpdb;
                $table_name = $wpdb->prefix . 'abptf_property';
                $dummy_infos = self::dummy_data();
                if (isset($dummy_infos['taxonomy'])) {
                    foreach ($dummy_infos['taxonomy'] as $tax => $taxonomy_option) {
                        if (taxonomy_exists($tax)) {
                            $check_terms = get_terms(array('taxonomy' => $tax, 'hide_empty' => false));
                            if (is_string($check_terms) || sizeof($check_terms) == 0) {
                                foreach ($taxonomy_option as $taxonomy_data) {
                                    unset($term);
                                    $term = wp_insert_term($taxonomy_data['name'], $tax);
                                }
                            }
                        }
                    }
                    do_action('abptf_category_update');
                    do_action('abptf_location_update');
                    do_action('abptf_brand_update');
                }
                if (isset($dummy_infos['options'])) {
                    foreach ($dummy_infos['options'] as $option => $dummy_option) {
                        $option_data = get_option($option);
                        if (!$option_data || sizeof($option_data) == 0) {
                            update_option($option, $dummy_option);
                        }
                    }
                }
                if (isset($dummy_infos['custom_post'])) {
                    $abptf_location = ABPTF_Function::get_option('abptf_location');
                    $abptf_brand = ABPTF_Function::get_option('abptf_brand');
                    $abptf_category = ABPTF_Function::get_option('abptf_category');
                    $_category = !empty($_category) ? explode(',', $_category) : ['transport'];
                    foreach ($dummy_infos['custom_post'] as $custom_post => $dummy_post) {
                        foreach ($dummy_post as $cat_key => $cat_data) {
                            if (in_array($cat_key, $_category)) {
                                foreach ($cat_data as $dummy_data) {
                                    $args = array();
                                    if (isset($dummy_data['name'])) {
                                        $args['post_title'] = $dummy_data['name'];
                                    }
                                    $args['post_status'] = 'publish';
                                    $args['post_type'] = $custom_post;
                                    $post_id = wp_insert_post($args);
                                    $post_data = $dummy_data['post_data'] ?? [];
                                    if (!empty($post_data)) {
                                        $common_data = $post_data['common_data'] ?? [];
                                        foreach ($common_data as $meta_key => $data) {
                                            update_post_meta($post_id, $meta_key, $data);
                                        }
                                        $template = $post_data['abptf_template'] ?? 'grid';
                                        update_post_meta($post_id, 'abptf_template', $template);
                                        $rent_rule = $post_data['rent_rule'] ?? 'hourly';
                                        update_post_meta($post_id, 'rent_rule', $rent_rule);
                                        $post_cat = sizeof($abptf_category) > 0 ? array_key_first($abptf_category) : '';
                                        update_post_meta($post_id, 'abptf_category', $post_cat);
                                        $post_loc = '';
                                        if (sizeof($abptf_location) > 3) {
                                            $post_loc_key = array_rand($abptf_location, 3);
                                            $post_loc = implode(',', $post_loc_key);
                                        }
                                        update_post_meta($post_id, 'abptf_location', $post_loc);
                                        $properties = $post_data['property'] ?? [];
                                        if (!empty($properties)) {
                                            foreach ($properties as $property) {
                                                $abptf_brand = sizeof($abptf_brand) > 0 ? array_rand($abptf_brand) : '';
                                                $data = [
                                                    'post_id' => intval($post_id),
                                                    'sale_continue' => 'on',
                                                    'name' => sanitize_text_field($property['name'] ?? ''),
                                                    'brand' => sanitize_text_field($abptf_brand),
                                                    'category' => sanitize_text_field($post_cat),
                                                    'location' => sanitize_text_field($post_loc),
                                                    'features' => sanitize_text_field($property['features'] ?? ''),
                                                    'rent_rule' => sanitize_text_field($rent_rule),
                                                    'price_qty_info' => wp_json_encode($property['price_qty_info'] ?? []),
                                                    'gallery' => sanitize_text_field($property['gallery'] ?? ''),
                                                    'status' => !empty($post_id) ? get_post_status($post_id) : '',
                                                    'others' => wp_json_encode($property['others'] ?? []),
                                                    'updated_at' => current_time('Y-m-d H:i')
                                                ];
                                                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                                                $wpdb->insert($table_name, $data);
                                            }
                                        }
                                        ABPTF_Function::update_dates($post_id);
                                        ABPTF_Function::update_time_slot($post_id);
                                        ABPTF_Function::update_global_data($post_id);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            public static function dummy_data(): array {
                return [
                    'taxonomy' => [
                        'abptf_category' => self::static_category(),
                        'abptf_location' => self::static_location(),
                        'abptf_brand' => self::static_brand(),
                        'abptf_organizer' => self::static_organizer(),
                    ],
                    'options' => [
                        'abptf_ticket' => self::static_ticket(),
                        'abptf_feature' => self::static_feature(),
                        'abptf_additional' => self::static_additional(),
                        'abptf_forms' => self::static_form(),
                    ],
                    'custom_post' => [
                        'abptf_post' => [
                            'transport' => [
                                0 => [
                                    'name' => 'Greyhound Express Intercity Bus Service',
                                    'post_data' => [
                                        'common_data' => self::post_data(),
                                        'abptf_template' => 'default',
                                        'post_icon' => '🚌',
                                        'abptf_category' => 'Express',
                                        'abptf_organizer' => 'Global Transit Group',
                                        'abptf_brand' => 'Greyhound',
                                        'post_feature' => 'fec_id_36,fec_id_40,fec_id_51,fec_id_57,fec_id_64',
                                        'routing_infos' => [
                                            0 => ['stop' => 'New York City', 'type' => 'bp', 'time' => '0'],
                                            1 => ['stop' => 'Philadelphia', 'type' => 'bp', 'time' => '90'],
                                            2 => ['stop' => 'Baltimore', 'type' => 'bp', 'time' => '180'],
                                            3 => ['stop' => 'Washington, D.C.', 'type' => 'both', 'time' => '240'],
                                        ],
                                        'route_direction' => ['New York City', 'Philadelphia', 'Baltimore', 'Washington, D.C.'],
                                        'abptm_bp' => ['New York City', 'Philadelphia', 'Baltimore', 'Washington, D.C.'],
                                        'abptm_dp' => ['Washington, D.C.'],
                                        'price_infos' => [
                                            0 => ['bp' => 'New York City', 'dp' => 'Philadelphia', 'price' => '35'],
                                            1 => ['bp' => 'New York City', 'dp' => 'Baltimore', 'price' => '55'],
                                            2 => ['bp' => 'New York City', 'dp' => 'Washington, D.C.', 'price' => '75'],
                                            3 => ['bp' => 'Philadelphia', 'dp' => 'Baltimore', 'price' => '30'],
                                            4 => ['bp' => 'Philadelphia', 'dp' => 'Washington, D.C.', 'price' => '50'],
                                            5 => ['bp' => 'Baltimore', 'dp' => 'Washington, D.C.', 'price' => '25'],
                                        ],
                                    ]
                                ],
                                1 => [
                                    'name' => 'Megabus Affordable City-to-City Travel',
                                    'post_data' => [
                                        'common_data' => self::post_data(),
                                        'abptf_template' => 'default',
                                        'post_icon' => '🚍',
                                        'abptf_category' => 'Economy',
                                        'abptf_organizer' => 'Express Travel Network',
                                        'abptf_brand' => 'Megabus',
                                        'post_feature' => 'fec_id_36,fec_id_38,fec_id_45,fec_id_55,fec_id_60',
                                        'routing_infos' => [
                                            0 => ['stop' => 'Los Angeles', 'type' => 'bp', 'time' => '0'],
                                            1 => ['stop' => 'Bakersfield', 'type' => 'bp', 'time' => '120'],
                                            2 => ['stop' => 'Fresno', 'type' => 'both', 'time' => '240'],
                                            3 => ['stop' => 'San Jose', 'type' => 'dp', 'time' => '330'],
                                            4 => ['stop' => 'San Francisco', 'type' => 'dp', 'time' => '390'],
                                        ],
                                        'route_direction' => ['Los Angeles', 'Bakersfield', 'Fresno', 'San Jose', 'San Francisco'],
                                        'abptm_bp' => ['Los Angeles', 'Bakersfield', 'Fresno'],
                                        'abptm_dp' => ['Fresno', 'San Jose', 'San Francisco'],
                                        'price_infos' => [
                                            0 => ['bp' => 'Los Angeles', 'dp' => 'Fresno', 'price' => '40'],
                                            1 => ['bp' => 'Los Angeles', 'dp' => 'San Jose', 'price' => '55'],
                                            2 => ['bp' => 'Los Angeles', 'dp' => 'San Francisco', 'price' => '65'],
                                            3 => ['bp' => 'Bakersfield', 'dp' => 'Fresno', 'price' => '20'],
                                            4 => ['bp' => 'Bakersfield', 'dp' => 'San Jose', 'price' => '35'],
                                            5 => ['bp' => 'Bakersfield', 'dp' => 'San Francisco', 'price' => '45'],
                                            6 => ['bp' => 'Fresno', 'dp' => 'San Jose', 'price' => '25'],
                                            7 => ['bp' => 'Fresno', 'dp' => 'San Francisco', 'price' => '35'],
                                        ],
                                    ]
                                ],
                                2 => [
                                    'name' => 'FlixBus Modern Long Distance Transport',
                                    'post_data' => [
                                        'common_data' => self::post_data(),
                                        'abptf_template' => 'default',
                                        'post_icon' => '🌿',
                                        'abptf_category' => 'Business',
                                        'abptf_organizer' => 'Premium Coach Services',
                                        'abptf_brand' => 'FlixBus',
                                        'post_feature' => 'fec_id_36,fec_id_40,fec_id_52,fec_id_65,fec_id_66',
                                        'routing_infos' => [
                                            0 => ['stop' => 'Chicago', 'type' => 'bp', 'time' => '0'],
                                            1 => ['stop' => 'South Bend', 'type' => 'bp', 'time' => '90'],
                                            2 => ['stop' => 'Toledo', 'type' => 'both', 'time' => '210'],
                                            3 => ['stop' => 'Detroit', 'type' => 'dp', 'time' => '300'],
                                        ],
                                        'route_direction' => ['Chicago', 'South Bend', 'Toledo', 'Detroit'],
                                        'abptm_bp' => ['Chicago', 'South Bend', 'Toledo'],
                                        'abptm_dp' => ['Toledo', 'Detroit'],
                                        'price_infos' => [
                                            0 => ['bp' => 'Chicago', 'dp' => 'Toledo', 'price' => '45'],
                                            1 => ['bp' => 'Chicago', 'dp' => 'Detroit', 'price' => '60'],
                                            2 => ['bp' => 'South Bend', 'dp' => 'Toledo', 'price' => '25'],
                                            3 => ['bp' => 'South Bend', 'dp' => 'Detroit', 'price' => '40'],
                                            4 => ['bp' => 'Toledo', 'dp' => 'Detroit', 'price' => '20'],
                                        ],
                                    ]
                                ],
                                3 => [
                                    'name' => 'Peter Pan Premium Travel Experience',
                                    'post_data' => [
                                        'common_data' => self::post_data(),
                                        'abptf_template' => 'light',
                                        'post_icon' => '✨',
                                        'abptf_category' => 'Luxury',
                                        'abptf_organizer' => 'InterCity Transport',
                                        'abptf_brand' => 'Peter Pan Bus Lines',
                                        'post_feature' => 'fec_id_42,fec_id_64,fec_id_65,fec_id_69,fec_id_73',
                                        'routing_infos' => [
                                            0 => ['stop' => 'Boston', 'type' => 'bp', 'time' => '0'],
                                            1 => ['stop' => 'Providence', 'type' => 'bp', 'time' => '60'],
                                            2 => ['stop' => 'New Haven', 'type' => 'both', 'time' => '150'],
                                            3 => ['stop' => 'New York City', 'type' => 'dp', 'time' => '240'],
                                        ],
                                        'route_direction' => ['Boston', 'Providence', 'New Haven', 'New York City'],
                                        'abptm_bp' => ['Boston', 'Providence', 'New Haven'],
                                        'abptm_dp' => ['New Haven', 'New York City'],
                                        'price_infos' => [
                                            0 => ['bp' => 'Boston', 'dp' => 'New Haven', 'price' => '35'],
                                            1 => ['bp' => 'Boston', 'dp' => 'New York City', 'price' => '55'],
                                            2 => ['bp' => 'Providence', 'dp' => 'New Haven', 'price' => '20'],
                                            3 => ['bp' => 'Providence', 'dp' => 'New York City', 'price' => '40'],
                                            4 => ['bp' => 'New Haven', 'dp' => 'New York City', 'price' => '20'],
                                        ],
                                    ]
                                ],
                                4 => [
                                    'name' => 'Jefferson Lines Regional Bus Network',
                                    'post_data' => [
                                        'common_data' => self::post_data(),
                                        'abptf_template' => 'light',
                                        'post_icon' => '🚏',
                                        'abptf_category' => 'Local',
                                        'abptf_organizer' => 'Continental Bus Lines',
                                        'abptf_brand' => 'Jefferson Lines',
                                        'post_feature' => 'fec_id_40,fec_id_51,fec_id_55,fec_id_74,fec_id_78',
                                        'routing_infos' => [
                                            0 => ['stop' => 'Dallas', 'type' => 'bp', 'time' => '0'],
                                            1 => ['stop' => 'Corsicana', 'type' => 'bp', 'time' => '60'],
                                            2 => ['stop' => 'Huntsville', 'type' => 'both', 'time' => '180'],
                                            3 => ['stop' => 'Houston', 'type' => 'dp', 'time' => '240'],
                                        ],
                                        'route_direction' => ['Dallas', 'Corsicana', 'Huntsville', 'Houston'],
                                        'abptm_bp' => ['Dallas', 'Corsicana', 'Huntsville'],
                                        'abptm_dp' => ['Huntsville', 'Houston'],
                                        'price_infos' => [
                                            0 => ['bp' => 'Dallas', 'dp' => 'Huntsville', 'price' => '35'],
                                            1 => ['bp' => 'Dallas', 'dp' => 'Houston', 'price' => '50'],
                                            2 => ['bp' => 'Corsicana', 'dp' => 'Huntsville', 'price' => '20'],
                                            3 => ['bp' => 'Corsicana', 'dp' => 'Houston', 'price' => '35'],
                                            4 => ['bp' => 'Huntsville', 'dp' => 'Houston', 'price' => '15'],
                                        ],
                                    ]
                                ],
                                5 => [
                                    'name' => 'Trailways Comfortable Shuttle Service',
                                    'post_data' => [
                                        'common_data' => self::post_data(),
                                        'abptf_template' => 'light',
                                        'post_icon' => '🛣️',
                                        'abptf_category' => 'Shuttle',
                                        'abptf_organizer' => 'Smart Mobility Solutions',
                                        'abptf_brand' => 'Trailways',
                                        'post_feature' => 'fec_id_36,fec_id_45,fec_id_64,fec_id_71,fec_id_73',
                                        'routing_infos' => [
                                            0 => ['stop' => 'Seattle', 'type' => 'bp', 'time' => '0'],
                                            1 => ['stop' => 'Tacoma', 'type' => 'bp', 'time' => '45'],
                                            2 => ['stop' => 'Olympia', 'type' => 'both', 'time' => '90'],
                                            3 => ['stop' => 'Portland', 'type' => 'dp', 'time' => '180'],
                                        ],
                                        'route_direction' => ['Seattle', 'Tacoma', 'Olympia', 'Portland'],
                                        'abptm_bp' => ['Seattle', 'Tacoma', 'Olympia'],
                                        'abptm_dp' => ['Olympia', 'Portland'],
                                        'price_infos' => [
                                            0 => ['bp' => 'Seattle', 'dp' => 'Olympia', 'price' => '25'],
                                            1 => ['bp' => 'Seattle', 'dp' => 'Portland', 'price' => '45'],
                                            2 => ['bp' => 'Tacoma', 'dp' => 'Olympia', 'price' => '15'],
                                            3 => ['bp' => 'Tacoma', 'dp' => 'Portland', 'price' => '35'],
                                            4 => ['bp' => 'Olympia', 'dp' => 'Portland', 'price' => '20'],
                                        ],
                                    ]
                                ],
                            ],
                        ]
                    ]
                ];
            }
            public static function post_data(): array {
                return [
                    'sale_continue' => 'on',
                    'display_sku' => 'on',
                    'sub_title' => 'Travel comfortably with modern vehicles and professional drivers.',
                    'post_description' => 'Experience hassle-free transportation with well-maintained vehicles, affordable fares, and excellent customer support. Whether traveling for business or leisure, our services ensure comfort, punctuality, and convenience from departure to arrival.',
                    'post_sku' => wp_rand(100, 999),
                    'display_category' => 'on',
                    'active_global_dates' => 'on',
                    'display_additional_services' => 'on',
                    'active_global_additional' => 'on',
                    'display_client_form' => 'on',
                    'active_global_form' => 'on',
                    'display_faq' => 'on',
                    'active_global_faq' => 'on',
                    'display_tc' => 'on',
                    'active_global_tc' => 'on',
                    'abptf_sliders' => '10,20,30,40,50,100,60,70,80,90',
                    'dummy' => 'on',
                ];
            }
            public static function static_category(): array {
                return [
                    0 => ['name' => 'AC'],
                    1 => ['name' => 'Non AC'],
                    2 => ['name' => 'Economy'],
                    3 => ['name' => 'Business'],
                    4 => ['name' => 'VIP'],
                    5 => ['name' => 'Sleeper'],
                    6 => ['name' => 'Express'],
                    7 => ['name' => 'Local'],
                    8 => ['name' => 'Luxury'],
                    9 => ['name' => 'Shuttle'],
                ];
            }
            public static function static_brand(): array {
                return [
                    0 => ['name' => 'Mercedes-Benz'],
                    1 => ['name' => 'Volvo'],
                    2 => ['name' => 'Scania'],
                    3 => ['name' => 'MAN'],
                    4 => ['name' => 'IVECO'],
                    5 => ['name' => 'Setra'],
                    6 => ['name' => 'Yutong'],
                    7 => ['name' => 'King Long'],
                    8 => ['name' => 'Alexander Dennis'],
                    9 => ['name' => 'VDL Bus & Coach'],
                ];
            }
            public static function static_organizer(): array {
                return [
                    0 => ['name' => 'Global Transit Group'],
                    1 => ['name' => 'Express Travel Network'],
                    2 => ['name' => 'Premium Coach Services'],
                    3 => ['name' => 'InterCity Transport'],
                    4 => ['name' => 'Continental Bus Lines'],
                    5 => ['name' => 'Smart Mobility Solutions'],
                ];
            }
            public static function static_location(): array {
                return [
                    0 => ['name' => 'New York City'],
                    1 => ['name' => 'Los Angeles'],
                    2 => ['name' => 'Chicago'],
                    3 => ['name' => 'Houston'],
                    4 => ['name' => 'Phoenix'],
                    5 => ['name' => 'Philadelphia'],
                    6 => ['name' => 'San Antonio'],
                    7 => ['name' => 'San Diego'],
                    8 => ['name' => 'Dallas'],
                    9 => ['name' => 'San Jose'],
                    10 => ['name' => 'Austin'],
                    11 => ['name' => 'Jacksonville'],
                    12 => ['name' => 'Fort Worth'],
                    13 => ['name' => 'Columbus'],
                    14 => ['name' => 'Charlotte'],
                    15 => ['name' => 'Indianapolis'],
                    16 => ['name' => 'Seattle'],
                    17 => ['name' => 'Denver'],
                    18 => ['name' => 'Washington, D.C.'],
                    19 => ['name' => 'Boston'],
                    20 => ['name' => 'Nashville'],
                    21 => ['name' => 'Detroit'],
                    22 => ['name' => 'Portland'],
                    23 => ['name' => 'Las Vegas'],
                    24 => ['name' => 'Memphis'],
                    25 => ['name' => 'Baltimore'],
                    26 => ['name' => 'Milwaukee'],
                    27 => ['name' => 'Albuquerque'],
                    28 => ['name' => 'Tucson'],
                    29 => ['name' => 'Fresno'],
                    30 => ['name' => 'Sacramento'],
                    31 => ['name' => 'Kansas City'],
                    32 => ['name' => 'Atlanta'],
                    33 => ['name' => 'Miami'],
                    34 => ['name' => 'Orlando'],
                    35 => ['name' => 'Tampa'],
                    36 => ['name' => 'New Orleans'],
                    37 => ['name' => 'Cleveland'],
                    38 => ['name' => 'Pittsburgh'],
                    39 => ['name' => 'Cincinnati'],
                    40 => ['name' => 'Minneapolis'],
                    41 => ['name' => 'St. Louis'],
                    42 => ['name' => 'Salt Lake City'],
                    43 => ['name' => 'Raleigh'],
                    44 => ['name' => 'Richmond'],
                    45 => ['name' => 'Buffalo'],
                    46 => ['name' => 'Providence'],
                    47 => ['name' => 'Honolulu'],
                    48 => ['name' => 'Anchorage'],
                    49 => ['name' => 'Charleston'],
                    50 => ['name' => 'Tacoma'],
                    51 => ['name' => 'Olympia'],
                    52 => ['name' => 'Bakersfield'],
                    53 => ['name' => 'South Bend'],
                    54 => ['name' => 'Toledo'],
                    55 => ['name' => 'New Haven'],
                    56 => ['name' => 'Corsicana'],
                    57 => ['name' => 'Huntsville'],
                ];
            }
            public static function static_form($key = ''): array {
                $form['pass_name'] = ['type' => 'text', 'required' => 'on', 'label' => __('First Name', 'abp-transportforge')];
                $form['pass_name_2'] = ['type' => 'text', 'required' => 'on', 'label' => __('Last Name', 'abp-transportforge')];
                $form['pass_email'] = ['type' => 'email', 'required' => 'on', 'label' => __('E-Mail', 'abp-transportforge')];
                $form['pass_phone'] = ['type' => 'text', 'required' => 'on', 'label' => __('Phone', 'abp-transportforge')];
                $form['pass_gender'] = ['type' => 'select', 'required' => 'off', 'label' => __('Gender', 'abp-transportforge'), 'option' => 'male,female'];
                $form['pass_date'] = ['type' => 'date', 'required' => 'off', 'label' => __('Date of Birth', 'abp-transportforge')];
                $form['pass_address'] = ['type' => 'textarea', 'required' => 'off', 'label' => __('Address', 'abp-transportforge')];
                if (!is_string($key) && !is_int($key)) {
                    return $form;
                }
                if ($key === '') {
                    return $form;
                }
                return is_array($form[$key] ?? null) ? $form[$key] : [];
            }
            public static function static_additional(): array {
                return [
                    'additional_service_1' => ['icon' => 'fas fa-helmet-un', 'name' => 'Helmet', 'qty' => 50, 'max_qty' => 1, 'price' => 0, 'returnable' => 'yes', 'description' => '1x Safety Helmet per order. Keep your head protected at no extra cost. Your safety is our priority!',],
                    'additional_service_2' => ['icon' => 'fas fa-suitcase', 'name' => 'Storage', 'qty' => 30, 'max_qty' => 3, 'price' => 2.99, 'returnable' => 'no', 'description' => 'Optional baggage support is available as a paid service to help carry your essentials with ease.',],
                    'additional_service_3' => ['icon' => 'fas fa-user-tie', 'name' => 'Tie', 'qty' => 100, 'price' => 1.00, 'returnable' => 'no', 'description' => 'Multiple color available',],
                    'additional_service_4' => ['icon' => 'fas fa-shoe-prints', 'name' => 'Shoes', 'qty' => 100, 'price' => 1.00, 'returnable' => 'yes', 'description' => 'Multiple Size available',]
                ];
            }
            public static function static_ticket(): array {
                return [
                    1 => ['icon' => '🎫', 'label' => 'No Group'],
                    2 => ['icon' => '👑', 'label' => 'VIP'],
                    3 => ['icon' => '💺', 'label' => 'Regular'],
                    4 => ['icon' => '🪑', 'label' => 'Economy'],
                    5 => ['icon' => '💎', 'label' => 'Special'],
                    6 => ['icon' => '👤', 'label' => 'Adult'],
                    7 => ['icon' => '👩', 'label' => 'Female'],
                    8 => ['icon' => '💑', 'label' => 'Couple'],
                    9 => ['icon' => '🧸', 'label' => 'Child'],
                    10 => ['icon' => '💼', 'label' => 'Business'],
                ];
            }
            public static function static_feature(): array {
                return [
                    1 => ['icon' => '🚍', 'label' => 'Coach Bus'],
                    2 => ['icon' => '🚐', 'label' => 'Minibus'],
                    3 => ['icon' => '🏫', 'label' => 'School Bus'],
                    4 => ['icon' => '🏙️', 'label' => 'City Bus'],
                    5 => ['icon' => '🏨', 'label' => 'Shuttle Bus'],
                    6 => ['icon' => '🚇', 'label' => 'Metro'],
                    7 => ['icon' => '🚉', 'label' => 'Subway'],
                    8 => ['icon' => '🚝', 'label' => 'Monorail'],
                    9 => ['icon' => '🚈', 'label' => 'Light Rail'],
                    10 => ['icon' => '🚄', 'label' => 'High Speed Train'],
                    11 => ['icon' => '🚅', 'label' => 'Bullet Train'],
                    12 => ['icon' => '🚞', 'label' => 'Mountain Railway'],
                    13 => ['icon' => '🚠', 'label' => 'Cable Car'],
                    14 => ['icon' => '✈️', 'label' => 'Flight'],
                    15 => ['icon' => '🏠', 'label' => 'Domestic Flight'],
                    16 => ['icon' => '🌍', 'label' => 'International Flight'],
                    17 => ['icon' => '🛩️', 'label' => 'Air Charter'],
                    18 => ['icon' => '🚢', 'label' => 'Passenger Ship'],
                    19 => ['icon' => '🛳️', 'label' => 'Cruise Ship'],
                    20 => ['icon' => '🛥️', 'label' => 'Water Taxi'],
                    21 => ['icon' => '🚤', 'label' => 'Speed Boat'],
                    22 => ['icon' => '🛶', 'label' => 'River Boat'],
                    23 => ['icon' => '🚕', 'label' => 'Cab Service'],
                    24 => ['icon' => '🚘', 'label' => 'Ride Sharing'],
                    25 => ['icon' => '🚗', 'label' => 'Car Rental'],
                    26 => ['icon' => '🏍️', 'label' => 'Motorbike Taxi'],
                    27 => ['icon' => '🚲', 'label' => 'Bicycle Rental'],
                    28 => ['icon' => '🛺', 'label' => 'Auto Rickshaw'],
                    29 => ['icon' => '🚜', 'label' => 'Van Service'],
                    30 => ['icon' => '🎫', 'label' => 'Online Ticket Booking'],
                    31 => ['icon' => '🎟️', 'label' => 'E-Ticket'],
                    32 => ['icon' => '📱', 'label' => 'Mobile Ticket'],
                    33 => ['icon' => '🔳', 'label' => 'QR Code Ticket'],
                    34 => ['icon' => '💺', 'label' => 'Seat Reservation'],
                    35 => ['icon' => '🪑', 'label' => 'Seat Selection'],
                    36 => ['icon' => '⭐', 'label' => 'VIP Seat'],
                    37 => ['icon' => '🛏️', 'label' => 'Sleeper Seat'],
                    38 => ['icon' => '👨‍👩‍👧‍👦', 'label' => 'Family Seat'],
                    39 => ['icon' => '👥', 'label' => 'Group Booking'],
                    40 => ['icon' => '🔄', 'label' => 'Round Trip Booking'],
                    41 => ['icon' => '🌐', 'label' => 'Multi City Booking'],
                    42 => ['icon' => '⚡', 'label' => 'Instant Booking'],
                    43 => ['icon' => '❌', 'label' => 'Ticket Cancellation'],
                    44 => ['icon' => '💰', 'label' => 'Ticket Refund'],
                    45 => ['icon' => '📍', 'label' => 'Route Tracking'],
                    46 => ['icon' => '🛰️', 'label' => 'GPS Tracking'],
                    47 => ['icon' => '📡', 'label' => 'Live Location'],
                    48 => ['icon' => '🗺️', 'label' => 'Route Management'],
                    49 => ['icon' => '📅', 'label' => 'Trip Scheduling'],
                    50 => ['icon' => '🌙', 'label' => 'Night Service'],
                    51 => ['icon' => '🚀', 'label' => 'Express Service'],
                    52 => ['icon' => '🌎', 'label' => 'International Routes'],
                    53 => ['icon' => '💳', 'label' => 'Online Payment'],
                    54 => ['icon' => '💵', 'label' => 'Cash Payment'],
                    55 => ['icon' => '💳', 'label' => 'Card Payment'],
                    56 => ['icon' => '📲', 'label' => 'Mobile Banking'],
                    57 => ['icon' => '🧾', 'label' => 'Invoice Support'],
                    58 => ['icon' => '❄️', 'label' => 'Air Conditioning'],
                    59 => ['icon' => '📶', 'label' => 'WiFi'],
                    60 => ['icon' => '🔌', 'label' => 'USB Charging'],
                    61 => ['icon' => '🔋', 'label' => 'Power Outlet'],
                    62 => ['icon' => '🪑', 'label' => 'Reclining Seats'],
                    63 => ['icon' => '🎬', 'label' => 'Entertainment System'],
                    64 => ['icon' => '💡', 'label' => 'Reading Light'],
                    65 => ['icon' => '🥤', 'label' => 'Refreshments'],
                    66 => ['icon' => '🚻', 'label' => 'Toilet Facility'],
                    67 => ['icon' => '🧳', 'label' => 'Luggage Storage'],
                    68 => ['icon' => '♿', 'label' => 'Wheelchair Access'],
                    69 => ['icon' => '👴', 'label' => 'Priority Seating'],
                    70 => ['icon' => '👶', 'label' => 'Child Friendly'],
                    71 => ['icon' => '🐕', 'label' => 'Pet Friendly'],
                    72 => ['icon' => '🎥', 'label' => 'CCTV Security'],
                    73 => ['icon' => '🚪', 'label' => 'Emergency Exit'],
                    74 => ['icon' => '⛑️', 'label' => 'First Aid Kit'],
                ];
            }
            public static function array_info($key) {
                $current_date = current_time('Y-m-d H:i');
                $des = array(
                    'general_config' => __('Note: Configure the general settings for this transport here. If you do not want to use any specific feature, you can enable or disable it from Main Configuration → On/Off Sections. Disabling a feature will remove it from the entire site.', 'abp-transportforge'),
                    'sale_continue' => __('Note: This switch indicate Transport Ticket sale close/continue . You can  sale close/continue  by this switch. By default sale will be  continue', 'abp-transportforge'),
                    'abptf_template' => __('Note: Here You can change your details page template.', 'abp-transportforge'),
                    'post_sku' => __('Note: Here you can add an SKU for this post. You can also show or hide it on the frontend by turning the switch On or Off.', 'abp-transportforge'),
                    'post_icon' => __('Note: Set a custom icon or emoji for this post. The selected icon/emoji will be displayed alongside the post title wherever the title appears across the website, helping it stand out and improve visual recognition.', 'abp-transportforge'),
                    'sub_title' => __('Note: Add a Sub-title to enable the Post sub-tile. Leave this blank if you dont want to show any Sub-title information for this Post.', 'abp-transportforge'),
                    'post_description' => __('Note: Add short description about this Transport . Leave this blank if you dont want to show any  description for this Transport.', 'abp-transportforge'),
                    'display_organizer' => __('Note : This switch indicate Transport Organizer . You can also show or hide it on the frontend by turning the switch On or Off.', 'abp-transportforge'),
                    'display_brand' => __('Note : This switch indicate Transport Brand name . You can also show or hide it on the frontend by turning the switch On or Off.', 'abp-transportforge'),
                    'display_category' => __('Note : This switch indicate Transport Category . You can also show or hide it on the frontend by turning the switch On or Off.', 'abp-transportforge'),
                    'related_item' => __('Note: Select related items to display on the details page. Leave this option empty or disabled if you do not want to show related items.', 'abp-transportforge'),
                    'post_feature' => __('Note: If you want to add feature for this Transport, you can add Here. These feature will be show with this Transport . You may leave this section empty if you do not want to show frontend. ', 'abp-transportforge'),
                    'abptf_sliders' => __('Note: If you want to add an image gallery for this transport, you can upload images below.  You may leave this section empty if you do not want to show images. ', 'abp-transportforge'),
                    //=============================//
                    'route_config' => __('Note: Configure the transport route by selecting the required stops and their types. You can also add new stops while configuring the route. Boarding stops allow passengers to board only, Dropping stops allow passengers to get off only, and Both stops support both boarding and dropping. The first stop must always be Boarding, the last stop must always be Dropping, and the first stop time must be 0 minutes. All remaining stop times represent the travel time in minutes from the first stop and are applied according to your Transport Time Configuration. Enable Multiple Pickup/Drop-off Points to allow passengers to select from multiple pickup and drop-off locations. The available options are based on the configured route stops: Boarding stops become Pickup Points, Dropping stops become Drop-off Points, and Both stops are available for both. If the return journey uses the same transport, enable Same Transport Return to automatically use the same transport configuration for the return trip. ', 'abp-transportforge'),
                    //=============================//
                    //=============================//
                    'no_category' => __('No Category Found !', 'abp-transportforge'),
                    'cat_name' => __('Note: Please enter a category name — the field cannot be empty. ', 'abp-transportforge'),
                    'cat_slug' => __('Note: Category slug is optional — leave it blank to auto-generate from the name. ', 'abp-transportforge'),
                    'cat_des' => __('Note: Category description is optional — you can add details to better explain this category. ', 'abp-transportforge'),
                    //=============================//
                    'no_organizer' => __('No Organizer Found !', 'abp-transportforge'),
                    'org_name' => __('Note: Please enter a Organizer name — the field cannot be empty. ', 'abp-transportforge'),
                    'org_slug' => __('Note: Organizer slug is optional — leave it blank to auto-generate from the name. ', 'abp-transportforge'),
                    'org_des' => __('Note: Organizer description is optional — you can add details to better explain this Organizer. ', 'abp-transportforge'),
                    //=============================//
                    'no_location' => __('No Location Found ! ', 'abp-transportforge'),
                    'loc_name' => __('Note: Please enter a Location name — the field cannot be empty. ', 'abp-transportforge'),
                    'loc_slug' => __('Note: Location slug is optional — leave it blank to auto-generate from the name. ', 'abp-transportforge'),
                    'loc_des' => __('Note: Location Address is optional — you can add details to better explain this Location Full  Address. ', 'abp-transportforge'),
                    'display_pd' => __('You can add multiple pickup/drop-off  points for a single location. For each pickup/drop-off  point, set the travel time relative to the main location. Use a negative value (in minutes) if the pickup/drop-off  point is before the main location, or a positive value (in minutes) if it is after the main location. For example, use -15 for 15 minutes before the main location, or 20 for 20 minutes after it.', 'abp-transportforge'),
                    //=============================//
                    'no_brand' => __('No Brand Found ! ', 'abp-transportforge'),
                    'brand_name' => __('Note: Please enter a Brand name — the field cannot be empty. ', 'abp-transportforge'),
                    'brand_slug' => __('Note: Brand slug is optional — leave it blank to auto-generate from the name. ', 'abp-transportforge'),
                    'brand_des' => __('Note: Brand description  is optional — you can add details to better explain this Brand. ', 'abp-transportforge'),
                    //=============================//
                    'no_feature' => __('No Feature Found ! ', 'abp-transportforge'),
                    'feature_value' => __('Note: Please enter a Feature Value  — the field optional ', 'abp-transportforge'),
                    'feature_icon' => __('Note: You can add an icon, or emoji for this Feature(optional).', 'abp-transportforge'),
                    'feature_name' => __('Note: Please enter a Feature Name  — the field cannot be empty.', 'abp-transportforge'),
                    //=============================//
                    'date_format' => __('Note:  If you want to change the Date  Format, simply choose a different format. The default date is: ', 'abp-transportforge') . ' ' . date_i18n('D j M , Y', strtotime($current_date)),
                    'time_format' => __('Note : If you want to change the Time Format, simply choose a different format. The default Time Format is: ', 'abp-transportforge') . ' ' . date_i18n(get_option('time_format'), strtotime($current_date)),
                    'sale_close_before' => __('Note:  Enter the time in minutes to close ticket sales before the transport starts. If not specified, it will default to 0 (e.g. 1 hour equals 60 minutes). ', 'abp-transportforge'),
                    'advance_date_number' => __('Note: Kindly provide the number of days in advance for booking. By default, the advance booking period is set to 28 days.(optional) ', 'abp-transportforge'),
                    'active_global_dates' => __('Note: Keep this switch ON to apply the global date settings.Switch it OFF if you want to set special date rules for this transport.Date configuration options will open when turned OFF. ', 'abp-transportforge'),
                    'date_type' => __('Note: Please Select your Transport operational date type. Default operational date will be Periodic', 'abp-transportforge'),
                    'specific_dates' => __('Note: Please add your Transport operational Specific Date lists  .', 'abp-transportforge'),
                    'operation_time' => __('Note: Operation Time is required. If you do not specify any operation time, it will automatically be set to 12:00 AM (00:00). You can add multiple operation times for the same transport within a single day if needed. However, at least one operation time is required.', 'abp-transportforge'),
                    'periodic_start_date' => __('Note: Please add your Transport Launching Date otherwise it will be start today ', 'abp-transportforge'),
                    'periodic_end_date' => __('Note: Please add your Transport Terminate  Date otherwise it will be Continuously running periodically', 'abp-transportforge'),
                    'periodic_after' => __('Note: Please add your periodically after days. if  your Transport operation day everyday this will be one(1).(optional)', 'abp-transportforge'),
                    'date_rule' => __('Note: Enable this checkbox to configure special on/off date  settings. This option is optional. If you set a date/time in the special “On” date, that date will remain active even if it falls within an “Off” date range or on weekends.', 'abp-transportforge'),
                    'special_on_dates' => __('Note: If you add any date  in Special On Dates, it will always remain active—even if that date falls within an off date range or on weekends.', 'abp-transportforge'),
                    'weekend' => __('Note: Please select your weekend.Default all days open(optional)', 'abp-transportforge'),
                    'day_wise_time' => __('Note:Add Day-wise Time if your transport operates on different schedules throughout the week. You can assign multiple departure times for each day, and only the configured times for the selected day will be available to passengers. ', 'abp-transportforge'),
                    'specific_off_dates' => __('Note: please add your specific Operation off dates.(optional)', 'abp-transportforge'),
                    'date_wise_time' => __('Note: Set the transport operation time for specific dates. A date will only be saved if it has at least one operation time. If a date is not saved, the regular day-wise schedule or the default operation time will be applied. You can add multiple operation times for the same date.(optional)', 'abp-transportforge'),
                    'off_date_range' => __('Note: If you have off days between two dates which can add here.(optional)', 'abp-transportforge'),
                    'abptf_dates' => __('Note: Set a global date configuration for your Transport  that can be reused across all posts, with options to import and customize anytime.', 'abp-transportforge'),
                    //=============================//
                    'qty_reserve_min_max' => __('Note: Set the total stock quantity available for sale. This field is required to save the transport. You can also set reserve, minimum, and maximum quantity limits for customer bookings. Reserve quantity keeps specific items unavailable, minimum quantity defaults to 1, and maximum quantity will follow the available stock if left empty.', 'abp-transportforge'),
                    //=============================//
                    '_tax_class' => __('Note: If you want to add any new tax class , Please go to WooCommerce ->configuration->Tax Area', 'abp-transportforge'),
                    'enable_tax_msg' => __('Note: Your Woo-commerce Tax setting already disable. If you want to enable tax please enable woo-commerce tax.', 'abp-transportforge'),
                    //=============================//
                    'display_additional_services' => __('Note: If you want sale additional product/equipment with this  transport then active this button and add additional service. Additional item not depends on  operation time.', 'abp-transportforge'),
                    'additional_services' => __('Note: Add extra services for products/equipment with your transport—import or set per Post (also usable globally); stock applies per Post, empty quantity = unlimited, empty max qty = no limit, empty/Zero price = free.', 'abp-transportforge'),
                    'active_global_additional' => __('Note: Keep this switch ON to apply the global additional settings.Switch it OFF if you want to set special additional rules for this transport.additional configuration options will open when turned OFF. ', 'abp-transportforge'),
                    //=============================//
                    'client_form_option' => __('Use comma( , ) to separate option.', 'abp-transportforge'),
                    'display_client_form' => __('Note: If you want to get Client information then active this button and add form/import global form or use global form as a client form', 'abp-transportforge'),
                    'active_global_form' => __('Note: Keep this switch ON to apply the global Client Form settings.Switch it OFF if you want to set special  Client Form rules for this transport. Client Form configuration options will open when turned OFF. ', 'abp-transportforge'),
                    'global_client_forms' => __('Note: This is a flexibility global form system. Once you design the structure here, it serves as a global form. You can effortlessly import this form into any transport or use this setting at any transport,', 'abp-transportforge'),
                    //=============================//
                    'abptf_tc' => __('You can set all transport-related Term & Condition here and use them globally across all transport. You can also import these Term & Condition into any individual transport and customize them as needed.', 'abp-transportforge'),
                    'tc_item' => __('Use the editor to customize and design your Terms & Conditions as you prefer. The content and formatting you create here will be displayed the same way on the frontend.', 'abp-transportforge'),
                    'display_tc' => __('Use this switch to control whether the Term & Condition is displayed on the frontend. Turn the switch ON to show the Term & Condition, and OFF to hide it. By default, this option is set to ON.', 'abp-transportforge'),
                    'active_global_tc' => __('Enable this switch to apply the global Term & Condition to this post. If you want to add custom Term & Condition specifically for this post, turn the switch OFF and add your custom Term & Condition below.You can also use the Import button to bring in global Term & Condition, which you can then edit or delete based on your needs.', 'abp-transportforge'),
                    //=============================//
                    'abptf_faqs' => __('You can set all transport-related FAQs here and use them globally across all transports. You can also import these FAQs into any individual transport and customize them as needed.', 'abp-transportforge'),
                    'faq_item' => __('Both the Title and Description fields are required. If either field is left empty, this FAQ item will not be displayed on the frontend.', 'abp-transportforge'),
                    'display_faq' => __('Use this switch to control whether the FAQ is displayed on the frontend. Turn the switch ON to show the FAQ, and OFF to hide it. By default, this option is set to ON.', 'abp-transportforge'),
                    'active_global_faq' => __('Enable this switch to apply the global FAQ to this post. If you want to add custom FAQs specifically for this post, turn the switch OFF and add your custom FAQs below.You can also use the Import button to bring in global FAQs, which you can then edit or delete based on your needs.', 'abp-transportforge'),
                    //=============================//
                    'search_get_wrong_data_info' => __('Somethings went Wrong ! Please Try again', 'abp-transportforge'),
                    'sale_close_msg' => __('This transport sale close shortly. please try another transport.', 'abp-transportforge'),
                    'not_date' => __('No Dates Found !', 'abp-transportforge'),
                    'not_match' => __('No Results Found !', 'abp-transportforge'),
                    'not_found' => __('No Post Found !', 'abp-transportforge'),
                    'not_post_found' => __('No Post Found !', 'abp-transportforge'),
                    'not_transport_found' => __('No transport Found !', 'abp-transportforge'),
                    'no_sp' => __('No Seat Plan Found. Click Add New to create one.', 'abp-transportforge'),
                    'transport_not_available' => __('The transport is not available for the selected date and time. Please choose a different schedule.', 'abp-transportforge'),
                    //=============================//
                    'no_ticket_type' => __('No Ticket Type Found ! Please add Ticket Type to use Multiple Ticket Type', 'abp-transportforge'),
                    'ticket_image' => __('You can add an image, icon, or emoji for this ticket type. It will be used as the seat layout representation in the Seat Plan. Once configured here, all seats assigned to this ticket type will automatically use the same image, icon, or emoji.', 'abp-transportforge'),
                    'ticket_name' => __('Enter the transport ticket or seat name. This name will be displayed throughout the booking process and used to identify the ticket or seat type in the Seat Plan and related booking information.', 'abp-transportforge'),
                    'ticket_color' => __('Choose the color for this ticket or seat type. The selected color will be used for all tickets or seats assigned to this type in the Seat Plan, making them easy to identify.', 'abp-transportforge'),
                    'ticket_prefix' => __('Enter a prefix for the seat name, such as A, B, or VIP. This prefix will be automatically added before the seat number when generating seat names (e.g., A1, A2, VIP1).', 'abp-transportforge'),
                    //=============================//
                    'no_decor_item' => __('No Decor Item Found ! Please add Decor item to use Multiple Decor item', 'abp-transportforge'),
                    'decor_image' => __('You can add an image, icon, or emoji for this Decor item. It will be used as the Decor item layout representation in the Seat Plan. Once configured here, all seats assigned to this ticket type will automatically use the same image, icon, or emoji.', 'abp-transportforge'),
                    'decor_name' => __('Enter the Decor Item name. This item is used only for designing the Seat Plan layout and is not treated as a bookable seat. Its name will not be displayed anywhere during the booking process or on the Seat Plan. If needed, you can double-click the item in the Seat Plan editor to add custom text for display.', 'abp-transportforge'),
                    'decor_color' => __('Choose the background color for this Decor Item. This color is used only in the Seat Plan editor to visually represent the item and does not affect booking, pricing, or seat availability.', 'abp-transportforge'),
                    //=============================//
                    'must_wc' => __('TransportForge is entirely dependent on the WooCommerce plugin. Please install and activate the WooCommerce plugin otherwise the plugin will not work. Installing this tool may take some time', 'abp-transportforge'),
                    //=============================//
                    //=============================//
                    'sign_up_msg' => __('Please Login your account to Download/View ticket !', 'abp-transportforge'),
                    'no_permit_msg' => __('You are not permitted to Download/View this ticket !', 'abp-transportforge'),
                    'wrong_msg_id' => __('We see, this id are not valid !', 'abp-transportforge'),
                    'no_order_found' => __('Sorry ! We can not find any Order in your criteria.', 'abp-transportforge'),
                    //''          => __( '', 'abp-transportforge' ),
                );
                $des = apply_filters('abptf_info_array_filter', $des);
                return $des[$key] ?? '';
            }
        }
        new ABPTF_Status();
    }