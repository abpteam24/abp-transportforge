<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTF_Resource')) {
        class ABPTF_Resource {
            public function __construct() {
                add_action('abptf_global_resource', array($this, 'global_resource'));
                add_action('wp_ajax_abptf_save_faq_config', array($this, 'save_faq_config'));
                add_filter('abptf_get_faq_array', array($this, 'get_faq_array'));
                add_action('abptf_post_content', array($this, 'post_resource'));
                add_action('wp_ajax_abptf_save_tc_config', array($this, 'save_tc_config'));
                add_action('wp_ajax_abptf_import_faq', array($this, 'import_faq'));
                add_action('wp_ajax_abptf_import_tc', array($this, 'import_tc'));
            }
            public function global_resource(): void {
                if (ABPTF_Function::on_off('faq')) {
                    ?>
                    <div class="setting_item faq_configuration _mar_b_xs">
                        <h5 class="_abp"><span class="_mar_r_xxs">❓</span><?php esc_html_e('Global FAQ Configuration', 'abp-transportforge'); ?></h5>
                        <div class="faq_config">
                            <?php $this->faq_config(); ?>
                        </div>
                        <div class="_divider_xxs"></div>
                        <?php ABPTF_Layout::info_text('abptf_faqs'); ?>
                    </div>
                    <?php
                }
                if (ABPTF_Function::on_off('tc')) {
                    ?>
                    <div class="setting_item">
                        <h5 class="_abp_mar_b_xs"><span class="_mar_r_xxs">🤝</span><?php esc_html_e('Global Term & Conditions Configuration', 'abp-transportforge'); ?></h5>
                        <div class="_divider_xxs"></div>
                        <?php ABPTF_Layout::info_text('abptf_tc'); ?>
                        <div class="tc_config">
                            <?php $this->tc_config(); ?>
                        </div>
                    </div>
                    <?php
                }
            }
            public function faq_config(): void {
                $faqs = ABPTF_Function::get_option('abptf_faqs');
                ?>
                <div class="abp_form">
                    <?php $this->faq($faqs, true); ?>
                </div>
                <?php
            }
            public function faq($faqs = [], $global = false): void {
                ?>
                <div class="configuration_content">
                    <div class="insertable_area sortable_area">
                        <?php
                            if (!empty($faqs) && sizeof($faqs) > 0) {
                                foreach ($faqs as $faq) {
                                    $this->faq_item($faq);
                                }
                            }
                        ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fj_between">
                        <?php ABPTF_Layout::button_add(__('Add New FAQ Item', 'abp-transportforge'));
                            if ($global) {
                                ABPTF_Layout::button_global_save('faq_config', __('Save FAQs Configuration', 'abp-transportforge'));
                            } ?>
                    </div>
                    <div class="abp_hidden">
                        <div class="hidden_content">
                            <?php $this->faq_item(); ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            public function faq_item($faq = []): void {
                $title = $faq['title'] ?? __('NEW', 'abp-transportforge');
                $description = $faq['des'] ?? '';
                $description = $description ? html_entity_decode($description) : '';
                $editor_id = 'abptf_editor_faq' . wp_rand(0, 999);
                ?>
                <div class="delete_area faq_item _mar_b_xs <?php echo esc_attr(empty($faq) ? 'active' : ''); ?>">
                    <div class="faq_question">
                        <h6 class="_abp edit_hook" data-paste="#faq_title"><?php echo esc_html($title); ?></h6>
                        <?php ABPTF_Layout::button_delete_sort_edit(); ?>
                    </div>
                    <div class="edit_area">
                        <div class="faq_answer_content">
                            <?php ABPTF_Layout::info_text('faq_item'); ?>
                            <div class="_divider_xs"></div>
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php esc_html_e('FAQ Title', 'abp-transportforge'); ?><sup class="_color_required">*</sup></span>
                                <input type="text" class="_form_control" name="faq_title[]" data-pass="#faq_title" placeholder="<?php esc_attr_e('EX: What is the check-in time?', 'abp-transportforge'); ?>" value="<?php echo esc_attr($title); ?>"/>
                            </label>
                            <div class="_fd_column_mar_t_xs">
                                <span class="_abp_label"><?php esc_html_e('Description', 'abp-transportforge'); ?></span>
                                <?php
                                    wp_editor(
                                        $description,
                                        $editor_id,
                                        array(
                                            'textarea_name' => 'faq_description[]',
                                            'textarea_rows' => 6,
                                            'media_buttons' => true,
                                            'teeny' => false,
                                            'quicktags' => true
                                        )
                                    );
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            public function save_faq_config(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $abptf_faqs = $this->get_faq_array();
                update_option('abptf_faqs', $abptf_faqs);
                ob_start();
                $this->faq_config();
                $html = ob_get_clean();
                wp_send_json_success([
                    'html' => $html,
                    'msg' => __('FAQs Configuration Saved Successfully..... !! ', 'abp-transportforge'),
                    'type' => 'success'
                ]);
            }
            public function post_resource($post_infos = []): void {
                ?>
                <div class="tab_item" data-tabs="#abptf_resource">
                    <?php $this->tax($post_infos);
                        $this->post_faq($post_infos);
                        $this->post_tc($post_infos); ?>
                </div>
                <?php
            }
            public function tax($post_infos = []): void {
                $tax_status = $post_infos['_tax_status'] ?? '';
                $tax_classes = WC_Tax::get_tax_rate_classes();
                $tax_class = $post_infos['_tax_class'] ?? '';
                ?>
                <h5 class="_abp"><span class="_mar_r_xxs">🧾</span> <?php esc_html_e('Tax Configuration', 'abp-transportforge'); ?></h5>
                <div class="_divider_xs"></div>
                <?php if (get_option('woocommerce_calc_taxes') == 'yes') { ?>
                    <div class="group_setting">
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php esc_html_e('Tax Status', 'abp-transportforge'); ?></span>
                                <select class="_form_control" name="_tax_status">
                                    <option disabled selected><?php esc_html_e('Please Select', 'abp-transportforge'); ?></option>
                                    <option value="taxable" <?php echo esc_attr($tax_status == 'taxable' ? 'selected' : ''); ?>><?php esc_html_e('Taxable', 'abp-transportforge'); ?></option>
                                    <option value="shipping" <?php echo esc_attr($tax_status == 'shipping' ? 'selected' : ''); ?>><?php esc_html_e('Shipping only', 'abp-transportforge'); ?></option>
                                    <option value="none" <?php echo esc_attr($tax_status == 'none' ? 'selected' : ''); ?>><?php esc_html_e('None', 'abp-transportforge'); ?></option>
                                </select>
                            </label>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php esc_html_e('Tax Class', 'abp-transportforge'); ?></span>
                                <select class="_form_control" name="_tax_class">
                                    <option disabled selected><?php esc_html_e('Please Select', 'abp-transportforge'); ?></option>
                                    <option value="standard" <?php echo esc_attr($tax_class == 'standard' ? 'selected' : ''); ?>><?php esc_html_e('Standard', 'abp-transportforge'); ?></option>
                                    <?php if (sizeof($tax_classes) > 0) { ?>
                                        <?php foreach ($tax_classes as $class) { ?>
                                            <option value="<?php echo esc_attr($class->slug); ?>" <?php echo esc_attr($tax_class == $class->slug ? 'selected' : ''); ?>> <?php echo esc_html($class->name); ?> </option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('_tax_class'); ?>
                        </div>
                    </div>
                <?php } else {
                    ABPTF_Layout::layout_warning_info('enable_tax_msg');
                } ?>
                <?php
            }
            public function post_faq($post_infos = []): void {
                if (ABPTF_Function::on_off('faq')) {
                    $post_id = absint($post_infos['post_id'] ?? 0);
                    $display = $post_infos['display_faq'] ?? 'on';
                    $active_global_faq = $post_infos['active_global_faq'] ?? 'on';
                    $faqs = get_post_meta($post_id, 'abptf_faqs', true);
                    $faqs = is_array($faqs) ? $faqs : [];
                    ?>
                    <h5 class="_abp"><span class="_mar_r_xxs">❓</span><?php esc_html_e('FAQs Configuration', 'abp-transportforge'); ?></h5>
                    <div class="_divider_xs"></div>
                    <div class="group_setting">
                        <div class="setting_item">
                            <div class="_f_wrap_fj_between_fa_center">
                                <div class="_fa_center">
                                    <?php ABPTF_Layout::switch_checkbox('display_faq', $display); ?>
                                    <span class="_abp_label"><?php esc_html_e('Active FAQs ?', 'abp-transportforge'); ?></span>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('display_faq'); ?>
                        </div>
                        <div data-collapse="#display_faq" class="setting_item <?php echo esc_attr($display == 'on' ? 'abp_active' : ''); ?>">
                            <div class="_fj_between">
                                <div class="_fa_center">
                                    <?php ABPTF_Layout::switch_checkbox('active_global_faq', $active_global_faq); ?>
                                    <span class="_abp_label"><?php esc_html_e('Use Global FAQ ?', 'abp-transportforge'); ?></span>
                                </div>
                                <div data-collapse="#active_global_faq" class=" <?php echo esc_attr($active_global_faq == 'on' ? '' : 'abp_active'); ?>">
                                    <button type="button" class="_btn_theme import_faq"><span class="fas fa-file-upload _mar_r_xs"></span><?php esc_html_e('Import Global FAQ', 'abp-transportforge'); ?></button>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('active_global_faq'); ?>
                        </div>
                    </div>
                    <div data-collapse="#display_faq" class="<?php echo esc_attr($display == 'on' ? 'abp_active' : ''); ?>">
                        <div class="_mar_t_xs <?php echo esc_attr($active_global_faq == 'on' ? '' : 'abp_active'); ?>" data-collapse="#active_global_faq">
                            <div class="faq_content">
                                <?php $this->faq($faqs); ?>
                            </div>
                        </div>
                    </div>
                <?php }
            }
            public function post_tc($post_infos = []): void {
                if (ABPTF_Function::on_off('tc')) {
                    $post_id = absint($post_infos['post_id'] ?? 0);
                    $abptf_tc = get_post_meta($post_id, 'abptf_tc', true);
                    $display = $post_infos['display_tc'] ?? 'on';
                    $active_global_tc = $post_infos['active_global_tc'] ?? 'on';
                    ?>
                    <h5 class="_abp"><span class="_mar_r_xxs">🤝</span><?php esc_html_e('Term & Conditions', 'abp-transportforge'); ?></h5>
                    <div class="_divider_xs"></div>
                    <div class="group_setting">
                        <div class="setting_item">
                            <div class="_f_wrap_fj_between_fa_center">
                                <div class="_fa_center">
                                    <?php ABPTF_Layout::switch_checkbox('display_tc', $display); ?>
                                    <span class="_abp_label"><?php esc_html_e('Active Term & Conditions ?', 'abp-transportforge'); ?></span>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('display_tc'); ?>
                        </div>
                        <div data-collapse="#display_tc" class="setting_item <?php echo esc_attr($display == 'on' ? 'abp_active' : ''); ?>">
                            <div class="_fj_between">
                                <div class="_fa_center">
                                    <?php ABPTF_Layout::switch_checkbox('active_global_tc', $active_global_tc); ?>
                                    <span class="_abp_label"><?php esc_html_e('Use Global Term & Conditions ?', 'abp-transportforge'); ?></span>
                                </div>
                                <div data-collapse="#active_global_tc" class=" <?php echo esc_attr($active_global_tc == 'on' ? '' : 'abp_active'); ?>">
                                    <button type="button" class="_btn_theme import_tc"><span class="fas fa-file-upload _mar_r_xs"></span><?php esc_html_e('Import Global Term & Conditions', 'abp-transportforge'); ?></button>
                                </div>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text('active_global_tc'); ?>
                        </div>
                        <div data-collapse="#display_tc" class="<?php echo esc_attr($display == 'on' ? 'abp_active' : ''); ?>">
                            <div class="setting_item full_width <?php echo esc_attr($active_global_tc == 'on' ? '' : 'abp_active'); ?>" data-collapse="#active_global_tc">
                                <div class="tc_content">
                                    <?php $this->tc($abptf_tc); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            public function tc_config(): void {
                $tcs = ABPTF_Function::get_option('abptf_tc', '');
                ?>
                <div class="abp_form">
                    <?php $this->tc($tcs); ?>
                    <div class="_divider_xs"></div>
                    <?php ABPTF_Layout::button_global_save('tc_config', __('Save Term & Conditions Configuration', 'abp-transportforge')); ?>
                </div>
                <?php
            }
            public function tc($tcs = ''): void {
                $description = $tcs ? wp_kses_post($tcs) : '';
                $editor_id = 'abptf_editor_tc_' . wp_rand(0, 999);
                ?>
                <div class="edit_area">
                    <div class="_fd_column_mar_t_xs">
                        <span class="_abp_label"><?php esc_html_e('Term & Conditions Content', 'abp-transportforge'); ?></span>
                        <?php
                            wp_editor(
                                $description,
                                $editor_id,
                                array(
                                    'textarea_name' => 'tc_content',
                                    'textarea_rows' => 12,
                                    'media_buttons' => true,
                                    'teeny' => false,
                                    'quicktags' => true
                                )
                            );
                        ?>
                    </div>
                    <?php ABPTF_Layout::info_text('tc_item'); ?>
                </div>
                <?php
            }
            public function save_tc_config(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $post_html = fn($key, $default = '') => isset($_POST[$key]) ? wp_kses_post(wp_unslash($_POST[$key])) : $default;
                update_option('abptf_tc', $post_html('tc_content'));
                ob_start();
                $this->tc_config();
                $html = ob_get_clean();
                wp_send_json_success([
                    'html' => $html,
                    'msg' => __('Term & Conditions  Saved Successfully..... !!', 'abp-transportforge'),
                    'type' => 'success'
                ]);
            }
            public function get_faq_array(array $abptf_faqs = []): array {
                $has_post_nonce = isset($_POST['abptf_post_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abptf_post_nonce'])), 'abptf_post_nonce');
                $has_ajax_nonce = check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false);
                if (($has_post_nonce || $has_ajax_nonce) && current_user_can('manage_options')) {
                    $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                    $post_html_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('wp_kses_post', wp_unslash($_POST[$key])) : [];
                    $titles = $post_array('faq_title');
                    $descriptions = $post_html_array('faq_description');
                    if (!empty($titles)) {
                        foreach ($titles as $key => $title) {
                            if ($title && !empty($descriptions[$key])) {
                                $abptf_faqs[$key] = [
                                    'title' => $title,
                                    'des' => $descriptions[$key],
                                ];
                            }
                        }
                    }
                }
                return $abptf_faqs;
            }
            public function import_tc(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $tcs = ABPTF_Function::get_option('abptf_tc', '');
                ob_start();
                $this->tc($tcs);
                $html_content = ob_get_clean();
                wp_send_json_success(['html' => $html_content, 'msg' => __('Term & Conditions  Imported Successfully ..... !! ', 'abp-transportforge'), 'type' => 'success']);
            }
            public function import_faq(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $faqs = ABPTF_Function::get_option('abptf_faqs');
                $faqs = is_array($faqs) ? $faqs : [];
                ob_start();
                $this->faq($faqs);
                $html_content = ob_get_clean();
                wp_send_json_success(['html' => $html_content, 'msg' => __('FAQ ImportedSuccessfully ..... !! ', 'abp-transportforge'), 'type' => 'success']);
            }
        }
        new ABPTF_Resource();
    }