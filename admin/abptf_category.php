<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTF_Category')) {
        class ABPTF_Category {
            public function __construct() {
                add_action('abptf_global_category', array($this, 'global_category'));
                add_action('wp_ajax_abptf_add_tax_category', array($this, 'add_tax_category'));
                add_action('wp_ajax_abptf_save_tax_category', array($this, 'save_tax_category'));
                add_action('wp_ajax_abptf_delete_tax_category', array($this, 'delete_tax_category'));
                add_action('abptf_category_update', array($this, 'update_category'));
            }
            public function global_category(): void {
                if (ABPTF_Function::on_off('category')) {
                    $label = ABPTF_Function::category_label(); ?>
                    <div class="_fj_between">
                        <h5 class="_abp"><span class="_mar_r_xs">🏘️</span><?php echo esc_html($label); ?></h5>
                        <?php ABPTF_Layout::button_global_popup('tax_category', __('Add New', 'abp-transportforge') . ' ' . $label); ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="tax_category _ov_auto">
                        <?php $this->category_list(); ?>
                    </div>
                    <?php
                }
            }
            public function add_tax_category(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $term_id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
                ob_start();
                $name = $slug = $des = '';
                $label = ABPTF_Function::category_label();
                $btn_label = __('Save', 'abp-transportforge') . ' ' . $label;
                $title = __('Add new ', 'abp-transportforge') . ' ' . $label;
                if (!empty($term_id)) {
                    $term = get_term($term_id);
                    if (!empty($term)) {
                        $name = $term->name;
                        $slug = $term->slug;
                        $des = $term->description;
                        $btn_label = __('Update', 'abp-transportforge') . ' ' . $label . ' ' . $name;
                        $title = __('Edit ', 'abp-transportforge') . ' ' . $label . ' ' . $name;
                    }
                }
                ?>
                <div class="abp_form">
                    <h5 class="_abp"><span class="_mar_r_xs">🏘️</span><?php echo esc_html($title); ?></h5>
                    <div class="_divider_xs"></div>
                    <input type="hidden" name="id" value="<?php echo esc_attr($term_id); ?>"/>
                    <div class="group_setting">
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php echo esc_html($label) . ' ' . esc_html__('Name', 'abp-transportforge'); ?><sup class="_color_required">*</sup></span>
                                <input class="_form_control" name="name" value="<?php echo esc_attr($name); ?>" placeholder="<?php esc_attr_e('Name', 'abp-transportforge'); ?>" required/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('cat_name'); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php echo esc_html($label) . ' ' . esc_html__('Slug (Optional)', 'abp-transportforge'); ?></span>
                                <input class="_form_control" name="slug" value="<?php echo esc_attr($slug); ?>" placeholder="<?php esc_attr_e('Slug', 'abp-transportforge'); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('cat_slug'); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php echo esc_html($label) . ' ' . esc_html__('Description(Optional)', 'abp-transportforge'); ?></span>
                                <textarea class="_form_control" name="description" placeholder="<?php esc_attr_e('Description', 'abp-transportforge'); ?>"><?php echo esc_html($des); ?></textarea>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('cat_des'); ?>
                        </div>
                    </div>
                    <div class="_divider_xs"></div>
                    <?php ABPTF_Layout::button_global_save('tax_category', $btn_label); ?>
                </div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => $label . ' ' . __('Form Loaded Successfully .....! ', 'abp-transportforge')]);
            }
            public function save_tax_category(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $post_int = fn($key, $default = 0) => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $post_textarea = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_textarea_field(wp_unslash($_POST[$key])) : $default;
                $post_slug = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_title(wp_unslash($_POST[$key])) : $default;
                $tax_id = $post_int('id');
                $name = $post_val('name');
                $slug = $post_slug('slug');
                $description = $post_textarea('description');
                $post_id = $post_int('post_id');
                $label = ABPTF_Function::category_label();
                if (empty($name)) {
                    ob_start();
                    if ($post_id > 0) {
                        $_category = ABPTF_Function::get_post_info($post_id, 'abptf_category');
                        self::category_selection($_category);
                    } else {
                        $this->category_list();
                    }
                    $html = ob_get_clean();
                    wp_send_json_error(['html' => $html, 'type' => 'warn', 'msg' => $label . ' ' . __('Name cannot be blank!', 'abp-transportforge')], 400);
                }
                if ($tax_id > 0) {
                    $result = wp_update_term($tax_id, 'abptf_category', [
                        'name' => $name,
                        'slug' => $slug,
                        'description' => $description,
                    ]);
                } else {
                    $result = wp_insert_term($name, 'abptf_category', [
                        'slug' => $slug,
                        'description' => $description,
                    ]);
                }
                $this->update_category();
                ob_start();
                if ($post_id > 0) {
                    $_category = ABPTF_Function::get_post_info($post_id, 'abptf_category');
                    self::category_selection($_category);
                } else {
                    $this->category_list();
                }
                $html = ob_get_clean();
                if (is_wp_error($result)) {
                    wp_send_json_error(['html' => $html, 'type' => 'warn', 'msg' => $result->get_error_message()], 400);
                }
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => $label . ' ' . __('Saved Successfully !', 'abp-transportforge'),]);
            }
            public function delete_tax_category(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $tax_id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : '';
                $label = ABPTF_Function::category_label();
                if (empty($tax_id) || !is_numeric($tax_id)) {
                    ob_start();
                    $this->category_list();
                    $html = ob_get_clean();
                    wp_send_json_error(['html' => $html, 'msg' => $label. ' ' . __('id Invalid...!', 'abp-transportforge'), 'type' => 'warn']);
                }
                $result = wp_delete_term($tax_id, 'abptf_category');
                $this->update_category();
                ob_start();
                $this->category_list();
                $html = ob_get_clean();
                if (is_wp_error($result)) {
                    wp_send_json_error(['html' => $html, 'msg' => $result->get_error_message(), 'type' => 'warn']);
                }
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => ABPTF_Function::category_label() . ' ' . __('Deleted Successfully !', 'abp-transportforge')]);
            }
            public function update_category(): void {
                $taxonomies = ABPTF_Function::get_taxonomy('abptf_category');
                $tax = [];
                if (!empty($taxonomies) && is_array($taxonomies) && sizeof($taxonomies) > 0) {
                    foreach ($taxonomies as $taxonomy) {
                        $tax[$taxonomy->term_id]['name'] = $taxonomy->name;
                        $tax[$taxonomy->term_id]['description'] = $taxonomy->description;
                    }
                }
                ksort($tax);
                update_option('abptf_category', $tax);
            }
            public function category_list(): void {
                $all_categories = ABPTF_Function::get_option('abptf_category');
                $count = 1;
                if (!empty($all_categories) && is_array($all_categories) && sizeof($all_categories) > 0) { ?>
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('SI', 'abp-transportforge') ?></th>
                            <th class="_min_150"><?php echo esc_html(ABPTF_Function::category_label()); ?></th>
                            <th><?php esc_html_e('ID', 'abp-transportforge') ?></th>
                            <th class="_min_150"><?php esc_html_e('Description', 'abp-transportforge') ?></th>
                            <th class="_w_250"><?php esc_html_e('Shortcode', 'abp-transportforge') ?></th>
                            <th class="_w_100"><?php esc_html_e('Action', 'abp-transportforge') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($all_categories as $term_id => $category) {
                            $name = $category['name'] ?? ''; ?>
                            <tr>
                                <th><?php echo esc_html($count); ?>.</th>
                                <th class="_text_left"><a href="<?php echo esc_url(get_term_link($term_id)); ?>" target="_blank" class="_abp_fs_h5_color_theme"><?php echo esc_html($name); ?></a></th>
                                <th><?php echo esc_html($term_id); ?></th>
                                <td><?php echo esc_html($category['description'] ?? ''); ?></td>
                                <th><code> [abptf-post cat_id="<?php echo esc_attr($term_id); ?>"]</code></th>
                                <th>
                                    <div class="_fj_center">
                                        <div class="_group_content">
                                            <button type="button" class="_btn_light_yellow_xxs" onclick="abptf_popup_open_global('tax_category','<?php echo esc_attr($term_id); ?>')" title="<?php echo esc_attr__('Edit : ', 'abp-transportforge') . ' ' . esc_attr($name); ?>">✍️</button>
                                            <button type="button" class="_btn_light_danger_xxs" onclick="abptf_delete_global('tax_category','<?php echo esc_attr($term_id); ?>')" title="<?php echo esc_attr__('Trash : ', 'abp-transportforge') . ' ' . esc_attr($name); ?>">❌</button>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                            <?php $count++;
                        } ?>
                        </tbody>
                    </table>
                <?php } else {
                    ABPTF_Layout::layout_warning_info('no_category');
                }
            }
            public static function category_selection($_category = ''): void {
                $all_categories = ABPTF_Function::get_option('abptf_category');
                if (!empty($all_categories) && is_array($all_categories) && sizeof($all_categories) > 0) { ?>
                    <div class="custom_radio _f_wrap_gap_xs">
                        <input type="hidden" name="abptf_category" value="<?php echo esc_attr($_category); ?>"/>
                        <?php foreach ($all_categories as $key => $category) {
                            $name = $category['name'] ?? ''; ?>
                            <div class="radio_item">
                                <button type="button" class="_btn_light_info_xs <?php echo esc_attr($_category == $key ? 'abp_active' : ''); ?>" data-radio="<?php echo esc_attr($key); ?>" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                    <span data-icon class="_mar_r_xs <?php echo esc_attr($_category == $key ? 'far fa-check-circle' : 'far fa-circle'); ?>"></span><span class="_text_left_fs_label"><?php echo esc_html($name); ?></span>
                                </button>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p><?php echo esc_html(ABPTF_Status::array_info('no_category')); ?></p>
                    <?php
                }
            }
        }
        new ABPTF_Category();
    }