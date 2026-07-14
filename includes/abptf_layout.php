<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTF_Layout')) {
        class ABPTF_Layout {
            public function __construct() {
                add_action('abptf_load_date_picker', [$this, 'load_date_picker'], 10, 2);
                //==============================//
                add_action('abptf_add_icon', array($this, 'load_icon'), 10, 2);
                add_action('abptf_add_image', array($this, 'add_single_image'), 10, 2);
                add_action('abptf_add_image_multiple', array($this, 'add_image_multi'), 10, 2);
                add_action('abptf_add_image_icon', array($this, 'selection_icon_image'), 10, 3);
                add_action('abptf_image_selection', array($this, 'image_selection'), 10, 3);
            }
            public function load_date_picker($selector, $dates): void {
                if (empty($dates) || !is_array($dates)) {
                    return;
                }
                $picker_data = self::create_datepicker_array($dates);
                $json_selector = wp_json_encode(sanitize_text_field($selector));
                $json_data = wp_json_encode($picker_data);
                $inline_js = "window.abptf_picker_data = window.abptf_picker_data || {}; window.abptf_picker_data[{$json_selector}] = {$json_data};";
                wp_add_inline_script('jquery-ui-datepicker', $inline_js);
            }
            public static function create_datepicker_array($dates): array {
                $start_date = current($dates);
                $start_year = (int)gmdate('Y', strtotime($start_date));
                $start_month = (int)(gmdate('n', strtotime($start_date)) - 1);
                $start_day = (int)gmdate('j', strtotime($start_date));
                $end_date = end($dates);
                $end_year = (int)gmdate('Y', strtotime($end_date));
                $end_month = (int)(gmdate('n', strtotime($end_date)) - 1);
                $end_day = (int)gmdate('j', strtotime($end_date));
                $all_dates = [];
                foreach ($dates as $date) {
                    $all_dates[] = gmdate('j-n-Y', strtotime($date));
                }
                return [
                    'minYear' => $start_year,
                    'minMonth' => $start_month,
                    'minDay' => $start_day,
                    'maxYear' => $end_year,
                    'maxMonth' => $end_month,
                    'maxDay' => $end_day,
                    'activeDates' => $all_dates,
                    'txtAvail' => esc_js(__('Available', 'abp-transportforge')),
                    'txtUnavail' => esc_js(__('Unavailable', 'abp-transportforge'))
                ];
            }
            //==============================//
            public static function load_admin_globally(): void {
                ?>
                <div class="abp_popup " data-popup="#abptf_global_popup">
                    <div class="popup_area">
                        <span class="close_icon" onclick="abptf_popup_close_global()"><i class="fas fa-times"></i></span>
                        <div class="popup_body"></div>
                    </div>
                </div>
                <div class="popup_icon abp_popup" data-popup="#abptf_popup_icon">
                    <div class="popup_area">
                        <div class="popup_head _all_center">
                            <div class="abp_dropdown _max_400">
                                <label class="_abp_all_center">
                                    <input type="hidden" class="abp_icon_search_hidden" name="abp_icon_search" value=""/>
                                    <input type="text" class="_form_control_text_center validation_name abptf_allow abp_icon_search" name="" placeholder="<?php esc_attr_e('Search  icon', 'abp-transportforge'); ?>" value=""/>
                                </label>
                                <div class="dropdown_list"></div>
                            </div>
                            <span class="popup_close"><i class="fas fa-times"></i></span>
                        </div>
                        <div class="popup_body">
                            <h4 class="_abp_text_center item_icon_title"></h4>
                            <div class="item_icon_area"></div>
                        </div>
                    </div>
                </div>
                <?php
            }
            //==============================//
            public static function button_add($button_text, $class = '', $button_class = ''): void {
                $class = $class ?: 'add_new_hook';
                $button_class = $button_class ?: '_btn_light_active_xs';
                $button_text = $button_text ?: __('Add New', 'abp-transportforge');
                ?>
                <button class="<?php echo esc_attr($button_class . ' ' . $class); ?>" type="button">
                    <span class="_mar_r_xxs">➕</span><?php echo esc_html($button_text); ?>
                </button>
                <?php
            }
            public static function button_delete_sort_edit(): void {
                ?>
                <div class="_all_center">
                    <div class="_group_content">
                        <?php
                            self::button_edit();
                            self::button_sort();
                            self::button_delete();
                        ?>
                    </div>
                </div>
                <?php
            }
            public static function button_delete_sort(): void {
                ?>
                <div class="_all_center">
                    <div class="_group_content">
                        <?php
                            self::button_sort();
                            self::button_delete();
                        ?>
                    </div>
                </div>
                <?php
            }
            public static function button_edit($class_edit = 'edit_hook'): void {
                ?>
                <button class="_btn_light_navy_blue_xs <?php echo esc_attr($class_edit); ?>" type="button" title="<?php esc_attr_e('Edit This Item', 'abp-transportforge'); ?>">
                    <span class="fas fa-edit"></span>
                </button>
                <?php
            }
            public static function button_delete($class = 'delete_hook'): void {
                ?>
                <button class="_btn_light_danger_xxs <?php echo esc_attr($class); ?>" type="button" title="<?php esc_attr_e('Delete This Item', 'abp-transportforge'); ?>">❌</button>
                <?php
            }
            public static function button_sort(): void {
                ?>
                <div class="_btn_light_info_xxs sortable_handle" type="button" title="<?php esc_attr_e('Move This Item', 'abp-transportforge'); ?>">
                    <span class="fas fa-arrows-alt"></span>
                </div>
                <?php
            }
            public static function button_global_save($action, $text = '', $class = ''): void {
                if (!empty($action)) {
                    $class = $class ?: '_btn_theme_xs';
                    $text = $text ?: __('save', 'abp-transportforge');
                    ?>
                    <button class="<?php echo esc_attr($class); ?>" type="button" onclick="abptf_save_global('<?php echo esc_attr($action); ?>',this)">
                        <span class="_mar_r_xxs">💾</span><?php echo esc_html($text); ?>
                    </button>
                    <?php
                }
            }
            public static function button_global_popup($action, $text = '', $class = ''): void {
                if (!empty($action)) {
                    $class = $class ?: '_btn_light_active_xs';
                    $text = $text ?: __('Add New', 'abp-transportforge');
                    ?>
                    <button type="button" class="<?php echo esc_attr($class) ?>" onclick="abptf_popup_open_global('<?php echo esc_attr($action); ?>')">
                        <span class="_mar_r_xxs">➕</span><?php echo esc_html($text); ?>
                    </button>
                    <?php
                }
            }
            //=============================//
            public static function session_notice($key = '', $type = 'success'): void {
                if (!empty($key) && isset($_SESSION[$key])) {
                    ?>
                    <div class="toast_notice" data-type="<?php echo esc_attr($type) ?>">
                        <?php echo esc_html(sanitize_text_field(wp_unslash($_SESSION[$key]))); ?>
                    </div>
                    <?php
                    unset($_SESSION[$key]);
                }
            }
            public static function info_text($key = '', $data = ''): void {
                $data = empty($data) ? ABPTF_Status::array_info($key) : $data;
                if ($data) {
                    ?>
                    <div class="info_text load_more">
                        ℹ️ &nbsp;<?php echo wp_kses_post($data); ?>
                        <span class="load_more_action" data-less="<?php esc_html_e('.... Less ', 'abp-transportforge'); ?>" data-more="<?php esc_html_e('.... More', 'abp-transportforge'); ?>"><?php esc_html_e('.... More', 'abp-transportforge'); ?></span>
                    </div>
                    <?php
                }
            }
            public static function load_more($data = ''): void {
                if ($data) {
                    ?>
                    <div class="load_more">
                        <?php echo wp_kses_post($data); ?>
                        <span class="load_more_action" data-less="<?php esc_html_e('.... Less ', 'abp-transportforge'); ?>" data-more="<?php esc_html_e('.... More', 'abp-transportforge'); ?>"><?php esc_html_e('.... More', 'abp-transportforge'); ?></span>
                    </div>
                    <?php
                }
            }
            public static function layout_warning_info($key): void {
                $data = ABPTF_Status::array_info($key);
                if ($data) {
                    echo '<div class="_section_bg_warning_mar_zero"><h4 class="_abp_text_center_color_white">' . esc_html($data) . '</h4></div>';
                }
            }
            public static function layout_warning_info_xs($key): void {
                $data = ABPTF_Status::array_info($key);
                if ($data) {
                    echo '<div class="_abp_text_center_color_white_bg_warning_padding_xxs_fs_label">' . esc_html($data) . '</div>';
                }
            }
            public static function layout_info_xs($key): void {
                $data = ABPTF_Status::array_info($key);
                if ($data) {
                    echo '<div class="_abp_bg_info_padding_xxs _all_center _color_5">' . esc_html($data) . '</div>';
                }
            }
            public static function on(): bool|string {
                ob_start();
                ?>
                <strong class="_abp_color_theme"> <?php esc_html_e('ON', 'abp-transportforge'); ?></strong>
                <?php
                return ob_get_clean();
            }
            public static function off(): bool|string {
                ob_start();
                ?>
                <strong class="_abp_color_theme"> <?php esc_html_e('OFF', 'abp-transportforge'); ?></strong>
                <?php
                return ob_get_clean();
            }
            //==============Input field===============//
            public static function input_dropdown($infos, $icon = ''): void {
                if (is_array($infos) && sizeof($infos) > 0) {
                    asort($infos);
                    ?>
                    <div class="dropdown_list">
                        <ul class="_abp">
                            <?php foreach ($infos as $info) { ?>
                                <li data-value="<?php echo esc_attr($info); ?>"><span class="<?php echo esc_attr($icon); ?> _mar_r_xxs"></span><span data-text><?php echo esc_html($info); ?></span></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <?php
                }
            }
            public static function quantity_input($input_info = []): void {
                $name = $input_info['name'] ?? '';
                $price = floatval($input_info['price'] ?? 0);
                $min_qty = absint($input_info['min_qty'] ?? 1);
                $max_qty = absint($input_info['max_qty'] ?? 1);
                $class = $input_info['class'] ?? '';
                $collapse_id = $input_info['collapse_id'] ?? '';
                if ($name && $max_qty >= $min_qty) {
                    if (!empty($collapse_id)) {
                        ?> <div data-collapse="<?php echo esc_attr($collapse_id); ?>"><?php
                    }
                    ?>
                    <div class="_group_content qty_input">
                        <div class="qty_decrease _ag_content"> ➖</div>
                        <label>
                            <input type="text" class="_form_control  validation_number <?php echo esc_attr($class); ?>"
                                   name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($min_qty); ?>"
                                   data-price="<?php echo esc_attr($price); ?>" data-min="<?php echo esc_attr($min_qty); ?>" data-max="<?php echo esc_attr($max_qty); ?>"
                            />
                        </label>
                        <div class="qty_increase _ag_content">➕</div>
                    </div>
                    <?php
                    if (!empty($collapse_id)) {
                        ?></div><?php
                    }
                }
            }
            public static function switch_checkbox($name, $value = ''): void {
                $value = in_array($value, ['on', 'off', ''], true) ? $value : '';
                ?>
                <div class="<?php echo esc_attr($value === 'on' ? 'abp_active' : ''); ?>" data-switch data-collapse-target="#<?php echo esc_attr($name); ?>">
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>">
                </div>
                <?php
            }
            public static function input_title($label = '', $required = ''): void {
                if ($label) { ?>
                    <span class="_mar_b_xxs">
							<?php echo esc_html($label); ?>
                        <?php if ($required) { ?>
                            <sup class="_color_required">*</sup>
                        <?php } ?>
						</span>
                    <?php
                }
            }
            public static function input_date($name, $date = '', $label = '', $required = ''): void {
                $date_format = ABPTF_Function::date_format_php();
                $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                $hidden_date = $date ? gmdate('Y-m-d', strtotime($date)) : '';
                $visible_date = $date ? date_i18n($date_format, strtotime($date)) : '';
                ?>
                <label class="_input_item">
                    <?php self::input_title($label, $required); ?>
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($hidden_date); ?>" <?php echo esc_attr($required); ?>/>
                    <input type="text" name="" class="_form_control abp_datepicker" value="<?php echo esc_attr($visible_date); ?>" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                    <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transportforge'); ?>"></span>
                </label>
                <?php
            }
            public static function input_time($name, $time = '', $label = '', $required = ''): void {
                ?>
                <label class="_input_item">
                    <?php self::input_title($label, $required); ?>
                    <input type="time" class="_form_control" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($time); ?>" <?php echo esc_attr($required); ?>/>
                    <span class="fas fa-times time_close_icon" title="<?php esc_attr_e('Clear Time', 'abp-transportforge'); ?>"></span>
                </label>
                <?php
            }
            public static function textarea($name, $value = '', $label = '', $required = ''): void {
                ?>
                <label class="abptf_textarea _input_item">
                    <?php self::input_title($label, $required); ?>
                    <textarea name="<?php echo esc_attr($name); ?>" rows="3" class="_form_control" placeholder="<?php echo esc_attr($label); ?>" title="<?php echo esc_attr($label); ?>"  <?php echo esc_attr($required); ?>><?php echo esc_textarea($value); ?></textarea>
                </label>
                <?php
            }
            public static function select($name, $value = '', $label = '', $required = '', $options = []): void {
                if (is_array($options) && sizeof($options) > 0) {
                    ?>
                    <label class="_input_item">
                        <?php self::input_title($label, $required); ?>
                        <select name="<?php echo esc_attr($name); ?>" class="_form_control" title="<?php echo esc_attr($label); ?>" <?php echo esc_attr($required); ?>>
                            <option value="" disabled selected><?php echo esc_html__('Please Select', 'abp-transportforge') . ' ' . esc_html($label); ?></option>
                            <?php foreach ($options as $option) { ?>
                                <option value="<?php echo esc_attr($option); ?>" <?php echo esc_attr($option == $value ? 'selected' : ''); ?>><?php echo esc_html($option); ?></option>
                            <?php } ?>
                        </select>
                    </label>
                    <?php
                }
            }
            public static function checkbox($name, $value = '', $label = '', $required = '', $options = []): void {
                if (is_array($options) && sizeof($options) > 0) {
                    ?>
                    <div class=" _input_item">
                        <span class="_fs_label"> <?php self::input_title($label, $required); ?></span>
                        <div class="custom_checkbox">
                            <input type="hidden" class="_form_control" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"/>
                            <?php foreach ($options as $option) { ?>
                                <div class="checkbox_item">
                                    <button type="button" class="_btn_white_xs <?php echo esc_attr($option == $value ? 'abp_active' : ''); ?>" data-checked="<?php echo esc_attr($option); ?>" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr($option == $value ? 'far fa-check-square' : 'far fa-square'); ?>"></span><?php echo esc_html($option); ?>
                                    </button>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                }
            }
            public static function radio($name, $value = '', $label = '', $required = '', $options = []): void {
                if (is_array($options) && sizeof($options) > 0) {
                    ?>
                    <div class=" _input_item">
                        <span class="_fs_label"> <?php self::input_title($label, $required); ?></span>
                        <div class="custom_radio">
                            <input type="hidden" class="_form_control" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"/>
                            <?php foreach ($options as $option) { ?>
                                <div class="radio_item">
                                    <button type="button" class="_btn_white_xs <?php echo esc_attr($option == $value ? 'abp_active' : ''); ?>" data-radio="<?php echo esc_attr($option); ?>" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr($option == $value ? 'far fa-check-circle' : 'far fa-circle'); ?>"></span><?php echo esc_html($option); ?>
                                    </button>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                }
            }
            //=============Add  Image / Icon================//
            public static function image($post_id = '', $image_id = '', $url = '', $class = ''): void {
                $image_url = ($post_id > 0 || $image_id) ? ABPTF_Function::get_image_url($post_id, $image_id) : $url;
                $post_url = $post_id > 0 ? get_the_permalink($post_id) : '';
                $image_url = $image_url ?: ABPTF_BLANK_IMG_URL;
                if ($image_url) {
                    ?>
                    <div class="abp_image <?php echo esc_attr($class); ?>" data-image-href="<?php echo esc_url($image_url); ?>" <?php if (!empty($post_url)) { ?> data-href="<?php echo esc_url($post_url); ?>" <?php } ?> >
                        <img class="_img_control" src="#" alt="<?php echo esc_attr(max($post_id, $image_id)); ?>">
                    </div>
                    <?php
                }
            }
            public static function image_icon($icon_image, $class = '_mar_r_xxs'): void {
                if (!empty($icon_image)) {
                    $icon = $image = $emoji = '';
                    if (is_numeric($icon_image)) {
                        $image = $icon_image;
                    } elseif (preg_match('/\s/', $icon_image)) {
                        $icon = $icon_image;
                    } else {
                        $emoji = $icon_image;
                    }
                    if ($image) {
                        ABPTF_Layout::image('', $image);
                    } else { ?>
                        <span class="<?php echo esc_attr($icon . ' ' . $class); ?>"><?php echo esc_html($emoji); ?></span>
                    <?php }
                }
            }
            public function load_icon($name, $value = ''): void {
                $button_active_class = $value ? '_d_none' : '';
                $icon = $emoji = '';
                if (preg_match('/\s/', $value)) {
                    $icon = $value;
                } else {
                    $emoji = $value;
                }
                $icon_class = ($icon || $emoji) ? '' : '_d_none';
                ?>
                <div class="icon_image_selection_area">
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"/>
                    <div class="icon_item  <?php echo esc_attr($icon_class); ?>">
                        <div class="_all_center"><span class="<?php echo esc_attr($icon); ?>" data-add-icon><?php echo esc_html($emoji); ?></span></div>
                        <span class="fas fa-times icon_close icon_delete" title="<?php esc_html_e('Remove Icon', 'abp-transportforge'); ?>"></span>
                    </div>
                    <div class="image_icon_select_area <?php echo esc_attr($button_active_class); ?>">
                        <button class="_btn_info_xs icon_add" type="button" data-target-popup="#abptf_popup_icon"><span class="fas fa-icons _fs_h6"></span></button>
                    </div>
                </div>
                <?php
            }
            public function add_single_image($name, $image_id = ''): void {
                ?>
                <div class="add_image">
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($image_id); ?>"/>
                    <?php if ($image_id) { ?>
                        <div class="add_image_item" data-image-id="<?php echo esc_attr($image_id); ?>'">
                            <span class="fas fa-times _circle_icon_xs remove_image"></span>
                            <img class="_img_control" src="<?php echo esc_url(wp_get_attachment_image_url($image_id, 'medium')); ?>" alt="<?php echo esc_attr($image_id); ?>"/>
                        </div>
                    <?php } ?>
                    <button type="button" class="_btn_default_xs_bg_color_5_w_full <?php echo esc_attr($image_id ? '_d_none' : ''); ?>">
                        <span class="fas fa-image _mar_r_xs"></span><?php esc_html_e('Image', 'abp-transportforge'); ?>
                    </button>
                </div>
                <?php
            }
            public function image_selection($name, $image_id = '', $target = ''): void {
                ?>
                <div class="image_selection">
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($image_id); ?>" data-target="<?php echo esc_attr($target); ?>"/>
                    <div class="image_item <?php echo esc_attr(empty($image_id) ? '_d_none' : ''); ?>" data-image-id="<?php echo esc_attr($image_id); ?>'">
                        <span class="image_remove" onclick="abptf_image_remove(this)">❌</span>
                        <img class="_img_control" src="<?php echo esc_url(wp_get_attachment_image_url($image_id, 'medium')); ?>" alt="<?php echo esc_attr($image_id); ?>"/>
                    </div>
                    <button type="button" class="_btn_light_active_xs <?php echo esc_attr($image_id ? '_d_none' : ''); ?>" onclick="abptf_image_selection(this)">
                        <span class="fas fa-image _mar_r_xs"></span><?php esc_html_e('Select Image', 'abp-transportforge'); ?>
                    </button>
                </div>
                <?php
            }
            public function add_image_multi($name, $images): void {
                $images = is_array($images) ? ABPTF_Function::array_to_string($images) : $images;
                ?>
                <div class="multiple_image_area">
                    <input type="hidden" class="multiple_image_ids" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($images); ?>"/>
                    <div class="multiple_image">
                        <?php
                            $all_images = explode(',', $images);
                            if ($images && sizeof($all_images) > 0) {
                                foreach ($all_images as $image) {
                                    $img_url = ABPTF_Function::get_image_url('', $image, 'medium') ?: ABPTF_BLANK_IMG_URL;
                                    ?>
                                    <div class="multiple_image_item" data-image-id="<?php echo esc_attr($image); ?>">
                                        <span class="fas fa-times _circle_icon_xs remove_image_multi"></span>
                                        <img class="_img_control" src="<?php echo esc_attr($img_url); ?>" alt="<?php echo esc_attr($image); ?>"/>
                                    </div>
                                    <?php
                                }
                            }
                        ?>
                    </div>
                    <?php ABPTF_Layout::button_add(__('Add  Image', 'abp-transportforge'), 'add_image_multi _mar_t_xs'); ?>
                </div>
                <?php
            }
            public function selection_icon_image($name, $value = ''): void {
                $icon = $image = $emoji = '';
                if (is_numeric($value)) {
                    $image = $value;
                } elseif (preg_match('/\s/', $value)) {
                    $icon = $value;
                } else {
                    $emoji = $value;
                }
                $icon_class = ($icon || $emoji) ? '' : '_d_none';
                $image_class = $image ? '' : '_d_none';
                $button_active_class = ($icon || $image || $emoji) ? '_d_none' : '';
                ?>
                <div class="icon_image_selection_area _fd_column">
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"/>
                    <div class="icon_item <?php echo esc_attr($icon_class); ?>">
                        <div class="_all_center"><span class="<?php echo esc_attr($icon); ?>" data-add-icon><?php echo esc_html($emoji); ?></span></div>
                        <span class="fas fa-times icon_close icon_delete" title="<?php esc_html_e('Remove Icon', 'abp-transportforge'); ?>"></span>
                    </div>
                    <div class="image_item <?php echo esc_attr($image_class); ?>">
                        <img class="_img_control" src="<?php echo esc_url(ABPTF_Function::get_image_url('', $image, 'medium')); ?>" alt="image">
                        <span class="fas fa-times icon_close image_delete" title="<?php esc_html_e('Remove Image', 'abp-transportforge'); ?>"></span>
                    </div>
                    <div class="image_icon_select_area <?php echo esc_attr($button_active_class); ?>">
                        <div class="_group_content_f_equal_w_full">
                            <button class="_btn_light_info_xs image_select" type="button"><span class="fas fa-image _fs_h6"></span></button>
                            <button class="_btn_light_info_xs icon_add" type="button" data-target-popup="#abptf_popup_icon"><span class="fas fa-icons _fs_h6"></span></button>
                        </div>
                    </div>
                </div>
                <?php
            }
            //=============static array================//
            public static function status_text($status): string {
                if (!is_string($status) && !is_int($status)) {
                    return '';
                }
                $status_array = function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : [];
                return is_array($status_array) ? ($status_array[$status] ?? '') : '';
            }
            public static function book_status_text($key): string {
                $rules = [
                    '0' => __('Pending', 'abp-transportforge'),
                    '1' => __('Waiting', 'abp-transportforge'),
                    '2' => __('In Rent', 'abp-transportforge'),
                    '3' => __('Completed', 'abp-transportforge'),
                    '4' => __('Delay', 'abp-transportforge'),
                    '5' => __('Canceled', 'abp-transportforge')
                ];
                $rules = apply_filters('abptf_filter_book_status_rule', $rules);
                $key = is_numeric($key) ? (string)$key : $key;
                if (!is_string($key) && !is_int($key)) {
                    return '';
                }
                return is_array($rules) ? ($rules[$key] ?? (string)$key) : (string)$key;
            }
            public static function get_book_status($order_id, $start_time, $end_time, $book_status): int {
                $now = current_time('Y-m-d H:i:s');
                if (!empty($book_status) && $book_status < 5 && $book_status > 0) {
                    $_book_status = 0;
                    if (strtotime($now) < strtotime($start_time)) {
                        $_book_status = $book_status;
                    } elseif (strtotime($now) > strtotime($start_time) && strtotime($now) < strtotime($end_time)) {
                        $_book_status = 2;
                    } elseif (strtotime($now) > strtotime($start_time) && strtotime($now) > strtotime($end_time)) {
                        $_book_status = 3;
                    }
                    if ($_book_status > $book_status) {
                        $book_status = $_book_status;
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'abptf_orders';
                        $booking_lists = ABPTF_Query::get_booking_query(['order_id' => $order_id]);
                        if (!empty($booking_lists) && is_array($booking_lists)) {
                            $data = [
                                'book_status' => intval($book_status),
                                'updated_at' => current_time('Y-m-d H:i:s')
                            ];
                            $where = ['order_id' => (int)$order_id];
                            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                            $wpdb->update($table_name, $data, $where, ['%s', '%s', '%s'], ['%d']);
                        }
                    }
                }
                return $book_status;
            }
            public static function week_day(): array {
                return [
                    'monday' => __('Monday', 'abp-transportforge'),
                    'tuesday' => __('Tuesday', 'abp-transportforge'),
                    'wednesday' => __('Wednesday', 'abp-transportforge'),
                    'thursday' => __('Thursday', 'abp-transportforge'),
                    'friday' => __('Friday', 'abp-transportforge'),
                    'saturday' => __('Saturday', 'abp-transportforge'),
                    'sunday' => __('Sunday', 'abp-transportforge'),
                ];
            }
            public static function date_option_rules(): array {
                $rules = [
                    'weekend' => __('Weekend', 'abp-transportforge'),
                    'specific_off_dates' => __('Specific Off Dates', 'abp-transportforge'),
                    'special_on_dates' => __('Special On Dates', 'abp-transportforge'),
                    'off_date_range' => __('Off Dates Range', 'abp-transportforge'),
                ];
                return apply_filters('abptf_filter_rent_rule', $rules);
            }
            public static function rent_rules_options(): array {
                $options = (ABPTF_On_Off['rent_rule'] ?? null) ?: self::rent_rules_string();
                $options = !empty($options) ? explode(',', $options) : [];
                $rent_rules = [];
                foreach ($options as $option) {
                    if (!empty($option)) {
                        $rent_rules[$option] = self::rent_rules($option);
                    }
                }
                return $rent_rules;
            }
            public static function rent_rules($key = '') {
                $rules = [
                    'hourly' => __('Hourly Rate', 'abp-transportforge'),
                    'daily' => __('Daily Rate', 'abp-transportforge'),
                    'multi_day' => __('Daily & Hourly Rate', 'abp-transportforge'),
                    'monthly' => __('Monthly Rate', 'abp-transportforge'),
                    'multi_month' => __('Monthly & Daily Rate', 'abp-transportforge')
                ];
                $rules = apply_filters('abptf_filter_rent_rule', $rules);
                if (!is_string($key) && !is_int($key)) {
                    return is_array($rules) ? $rules : [];
                }
                if ($key === '') {
                    return is_array($rules) ? $rules : [];
                }
                return is_array($rules) ? ($rules[$key] ?? '') : '';
            }
            public static function rent_rules_string() {
                return apply_filters('abptf_filter_rent_rule_string', 'hourly,daily,multi_day,monthly,multi_month');
            }
            public static function per_rent_rules($key = '') {
                $rules = [
                    'hourly' => __('/hr', 'abp-transportforge'),
                    'daily' => __('/day', 'abp-transportforge'),
                    'multi_day' => __('/day', 'abp-transportforge'),
                    'monthly' => __('/month', 'abp-transportforge'),
                    'multi_month' => __('/month', 'abp-transportforge')
                ];
                $rules = apply_filters('abptf_filter_per_rent_rule', $rules);
                if (!is_string($key) && !is_int($key)) {
                    return is_array($rules) ? $rules : [];
                }
                if ($key === '') {
                    return is_array($rules) ? $rules : [];
                }
                return is_array($rules) ? ($rules[$key] ?? '') : '';
            }
            public static function rent_rules_sin_plu($key = '') {
                $rules = [
                    'hourly' => ['sin' => __('Hour', 'abp-transportforge'), 'plu' => __('Hours', 'abp-transportforge')],
                    'daily' => ['sin' => __('Day', 'abp-transportforge'), 'plu' => __('Days', 'abp-transportforge')],
                    'multi_day' => ['sin' => __('Day', 'abp-transportforge'), 'plu' => __('Days', 'abp-transportforge')],
                    'monthly' => ['sin' => __('Month', 'abp-transportforge'), 'plu' => __('Months', 'abp-transportforge')],
                    'multi_month' => ['sin' => __('Month', 'abp-transportforge'), 'plu' => __('Months', 'abp-transportforge')]
                ];
                $rules = apply_filters('abptf_filter_sin_plu_rent_rule', $rules);
                if (!is_string($key) && !is_int($key)) {
                    return is_array($rules) ? $rules : [];
                }
                if ($key === '') {
                    return is_array($rules) ? $rules : [];
                }
                return is_array($rules) ? ($rules[$key] ?? []) : [];
            }
            public static function array_date_format(): array {
                $current_date = current_time('Y-m-d');
                return [
                    'yy-mm-dd' => $current_date,
                    'yy/mm/dd' => date_i18n('Y/m/d', strtotime($current_date)),
                    'yy-dd-mm' => date_i18n('Y-d-m', strtotime($current_date)),
                    'yy/dd/mm' => date_i18n('Y/d/m', strtotime($current_date)),
                    'dd-mm-yy' => date_i18n('d-m-Y', strtotime($current_date)),
                    'dd/mm/yy' => date_i18n('d/m/Y', strtotime($current_date)),
                    'mm-dd-yy' => date_i18n('m-d-Y', strtotime($current_date)),
                    'mm/dd/yy' => date_i18n('m/d/Y', strtotime($current_date)),
                    'd M , yy' => date_i18n('j M , Y', strtotime($current_date)),
                    'D d M , yy' => date_i18n('D j M , Y', strtotime($current_date)),
                    'M d , yy' => date_i18n('M  j, Y', strtotime($current_date)),
                    'D M d , yy' => date_i18n('D M  j, Y', strtotime($current_date)),
                ];
            }
            //=============================//
            public static function location_select($post_id = '', $location = ''): void {
                $all_locations = ABPTF_Locations;
                if (!empty($all_locations)) {
                    if (!empty($post_id)) {
                        $location_array = !empty($location) ? explode(',', $location) : [];
                        if (!empty($location_array)) {
                            if (sizeof($location_array) > 1) {
                                ?>
                                <div class="_input_item">
                                    <label>
                                        <span><i class="fas fa-location _mar_r_xxs"></i><?php esc_html_e('Location', 'abp-transportforge'); ?><sup class="_color_required">*</sup></span>
                                        <select class="_form_control" name="location">
                                            <?php foreach ($location_array as $loc_id) {
                                                if (in_array($loc_id, $location_array)) {
                                                    ?>
                                                    <option value="<?php echo esc_attr($loc_id); ?>"><?php echo esc_html($all_locations[$loc_id]['name'] ?? ''); ?></option>
                                                <?php }
                                            } ?>
                                        </select>
                                    </label>
                                </div>
                                <?php
                            } else {
                                ?><input type="hidden" name="location" value="<?php echo esc_attr($location); ?>" /><?php
                            }
                        }
                    }
                }
            }
            public static function rent_start_month($all_dates): void {
                if (sizeof($all_dates) > 0) {
                    ?>
                    <label>
                        <span><i class="fas fa-calendar-check _mar_r_xxs"></i><?php esc_html_e('Pickup Month', 'abp-transportforge'); ?><sup class="_color_required">*</sup></span>
                        <select name="rent_start_date" class="_form_control">
                            <option value=""><?php esc_html_e('Select Pickup Month', 'abp-transportforge'); ?></option>
                            <?php foreach ($all_dates as $option) { ?>
                                <option value="<?php echo esc_attr($option['value']); ?>">
                                    <?php echo esc_html($option['label']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </label>
                    <?php
                } else {
                    esc_html_e('Month Configuration not complete', 'abp-transportforge');
                }
            }
            public static function rent_end_month($post_id, $start_date): void {
                $all_dates = ABPTF_Function::get_end_month($post_id, $start_date);
                //echo '<pre>';print_r($all_dates);echo '</pre>';
                if (sizeof($all_dates) > 0) {
                    ?>
                    <label>
                        <span><i class="fas fa-calendar-check _mar_r_xxs"></i><?php esc_html_e('Drop-Off Month', 'abp-transportforge'); ?><sup class="_color_required">*</sup></span>
                        <select name="rent_end_date" class="_form_control">
                            <option value=""><?php esc_html_e('Select Drop-Off Month', 'abp-transportforge'); ?></option>
                            <?php foreach ($all_dates as $option) { ?>
                                <option value="<?php echo esc_attr($option['value']); ?>">
                                    <?php echo esc_html($option['label']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </label>
                    <?php
                } else {
                    esc_html_e('Month Configuration not complete', 'abp-transportforge');
                }
            }
            public static function rent_start_date($all_dates, $date = '', $post_id = ''): void {
                //echo '<pre>';print_r($all_dates);					echo '</pre>';
                if (sizeof($all_dates) > 0) {
                    $date_format = ABPTF_Function::date_format_php();
                    $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                    $date = $date ?: current($all_dates);
                    //if ( sizeof( $all_dates ) > 10 ) {
                    $hidden_date = !empty($date) ? gmdate('Y-m-d', strtotime($date)) : '';
                    $visible_date = !empty($date) ? date_i18n($date_format, strtotime($date)) : '';
                    ?>
                    <label>
                        <span>📆<i class="_mar_r_xxs"></i><?php esc_html_e('Pickup Date', 'abp-transportforge'); ?><sup class="_color_required">*</sup></span>
                        <input type="hidden" name="rent_start_date" value="<?php echo esc_attr($hidden_date); ?>" required/>
                        <input id="start_date" type="text" value="<?php echo esc_attr($visible_date); ?>" class="_form_control" placeholder="<?php echo esc_attr($now); ?>" data-alert="<?php esc_attr_e('Please Select Pickup Date', 'abp-transportforge'); ?>" readonly required/>
                        <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transportforge'); ?>"></span>
                    </label>
                    <?php
                    do_action('abptf_load_date_picker', '#start_date', $all_dates);
                    //}
                } else {
                    if (!empty($post_id)) {
                        ABPTF_Layout::layout_warning_info_xs('not_date');
                    } else {
                        $date_format = ABPTF_Function::date_format_php();
                        $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                        ?>
                        <label>
                            <span>📆<i class="_mar_r_xxs"></i><?php esc_html_e('Pickup Date', 'abp-transportforge'); ?><sup class="_color_required">*</sup></span>
                            <input type="hidden" name="rent_start_date" value="" required/>
                            <input type="text" id="start_date" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transportforge'); ?>"></span>
                        </label>
                        <?php
                    }
                }
            }
            public static function rent_end_date($all_dates, $post_id = ''): void {
                $date_format = ABPTF_Function::date_format_php();
                $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                if (sizeof($all_dates) > 0) {
                    $date = current($all_dates);
                    //if ( sizeof( $all_dates ) > 10 ) {
                    $hidden_date = !empty($date) ? gmdate('Y-m-d', strtotime($date)) : '';
                    $visible_date = !empty($date) ? date_i18n($date_format, strtotime($date)) : '';
                    ?>
                    <label>
                        <span>🗓️<i class=" _mar_r_xxs"></i><?php esc_html_e('Drop-Off Date', 'abp-transportforge'); ?><sup class="_color_required">*</sup></span>
                        <input type="hidden" name="rent_end_date" value="<?php echo esc_attr($hidden_date); ?>" required/>
                        <input id="end_date" type="text" value="<?php echo esc_attr($visible_date); ?>" class="_form_control" placeholder="<?php echo esc_attr($now); ?>" data-alert="<?php esc_attr_e('Please Select Drop-Off  Date', 'abp-transportforge'); ?>" readonly required/>
                        <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transportforge'); ?>"></span>
                    </label>
                    <?php
                    do_action('abptf_load_date_picker', '#end_date', $all_dates);
                    //}
                } else {
                    if (!empty($post_id)) {
                        ABPTF_Layout::layout_warning_info_xs('not_date');
                    } else {
                        $date_format = ABPTF_Function::date_format_php();
                        $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                        ?>
                        <label>
                            <span>🗓️<i class=" _mar_r_xxs"></i><?php esc_html_e('Drop-Off Date', 'abp-transportforge'); ?><sup class="_color_required">*</sup></span>
                            <input type="hidden" name="rent_end_date" value="" required/>
                            <input type="text" id="end_date" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transportforge'); ?>"></span>
                        </label>
                        <?php
                    }
                }
            }
            public static function title($post_id): void {
                $post_sku = ABPTF_Function::get_post_info($post_id, 'post_sku');
                if (ABPTF_Function::on_off('post_icon')) {
                    ABPTF_Layout::image_icon(ABPTF_Function::get_post_info($post_id, 'post_icon'));
                }
                echo esc_html(get_the_title($post_id)); ?>
                <p class="_abp">
                    <?php if (!empty($post_sku) && ABPTF_Function::on_off('sku')) { ?>
                        <small class=" _abp_color_gray"><?php echo esc_html__('SKU : ', 'abp-transportforge') . esc_html($post_sku); ?></small>
                    <?php } ?>
                </p>
                <?php
            }
            public static function item_feature($features = ''): void {
                if (ABPTF_Function::on_off('feature')) {
                    if (!is_string($features) || $features === '') {
                        return;
                    }
                    $feature_ids = explode(',', $features);
                    $abptf_feature = defined('ABPTF_Features') ? ABPTF_Features : [];
                    if (empty($feature_ids) || !is_array($abptf_feature)) {
                        return;
                    }
                    ?>
                    <div class="item_spec load_more">
                        <?php
                            foreach ($feature_ids as $fec_id) {
                                $feature = $abptf_feature[$fec_id] ?? null;
                                if (!is_array($feature)) {
                                    continue;
                                }
                                $label = $feature['label'] ?? '';
                                $value = $feature['value'] ?? '';
                                $icon = $feature['icon'] ?? '';
                                if ($value !== '') {
                                    echo '<span class="spec_badge" title="' . esc_attr($label) . '">';
                                    ABPTF_Layout::image_icon($icon);
                                    echo esc_html($label . ' - ' . $value);
                                    echo '</span>';
                                }
                            } ?>
                        <span class="load_more_action" data-less="<?php esc_html_e('....Less ', 'abp-transportforge'); ?>" data-more="<?php esc_html_e('.... More', 'abp-transportforge'); ?>"><?php esc_html_e('.... More', 'abp-transportforge'); ?></span>
                    </div>
                    <?php
                }
            }
            public static function item_condition($rent_rule, $price_info = []): string {
                if (!is_array($price_info)) {
                    return '';
                }
                $condition = '';
                $min = $price_info['min'] ?? '';
                $max = $price_info['max'] ?? '';
                if ($min !== '' || $max !== '') {
                    $rule_info = self::rent_rules_sin_plu($rent_rule);
                    $rule_info = is_array($rule_info) ? $rule_info : [];
                    $sin_text = $rule_info['sin'] ?? '';
                    $plu_text = $rule_info['plu'] ?? '';
                    $min_val = absint($min);
                    $unit_text = (1 === $min_val) ? $sin_text : $plu_text;
                    if ((string)$min === (string)$max) {
                        $condition .= sprintf(
                        // translators: 1: minimum number, 2: time unit (e.g. hours)
                            __('Rental is available for %1$s %2$s Only', 'abp-transportforge'),
                            $min,
                            $unit_text
                        );
                    } else {
                        if ($min !== '') {
                            $condition .= '📉 ' . sprintf(
                                // translators: 1: The minimum number, 2: The unit text (e.g., "Hours").
                                    __('Min. %1$s %2$s', 'abp-transportforge'),
                                    $min,
                                    $unit_text
                                );
                        }
                        if ($max !== '') {
                            $max_val = absint($max);
                            $max_unit_text = (1 === $max_val) ? $sin_text : $plu_text;
                            if ($min !== '') {
                                $condition .= '  📈  ';
                            } else {
                                $condition .= '📈 ';
                            }
                            $condition .= sprintf(
                            // translators: 1: The maximum number, 2: The unit text (e.g., "Hours").
                                __('Max. %1$s %2$s', 'abp-transportforge'),
                                $max,
                                $max_unit_text
                            );
                        }
                    }
                } else {
                    $text = self::rent_rules($rent_rule);
                    $text = is_string($text) ? $text : '';
                    // translators: %s is the user role or restriction text .
                    $condition .= sprintf(__('Rental is available for %s  only', 'abp-transportforge'), $text);
                }
                return $condition;
            }
            public static function item_deposit($price_info): void {
                if (ABPTF_Function::on_off('deposit')) {
                    if (!is_array($price_info)) {
                        return;
                    }
                    $deposit_info = $price_info['deposit'] ?? [];
                    if (!is_array($deposit_info)) {
                        return;
                    }
                    $deposit_type = $deposit_info['type'] ?? '';
                    $deposit_value = $deposit_info['value'] ?? '';
                    if ($deposit_type !== '' && $deposit_value !== '') {
                        ?>
                        <div class="item_condition">
                            <?php
                                if ($deposit_type === 'fixed') {
                                    echo wp_kses_post(sprintf(
                                    /* translators: %s = deposit label' */
                                        _x('• Deposit: %s Fixed', 'deposit label', 'abp-transportforge'),
                                        wc_price($deposit_value)
                                    ));
                                } elseif ($deposit_type === 'percent') {
                                    echo esc_html(sprintf(
                                    /* translators: %s = deposit label' */
                                        _x('• Deposit: %s of Total Price', 'deposit label', 'abp-transportforge'),
                                        $deposit_value . '%'
                                    ));
                                } else {
                                    echo wp_kses_post(sprintf(
                                    /* translators: %s = deposit label' */
                                        _x('• Deposit: %s Per Item', 'deposit label', 'abp-transportforge'),
                                        wc_price($deposit_value)
                                    ));
                                }
                            ?>
                        </div>
                        <?php
                    }
                }
            }
            public static function item_price($post_id, $rent_rule, $price_info): void {
                if (!is_array($price_info)) {
                    return;
                }
                $rent_rule = is_string($rent_rule) ? $rent_rule : '';
                ?>
                <span class="price_label"><?php echo esc_html(ABPTF_Layout::rent_rules($rent_rule)); ?></span>
                <span class="price_value">
                        <?php
                            $price = $price_info['price'] ?? '';
                            $price = apply_filters('abptf_filter_price', $price, $rent_rule, $price_info);
                            $price = ($price !== '' && $price > 0) ? ABPTF_Function::tax_with_price($post_id, $price) : 0;
                            echo ($price > 0) ? wp_kses_post(wc_price($price)) : esc_html__('Free', 'abp-transportforge');
                            echo esc_html(ABPTF_Layout::per_rent_rules($rent_rule));
                            if ($rent_rule === 'multi_day' || $rent_rule === 'multi_month') {
                                $price_multi = $price_info['price_multi'] ?? '';
                                $price_multi = apply_filters('abptf_filter_price_multi', $price_multi, $rent_rule, $price_info);
                                $price_multi = ($price_multi !== '' && $price_multi > 0) ? ABPTF_Function::tax_with_price($post_id, $price_multi) : 0;
                                esc_html_e(' & ', 'abp-transportforge');
                                echo ($price_multi > 0) ? wp_kses_post(wc_price($price_multi)) : esc_html__('Free', 'abp-transportforge');
                                echo ($rent_rule === 'multi_day') ? esc_html(ABPTF_Layout::per_rent_rules('hourly')) : esc_html(ABPTF_Layout::per_rent_rules('daily'));
                            }
                        ?>
                    </span>
                <?php
            }
            public static function item_cost($post_infos, $price_info, $total_price, $time_duration): void {
                if (!is_array($post_infos)) {
                    return;
                }
                $rent_rule = $post_infos['rent_rule'] ?? '';
                $date_info = $post_infos['date_info'] ?? [];
                $dif_text = is_array($date_info) ? ($date_info['text'] ?? '') : '';
                $price_info = is_array($price_info) ? $price_info : [];
                ?>
                <div class="calculated_cost">
                    <?php if (!empty($time_duration)) { ?>
                        <div class="cost_label">
                            <?php echo esc_html__('Total for ', 'abp-transportforge') . ' ' . esc_html($dif_text); ?>
                        </div>
                        <div class="cost_value">
                            <?php echo $total_price > 0 ? wp_kses_post(wc_price($total_price)) : esc_html__('Free ', 'abp-transportforge'); ?>
                        </div>
                    <?php } else { ?>
                        <div class="cost_condition">
                            <?php echo esc_html(ABPTF_Layout::item_condition($rent_rule, $price_info)); ?>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
            public static function item_select_property($post_infos, $price_info, $total_price = 0): void {
                if (!is_array($post_infos)) {
                    return;
                }
                $price_info = is_array($price_info) ? $price_info : [];
                $post_id = $post_infos['post_id'] ?? '';
                $property_id = $post_infos['property_id'] ?? '';
                $name = $post_infos['property_name'] ?? '';
                $deposit_info = $price_info['deposit'] ?? [];
                $deposit_type = is_array($deposit_info) ? ($deposit_info['type'] ?? '') : '';
                $deposit_value = is_array($deposit_info) ? ($deposit_info['value'] ?? '') : '';
                $total_qty = intval($price_info['qty'] ?? 0);
                $reserve_qty = intval($price_info['reserve'] ?? 0);
                $min_qty = intval($price_info['min_qty'] ?? 1);
                $max_qty = $price_info['max_qty'] ?? '';
                $sold_qty = intval(ABPTF_Query::get_sold_qty($post_infos));
                $available_qty = $total_qty - $reserve_qty - $sold_qty;
                $max_qty = ($max_qty !== '' && intval($max_qty) <= $available_qty) ? intval($max_qty) : $available_qty;
                $min_qty = max($min_qty, 1);
                if ($max_qty >= $min_qty) {
                    $collapse_id = '#' . $post_id . '_' . $property_id;
                    ?>
                    <div class="select_property">
                        <input type="hidden" name="property_id[]" value="<?php echo esc_attr($property_id); ?>"/>
                        <input type="hidden" name="deposit_type[]" value="<?php echo esc_attr($deposit_type); ?>"/>
                        <input type="hidden" name="deposit_value[]" value="<?php echo esc_attr($deposit_value); ?>"/>
                        <div class="custom_checkbox">
                            <input type="hidden" name="property_check[]" value="" data-id="<?php echo esc_attr($collapse_id); ?>"/>
                            <div class="checkbox_item _fa_center _fs_label" data-checked="1" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                <h3 class="_abp"><span data-icon class="_mar_r_xs far fa-square"></span></h3>
                                <?php echo esc_html__('Select ', 'abp-transportforge') . ' ' . esc_html($name); ?>
                            </div>
                        </div>
                        <?php
                            if ($max_qty > $min_qty) {
                                $input_info = [
                                    'name' => 'property_qty[]',
                                    'price' => $total_price,
                                    'available' => $available_qty,
                                    'min_qty' => $min_qty,
                                    'max_qty' => $max_qty,
                                    'collapse_id' => $collapse_id,
                                ];
                                ABPTF_Layout::quantity_input($input_info);
                            } else {
                                ?>
                                <input type="hidden" name="property_qty[]" value="<?php echo esc_attr($min_qty); ?>" data-price="<?php echo esc_attr($total_price); ?>"/>
                                <?php
                            }
                        ?>
                    </div>
                    <?php
                } else {
                    ABPTF_Layout::layout_warning_info_xs('property_not_available');
                }
            }
            public static function create_client_form($form, $name): void {
                if (!is_array($form)) {
                    return;
                }
                $name = is_string($name) ? $name : '';
                $type = $form['type'] ?? '';
                $required = (($form['required'] ?? '') === 'on') ? 'required' : '';
                $label = $form['label'] ?? '';
                $d_value = $form['d_value'] ?? '';
                if ($type === 'text' || $type === 'number' || $type === 'email') {
                    $validation_class = match ($type) {
                        'text' => 'validation_name',
                        'number' => 'validation_number',
                        default => '',
                    };
                    ?>
                    <label class="_input_item">
                        <?php ABPTF_Layout::input_title($label, $required); ?>
                        <input type="<?php echo esc_attr($type); ?>"
                               name="<?php echo esc_attr($name); ?>"
                               value="<?php echo esc_attr($d_value); ?>"
                               class="_form_control <?php echo esc_attr($validation_class); ?>"
                               placeholder="<?php echo esc_attr($label); ?>"
                               title="<?php echo esc_attr($label); ?>"
                            <?php echo esc_attr($required); ?> />
                    </label>
                    <?php
                    return;
                }
                if ($type === 'date') {
                    ABPTF_Layout::input_date($name, $d_value, $label, $required);
                    return;
                }
                if ($type === 'textarea') {
                    ABPTF_Layout::textarea($name, $d_value, $label, $required);
                    return;
                }
                // Options bound input layouts (Select, Checkbox, Radio)
                if ($type === 'select' || $type === 'checkbox' || $type === 'radio') {
                    $options_str = $form['option'] ?? '';
                    $options = ($options_str !== '') ? explode(',', $options_str) : [];
                    match ($type) {
                        'select' => ABPTF_Layout::select($name, $d_value, $label, $required, $options),
                        'checkbox' => ABPTF_Layout::checkbox($name, $d_value, $label, $required, $options),
                        'radio' => ABPTF_Layout::radio($name, $d_value, $label, $required, $options),
                        default => null,
                    };
                }
            }
            //=============================//
            public static function ticket_info($ticket_infos): void {
                if (!empty($ticket_infos) && is_array($ticket_infos)) { ?>
                    <ul class=" _abp">
                        <?php foreach ($ticket_infos as $ticket_info) {
                            if (!empty($ticket_info) && sizeof($ticket_info) > 0) {
                                $name = $ticket_info['name'] ?? '';
                                $qty = $ticket_info['qty'] ?? 1;
                                $price = $ticket_info['price'] ?? '';
                                if (!empty($name)) { ?>
                                    <li>
                                        <strong><?php echo esc_html($name); ?></strong>
                                        <?php echo esc_html(' X ' . $qty . ' = ') . ' ' . (!empty($price) && $price > 0 ? wp_kses_post(wc_price($price)) : esc_html__('FREE', 'abp-transportforge')); ?>
                                    </li>
                                <?php }
                            }
                        } ?>
                    </ul>
                <?php }
            }
            public static function additional_info($additional_infos): void {
                if (!empty($additional_infos) && is_array($additional_infos)) { ?>
                    <ul class=" _abp">
                        <?php foreach ($additional_infos as $ex_info) {
                            if (!empty($ex_info) && sizeof($ex_info) > 0) {
                                $name = $ex_info['name'] ?? '';
                                $qty = $ex_info['qty'] ?? 1;
                                $price = $ex_info['price'] ?? '';
                                $returnable = $ex_info['returnable'] ?? 'no';
                                if (!empty($name)) { ?>
                                    <li>
                                        <strong><?php echo esc_html($name); ?></strong>
                                        <?php echo esc_html(' X ' . $qty . ' = ') . ' ' . (!empty($price) && $price > 0 ? wp_kses_post(wc_price($price)) : esc_html__('FREE', 'abp-transportforge')); ?>
                                        <?php
                                            if ($returnable == 'yes') {
                                                ?> <span class="_color_required"> - <?php esc_html_e('Returnable', 'abp-transportforge'); ?></span><?php
                                            } ?>
                                    </li>
                                    <?php
                                }
                            }
                        } ?>
                    </ul>
                <?php }
            }
            public static function client_info($passenger_infos): void {
                if (!empty($passenger_infos) && is_array($passenger_infos)) { ?>
                    <ul class=" _abp">
                        <?php foreach ($passenger_infos as $pas_form) {
                            if (!empty($pas_form) && sizeof($pas_form) > 0) {
                                $label = $pas_form['label'] ?? '';
                                $value = $pas_form['value'] ?? '';
                                if (!empty($label) && !empty($value)) { ?>
                                    <li>
                                        <strong><?php echo esc_html($label); ?></strong> : <?php echo esc_html($value); ?>
                                    </li>
                                    <?php
                                }
                            }
                        } ?>
                    </ul>
                <?php }
            }
            public static function billing_info($booking_list): void {
                if (!empty($booking_list)) {
                    $billing_name = $booking_list['billing_name'] ?? '';
                    $billing_email = $booking_list['billing_email'] ?? '';
                    $billing_phone = $booking_list['billing_phone'] ?? '';
                    $billing_address = $booking_list['billing_address'] ?? '';
                    ?>
                    <ul class=" _abp">
                        <?php if (!empty($billing_name)) { ?>
                            <li><strong><?php esc_html_e('Name :', 'abp-transportforge'); ?></strong>&nbsp;<?php echo esc_html($billing_name); ?></li>
                        <?php } ?>
                        <?php if (!empty($billing_email)) { ?>
                            <li><strong><?php esc_html_e('E-Mail :', 'abp-transportforge'); ?></strong>&nbsp;<?php echo esc_html($billing_email); ?></li>
                        <?php } ?>
                        <?php if (!empty($billing_phone)) { ?>
                            <li><strong><?php esc_html_e('Phone :', 'abp-transportforge'); ?></strong>&nbsp;<?php echo esc_html($billing_phone); ?></li>
                        <?php } ?>
                        <?php if (!empty($billing_address)) { ?>
                            <li><strong><?php esc_html_e('Address :', 'abp-transportforge'); ?></strong>&nbsp;<?php echo esc_html($billing_address); ?></li>
                        <?php } ?>
                    </ul>
                    <?php
                }
            }
            //=============================//
            public static function filter_post_list($post_id = 0): void {
                $label = ABPTF_Function::label();
                $all_post_ids = ABPTF_Query::get_post_id();
                $value = $post_id > 0 ? $post_id : '';
                $brand_icon = ABPTF_Function::icon();
                // echo '<pre>';print_r($configuration);echo '</pre>';
                ?>
                <div class="_input_item abp_dropdown">
                    <label>
                        <span><?php ABPTF_Layout::image_icon($brand_icon); ?><?php echo esc_html($label); ?></span>
                        <input type="hidden" name="post_id" value="<?php echo esc_attr($value); ?>"/>
                        <input type="text" class="_form_control_w_full" name="" placeholder="<?php echo esc_attr($label); ?>" value="<?php echo esc_attr(get_the_title($post_id)); ?>"/>
                    </label>
                    <?php if (sizeof($all_post_ids) > 0) { ?>
                        <div class="dropdown_list">
                            <ul class="_abp ">
                                <?php foreach ($all_post_ids as $all_post_id) {
                                    $sku = ABPTF_Function::get_post_info($all_post_id, 'post_sku');
                                    $category = ABPTF_Function::get_post_info($all_post_id, 'category');
                                    $category = !empty($category) ? get_term($category)->name : '';
                                    $title = get_the_title($all_post_id);
                                    ?>
                                    <li data-value="<?php echo esc_attr($all_post_id); ?>" data-text="<?php echo esc_attr($title); ?>">
                                        <?php if (ABPTF_Function::on_off('post_icon')) {
                                            ABPTF_Layout::image_icon(ABPTF_Function::get_post_info($all_post_id, 'post_icon'));
                                        } ?>
                                        <span class="_fs_label"><?php echo esc_html($title); ?></span>
                                        <?php if (!empty($category) && ABPTF_Function::on_off('category')) { ?>
                                            <sub class="_abp_color_gray"> - <?php echo esc_html($category); ?></sub>
                                        <?php } ?>
                                        <?php if (!empty($sku) && ABPTF_Function::on_off('sku')) { ?>
                                            <sub class="_abp_color_info"> - <?php echo esc_html($sku); ?></sub>
                                        <?php } ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
            public static function filter_booking_date(): void {
                $date_format = ABPTF_Function::date_format_php();
                $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                ?>
                <div class="_input_item">
                    <label class="_fd_column">
                        <span>📅 <?php esc_html_e('Booking Date', 'abp-transportforge') ?></span>
                        <input type="hidden" name="start_time" value=""/>
                        <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                        <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transportforge'); ?>"></span>
                    </label>
                </div>
                <?php
            }
            public static function filter_order_date(): void {
                $date_format = ABPTF_Function::date_format_php();
                $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                ?>
                <div class="_input_item">
                    <label class="_fd_column">
                        <span>🗓️ <?php esc_html_e('Order Date', 'abp-transportforge') ?></span>
                        <input type="hidden" name="order_date" value=""/>
                        <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                        <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transportforge'); ?>"></span>
                    </label>
                </div>
                <?php
            }
            public static function filter_booking_date_between(): void {
                $date_format = ABPTF_Function::date_format_php();
                $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                ?>
                <div class="_g_input_input_item_fd_column">
                    <label><span>⏰ <?php esc_html_e('Booking Date Between', 'abp-transportforge'); ?></span></label>
                    <div class="_f_equal">
                        <label>
                            <input type="hidden" name="booking_time_from" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transportforge'); ?>"></span>
                        </label>
                        <label>
                            <input type="hidden" name="booking_time_to" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transportforge'); ?>"></span>
                        </label>
                    </div>
                </div>
                <?php
            }
            public static function filter_order_date_between(): void {
                $date_format = ABPTF_Function::date_format_php();
                $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                ?>
                <div class="_g_input_input_item_fd_column" data-collapse="#view_more_filter_option">
                    <label><span>⏰ <?php esc_html_e('Order Date Between', 'abp-transportforge'); ?></span></label>
                    <div class="_f_equal">
                        <label>
                            <input type="hidden" name="order_date_from" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transportforge'); ?>"></span>
                        </label>
                        <label>
                            <input type="hidden" name="order_date_to" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr($now); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e('Clear Date', 'abp-transportforge'); ?>"></span>
                        </label>
                    </div>
                </div>
                <?php
            }
            public static function filter_user_id(): void {
                $all_users = get_users(array(
                    'fields' => array('ID', 'display_name'),
                ));
                ?>
                <div class="_input_item abp_dropdown ">
                    <label class="_fd_column">
                        <span>👨‍💼  <?php esc_html_e('User Name', 'abp-transportforge'); ?></span>
                        <input type="hidden" name="user_id" value=""/>
                        <input type="text" class="_form_control_w_full" placeholder="<?php esc_attr_e('User Name', 'abp-transportforge'); ?>" value=""/>
                    </label>
                    <?php if (!empty($all_users)) { ?>
                        <div class="dropdown_list">
                            <ul class="_abp ">
                                <?php foreach ($all_users as $user) { ?>
                                    <li data-value="<?php echo esc_attr($user->ID); ?>" data-text="<?php echo esc_attr($user->display_name); ?>">
                                        <span class="_fs_label"><?php echo esc_html($user->display_name); ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
            public static function filter_order_id(): void {
                ?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label class="_fd_column">
                        <span>📦 <?php esc_html_e('Order ID', 'abp-transportforge'); ?></span>
                        <input type="number" class="_form_control_w_full validation_number" name="order_id" placeholder="<?php esc_attr_e('Order ID', 'abp-transportforge'); ?>" value=""/>
                    </label>
                </div>
                <?php
            }
            public static function filter_bill_name(): void {
                ?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label class="_fd_column">
                        <span>👤 <?php esc_html_e('Billing Name', 'abp-transportforge'); ?></span>
                        <input type="text" class="_form_control_w_full " name="billing_name" placeholder="<?php esc_attr_e('Billing Name', 'abp-transportforge'); ?>" value=""/>
                    </label>
                </div>
                <?php
            }
            public static function filter_bill_email(): void {
                ?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label class="_fd_column">
                        <span>✉️ <?php esc_html_e('Billing Email', 'abp-transportforge'); ?></span>
                        <input type="email" class="_form_control_w_full " name="billing_email" placeholder="<?php esc_attr_e('Billing Email', 'abp-transportforge'); ?>" value=""/>
                    </label>
                </div>
                <?php
            }
            public static function filter_bill_phone(): void {
                ?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label class="_fd_column">
                        <span>☎️ <?php esc_html_e('Billing phone', 'abp-transportforge'); ?></span>
                        <input type="text" class="_form_control_w_full " name="billing_phone" placeholder="<?php esc_attr_e('Billing phone', 'abp-transportforge'); ?>" value=""/>
                    </label>
                </div>
                <?php
            }
            public static function filter_location(): void {
                $all_locations = defined('ABPTF_Locations') ? ABPTF_Locations : [];
                if (empty($all_locations) || !is_array($all_locations)) {
                    return;
                }
                ?>
                <div class="_input_item abp_dropdown">
                    <label class="_fd_column">
                        <span>📍 <?php esc_html_e('Location', 'abp-transportforge'); ?></span>
                        <input type="hidden" name="location" value=""/>
                        <input type="text" class="_form_control_w_full" placeholder="<?php esc_attr_e('Location', 'abp-transportforge'); ?>" value=""/>
                    </label>
                    <div class="dropdown_list">
                        <ul class="_abp">
                            <?php
                                foreach ($all_locations as $key => $location) {
                                    $name = is_array($location) ? ($location['name'] ?? '') : '';
                                    ?>
                                    <li data-value="<?php echo esc_attr($key); ?>" data-text="<?php echo esc_attr($name); ?>">
                                        <span class="_fs_label"><?php echo esc_html($name); ?></span>
                                    </li>
                                    <?php
                                }
                            ?>
                        </ul>
                    </div>
                </div>
                <?php
            }
        }
        new ABPTF_Layout();
    }