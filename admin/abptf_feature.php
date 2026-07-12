<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTF_Feature')) {
        class ABPTF_Feature {
            public function __construct() {
                add_action('abptf_global_feature', array($this, 'global_feature'));
                add_action('wp_ajax_abptf_add_option_feature', array($this, 'add_option_feature'));
                add_action('wp_ajax_abptf_save_option_feature', array($this, 'save_option_feature'));
                add_action('wp_ajax_abptf_delete_option_feature', array($this, 'delete_option_feature'));
            }
            public function global_feature(): void {
                if (ABPTF_Function::on_off('feature')) {
                    $label = ABPTF_Function::feature_label(); ?>
                    <div class="_fj_between">
                        <h5 class="_abp"><span class="_mar_r_xs">🔗</span><?php echo esc_html($label); ?></h5>
                        <?php ABPTF_Layout::button_global_popup('option_feature', __('Add New', 'abp-transportforge') . ' ' . $label); ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="option_feature _ov_auto">
                        <?php $this->feature_list(); ?>
                    </div>
                    <?php
                }
            }
            public function add_option_feature(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                ob_start();
                $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : '';
                $options = ABPTF_Function::get_option('abptf_feature');
                $options = is_array($options) ? $options : [];
                $feature = $options[$id] ?? [];
                $label = ABPTF_Function::feature_label();
                $btn_label = __('Save', 'abp-transportforge') . ' ' . $label;
                $title = __('Add new ', 'abp-transportforge') . ' ' . $label;
                ?>
                <div class="abp_form">
                    <h5 class="_abp"><span class="_mar_r_xs">🔗</span><?php echo esc_html($title); ?></h5>
                    <?php ABPTF_Layout::info_text('feature_icon');
                        ABPTF_Layout::info_text('feature_name');
                        ABPTF_Layout::info_text('feature_value'); ?>
                    <div class="_divider_xxs"></div>
                    <div class="configuration_content">
                        <table class="_abp ">
                            <thead>
                            <tr>
                                <th><?php esc_html_e('Icon', 'abp-transportforge'); ?></th>
                                <th><?php esc_html_e('Label', 'abp-transportforge'); ?><sup class="_color_required">*</sup></th>
                                <th><?php esc_html_e('Value', 'abp-transportforge'); ?></th>
                                <th class="_w_10"><?php esc_html_e('Action', 'abp-transportforge'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="insertable_area sortable_area">
                            <?php self::form_feature($feature, $id); ?>
                            </tbody>
                        </table>
                        <div class="_divider_xs"></div>
                        <div class="_fj_between">
                            <?php ABPTF_Layout::button_add_xs(__('Add New Feature Item', 'abp-transportforge')); ?>
                            <?php ABPTF_Layout::button_global_save('option_feature', $btn_label); ?>
                        </div>
                        <div class="abp_hidden">
                            <table class="_abp">
                                <tbody class="hidden_content">
                                <?php self::form_feature(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => $label . ' ' . __('Form Loaded Successfully .....! ', 'abp-transportforge')]);
            }
            public function save_option_feature(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $post_int = fn($key, $default = 0) => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                $old_features = ABPTF_Function::get_option('abptf_feature');
                $old_features = is_array($old_features) ? $old_features : [];
                $feature_ids = $post_array('feature_id');
                $feature_names = $post_array('feature_name');
                $feature_values = $post_array('feature_value');
                $feature_icon = $post_array('feature_icon');
                $post_id = $post_int('post_id');
                if (!empty($feature_names)) {
                    foreach ($feature_names as $key => $feature_name) {
                        $feature_val = $feature_values[$key] ?? '';
                        if ($feature_name !== '') {
                            $old_id = isset($feature_ids[$key]) ? (int)$feature_ids[$key] : '';
                            if (!empty($old_id) && isset($old_features[$old_id])) {
                                $id = $old_id;
                            } else {
                                $id = 1;
                                while (isset($old_features[$id])) {
                                    $id++;
                                }
                            }
                            $old_features[$id] = [
                                'label' => $feature_name,
                                'value' => $feature_val,
                                'icon' => $feature_icon[$key] ?? '',
                            ];
                        }
                    }
                }
                update_option('abptf_feature', $old_features);
                $html = '';
                if (empty($post_id) || $post_id <= 0) {
                    ob_start();
                    $this->feature_list();
                    $html = ob_get_clean();
                }
                wp_send_json_success([
                    'html' => $html,
                    'msg' => __('Feature Saved Successfully..........!!', 'abp-transportforge'),
                    'js' => (!empty($post_id) && $post_id > 0 ? self::get_feature_js() : ''),
                ]);
            }
            public function delete_option_feature(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
                $options = ABPTF_Function::get_option('abptf_feature');
                $options = is_array($options) ? $options : [];
                if (!empty($id) && isset($options[$id])) {
                    unset($options[$id]);
                    update_option('abptf_feature', $options);
                }
                ob_start();
                $this->feature_list();
                $html = ob_get_clean();
                wp_send_json_success([
                    'html' => $html,
                    'msg' => ABPTF_Function::feature_label() . ' ' . __('Deleted Successfully!', 'abp-transportforge'),
                    'type' => 'success'
                ]);
            }
            public function feature_list(): void {
                $features = ABPTF_Function::get_option('abptf_feature');
                //echo '<pre>';				print_r( $features );				echo '</pre>';
                if (sizeof($features) > 0) { ?>
                    <div class="_group_list">
                        <?php foreach ($features as $key => $feature) {
                            $label = $feature['label'] ?? '';
                            $value = $feature['value'] ?? '';
                            if (!empty($label)) { ?>
                                <div class="_list_item">
                                    <h6 class="_abp">
                                        <?php ABPTF_Layout::image_icon($feature['icon'] ?? '');
                                            echo esc_html($label . ' ' . (!empty($value) ? '-' . $value : '')); ?>
                                    </h6>
                                    <div class="_group_content">
                                        <button type="button" class="_btn_light_yellow_xxs" onclick="abptf_popup_open_global('option_feature','<?php echo esc_attr($key); ?>')" title="<?php echo esc_attr__('Edit : ', 'abp-transportforge') . ' ' . esc_attr($label); ?>">✍️</button>
                                        <button type="button" class="_btn_light_danger_xxs" onclick="abptf_delete_global('option_feature','<?php echo esc_attr($key); ?>')" title="<?php echo esc_attr__('Trash : ', 'abp-transportforge') . ' ' . esc_attr($label); ?>">❌</button>
                                    </div>
                                </div>
                                <?php
                            }
                        } ?>
                    </div>
                <?php } else {
                    ABPTF_Layout::layout_warning_info('no_feature');
                }
            }
            public static function get_feature_js(): array {
                $features = ABPTF_Function::get_option('abptf_feature');
                $features = is_array($features) ? $features : [];
                $feature_js = [];
                if (sizeof($features) > 0) {
                    foreach ($features as $key => $feature) {
                        $feature_js[] = ['id' => $key, 'icon' => ($feature['icon'] ?? ''), 'label' => ($feature['label'] ?? ''), 'value' => ($feature['value'] ?? '')];
                    }
                }
                return $feature_js;
            }
            public static function form_feature($feature = [], $id = ''): void {
                $label = $feature['label'] ?? '';
                $value = $feature['value'] ?? '';
                $icon = $feature['icon'] ?? '';
                ?>
                <tr class="delete_area">
                    <th><?php do_action('abptf_add_icon', 'feature_icon[]', $icon); ?></th>
                    <th>
                        <label>
                            <input type="hidden" name="feature_id[]" value="<?php echo esc_attr($id); ?>"/>
                            <input type="text" class="_form_control validation_name" name="feature_name[]" placeholder="<?php esc_attr_e('EX: Feature Title', 'abp-transportforge'); ?>" value="<?php echo esc_attr($label); ?>" required/>
                        </label>
                    </th>
                    <th>
                        <label>
                            <input type="text" class="_form_control validation_name" name="feature_value[]" placeholder="<?php esc_attr_e('EX: Feature Value', 'abp-transportforge'); ?>" value="<?php echo esc_attr($value); ?>"/>
                        </label>
                    </th>
                    <td><?php ABPTF_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }
        }
        new ABPTF_Feature();
    }