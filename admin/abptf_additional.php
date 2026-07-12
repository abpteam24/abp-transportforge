<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTF_Additional')) {
        class ABPTF_Additional {
            public function __construct() {
                add_action('abptf_global_additional', array($this, 'global_additional'));
                add_action('abptf_post_content', [$this, 'post_additional_service']);
                add_filter('abptf_get_additional_array', array($this, 'get_additional_array'));
                add_action('wp_ajax_abptf_save_global_additional', array($this, 'save_global_additional'));
                add_action('wp_ajax_abptf_import_additional', array($this, 'import_additional'));
            }
            public function global_additional(): void {
                ?>
                <div class="global_additional">
                    <?php $this->additional_config(); ?>
                </div>
                <?php
            }
            public function additional_config(): void {
                if (ABPTF_Function::on_off('additional_info')) {
                    $additional_services = ABPTF_Function::get_option('abptf_additional', ABPTF_Status::static_additional());
                    ?>
                    <div class="abp_form">
                        <h4 class="_abp_color_theme"><span class="_mar_r_xxs">💰</span><?php esc_html_e('Global Additional services Configuration', 'abp-transportforge'); ?></h4>
                        <?php ABPTF_Layout::info_text('additional_services'); ?>
                        <div class="_divider_xs"></div>
                        <?php $this->additional_service($additional_services, true); ?>
                    </div>
                    <?php
                }
            }
            public function post_additional_service($post_infos): void {
                if (ABPTF_Function::on_off('additional_info')) {
                    $additional_services = $post_infos['additional_services'] ?? array();
                    $display = $post_infos['display_additional_services'] ?? 'on';
                    $active_global_additional = $post_infos['active_global_additional'] ?? 'on';
                    ?>
                    <div class="tab_item additional_configuration" data-tabs="#abptf_additional_service">
                        <h4 class="_abp_color_theme"><span class="_mar_r_xxs">💰</span><?php esc_html_e('Additional services Configuration', 'abp-transportforge'); ?></h4>
                        <div class="_divider_xs"></div>
                        <div class="group_setting">
                            <div class="setting_item">
                                <div class="_f_wrap_fj_between_fa_center">
                                    <div class="_fa_center">
                                        <?php ABPTF_Layout::switch_checkbox('display_additional_services', $display); ?>
                                        <span class="_fs_label_mar_l_xs"><?php esc_html_e('Active Additional services ?', 'abp-transportforge'); ?></span>
                                    </div>
                                </div>
                                <div class="_divider_xs"></div>
                                <?php ABPTF_Layout::info_text('display_additional_services'); ?>
                            </div>
                            <div data-collapse="#display_additional_services" class="setting_item <?php echo esc_attr($display == 'on' ? 'abp_active' : ''); ?>">
                                <div class="_fj_between">
                                    <div class="_fa_center">
                                        <?php ABPTF_Layout::switch_checkbox('active_global_additional', $active_global_additional); ?>
                                        <span class="_fs_label_mar_lr_xs"><?php esc_html_e('Use Global Additional Service ?', 'abp-transportforge'); ?></span>
                                    </div>
                                    <div data-collapse="#active_global_additional" class=" <?php echo esc_attr($active_global_additional == 'on' ? '' : 'abp_active'); ?>">
                                        <button type="button" class="_btn_theme import_additional"><span class="fas fa-file-upload _mar_r_xs"></span><?php esc_html_e('Import Additional Service', 'abp-transportforge'); ?></button>
                                    </div>
                                </div>
                                <div class="_divider_xs"></div>
                                <?php ABPTF_Layout::info_text('active_global_additional'); ?>
                            </div>
                        </div>
                        <div class="<?php echo esc_attr($active_global_additional == 'on' ? '' : 'abp_active'); ?>" data-collapse="#active_global_additional">
                            <div class="additional_content _mar_t_xs">
                                <?php $this->additional_service($additional_services); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            public function additional_service($services = [], $global = false): void {
                ?>
                <div class="configuration_content additional_service">
                    <div class="_ov_auto">
                        <table class="_abp">
                            <thead>
                            <tr>
                                <th class="_w_125"><?php esc_html_e('Icon / Image', 'abp-transportforge'); ?></th>
                                <th class="_min_200"><?php esc_html_e('Name', 'abp-transportforge'); ?><sup class="_color_required">*</sup></th>
                                <th class="_min_100"><?php esc_html_e('Quantity', 'abp-transportforge'); ?></th>
                                <th class="_min_100"><?php esc_html_e('Price', 'abp-transportforge'); ?></th>
                                <th class="_min_100"><?php esc_html_e('Max qty', 'abp-transportforge'); ?></th>
                                <th class="_min_100"><?php esc_html_e('Returnable or Not', 'abp-transportforge'); ?></th>
                                <th class="_min_250"><?php esc_html_e('Description', 'abp-transportforge'); ?></th>
                                <th class="_w_75"><?php esc_html_e('Action', 'abp-transportforge'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="insertable_area sortable_area">
                            <?php
                                if ($services && is_array($services) && sizeof($services) > 0) {
                                    foreach ($services as $key => $service) {
                                        $this->service_item($key, $service);
                                    }
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fj_between">
                        <?php ABPTF_Layout::button_add_xs(__('Add New Additional services', 'abp-transportforge'));
                            if ($global) {
                                ABPTF_Layout::button_global_save('global_additional',__('Save Global Additional services Configuration', 'abp-transportforge'));
                            } ?>
                    </div>
                    <div class="abp_hidden">
                        <table class="_abp">
                            <tbody class="hidden_content">
                            <?php $this->service_item(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }
            public function service_item($key = '', $field = array()): void {
                $field = $field ?: [];
                $name = $field['name'] ?? '';
                $qty = $field['qty'] ?? '';
                $max_qty = $field['max_qty'] ?? '';
                $price = $field['price'] ?? '';
                $returnable = $field['returnable'] ?? 'no';
                $description = $field['description'] ?? '';
                ?>
                <tr class="delete_area ">
                    <td> <?php do_action('abptf_add_image_icon', 'additional_icon[]', ($field['icon'] ?? '')); ?>  </td>
                    <td>
                        <input type="hidden" name="additional_id[]" value="<?php echo esc_attr($key ?: uniqid()); ?>"/>
                        <label>
                            <input type="text" class="_form_control validation_name" name="additional_name[]" placeholder="<?php esc_attr_e('EX: Water Bottle', 'abp-transportforge'); ?>" value="<?php echo esc_attr($name); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="additional_qty[]" placeholder="<?php esc_attr_e('EX: 15', 'abp-transportforge'); ?>" value="<?php echo esc_attr($qty); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="0.01" class="_form_control validation_price" name="additional_price[]" placeholder="<?php esc_attr_e('EX: 15', 'abp-transportforge'); ?>" value="<?php echo esc_attr($price); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="additional_max_qty[]" placeholder="<?php esc_attr_e('EX: 15', 'abp-transportforge'); ?>" value="<?php echo esc_attr($max_qty); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <select class="_form_control" name="additional_returnable[]">
                                <option value="yes" <?php echo esc_attr($returnable == 'yes' ? 'selected' : ''); ?>><?php esc_html_e('Yes', 'abp-transportforge'); ?></option>
                                <option value="no" <?php echo esc_attr($returnable == 'no' ? 'selected' : ''); ?>><?php esc_html_e('No', 'abp-transportforge'); ?></option>
                            </select>
                        </label>
                    </td>
                    <td>
                        <label>
                            <textarea class="_form_control" name="additional_description[]" placeholder="<?php esc_attr_e('EX: Description', 'abp-transportforge'); ?>"><?php echo esc_html($description); ?></textarea>
                        </label>
                    </td>
                    <td><?php ABPTF_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }
            public function save_global_additional(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $additional_services = $this->get_additional_array();
                update_option('abptf_additional', $additional_services);
                ob_start();
                $this->additional_config();
                $html = ob_get_clean();
                wp_send_json_success([
                    'html' => $html,
                    'msg' => __('Additional services Configuration Saved Successfully ..... !!', 'abp-transportforge'),
                    'type' => 'success'
                ]);
            }
            public function get_additional_array(array $additional_services = []): array {
                $has_post_nonce = isset($_POST['abptf_post_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abptf_post_nonce'])), 'abptf_post_nonce');
                $has_ajax_nonce = check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false);
                if (($has_post_nonce || $has_ajax_nonce) && current_user_can('manage_options')) {
                    $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                    $post_textarea_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_textarea_field', wp_unslash($_POST[$key])) : [];
                    $additional_ids = $post_array('additional_id');
                    $icon = $post_array('additional_icon');
                    $name = $post_array('additional_name');
                    $qty = $post_array('additional_qty');
                    $max_qty = $post_array('additional_max_qty');
                    $returnable = $post_array('additional_returnable');
                    $price = $post_array('additional_price');
                    $description = $post_textarea_array('additional_description');
                    if (!empty($additional_ids)) {
                        foreach ($additional_ids as $key => $additional_id) {
                            $service_name = $name[$key] ?? '';
                            if (!empty($service_name)) {
                                $final_id = isset($additional_services[$additional_id]) ? uniqid() : $additional_id;
                                $additional_services[$final_id] = [
                                    'icon' => $icon[$key] ?? '',
                                    'name' => $service_name,
                                    'qty' => $qty[$key] ?? '',
                                    'max_qty' => $max_qty[$key] ?? '',
                                    'price' => $price[$key] ?? '',
                                    'returnable' => $returnable[$key] ?? '',
                                    'description' => $description[$key] ?? '',
                                ];
                            }
                        }
                    }
                }
                return $additional_services;
            }
            public function import_additional(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $additional_services = ABPTF_Function::get_option('abptf_additional', ABPTF_Status::static_additional());
                $additional_services = is_array($additional_services) ? $additional_services : [];
                ob_start();
                $this->additional_service($additional_services);
                $html_content = ob_get_clean();
                wp_send_json_success(['html' => $html_content, 'msg' => __('Additional services ImportedSuccessfully ..... !! ', 'abp-transportforge'), 'type' => 'success']);
            }
        }
        new ABPTF_Additional();
    }