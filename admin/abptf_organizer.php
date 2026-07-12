<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTF_Organizer')) {
        class ABPTF_Organizer {
            public function __construct() {
                add_action('abptf_global_organizer', array($this, 'global_organizer'));
                add_action('wp_ajax_abptf_add_tax_organizer', array($this, 'add_tax_organizer'));
                add_action('wp_ajax_abptf_save_tax_organizer', array($this, 'save_tax_organizer'));
                add_action('wp_ajax_abptf_delete_tax_organizer', array($this, 'delete_tax_organizer'));
                add_action('abptf_organizer_update', array($this, 'update_organizer'));
            }
            public function global_organizer(): void {
                if (ABPTF_Function::on_off('organizer')) {
                    $label = ABPTF_Function::organizer_label(); ?>
                    <div class="_fj_between">
                        <h5 class="_abp"><span class="_mar_r_xs">🏢</span><?php echo esc_html($label); ?></h5>
                        <?php ABPTF_Layout::button_global_popup('tax_organizer', __('Add New', 'abp-transportforge') . ' ' . $label); ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="tax_organizer _ov_auto">
                        <?php $this->organizer_list(); ?>
                    </div>
                    <?php
                }
            }
            public function add_tax_organizer(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $term_id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
                ob_start();
                $name = $slug = $des = '';
                $label = ABPTF_Function::organizer_label();
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
                    <h5 class="_abp"><span class="_mar_r_xs">🏢</span><?php echo esc_html($title); ?></h5>
                    <div class="_divider_xs"></div>
                    <input type="hidden" name="id" value="<?php echo esc_attr($term_id); ?>"/>
                    <div class="group_setting">
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php echo esc_html($label) . ' ' . esc_html__('Name', 'abp-transportforge'); ?><sup class="_color_required">*</sup></span>
                                <input class="_form_control" name="name" value="<?php echo esc_attr($name); ?>" placeholder="<?php esc_attr_e('Name', 'abp-transportforge'); ?>" required/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('org_name'); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php echo esc_html($label) . ' ' . esc_html__('Slug (Optional)', 'abp-transportforge'); ?></span>
                                <input class="_form_control" name="slug" value="<?php echo esc_attr($slug); ?>" placeholder="<?php esc_attr_e('Slug', 'abp-transportforge'); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('org_slug'); ?>
                        </div>
                        <div class="setting_item full_width">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php echo esc_html($label) . ' ' . esc_html__('Description(Optional)', 'abp-transportforge'); ?></span>
                                <textarea class="_form_control" name="description" placeholder="<?php esc_attr_e('Description', 'abp-transportforge'); ?>"><?php echo esc_html($des); ?></textarea>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('org_des'); ?>
                        </div>
                    </div>
                    <div class="_divider_xs"></div>
                    <?php ABPTF_Layout::button_global_save('tax_organizer', $btn_label); ?>
                </div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => $label . ' ' . __('Form Loaded Successfully .....! ', 'abp-transportforge')]);
            }
            public function save_tax_organizer(): void {
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
                if (empty($name)) {
                    ob_start();
                    if ($post_id > 0) {
                        $_category = ABPTF_Function::get_post_info($post_id, 'abptf_organizer');
                        self::organizer_selection($_category);
                    } else {
                        $this->organizer_list();
                    }
                    $html = ob_get_clean();
                    wp_send_json_error(['html' => $html, 'type' => 'warn', 'msg' => ABPTF_Function::organizer_label() . ' ' . __('Name cannot be blank!', 'abp-transportforge')], 400);
                }
                if ($tax_id > 0) {
                    $result = wp_update_term($tax_id, 'abptf_organizer', [
                        'name' => $name,
                        'slug' => $slug,
                        'description' => $description,
                    ]);
                } else {
                    $result = wp_insert_term($name, 'abptf_organizer', [
                        'slug' => $slug,
                        'description' => $description,
                    ]);
                }
                $this->update_organizer();
                ob_start();
                if ($post_id > 0) {
                    $_organizer = ABPTF_Function::get_post_info($post_id, 'abptf_organizer');
                    self::organizer_selection($_organizer);
                } else {
                    $this->organizer_list();
                }
                $html = ob_get_clean();
                if (is_wp_error($result)) {
                    wp_send_json_error(['html' => $html, 'type' => 'warn', 'msg' => $result->get_error_message()], 400);
                }
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => ABPTF_Function::organizer_label() . ' ' . __('Saved Successfully !', 'abp-transportforge'),]);
            }
            public function delete_tax_organizer(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $tax_id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : '';
                if (empty($tax_id) || !is_numeric($tax_id)) {
                    ob_start();
                    $this->organizer_list();
                    $html = ob_get_clean();
                    wp_send_json_error(['html' => $html, 'msg' => ABPTF_Function::organizer_label() . ' ' . __('id Invalid...!', 'abp-transportforge'), 'type' => 'warn']);
                }
                $result = wp_delete_term($tax_id, 'abptf_organizer');
                $this->update_organizer();
                ob_start();
                $this->organizer_list();
                $html = ob_get_clean();
                if (is_wp_error($result)) {
                    wp_send_json_error(['html' => $html, 'msg' => $result->get_error_message(), 'type' => 'warn']);
                }
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => ABPTF_Function::organizer_label() . ' ' . __('Deleted Successfully !', 'abp-transportforge')]);
            }
            public function update_organizer(): void {
                $taxonomies = ABPTF_Function::get_taxonomy('abptf_organizer');
                $organizer = [];
                if (!empty($taxonomies) && is_array($taxonomies) && sizeof($taxonomies) > 0) {
                    foreach ($taxonomies as $taxonomy) {
                        $organizer[$taxonomy->term_id]['name'] = $taxonomy->name;
                        $organizer[$taxonomy->term_id]['description'] = $taxonomy->description;
                    }
                }
                ksort($organizer);
                update_option('abptf_organizer', $organizer);
            }
            public function organizer_list(): void {
                $all_organizer = ABPTF_Function::get_option('abptf_organizer');
                $count = 1;
                if (!empty($all_organizer) && is_array($all_organizer) && sizeof($all_organizer) > 0) { ?>
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('SI', 'abp-transportforge') ?></th>
                            <th class="_min_150"><?php echo esc_html(ABPTF_Function::organizer_label()); ?></th>
                            <th><?php esc_html_e('ID', 'abp-transportforge') ?></th>
                            <th class="_min_150"><?php esc_html_e('Description', 'abp-transportforge') ?></th>
                            <th class="_w_250"><?php esc_html_e('Shortcode', 'abp-transportforge') ?></th>
                            <th class="_w_100"><?php esc_html_e('Action', 'abp-transportforge') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($all_organizer as $term_id => $organizer) {
                            $name = $organizer['name'] ?? '';
                            $description = $organizer['description'] ?? '';
                            ?>
                            <tr>
                                <th><?php echo esc_html($count); ?>.</th>
                                <th class="_text_left"><a href="<?php echo esc_url(get_term_link($term_id)); ?>" target="_blank" class="_abp_fs_h5_color_theme"><?php echo esc_html($name); ?></a></th>
                                <th><?php echo esc_html($term_id); ?></th>
                                <td><?php echo esc_html($description); ?></td>
                                <th><code> [abptf-post cat_id="<?php echo esc_attr($term_id); ?>"]</code></th>
                                <th>
                                    <div class="_fj_center">
                                        <div class="_group_content">
                                            <button type="button" class="_btn_light_yellow_xxs" onclick="abptf_popup_open_global('tax_organizer','<?php echo esc_attr($term_id); ?>')" title="<?php echo esc_attr__('Edit : ', 'abp-transportforge') . ' ' . esc_attr($name); ?>">✍️</button>
                                            <button type="button" class="_btn_light_danger_xxs" onclick="abptf_delete_global('tax_organizer','<?php echo esc_attr($term_id); ?>')" title="<?php echo esc_attr__('Trash : ', 'abp-transportforge') . ' ' . esc_attr($name); ?>">❌</button>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                            <?php $count++;
                        } ?>
                        </tbody>
                    </table>
                <?php } else {
                    ABPTF_Layout::layout_warning_info('no_organizer');
                }
            }
            public static function organizer_selection($value = ''): void {
                ?>
                <div class="_group_content">
                    <?php $options = ABPTF_Function::get_option('abptf_organizer');
                        if (!empty($options) && is_array($options) && sizeof($options) > 0) { ?>
                            <label>
                                <select class="_form_control" name="abptf_organizer">
                                    <option value="" selected><?php echo esc_html__('Please Select', 'abp-transportforge') .' '.esc_html(ABPTF_Function::organizer_label()); ?></option>
                                    <?php foreach ($options as $key => $option) { ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($key == $value ? 'selected' : ''); ?>><?php echo esc_html($option['name'] ?? ''); ?></option>
                                    <?php } ?>
                                </select>
                            </label>
                        <?php } else {
                            ABPTF_Layout::layout_info_xs('no_organizer');
                        }
                        ABPTF_Layout::button_global_popup('tax_organizer', __('Add New', 'abp-transportforge') . ' ' . ABPTF_Function::organizer_label()); ?>
                </div>
                <?php
            }
        }
        new ABPTF_Organizer();
    }