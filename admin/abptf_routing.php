<?php
    if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.
    if (!class_exists('ABPTF_Routing')) {
        class ABPTF_Routing {
            public function __construct() {
                add_action('abptf_post_content', [$this, 'routing']);
            }
            public function routing($post_infos = []): void {
                $routing_infos = $post_infos['routing_infos'] ?? [];
                $return_routing_infos = $post_infos['return_routing_infos'] ?? [];
                $display_return = $post_infos['display_return'] ?? 'off';
                ?>
                <div class="tab_item abptf_routing" data-tabs="#abptf_routing">
                    <div class="_fj_between_fa_center">
                        <?php if (ABPTF_Function::on_off('return')) { ?>
                            <div class="_fa_center">
                                <?php ABPTF_Layout::switch_checkbox('display_return', $display_return); ?>
                                <span class="_abp_label"><?php esc_html_e('Same Transport Return', 'abp-transportforge'); ?></span>
                            </div>
                        <?php } else { ?>
                            <h4 class="_abp_color_theme"><span class="fas fa-route _mar_r_xs"></span><?php esc_html_e('Route Configuration', 'abp-transportforge'); ?></h4>
                        <?php } ?>
                        <?php ABPTF_Layout::button_global_popup('tax_location', __('Add New', 'abp-transportforge') . ' ' . ABPTF_Function::location_label()); ?>
                    </div>
                    <div class="_divider_xxs"></div>
                    <?php ABPTF_Layout::info_text('route_config'); ?>
                    <div class="_divider_xs"></div>
                    <div class="_f_wrap _f_equal _gap_xs">
                        <div class="configuration_content">
                            <h6 class="_abp_color_theme_mar_b_xxs <?php echo esc_attr($display_return == 'on' ? 'abp_active' : ''); ?>" data-collapse="#display_return"><span class="fas fa-route _mar_r_xs"></span><?php esc_html_e('Forward Route Configuration', 'abp-transportforge'); ?></h6>
                            <div class="_ov_auto">
                                <table class="_abp">
                                    <thead>
                                    <tr>
                                        <th class="_w_50"></th>
                                        <th><span class="fas fa-route _mar_r_xs"></span><?php esc_html_e('Stops Name', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                                        <th><?php esc_html_e('Stops Type', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                                        <th><?php esc_html_e('Time(in min)', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                                        <?php if (ABPTF_Function::on_off('pickup')) { ?>
                                            <th><?php esc_html_e('Pickup/Drop', 'abp-wc-transport-manager'); ?></th>
                                        <?php } ?>
                                        <th class="_w_75"><?php esc_html_e('Action', 'abp-wc-transport-manager'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody class="insertable_area sortable_area route_configuration">
                                    <?php
                                        if (!empty($routing_infos)) {
                                            foreach ($routing_infos as $routing_info) {
                                                $this->stop_item($routing_info);
                                            }
                                        }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::button_add(__('Add New Route', 'abp-wc-transport-manager')); ?>
                            <div class="abp_hidden">
                                <table class="_abp">
                                    <tbody class="hidden_content">
                                    <?php $this->stop_item(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php if (ABPTF_Function::on_off('return')) { ?>
                            <div class="configuration_content <?php echo esc_attr($display_return == 'on' ? 'abp_active' : ''); ?>" data-collapse="#display_return">
                                <h6 class="_abp_color_theme_mar_b_xxs"><span class="fas fa-route _mar_r_xs"></span><?php esc_html_e('Return Route Configuration', 'abp-transportforge'); ?></h6>
                                <div class="_ov_auto">
                                    <table class="_abp">
                                        <thead>
                                        <tr>
                                            <th class="_w_50"></th>
                                            <th><span class="fas fa-route _mar_r_xs"></span><?php esc_html_e('Stops Name', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                                            <th><?php esc_html_e('Stops Type', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                                            <th><?php esc_html_e('Time(in min)', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                                            <?php if (ABPTF_Function::on_off('pickup')) { ?>
                                                <th><?php esc_html_e('Pickup/Drop', 'abp-wc-transport-manager'); ?></th>
                                            <?php } ?>
                                            <th class="_w_75"><?php esc_html_e('Action', 'abp-wc-transport-manager'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody class="insertable_area sortable_area route_configuration">
                                        <?php
                                            if (!empty($return_routing_infos)) {
                                                foreach ($return_routing_infos as $routing_info) {
                                                    $this->stop_item($routing_info, 'return_');
                                                }
                                            }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="_divider_xs"></div>
                                <?php ABPTF_Layout::button_add(__('Add New Return Route', 'abp-wc-transport-manager')); ?>
                                <div class="abp_hidden">
                                    <table class="_abp">
                                        <tbody class="hidden_content">
                                        <?php $this->stop_item([], 'return_'); ?> ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
            }
            public function stop_item($field = [],$prefix=''): void {
                $stop = $field['stop'] ?? '';
                $type = $field['type'] ?? 'bp';
                ?>
                <tr class="delete_area data_single_collapse">
                    <th class="_text_table_center "><span class="fas fa-arrow-down"></span></th>
                    <td>
                        <label>
                            <select name="<?php echo esc_attr($prefix);?>stop_name[]" class='_form_control_w_full'>
                                <option value="" selected><?php esc_html_e('Select Stops.', 'abp-wc-transport-manager'); ?></option>
                                <?php if (!empty(ABPTF_Locations)) {
                                    foreach (ABPTF_Locations as $key => $location) { ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($key == $stop ? 'selected' : ''); ?>><?php echo esc_html($location['name'] ?? ''); ?></option>
                                        <?php
                                    }
                                } ?>
                            </select>
                        </label>
                    </td>
                    <th>
                        <label>
                            <select name="<?php echo esc_attr($prefix);?>stop_type[]" class='_form_control_w_full'>
                                <option value="bp" <?php echo esc_attr($type == 'bp' ? 'selected' : ''); ?>><?php esc_html_e('Boarding', 'abp-wc-transport-manager'); ?></option>
                                <option value="dp" <?php echo esc_attr($type == 'dp' ? 'selected' : ''); ?>><?php esc_html_e('Dropping', 'abp-wc-transport-manager'); ?></option>
                                <option value="both" <?php echo esc_attr($type == 'both' ? 'selected' : ''); ?>><?php esc_html_e('Both', 'abp-wc-transport-manager'); ?></option>
                            </select>
                        </label>
                    </th>
                    <th>
                        <label>
                            <input type="number" class="_form_control_w_full validation_number" name="<?php echo esc_attr($prefix);?>stop_time[]" value="<?php echo esc_attr($field['time'] ?? ''); ?>"/>
                        </label>
                    </th>
                    <?php if (ABPTF_Function::on_off('pickup')) { ?>
                        <td>
                            <div class="_all_center">
                                <?php ABPTF_Layout::switch_checkbox('<?php echo esc_attr($prefix);?>display_pd[]', ($field['display_pd'] ?? 'on')); ?>
                            </div>
                        </td>
                    <?php } ?>
                    <td><?php ABPTF_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }
        }
        new ABPTF_Routing();
    }