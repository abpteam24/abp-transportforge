<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTF_Seat_Plan')) {
        class ABPTF_Seat_Plan {
            public function __construct() {
                add_action('abptf_load_sp', array($this, 'load_sp'));
                /************************************/
                add_action('wp_ajax_abptf_add_sp', [$this, 'add_sp']);
                add_action('wp_ajax_abptf_add_view_sp', [$this, 'view_sp']);
                add_action('wp_ajax_abptf_save_sp', [$this, 'save_sp']);
                add_action('wp_ajax_abptf_delete_sp', [$this, 'delete_sp']);
                /************************************/
                add_action('wp_ajax_abptf_add_ticket_type', array($this, 'add_ticket_type'));
                add_action('wp_ajax_abptf_save_ticket_type', array($this, 'save_ticket_type'));
                add_action('wp_ajax_abptf_delete_ticket_type', array($this, 'delete_ticket_type'));
                /************************************/
                add_action('wp_ajax_abptf_add_decor_item', array($this, 'add_decor_item'));
                add_action('wp_ajax_abptf_save_decor_item', array($this, 'save_decor_item'));
                add_action('wp_ajax_abptf_delete_decor_item', array($this, 'delete_decor_item'));
            }
            public function load_sp(): void {
                ?>
                <div class="group_setting">
                    <?php if (ABPTF_Function::on_off('ticket_type')) { ?>
                        <div class="ticket_configuration setting_item">
                            <div class="_fj_between_fa_center">
                                <h5 class="_abp"><?php esc_html_e('Ticket Type List', 'abp-transportforge'); ?></h5>
                                <?php ABPTF_Layout::button_global_popup('ticket_type', __('Add New Ticket Type', 'abp-transportforge')); ?>
                            </div>
                            <div class="_divider_xxs"></div>
                            <div class="ticket_type">
                                <?php $this->ticket_list(); ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if (ABPTF_Function::on_off('sp')) { ?>
                        <div class="decor_configuration setting_item">
                            <div class="_fj_between_fa_center">
                                <h5 class="_abp"><?php esc_html_e('Others/Decor Item List', 'abp-transportforge'); ?></h5>
                                <?php ABPTF_Layout::button_global_popup('decor_item', __('Add New Decor Item', 'abp-transportforge')); ?>
                            </div>
                            <div class="_divider_xxs"></div>
                            <div class="decor_item">
                                <?php $this->decor_list(); ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php if (ABPTF_Function::on_off('sp')) { ?>
                    <div id="abptf_sp_builder">
                        <div class="setting_item sp_list">
                            <?php $this->sp_list(); ?>
                        </div>
                        <div class="sp_builder_area"></div>
                    </div>
                    <?php
                }
            }
            /************************************/
            public function sp_list(): void {
                $sp_infos = self::get_sp();
                $options = ABPTF_Function::get_option('abptf_ticket');
                ?>
                <div class="_fj_between">
                    <h5 class="_abp_d_flex">💺 <?php esc_html_e('Seat Plan', 'abp-transportforge'); ?><sup class="_mar_l_xs_circle_icon_xs"><?php echo esc_html(self::get_sp('', true)); ?></sup></h5>
                    <button class="_btn_light_active_xs" onclick="abptf_sp_add()">
                        <span class="_mar_r_xxs">➕</span><?php esc_html_e('Add New Seat Plan', 'abp-transportforge'); ?>
                    </button>
                </div>
                <div class="_divider_xs"></div>
                <?php if (!empty($sp_infos)) { ?>
                    <table class="_abp">
                        <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'abp-transportforge'); ?></th>
                            <th><?php esc_html_e('Background', 'abp-transportforge'); ?></th>
                            <th><?php esc_html_e('Seat Plan Name', 'abp-transportforge'); ?></th>
                            <th><?php esc_html_e('Seat Information', 'abp-transportforge'); ?></th>
                            <th><?php esc_html_e('Total Seats', 'abp-transportforge'); ?></th>
                            <th><?php esc_html_e('Dimension (R x C)', 'abp-transportforge'); ?></th>
                            <th><?php esc_html_e('Cell Dimension Width X Height X Gap in px', 'abp-transportforge'); ?></th>
                            <th><?php esc_html_e('Actions', 'abp-transportforge'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($sp_infos as $sp_info) { ?>
                            <tr>
                                <td><?php echo esc_html($sp_info['id'] ?? ''); ?></td>
                                <th><?php $bg_image = $sp_info['bg_image'] ?? '';
                                        if (!empty($bg_image) && $bg_image > 0) {
                                            ABPTF_Layout::image('', $bg_image, '', '_max_100');
                                        } ?></th>
                                <th><?php echo esc_html($sp_info['name'] ?? ''); ?></th>
                                <th><?php
                                        $meta_info = json_decode($sp_info['seat_info'] ?? '', true) ?: [];
                                        if (ABPTF_Function::on_off('ticket_type') && sizeof($options) > 0) { ?>
                                            <div class="_group_list">
                                                <?php foreach ($options as $key => $item) {
                                                    $label = $item['label'] ?? '';
                                                    if (!empty($label) && array_key_exists($key, $meta_info)) { ?>
                                                        <div class="_list_item">
                                                            <h6 class="_abp" style="color:<?php echo esc_attr($item['color'] ?? ''); ?>">
                                                                <?php ABPTF_Layout::image_icon($item['icon'] ?? '');
                                                                    echo esc_html($label); ?>
                                                            </h6>
                                                            <span class="_mar_l_xs_circle_icon_xs"><?php echo esc_html($meta_info[$key]); ?></span>
                                                        </div>
                                                        <?php
                                                    }
                                                } ?>
                                            </div>
                                        <?php } else { ?>
                                            <div class="_fj_between"><h6><?php esc_html_e('Ticket/Seat : ', 'abp-transportforge'); ?></h6><span class="_mar_l_xs_circle_icon_xs"><?php echo esc_html($sp_info['total_seats'] ?? 0); ?></span></div>
                                        <?php } ?></th>
                                <th><?php echo esc_html($sp_info['total_seats'] ?? 0); ?></th>
                                <th><?php echo esc_html(($sp_info['rows_count'] ?? 0) . ' X ' . ($sp_info['cols_count'] ?? 0)); ?></th>
                                <th><?php echo esc_html(($sp_info['cell_width'] ?? 50) . ' X ' . ($sp_info['cell_height'] ?? 50) . ' X ' . ($sp_info['gap'] ?? 0)); ?></th>
                                <td>
                                    <div class="_group_content">
                                        <button type="button" class="_btn_light_theme_xxs" onclick="abptf_popup_open_global('view_sp','<?php echo esc_attr($sp_info['id'] ?? ''); ?>')" title="<?php echo esc_attr__('View : ', 'abp-transportforge') . ' ' . esc_attr($sp_info['name'] ?? ''); ?>">👁️</button>
                                        <button type="button" class="_btn_light_navy_blue_xxs" onclick="abptf_sp_add('<?php echo esc_attr($sp_info['id'] ?? ''); ?>','1')" title="<?php echo esc_attr__('Copy/Clone : ', 'abp-transportforge') . ' ' . esc_attr($sp_info['name'] ?? ''); ?>">🔁</button>
                                        <button type="button" class="_btn_light_yellow_xxs" onclick="abptf_sp_add('<?php echo esc_attr($sp_info['id'] ?? ''); ?>')" title="<?php echo esc_attr__('Edit : ', 'abp-transportforge') . ' ' . esc_attr($sp_info['name'] ?? ''); ?>">✍️</button>
                                        <button type="button" class="_btn_light_danger_xxs" onclick="abptf_sp_delete('<?php echo esc_attr($sp_info['id'] ?? ''); ?>')" title="<?php echo esc_attr__('Permanent Remove : ', 'abp-transportforge') . ' ' . esc_attr($sp_info['name'] ?? ''); ?>">❌</button>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <?php
                } else {
                    ABPTF_Layout::layout_warning_info_xs('no_sp');
                }
            }
            public function add_sp(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                ob_start();
                $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : '';
                $clone = isset($_POST['clone']) ? absint(wp_unslash($_POST['clone'])) : '';
                $sp_info = [];
                if (!empty($id)) {
                    $row = self::get_sp($id);
                    if (!empty($row)) {
                        $sp_info = current($row);
                    }
                }
                $id = !empty($clone) && $clone > 0 ? '' : $id;
                //echo '<pre>';print_r($sp);echo '</pre>';
                $bg_image = $sp_info['bg_image'] ?? '';
                $img_url = !empty($bg_image) && $bg_image > 0 ? ABPTF_Function::get_image_url('', $bg_image) : '';
                ?>
                <div class="sp_section_card_xs _p_relative">
                    <div class="info_text ">
                        🖱 <strong class="_abp"><?php esc_html_e('Drag Cells', 'abp-transportforge'); ?></strong>→
                        <?php esc_html_e('to Clone/Copy & range select', 'abp-transportforge'); ?>
                        <strong class="_abp _mar_lr_xs"> | </strong>
                        <strong class="_abp"><?php esc_html_e('Double-Click', 'abp-transportforge'); ?></strong>→
                        <?php esc_html_e('to edit Row/Col Spanning in the Left Panel.', 'abp-transportforge'); ?>
                        <strong class="_abp _mar_lr_xs"> | </strong>
                        <strong class="_abp"><?php esc_html_e('Ctrl+Click ', 'abp-transportforge'); ?></strong>→
                        <?php esc_html_e('To particular Item select', 'abp-transportforge'); ?>
                        <strong class="_abp _mar_lr_xs"> | </strong>
                        <strong class="_abp"><?php esc_html_e('Shift+Click', 'abp-transportforge'); ?></strong>→
                        <?php esc_html_e('To any range select', 'abp-transportforge'); ?>
                    </div>
                </div>
                <div class="_d_flex_gap_xs">
                    <div class="_max_350">
                        <div class="sp_section_card_xs _p_relative">
                            <label class="_f_equal">
                                <span class="_abp_label"><?php esc_html_e('Plan Name', 'abp-transportforge'); ?></span>
                                <input type="text" class="_form_control sp_name" value="<?php echo esc_attr($sp_info['name'] ?? uniqid('sp_')); ?>" placeholder="<?php esc_attr_e('EX: Scania AC Double Decker', 'abp-transportforge'); ?>">
                            </label>
                            <div class="_divider_xxs"></div>
                            <div class="_f_equal _fj_between">
                                <span class="_abp_label"><?php esc_html_e('Bg Image', 'abp-transportforge'); ?></span>
                                <?php do_action('abptf_image_selection', '', $bg_image, '.sp_canvas'); ?>
                            </div>
                            <div class="_divider_xxs"></div>
                            <div class="_fj_between">
                                <span class="_abp_label"><?php esc_html_e('Bg Color', 'abp-transportforge'); ?></span>
                                <label>
                                    <input type="text" name="bg_color" disabled class="_form_control abp_color_picker" value="<?php echo esc_attr($sp_info['color'] ?? ''); ?>" data-default-color="#fff"/>
                                </label>
                            </div>
                        </div>
                        <div class="sp_section_card_xs">
                            <div class="_fd_column">
                                <span class="_abp_label_mar_b_xxs_text_center_color_burnt_orange"><?php esc_html_e('Dimension (Rows X Columns)', 'abp-transportforge'); ?></span>
                                <div class="_group_content">
                                    <label><input type="number" class="_form_control_min_auto validation_number sp_rows" value="<?php echo esc_attr($sp_info['rows_count'] ?? 10); ?>" onchange="abptf_sp_row_column()"></label>
                                    <label><input type="number" class="_form_control_min_auto validation_number sp_cols" value="<?php echo esc_attr($sp_info['cols_count'] ?? 10); ?>" onchange="abptf_sp_row_column()"></label>
                                </div>
                            </div>
                            <div class="_divider_xxs"></div>
                            <div class="_group_content _w_full _f_equal">
                                <button type="button" class="_btn_light_warning_xs" onclick="abptf_sp_row_last_remove()"><span class="_mar_r_xxs">➖</span> <?php esc_html_e(' Remove Last Row', 'abp-transportforge'); ?></button>
                                <button type="button" class="_btn_light_warning_xs" onclick="abptf_sp_col_last_remove()"><span class="_mar_r_xxs">➖</span> <?php esc_html_e('Remove Last Col', 'abp-transportforge'); ?></button>
                            </div>
                        </div>
                        <div class="sp_section_card_xs">
                            <span class="_abp_label_mar_b_xxs_text_center_color_burnt_orange"><?php esc_html_e('Cell Dimension Width X Height X Gap in px', 'abp-transportforge'); ?></span>
                            <div class="_group_content">
                                <label><input type="number" class="_form_control_min_auto validation_number sp_width" min="20" value="<?php echo esc_attr($sp_info['cell_width'] ?? 50); ?>"></label>
                                <label><input type="number" class="_form_control_min_auto validation_number sp_height" min="20" value="<?php echo esc_attr($sp_info['cell_height'] ?? 50); ?>"></label>
                                <label><input type="number" class="_form_control_min_auto validation_number sp_gap" min="0" value="<?php echo esc_attr($sp_info['gap'] ?? 0); ?>"></label>
                                <button type="button" class="_btn_green_pale_xs" onclick="abptf_sp_cell_wh()"><?php esc_html_e('Apply', 'abp-transportforge'); ?></button>
                            </div>
                            <div class="span_control">
                                <div class="_divider_xxs"></div>
                                <span class="_abp_label_mar_b_xxs_text_center_color_burnt_orange"><?php esc_html_e('Cell  Control (Cols × Rows x Text x Font size)', 'abp-transportforge'); ?></span>
                                <div class="_group_content">
                                    <label><input type="number" class="_form_control_min_auto validation_number col_span" value="1" min="1"></label>
                                    <label><input type="number" class="_form_control_min_auto validation_number row_span" value="1" min="1"></label>
                                    <label><input type="text" class="_form_control_min_auto validation_name custom_label" value="" placeholder="<?php esc_attr_e('Custom Text', 'abp-transportforge'); ?>"></label>
                                    <label><input type="number" class="_form_control_min_auto validation_number custom_font_size" value="12" min="8"></label>
                                    <button type="button" class="_btn_green_pale_xs" onclick="abptf_sp_cell_design()"><?php esc_html_e('Apply', 'abp-transportforge'); ?></button>
                                </div>
                            </div>
                        </div>
                        <div class="sp_section_card_xs">
                            <div class="_group_content_f_equal _w_full">
                                <button type="button" class="sp_tab _btn_light_active_xs abp_active" data-tab="sp_tab_seats"><?php esc_html_e('Seats', 'abp-transportforge'); ?> (<strong class="total_seat">0</strong>)</button>
                                <button type="button" class="sp_tab _btn_light_active_xs" data-tab="sp_tab_others"><?php esc_html_e('Others / Decor', 'abp-transportforge'); ?> (<strong class="total_others">0</strong>)</button>
                            </div>
                            <div id="sp_tab_seats" class="sp_tab_content abp_active">
                                <div class="sp_group_seats"></div>
                            </div>
                            <div id="sp_tab_others" class="sp_tab_content">
                                <div class="sp_group_others"></div>
                            </div>
                        </div>
                        <div class="sp_section_card_xs _group_content_f_equal">
                            <button type="button" class="_btn_active_xs" onclick="abptf_sp_save()"><span class="_mar_r_xxs">💾</span><?php esc_html_e('Save Seat Plan', 'abp-transportforge'); ?></button>
                            <button type="button" class="_btn_warning_xs" onclick="abptf_sp_clear()"><span class="_mar_r_xxs">❌</span><?php esc_html_e('Clear Layout', 'abp-transportforge'); ?></button>
                        </div>
                    </div>
                    <div class="sp_builder">
                        <div class="sp_canvas" style="background-image: url('<?php echo esc_url($img_url); ?>'); background-color: url('<?php echo esc_url($sp_info['color'] ?? 'transparent'); ?>');gap: <?php echo esc_attr($sp_info['gap'] ?? 0); ?>px;"></div>
                    </div>
                </div>
                <div id="sp_saved_data" data-id="<?php echo esc_attr($id); ?>" data-layout="<?php echo esc_attr($sp_info['layout_data'] ?? '{}'); ?>" data-meta="<?php echo esc_attr($sp_info['seat_info'] ?? '{}'); ?>" style="display:none;"></div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => __('Seat Plan Loaded Successfully .....! ', 'abp-transportforge')]);
            }
            public function view_sp(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                ob_start();
                $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : '';
                $sp_info = [];
                if (!empty($id)) {
                    $row = self::get_sp($id);
                    if (!empty($row)) {
                        $sp_info = current($row);
                    }
                }
                $cell_width = $sp_info['cell_width'] ?? 50;
                $cell_height = $sp_info['cell_height'] ?? 50;
                $gap = $sp_info['gap'] ?? 0;
                $bg_image = $sp_info['bg_image'] ?? '';
                $img_url = !empty($bg_image) && $bg_image > 0 ? ABPTF_Function::get_image_url('', $bg_image) : '';
                $bg_color = $sp_info['color'] ?? '';
                $layout = json_decode($sp_info['layout_data'] ?? '', true) ?: [];
                // echo '<pre>';                print_r($layout);                echo '</pre>';
                $cols = intval($sp_info['cols_count'] ?? 10);
                $hidden_cells = [];
                foreach ($layout as $index => $cell) {
                    $c_span = intval($cell['width_ratio'] ?? 1);
                    $r_span = intval($cell['height_ratio'] ?? 1);
                    if ($c_span > 1 || $r_span > 1) {
                        for ($r = 0; $r < $r_span; $r++) {
                            for ($c = 0; $c < $c_span; $c++) {
                                if ($r === 0 && $c === 0)
                                    continue;
                                $target_idx = $index + ($r * $cols) + $c;
                                $hidden_cells[$target_idx] = true;
                            }
                        }
                    }
                }
                ?>
                <div class="sp_section_card_xs _w_300">
                    <div class="_fj_between">
                        <span class="_abp_label"><?php esc_html_e('Plan Name', 'abp-transportforge'); ?></span>
                        <span class="_abp_label"><?php echo esc_html($sp_info['name'] ?? ''); ?></span>
                    </div>
                    <div class="_divider_xxs"></div>
                    <div class="_fj_between_f_equal">
                        <span class="_abp_label"><?php esc_html_e('Bg Image', 'abp-transportforge'); ?></span>
                        <?php if (!empty($bg_image)) {
                            ABPTF_Layout::image('', $bg_image);
                        } else { ?>
                            <span class="_abp_label"><?php esc_html_e('None', 'abp-transportforge'); ?></span>
                        <?php } ?>
                    </div>
                    <div class="_divider_xxs"></div>
                    <div class="_fj_between">
                        <span class="_abp_label"><?php esc_html_e('Bg Color', 'abp-transportforge'); ?></span>
                        <?php if (!empty($bg_color)) { ?>
                            <span class="_circle_icon_xs" style="background-color: <?php echo esc_attr($bg_color); ?>"></span>
                        <?php } else { ?>
                            <span class="_abp_label"><?php esc_html_e('None', 'abp-transportforge'); ?></span>
                        <?php } ?>
                    </div>
                    <div class="_divider_xxs"></div>
                    <div class="_fj_between">
                        <span class="_abp_label"><?php esc_html_e('Dimension Rows', 'abp-transportforge'); ?></span>
                        <span class="_abp_label"><?php echo esc_attr($sp_info['rows_count'] ?? ''); ?></span>
                    </div>
                    <div class="_divider_xxs"></div>
                    <div class="_fj_between">
                        <span class="_abp_label"><?php esc_html_e('Dimension Columns', 'abp-transportforge'); ?></span>
                        <span class="_abp_label"><?php echo esc_attr($sp_info['cols_count'] ?? ''); ?></span>
                    </div>
                    <div class="_divider_xxs"></div>
                    <div class="_fj_between">
                        <span class="_abp_label"><?php esc_html_e('Cell Width', 'abp-transportforge'); ?></span>
                        <span class="_abp_label"><?php echo esc_attr($sp_info['cell_width'] ?? 50); ?>PX</span>
                    </div>
                    <div class="_divider_xxs"></div>
                    <div class="_fj_between">
                        <span class="_abp_label"><?php esc_html_e('Cell Height', 'abp-transportforge'); ?></span>
                        <span class="_abp_label"><?php echo esc_attr($sp_info['cell_height'] ?? 50); ?>PX</span>
                    </div>
                    <div class="_divider_xxs"></div>
                    <div class="_fj_between">
                        <h5 class="_abp"><?php esc_html_e('Total Seat', 'abp-transportforge'); ?></h5>
                        <h5 class="_abp_color_theme"><?php echo esc_attr($sp_info['total_seats'] ?? 0); ?></h5>
                    </div>
                    <div class="_divider_xxs"></div>
                    <?php $options = ABPTF_Function::get_option('abptf_ticket');
                        $meta_info = json_decode($sp_info['seat_info'] ?? '', true) ?: [];
                        if (ABPTF_Function::on_off('ticket_type') && sizeof($options) > 0) { ?>
                            <div class="_group_list">
                                <?php foreach ($options as $key => $item) {
                                    $label = $item['label'] ?? '';
                                    if (!empty($label) && array_key_exists($key, $meta_info)) { ?>
                                        <div class="_list_item">
                                            <h6 class="_abp" style="color:<?php echo esc_attr($item['color'] ?? ''); ?>">
                                                <?php ABPTF_Layout::image_icon($item['icon'] ?? '');
                                                    echo esc_html($label); ?>
                                            </h6>
                                            <span class="_mar_l_xs_circle_icon_xs"><?php echo esc_html($meta_info[$key]); ?></span>
                                        </div>
                                        <?php
                                    }
                                } ?>
                            </div>
                        <?php } ?>
                </div>
                <div class="sp_builder_area sp_section_card_xs">
                    <div class="sp_builder_grid" style="grid-template-columns: repeat(<?php echo esc_attr($cols); ?>, 1fr); background-image: url('<?php echo esc_url($img_url); ?>'); background-color: <?php echo esc_attr($bg_color); ?>;gap: <?php echo esc_attr($gap); ?>px;">
                        <?php foreach ($layout as $index => $cell) {
                            if (isset($hidden_cells[$index]))
                                continue;
                            $c_span = intval($cell['width_ratio'] ?? 1);
                            $r_span = intval($cell['height_ratio'] ?? 1);
                            $rotate = intval($cell['rotate'] ?? 0);
                            $color = $cell['color'] ?? '';
                            $fs = $cell['fs'] ?? 12;
                            $is_seat = ($cell['type'] === 'seat');
                            $class = $is_seat ? "sp_cell available" : "sp_decor";
                            $style = "background: {$cell['color']}; grid-column: span {$c_span}; grid-row: span {$r_span}; min-width:{$cell_width}px;min-height:{$cell_height}px; border:1px solid  {$color};font-size:{$fs}px;";
                            $icon_image = $cell['icon'] ?? '';
                            $image = '';
                            if (!empty($icon_image)) {
                                if (is_numeric($icon_image)) {
                                    $image = ABPTF_Function::get_image_url('', $icon_image);
                                }
                            }
                            ?>
                            <div class="<?php echo esc_attr($class); ?>" style="<?php echo esc_attr($style); ?>" data-name="<?php echo esc_attr($cell['name'] ?? ''); ?>">
                                <div class="cell_content <?php echo esc_attr($rotate ? "rotate-{$rotate}" : ""); ?>" style="background-image: url('<?php echo esc_url($image); ?>'); background-color: <?php echo esc_attr($color); ?>; ">
                                    <?php if (!empty($image)) { ?>
                                        <span style="color: <?php echo esc_attr($color); ?>"><?php echo esc_html($cell['name'] ?? ''); ?></span>
                                    <?php } else { ?>
                                        <?php ABPTF_Layout::image_icon(($cell['icon'] ?? ''), ''); ?>
                                        <span><?php echo esc_html($cell['name'] ?? ''); ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => __('Seat Plan Loaded Successfully .....! ', 'abp-transportforge')]);
            }
            public function save_sp(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                global $wpdb;
                $table_name = $wpdb->prefix . 'abptf_sp';
                $post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
                $post_int = fn($key, $default = 0) => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                $post_json = function ($key) {
                    if (!isset($_POST[$key])) {
                        return array();
                    }
                    $raw_data = json_decode(wp_unslash($_POST[$key]), true);
                    if (!is_array($raw_data)) {
                        return array();
                    }
                    return array_map(function ($item) {
                        return is_array($item) ? array_map('sanitize_text_field', $item) : sanitize_text_field($item);
                    }, $raw_data);
                };
                $id = $post_int('id');
                $layout_data = $post_json('layout_data');
                $total_seats = 0;
                foreach ($layout_data as $cell) {
                    if (isset($cell['type']) && $cell['type'] === 'seat' && !empty($cell['name'])) {
                        $total_seats++;
                    }
                }
                $data = [
                    'name' => $post_val('name', uniqid('sp_')),
                    'rows_count' => $post_int('rows', 10),
                    'cols_count' => $post_int('cols', 10),
                    'cell_width' => $post_int('width', 50),
                    'cell_height' => $post_int('height', 50),
                    'gap' => $post_int('gap', 0),
                    'total_seats' => $total_seats,
                    'layout_data' => wp_json_encode($layout_data),
                    'seat_info' => wp_json_encode($post_json('seat_info')),
                    'bg_image' => $post_val('bg_image', ''),
                    'color' => $post_val('color', '')
                ];
                if ($id > 0) {
                    $wpdb->update($table_name, $data, ['id' => $id]);
                    wp_send_json_success(['msg' => 'Seat Plan Updated Successfully!', 'type' => 'success']);
                } else {
                    $wpdb->insert($table_name, $data);
                    wp_send_json_success(['msg' => 'Seat Plan Created Successfully!', 'type' => 'success']);
                }
            }
            public function delete_sp(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
                if (!empty($id) && $id > 0) {
                    global $wpdb;
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->delete($wpdb->prefix . 'abptf_sp', ['id' => $id], ['%d']);
                }
                ob_start();
                $this->sp_list();
                $html_content = ob_get_clean();
                wp_send_json_success(['html' => $html_content, 'msg' => __('Seat Plan deleted Successfully ..... !! ', 'abp-transportforge'), 'type' => 'success'], 200);
            }
            public static function get_sp($id = '', $count = false) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'abptf_sp';
                $cache_key = 'abptf_sp_' . md5((string)$id . ($count ? '_count' : '_all'));
                $abptf_sp = wp_cache_get($cache_key);
                if (false !== $abptf_sp) {
                    return $abptf_sp;
                }
                $conditions = [];
                $params = [];
                if (!empty($id)) {
                    $conditions[] = "id = %d";
                    $params[] = (int)$id;
                }
                $select = $count ? "COUNT(*)" : "*";
                $where = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $sql = "SELECT $select FROM $table_name $where ORDER BY id ASC";
                if (!empty($params)) {
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                    $final_query = $wpdb->prepare($sql, ...$params);
                } else {
                    $final_query = $sql;
                }
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                if ($count) {
                    $results = $wpdb->get_var($final_query);
                } else {
                    $results = $wpdb->get_results($final_query, ARRAY_A);
                }
                wp_cache_set($cache_key, $results);
                return $results;
            }
            /************************************/
            public function ticket_list(): void {
                $options = ABPTF_Function::get_option('abptf_ticket');
                //echo '<pre>';                print_r($options);                echo '</pre>';
                if (sizeof($options) > 0) { ?>
                    <div class="_group_list">
                        <?php foreach ($options as $key => $item) {
                            $label = $item['label'] ?? '';
                            $prefix = $item['prefix'] ?? '';
                            if (!empty($label)) { ?>
                                <div class="_list_item">
                                    <h6 class="_abp" style="color:<?php echo esc_attr($item['color'] ?? ''); ?>">
                                        <?php ABPTF_Layout::image_icon($item['icon'] ?? '');
                                            echo esc_html($label . ' ' . (!empty($prefix) ? '(' . $prefix . ')' : '')); ?>
                                    </h6>
                                    <div class="_group_content">
                                        <button type="button" class="_btn_light_yellow_xxs" onclick="abptf_popup_open_global('ticket_type','<?php echo esc_attr($key); ?>')" title="<?php echo esc_attr__('Edit : ', 'abp-transportforge') . ' ' . esc_attr($label); ?>">✍️</button>
                                        <button type="button" class="_btn_light_danger_xxs" onclick="abptf_delete_global('ticket_type','<?php echo esc_attr($key); ?>')" title="<?php echo esc_attr__('Trash : ', 'abp-transportforge') . ' ' . esc_attr($label); ?>">❌</button>
                                    </div>
                                </div>
                                <?php
                            }
                        } ?>
                    </div>
                <?php } else {
                    ABPTF_Layout::layout_warning_info_xs('no_ticket_type');
                }
            }
            public function add_ticket_type(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                ob_start();
                $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : '';
                $options = ABPTF_Function::get_option('abptf_ticket');
                $options = is_array($options) ? $options : [];
                $ticket = !empty($id) ? ($options[$id] ?? []) : []; ?>
                <div class="abp_form">
                    <h5 class="_abp"><?php esc_html_e('Ticket Type List', 'abp-transportforge'); ?></h5>
                    <?php ABPTF_Layout::info_text('ticket_image');
                        ABPTF_Layout::info_text('ticket_name');
                        ABPTF_Layout::info_text('ticket_color');
                        ABPTF_Layout::info_text('ticket_prefix'); ?>
                    <div class="_divider_xxs"></div>
                    <div class="configuration_content">
                        <table class="_abp ">
                            <thead>
                            <tr>
                                <th><?php esc_html_e('image/Icon', 'abp-transportforge'); ?></th>
                                <th><?php esc_html_e('Ticket Name', 'abp-transportforge'); ?><sup class="_color_required">*</sup></th>
                                <th><?php esc_html_e('Color', 'abp-transportforge'); ?></th>
                                <th><?php esc_html_e('Prefix', 'abp-transportforge'); ?></th>
                                <th class="_w_10"><?php esc_html_e('Action', 'abp-transportforge'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="insertable_area sortable_area">
                            <?php self::form_ticket($ticket, $id); ?>
                            </tbody>
                        </table>
                        <div class="_divider_xs"></div>
                        <div class="_fj_between">
                            <?php ABPTF_Layout::button_add_xs(__('Add New Ticket Type Item', 'abp-transportforge')); ?>
                            <?php ABPTF_Layout::button_global_save('ticket_type', __('Save Ticket Types', 'abp-transportforge')); ?>
                        </div>
                        <div class="abp_hidden">
                            <table class="_abp">
                                <tbody class="hidden_content">
                                <?php self::form_ticket(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => __('Ticket Type Form Loaded Successfully .....! ', 'abp-transportforge')]);
            }
            public function save_ticket_type(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $post_int = fn($key, $default = 0) => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
                $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                $options = ABPTF_Function::get_option('abptf_ticket');
                $options = is_array($options) ? $options : [];
                $ids = $post_array('id');
                $names = $post_array('name');
                $icon = $post_array('icon');
                $color = $post_array('color');
                $prefix = $post_array('prefix');
                $post_id = $post_int('post_id');
                if (!empty($names)) {
                    foreach ($names as $key => $name) {
                        if ($name !== '') {
                            $old_id = isset($ids[$key]) ? (int)$ids[$key] : '';
                            if (!empty($old_id) && isset($options[$old_id])) {
                                $id = $old_id;
                            } else {
                                $id = 1;
                                while (isset($options[$id])) {
                                    $id++;
                                }
                            }
                            $options[$id] = [
                                'label' => $name,
                                'icon' => $icon[$key] ?? '',
                                'color' => $color[$key] ?? '',
                                'prefix' => $prefix[$key] ?? '',
                            ];
                        }
                    }
                }
                if (empty($options)) {
                    $options[1]['label'] = 'Ticket/Seat';
                }
                update_option('abptf_ticket', $options);
                $html = '';
                if (empty($post_id) || $post_id <= 0) {
                    ob_start();
                    $this->ticket_list();
                    $html = ob_get_clean();
                }
                wp_send_json_success([
                    'html' => $html,
                    'msg' => __('Ticket types Saved Successfully..........!!', 'abp-transportforge'),
                    'js' => self::get_ticket_type_js(),
                    'type' => 'success'
                ]);
            }
            public function delete_ticket_type(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
                $options = ABPTF_Function::get_option('abptf_ticket');
                $options = is_array($options) ? $options : [];
                if (!empty($id) && isset($options[$id])) {
                    unset($options[$id]);
                    if (empty($options)) {
                        $options[1]['label'] = 'Ticket/Seat';
                    }
                    update_option('abptf_ticket', $options);
                }
                ob_start();
                $this->ticket_list();
                $html = ob_get_clean();
                wp_send_json_success([
                    'html' => $html,
                    'msg' => __('Ticket type Deleted Successfully!', 'abp-transportforge'),
                    'js' => self::get_ticket_type_js(),
                    'type' => 'success'
                ]);
            }
            public static function form_ticket($ticket = [], $id = ''): void {
                ?>
                <tr class="delete_area">
                    <th><?php do_action('abptf_add_image_icon', 'icon[]', ($ticket['icon'] ?? '')); ?></th>
                    <th>
                        <label>
                            <input type="hidden" name="id[]" value="<?php echo esc_attr($id); ?>"/>
                            <input type="text" class="_form_control validation_name" name="name[]" placeholder="<?php esc_attr_e('EX: Ticket Name', 'abp-transportforge'); ?>" value="<?php echo esc_attr($ticket['label'] ?? ''); ?>" required/>
                        </label>
                    </th>
                    <th>
                        <label>
                            <input type="text" name="color[]" disabled class="_form_control abp_color_picker" value="<?php echo esc_attr($ticket['color'] ?? ''); ?>" data-default-color=""/>
                        </label>
                    </th>
                    <th>
                        <label>
                            <input type="text" class="_form_control validation_name" name="prefix[]" placeholder="<?php esc_attr_e('EX: A', 'abp-transportforge'); ?>" value="<?php echo esc_attr($ticket['prefix'] ?? ''); ?>"/>
                        </label>
                    </th>
                    <td><?php ABPTF_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }
            public static function get_ticket_type_js(): array {
                $data = [];
                if (ABPTF_Function::on_off('ticket_type')) {
                    $options = ABPTF_Function::get_option('abptf_ticket');
                    $options = is_array($options) ? $options : [];
                    if (sizeof($options) > 0) {
                        foreach ($options as $key => $item) {
                            $icon = $item['icon'] ?? '';
                            $image = (!empty($icon) && is_numeric($icon)) ? ABPTF_Function::get_image_url('', $icon) : '';
                            $data[] = ['id' => $key, 'icon' => $icon, 'img' => $image, 'label' => ($item['label'] ?? ''), 'prefix' => ($item['prefix'] ?? ''), 'color' => ($item['color'] ?? '#333'), 'type' => 'seat'];
                        }
                    }
                }
                return $data;
            }
            /******************************/
            public function decor_list(): void {
                $options = ABPTF_Function::get_option('abptf_decor');
                //echo '<pre>';                print_r($options);                echo '</pre>';
                if (sizeof($options) > 0) { ?>
                    <div class="_group_list">
                        <?php foreach ($options as $key => $item) {
                            $label = $item['label'] ?? '';
                            if (!empty($label)) { ?>
                                <div class="_list_item">
                                    <h6 class="_abp" style="color:<?php echo esc_attr($item['color'] ?? ''); ?>"><?php ABPTF_Layout::image_icon($item['icon'] ?? '');
                                            echo esc_html($label); ?></h6>
                                    <div class="_group_content">
                                        <button type="button" class="_btn_light_yellow_xxs" onclick="abptf_popup_open_global('decor_item','<?php echo esc_attr($key); ?>')" title="<?php echo esc_attr__('Edit : ', 'abp-transportforge') . ' ' . esc_attr($label); ?>">✍️</button>
                                        <button type="button" class="_btn_light_danger_xxs" onclick="abptf_delete_global('decor_item','<?php echo esc_attr($key); ?>')" title="<?php echo esc_attr__('Trash : ', 'abp-transportforge') . ' ' . esc_attr($label); ?>">❌</button>
                                    </div>
                                </div>
                            <?php }
                        } ?>
                    </div>
                <?php } else {
                    ABPTF_Layout::layout_warning_info_xs('no_decor_item');
                }
            }
            public function add_decor_item(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                ob_start();
                $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : '';
                $options = ABPTF_Function::get_option('abptf_decor');
                $options = is_array($options) ? $options : [];
                $item = $options[$id] ?? []; ?>
                <div class="abp_form">
                    <h5 class="_abp"><?php esc_html_e('Decoration Item List', 'abp-transportforge'); ?></h5>
                    <?php ABPTF_Layout::info_text('decor_image');
                        ABPTF_Layout::info_text('decor_name');
                        ABPTF_Layout::info_text('decor_color'); ?>
                    <div class="_divider_xxs"></div>
                    <div class="configuration_content">
                        <table class="_abp ">
                            <thead>
                            <tr>
                                <th><?php esc_html_e('image/Icon', 'abp-transportforge'); ?></th>
                                <th><?php esc_html_e('Decor Name', 'abp-transportforge'); ?><sup class="_color_required">*</sup></th>
                                <th><?php esc_html_e('Color', 'abp-transportforge'); ?></th>
                                <th class="_w_10"><?php esc_html_e('Action', 'abp-transportforge'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="insertable_area sortable_area">
                            <?php self::form_decor($item, $id); ?>
                            </tbody>
                        </table>
                        <div class="_divider_xs"></div>
                        <div class="_fj_between">
                            <?php ABPTF_Layout::button_add_xs(__('Add New Decor Item', 'abp-transportforge')); ?>
                            <?php ABPTF_Layout::button_global_save('decor_item', __('Save Decor items', 'abp-transportforge')); ?>
                        </div>
                        <div class="abp_hidden">
                            <table class="_abp">
                                <tbody class="hidden_content">
                                <?php self::form_decor(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html, 'type' => 'success', 'msg' => __('Decor item Form Loaded Successfully .....! ', 'abp-transportforge')]);
            }
            public function save_decor_item(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
                $options = ABPTF_Function::get_option('abptf_decor');
                $options = is_array($options) ? $options : [];
                $ids = $post_array('id');
                $names = $post_array('name');
                $icon = $post_array('icon');
                $color = $post_array('color');
                if (!empty($names)) {
                    foreach ($names as $key => $name) {
                        if ($name !== '') {
                            $old_id = isset($ids[$key]) && $ids[$key] !== '' ? (int)$ids[$key] : '';
                            if ($old_id !== '' && isset($options[$old_id])) {
                                $id = $old_id;
                            } else {
                                $id = 1;
                                while (isset($options[$id])) {
                                    $id++;
                                }
                            }
                            $options[$id] = [
                                'label' => $name,
                                'icon' => $icon[$key] ?? '',
                                'color' => $color[$key] ?? '',
                            ];
                        }
                    }
                }
                if (!array_key_exists(1, $options)) {
                    $options[1]['label'] = 'Blank';
                }
                $options[1]['icon'] = '';
                $options[1]['color'] = '';
                ksort($options);
                update_option('abptf_decor', $options);
                ob_start();
                $this->decor_list();
                $html = ob_get_clean();
                wp_send_json_success([
                    'html' => $html,
                    'msg' => __('Decoration Item Saved Successfully..........!!', 'abp-transportforge'),
                    'js' => self::get_decor_js(),
                    'type' => 'success'
                ]);
            }
            public function delete_decor_item(): void {
                if (!check_ajax_referer('abptf_admin_ajax_nonce', 'nonce', false) || !current_user_can('manage_options')) {
                    wp_send_json_error(['msg' => __('Invalid security token or Insufficient permissions.', 'abp-transportforge'), 'type' => 'warn'], 403);
                }
                $id = isset($_POST['id']) ? absint(wp_unslash($_POST['id'])) : 0;
                $options = ABPTF_Function::get_option('abptf_decor');
                $options = is_array($options) ? $options : [];
                if (!empty($id) && isset($options[$id])) {
                    unset($options[$id]);
                    if (!array_key_exists(1, $options)) {
                        $options[1]['label'] = 'Blank';
                    }
                    $options[1]['icon'] = '';
                    $options[1]['color'] = '';
                    ksort($options);
                    update_option('abptf_decor', $options);
                }
                ob_start();
                $this->decor_list();
                $html = ob_get_clean();
                wp_send_json_success([
                    'html' => $html,
                    'msg' => __('Decor Item Deleted Successfully!', 'abp-transportforge'),
                    'js' => self::get_decor_js(),
                    'type' => 'success'
                ]);
            }
            public static function form_decor($item = [], $id = ''): void {
                ?>
                <tr class="delete_area">
                    <th><?php do_action('abptf_add_image_icon', 'icon[]', ($item['icon'] ?? '')); ?></th>
                    <th>
                        <label>
                            <input type="hidden" name="id[]" value="<?php echo esc_attr($id); ?>"/>
                            <input type="text" class="_form_control validation_name" name="name[]" placeholder="<?php esc_attr_e('EX: Decor item Name', 'abp-transportforge'); ?>" value="<?php echo esc_attr($item['label'] ?? ''); ?>" required/>
                        </label>
                    </th>
                    <th>
                        <label>
                            <input type="text" name="color[]" disabled class="_form_control abp_color_picker" value="<?php echo esc_attr($item['color'] ?? ''); ?>" data-default-color=""/>
                        </label>
                    </th>
                    <td><?php ABPTF_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }
            public static function get_decor_js(): array {
                $data = [];
                if (ABPTF_Function::on_off('sp')) {
                    $options = ABPTF_Function::get_option('abptf_decor');
                    $options = is_array($options) ? $options : [];
                    if (sizeof($options) > 0) {
                        foreach ($options as $key => $item) {
                            $icon = $item['icon'] ?? '';
                            $image = (!empty($icon) && is_numeric($icon)) ? ABPTF_Function::get_image_url('', $icon) : '';
                            $data[] = ['id' => $key, 'icon' => $icon, 'img' => $image, 'label' => ($item['label'] ?? ''), 'color' => ($item['color'] ?? '#333'), 'type' => 'other'];
                        }
                    }
                }
                return $data;
            }
        }
        new ABPTF_Seat_Plan();
    }