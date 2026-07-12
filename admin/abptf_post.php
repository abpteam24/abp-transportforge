<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTF_Post')) {
        class ABPTF_Post {
            public function __construct() {
                add_action('abptf_load_posts', array($this, 'load_posts'));
                add_action('add_meta_boxes', [$this, 'settings_meta']);
                add_action('save_post', array($this, 'save_settings'));
                add_action('wp_ajax_abptf_post_permanent_remove', array($this, 'post_permanent_remove'));
                add_action('wp_ajax_abptf_post_move_trash', array($this, 'post_move_trash'));
                add_action('wp_ajax_abptf_post_restore', array($this, 'post_restore'));
                add_action('wp_ajax_abptf_reload_post_list', array($this, 'reload_post_list'));
            }
            public function load_posts($abptf_info = []): void {
                $brand_icon = ABPTF_Function::icon();
                $total_posts = $abptf_info['total_post'] ?? 0;
                $total_publish = $abptf_info['total_publish'] ?? 0;
                $total_draft = $abptf_info['total_draft'] ?? 0;
                $total_private = $abptf_info['total_private'] ?? 0;
                $total_trash = $abptf_info['total_trash'] ?? 0;
                $status = 'publish';
                if (isset($_GET['_abptf_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_abptf_nonce'])), 'abptf_url_action')) {
                    $status = isset($_GET['status']) ? sanitize_text_field(wp_unslash($_GET['status'])) : 'publish';
                }
                $status = $status ?? 'publish';
                $filter_args['status'] = $status;
                ?>
                <div class="abptf_posts _abp_panel">
                    <div class="_panel_head _fj_between_f_wrap">
                        <h4 class="_abp_color_white"><?php ABPTF_Layout::image_icon($brand_icon); ?><?php esc_html_e('Post Lists', 'abp-transportforge'); ?></h4>
                        <div class="_group_content">
                            <input type="hidden" name="select_hidden_post_status" value="<?php echo esc_attr($status); ?>"/>
                            <button type="button" class="_btn_white_xs <?php echo esc_attr($status == 'all' ? 'abp_active' : ''); ?>" data-href="<?php echo esc_url(ABPTF_Function::build_url('posts', ['status' => 'all'])); ?>"><?php esc_html_e('All', 'abp-transportforge'); ?> ( <?php echo esc_html($total_posts); ?> )</button>
                            <button type="button" class="_btn_white_xs <?php echo esc_attr($status == 'publish' ? 'abp_active' : ''); ?>" data-href="<?php echo esc_url(ABPTF_Function::build_url('posts', ['status' => 'publish'])); ?>"><?php esc_html_e('Published', 'abp-transportforge'); ?> ( <?php echo esc_html($total_publish); ?> )</button>
                            <button type="button" class="_btn_white_xs <?php echo esc_attr($status == 'private' ? 'abp_active' : ''); ?>" data-href="<?php echo esc_url(ABPTF_Function::build_url('posts', ['status' => 'private'])); ?>"><?php esc_html_e('Private', 'abp-transportforge'); ?> ( <?php echo esc_html($total_private); ?> )</button>
                            <button type="button" class="_btn_white_xs <?php echo esc_attr($status == 'draft' ? 'abp_active' : ''); ?>" data-href="<?php echo esc_url(ABPTF_Function::build_url('posts', ['status' => 'draft'])); ?>"><?php esc_html_e('Draft', 'abp-transportforge'); ?> ( <?php echo esc_html($total_draft); ?> )</button>
                            <button type="button" class="_btn_white_xs <?php echo esc_attr($status == 'trash' ? 'abp_active' : ''); ?>" data-href="<?php echo esc_url(ABPTF_Function::build_url('posts', ['status' => 'trash'])); ?>"><?php esc_html_e('Trash', 'abp-transportforge'); ?> ( <?php echo esc_html($total_trash); ?> )</button>
                        </div>
                        <a class="_btn_light_white_xs" href="<?php echo esc_url(admin_url('post-new.php?post_type=' . ABPTF_Function::get_cpt())); ?>"><span class="_mar_r_xs">➕</span><?php esc_html_e('Add New Post', 'abp-transportforge'); ?></a>
                    </div>
                    <div class="_panel_body post_list">
                        <?php $this->post_table($filter_args); ?>
                    </div>
                </div>
                <?php
            }
            public function settings_meta(): void {
                $label = ABPTF_Function::label();
                $brand_icon = ABPTF_Function::icon();
                $label = $label . ' ' . __('Configuration', 'abp-transportforge') . get_the_title(get_the_id());
                add_meta_box('abptf_configuration', '<span class="' . esc_attr($brand_icon ?: '') . '"></span>' . esc_html($label), array($this, 'settings'), esc_attr(ABPTF_Function::get_cpt()), 'normal', 'high');
            }
            //=============================//
            public function post_table($filter_args): void {
                //echo '<pre>';print_r($filter_args);echo '</pre>';
                $status = $filter_args['status'] ?? '';
                if (empty($status) || $status == 'all') {
                    $status = ['publish', 'draft', 'private', 'trash'];
                }
                $page_number = absint($filter_args['page_number'] ?? 1) ?: 1;
                $limit = absint(($filter_args['page_item'] ?? 0) ?: ABPTF_Function::get_option('abptf_per_page_item', 20));
                $count = ($page_number - 1) * $limit + 1;
                $offset = $count - 1;
                $cpt = ABPTF_Function::get_cpt();
                $filters['status'] = $status;
                $filters['posts_per_page'] = $limit;
                $filters['paged'] = $offset;
                $post_ids = ABPTF_Query::get_post_id($filters);
                if (!empty($post_ids) && sizeof($post_ids) > 0) {
                    $total_post = sizeof(ABPTF_Query::get_post_id(['status' => $status]));
                    $new_post_url = admin_url('post-new.php?post_type=' . $cpt);
                    ?>
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th class="_w_50"><?php esc_html_e('SI', 'abp-transportforge'); ?></th>
                            <th class="_w_100"><?php esc_html_e('Image', 'abp-transportforge'); ?></th>
                            <th><?php esc_html_e('Post', 'abp-transportforge'); ?></th>
                            <th><?php esc_html_e('Rent Rule', 'abp-transportforge'); ?></th>
                            <th class="_w_100"><?php esc_html_e('Property', 'abp-transportforge'); ?></th>
                            <th><?php esc_html_e('Shortcode', 'abp-transportforge'); ?></th>
                            <th class="_w_175"><?php esc_html_e('Actions', 'abp-transportforge'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                            foreach ($post_ids as $post_id) {
                                $title = get_the_title($post_id);
                                $edit_link = get_edit_post_link($post_id);
                                $post_sale_continue = ABPTF_Function::get_post_info($post_id, 'sale_continue', 'on');
                                $rent_rule = ABPTF_Function::get_post_info($post_id, 'rent_rule');
                                $post_status = get_post_status($post_id);
                                $new_post_url = add_query_arg(array('copy_post' => $post_id, '_abptf_nonce' => wp_create_nonce('abptf_copy_post_action'),), $new_post_url);
                                ?>
                                <tr>
                                    <th><?php echo esc_html($count); ?>.</th>
                                    <td><?php ABPTF_Layout::image($post_id); ?></td>
                                    <td>
                                        <?php if ($post_status == 'trash') { ?>
                                            <h5 class="_abp_color_warning"><?php
                                                    if (ABPTF_Function::on_off('post_icon')) {
                                                        ABPTF_Layout::image_icon(ABPTF_Function::get_post_info($post_id, 'post_icon'));
                                                    }
                                                    echo esc_html($title);
                                                ?></h5>
                                        <?php } else { ?>
                                            <a href="<?php echo esc_url($edit_link); ?>" class="_abp_fs_h5_color_theme">
                                                <?php if (ABPTF_Function::on_off('post_icon')) {
                                                    ABPTF_Layout::image_icon(ABPTF_Function::get_post_info($post_id, 'post_icon'));
                                                }
                                                    echo esc_html($title); ?>
                                            </a>
                                        <?php } ?>
                                        <div class="_d_flex">
                                            <span class="_mar_r_xxs publish"><?php echo esc_html(__('ID : ', 'abp-transportforge') . ' ' . $post_id); ?></span>
                                            <span class="_mar_r_xxs <?php echo esc_attr($post_sale_continue == 'on' ? 'publish' : 'trash'); ?>"><?php echo esc_html($post_sale_continue == 'on' ? __('Rent On', 'abp-transportforge') : __('Rent Off', 'abp-transportforge')); ?></span>
                                            <span class="_mar_r_xxs <?php echo esc_attr($post_status); ?>"><?php echo esc_html($post_status); ?></span>
                                        </div>
                                    </td>
                                    <th><?php echo esc_html(!empty($rent_rule) ? ABPTF_Layout::rent_rules($rent_rule) : ''); ?></th>
                                    <th><?php echo esc_html(ABPTF_Query::get_property(['post_id' => $post_id], true)); ?></th>
                                    <th>
                                        <p class="_abp"><code> [abptf-post post_id="<?php echo esc_attr($post_id); ?>"]</code></p>
                                        <p class="_abp"><code> [abptf-property post_id="<?php echo esc_attr($post_id); ?>"]</code></p>
                                    </th>
                                    <th>
                                        <div class="_group_content">
                                            <button type="button" class="_btn_light_navy_blue _mar_r_xxs" data-href="<?php echo esc_url($new_post_url); ?>" data-blank="_blank" title="<?php echo esc_html__('Copy/Clone : ', 'abp-transportforge') . ' ' . esc_html($title); ?>">🔁</button>
                                            <?php if ($post_status == 'trash') { ?>
                                                <button type="button" class="_btn_light_success_mar_r_xxs post_restore" data-post_id="<?php echo esc_attr($post_id); ?>" title="<?php echo esc_html__('Restore : ', 'abp-transportforge') . ' ' . esc_html($title); ?>">♻️</button>
                                                <button type="button" class="_btn_light_danger_xxs post_permanent_remove" data-post_id="<?php echo esc_attr($post_id); ?>" title="<?php echo esc_html__('Permanent Remove : ', 'abp-transportforge') . ' ' . esc_html($title); ?>">❌</button>
                                            <?php } else { ?>
                                                <button type="button" class="_btn_light_yellow_mar_r_xxs" data-href="<?php echo esc_url($edit_link); ?>" data-blank="_blank" title="<?php echo esc_html__('Edit : ', 'abp-transportforge') . ' ' . esc_html($title); ?>">✍️</button>
                                                <button type="button" class="_btn_light_theme_mar_r_xxs" data-href="<?php echo esc_url(get_permalink($post_id)); ?>" data-blank="_blank" title="<?php echo esc_html__('View : ', 'abp-transportforge') . ' ' . esc_html($title); ?>">👁️</button>
                                                <button type="button" class="_btn_light_danger_xxs post_move_trash" data-post_id="<?php echo esc_attr($post_id); ?>" title="<?php echo esc_html__('Move to Trash : ', 'abp-transportforge') . ' ' . esc_html($title); ?>"><span class="fas fa-trash"></span></button>
                                            <?php } ?>
                                        </div>
                                    </th>
                                </tr>
                                <?php
                                $count++;
                            }
                        ?>
                        </tbody>
                    </table>
                    <?php
                    do_action('abptf_pagination', ['page_item' => $limit, 'page_number' => $page_number, 'total' => $total_post, 'style' => 'ajax']);
                } else {
                    ABPTF_Layout::layout_warning_info('not_post_found');
                }
            }
            public function settings(): void {
                $post_id = get_the_id();
                $copy_post_id = isset($_GET['copy_post']) ? absint($_GET['copy_post']) : '';
                if (!empty($copy_post_id) && isset($_GET['_abptf_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_abptf_nonce'])), 'abptf_copy_post_action') && current_user_can('edit_post', $copy_post_id)) {
                    ?>
                    <input type="hidden" name="abptf_copy_post" value="<?php echo esc_attr($copy_post_id); ?>"/>
                    <?php
                    $post_infos['copy_post_id'] = $copy_post_id;
                    $new_post_id = $copy_post_id;
                } else {
                    $new_post_id = $post_id;
                }
                $post_infos = ABPTF_Function::get_all_meta($new_post_id);
                wp_nonce_field('abptf_post_nonce', 'abptf_post_nonce');
                ?>
                <div class="abptf_area abptf_admin abp_post_config">
                    <input type="hidden" name="abptf_post_id" value="<?php echo esc_attr($post_id); ?>"/>
                    <div class="_abp_panel">
                        <div class="abptf_tabs tab_top">
                            <div class="_panel_head">
                                <ul class="_abp tab_lists">
                                    <li data-tabs-target="#abptf_general"><span class="fas fa-rainbow"></span><?php esc_html_e('General', 'abp-transportforge'); ?></li>
                                    <li data-tabs-target="#abptf_routing"><span class="fas fa-route"></span><?php esc_html_e('Route Configuration', 'abp-transportforge'); ?></li>
                                    <li data-tabs-target="#abptf_dates"><span>🗓️</span><?php esc_html_e('Date & Time', 'abp-transportforge'); ?></li>
                                    <?php if (ABPTF_Function::on_off('additional_info')) { ?>
                                        <li data-tabs-target="#abptf_additional_service"><span>💰</span><?php esc_html_e('Additional services', 'abp-transportforge'); ?></li>
                                    <?php } ?>
                                    <?php if (ABPTF_Function::on_off('client_info')) { ?>
                                        <li data-tabs-target="#abptf_client_form"><span>📋</span><?php esc_html_e('Client Form', 'abp-transportforge'); ?></li>
                                    <?php } ?>
                                    <?php do_action('abptf_post_tab_menu', $post_infos); ?>
                                    <li data-tabs-target="#abptf_resource"><span>📚</span><?php esc_html_e('Resources', 'abp-transportforge'); ?></li>
                                </ul>
                            </div>
                            <div class="tab_content _panel_body">
                                <?php
                                    $this->general_configuration($post_infos);
                                    do_action('abptf_post_content', $post_infos);
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php ABPTF_Layout::load_admin_globally(); ?>
                </div>
                <?php
            }
            public function general_configuration($post_infos): void {
                $abptf_template = $post_infos['abptf_template'] ?? 'default';
                ?>
                <div class="tab_item" data-tabs="#abptf_general">
                    <h4 class="_abp_color_theme"><?php esc_html_e('General Configuration', 'abp-transportforge'); ?></h4>
                    <?php ABPTF_Layout::info_text('general_config'); ?>
                    <div class="_divider_xs"></div>
                    <div class="group_setting">
                        <div class="setting_item">
                            <div class="_fa_center">
                                <?php ABPTF_Layout::switch_checkbox('sale_continue', ($post_infos['sale_continue'] ?? 'on')); ?>
                                <span class="_abp_label"><?php esc_html_e('Sale continue?', 'abp-transportforge'); ?></span>
                            </div>
                            <div class="_divider_xxs"></div>
                            <?php ABPTF_Layout::info_text('sale_continue'); ?>
                        </div>
                        <div class="setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="_abp_label"><?php esc_html_e('Template', 'abp-transportforge'); ?></span>
                                <select class="_form_control " name="abptf_template" data-collapse-target required>
                                    <option disabled selected><?php esc_html_e('Please Select', 'abp-transportforge'); ?></option>
                                    <option value="default" <?php echo esc_attr($abptf_template == 'default' ? 'selected' : ''); ?>><?php esc_html_e('Default Template', 'abp-transportforge'); ?></option>
                                    <option value="light" <?php echo esc_attr($abptf_template == 'light' ? 'selected' : ''); ?>><?php esc_html_e('Light Template', 'abp-transportforge'); ?></option>
                                </select>
                            </label>
                            <div class="_divider_xxs"></div>
                            <?php ABPTF_Layout::info_text('abptf_template'); ?>
                        </div>
                        <?php if (ABPTF_Function::on_off('sku')) { ?>
                            <div class="setting_item">
                                <div class="_fj_between">
                                    <div class="_fa_center">
                                        <?php ABPTF_Layout::switch_checkbox('display_sku', ($post_infos['display_sku'] ?? 'off')); ?>
                                        <span class="_abp_label"><?php esc_html_e('ID/SKU', 'abp-transportforge'); ?></span>
                                    </div>
                                    <label>
                                        <input class="_form_control" name="post_sku" value="<?php echo esc_attr($post_infos['post_sku'] ?? ''); ?>" placeholder="<?php esc_attr_e('Transport ID', 'abp-transportforge'); ?>"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTF_Layout::info_text('post_sku'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTF_Function::on_off('post_icon')) { ?>
                            <div class="setting_item">
                                <divl class="_fj_between">
                                    <span class="_abp_label"><?php esc_html_e('Transport Icon', 'abp-transportforge'); ?></span>
                                    <?php do_action('abptf_add_icon', 'post_icon', ($post_infos['post_icon'] ?? '')); ?>
                                </divl>
                                <div class="_divider_xxs"></div>
                                <?php ABPTF_Layout::info_text('post_icon'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTF_Function::on_off('sub_title')) { ?>
                            <div class="setting_item">
                                <div class="_f_equal_f_wrap">
                                    <span class="_abp_label"><?php esc_html_e('Sub Title', 'abp-transportforge'); ?></span>
                                    <label>
                                        <textarea class="_form_control" name="sub_title" placeholder="<?php esc_attr_e('Transport Sub Title', 'abp-transportforge'); ?>"><?php echo esc_html($post_infos['sub_title'] ?? ''); ?></textarea>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTF_Layout::info_text('sub_title'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTF_Function::on_off('post_des')) { ?>
                            <div class="setting_item">
                                <label class="_f_equal_f_wrap">
                                    <span class="_abp_label"><?php esc_html_e('Short Description', 'abp-transportforge'); ?></span>
                                    <textarea class="_form_control" name="post_description" placeholder="<?php esc_attr_e('EX: Description', 'abp-transportforge'); ?>"><?php echo esc_html($post_infos['post_description'] ?? ''); ?></textarea>
                                </label>
                                <div class="_divider_xxs"></div>
                                <?php ABPTF_Layout::info_text('post_description'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTF_Function::on_off('organizer')) { ?>
                            <div class="setting_item">
                                <div class="_fj_between_fa_center">
                                    <div class="_fa_center">
                                        <?php ABPTF_Layout::switch_checkbox('display_organizer', ($post_infos['display_organizer'] ?? 'off')); ?>
                                        <span class="_abp_label"><?php echo esc_html(ABPTF_Function::organizer_label()); ?></span>
                                    </div>
                                    <div class="tax_organizer _group_content"><?php ABPTF_Organizer::organizer_selection($post_infos['abptf_organizer'] ?? ''); ?></div>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTF_Layout::info_text('display_organizer'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTF_Function::on_off('brand')) { ?>
                            <div class="setting_item">
                                <div class="_fj_between_fa_center">
                                    <div class="_fa_center">
                                        <?php ABPTF_Layout::switch_checkbox('display_brand', ($post_infos['display_brand'] ?? 'off')); ?>
                                        <span class="_abp_label"><?php echo esc_html(ABPTF_Function::brand_label()); ?></span>
                                    </div>
                                    <div class="tax_brand _group_content"><?php ABPTF_Brand::brand_selection($post_infos['abptf_organizer'] ?? ''); ?></div>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTF_Layout::info_text('display_brand'); ?>
                            </div>
                        <?php } ?>
                        <?php if (ABPTF_Function::on_off('category')) { ?>
                            <div class="setting_item">
                                <div class="_fj_between_fa_center">
                                    <div class="_fa_center">
                                        <?php ABPTF_Layout::switch_checkbox('display_category', ($post_infos['display_category'] ?? 'on')); ?>
                                        <span class="_abp_label"><?php echo esc_html(ABPTF_Function::category_label()); ?></span>
                                    </div>
                                    <?php ABPTF_Layout::button_global_popup('tax_category', __('Add New', 'abp-transportforge') . ' ' . ABPTF_Function::category_label()); ?>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTF_Layout::info_text('display_category'); ?>
                                <div class="_divider_xxs"></div>
                                <div class="tax_category">
                                    <?php ABPTF_Category::category_selection($post_infos['abptf_category'] ?? ''); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (ABPTF_Function::on_off('related')) { ?>
                            <div class="setting_item related_item">
                                <div class="_fj_between_fa_center">
                                    <span class="_abp_label"><?php esc_html_e('Related Transport', 'abp-transportforge'); ?></span>
                                    <div class="selection_area">
                                        <label>
                                            <input class="_form_control item_search" type="text" placeholder="<?php esc_attr_e('Search Related Transport ....', 'abp-transportforge'); ?>"/>
                                        </label>
                                        <div class="selection_list"></div>
                                    </div>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTF_Layout::info_text('related_item'); ?>
                                <div class="_divider_xxs"></div>
                                <div class="selected_area">
                                    <input type="hidden" name="related_item" value="<?php echo esc_attr($post_infos['related_item'] ?? ''); ?>"/>
                                    <div class="selected_list"></div>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (ABPTF_Function::on_off('feature')) { ?>
                            <div class="setting_item full_width post_feature">
                                <div class="_fj_between_fa_center">
                                    <span class="_abp_label"><?php esc_html_e('Feature', 'abp-transportforge'); ?></span>
                                    <div class="_group_content">
                                        <div class="selection_area">
                                            <label>
                                                <input class="_form_control item_search" type="text" placeholder="<?php esc_attr_e('Search feature ....', 'abp-transportforge'); ?>"/>
                                            </label>
                                            <div class="selection_list"></div>
                                        </div>
                                        <?php ABPTF_Layout::button_global_popup('option_feature', __('Add New', 'abp-transportforge') . ' ' . ABPTF_Function::feature_label()); ?>
                                    </div>
                                </div>
                                <div class="_divider_xxs"></div>
                                <?php ABPTF_Layout::info_text('post_feature'); ?>
                                <div class="_divider_xxs"></div>
                                <div class="selected_area">
                                    <input type="hidden" name="post_feature" value="<?php echo esc_attr($post_infos['post_feature'] ?? ''); ?>"/>
                                    <div class="selected_list"></div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="setting_item full_width">
                            <span class="_abp_label"><?php esc_html_e('Gallery', 'abp-transportforge'); ?></span>
                            <div class="_divider_xxs"></div>
                            <?php ABPTF_Layout::info_text('abptf_sliders'); ?>
                            <div class="_divider_xxs"></div>
                            <?php do_action('abptf_add_image_multiple', 'abptf_sliders', ($post_infos['abptf_sliders'] ?? '')); ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            //====================================//
            public function save_settings($post_id): void {
                if (!isset($_POST['abptf_post_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abptf_post_nonce'])), 'abptf_post_nonce')) {
                    return;
                }
                if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                    return;
                }
                if (!current_user_can('edit_post', $post_id)) {
                    return;
                }
                if (get_post_type($post_id) == ABPTF_Function::get_cpt()) {
                    //$post_int            = fn( $key, $default = 0 ) => isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
                    $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                    $post_textarea = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_textarea_field(wp_unslash($_POST[$key])) : $default;
                    $post_html = fn($key, $default = '') => isset($_POST[$key]) ? wp_kses_post(wp_unslash($_POST[$key])) : $default;
                    $post_int_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('absint', wp_unslash($_POST[$key])) : [];
                    $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                    //$post_textarea_array = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_textarea_field', wp_unslash( $_POST[ $key ] ) ) : [];
                    //$post_html_array     = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'wp_kses_post', wp_unslash( $_POST[ $key ] ) ) : [];
                    //$format_date         = fn( $date ) => $date ? gmdate( 'Y-m-d', strtotime( $date ) ) : '';
                    $route_infos = [];
                    $route_direction = [];
                    $bp = [];
                    $dp = [];
                    $stops = $post_int_array('stop_name');
                    $types = $post_array('stop_type');
                    $display_pd = $post_array('display_pd');
                    $times = $post_int_array('stop_time');
                    $count = count($stops);
                    for ($i = 0; $i < $count; $i++) {
                        $stop = $stops[$i] ?? '';
                        $type = $types[$i] ?? '';
                        if ($stop && $type) {
                            $route_infos[$i]['stop'] = $stop;
                            $route_infos[$i]['type'] = $type;
                            $route_infos[$i]['time'] = $times[$i] ?? '';
                            $route_infos[$i]['display_pd'] = $display_pd[$i] ?? '';
                            $route_direction[] = $stop;
                        }
                    }
                    $count = sizeof($route_infos);
                    if ($count > 0) {
                        $route_infos[0]['type'] = 'bp';
                        $route_infos[0]['display_drop'] = 'off';
                        $route_infos[0]['time'] = 0;
                        $route_infos[$count - 1]['type'] = 'dp';
                        foreach ($route_infos as $route_info) {
                            if ($route_info['type'] == 'bp') {
                                $bp[] = $route_info['stop'];
                            } elseif ($route_info['type'] == 'dp') {
                                $dp[] = $route_info['stop'];
                            } else {
                                $bp[] = $route_info['stop'];
                                $dp[] = $route_info['stop'];
                            }
                        }
                    }
                    /***********************************/
                    $display_return=$post_val('display_return', 'off');
                    $return_route_infos = [];
                    $return_route_direction = [];
                    $return_bp = [];
                    $return_dp = [];
                    if($display_return=='on') {
                        $stops = $post_int_array('return_stop_name');
                        $types = $post_array('return_stop_type');
                        $display_pd = $post_array('return_display_pd');
                        $times = $post_int_array('return_stop_time');
                        $count = count($stops);
                        for ($i = 0; $i < $count; $i++) {
                            $stop = $stops[$i] ?? '';
                            $type = $types[$i] ?? '';
                            if ($stop && $type) {
                                $return_route_infos[$i]['stop'] = $stop;
                                $return_route_infos[$i]['type'] = $type;
                                $return_route_infos[$i]['time'] = $times[$i] ?? '';
                                $return_route_infos[$i]['display_pd'] = $display_pd[$i] ?? '';
                                $return_route_direction[] = $stop;
                            }
                        }
                        $count = sizeof($return_route_infos);
                        if ($count > 0) {
                            $return_route_infos[0]['type'] = 'bp';
                            $return_route_infos[0]['display_drop'] = 'off';
                            $return_route_infos[0]['time'] = 0;
                            $return_route_infos[$count - 1]['type'] = 'dp';
                            foreach ($return_route_infos as $route_info) {
                                if ($route_info['type'] == 'bp') {
                                    $return_bp[] = $route_info['stop'];
                                } elseif ($route_info['type'] == 'dp') {
                                    $return_dp[] = $route_info['stop'];
                                } else {
                                    $return_bp[] = $route_info['stop'];
                                    $return_dp[] = $route_info['stop'];
                                }
                            }
                        }
                    }
                    /***********************************/
                    $operation_time = [];
                    $operation_times = $post_array('operation_time');
                    if (!empty($operation_times)) {
                        foreach ($operation_times as $value) {
                            if (!empty($value)) {
                                $operation_time[] = $value;
                            }
                        }
                    }
                    $day_time = [];
                    foreach (ABPTF_Layout::week_day() as $key => $day) {
                        $time = $post_val($key . '_time');
                        if ($time) {
                            $day_time[$key] = $time;
                        }
                    }
                    $date_time_ids = $post_array('date_wise_time_id');
                    $date_time = [];
                    if (!empty($date_time_ids)) {
                        foreach ($date_time_ids as $time_id) {
                            $date_wise_dates = $post_array('date_wise_date[' . $time_id . ']');
                            $date_wise_time = $post_array('date_wise_time[' . $time_id . ']');
                            if (!empty($date_wise_dates) && !empty($date_wise_time)) {
                                foreach ($date_wise_dates as $value) {
                                    if (!empty($value)) {
                                        $date_time[$time_id]['date'] = $value;
                                        foreach ($date_wise_time as $value2) {
                                            if (!empty($value2)) {
                                                $date_time[$time_id]['time'][] = $value2;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $time_info['operation_time'] = !empty($operation_time) ? $operation_time : ['00:00'];
                    $time_info['day_time'] = $day_time;
                    $time_info['date_times'] = $date_time;
                    $meta_info = [
                        'sale_continue' => $post_val('sale_continue', 'on'),
                        'abptf_template' => $post_val('abptf_template', 'default'),
                        'display_sku' => $post_val('display_sku', 'off'),
                        'post_sku' => $post_val('post_sku'),
                        'post_icon' => $post_val('post_icon'),
                        'sub_title' => $post_textarea('sub_title'),
                        'post_description' => $post_textarea('post_description'),
                        'display_organizer' => $post_val('display_organizer', 'off'),
                        'abptf_organizer' => $post_val('abptf_organizer'),
                        'display_brand' => $post_val('display_brand', 'off'),
                        'abptf_brand' => $post_val('abptf_brand'),
                        'display_category' => $post_val('display_category', 'on'),
                        'abptf_category' => $post_val('abptf_category'),
                        'related_item' => $post_val('related_item'),
                        'post_feature' => $post_val('post_feature'),
                        'abptf_sliders' => $post_val('abptf_sliders'),
                        //================//
                        'routing_infos' => $route_infos,
                        'route_direction' => $route_direction,
                        'abptf_bp' => $bp,
                        'abptf_dp' => $dp,
                        'display_return' => $display_return,
                        'return_routing_infos' => $return_route_infos,
                        'return_route_direction' => $return_route_direction,
                        'return_abptf_bp' => $return_bp,
                        'return_abptf_dp' => $return_dp,
                        //================//
                        'active_global_dates' => $post_val('active_global_dates', 'on'),
                        'abptf_dates' => apply_filters('abptf_get_date_array', []),
                        'time_infos' => $time_info,
                        'display_additional_services' => $post_val('display_additional_services', 'off'),
                        'active_global_additional' => $post_val('active_global_additional', 'on'),
                        'additional_services' => apply_filters('abptf_get_additional_array', []),
                        'display_client_form' => $post_val('display_client_form', 'off'),
                        'active_global_form' => $post_val('active_global_form', 'on'),
                        'abptf_forms' => apply_filters('abptf_get_form_array', []),
                        'display_faq' => $post_val('display_faq', 'on'),
                        'active_global_faq' => $post_val('active_global_faq', 'on'),
                        'abptf_faqs' => apply_filters('abptf_get_faq_array', []),
                        'display_tc' => $post_val('display_tc', 'on'),
                        'active_global_tc' => $post_val('active_global_tc', 'on'),
                        'abptf_tc' => $post_html('tc_content'),
                    ];
                    //=============tax================//
                    if (get_option('woocommerce_calc_taxes') == 'yes') {
                        $meta_info['_tax_status'] = $post_val('_tax_status', 'none');
                        $meta_info['_tax_class'] = $post_val('_tax_class');
                    }
                    //=============================//
                    $meta_info = apply_filters('abptf_meta_info_update', $meta_info, $post_id);
                    if (sizeof($meta_info) > 0) {
                        foreach ($meta_info as $key => $value) {
                            update_post_meta($post_id, sanitize_key($key), $value);
                        }
                    }
                    //=============================//
                    if (!empty(get_the_title($post_id))) {
                        ABPTF_Function::update_dates($post_id);
                        ABPTF_Function::update_time_slot($post_id);
                        ABPTF_Function::update_global_data($post_id);
                    }
                }
            }
            public function post_permanent_remove(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
                if ($post_id <= 0) {
                    wp_send_json_error(['html' => '', 'msg' => __('Invalid Post ID ..... !! ', 'abp-transportforge')], 400);
                }
                $link_wc_id = absint(ABPTF_Function::get_post_info($post_id, 'link_wc_id'));
                if ($link_wc_id > 0) {
                    wp_delete_post($link_wc_id, true);
                }
                wp_delete_post($post_id, true);
                global $wpdb;
                $table_name = $wpdb->prefix . 'abptf_property';
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->delete($table_name, ['post_id' => $post_id], ['%d']);
                wp_send_json_success(['html' => '', 'msg' => __('Post permanently removed. ..... !! ', 'abp-transportforge')]);
            }
            public function post_move_trash(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
                if ($post_id > 0) {
                    $link_wc_id = absint(ABPTF_Function::get_post_info($post_id, 'link_wc_id'));
                    if ($link_wc_id > 0) {
                        wp_trash_post($link_wc_id);
                    }
                    wp_trash_post($post_id);
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'abptf_property';
                    $current_status = get_post_status($post_id) ? get_post_status($post_id) : 'trash';
                    $data = ['status' => $current_status];
                    $where = ['post_id' => $post_id];
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->update($table_name, $data, $where, ['%s', '%s', '%s'], ['%d']);
                    wp_send_json_success(['html' => '', 'msg' => __('Post moved to trash successfully...... !! ', 'abp-transportforge')]);
                }
                wp_send_json_error(['html' => '', 'msg' => __('Invalid Post ID ..... !! ', 'abp-transportforge')], 400);
            }
            public function post_restore(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
                if ($post_id > 0) {
                    $link_wc_id = absint(ABPTF_Function::get_post_info($post_id, 'link_wc_id'));
                    if ($link_wc_id > 0) {
                        wp_untrash_post($link_wc_id);
                    }
                    wp_untrash_post($post_id);
                    $updated_post = [
                        'ID' => $post_id,
                        'post_status' => 'publish',
                    ];
                    wp_update_post($updated_post);
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'abptf_property';
                    $current_status = get_post_status($post_id) ? get_post_status($post_id) : 'publish';
                    $data = ['status' => $current_status];
                    $where = ['post_id' => $post_id];
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->update($table_name, $data, $where, ['%s', '%s', '%s'], ['%d']);
                    wp_send_json_success(['html' => '', 'msg' => __('Property restored successfully...... !! ', 'abp-transportforge')]);
                }
                wp_send_json_error(['html' => '', 'msg' => __('Invalid Post ID ..... !! ', 'abp-transportforge')], 400);
            }
            public function reload_post_list(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                $filter_args = $post_array('filter_args');
                ob_start();
                $this->post_table($filter_args);
                $table_html = ob_get_clean();
                wp_send_json_success([
                    'html' => $table_html,
                    'msg' => __('Post List Loaded successfully...... !! ', 'abp-transportforge')
                ]);
            }
        }
        new ABPTF_Post();
    }

