<?php
    if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.
    if (!class_exists('ABPTF_Ticket_Price')) {
        class ABPTF_Ticket_Price {
            public function __construct() {
                add_action('abptf_post_content', [$this, 'ticket_price']);
            }
            public function ticket_price($post_infos = []): void {
                $seat_type = $post_infos['seat_type'] ?? 'sp';
                $seat_type = ABPTF_Function::on_off('sp') ? $seat_type : 'ticket';
                $display_ticket_type = $post_infos['display_ticket_type'] ?? 'on';
                $display_ticket_type = ABPTF_Function::on_off('sp') ? $display_ticket_type : 'off';
                $ticket_info_key = ABPTF_Function::on_off('sp') ? 'seat_type' : 'ticket_type';
                $multi_info_key = ABPTF_Function::on_off('ticket_type') ? 'display_ticket_type' : 'single_ticket_type';
                ?>
                <div class="tab_item abptf_ticket" data-tabs="#abptf_ticket">
                    <div class="group_setting">
                        <div class="setting_item">
                            <label class="_fj_between">
                                <span class="_abp_label"><?php esc_html_e('Seat Type', 'abp-wc-transport-manager'); ?></span>
                                <?php if (ABPTF_Function::on_off('sp')) { ?>
                                    <select class="_form_control " name="seat_type" required data-collapse-target>
                                        <option disabled selected> <?php esc_html_e('Please Select', 'abp-wc-transport-manager'); ?></option>
                                        <option value="sp" <?php echo esc_attr($seat_type == 'sp' ? 'selected' : ''); ?> data-option-target="#abp_seat_type"><?php esc_html_e('Seat Plan', 'abp-wc-transport-manager'); ?></option>
                                        <option value="ticket" <?php echo esc_attr($seat_type == 'ticket' ? 'selected' : ''); ?> data-option-target="#abp_ticket_type"><?php esc_html_e('Ticket', 'abp-wc-transport-manager'); ?></option>
                                    </select>
                                <?php } else { ?>
                                    <input type="hidden" name="seat_type" value="<?php echo esc_attr($seat_type); ?>">
                                    <span class="_abp_label"><?php esc_html_e('Ticket', 'abp-wc-transport-manager'); ?></span>
                                <?php } ?>
                            </label>
                            <div class="_divider_xs"></div>
                            <?php ABPTF_Layout::info_text($ticket_info_key); ?>
                        </div>
                        <div class="setting_item">
                            <?php if (ABPTF_Function::on_off('ticket_type')) { ?>
                                <div class="_fa_center">
                                    <?php ABPTF_Layout::switch_checkbox('display_ticket_type', $display_ticket_type); ?>
                                    <span class="_abp_label"><?php esc_html_e('Multiple Ticket Type?', 'abp-transportforge'); ?></span>
                                </div>
                            <?php } else { ?>
                                <div class="_fj_between">
                                    <input type="hidden" name="display_ticket_type" value="<?php echo esc_attr($display_ticket_type); ?>">
                                    <span class="_abp_label"><?php esc_html_e('Ticket Type', 'abp-transportforge'); ?></span>
                                    <span class="_abp_label"><?php esc_html_e('Single', 'abp-wc-transport-manager'); ?></span>
                                </div>
                            <?php } ?>
                            <div class="_divider_xxs"></div>
                            <?php ABPTF_Layout::info_text($multi_info_key); ?>
                        </div>
                        <?php $this->seat_type($post_infos, $seat_type, $display_ticket_type); ?>
                        <?php $this->ticket_type($post_infos, $seat_type, $display_ticket_type); ?>
                    </div>
                </div>
                <?php
            }
            public function seat_type($post_infos, $seat_type, $display_ticket_type): void {
                $ticket_infos = $post_infos['ticket_infos'] ?? [];
                if (ABPTF_Function::on_off('sp')) {
                    ?>
                <div class="seat_configuration configuration_content setting_item full_width <?php echo esc_attr($seat_type == 'sp' ? 'abp_active' : ''); ?>" data-collapse="#abp_seat_type"></div>
                <?php
                }
            }
            public function ticket_type($post_infos, $seat_type, $display_ticket_type): void {
                $ticket_infos = $post_infos['ticket_infos'] ?? [];
                $counter = 0;
                ?>
                <div class="ticket_configuration configuration_content setting_item full_width <?php echo esc_attr($seat_type == 'ticket' ? 'abp_active' : ''); ?>" data-collapse="#abp_ticket_type">
                    <div class="_fj_between">
                        <h5 class="_abp"><?php esc_html_e('Ticket Configuration', 'abp-wc-transport-manager'); ?></h5>
                        <?php if (ABPTF_Function::on_off('ticket_type')) { ?>
                            <div class="<?php echo esc_attr($display_ticket_type == 'on' ? 'abp_active' : ''); ?>" data-collapse="#display_ticket_type">
                                <div class="_group_content">
                                    <?php ABPTF_Layout::button_add(__('Add New item', 'abp-wc-transport-manager'));
                                        ABPTF_Layout::button_global_popup('ticket_type', __('Add New Ticket Type', 'abp-transportforge')); ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="_divider_xxs"></div>
                    <div class="_ov_auto">
                        <table class="_abp">
                            <thead>
                            <tr>
                                <th><?php esc_html_e('Icon / Image', 'abp-wc-transport-manager'); ?></th>
                                <th><?php esc_html_e('Name', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                                <th><?php esc_html_e('Quantity', 'abp-wc-transport-manager'); ?><sup class="_color_required">*</sup></th>
                                <th><?php esc_html_e('Max qty/Order', 'abp-wc-transport-manager'); ?></th>
                                <th><?php esc_html_e('Description', 'abp-wc-transport-manager'); ?></th>
                                <th><?php esc_html_e('Action', 'abp-wc-transport-manager'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="insertable_area sortable_area">
                            <?php
                                if (!empty($ticket_infos)) {
                                    if ($display_ticket_type === 'on') {
                                        $key = array_key_first($ticket_infos);
                                        $this->ticket_item($display_ticket_type, $ticket_infos[$key], $key);
                                    } else {
                                        foreach ($ticket_infos as $key => $ticket_info) {
                                            $this->ticket_item($display_ticket_type, $ticket_info, $key, $counter);
                                        }
                                    }
                                } else {
                                    $this->ticket_item($display_ticket_type);
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="_divider_xxs"></div>
                    <div class="abp_hidden">
                        <table class="_abp">
                            <tbody class="hidden_content">
                            <?php $this->ticket_item($display_ticket_type, [], '', 10); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }
            public function ticket_item($display_ticket_type, $field = array(), $id = '', $counter = 0): void {
                $field = $field ?: [];
                $qty = $field['qty'] ?? '';
                $max_qty = $field['max_qty'] ?? '';
                $description = $field['description'] ?? '';
                ?>
                <tr class="delete_area <?php echo esc_attr($display_ticket_type == 'on' ? 'abp_active' : ''); ?>" <?php if ($counter > 0){ ?>data-collapse="#display_ticket_type" <?php } ?>>
                    <td> <?php do_action('abptf_add_image_icon', 'ticket_icon[]', ($field['icon'] ?? '')); ?>  </td>
                    <td>
                        <label>
                            <select name="ticket_name[]" class='_form_control'>
                                <option value="" selected><?php esc_html_e('Select Ticket Name.', 'abp-wc-transport-manager'); ?></option>
                                <?php if (!empty(ABPTF_ticket_type)) {
                                    foreach (ABPTF_ticket_type as $key => $ticket) { ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($key == $id ? 'selected' : ''); ?>><?php echo esc_html($ticket['label'] ?? ''); ?></option>
                                        <?php
                                    }
                                } ?>
                            </select>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="ticket_qty[]" placeholder="<?php esc_attr_e('EX: 15', 'abp-transportforge'); ?>" value="<?php echo esc_attr($qty); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="ticket_max_qty[]" placeholder="<?php esc_attr_e('EX: 15', 'abp-transportforge'); ?>" value="<?php echo esc_attr($max_qty); ?>"/>
                        </label>
                    </td>
                    <td>
                        <label>
                            <textarea class="_form_control" name="ticket_description[]" placeholder="<?php esc_attr_e('EX: Description', 'abp-transportforge'); ?>"><?php echo esc_html($description); ?></textarea>
                        </label>
                    </td>
                    <td><?php ABPTF_Layout::button_delete_sort(); ?></td>
                </tr>
                <?php
            }
        }
        new ABPTF_Ticket_Price();
    }