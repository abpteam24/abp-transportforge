<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTF_Location')) {
        class ABPTF_Location {
            public function __construct() {
                add_action('abptf_global_location', [$this, 'global_location']);
                add_action('wp_ajax_abptf_add_tax_location', [$this, 'add_tax_location']);
                add_action('wp_ajax_abptf_save_tax_location', [$this, 'save_tax_location']);
                add_action('wp_ajax_abptf_delete_tax_location', [$this, 'delete_tax_location']);
                add_action('abptf_location_update', [$this, 'update_location']);
            }
            public function global_location(): void {
                $label = ABPTF_Function::location_label(); ?>
                <div class="_fj_between">
                    <h5 class="_abp"><span class="_mar_r_xs">📍</span><?php echo esc_html($label); ?></h5>
                    <?php ABPTF_Layout::button_global_popup('tax_location', __('Add New', 'abp-transportforge') . ' ' . $label); ?>
                </div>
                <div class="_divider_xs"></div>
                <div class="tax_location _ov_auto">
                    <?php $this->location_list(); ?>
                </div>
                <?php
            }
            public function add_tax_location(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $term_id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
                ob_start();
                $name = $slug = $des = '';
                $label = ABPTF_Function::location_label();
                $btn_label = __('Save', 'abp-transportforge') . ' ' . $label;
                $title = __('Add new ', 'abp-transportforge') . ' ' . $label;
                $location = [];
                if (!empty($term_id)) {
                    $term = get_term($term_id);
                    if (!empty($term)) {
                        $locations = ABPTF_Function::get_option('abptf_location');
                        $locations = is_array($locations) ? $locations : [];
                        $location = $locations[$term_id] ?? [];
                        $name = $term->name;
                        $slug = $term->slug;
                        $des = $term->description;
                        $btn_label = __('Update', 'abp-transportforge') . ' ' . $label . ' ' . $name;
                        $title = __('Edit ', 'abp-transportforge') . ' ' . $label . ' ' . $name;
                    }
                }
                $pick_infos = $location['pd_info'] ?? [];
                ?>
                <div class="abp_form">
                    <h5 class="_abp"><span class="_mar_r_xs">📍</span><?php echo esc_html($title); ?></h5>
                    <div class="_divider_xs"></div>
                    <input type="hidden" name="id" value="<?php echo esc_attr($term_id); ?>"/>
                    <div class="group_setting">
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php echo esc_html($label); ?><sup class="_color_required">*</sup></span>
                                <input class="_form_control" name="name" value="<?php echo esc_attr($name); ?>" placeholder="<?php esc_attr_e('Name', 'abp-transportforge'); ?>" required/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('loc_name'); ?>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php echo esc_html($label) . ' ' . esc_html_e('Slug (Optional)', 'abp-transportforge'); ?></span>
                                <input class="_form_control" name="slug" value="<?php echo esc_attr($slug); ?>" placeholder="<?php esc_attr_e('Slug', 'abp-transportforge'); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('loc_slug'); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php esc_html_e('Full Address(optional)', 'abp-transportforge'); ?></span>
                                <textarea class="_form_control" name="description" placeholder="<?php esc_attr_e('Address', 'abp-transportforge'); ?>"><?php echo esc_html($des); ?></textarea>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('loc_des'); ?>
                        </div>
                        <?php if (ABPTF_Function::on_off('pickup')) { ?>
                            <div class="setting_item configuration_content full_width">
                                <div class="_fj_between_fa_center">
                                    <span class="_abp_label"><?php esc_html_e('Multiple Pickup/drop-off Point', 'abp-transportforge'); ?></span>
                                    <?php ABPTF_Layout::button_add(__('Add New Pickup/drop-off Point', 'abp-transportforge')); ?>
                                </div>
                                <div class="_divider_xs"></div>
                                <div class="insertable_area sortable_area _gap_xs_f_wrap">
                                    <?php if (!empty($pick_infos)) {
                                        foreach ($pick_infos as $key => $pick_info) {
                                            self::pickup_form($pick_info, $key);
                                        }
                                    } ?>
                                    <div class="abp_hidden">
                                        <div class="hidden_content">
                                            <?php self::pickup_form(); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="_divider_xs"></div>
                                <?php ABPTF_Layout::info_text('display_pd'); ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <?php ABPTF_Layout::button_global_save('tax_location', $btn_label); ?>
                </div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => $label . ' ' . __('Form Loaded Successfully .....! ', 'abp-transportforge')]);
            }
            public function save_tax_location(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $post_int = fn($key, $default = 0) => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $post_slug = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_title(wp_unslash($_POST[$key])) : $default;
                $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                $tax_id = $post_int('id');
                $name = $post_val('name');
                $slug = $post_slug('slug');
                $description = $post_val('description');
                $post_id = $post_int('post_id');
                $pick_ids = $post_array('pick_id');
                $pick_names = $post_array('pick_name');
                $pick_times = $post_array('pick_time');
                if (empty($name)) {
                    ob_start();
                    $html = '';
                    if (empty($post_id) || $post_id <= 0) {
                        ob_start();
                        $this->location_list();
                        $html = ob_get_clean();
                    }
                    wp_send_json_error(['html' => $html, 'msg' => ABPTF_Function::location_label() . ' ' . __('Name cannot be blank!', 'abp-transportforge'), 'type' => 'warn']);
                }
                if ($tax_id > 0) {
                    $result = wp_update_term($tax_id, 'abptf_location', [
                        'name' => $name,
                        'slug' => $slug,
                        'description' => $description,
                    ]);
                } else {
                    $result = wp_insert_term($name, 'abptf_location', [
                        'slug' => $slug,
                        'description' => $description,
                    ]);
                }
                if (is_wp_error($result)) {
                    wp_send_json_error(['html' => '', 'msg' => $result->get_error_message()]);
                }
                $term_id = absint($result['term_id'] ?? 0);
                if ($term_id <= 0) {
                    wp_send_json_error(['html' => '', 'msg' => __('Failed to resolve location context.', 'abp-transportforge'), 'type' => 'warn']);
                }
                $pickup_info = [];
                $number = 0;
                if (!empty($pick_names)) {
                    foreach ($pick_names as $key => $pick) {
                        if (!empty($pick)) {
                            $pick_id = isset($pick_ids[$key]) && $pick_ids[$key] !== '' ? (int)$pick_ids[$key] : '';
                            if ($pick_id === '') {
                                $pick_id = $number;
                                while (isset($pickup_info[$pick_id])) {
                                    $number++;
                                    $pick_id = $number;
                                }
                            }
                            $pickup_info[$pick_id]['name'] = $pick;
                            $pickup_info[$pick_id]['time'] = $pick_times[$key] ?? '';
                        }
                    }
                }
                $this->update_location($pickup_info, $term_id);
                ob_start();
                $html = '';
                if (empty($post_id) || $post_id <= 0) {
                    ob_start();
                    $this->location_list();
                    $html = ob_get_clean();
                }
                wp_send_json_success([
                    'html' => $html, 'type' => 'success', 'msg' => ABPTF_Function::location_label() . ' ' . __('Saved Successfully !', 'abp-transportforge'),
                    'js' => (!empty($post_id) && $post_id > 0 ? self::location_info_js($post_id) : ''),
                ]);
            }
            public function delete_tax_location(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $tax_id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : '';
                if (empty($tax_id) || !is_numeric($tax_id)) {
                    ob_start();
                    $this->location_list();
                    $html = ob_get_clean();
                    wp_send_json_error(['html' => $html, 'msg' => ABPTF_Function::location_label() . ' ' . __('id Invalid...!', 'abp-transportforge'), 'type' => 'warn']);
                }
                $result = wp_delete_term($tax_id, 'abptf_location');
                $this->update_location();
                ob_start();
                $this->location_list();
                $html = ob_get_clean();
                if (is_wp_error($result)) {
                    wp_send_json_error(['html' => $html, 'msg' => $result->get_error_message(), 'type' => 'warn']);
                }
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => ABPTF_Function::location_label() . ' ' . __('Deleted Successfully !', 'abp-transportforge')]);
            }
            public function update_location($pickup_info = [], $id = ''): void {
                $taxonomies = ABPTF_Function::get_taxonomy('abptf_location');
                $taxonomies = is_array($taxonomies) ? $taxonomies : [];
                $location = [];
                $old_location = ABPTF_Function::get_option('abptf_location');
                $old_location = is_array($old_location) ? $old_location : [];
                if (count($taxonomies) > 0) {
                    foreach ($taxonomies as $taxonomy) {
                        $term_id = $taxonomy->term_id;
                        $location[$term_id]['name'] = $taxonomy->name;
                        $location[$term_id]['description'] = $taxonomy->description;
                        $location[$term_id]['slug'] = $taxonomy->slug;
                        if (!empty($id) && (int)$id === (int)$term_id) {
                            $new_location = $pickup_info;
                        } else {
                            $new_location = $old_location[$term_id] ?? [];
                        }
                        $location[$term_id]['pd_info'] = $new_location;
                    }
                }
                ksort($location);
                update_option('abptf_location', $location);
            }
            public function location_list(): void {
                $options = ABPTF_Function::get_option('abptf_location');
                $options = is_array($options) ? $options : [];
                $count = 1;
                if (count($options) > 0) { ?>
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('SI', 'abp-transportforge') ?></th>
                            <th><?php esc_html_e('ID', 'abp-transportforge') ?></th>
                            <th class="_min_150"><?php echo esc_html(ABPTF_Function::location_label()); ?></th>
                            <th><?php esc_html_e('Pickup/Drop-off Point', 'abp-transportforge') ?></th>
                            <th><?php esc_html_e('Full Address', 'abp-transportforge') ?></th>
                            <th><?php esc_html_e('Shortcode Post', 'abp-transportforge') ?></th>
                            <th><?php esc_html_e('Action', 'abp-transportforge') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($options as $term_id => $option) {
                            $name = $option['name'] ?? ''; ?>
                            <tr>
                                <th><?php echo esc_html($count); ?>.</th>
                                <th><?php echo esc_html($term_id); ?></th>
                                <th class="_text_left"><a href="<?php echo esc_url(get_term_link((int)$term_id)); ?>" target="_blank" class="_abp_fs_h5_color_theme"><?php echo esc_html($name); ?></a></th>
                                <th>
                                    <?php
                                        $drop_info = $option['pd_info'] ?? [];
                                        if (!empty($drop_info)) {
                                            foreach ($drop_info as $drop) {
                                                if (!empty($drop)) { ?>
                                                    <div class="_section_xxs">
                                                        <?php echo esc_html($drop['name']);
                                                            if (!empty($drop['time'])) {
                                                                echo esc_html(' ( ' . $drop['time'] . ' Min)');
                                                            } ?>
                                                    </div>
                                                <?php }
                                            }
                                        } ?>
                                </th>
                                <td><?php echo esc_html($option['description'] ?? ''); ?></td>
                                <th class="_text_nowrap"><code> [abptf-post loc_id="<?php echo esc_attr($term_id); ?>"]</code></th>
                                <td>
                                    <div class="_fj_center">
                                        <div class="_group_content">
                                            <button type="button" class="_btn_light_yellow_xxs" onclick="abptf_popup_open_global('tax_location','<?php echo esc_attr($term_id); ?>')" title="<?php echo esc_attr__('Edit : ', 'abp-transportforge') . ' ' . esc_attr($name); ?>">✍️</button>
                                            <button type="button" class="_btn_light_danger_xxs" onclick="abptf_delete_global('tax_location','<?php echo esc_attr($term_id); ?>')" title="<?php echo esc_attr__('Trash : ', 'abp-transportforge') . ' ' . esc_attr($name); ?>">❌</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php $count++;
                        } ?>
                        </tbody>
                    </table>
                <?php } else {
                    ABPTF_Layout::layout_warning_info('no_location');
                }
            }
            public static function pickup_form($point = [], $key = ''): void {
                ?>
                <div class="delete_area _group_content">
                    <label>
                        <input type="hidden" name="pick_id[]" value="<?php echo esc_attr($key); ?>"/>
                        <input type="text" class="_form_control validation_name" name="pick_name[]" placeholder="<?php esc_attr_e('EX: Boston', 'abp-transportforge'); ?>" value="<?php echo esc_attr($point['name'] ?? ''); ?>" required/>
                    </label>
                    <label>
                        <input type="text" class="_form_control validation_time_number" name="pick_time[]" placeholder="<?php esc_attr_e('EX: +30/-30', 'abp-transportforge'); ?>" value="<?php echo esc_attr($point['time'] ?? ''); ?>" required/>
                    </label>
                    <?php ABPTF_Layout::button_delete_sort(); ?>
                </div>
                <?php
            }
            public static function location_info_js($_post_id = ''): array {
                $all_info = [];
                if (!empty($_post_id) && $_post_id > 0) {
                    $all_location = ABPTF_Function::get_option('abptf_location');
                    if (!empty($all_location)) {
                        foreach ($all_location as $key => $location) {
                            $name = $location['name'] ?? '';
                            if ($name) {
                                $all_info[] = ['id' => $key, 'label' => $name];
                            }
                        }
                    }
                }
                return $all_info;
            }
        }
        new ABPTF_Location();
    }