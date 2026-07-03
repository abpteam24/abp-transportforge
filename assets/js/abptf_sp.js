/**
 * ABP Seat Plan Builder — JS v4.0
 *
 * Key design:
 *  - GROUPS are fixed: no-group, vip, normal, special, adult, female, couple, business, economy
 *  - Each group has: default icon (emoji), default image (URL/base64), default label prefix
 *  - Seat cells inherit icon + image from their group (set via group config panel)
 *  - Individual seat cells: only label + custom text override
 *  - Non-seat cells: own emoji icon OR font-awesome class + custom text
 *  - Active group selector: ONE place only (sidebar top), applies on click
 *  - Cell type palette uses emoji icons (not tabler icons)
 *  - Multi-select: mouse drag, Ctrl+click, Shift+click
 *  - Blank cells: always recoverable
 *  - Status (available/sold/blocked/reserved): frontend only, never saved to DB
 */
(function () {
    'use strict';
    /* ═══════════════════════════════════════════════════════════
       CONFIG
       ═══════════════════════════════════════════════════════════ */
    const CFG = window.abptf_config || {};
    const AJAX_URL = CFG.ajax_url || window.ajaxurl || '/wp-admin/admin-ajax.php';
    const NONCE = CFG.nonce || '';
    const S = Object.assign({
        plans: 'Seat Plans', builder: 'Builder', new_plan: 'New Plan', back: 'Back to Plans',
        save: 'Save Plan', clear: 'Clear', plan_bg: 'Plan BG', rm_bg: 'Remove BG',
        add_row: '+ Row', add_col: '+ Col', rem_row: '− Row', rem_col: '− Col',
        total_seats: 'Total Seats', status_note: 'Status → frontend only',
        rows_x_cols: 'Grid',
        cell_type: 'Cell Type', grid_size: 'Grid Size', rows: 'Rows', cols: 'Cols',
        selected_cell: 'Selected Cell', click_cell: 'Click a cell to edit',
        label: 'Label', custom_text: 'Custom', width_cells: 'Width', cells: 'cells',
        rotate: 'Rotate', delete_cell: 'Delete Cell',
        active_group: 'Active Group',
        group_config: 'Group Config',
        group_icon: 'Group Icon (emoji)',
        group_fa: 'Group FA Icon',
        group_image: 'Group BG Image',
        no_group: 'No Group', vip: 'VIP', normal: 'Normal', special: 'Special',
        adult: 'Adult', female: 'Female', couple: 'Couple', business: 'Business', economy: 'Economy',
        cell_icon: 'Cell Icon', emoji_icon: 'Emoji', fa_icon: 'Font Awesome',
        fa_placeholder: 'fa-solid fa-star', current_icon: 'Current:', remove_icon: 'Remove',
        upload: 'Upload', remove_bg: 'Remove BG',
        auto_number: 'Auto Number', prefix: 'Prefix', apply: 'Apply',
        apply_to_group: 'Apply numbers to active group only',
        groups_in_plan: 'Groups in Plan', no_groups: 'No groups yet',
        available: 'Available', blocked: 'Blocked', sold: 'Sold', reserved: 'Reserved',
        delete_confirm: 'Delete this plan?', clear_confirm: 'Clear entire grid?',
        name_required: 'Plan name is required', dup_label: 'Duplicate seat label!',
        saved_ok: 'Plan saved ✓', saved_local: 'Saved locally ✓', deleted: 'Plan deleted',
        numbers_ok: 'Numbers applied ✓',
        no_plans: 'No seat plans yet', no_plans_sub: 'Create your first seat plan.',
        create_first: 'Create Plan', edit: 'Edit', delete: 'Delete', seats_label: 'seats',
        click_to_restore: 'Click active tool to restore',
        multisel_drag: '🖱 Drag → range select',
        multisel_ctrl: 'Ctrl+Click → toggle add',
        multisel_shift: 'Shift+Click → range from last',
        multisel_apply: 'Apply to Selection',
        multisel_clear: 'Clear Selection',
        tool_others: 'Others',
    }, CFG.strings || {});
    /* ═══════════════════════════════════════════════════════════
       CONSTANTS
       ═══════════════════════════════════════════════════════════ */
    // Top-level tools: Seat and Others
    const TOOLS = [
        {type: 'seat', emoji: '💺', label: S.tool_seat || 'Seat'},
        {type: 'others', emoji: '◼', label: S.tool_others || 'Others'},
    ];
    const OTHER_TOOLS = [
        {type: 'driver', emoji: '🚗', label: S.tool_driver || 'Driver'},
        {type: 'door', emoji: '🚪', label: S.tool_door || 'Door'},
        {type: 'toilet', emoji: '🚽', label: S.tool_toilet || 'Toilet'},
        {type: 'window', emoji: '🪟', label: S.tool_window || 'Window'},
        {type: 'food', emoji: '🍔', label: S.tool_food || 'Food'},
        {type: 'luggage', emoji: '🧳', label: S.tool_luggage || 'Luggage'},
        {type: 'stairs', emoji: '🪜', label: S.tool_stairs || 'Stairs'},
        {type: 'aisle', emoji: '↔', label: S.tool_aisle || 'Aisle'},
        {type: 'exit', emoji: '🚨', label: S.tool_exit || 'Exit'},
        {type: 'blank', emoji: '◻', label: S.tool_blank || 'Blank'},
    ];
    // Fixed groups are built dynamically from backend seat_type data.
    function buildGroupList() {
        var groups = [
            {key: '', label: S.no_group, color: '#8B949E', defaultIcon: '', defaultPrefix: 'S'}
        ];
        if (window.abptf_config && abptf_config.seat_type) {
            var src = abptf_config.seat_type;
            var parsed = [];
            try {
                parsed = typeof src === 'string' ? JSON.parse(src) : src;
            } catch (e) {
                parsed = [];
            }
            if (Array.isArray(parsed) && parsed.length) {
                parsed.forEach(function (item, idx) {
                    groups.push({
                        key: item.id !== undefined ? String(item.id) : 'grp_' + idx,
                        label: item.label || S.no_group,
                        color: item.color || '#8B949E',
                        defaultIcon: item.icon || '',
                        defaultPrefix: item.prefix || '',
                    });
                });
            }
        }
        return groups;
    }
    const GROUPS = buildGroupList();
    // Emoji options for non-seat cells and group icon picker
    const EMOJI_LIST = [
        '💺', '🪑', '👑', '💎', '⭐', '🌟', '🔥', '❤️', '✈️', '🚀', '🚢', '🎯',
        '🌈', '🦁', '🐬', '🦅', '🌺', '⚡', '🎸', '🎮', '🏆', '🎪', '🎭', '🍀',
        '💑', '👤', '👩', '💼', '🎫', '🔑', '🛡️', '🏅', '💫', '🌙', '☀️', '🎵',
        // non-seat specific
        '🚪', '🚽', '🪟', '🍔', '🧳', '🪜', '🚨', '🚗', '↔', '⚠️', '🔒', '📦',
        '🧯', '♿', '💡', '📷', '🖥️', '🎰', '🛗', '🛞',
    ];
    // Default type emoji for non-seat cells (fallback if no custom icon)
    const TYPE_EMOJI = {
        driver: '🚗', door: '🚪', toilet: '🚽', window: '🪟', food: '🍔',
        luggage: '🧳', stairs: '🪜', aisle: '↔', exit: '🚨', blank: '',
    };
    function getGroupObj(key) {
        return GROUPS.find(function (g) {
            return g.key === (key || '');
        }) || GROUPS[0];
    }
    function getGroupSeatCount(key) {
        var count = 0;
        gridData.forEach(function (row) {
            row.forEach(function (cell) {
                if (cell && cell.type === 'seat' && String(cell.group || '') === String(key || '')) {
                    count++;
                }
            });
        });
        return count;
    }
    /* ═══════════════════════════════════════════════════════════
       STATE
       ═══════════════════════════════════════════════════════════ */
    var plans = [];
    var gridData = [];
    var planBGImg = '';
    var selCell = null;         // [r, c]
    var multiSel = new Set();    // "r-c" keys
    var activeTool = 'seat';
    var activeGroup = '';           // key of active group
    var activeOther = OTHER_TOOLS[0] ? OTHER_TOOLS[0].type : 'blank';
    var dragType = null;
    var editingPlanId = null;
    var isDragSel = false;
    var dragSelStart = null;
    var dragSelEnd = null;
    // configMode: true when user has explicitly picked a tool/group to apply
    // false = "inspect mode" — clicking cell only shows its config, doesn't change it
    var configMode = false;
    // Per-group config: icon, faIcon, bgImage (label prefix removed — Auto Number prefix is the single source)
    // Initialised from GROUPS defaults, user can override per plan
    var groupConfig = {};
    function initGroupConfig() {
        groupConfig = {};
        GROUPS.forEach(function (g) {
            groupConfig[g.key] = {
                icon: g.defaultIcon,
                faIcon: '',
                bgImage: '',
            };
        });
    }
    initGroupConfig();
    /* ═══════════════════════════════════════════════════════════
        PLAN LIST
        ═══════════════════════════════════════════════════════════ */
    function renderPlansList() {
        var grid = document.getElementById('plans-grid');
        if (!grid) return;
        if (!plans.length) {
            grid.innerHTML = '<div class="empty-state"><div class="empty-icon">💺</div>'
                + '<div class="empty-title">' + esc(S.no_plans) + '</div>'
                + '<div class="empty-sub">' + esc(S.no_plans_sub) + '</div>'
                + '<button class="btn btn-primary" onclick="abptf_sp_create()">+ ' + esc(S.create_first) + '</button></div>';
            return;
        }
        grid.innerHTML = plans.map(function (p) {
            var groups = Array.isArray(p.groups_json) ? p.groups_json : [];
            var gcfg = p.group_config_json || {};
            var groupBadges = groups.map(function (g) {
                var gObj = getGroupObj(g);
                var cfg2 = gcfg[g] || {};
                var icon = cfg2.icon || gObj.defaultIcon || '●';
                return '<span class="badge" style="background:' + gObj.color + '18;color:' + gObj.color + '">' + icon + ' ' + esc(gObj.label) + '</span>';
            }).join('');
            return '<div class="plan-card" onclick="abptfOpenEdit(' + p.id + ')">'
                + '<div class="plan-card-hdr"><div>'
                + '<div class="plan-card-name">' + esc(p.plan_name) + '</div>'
                + '<div class="plan-card-id">ID: ' + p.id + '</div></div>'
                + '<div class="plan-card-acts" onclick="event.stopPropagation()">'
                + '<button class="btn btn-xs" onclick="abptfOpenEdit(' + p.id + ')">✏️</button>'
                + '<button class="btn btn-xs btn-danger" onclick="abptfDeletePlan(' + p.id + ')">🗑</button>'
                + '</div></div>'
                + '<div class="plan-card-meta">'
                + '<span class="badge badge-green">💺 ' + p.seat_count + ' ' + esc(S.seats_label) + '</span>'
                + '<span class="badge badge-muted">' + p.rows + '×' + p.cols + '</span>'
                + (p.plan_bg_image ? '<span class="badge badge-amber">🖼 BG</span>' : '')
                + groupBadges + '</div>'
                + '<div class="plan-mini">' + buildMini(p.grid_json, gcfg) + '</div>'
                + '</div>';
        }).join('');
    }
    function buildMini(gj, gcfg) {
        var grid = typeof gj === 'string' ? tryParse(gj) : gj;
        if (!grid || !grid.length) return '<span style="color:var(--text3);font-size:10px">—</span>';
        var cm = {driver: '#5F5E5A', door: '#185FA5', toilet: '#534AB7', window: '#3B6D11', food: '#854F0B', luggage: '#712B13', stairs: '#993C1D', aisle: '#30363D', exit: '#A32D2D', blank: 'transparent'};
        var gc = {'': '#1D9E75', vip: '#A78BFA', normal: '#1D9E75', special: '#6366F1', adult: '#EC4899', female: '#F472B6', couple: '#C026D3', business: '#0EA5E9', economy: '#84CC16'};
        return grid.slice(0, 6).map(function (row) {
            return '<div class="mini-row">' + (row || []).slice(0, 20).map(function (cell) {
                if (!cell) return '<div class="mini-cell" style="background:transparent"></div>';
                var bg = cell.type === 'seat' ? (gc[cell.group || ''] || '#1D9E75') : (cm[cell.type] || '#30363D');
                return '<div class="mini-cell" style="background:' + bg + '"></div>';
            }).join('') + '</div>';
        }).join('');
    }
    window.abptf_sp_create = function () {
        editingPlanId = null;
        planBGImg = '';
        selCell = null;
        activeGroup = '';
        multiSel.clear();
        isDragSel = false;
        dragSelStart = null;
        dragSelEnd = null;
        initGroupConfig();
        configMode = false;
        setVal('abp_sp_name', '');
        ['btn-rm-bg', 'props-panel'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
        var emp = document.getElementById('props-empty');
        if (emp) emp.style.display = '';
        refreshSidebarCounters();
        refreshGroupConfigPanel();
        updateConfigModeIndicator();
        initGrid(5, 5);
        window.abptfShowView('builder');
    };
    window.abptfOpenEdit = function (id) {
        var p = null;
        plans.forEach(function (pl) {
            if (pl.id === id) p = pl;
        });
        if (!p) return;
        editingPlanId = id;
        planBGImg = p.plan_bg_image || '';
        selCell = null;
        activeGroup = '';
        multiSel.clear();
        isDragSel = false;
        dragSelStart = null;
        dragSelEnd = null;
        initGroupConfig();
        configMode = false;
        // Restore saved group config if present
        if (p.group_config_json && typeof p.group_config_json === 'object') {
            Object.keys(p.group_config_json).forEach(function (k) {
                groupConfig[k] = p.group_config_json[k];
            });
        }
        setVal('abp_sp_name', p.plan_name);
        var rmBg = document.getElementById('btn-rm-bg');
        if (rmBg) rmBg.style.display = planBGImg ? '' : 'none';
        var emp = document.getElementById('props-empty');
        if (emp) emp.style.display = '';
        var pan = document.getElementById('props-panel');
        if (pan) pan.style.display = 'none';
        refreshSidebarCounters();
        gridData = typeof p.grid_json === 'string' ? tryParse(p.grid_json) : JSON.parse(JSON.stringify(p.grid_json));
        gridData.forEach(function (row) {
            row && row.forEach(function (cell) {
                if (!cell) return;
                if (!('faIcon' in cell)) cell.faIcon = '';
                if (!('bgImage' in cell)) cell.bgImage = '';
                if (!('cellW' in cell)) cell.cellW = 0;
                if (!('cellH' in cell)) cell.cellH = 0;
            });
        });
        buildActiveGroupBtns();
        refreshGroupConfigPanel();
        updateRCCount();
        renderGrid();
        window.abptfShowView('builder');
    };
    window.abptfDeletePlan = function (id) {
        if (!confirm(S.delete_confirm)) return;
        fetch(AJAX_URL, {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: new URLSearchParams({action: 'abptf_delete_sp', nonce: NONCE, plan_db_id: id})})
            .then(function (r) {
                return r.json();
            }).finally(function () {
            plans = plans.filter(function (p) {
                return p.id !== id;
            });
            localStorage.setItem('abptf_plans_v4', JSON.stringify(plans));
            renderPlansList();
            showToast(S.deleted);
        });
    };
    /* ═══════════════════════════════════════════════════════════
       INIT
       ═══════════════════════════════════════════════════════════ */
    function initApp() {
        buildBuilderView();
        buildToast();
        initOutsideClick();
        initActionDelegation();
        loadPlans();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
    } else {
        initApp();
    }
    /* ── DELEGATED ACTION HANDLER ──────────────────────────────
       Single document-level listener for all data-action buttons.
       This guarantees tool/group selection always fires exactly once,
       with no inline-onclick quote-escaping or bubbling races. ── */
    function initActionDelegation() {
        document.addEventListener('click', function (e) {
            var el = e.target.closest('[data-action]');
            if (!el) return;
            e.stopPropagation();
            var action = el.dataset.action;
            var value = el.dataset.actionValue;
            if (action === 'set-tool') {
                window.abptfSetTool(value);
            } else if (action === 'set-active-group') {
                window.abptfSetActiveGroup(value);
            }
        }, true); // capture phase: runs BEFORE the outside-click listener, guaranteed
    }
    /* ═══════════════════════════════════════════════════════════
       BUILDER VIEW — HTML skeleton
       ═══════════════════════════════════════════════════════════ */
    function buildBuilderView() {
        var v = document.getElementById('abptf_sp_builder');
        if (!v) return;
        buildPalette();
        buildActiveGroupBtns();
        buildOtherTypeBtns();
        buildNonSeatIconPicker();
        buildLegend();
        updateSidebarForTool();
        attachBuilderListeners();
    }
    function attachBuilderListeners() {
        var saveBtn = document.getElementById('btn-save-plan');
        if (!saveBtn) return;
        saveBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (typeof window.abptfSavePlan === 'function') {
                window.abptfSavePlan();
            }
        });
    }
    function buildToast() {
        var t = document.createElement('div');
        t.id = 'abptf-toast';
        t.className = 'abptf-toast';
        document.body.appendChild(t);
    }
    /* ── SIDEBAR visibility based on active tool ─────────────── */
    function updateSidebarForTool() {
        var isSeat = activeTool === 'seat';
        var isOther = !isSeat;
        // Active Group section
        var grpSec = document.getElementById('sb-group-section');
        if (grpSec) grpSec.style.display = isSeat ? '' : 'none';
        // Auto Number section
        var numPanel = document.getElementById('sb-autonumber-section');
        if (numPanel) numPanel.style.display = isSeat ? '' : 'none';
        // Other type section
        var otherSec = document.getElementById('sb-other-section');
        if (otherSec) otherSec.style.display = isOther ? '' : 'none';
    }
    /* ── CONFIG MODE indicator ─────────────────────────────────── */
    // Shows a visual badge on the toolbar when config-apply mode is active
    function updateConfigModeIndicator() {
        var ind = document.getElementById('config-mode-ind');
        if (!ind) return;
        if (configMode) {
            var toolLabel = getToolLabel(activeTool) || activeTool;
            var grpObj = getGroupObj(activeGroup);
            if (activeTool === 'blank') {
                ind.style.display = '';
                ind.textContent = '◻ Blank mode — click any cell to make it blank';
            } else {
                ind.style.display = '';
                ind.textContent = '⚡ Apply mode: ' + toolLabel
                    + (activeTool === 'seat' && activeGroup ? ' + ' + grpObj.label : '')
                    + ' — click cells to apply';
            }
        } else {
            ind.style.display = 'none';
        }
    }
    /* ── OUTSIDE CLICK → reset configMode, deselect cell ──────── */
    function initOutsideClick() {
        // Canvas outer: click on the padding area (not on a cell) resets
        document.addEventListener('click', function (e) {
            var builder = document.getElementById('abptf_sp_builder');
            if (!builder || builder.style.display === 'none') return;
            var canvasOuter = document.getElementById('canvas-wrap');
            // Click is inside a cell → handled by cell click, skip
            if (e.target.closest && e.target.closest('.cell')) return;
            // Click is inside top panel → skip (user editing props)
            var topPanel = document.querySelector('.abptf-top-panel');
            if (topPanel && topPanel.contains(e.target)) return;
            // Click is inside toolbar → skip
            if (e.target.closest && e.target.closest('.sp_toolbar')) return;
            // Click is inside canvas area but NOT on a cell → deselect
            if (canvasOuter && canvasOuter.contains(e.target)) {
                clearSelection();
                return;
            }
            // Click completely outside canvas area → full reset
            var canvasArea = document.querySelector('.canvas-outer');
            if (canvasArea && !canvasArea.contains(e.target)) {
                configMode = false;
                clearSelection();
                updateConfigModeIndicator();
            }
        });
    }
    function clearSelection() {
        selCell = null;
        multiSel.clear();
        isDragSel = false;
        dragSelStart = null;
        dragSelEnd = null;
        var bar = document.getElementById('multi-sel-bar');
        var emp = document.getElementById('props-empty');
        var pan = document.getElementById('props-panel');
        if (bar) bar.innerHTML = '';
        if (emp) emp.style.display = '';
        if (pan) pan.style.display = 'none';
        renderGrid();
    }
    /* ═══════════════════════════════════════════════════════════
       PALETTE  (emoji icons)
       ═══════════════════════════════════════════════════════════ */
    function buildPalette() {
        let el = document.getElementById('tool_palette');
        if (!el) return;
        let isOtherMode = activeTool === 'others' || OTHER_TOOLS.some(function (t) {
            return t.type === activeTool;
        });
        el.innerHTML = TOOLS.map(function (t) {
            let active = t.type === activeTool || (t.type === 'others' && isOtherMode);
            let count = 0;
            if (t.type === 'seat') {
                count = getTypeCount('seat');
            } else if (t.type === 'others') {
                count = gridData.flat().filter(function (c) {
                    return c && c.type !== 'seat' && c.type !== 'blank';
                }).length;
            }
            return '<div class="_btn_light_navy_blue_xxs' + (active ? ' abp_active' : '') + '"'
                + ' id="tool-' + t.type + '" draggable="true" data-type="' + t.type + '"'
                + ' data-action="set-tool" data-action-value="' + t.type + '"'
                + ' ondragstart="abptfToolDragStart(event,\'' + t.type + '\')"'
                + ' ondragend="abptfToolDragEnd(event)">'
                + '<span class="tool-emoji">' + t.emoji + '</span>'
                + '<span>' + esc(t.label) + '</span>'
                + '<span class="grp-cnt">' + count + '</span>'
                + '</div>';
        }).join('');
    }
    /* ═══════════════════════════════════════════════════════════
       ACTIVE GROUP BUTTONS  (sidebar — only one instance)
       ═══════════════════════════════════════════════════════════ */
    function buildActiveGroupBtns() {
        var el = document.getElementById('active-group-btns');
        if (!el) return;
        el.innerHTML = GROUPS.map(function (g) {
            var isActive = g.key === activeGroup;
            var showIcon = g.defaultIcon;
            var count = getGroupSeatCount(g.key);
            return '<button class="grp-type-btn' + (isActive ? ' active' : '') + '"'
                + ' data-gkey="' + g.key + '"'
                + ' data-action="set-active-group" data-action-value="' + g.key + '"'
                + (isActive ? ' style="border-color:' + g.color + ';background:' + g.color + '18"' : '') + '>'
                + '<span class="grp-dot" style="background:' + g.color + '"></span>'
                + (showIcon ? '<span>' + showIcon + '</span>' : '')
                + '<span>' + esc(g.label) + '</span>'
                + '<span class="grp-cnt">' + count + '</span>'
                + '</button>';
        }).join('');
    }
    function getTypeCount(type) {
        var count = 0;
        gridData.forEach(function (row) {
            row.forEach(function (cell) {
                if (cell && cell.type === type) count++;
            });
        });
        return count;
    }
    function buildOtherTypeBtns() {
        var el = document.getElementById('other-type-btns');
        if (!el) return;
        if (activeTool === 'others') {
            activeTool = OTHER_TOOLS[0] ? OTHER_TOOLS[0].type : 'blank';
        }
        el.innerHTML = OTHER_TOOLS.map(function (t) {
            var isActive = t.type === activeTool;
            var count = getTypeCount(t.type);
            return '<button class="grp-type-btn' + (isActive ? ' active' : '') + '"'
                + ' data-action="set-tool" data-action-value="' + t.type + '"'
                + (isActive ? ' style="border-color:#666;background:var(--bg2)"' : '') + '>'
                + '<span>' + t.emoji + '</span>'
                + '<span>' + esc(t.label) + '</span>'
                + '<span class="grp-cnt">' + count + '</span>'
                + '</button>';
        }).join('');
    }
    function refreshSidebarCounters() {
        buildPalette();
        buildActiveGroupBtns();
        buildOtherTypeBtns();
    }
    function getToolLabel(type) {
        var tool = TOOLS.concat(OTHER_TOOLS).find(function (t) {
            return t.type === type;
        });
        return tool ? tool.label : type;
    }
    /* ═══════════════════════════════════════════════════════════
       GROUP CONFIG PANEL  (icon / image / prefix for active group)
       ═══════════════════════════════════════════════════════════ */
    function refreshGroupConfigPanel() {
        var g = getGroupObj(activeGroup);
        var cfg = groupConfig[activeGroup] || {};
        var titleEl = document.getElementById('group-config-title');
        if (titleEl) titleEl.textContent = (S.group_config || 'Group Config') + ': ' + g.label;
        // icon
        var curEl = document.getElementById('grp-cur-icon');
        if (curEl) {
            if (cfg.faIcon) curEl.innerHTML = '<i class="' + esc(cfg.faIcon) + '" style="font-size:16px"></i>';
            else if (cfg.icon) curEl.textContent = cfg.icon;
            else curEl.textContent = '—';
        }
        updateFAPreview('grp-fa-prev', cfg.faIcon || '');
        var faInput = document.getElementById('grp-fa-input');
        if (faInput) faInput.value = cfg.faIcon || '';
        document.querySelectorAll('#grp-icon-picker .icon-opt').forEach(function (el) {
            el.classList.toggle('active', el.dataset.icon === (cfg.icon || ''));
        });
        // bg image
        var hasImg = !!cfg.bgImage;
        var prevEl = document.getElementById('grp-img-preview');
        var thumbEl = document.getElementById('grp-img-thumb');
        if (prevEl) prevEl.style.display = hasImg ? '' : 'none';
        if (hasImg && thumbEl) thumbEl.style.backgroundImage = 'url(' + cfg.bgImage + ')';
    }
    window.abptfGrpPickIcon = function (icon) {
        var cfg = groupConfig[activeGroup] || {};
        cfg.icon = icon;
        cfg.faIcon = '';
        groupConfig[activeGroup] = cfg;
        refreshGroupConfigPanel();
        buildActiveGroupBtns();
        renderGrid();
    };
    window.abptfGrpFAInput = function (val) {
        var cfg = groupConfig[activeGroup] || {};
        cfg.faIcon = val.trim();
        cfg.icon = '';
        groupConfig[activeGroup] = cfg;
        updateFAPreview('grp-fa-prev', val.trim());
        var curEl = document.getElementById('grp-cur-icon');
        if (curEl) curEl.innerHTML = val.trim() ? '<i class="' + esc(val.trim()) + '" style="font-size:16px"></i>' : '—';
        document.querySelectorAll('#grp-icon-picker .icon-opt').forEach(function (el) {
            el.classList.remove('active');
        });
        buildActiveGroupBtns();
        renderGrid();
    };
    window.abptfGrpClearIcon = function () {
        var cfg = groupConfig[activeGroup] || {};
        cfg.icon = '';
        cfg.faIcon = '';
        groupConfig[activeGroup] = cfg;
        refreshGroupConfigPanel();
        buildActiveGroupBtns();
        renderGrid();
    };
    window.abptfGrpSetImage = function (ev) {
        var f = ev.target.files[0];
        if (!f) return;
        readFile(f, function (url) {
            var cfg = groupConfig[activeGroup] || {};
            cfg.bgImage = url;
            groupConfig[activeGroup] = cfg;
            refreshGroupConfigPanel();
            renderGrid();
        });
        ev.target.value = '';
    };
    window.abptfGrpRemoveImage = function () {
        var cfg = groupConfig[activeGroup] || {};
        cfg.bgImage = '';
        groupConfig[activeGroup] = cfg;
        refreshGroupConfigPanel();
        renderGrid();
    };
    /* ═══════════════════════════════════════════════════════════
       NON-SEAT ICON PICKER
       ═══════════════════════════════════════════════════════════ */
    function buildNonSeatIconPicker() {
        var el = document.getElementById('ns-icon-picker');
        if (!el) return;
        el.innerHTML = EMOJI_LIST.map(function (ic) {
            return '<div class="icon-opt" data-icon="' + ic + '" onclick="abptfNSPickIcon(\'' + ic + '\')">' + ic + '</div>';
        }).join('');
    }
    window.abptfNSPickIcon = function (icon) {
        if (!selCell) return;
        var r = selCell[0], c = selCell[1];
        var cell = gridData[r][c];
        if (!cell || cell.type === 'seat' || cell.type === 'blank') return;
        cell.icon = icon;
        cell.faIcon = '';
        setVal('ns-fa-input', '');
        updateFAPreview('ns-fa-prev', '');
        document.querySelectorAll('#ns-icon-picker .icon-opt').forEach(function (el) {
            el.classList.toggle('active', el.dataset.icon === icon);
        });
        updateNSCurIcon(cell);
        renderGrid();
    };
    window.abptfNSFAInput = function (val) {
        if (!selCell) return;
        var r = selCell[0], c = selCell[1];
        var cell = gridData[r][c];
        if (!cell || cell.type === 'seat' || cell.type === 'blank') return;
        cell.faIcon = val.trim();
        cell.icon = '';
        updateFAPreview('ns-fa-prev', val.trim());
        document.querySelectorAll('#ns-icon-picker .icon-opt').forEach(function (el) {
            el.classList.remove('active');
        });
        updateNSCurIcon(cell);
        renderGrid();
    };
    window.abptfNSClearIcon = function () {
        if (!selCell) return;
        var r = selCell[0], c = selCell[1];
        var cell = gridData[r][c];
        if (!cell) return;
        cell.icon = '';
        cell.faIcon = '';
        setVal('ns-fa-input', '');
        updateFAPreview('ns-fa-prev', '');
        document.querySelectorAll('#ns-icon-picker .icon-opt').forEach(function (el) {
            el.classList.remove('active');
        });
        updateNSCurIcon(cell);
        renderGrid();
    };
    window.abptfNSSetBG = function (ev) {
        var f = ev.target.files[0];
        if (!f) return;
        readFile(f, function (url) {
            if (!selCell) return;
            var r = selCell[0], c = selCell[1];
            var cell = gridData[r][c];
            if (!cell || cell.type === 'seat' || cell.type === 'blank') return;
            cell.bgImage = url;
            var prev = document.getElementById('ns-bg-preview');
            var thumb = document.getElementById('ns-bg-thumb');
            if (prev) prev.style.display = '';
            if (thumb) thumb.style.backgroundImage = 'url(' + url + ')';
            renderGrid();
        });
        ev.target.value = '';
    };
    window.abptfNSRemoveBG = function () {
        if (!selCell) return;
        var r = selCell[0], c = selCell[1];
        var cell = gridData[r][c];
        if (!cell) return;
        cell.bgImage = '';
        var prev = document.getElementById('ns-bg-preview');
        if (prev) prev.style.display = 'none';
        renderGrid();
    };
    function updateNSCurIcon(cell) {
        var el = document.getElementById('ns-cur-icon');
        if (!el) return;
        if (cell.faIcon) el.innerHTML = '<i class="' + esc(cell.faIcon) + '" style="font-size:16px"></i>';
        else if (cell.icon) el.textContent = cell.icon;
        else el.textContent = '—';
    }
    /* ═══════════════════════════════════════════════════════════
       LEGEND
       ═══════════════════════════════════════════════════════════ */
    function buildLegend() {
        var el = document.getElementById('abptf-legend');
        if (!el) return;
        var items = [
            {color: '#1D9E75', label: S.available},
            {color: '#D85A30', label: S.blocked},
            {color: '#D4537E', label: S.sold},
            {color: '#E3B341', label: S.reserved},
        ].concat(GROUPS.filter(function (g) {
            return g.key;
        }).map(function (g) {
            return {color: g.color, label: g.label};
        }));
        el.innerHTML = items.map(function (i) {
            return '<div class="leg-item"><div class="leg-dot" style="background:' + i.color + '"></div>' + esc(i.label) + '</div>';
        }).join('');
    }
    /* ═══════════════════════════════════════════════════════════
       VIEW SWITCH
       ═══════════════════════════════════════════════════════════ */
    window.abptfShowView = function (v) {
        var lEl = document.getElementById('view-list');
        var bEl = document.getElementById('abptf_sp_builder');
        var bb = document.getElementById('btn-back');
        var ctx = document.getElementById('topbar-ctx');
        if (lEl) lEl.style.display = v === 'list' ? 'block' : 'none';
        if (bEl) bEl.style.display = v === 'builder' ? 'block' : 'none';
        if (bb) bb.style.display = v === 'builder' ? '' : 'none';
        if (ctx) ctx.textContent = v === 'list' ? S.plans : S.builder;
        if (v === 'list') renderPlansList();
    };
    /* ═══════════════════════════════════════════════════════════
       TOOL
       ═══════════════════════════════════════════════════════════ */
    window.abptfSetTool = function (type) {
        if (type === 'others') {
            activeTool = activeOther || (OTHER_TOOLS[0] ? OTHER_TOOLS[0].type : 'blank');
        } else if (OTHER_TOOLS.some(function (t) {
            return t.type === type;
        })) {
            activeTool = type;
            activeOther = type;
        } else {
            activeTool = type;
        }
        configMode = true;   // blank also needs ON to apply to cells
        updateSidebarForTool();
        refreshSidebarCounters();
        updateConfigModeIndicator();
    };
    window.abptfToolDragStart = function (e, type) {
        dragType = type;
        e.dataTransfer.effectAllowed = 'copy';
        e.dataTransfer.setData('text/plain', type);
        e.currentTarget.style.opacity = '.35';
    };
    window.abptfToolDragEnd = function (e) {
        e.currentTarget.style.opacity = '';
        dragType = null;
    };
    /* ═══════════════════════════════════════════════════════════
       ACTIVE GROUP
       ═══════════════════════════════════════════════════════════ */
    window.abptfSetActiveGroup = function (key) {
        activeGroup = key;
        configMode = true;
        refreshSidebarCounters();
        refreshGroupConfigPanel();
        // auto-fill auto-number prefix with this group's default
        var g = getGroupObj(key);
        setVal('auto-prefix', g.defaultPrefix || '');
        updateConfigModeIndicator();
    };
    /* ═══════════════════════════════════════════════════════════
       GRID
       ═══════════════════════════════════════════════════════════ */
    function mkCell(type) {
        return {type: type || 'seat', label: '', size: 1, rotate: 0, group: '', custom: '', icon: '', faIcon: '', bgImage: '', cellW: 0, cellH: 0};
    }
    function initGrid(rows, cols) {
        gridData = [];
        for (var r = 0; r < rows; r++) {
            gridData[r] = [];
            for (var c = 0; c < cols; c++) gridData[r][c] = mkCell();
        }
        updateRCCount();
        renderGrid();
    }
    window.abptfAddRow = function () {
        var cols = (gridData[0] || []).length || 5;
        var row = [];
        for (var c = 0; c < cols; c++) row.push(mkCell());
        gridData.push(row);
        updateRCCount();
        renderGrid();
    };
    window.abptfRemoveRow = function () {
        if (gridData.length > 1) {
            gridData.pop();
            updateRCCount();
            renderGrid();
        }
    };
    window.abptfAddCol = function () {
        gridData.forEach(function (r) {
            r.push(mkCell());
        });
        updateRCCount();
        renderGrid();
    };
    window.abptfRemoveCol = function () {
        if ((gridData[0] || []).length > 1) {
            gridData.forEach(function (r) {
                r.pop();
            });
            updateRCCount();
            renderGrid();
        }
    };
    function updateRCCount() {
        var r = document.getElementById('abptf_sp_rows');
        var c = document.getElementById('rc-input-cols');
        if (r) r.value = gridData.length;
        if (c) c.value = (gridData[0] || []).length;
    }
    window.abptfSetRows = function (val) {
        var rows = parseInt(val, 10);
        if (!rows || rows < 1) rows = 1;
        var cols = (gridData[0] || []).length || 1;
        if (rows === gridData.length) {
            updateRCCount();
            return;
        }
        if (rows > gridData.length) {
            for (var r = gridData.length; r < rows; r++) {
                var row = [];
                for (var c = 0; c < cols; c++) row.push(mkCell());
                gridData.push(row);
            }
        } else {
            gridData.splice(rows);
            if (!gridData.length) {
                gridData = [Array.from({length: cols}, function () {
                    return mkCell();
                })];
            }
        }
        updateRCCount();
        renderGrid();
    };
    window.abptfSetCols = function (val) {
        var cols = parseInt(val, 10);
        if (!cols || cols < 1) cols = 1;
        var rows = gridData.length || 1;
        if (cols === (gridData[0] || []).length) {
            updateRCCount();
            return;
        }
        gridData.forEach(function (row) {
            if (!row) return;
            if (cols > row.length) {
                for (var c = row.length; c < cols; c++) row.push(mkCell());
            } else {
                row.splice(cols);
            }
        });
        updateRCCount();
        renderGrid();
    };
    /* ═══════════════════════════════════════════════════════════
       MULTI-SELECT
       ═══════════════════════════════════════════════════════════ */
    function inDragRange(r, c) {
        if (!dragSelStart || !dragSelEnd) return false;
        var rMin = Math.min(dragSelStart.r, dragSelEnd.r), rMax = Math.max(dragSelStart.r, dragSelEnd.r);
        var cMin = Math.min(dragSelStart.c, dragSelEnd.c), cMax = Math.max(dragSelStart.c, dragSelEnd.c);
        return r >= rMin && r <= rMax && c >= cMin && c <= cMax;
    }
    function applyToMultiSel() {
        if (!multiSel.size) return;
        var count = multiSel.size;
        multiSel.forEach(function (key) {
            var p = key.split('-');
            var r = parseInt(p[0]), c = parseInt(p[1]);
            if (gridData[r] && gridData[r][c] !== undefined) applyTool(activeTool, r, c);
        });
        multiSel.clear();
        var bar = document.getElementById('multi-sel-bar');
        var emp = document.getElementById('props-empty');
        var pan = document.getElementById('props-panel');
        if (bar) bar.innerHTML = '';
        if (emp) emp.style.display = '';
        if (pan) pan.style.display = 'none';
        renderGrid();
        showToast('Applied to ' + count + ' cells ✓', 'success');
    }
    window.applyToMultiSel = applyToMultiSel;
    window.abptfApplyWHToMultiSel = function () {
        if (!multiSel.size) return;
        var w = parseInt(getVal('ms-cellW')) || 0;
        var h = parseInt(getVal('ms-cellH')) || 0;
        if (!w && !h) {
            showToast('Set a W or H value first', 'error');
            return;
        }
        var count = 0;
        multiSel.forEach(function (key) {
            var p = key.split('-');
            var r = parseInt(p[0]), c = parseInt(p[1]);
            if (gridData[r] && gridData[r][c]) {
                gridData[r][c].cellW = w;
                gridData[r][c].cellH = h;
                count++;
            }
        });
        renderGrid();
        showToast('Applied ' + w + '×' + (h || 44) + 'px to ' + count + ' selected cells ✓', 'success');
    };
    function showMultiSelBar() {
        var bar = document.getElementById('multi-sel-bar');
        if (!bar) return;
        var pan = document.getElementById('props-panel');
        var emp = document.getElementById('props-empty');
        var sp = document.getElementById('seat-props');
        var nsp = document.getElementById('nonseat-props');
        if (pan) pan.style.display = '';
        if (emp) emp.style.display = 'none';
        if (sp) sp.style.display = 'none';
        if (nsp) nsp.style.display = 'none';
        bar.innerHTML =
            '<div class="multisel-bar">'
            + '<div class="multisel-count">' + multiSel.size + ' cells selected</div>'
            + '<div class="multisel-desc">Set Cell Type + Group → apply to all.</div>'
            + '<button class="btn btn-sm btn-primary btn-full" onclick="applyToMultiSel()" style="margin-bottom:8px">✓ ' + esc(S.multisel_apply) + '</button>'
            + '<div style="border-top:1px solid var(--border);padding-top:8px;margin-bottom:8px">'
            + '<div style="font-size:10px;color:var(--text2);margin-bottom:5px">Custom size for selected:</div>'
            + '<div style="display:flex;align-items:center;gap:4px;margin-bottom:5px">'
            + '<span style="font-size:10px;color:var(--text3)">W</span>'
            + '<input class="prop-input" id="ms-cellW" type="number" value="0" min="0" max="400" style="width:50px;flex:none" title="0=auto">'
            + '<span style="font-size:10px;color:var(--text3);margin-left:4px">H</span>'
            + '<input class="prop-input" id="ms-cellH" type="number" value="0" min="0" max="400" style="width:50px;flex:none" title="0=auto">'
            + '</div>'
            + '<button class="btn btn-xs btn-primary btn-full" onclick="abptfApplyWHToMultiSel()">⤢ Apply Size to Selection</button>'
            + '</div>'
            + '<button class="btn btn-xs btn-ghost btn-full" onclick="abptfClearMultiSel()">✕ ' + esc(S.multisel_clear) + '</button>'
            + '</div>';
    }
    window.abptfClearMultiSel = function () {
        multiSel.clear();
        isDragSel = false;
        dragSelStart = null;
        dragSelEnd = null;
        var bar = document.getElementById('multi-sel-bar');
        var emp = document.getElementById('props-empty');
        var pan = document.getElementById('props-panel');
        if (bar) bar.innerHTML = '';
        if (emp) emp.style.display = '';
        if (pan) pan.style.display = 'none';
        renderGrid();
    };
    function initDragSelect() {
        var inner = document.getElementById('grid-inner');
        if (!inner || inner._dragBound) return;
        inner._dragBound = true;
        inner.addEventListener('mousedown', function (e) {
            if (e.button !== 0 || dragType) return;
            var cell = e.target.closest('.cell');
            if (!cell) return;
            var r = parseInt(cell.dataset.r), c = parseInt(cell.dataset.c);
            if (isNaN(r)) return;
            isDragSel = true;
            dragSelStart = {r: r, c: c};
            dragSelEnd = {r: r, c: c};
        });
        inner.addEventListener('mousemove', function (e) {
            if (!isDragSel) return;
            var cell = e.target.closest('.cell');
            if (!cell) return;
            var r = parseInt(cell.dataset.r), c = parseInt(cell.dataset.c);
            if (isNaN(r)) return;
            if (dragSelEnd && dragSelEnd.r === r && dragSelEnd.c === c) return;
            dragSelEnd = {r: r, c: c};
            renderGrid();
        });
        if (!window._abptfMouseUpBound) {
            window._abptfMouseUpBound = true;
            window.addEventListener('mouseup', function () {
                if (!isDragSel) return;
                isDragSel = false;
                if (!dragSelStart || !dragSelEnd) return;
                var rMin = Math.min(dragSelStart.r, dragSelEnd.r), rMax = Math.max(dragSelStart.r, dragSelEnd.r);
                var cMin = Math.min(dragSelStart.c, dragSelEnd.c), cMax = Math.max(dragSelStart.c, dragSelEnd.c);
                if (rMin === rMax && cMin === cMax) {
                    dragSelStart = null;
                    dragSelEnd = null;
                    return;
                }
                for (var ri = rMin; ri <= rMax; ri++)
                    for (var ci = cMin; ci <= cMax; ci++) multiSel.add(ri + '-' + ci);
                dragSelStart = null;
                dragSelEnd = null;
                showMultiSelBar();
                renderGrid();
            });
        }
    }
    /* ═══════════════════════════════════════════════════════════
       RENDER GRID
       ═══════════════════════════════════════════════════════════ */
    function renderGrid() {
        var inner = document.getElementById('grid-inner');
        var wrap = document.getElementById('canvas-wrap');
        var ovl = document.getElementById('canvas-bg-overlay');
        if (!inner) return;
        if (planBGImg) {
            wrap.style.backgroundImage = 'url(' + planBGImg + ')';
            if (ovl) ovl.style.display = '';
        } else {
            wrap.style.backgroundImage = '';
            if (ovl) ovl.style.display = 'none';
        }
        var html = '';
        gridData.forEach(function (row, r) {
            html += '<div class="seat-row"><span class="row-num">' + (r + 1) + '</span>';
            row.forEach(function (cell, c) {
                if (!cell) return;
                var isSeat = cell.type === 'seat';
                var isBlank = cell.type === 'blank';
                var key = r + '-' + c;
                var isSel = multiSel.has(key) || (selCell && selCell[0] === r && selCell[1] === c);
                var isDR = isDragSel && inDragRange(r, c);
                var defaultW = (44 * (cell.size || 1)) + (5 * ((cell.size || 1) - 1));
                var w = (cell.cellW && cell.cellW > 0) ? cell.cellW : defaultW;
                var h = (cell.cellH && cell.cellH > 0) ? cell.cellH : 44;
                var hStyle = h !== 44 ? 'height:' + h + 'px;' : '';
                var rot = cell.rotate ? 'transform:rotate(' + cell.rotate + 'deg)' : '';
                var grp = getGroupObj(cell.group);
                var cfg = groupConfig[cell.group] || {};
                var grpCls = isSeat && cell.group ? ' grp-' + cell.group : '';
                var selCls = isSel ? ' selected' : (isDR ? ' drag-range' : '');
                // Matching-type highlight: when active tool is seat + a group is chosen,
                // every seat already belonging to that group gets a soft highlight ring
                // (separate from multiSel — purely informational, click still works normally)
                var isMatching = false;
                if (configMode && cell.type === activeTool) {
                    if (activeTool === 'seat') {
                        isMatching = (activeGroup === '' ? false : cell.group === activeGroup);
                    } else {
                        isMatching = true; // non-seat: matching just means same type
                    }
                }
                var matchCls = (isMatching && !isSel) ? ' type-match' : '';
                var cellContent = '';
                if (isBlank) {
                    cellContent = '<div class="cell-inner blank-inner"><span style="font-size:11px;opacity:.3">+</span></div>';
                } else if (isSeat) {
                    var iconHtml = grp.defaultIcon ? '<span class="cell-icon">' + grp.defaultIcon + '</span>' : '';
                    cellContent = '<div class="cell-inner">'
                        + iconHtml
                        + (cell.label ? '<span class="cell-lbl">' + esc(cell.label) + '</span>' : '')
                        + (cell.custom ? '<span class="cell-custom">' + esc(cell.custom) + '</span>' : '')
                        + '</div>';
                } else {
                    // Non-seat: own icon + optional bgImage
                    var nsIcon = '';
                    if (cell.faIcon) nsIcon = '<i class="cell-fa ' + esc(cell.faIcon) + '"></i>';
                    else if (cell.icon) nsIcon = '<span class="cell-icon">' + cell.icon + '</span>';
                    else nsIcon = '<span class="cell-icon">' + (TYPE_EMOJI[cell.type] || '') + '</span>';
                    var nsBgStyle = cell.bgImage
                        ? 'background-image:url(' + cell.bgImage + ');background-size:cover;background-position:center;'
                        : '';
                    cellContent = (cell.bgImage ? '<div class="cell-bg-overlay"></div>' : '')
                        + '<div class="cell-inner">'
                        + nsIcon
                        + (cell.custom ? '<span class="cell-custom">' + esc(cell.custom) + '</span>' : '')
                        + '</div>';
                    if (cell.bgImage) rot += ';' + nsBgStyle;
                }
                html += '<div class="cell cell-' + cell.type + grpCls + selCls + matchCls + '"'
                    + ' style="width:' + w + 'px;' + hStyle + rot + '"'
                    + ' data-r="' + r + '" data-c="' + c + '"'
                    + ' onclick="abptfCellClick(event,' + r + ',' + c + ')"'
                    + ' ondragover="abptfCellDragOver(event,' + r + ',' + c + ')"'
                    + ' ondragleave="abptfCellDragLeave(event)"'
                    + ' ondrop="abptfCellDrop(event,' + r + ',' + c + ')"'
                    + '>' + cellContent + '</div>';
            });
            html += '</div>';
        });
        inner.innerHTML = html;
        updateStats();
        refreshSidebarCounters();
        initDragSelect();
    }
    /* ═══════════════════════════════════════════════════════════
       CELL CLICK
       ═══════════════════════════════════════════════════════════ */
    window.abptfCellClick = function (e, r, c) {
        e.stopPropagation();
        var key = r + '-' + c;
        // Ctrl/Cmd+Click → toggle this cell into/out of the multi-selection
        if (e.ctrlKey || e.metaKey) {
            if (multiSel.has(key)) multiSel.delete(key); else multiSel.add(key);
            selCell = [r, c];
            showMultiSelBar();
            renderGrid();
            return;
        }
        // Shift+Click → extend selection as a rectangle from the last selected cell
        if (e.shiftKey && selCell) {
            var r0 = selCell[0], c0 = selCell[1];
            var rMin = Math.min(r0, r), rMax = Math.max(r0, r);
            var cMin = Math.min(c0, c), cMax = Math.max(c0, c);
            for (var ri = rMin; ri <= rMax; ri++)
                for (var ci = cMin; ci <= cMax; ci++) multiSel.add(ri + '-' + ci);
            showMultiSelBar();
            renderGrid();
            return;
        }
        // Plain click (no modifier):
        //  - If this cell is ALREADY part of the current multi-selection,
        //    treat it as "commit": apply active tool+group to the whole selection.
        //  - Otherwise (clicking a cell OUTSIDE the current selection),
        //    drop the old selection entirely and start fresh on just this cell.
        if (multiSel.size > 0 && multiSel.has(key)) {
            applyToMultiSel();
            return;
        }
        multiSel.clear();
        var cellIsBlank = gridData[r] && gridData[r][c] && gridData[r][c].type === 'blank';
        if (configMode || cellIsBlank) {
            // Apply active tool + group to just this single cell
            applyTool(activeTool, r, c);
        }
        // Always show the cell's config in sidebar
        selectCell(r, c);
    };
    /* ═══════════════════════════════════════════════════════════
       APPLY TOOL
       ═══════════════════════════════════════════════════════════ */
    function applyTool(type, r, c) {
        if (!type || type === 'others') return;
        var old = gridData[r][c] || {};
        var isSeatType = type === 'seat';
        var isBlankType = type === 'blank';
        var sameType = (old.type === type);
        if (isBlankType) {
            // Blank: wipe the cell cleanly, keep only size
            gridData[r][c] = {type: 'blank', label: '', size: old.size || 1, rotate: 0, group: '', custom: '', icon: '', faIcon: '', bgImage: '', cellW: old.cellW || 0, cellH: old.cellH || 0};
            return;
        }
        gridData[r][c] = {
            type: type,
            label: isSeatType ? (old.label || '') : '',  // non-seat: no label
            custom: old.custom || '',
            size: old.size || 1,
            rotate: old.rotate || 0,
            group: isSeatType ? activeGroup : '',          // non-seat: no group
            icon: isSeatType ? '' : (sameType ? (old.icon || '') : ''),
            faIcon: isSeatType ? '' : (sameType ? (old.faIcon || '') : ''),
            bgImage: isSeatType ? '' : (sameType ? (old.bgImage || '') : ''),
            cellW: old.cellW || 0,
            cellH: old.cellH || 0,
        };
    }
    /* ═══════════════════════════════════════════════════════════
       SELECT CELL / PROPS PANEL
       ═══════════════════════════════════════════════════════════ */
    function selectCell(r, c) {
        selCell = [r, c];
        var cell = gridData[r][c];
        var isSeat = cell && cell.type === 'seat';
        var isBlank = cell && cell.type === 'blank';
        var isNS = cell && !isSeat && !isBlank;
        var emp = document.getElementById('props-empty');
        var pan = document.getElementById('props-panel');
        var sEl = document.getElementById('seat-props');
        var nEl = document.getElementById('nonseat-props');
        var bar = document.getElementById('multi-sel-bar');
        if (bar) bar.innerHTML = '';
        if (isBlank) {
            // Blank: show a restore hint instead of empty
            if (emp) {
                emp.style.display = '';
                emp.innerHTML = '<div style="text-align:center;padding:8px 0">'
                    + '<span style="font-size:18px">◻</span>'
                    + '<div style="font-size:10px;color:var(--text3);margin-top:4px">' + (S.click_to_restore || 'Select a tool then click to restore') + '</div>'
                    + '</div>';
            }
            if (pan) pan.style.display = 'none';
        } else {
            if (emp) emp.style.display = 'none';
            if (pan) pan.style.display = '';
            if (sEl) sEl.style.display = isSeat ? '' : 'none';
            if (nEl) nEl.style.display = isNS ? '' : 'none';
        }
        if (cell && isSeat) {
            var curLblEl = document.getElementById('seat-cur-label');
            if (curLblEl) {
                curLblEl.innerHTML = cell.label
                    ? 'Current label: <strong style="color:var(--text)">' + esc(cell.label) + '</strong>'
                    : '<span style="color:var(--text3)">No label yet</span>';
            }
        }
        if (cell && isNS) {
            setVal('ns-fa-input', cell.faIcon || '');
            updateFAPreview('ns-fa-prev', cell.faIcon || '');
            document.querySelectorAll('#ns-icon-picker .icon-opt').forEach(function (el) {
                el.classList.toggle('active', el.dataset.icon === cell.icon);
            });
            updateNSCurIcon(cell);
            // BG image preview
            var nsBgPrev = document.getElementById('ns-bg-preview');
            var nsBgThumb = document.getElementById('ns-bg-thumb');
            if (nsBgPrev) nsBgPrev.style.display = cell.bgImage ? '' : 'none';
            if (cell.bgImage && nsBgThumb) nsBgThumb.style.backgroundImage = 'url(' + cell.bgImage + ')';
        }
        if (cell && !isBlank) {
            setVal('p-custom', cell.custom || '');
            setVal('p-size', cell.size || 1);
            setVal('p-cellW', cell.cellW || 0);
            setVal('p-cellH', cell.cellH || 0);
            document.querySelectorAll('#rot-btns .rot-btn').forEach(function (b, i) {
                b.classList.toggle('active', [0, 90, 180, 270][i] === (cell.rotate || 0));
            });
        }
        renderGrid();
    }
    window.abptfUpdateProp = function (key, val) {
        if (!selCell) return;
        var r = selCell[0], c = selCell[1];
        var cell = gridData[r][c];
        if (!cell) return;
        if (key === 'label' && cell.type === 'seat') {
            var trimmed = String(val || '').trim();
            // Empty label is always allowed
            if (trimmed) {
                var dup = false;
                gridData.forEach(function (row, ri) {
                    row.forEach(function (cl, ci) {
                        if (cl && cl.label === trimmed && cl.type === 'seat' && !(ri === r && ci === c)) dup = true;
                    });
                });
                if (dup) {
                    showToast(S.dup_label, 'error');
                    return;
                }
                val = trimmed;
            } else {
                val = '';
            }
        }
        cell[key] = val;
        if (key === 'rotate') {
            document.querySelectorAll('#rot-btns .rot-btn').forEach(function (b, i) {
                b.classList.toggle('active', [0, 90, 180, 270][i] === val);
            });
        }
        renderGrid();
    };
    window.abptfApplyWHToAll = function () {
        if (!selCell) return;
        var srcCell = gridData[selCell[0]][selCell[1]];
        if (!srcCell) return;
        var w = srcCell.cellW || 0;
        var h = srcCell.cellH || 0;
        if (!w && !h) {
            showToast('Set a W or H value first', 'error');
            return;
        }
        var count = 0;
        gridData.forEach(function (row) {
            row.forEach(function (cell) {
                if (!cell) return;
                cell.cellW = w;
                cell.cellH = h;
                count++;
            });
        });
        renderGrid();
        showToast('Applied ' + w + '×' + (h || 44) + 'px to ' + count + ' cells ✓', 'success');
    };
    window.abptfDeleteCell = function () {
        if (!selCell) return;
        gridData[selCell[0]][selCell[1]] = mkCell('blank');
        selCell = null;
        var emp = document.getElementById('props-empty');
        var pan = document.getElementById('props-panel');
        if (emp) emp.style.display = '';
        if (pan) pan.style.display = 'none';
        renderGrid();
    };
    /* ═══════════════════════════════════════════════════════════
       DRAG & DROP
       ═══════════════════════════════════════════════════════════ */
    window.abptfCellDragOver = function (e, r, c) {
        if (!dragType) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        var el = document.querySelector('[data-r="' + r + '"][data-c="' + c + '"]');
        if (el) el.classList.add('drag-over');
    };
    window.abptfCellDragLeave = function (e) {
        e.currentTarget.classList.remove('drag-over');
    };
    window.abptfCellDrop = function (e, r, c) {
        e.preventDefault();
        e.currentTarget.classList.remove('drag-over');
        var type = dragType || e.dataTransfer.getData('text/plain');
        if (!type) return;
        applyTool(type, r, c);
        window.abptfSetTool(type);
        selectCell(r, c);
        renderGrid();
    };
    /* ═══════════════════════════════════════════════════════════
       AUTO NUMBER
       ═══════════════════════════════════════════════════════════ */
    window.abptfAutoNumber = function () {
        var prefix = getVal('auto-prefix') || '';
        var n = parseInt(getVal('auto-start')) || 1;
        // Determine target seats:
        //  - If multiSel has cells → apply ONLY to selected seats (ignore activeGroup filter)
        //  - Else if a single cell is selected and it's a seat → apply to that one seat
        //  - Else → apply to all seats matching activeGroup (or all seats if no group active)
        var targets = []; // [{r,c,cell}]
        if (multiSel.size > 0) {
            multiSel.forEach(function (key) {
                var p = key.split('-');
                var r = parseInt(p[0]), c = parseInt(p[1]);
                var cell = gridData[r] && gridData[r][c];
                if (cell && cell.type === 'seat') targets.push({r: r, c: c, cell: cell});
            });
        } else if (selCell) {
            var sc = gridData[selCell[0]] && gridData[selCell[0]][selCell[1]];
            if (sc && sc.type === 'seat') targets.push({r: selCell[0], c: selCell[1], cell: sc});
        }
        if (!targets.length) {
            // Fallback: no selection → apply to all seats in active group scope
            gridData.forEach(function (row, r) {
                row.forEach(function (cell, c) {
                    if (!cell || cell.type !== 'seat') return;
                    if (activeGroup === '' || cell.group === activeGroup) targets.push({r: r, c: c, cell: cell});
                });
            });
        }
        if (!targets.length) {
            showToast('No seats to number — select a seat or group first', 'error');
            return;
        }
        // Collect labels of seats NOT being renumbered (preserve uniqueness against them)
        var targetKeys = {};
        targets.forEach(function (t) {
            targetKeys[t.r + '-' + t.c] = true;
        });
        var outOfScopeLabels = {};
        gridData.forEach(function (row, r) {
            row.forEach(function (cell, c) {
                if (!cell || cell.type !== 'seat') return;
                if (!targetKeys[r + '-' + c] && cell.label) outOfScopeLabels[cell.label] = true;
            });
        });
        var assigned = {};
        function safeNextLabel() {
            var label, safety = 0;
            do {
                label = prefix + n++;
                safety++;
                if (safety > 10000) break;
            } while (outOfScopeLabels[label] || assigned[label]);
            assigned[label] = true;
            return label;
        }
        targets.forEach(function (t) {
            t.cell.label = safeNextLabel();
        });
        renderGrid();
        showToast(S.numbers_ok + ' (' + targets.length + ' seats)', 'success');
        // Refresh current-label display if a single seat was being inspected
        if (selCell) {
            var curLblEl = document.getElementById('seat-cur-label');
            var sc2 = gridData[selCell[0]] && gridData[selCell[0]][selCell[1]];
            if (curLblEl && sc2 && sc2.type === 'seat') {
                curLblEl.innerHTML = sc2.label
                    ? 'Current label: <strong style="color:var(--text)">' + esc(sc2.label) + '</strong>'
                    : '<span style="color:var(--text3)">No label yet</span>';
            }
        }
    };
    window.abptfClearGrid = function () {
        if (!confirm(S.clear_confirm)) return;
        multiSel.clear();
        isDragSel = false;
        dragSelStart = null;
        dragSelEnd = null;
        initGrid(gridData.length || 5, (gridData[0] || []).length || 5);
        selCell = null;
        var emp = document.getElementById('props-empty');
        var pan = document.getElementById('props-panel');
        if (emp) emp.style.display = '';
        if (pan) pan.style.display = 'none';
    };
    /* ═══════════════════════════════════════════════════════════
       PLAN BG
       ═══════════════════════════════════════════════════════════ */
    window.abptfSetPlanBG = function (ev) {
        var f = ev.target.files[0];
        if (!f) return;
        readFile(f, function (url) {
            planBGImg = url;
            var btn = document.getElementById('btn-rm-bg');
            if (btn) btn.style.display = '';
            renderGrid();
        });
        ev.target.value = '';
    };
    window.abptfRemovePlanBG = function () {
        planBGImg = '';
        var btn = document.getElementById('btn-rm-bg');
        if (btn) btn.style.display = 'none';
        renderGrid();
    };
    /* ═══════════════════════════════════════════════════════════
       STATS
       ═══════════════════════════════════════════════════════════ */
    function updateStats() {
        var el = document.getElementById('sp_starts-content');
        if (!el) return;
        var seats = gridData.flat().filter(function (c) {
            return c && c.type === 'seat';
        });
        el.innerHTML =
            '<div class="stat-item"><div class="stat-dot" style="background:var(--green)"></div>'
            + '<span class="stat-label">' + esc(S.total_seats) + '</span>'
            + '<span class="stat-val" style="color:var(--green)">' + seats.length + '</span></div>'
            + (multiSel.size ? '<div class="stat-item"><span class="stat-label" style="color:var(--accent2)">' + multiSel.size + ' selected</span></div>' : '');
    }
    /* ═══════════════════════════════════════════════════════════
       SAVE
       ═══════════════════════════════════════════════════════════ */
    window.abptfSavePlan = function () {
        var name = getVal('abp_sp_name').trim();
        if (!name) {
            showToast(S.name_required, 'error');
            return;
        }
        var seats = gridData.flat().filter(function (c) {
            return c && c.type === 'seat';
        });
        var groups = [], labels = [];
        seats.forEach(function (c) {
            if (c.group && groups.indexOf(c.group) < 0) groups.push(c.group);
            if (c.label) labels.push(c.label);
        });
        // Clean grid — no status ever
        var cleanGrid = gridData.map(function (row) {
            return row.map(function (cell) {
                if (!cell) return null;
                if (cell.type === 'seat')
                    return {type: cell.type, label: cell.label, size: cell.size, rotate: cell.rotate, group: cell.group, custom: cell.custom, cellW: cell.cellW || 0, cellH: cell.cellH || 0};
                if (cell.type === 'blank')
                    return {type: 'blank', size: cell.size || 1, cellW: cell.cellW || 0, cellH: cell.cellH || 0};
                return {type: cell.type, custom: cell.custom, size: cell.size, rotate: cell.rotate, icon: cell.icon || '', faIcon: cell.faIcon || '', bgImage: cell.bgImage || '', cellW: cell.cellW || 0, cellH: cell.cellH || 0};
            });
        });
        var payload = {
            action: 'abptf_save_sp', nonce: NONCE,
            plan_name: name, plan_bg_image: planBGImg,
            rows: gridData.length, cols: (gridData[0] || []).length,
            plan_rows: gridData.length, plan_cols: (gridData[0] || []).length,
            seat_count: seats.length,
            groups_json: JSON.stringify(groups),
            grid_json: JSON.stringify(cleanGrid),
            seat_labels_json: JSON.stringify(labels),
            group_config_json: JSON.stringify(groupConfig),
        };
        if (editingPlanId !== null) payload.plan_db_id = editingPlanId;
        fetch(AJAX_URL, {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: new URLSearchParams(payload)})
            .then(function (r) {
                return r.text().then(function (text) {
                    try {
                        return JSON.parse(text);
                    } catch (err) {
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(function (res) {
                if (res.success) {
                    editingPlanId = res.data.plan_db_id || editingPlanId;
                    showToast(S.saved_ok, 'success');
                    loadPlans();
                } else {
                    var msg = res.data && res.data.message ? res.data.message : (res.message || 'Unknown save error');
                    showToast('Save failed: ' + msg, 'error');
                    fallbackSave(payload, cleanGrid, groups, labels, seats.length);
                }
            })
            .catch(function (err) {
                showToast('Save request failed: ' + (err && err.message ? err.message : 'network error'), 'error');
                fallbackSave(payload, cleanGrid, groups, labels, seats.length);
            });
    };
    function fallbackSave(payload, cleanGrid, groups, labels, seatCount) {
        var idx = -1;
        plans.forEach(function (p, i) {
            if (p.plan_name === payload.plan_name || (editingPlanId !== null && p.id === editingPlanId)) idx = i;
        });
        var entry = {
            id: idx >= 0 ? plans[idx].id : Date.now(),
            plan_name: payload.plan_name, plan_bg_image: planBGImg,
            rows: +payload.rows, cols: +payload.cols, seat_count: seatCount,
            groups_json: groups, grid_json: cleanGrid,
            seat_labels_json: labels, group_config_json: groupConfig,
            created_at: idx >= 0 ? plans[idx].created_at : new Date().toISOString(),
            updated_at: new Date().toISOString(),
        };
        if (idx >= 0) plans[idx] = entry; else plans.push(entry);
        localStorage.setItem('abptf_plans_v4', JSON.stringify(plans));
        editingPlanId = entry.id;
        showToast(S.saved_local, 'success');
        renderPlansList();
    }
    /* ═══════════════════════════════════════════════════════════
       LOAD
       ═══════════════════════════════════════════════════════════ */
    function loadPlans() {
        fetch(AJAX_URL, {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: new URLSearchParams({action: 'abptf_get_plans', nonce: NONCE})})
            .then(function (r) {
                return r.json();
            })
            .then(function (res) {
                if (res.success && Array.isArray(res.data)) {
                    plans = res.data;
                    renderPlansList();
                } else loadFallback();
            })
            .catch(loadFallback);
    }
    function loadFallback() {
        try {
            plans = JSON.parse(localStorage.getItem('abptf_plans_v4') || '[]');
        } catch (e) {
            plans = [];
        }
        if (!plans.length) {
            plans = createDummyBusPlan();
            saveDummyBusPlanToDb(plans[0]).then(function () {
                localStorage.setItem('abptf_plans_v4', JSON.stringify(plans));
                renderPlansList();
            }).catch(function () {
                localStorage.setItem('abptf_plans_v4', JSON.stringify(plans));
                renderPlansList();
            });
            return;
        }
        renderPlansList();
    }
    function createDummyBusPlan() {
        var rows = 8;
        var cols = 5;
        var labels = [];
        var grid = [];
        var seatLetters = ['A', 'B', 'C', 'D'];
        for (var r = 0; r < rows; r++) {
            var row = [];
            var seatIndex = 0;
            for (var c = 0; c < cols; c++) {
                if (c === 2) {
                    row.push({type: 'blank', size: 1, rotate: 0, group: '', custom: '', icon: '', faIcon: '', bgImage: '', cellW: 0, cellH: 0});
                } else {
                    var label = (r + 1) + seatLetters[seatIndex++];
                    labels.push(label);
                    row.push({
                        type: 'seat', label: label, size: 1, rotate: 0,
                        group: '', custom: '', icon: '', faIcon: '', bgImage: '',
                        cellW: 0, cellH: 0,
                    });
                }
            }
            grid.push(row);
        }
        return [{
            id: Date.now(),
            plan_name: 'Dummy Bus Seat Plan',
            plan_bg_image: '',
            rows: rows,
            cols: cols,
            seat_count: labels.length,
            groups_json: [''],
            grid_json: grid,
            seat_labels_json: labels,
            group_config_json: {},
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
        }];
    }
    function saveDummyBusPlanToDb(plan) {
        var payload = {
            action: 'abptf_save_sp',
            nonce: NONCE,
            plan_name: plan.plan_name,
            rows: plan.rows,
            cols: plan.cols,
            plan_rows: plan.rows,
            plan_cols: plan.cols,
            seat_count: plan.seat_count,
            groups_json: JSON.stringify(plan.groups_json),
            grid_json: JSON.stringify(plan.grid_json),
            seat_labels_json: JSON.stringify(plan.seat_labels_json),
            group_config_json: JSON.stringify(plan.group_config_json),
            plan_bg_image: plan.plan_bg_image || '',
            cell_width: 44,
            cell_height: 44,
        };
        return fetch(AJAX_URL, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(payload),
        })
            .then(function (r) {
                return r.json();
            })
            .then(function (res) {
                if (res.success) {
                    plan.id = res.data.plan_db_id || plan.id;
                    return plan;
                }
                throw new Error(res.data && res.data.message ? res.data.message : 'Failed to save dummy plan');
            });
    }
    /* ═══════════════════════════════════════════════════════════
       UTILS
       ═══════════════════════════════════════════════════════════ */
    window.abptf_sp_get_val = function (id) {
        let el = document.getElementById(id);
        return el ? el.value : '';
    };
    function updateFAPreview(elId, val) {
        var el = document.getElementById(elId);
        if (!el) return;
        el.className = val ? val : '';
        el.textContent = '';
    }
    function esc(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function getVal(id) {
        var el = document.getElementById(id);
        return el ? el.value : '';
    }
    function setVal(id, v) {
        var el = document.getElementById(id);
        if (el) el.value = v;
    }
    function readFile(f, cb) {
        var r = new FileReader();
        r.onload = function (e) {
            cb(e.target.result);
        };
        r.readAsDataURL(f);
    }
    function tryParse(s) {
        try {
            return JSON.parse(s);
        } catch (e) {
            return [];
        }
    }
    function showToast(msg, type) {
        var t = document.getElementById('abptf-toast');
        if (!t) return;
        t.textContent = msg;
        t.className = 'abptf-toast ' + (type || '');
        t.classList.add('show');
        clearTimeout(t._timer);
        t._timer = setTimeout(function () {
            t.classList.remove('show');
        }, 2500);
    }
})();