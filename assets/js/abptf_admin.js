let abptf_feature_data = JSON.parse(abptf_admin_data.feature_data);
let abptf_related_info = JSON.parse(abptf_admin_data.related_info);
function abptf_save_data(form_area, target, action) {
    let formData = abprf_get_form_data(form_area);
    formData.append('action', action);
    jQuery.ajax({
        type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
        beforeSend: function () {
            abptf_spinner(form_area);
            abptf_toast_msg(abptf_admin_data.msg.saving);
        },
        success: function (response) {
            abptf_spinner_remove(form_area);
            abptf_toast_msg(response.data.msg, 'success');
            if (target && target.length > 0) {
                target.html(response.data.html);
            }
        }
    });
}
function abptf_load_post_list(parent, filter_args) {
    let target = parent.find('.post_list');
    if (target.length > 0) {
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_reload_post_list", "filter_args": filter_args, 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.post_loading);
            }, success: function (response) {
                target.html(response.data.html);
                abptf_spinner_remove(parent);
                abptf_toast_msg(response.data.html, 'success');
            }
        });
    } else {
        parent.find('.post_tab').trigger('click');
    }
}
function abptf_load_sortable_datepicker(parent, item) {
    if (parent.find('.insertable_area_before').length > 0) {
        jQuery(item).insertBefore(parent.find('.insertable_area_before').first()).promise().done(function () {
            parent.find('.sortable_area').sortable({
                handle: jQuery(this).find('.sortable_handle')
            });
            abptf_load_datepicker(parent);
        });
    } else {
        parent.find('.insertable_area').first().append(item).promise().done(function () {
            parent.find('.sortable_area').sortable({
                handle: jQuery(this).find('.sortable_handle')
            });
            abptf_load_datepicker(parent);
        });
    }
    return true;
}
function abptf_wp_editor_init(target) {
    let textArea = target.find('textarea.wp-editor-area');
    if (textArea.length > 0) {
        let uniqueId = 'editor_' + Math.random().toString(36).substring(2, 11);
        if (target.find('.wp-editor-wrap').length > 0) {
            target.find('.wp-editor-wrap').replaceWith(textArea);
        }
        textArea.attr('id', uniqueId).show();
        setTimeout(function () {
            if (typeof wp !== 'undefined' && wp.editor) {
                wp.editor.remove(uniqueId);
                wp.editor.initialize(uniqueId, {
                    tinymce: {
                        wpautop: true,
                        cleanup: false,
                        verify_html: false,
                        entity_encoding: 'raw',
                        forced_root_block: false,
                        valid_elements: '*[*]',
                        setup: function (editor) {
                            editor.on('change', function () {
                                editor.save();
                            });
                        }
                    },
                    quicktags: true,
                    mediaButtons: true
                });
            }
        }, 100);
    }
}
function abptf_emoji_check(str) {
    return !(/^fa[bsrld]\s/.test(str));
}
function abprf_get_form_data(form_area) {
    let formData = new FormData();
    form_area.find('input, select, textarea').each(function () {
        let name = jQuery(this).attr('name');
        let value = jQuery(this).val();
        if (name) {
            if (jQuery(this).attr('type') === 'checkbox' || jQuery(this).attr('type') === 'radio') {
                if (jQuery(this).is(':checked')) {
                    formData.append(name, value);
                }
            } else {
                formData.append(name, value);
            }
        }
    });
    let post_page = jQuery("body [name='abptf_post_id']");
    if (post_page.length > 0) {
        formData.append('abptf_post_id', post_page.val());
    }
    formData.append('nonce', abptf_admin_data.nonce);
    return formData;
}
(function ($) {
    "use strict";
    $(document).ready(function () {
        //=========== Feature  selection=================//
        new ABPTF_Multi_Selection('div.abptf_admin .post_feature', abptf_feature_data);
        //=========== Related post  selection=================//
        new ABPTF_Multi_Selection('div.abptf_admin .related_item', abptf_related_info);
        //=========Color Picker==============//
        $('.abptf_color_picker').wpColorPicker();
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.abptf_color_picker').length) {
                $('.wp-picker-container.wp-picker-active').find('.wp-color-result').trigger('click');
            }
        });
        //=========Short able==============//
        $(document).find('div.abptf_area .sortable_area').sortable({
            handle: $(this).find('.sortable_handle'),
            stop: function (event, ui) {
                ui.item.trigger('abp_trigger');
            }
        });
    });
    //========== Global popup =================//
    $(document).on("abp_trigger", "div.abptf_admin [data-popup='#abptf_global_popup'] .popup_close", function () {
        $(this).closest('.abptf_popup').find('.popup_body').html('');
    });
    $(document).on("abp_trigger", "div.abptf_admin [data-target-popup='#abptf_global_popup']", function () {
        let tax_id = $(this).attr('data-id');
        let type = $(this).attr('data-type');
        let action = '';
        if (type === 'category') {
            action = 'abptf_add_category';
        } else if (type === 'location') {
            action = 'abptf_add_location';
        } else if (type === 'brand') {
            action = 'abptf_add_brand';
        } else if (type === 'organizer') {
            action = 'abptf_add_organizer';
        } else if (type === 'feature') {
            action = 'abptf_add_feature';
        }
        if (action) {
            tax_id = (typeof tax_id !== 'undefined' && tax_id !== false) ? parseInt(tax_id) : '';
            let body = $('body');
            let target_id = $(this).attr('data-active-popup', '').data('target-popup');
            let parent = body.find('[data-popup="' + target_id + '"]').find('.popup_area');
            let target = parent.find('.popup_body');
            let post_id = body.find("[name='abptf_post_id']").val();
            $.ajax({
                type: 'POST', url: abptf_admin_data.ajax_url, data: {
                    "action": action, 'tax_id': tax_id, 'post_id': post_id, 'nonce': abptf_admin_data.nonce
                }, beforeSend: function () {
                    abptf_spinner(parent);
                    abptf_toast_msg(abptf_admin_data.msg.loading);
                }, success: function (response) {
                    abptf_spinner_remove(parent);
                    target.html(response.data.html).promise().done(function () {
                        abptf_toast_msg(response.data.msg, 'success');
                        abptf_load_more();
                        abptf_load_datepicker(target);
                    });
                }
            })
        }
    });
    //==========Post List=================//
    $(document).on("click", "div.abptf_admin button.post_permanent_remove", function () {
        let post_id = $(this).attr('data-post_id');
        let parent = $(this).closest('.abptf_posts');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_post_permanent_remove", 'post_id': post_id, 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.post_deleting, 'error');
            }, success: function (response) {
                abptf_spinner_remove(parent);
                abptf_toast_msg(response.data.msg, 'warn');
                window.location.reload();
            }
        })
    });
    $(document).on("click", "div.abptf_admin button.post_move_trash", function () {
        let post_id = $(this).attr('data-post_id');
        let parent = $(this).closest('.abptf_posts');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_post_move_trash", 'post_id': post_id, 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.post_trashing, 'error');
            }, success: function (response) {
                abptf_spinner_remove(parent);
                abptf_toast_msg(response.data.msg, 'warn');
                window.location.reload();
            }
        })
    });
    $(document).on("click", "div.abptf_admin button.post_restore", function () {
        let post_id = $(this).attr('data-post_id');
        let parent = $(this).closest('.abptf_posts');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_post_restore", 'post_id': post_id, 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.post_restoring, 'info');
            }, success: function (response) {
                abptf_spinner_remove(parent);
                abptf_toast_msg(response.data.msg, 'success');
                window.location.reload();
            }
        })
    });
    $(document).on('click', 'div.abptf_admin .post_list .pagination_area button[data-page]', function () {
        let $this = $(this);
        if (!$this.hasClass('abp_active')) {
            let parent = $(this).closest('.abptf_posts');
            let filter_args = {};
            if (parent.find("[name='select_hidden_post_status']").length > 0) {
                filter_args['status'] = parent.find("[name='select_hidden_post_status']").val();
            }
            filter_args['page_number'] = parseInt($this.attr('data-page'));
            if (parent.find("[name='page_item']").length > 0) {
                filter_args['page_item'] = parseInt(parent.find("[name='page_item']").val());
            }
            abptf_load_post_list(parent, filter_args);
        }
    });
    //==========Orders list=================//
    $(document).on('submit', 'div.abptf_admin form.load_order_list', function (e) {
        e.preventDefault();
        let parent = $(this).closest('.abptf_orders');
        let target = parent.find('.order_list');
        let formData = new FormData(this);
        if (parent.find('[data-page].abp_active').length > 0) {
            formData.append('page_number', parseInt(parent.find('[data-page].abp_active').attr('data-page')));
        }
        formData.append('page_item', parseInt(parent.find("[name='page_item']").val()));
        formData.append('status', parent.find('.order_status_menu [data-status].abp_active').attr('data-status'));
        formData.append('action', 'abptf_load_order_list');
        formData.append('nonce', abptf_admin_data.nonce);
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.order_loading);
            },
            success: function (response) {
                abptf_spinner_remove(parent);
                target.html(response.data.html);
                abptf_toast_msg(response.data.msg, 'success');
                abptf_load_more();
            }
        });
    });
    $(document).on('click', 'div.abptf_admin .order_status_menu button[data-status]', function () {
        let $this = $(this);
        if (!$this.hasClass('abp_active')) {
            $this.closest('.order_status_menu').find('[data-status].abp_active').removeClass('abp_active').promise().done(function () {
                $this.addClass('abp_active').promise().done(function () {
                    $this.closest('.abptf_orders').find('form.load_order_list').submit();
                });
            });
        }
    });
    $(document).on('click', 'div.abptf_admin button.abptf_item_cancel', function () {
        let $this = $(this);
        let parent = $(this).closest('.abptf_orders');
        let item_id = $this.attr('data-item_id');
        if (confirm(abptf_admin_data.msg.confirm_delete + ' \n\n' + abptf_admin_data.msg.confirm_ok + ' \n ' + abptf_admin_data.msg.confirm_cancel)) {
            $.ajax({
                type: 'POST', url: abptf_admin_data.ajax_url, data: {
                    "action": "abptf_item_cancel", 'item_id': item_id, 'nonce': abptf_admin_data.nonce
                }, beforeSend: function () {
                    abptf_spinner(parent);
                    abptf_toast_msg(abptf_admin_data.msg.deleting, 'error');
                }, success: function (response) {
                    abptf_spinner_remove(parent);
                    abptf_toast_msg(response.data.msg);
                    $this.closest('.abptf_orders').find('form.load_order_list').submit();
                }
            });
        }
    });
    $(document).on('click', 'div.abptf_admin .order_list .pagination_area button[data-page]', function () {
        let $this = $(this);
        if (!$this.hasClass('abp_active')) {
            let parent = $(this).closest('.order_list');
            parent.find('[data-page].abp_active').removeClass('abp_active').promise().done(function () {
                $this.addClass('abp_active').promise().done(function () {
                    $this.closest('.abptf_orders').find('form.load_order_list').submit();
                });
            });
        }
    });
//========== Dates configuration=================//
    $(document).on('submit', 'div.abptf_admin form.save_dates', function (e) {
        e.preventDefault();
        let target = $(this);
        let formData = new FormData(this);
        formData.append('action', 'abptf_save_global_date');
        formData.append('nonce', abptf_admin_data.nonce);
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg(abptf_admin_data.msg.saving);
            },
            success: function (response) {
                abptf_toast_msg(response.data.msg, response.data.type);
                window.location.reload();
            }
        });
    });
    $(document).on('click', 'div.abptf_admin button.import_dates', function () {
        let parent = $(this).closest('.date_configuration');
        let target = parent.find('.date_content');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_import_date", 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg(abptf_admin_data.msg.importing);
            }, success: function (response) {
                if (response.data) {
                    if (response.data.hasOwnProperty('html')) {
                        target.html(response.data.html).promise().done(function () {
                            target.find('.sortable_area').sortable({
                                handle: jQuery(this).find('.sortable_handle')
                            });
                            abptf_load_datepicker(target);
                            abptf_load_more();
                        });
                    }
                    abptf_toast_msg(response.data.msg, response.data.type);
                }
            }
        });
    });
//==========Additional configuration=================//
    $(document).on('submit', 'div.abptf_admin form.save_additional_service', function (e) {
        e.preventDefault();
        let target = $(this);
        let formData = new FormData(this);
        formData.append('action', 'abptf_save_additional_service');
        formData.append('nonce', abptf_admin_data.nonce);
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg(abptf_admin_data.msg.saving);
            },
            success: function (response) {
                abptf_toast_msg(response.data.msg, 'success');
                abptf_spinner_remove(target);
            }
        });
    });
    $(document).on('click', 'div.abptf_admin button.import_additional', function () {
        let parent = $(this).closest('.additional_configuration');
        let target = parent.find('.additional_content');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_import_additional", 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg(abptf_admin_data.msg.importing);
            }, success: function (response) {
                target.html(response.data.html).promise().done(function () {
                    target.find('.sortable_area').sortable({
                        handle: jQuery(this).find('.sortable_handle')
                    });
                    abptf_toast_msg(response.data.msg, 'success');
                });
            }
        });
    });
//==========Client Form configuration=================//
    $(document).on('submit', 'div.abptf_admin form.save_client_form', function (e) {
        e.preventDefault();
        let target = $(this);
        let formData = new FormData(this);
        formData.append('action', 'abptf_save_client_form');
        formData.append('nonce', abptf_admin_data.nonce);
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg(abptf_admin_data.msg.saving);
            },
            success: function (response) {
                abptf_toast_msg(response.data.msg, 'success');
                abptf_spinner_remove(target);
            }
        });
    });
    $(document).on('click', 'div.abptf_admin button.import_global_form', function () {
        let parent = $(this).closest('.abptf_client_form');
        let target = parent.find('.client_form_content');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_import_global_form", 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg(abptf_admin_data.msg.importing);
            }, success: function (response) {
                target.html(response.data.html).promise().done(function () {
                    target.find('.sortable_area').sortable({
                        handle: jQuery(this).find('.sortable_handle')
                    });
                    abptf_toast_msg(response.data.msg, 'success');
                });
            }
        });
    });
    //==========category configuration=================//
    $(document).on('click', 'div.abptf_admin button.save_category', function (e) {
        e.preventDefault();
        let $this = $(this);
        let body = $('body .abp_post_config');
        let target = (body.find("[name='abptf_post_id']").length > 0) ? body.find('.category_selection') : $('div.abptf_admin .category_list');
        let form_area = $this.closest('.popup_body');
        let formData = abprf_get_form_data(form_area);
        formData.append('action', 'abptf_save_category');
        jQuery.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(form_area);
                abptf_toast_msg(abptf_admin_data.msg.saving);
            },
            success: function (response) {
                abptf_spinner_remove(form_area);
                if (target && target.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html);
                }
                abptf_popup_close('#abptf_global_popup');
                abptf_toast_msg(response.data.msg, 'success');
            }
        });
    });
    $(document).on('click', 'div.abptf_admin button.delete_category', function (e) {
        e.preventDefault();
        let parent = $(this).closest('.category_list');
        let cat_id = parseInt($(this).attr('data-cat_id'));
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_delete_category", "cat_id": cat_id, 'nonce': abptf_admin_data.nonce
            },
            beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.deleting, 'error');
            },
            success: function (response) {
                abptf_spinner_remove(parent);
                abptf_toast_msg(response.data.msg);
                parent.html(response.data.html);
            }
        });
    });
    //==========Organizer configuration=================//
    $(document).on('click', 'div.abptf_admin button.save_organizer', function (e) {
        e.preventDefault();
        let $this = $(this);
        let body = $('body .abp_post_config');
        let target = (body.find("[name='abptf_post_id']").length > 0) ? body.find('.organizer_selection') : $('div.abptf_admin .organizer_list');
        let form_area = $this.closest('.popup_body');
        let formData = abprf_get_form_data(form_area);
        formData.append('action', 'abptf_save_organizer');
        jQuery.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(form_area);
                abptf_toast_msg(abptf_admin_data.msg.saving);
            },
            success: function (response) {
                abptf_spinner_remove(form_area);
                if (target && target.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html);
                }
                abptf_popup_close('#abptf_global_popup');
                abptf_toast_msg(response.data.msg, 'success');
            }
        });
    });
    $(document).on('click', 'div.abptf_admin button.delete_organizer', function (e) {
        e.preventDefault();
        let parent = $(this).closest('.organizer_list');
        let cat_id = parseInt($(this).attr('data-cat_id'));
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_delete_organizer", "cat_id": cat_id, 'nonce': abptf_admin_data.nonce
            },
            beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.deleting, 'error');
            },
            success: function (response) {
                abptf_spinner_remove(parent);
                abptf_toast_msg(response.data.msg);
                parent.html(response.data.html);
            }
        });
    });
    //==========location configuration=================//
    $(document).on('click', 'div.abptf_admin button.save_location', function (e) {
        e.preventDefault();
        let $this = $(this);
        let body = $('body .abp_post_config');
        let target = (body.find("[name='abptf_post_id']").length > 0) ? body.find('.location_selection') : $('div.abptf_admin .location_list');
        let form_area = $this.closest('.popup_body');
        let formData = abprf_get_form_data(form_area);
        formData.append('action', 'abptf_save_location');
        jQuery.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(form_area);
                abptf_toast_msg(abptf_admin_data.msg.saving);
            },
            success: function (response) {
                abptf_spinner_remove(form_area);
                if (target && target.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html);
                }
                abptf_popup_close('#abptf_global_popup');
                abptf_toast_msg(response.data.msg, 'success');
            }
        });
    });
    $(document).on('click', 'div.abptf_admin button.delete_location', function (e) {
        e.preventDefault();
        let parent = $(this).closest('.location_list');
        let loc_id = parseInt($(this).attr('data-loc_id'));
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_delete_location", "loc_id": loc_id, 'nonce': abptf_admin_data.nonce
            },
            beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.deleting, 'error');
            },
            success: function (response) {
                abptf_spinner_remove(parent);
                abptf_toast_msg(response.data.msg);
                parent.html(response.data.html);
            }
        });
    });
    //==========brand configuration=================//
    $(document).on('click', 'div.abptf_admin button.save_brand', function (e) {
        e.preventDefault();
        let $this = $(this);
        let body = $('body .abp_post_config');
        let form_area = $this.closest('.popup_body');
        let target = (body.find("[name='abptf_post_id']").length > 0) ? body.find('.brand_selection') : $('div.abptf_admin .brand_list');
        let formData = abprf_get_form_data(form_area);
        formData.append('action', 'abptf_save_brand');
        jQuery.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(form_area);
                abptf_toast_msg(abptf_admin_data.msg.saving);
            },
            success: function (response) {
                abptf_spinner_remove(form_area);
                if (target && target.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html);
                }
                abptf_popup_close('#abptf_global_popup');
                abptf_toast_msg(response.data.msg, 'success');
            }
        });
    });
    $(document).on('click', 'div.abptf_admin button.delete_brand', function (e) {
        e.preventDefault();
        let parent = $(this).closest('.brand_list');
        let brand_id = parseInt($(this).attr('data-id'));
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_delete_brand", "brand_id": brand_id, 'nonce': abptf_admin_data.nonce
            },
            beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.deleting, 'error');
            },
            success: function (response) {
                abptf_spinner_remove(parent);
                abptf_toast_msg(response.data.msg);
                parent.html(response.data.html);
            }
        });
    });
    //==========feature configuration=================//
    $(document).on('click', 'div.abptf_admin button.save_feature', function (e) {
        e.preventDefault();
        let $this = $(this);
        let form_area = $this.closest('.popup_body');
        let target = $('div.abptf_admin .feature_list');
        let formData = abprf_get_form_data(form_area);
        formData.append('action', 'abptf_save_feature');
        jQuery.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(form_area);
                abptf_toast_msg(abptf_admin_data.msg.saving);
            },
            success: function (response) {
                abptf_spinner_remove(form_area);
                abptf_popup_close('#abptf_global_popup');
                abptf_toast_msg(response.data.msg, 'success');
                if (target && target.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html);
                } else {
                    if (response.data.hasOwnProperty('feature_js')) {
                        abptf_feature_data = response.data.feature_js;
                        new ABPTF_Multi_Selection('div.abptf_admin .post_feature', abptf_feature_data);
                    }
                }

            }
        });
    });
    $(document).on('click', 'div.abptf_admin button.delete_feature', function (e) {
        e.preventDefault();
        let target = $(this);
        let parent = target.closest('.feature_area');
        let fec_id = $(this).attr('data-fec_id');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_delete_feature", "fec_id": fec_id, 'nonce': abptf_admin_data.nonce
            },
            beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.deleting, 'error');
            },
            success: function (response) {
                abptf_spinner_remove(parent);
                abptf_toast_msg(response.data.msg);
                parent.find('.feature_list').html(response.data.html);
            }
        });
    });
    //==========Faq configuration=================//
    $(document).on('submit', 'div.abptf_admin form.save_faq', function (e) {
        e.preventDefault();
        let target = $(this);
        let formData = new FormData(this);
        formData.append('action', 'abptf_save_faqs');
        formData.append('nonce', abptf_admin_data.nonce);
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg(abptf_admin_data.msg.saving);
            },
            success: function (response) {
                abptf_toast_msg(response.data.msg, 'success');
                window.location.reload();
            }
        });
    });
    $(document).on('click', 'div.abptf_admin button.import_faq', function () {
        let parent = $(this).closest('.faq_configuration');
        let target = parent.find('.faq_content');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_import_faq", 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg(abptf_admin_data.msg.importing);
            }, success: function (response) {
                target.html(response.data.html).promise().done(function () {
                    target.find('.sortable_area').sortable({
                        handle: jQuery(this).find('.sortable_handle')
                    });
                    target.find('.insertable_area .edit_area').each(function () {
                        abptf_wp_editor_init($(this));
                    });
                    abptf_toast_msg(response.data.msg, 'success');
                });
            }
        });
    });
    $(document).on('submit', 'div.abptf_admin form.save_tc', function (e) {
        e.preventDefault();
        let target = $(this);
        let formData = new FormData(this);
        formData.append('action', 'abptf_save_tc');
        formData.append('nonce', abptf_admin_data.nonce);
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg(abptf_admin_data.msg.saving);
            },
            success: function (response) {
                abptf_toast_msg(response.data.msg, 'success');
                window.location.reload();
            }
        });
    });
    $(document).on('click', 'div.abptf_admin button.import_tc', function () {
        let parent = $(this).closest('.tc_configuration');
        let target = parent.find('.tc_content');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_import_tc", 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg(abptf_admin_data.msg.importing);
            }, success: function (response) {
                target.html(response.data.html).promise().done(function () {
                    target.find('.edit_area').each(function () {
                        abptf_wp_editor_init($(this));
                    });
                    abptf_toast_msg(response.data.msg, 'success');
                });
            }
        });
    });
    //==========WooCommerce configuration=================//
    $(document).on('click', 'div.abptf_admin button.install_and_active_wc', function () {
        let parent = $(this).closest('.abptf_status');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_install_and_active_wc", 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.wc_install);
            }, success: function () {
                abptf_toast_msg(abptf_admin_data.msg.wc_installed_success, 'success');
                window.location.reload();
            }
        });
    });
    $(document).on('click', 'div.abptf_admin button.active_wc', function () {
        let parent = $(this).closest('.abptf_status');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_active_wc", 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.wc_installing);
            }, success: function () {
                abptf_toast_msg(abptf_admin_data.msg.wc_installed, 'success');
                window.location.reload();
            }
        });
    });
    //==========page create=================//
    $(document).on('click', 'div.abptf_admin button.create_page', function () {
        let type = $(this).attr('data-page_type');
        let parent = $(this).closest('.abptf_status');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_create_page", 'nonce': abptf_admin_data.nonce, 'type': type
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.create_post_page);
            }, success: function (response) {
                abptf_toast_msg(response.data.msg, response.data.info_type);
                window.location.reload();
            }
        });
    });
    //==========Dummy data configuration=================//
    $(document).on('click', 'div.abptf_admin button.import_dummy', function () {
        let parent = $(this).closest('.abptf_status');
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_import_dummy", 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.importing);
            }, success: function (response) {
                abptf_toast_msg(response.data.msg, 'success');
                window.location.reload();
            }
        });
    });
    //=================================================//
}(jQuery));
//==============Empty title check /image selection/add_new_delete============================//
(function ($) {
    'use strict';
    //========= Empty title check==============//
    $(document).on('click', '#publish, .editor-post-publish-button', function (e) {
        let hasPostIdInput = $('input[name="abptf_post_id"]').length > 0;
        if (hasPostIdInput) {
            let title = $('#title').val() || $('.editor-post-title__input').val();
            if (!title || title.trim().length === 0) {
                alert('Title empty! Please enter a title before updating.');
                e.preventDefault();
                return false;
            }
        }
    });
    //==================image selection========================//
    $(document).on('click', 'div.abptf_admin .add_image', function () {
        let parent = $(this);
        parent.find('.add_image_item').remove();
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            let html = '<div class="add_image_item" data-image-id="' + attachment_id + '"><span class="fas fa-times _circle_icon_xs remove_image"></span>';
            html += '<img class="_img_control" src="' + attachment_url + '" alt="' + attachment_id + '"/>';
            html += '</div>';
            parent.append(html);
            parent.find('input').val(attachment_id);
            parent.find('button').slideUp('fast');
        }
        wp.media.editor.open($(this));
        return false;
    });
    $(document).on('click', 'div.abptf_admin .remove_image', function (e) {
        e.stopPropagation();
        let parent = $(this).closest('.add_image');
        $(this).closest('.add_image_item').remove();
        parent.find('input').val('');
        parent.find('button').slideDown('fast');
    });
    $(document).on('click', 'div.abptf_admin .add_image_multi', function () {
        let parent = $(this).closest('.multiple_image_area');
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            let html = '<div class="multiple_image_item" data-image-id="' + attachment_id + '"><span class="fas fa-times _circle_icon_xs remove_image_multi"></span>';
            html += '<img class="_img_control" src="' + attachment_url + '" alt="' + attachment_id + '"/>';
            html += '</div>';
            parent.find('.multiple_image').append(html);
            let value = parent.find('.multiple_image_ids').val();
            value = value ? value + ',' + attachment_id : attachment_id;
            parent.find('.multiple_image_ids').val(value);
        }
        wp.media.editor.open($(this));
        return false;
    });
    $(document).on('click', 'div.abptf_admin .remove_image_multi', function () {
        let parent = $(this).closest('.multiple_image_area');
        let current_parent = $(this).closest('.multiple_image_item');
        let img_id = current_parent.data('image-id');
        current_parent.remove();
        let all_img_ids = parent.find('.multiple_image_ids').val();
        all_img_ids = all_img_ids.replace(',' + img_id, '')
        all_img_ids = all_img_ids.replace(img_id + ',', '')
        all_img_ids = all_img_ids.replace(img_id, '')
        parent.find('.multiple_image_ids').val(all_img_ids);
    });
    $(document).on('click', 'div.abptf_admin .icon_image_selection_area .icon_delete', function () {
        let parent = $(this).closest('.icon_image_selection_area');
        parent.find('input[type="hidden"]').val('');
        parent.find('[data-add-icon]').removeAttr('class');
        parent.find('.icon_item').slideUp('fast');
        parent.find('.image_icon_select_area').slideDown('fast');
    });
    $(document).on('click', 'div.abptf_admin button.image_select', function () {
        let $this = $(this);
        let parent = $this.closest('.icon_image_selection_area');
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            parent.find('input[type="hidden"]').val(attachment_id);
            parent.find('.icon_item').slideUp('fast');
            parent.find('img').attr('src', attachment_url);
            parent.find('.image_item').slideDown('fast');
            parent.find('.image_icon_select_area').slideUp('fast');
        }
        wp.media.editor.open($this);
        return false;
    });
    $(document).on('click', 'div.abptf_admin .icon_image_selection_area .image_delete', function () {
        let parent = $(this).closest('.icon_image_selection_area');
        parent.find('input[type="hidden"]').val('');
        parent.find('img').attr('src', '');
        parent.find('.image_item').slideUp('fast');
        parent.find('.image_icon_select_area').slideDown('fast');
    });
    //=========add_new_delete ==============//
    $(document).on('click', 'div.abptf_admin .delete_hook', function () {
        if (confirm(abptf_admin_data.msg.confirm_delete + ' \n\n' + abptf_admin_data.msg.confirm_ok + ' \n ' + abptf_admin_data.msg.confirm_cancel)) {
            let deleteArea = $(this).closest('.delete_area');
            let parent = $(this).closest('.configuration_content');
            deleteArea.slideUp(250, function () {
                $(this).remove();
                if (parent.find('.insertable_area .delete_area').length === 0) {
                    parent.find('.hide_on_load').slideUp(250);
                }
            });
            abptf_toast_msg(abptf_admin_data.msg.delete_success);
        }
    });
    $(document).on('click', 'div.abptf_admin .add_new_hook', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        let parent = $(this).closest('.configuration_content');
        let target_element = $(this).next('.abptf_d_none');
        if (target_element.length === 0) {
            target_element = parent.children('.abptf_d_none');
        }
        if (target_element.length === 0) {
            target_element = parent.find('.abptf_d_none').first();
        }
        let item = target_element.find('.hidden_content').html();
        if (!item || item === "undefined" || item === " ") {
            target_element = parent.find('.abptf_d_none').first();
            item = target_element.find('.hidden_content').html();
        }
        if (target_element.attr('data-hidden_id') !== undefined) {
            if (item && item !== "undefined" && item.trim() !== "") {
                let $item = $(item);
                let unique_id = 'abp_' + Date.now();
                $item.find('.hidden_id').val(unique_id);
                $item.find('input, select, textarea').each(function () {
                    let current_name = $(this).attr('name');
                    if (current_name && !$(this).hasClass('hidden_id')) {
                        let new_name = current_name.replace(/\[(.*?)\]/, '[' + unique_id + '][$1]');
                        $(this).attr('name', new_name);
                    }
                });
                item = $('<div>').append($item).html();
            }
        }
        if (typeof abptf_load_sortable_datepicker === 'function') {
            abptf_load_sortable_datepicker(parent, item);
        }
        let insertable_area = parent.children('.insertable_area');
        if (insertable_area.length === 0) {
            insertable_area = parent.find('.insertable_area').first();
        }
        let target = insertable_area.find('.delete_area').last();
        if (target.length && typeof abptf_wp_editor_init === 'function') {
            abptf_wp_editor_init(target);
        }
        target.find('.edit_area').slideDown('fast');
        parent.find('.hide_on_load').slideDown(300);
        $(this).trigger('abp_trigger');
    });
    $(document).on('click', 'div.abptf_admin .edit_hook', function () {
        $(this).closest('.delete_area').toggleClass('active').find('.edit_area').slideToggle('fast');
        //$(this).closest('.delete_area').find('.edit_area').slideToggle('fast');
    });
    $(document).on('keyup change', 'div.abptf_admin [data-pass]', function () {
        let input_value = $(this).val();
        let input_id = $(this).attr('data-pass');
        $(this).closest('.delete_area').find("[data-paste='" + input_id + "']").each(function () {
            $(this).html(input_value);
        });
    });
})(jQuery);
//=================select icon=========================//
(function ($) {
    'use strict';
    let abptf_target_popup = $(document).find('div.abptf_admin .popup_icon');
    let abptf_category_list = abptf_target_popup.find('.dropdown_list');
    let abptf_search_field = abptf_target_popup.find('.abp_dropdown .abp_icon_search');
    let abptf_icon_title = abptf_target_popup.find('.item_icon_title');
    let abptf_icon_area = abptf_target_popup.find('.item_icon_area');
    let abptf_item_loader = abptf_target_popup.find('.item_loader');
    let search_result_icon = [];
    let total_icon = 0;
    let abptf_json_icon = [];
    $.getJSON(abptf_admin_data.icon_url, function (data) {
        abptf_json_icon = data;
        load_icon_category_list();
    }).fail(function () {
        abptf_icon_area.html('Nothing Found !');
    });
    $(document).on('click', 'div.abptf_admin .icon_image_selection_area button.icon_add', function () {
        load_icon_list();
    });
    $(document).on('abp_trigger', 'div.abptf_admin .abp_dropdown .abp_icon_search_hidden', function () {
        let search_value = $(this).val().toLowerCase().trim();
        if (search_value === '' || search_value.length > 2) {
            load_icon_list();
        }
    });
    abptf_search_field.keyup(function () {
        let search_value = $(this).val().toLowerCase().trim();
        if (search_value === '' || search_value.length > 2) {
            load_icon_list();
        }
    });
    abptf_search_field.change(function () {
        let search_value = $(this).val().toLowerCase().trim();
        if (search_value === '' || search_value.length > 2) {
            load_icon_list();
        }
    });
    abptf_target_popup.find('.popup_close').click(function () {
        abptf_search_field.val('').trigger('change');
        abptf_target_popup.find('.icon_item').removeClass('abp_active');
    });
    abptf_target_popup.on('click', '.icon_item', function () {
        let parent = $('[data-active-popup]').closest('.icon_image_selection_area');
        let icon_class = $(this).data('icon-class');
        if (icon_class) {
            parent.find('input[type="hidden"]').val(icon_class);
            parent.find('.image_icon_select_area').slideUp('fast');
            parent.find('.image_item').slideUp('fast');
            parent.find('.icon_item').slideDown('fast');
            if (abptf_emoji_check(icon_class)) {
                parent.find('[data-add-icon]').removeAttr('class').html(icon_class);
            } else {
                parent.find('[data-add-icon]').removeAttr('class').addClass(icon_class).html('');
            }
            abptf_target_popup.find('.icon_item').removeClass('abp_active');
            abptf_target_popup.find('.popup_close').trigger('click');
        }
    });
    // ─── get search icon array / initial array───────────
    function get_icon_array() {
        let pool = [];
        let search_value = abptf_search_field.val().toLowerCase().trim();
        if (search_value) {
            $.each(abptf_json_icon, function (i, group) {
                if (group.category.toLowerCase().includes(search_value)) {
                    $.each(group.icons, function (iconKey, iconLabel) {
                        let match = iconLabel.match(/#(.*?)#/);
                        let finalLabel = match ? match[1] : iconLabel;
                        pool.push({key: iconKey, label: finalLabel});
                    });
                    return pool;
                } else {
                    if (i !== 0) {
                        $.each(group.icons, function (iconKey, iconLabel) {
                            if (iconLabel.toLowerCase().includes(search_value)) {
                                let match = iconLabel.match(/#(.*?)#/);
                                let finalLabel = match ? match[1] : iconLabel;
                                pool.push({key: iconKey, label: finalLabel});
                            }
                        });
                    }
                }
            });
        } else {
            let group = abptf_json_icon[0];
            if (!group) return [];
            $.each(group.icons, function (iconKey, iconLabel) {
                pool.push({key: iconKey, label: iconLabel});
            });
        }
        return pool;
    }
    // ─── load input category ───────────
    function load_icon_category_list() {
        let category_list = $('<ul>').addClass('_abp');
        $.each(abptf_json_icon, function (i, group) {
            let current_count = Object.keys(group.icons).length;
            if (i !== 0) {
                total_icon += current_count;
            }
            let text = group.category;
            let category_li = $('<li>').attr('data-value', text).attr('data-text', text);
            $('<span>').addClass('_mar_r_xxs').text(group.emoji).appendTo(category_li);
            $('<span>').text(text).appendTo(category_li);
            $('<span>').text('( ' + current_count + ' )').appendTo(category_li);
            category_li.appendTo(category_list);
        });
        category_list.appendTo(abptf_category_list);
        abptf_spinner(abptf_item_loader);
    }
    function load_icon_list() {
        abptf_icon_area.empty();
        search_result_icon = get_icon_array();
        if (search_result_icon.length === 0) {
            abptf_icon_area.html('Nothing Found !');
            updateCount();
            return;
        }
        $.each(search_result_icon, function (i, item) {
            let $item = $('<div>').addClass('icon_item').attr('title', item.label).attr('data-icon-class', item.key);
            let $preview;
            if (abptf_emoji_check(item.key)) {
                $preview = $('<span>').text(item.key);
            } else {
                $preview = $('<span>').addClass(item.key);
            }
            $item.append($preview);
            $item.append($('<i>').text(item.label));
            $item.appendTo(abptf_icon_area);
        });
        updateCount();
    }
    function updateCount() {
        let search_value = abptf_search_field.val();
        search_value = search_value ? search_value : 'Selected Icon'
        abptf_icon_title.text(search_value + ' : ' + search_result_icon.length + ' / ' + total_icon + ' icons');
    }
})(jQuery);
//=========== Multi selection start=================//
class ABPTF_Multi_Selection {
    constructor(parentSelector, dataSource) {
        this.parent = document.querySelector(parentSelector);
        if (!this.parent) return;
        this.dataSource = dataSource;
        this.hiddenInput = this.parent.querySelector('input[type="hidden"]');
        this.selectedList = this.parent.querySelector('.selected_list');
        this.init();
    }
    init() {
        this.searchEl = this.parent.querySelector('.item_search');
        this.featureListEl = this.parent.querySelector('.selection_list');
        this.loadPreSelected();
        this.bindEvents();
        this.render();
    }
    bindEvents() {
        if (this.searchEl) {
            ['focusin', 'click'].forEach(eventType => {
                this.searchEl.addEventListener(eventType, (e) => {
                    e.stopPropagation();
                    this.featureListEl?.classList.add('active');
                });
            });
            this.searchEl.addEventListener('input', () => this.render());
        }
        document.addEventListener('click', (e) => {
            if (!e.target.closest(this.parent.className.split(' ').map(c => '.' + c).join(''))) {
                this.featureListEl?.classList.remove('active');
            }
        });
    }
    loadPreSelected() {
        if (!this.hiddenInput || !this.selectedList) return;
        let hiddenVal = this.hiddenInput.value;
        let preIds = hiddenVal ? hiddenVal.split(',').map(s => s.trim()).filter(Boolean) : [];
        if (preIds.length > 0) {
            this.selectedList.innerHTML = '';
            preIds.forEach(id => {
                let f = this.dataSource.find(x => String(x.id) === String(id));
                if (!f) return;
                this.appendSelectedItem(f);
            });
        }
    }
    getSelectedIds() {
        let ids = [];
        this.parent.querySelectorAll('.selected_item').forEach(el => {
            let id = el.getAttribute('data-id');
            if (id) ids.push(id);
        });
        return ids;
    }
    render() {
        if (!this.featureListEl) return;
        let q = this.searchEl ? this.searchEl.value.toLowerCase() : '';
        let selectedIds = this.getSelectedIds();
        let available = this.dataSource.filter(f => {
            return selectedIds.indexOf(String(f.id)) === -1 && f.label.toLowerCase().indexOf(q) !== -1;
        });
        if (available.length === 0) {
            this.featureListEl.innerHTML = `<div class="item_empty">${abptf_admin_data.msg.no_item}</div>`;
            return;
        }
        this.featureListEl.innerHTML = available.map(f => {
            let icon_text = abptf_emoji_check(f.icon) ? `<span class="_mar_r_xxs">${f.icon}</span>` : `<span class="${f.icon} _mar_r_xxs"></span>`;
            let label = f.value ? f.label + '-' + f.value : f.label;
            return `
                <div class="selection_item" data-id="${f.id}">
                    <div>${icon_text}${label}</div>
                    <span class="fa-solid fa-plus fs-add"></span>
                </div>
            `;
        }).join('');
        this.featureListEl.querySelectorAll('.selection_item').forEach(item => {
            item.addEventListener('click', () => {
                this.selectItem(item.getAttribute('data-id'));
            });
        });
    }
    selectItem(id) {
        let f = this.dataSource.find(x => String(x.id) === String(id));
        if (!f) return;
        let placeholder = this.selectedList.querySelector('.item_empty');
        if (placeholder) placeholder.remove();
        this.appendSelectedItem(f);
        this.updateHiddenField();
        this.render();
        setTimeout(() => {
            this.featureListEl?.classList.add('active');
        }, 0);
    }
    appendSelectedItem(f) {
        let div = document.createElement('div');
        div.className = 'selected_item';
        div.setAttribute('data-id', f.id);
        let icon_text = abptf_emoji_check(f.icon) ? `<span class="_mar_r_xxs">${f.icon}</span>` : `<i class="${f.icon} _mar_r_xxs"></i>`;
        let label = f.value ? f.label + '-' + f.value : f.label;
        div.innerHTML = `
            <div class="_fa_center">${icon_text}${label}</div>
            <span class="item_remove">❌</span>
        `;
        div.querySelector('.item_remove').addEventListener('click', (e) => {
            e.stopPropagation();
            this.removeItem(f.id);
        });
        this.selectedList.appendChild(div);
    }
    removeItem(id) {
        let item = this.selectedList.querySelector(`.selected_item[data-id="${id}"]`);
        if (item) item.remove();
        if (!this.selectedList.querySelector('.selected_item')) {
            this.selectedList.innerHTML = `<div class="item_empty">${abptf_admin_data.msg.no_item_selected}</div>`;
        }
        this.updateHiddenField();
        this.render();
    }
    updateHiddenField() {
        if (this.hiddenInput) {
            this.hiddenInput.value = this.getSelectedIds().join(',');
        }
    }
}
//=========== Feature selection end=================//