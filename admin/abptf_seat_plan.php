<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Seat_Plan' ) ) {
		class ABPTF_Seat_Plan {
			public function __construct() {
				add_action( 'abptf_global_seat_plan', array( $this, 'global_seat_plan' ) );
			}

			public function global_seat_plan() {
				if ( ABPTF_Function::on_off( 'sp' ) ) {
					?>
                    <div id="abptf-app">
                        <header class="abptf-topbar" id="abptf-topbar"></header>
                        <div id="view-list" style="display:block">
                            <div class="plans-view">
                                <div class="plans-hdr">
                                    <div class="plans-title">Seat Plans</div>
                                    <button class="btn btn-primary" onclick="abptfNewPlan()">+ New Plan</button>
                                </div>
                                <div id="plans-grid" class="plans-grid"></div>
                            </div>
                        </div>
                        <div id="view-builder" style="display:none"></div>
                    </div>
					<?php
				}
			}
		}
		new ABPTF_Seat_Plan();
	}

	/* ══ 3. STRINGS (fully translatable) ════════════════════ */
	function abptf_sp_strings() {
		return [
			'plans'              => __( 'Seat Plans',                    'abptf' ),
			'builder'            => __( 'Builder',                       'abptf' ),
			'new_plan'           => __( 'New Plan',                      'abptf' ),
			'back'               => __( 'Back to Plans',                 'abptf' ),
			'save'               => __( 'Save Plan',                     'abptf' ),
			'clear'              => __( 'Clear',                         'abptf' ),
			'plan_bg'            => __( 'Plan BG',                       'abptf' ),
			'rm_bg'              => __( 'Remove BG',                     'abptf' ),
			'add_row'            => __( '+ Row',                         'abptf' ),
			'add_col'            => __( '+ Col',                         'abptf' ),
			'rem_row'            => __( '− Row',                         'abptf' ),
			'rem_col'            => __( '− Col',                         'abptf' ),
			'total_seats'        => __( 'Total Seats',                   'abptf' ),
			'status_note'        => __( 'Status → frontend only',        'abptf' ),
			'rows_x_cols'        => __( 'Grid',                          'abptf' ),
			'cell_type'          => __( 'Cell Type',                     'abptf' ),
			'grid_size'          => __( 'Grid Size',                     'abptf' ),
			'rows'               => __( 'Rows',                          'abptf' ),
			'cols'               => __( 'Cols',                          'abptf' ),
			'selected_cell'      => __( 'Selected Cell',                 'abptf' ),
			'click_cell'         => __( 'Click a cell to edit',          'abptf' ),
			'label'              => __( 'Label',                         'abptf' ),
			'custom_text'        => __( 'Custom',                        'abptf' ),
			'width_cells'        => __( 'Width',                         'abptf' ),
			'cells'              => __( 'cells',                         'abptf' ),
			'rotate'             => __( 'Rotate',                        'abptf' ),
			'delete_cell'        => __( 'Delete Cell',                   'abptf' ),
			'active_group'       => __( 'Active Group',                  'abptf' ),
			'group_config'       => __( 'Group Config',                  'abptf' ),
			'group_icon'         => __( 'Group Icon',                    'abptf' ),
			'group_fa'           => __( 'Font Awesome',                  'abptf' ),
			'group_image'        => __( 'Group BG Image',                'abptf' ),
			'group_label_prefix' => __( 'Label Prefix',                  'abptf' ),
			'no_group'           => __( 'No Group',                      'abptf' ),
			'vip'                => __( 'VIP',                           'abptf' ),
			'normal'             => __( 'Normal',                        'abptf' ),
			'special'            => __( 'Special',                       'abptf' ),
			'adult'              => __( 'Adult',                         'abptf' ),
			'female'             => __( 'Female',                        'abptf' ),
			'couple'             => __( 'Couple',                        'abptf' ),
			'business'           => __( 'Business',                      'abptf' ),
			'economy'            => __( 'Economy',                       'abptf' ),
			'cell_icon'          => __( 'Cell Icon',                     'abptf' ),
			'emoji_icon'         => __( 'Emoji',                         'abptf' ),
			'fa_icon'            => __( 'Font Awesome',                  'abptf' ),
			'fa_placeholder'     => __( 'fa-solid fa-star',              'abptf' ),
			'current_icon'       => __( 'Current:',                      'abptf' ),
			'remove_icon'        => __( 'Remove',                        'abptf' ),
			'upload'             => __( 'Upload',                        'abptf' ),
			'remove_bg'          => __( 'Remove BG',                     'abptf' ),
			'auto_number'        => __( 'Auto Number',                   'abptf' ),
			'prefix'             => __( 'Prefix',                        'abptf' ),
			'apply'              => __( 'Apply',                         'abptf' ),
			'apply_to_group'     => __( 'Applies to active group only',  'abptf' ),
			'groups_in_plan'     => __( 'Groups in Plan',                'abptf' ),
			'no_groups'          => __( 'No groups yet',                 'abptf' ),
			'available'          => __( 'Available',                     'abptf' ),
			'blocked'            => __( 'Blocked',                       'abptf' ),
			'sold'               => __( 'Sold',                          'abptf' ),
			'reserved'           => __( 'Reserved',                      'abptf' ),
			'delete_confirm'     => __( 'Delete this plan?',             'abptf' ),
			'clear_confirm'      => __( 'Clear entire grid?',            'abptf' ),
			'name_required'      => __( 'Plan name is required',         'abptf' ),
			'dup_label'          => __( 'Duplicate seat label!',         'abptf' ),
			'saved_ok'           => __( 'Plan saved ✓',                  'abptf' ),
			'saved_local'        => __( 'Saved locally ✓',               'abptf' ),
			'deleted'            => __( 'Plan deleted',                  'abptf' ),
			'numbers_ok'         => __( 'Numbers applied ✓',             'abptf' ),
			'no_plans'           => __( 'No seat plans yet',             'abptf' ),
			'no_plans_sub'       => __( 'Create your first seat plan.',  'abptf' ),
			'create_first'       => __( 'Create Plan',                   'abptf' ),
			'edit'               => __( 'Edit',                          'abptf' ),
			'delete'             => __( 'Delete',                        'abptf' ),
			'seats_label'        => __( 'seats',                         'abptf' ),
			'click_to_restore'   => __( 'Click active tool to restore',  'abptf' ),
			'multisel_drag'      => __( '🖱 Drag → range select',        'abptf' ),
			'multisel_ctrl'      => __( 'Ctrl+Click → toggle',           'abptf' ),
			'multisel_shift'     => __( 'Shift+Click → range from last', 'abptf' ),
			'multisel_apply'     => __( 'Apply to Selection',            'abptf' ),
			'multisel_clear'     => __( 'Clear Selection',               'abptf' ),
			'tool_seat'          => __( 'Seat',                          'abptf' ),
			'tool_driver'        => __( 'Driver',                        'abptf' ),
			'tool_door'          => __( 'Door',                          'abptf' ),
			'tool_toilet'        => __( 'Toilet',                        'abptf' ),
			'tool_window'        => __( 'Window',                        'abptf' ),
			'tool_food'          => __( 'Food',                          'abptf' ),
			'tool_luggage'       => __( 'Luggage',                       'abptf' ),
			'tool_stairs'        => __( 'Stairs',                        'abptf' ),
			'tool_aisle'         => __( 'Aisle',                         'abptf' ),
			'tool_exit'          => __( 'Exit',                          'abptf' ),
			'tool_blank'         => __( 'Blank',                         'abptf' ),
		];
	}

	/* ══ 4. AUTH ═════════════════════════════════════════════ */
	function abptf_sp_auth( $require_manage = true ) {
		if ( ! check_ajax_referer( 'abptf_nonce', 'nonce', false ) )
			wp_send_json_error( [ 'message' => 'Invalid nonce.' ], 403 );
		if ( $require_manage && ! current_user_can( 'manage_options' ) )
			wp_send_json_error( [ 'message' => 'Permission denied.' ], 403 );
	}

	/* ══ 5. SAVE ═════════════════════════════════════════════ */
	add_action( 'wp_ajax_abptf_save_sp',        'abptf_ajax_save_sp' );
	add_action( 'wp_ajax_nopriv_abptf_save_sp', 'abptf_ajax_save_sp' );
	function abptf_ajax_save_sp() {
		abptf_sp_auth();
		global $wpdb;
		$t = $wpdb->prefix . 'abptf_sp';

		$plan_name        = sanitize_text_field( wp_unslash( $_POST['plan_name']          ?? '' ) );
		$rows             = absint( $_POST['rows']       ?? 0 );
		$cols             = absint( $_POST['cols']       ?? 0 );
		$seat_count       = absint( $_POST['seat_count'] ?? 0 );
		$groups           = wp_unslash( $_POST['groups_json']        ?? '[]' );
		$grid_raw         = wp_unslash( $_POST['grid_json']          ?? '[]' );
		$labels           = wp_unslash( $_POST['seat_labels_json']   ?? '[]' );
		$group_cfg        = wp_unslash( $_POST['group_config_json']  ?? '{}' );
		$bg               = wp_unslash( $_POST['plan_bg_image']      ?? '' );
		$plan_db_id       = absint( $_POST['plan_db_id'] ?? 0 );

		if ( empty( $plan_name ) ) wp_send_json_error( [ 'message' => 'Plan name required.' ] );

		$grid = json_decode( $grid_raw, true );
		if ( ! is_array( $grid ) ) wp_send_json_error( [ 'message' => 'Invalid grid_json.' ] );

		// Strip status — NEVER persisted
		foreach ( $grid as &$row ) {
			if ( ! is_array( $row ) ) continue;
			foreach ( $row as &$cell ) {
				if ( is_array( $cell ) ) unset( $cell['status'] );
			}
		}
		unset( $row, $cell );

		$data = [
			'plan_name'         => $plan_name,
			'rows'              => $rows,
			'cols'              => $cols,
			'seat_count'        => $seat_count,
			'groups_json'       => $groups,
			'seat_labels_json'  => $labels,
			'group_config_json' => $group_cfg,
			'plan_bg_image'     => $bg ?: null,
			'grid_json'         => wp_json_encode( $grid ),
			'updated_at'        => current_time( 'mysql' ),
		];
		$fmt = [ '%s','%d','%d','%d','%s','%s','%s','%s','%s','%s' ];

		if ( $plan_db_id > 0 ) {
			$r = $wpdb->update( $t, $data, [ 'id' => $plan_db_id ], $fmt, [ '%d' ] ); // phpcs:ignore
			if ( false === $r ) wp_send_json_error( [ 'message' => $wpdb->last_error ] );
			wp_send_json_success( [ 'plan_db_id' => $plan_db_id, 'action' => 'updated' ] );
		} else {
			$data['created_at'] = current_time( 'mysql' );
			$fmt[] = '%s';
			$r = $wpdb->insert( $t, $data, $fmt ); // phpcs:ignore
			if ( ! $r ) wp_send_json_error( [ 'message' => $wpdb->last_error ] );
			wp_send_json_success( [ 'plan_db_id' => $wpdb->insert_id, 'action' => 'created' ] );
		}
	}

	/* ══ 6. GET ALL ══════════════════════════════════════════ */
	add_action( 'wp_ajax_abptf_get_plans',        'abptf_ajax_get_plans' );
	add_action( 'wp_ajax_nopriv_abptf_get_plans', 'abptf_ajax_get_plans' );
	function abptf_ajax_get_plans() {
		abptf_sp_auth( false );
		global $wpdb;
		$t    = $wpdb->prefix . 'abptf_sp';
		$rows = $wpdb->get_results( "SELECT * FROM {$t} ORDER BY updated_at DESC", ARRAY_A ); // phpcs:ignore
		if ( null === $rows ) wp_send_json_error( [ 'message' => $wpdb->last_error ] );
		foreach ( $rows as &$row ) {
			$row['id']                = (int) $row['id'];
			$row['rows']              = (int) $row['rows'];
			$row['cols']              = (int) $row['cols'];
			$row['seat_count']        = (int) $row['seat_count'];
			$row['groups_json']       = json_decode( $row['groups_json']       ?? '[]', true ) ?: [];
			$row['seat_labels_json']  = json_decode( $row['seat_labels_json']  ?? '[]', true ) ?: [];
			$row['group_config_json'] = json_decode( $row['group_config_json'] ?? '{}', true ) ?: [];
			$row['grid_json']         = json_decode( $row['grid_json'],                 true ) ?: [];
		}
		unset( $row );
		wp_send_json_success( $rows );
	}

	/* ══ 7. GET SINGLE ═══════════════════════════════════════ */
	add_action( 'wp_ajax_abptf_get_sp',        'abptf_ajax_get_sp' );
	add_action( 'wp_ajax_nopriv_abptf_get_sp', 'abptf_ajax_get_sp' );
	function abptf_ajax_get_sp() {
		abptf_sp_auth( false );
		global $wpdb;
		$t  = $wpdb->prefix . 'abptf_sp';
		$id = absint( $_POST['plan_db_id'] ?? 0 );
		if ( ! $id ) wp_send_json_error( [ 'message' => 'plan_db_id required.' ] );
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE id = %d", $id ), ARRAY_A ); // phpcs:ignore
		if ( ! $row ) wp_send_json_error( [ 'message' => 'Not found.' ], 404 );
		$row['id']                = (int) $row['id'];
		$row['rows']              = (int) $row['rows'];
		$row['cols']              = (int) $row['cols'];
		$row['seat_count']        = (int) $row['seat_count'];
		$row['groups_json']       = json_decode( $row['groups_json']       ?? '[]', true ) ?: [];
		$row['seat_labels_json']  = json_decode( $row['seat_labels_json']  ?? '[]', true ) ?: [];
		$row['group_config_json'] = json_decode( $row['group_config_json'] ?? '{}', true ) ?: [];
		$row['grid_json']         = json_decode( $row['grid_json'],                 true ) ?: [];
		wp_send_json_success( $row );
	}

	/* ══ 8. DELETE ═══════════════════════════════════════════ */
	add_action( 'wp_ajax_abptf_delete_sp', 'abptf_ajax_delete_sp' );
	function abptf_ajax_delete_sp() {
		abptf_sp_auth();
		global $wpdb;
		$id = absint( $_POST['plan_db_id'] ?? 0 );
		if ( ! $id ) wp_send_json_error( [ 'message' => 'plan_db_id required.' ] );
		$r = $wpdb->delete( $wpdb->prefix . 'abptf_sp', [ 'id' => $id ], [ '%d' ] ); // phpcs:ignore
		if ( false === $r ) wp_send_json_error( [ 'message' => $wpdb->last_error ] );
		wp_send_json_success( [ 'deleted' => $id ] );
	}

	/* ══ 9. FRONTEND HELPER — merge booking status ══════════ */
	/**
	 * Returns plan data with seat status merged from abptf_bookings table.
	 * Use this in your booking shortcode or block.
	 *
	 * @param int $plan_id  The abptf_sp.id value.
	 * @return array|null   Plan data with grid_json cells having 'status' injected, or null.
	 */
	function abptf_get_plan_with_status( $plan_id ) {
		global $wpdb;
		$sp = $wpdb->prefix . 'abptf_sp';
		$bk = $wpdb->prefix . 'abptf_bookings';

		$plan = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$sp} WHERE id = %d", (int) $plan_id ), ARRAY_A ); // phpcs:ignore
		if ( ! $plan ) return null;

		$grid   = json_decode( $plan['grid_json'], true );
		$booked = [];

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $bk ) ) === $bk ) { // phpcs:ignore
			$rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore
				"SELECT seat_label, status FROM {$bk} WHERE plan_id=%d AND status IN('sold','reserved','blocked')",
				(int) $plan_id
			), ARRAY_A );
			foreach ( $rows as $b ) $booked[ $b['seat_label'] ] = $b['status'];
		}

		foreach ( $grid as &$row ) {
			foreach ( $row as &$cell ) {
				if ( isset( $cell['type'] ) && $cell['type'] === 'seat' ) {
					$cell['status'] = $booked[ $cell['label'] ?? '' ] ?? 'available';
				}
			}
		}
		unset( $row, $cell );

		$plan['grid_json']         = $grid;
		$plan['groups_json']       = json_decode( $plan['groups_json']       ?? '[]', true ) ?: [];
		$plan['seat_labels_json']  = json_decode( $plan['seat_labels_json']  ?? '[]', true ) ?: [];
		$plan['group_config_json'] = json_decode( $plan['group_config_json'] ?? '{}', true ) ?: [];
		$plan['id'] = (int) $plan['id'];
		return $plan;
	}
