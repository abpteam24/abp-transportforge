<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Seat_Plan' ) ) {
		class ABPTF_Seat_Plan {
			public function __construct() {
				add_action( 'abptf_global_seat_plan', array( $this, 'global_seat_plan' ) );
				add_action( 'wp_ajax_abptf_add_ticket_type', array( $this, 'add_ticket_type' ) );
				add_action( 'wp_ajax_abptf_save_ticket_type', array( $this, 'save_ticket_type' ) );
				add_action( 'wp_ajax_abptf_delete_ticket_type', array( $this, 'delete_ticket_type' ) );
			}

			public function global_seat_plan(): void {
				if ( ABPTF_Function::on_off( 'ticket_type' ) ) {
					?>
                    <div class="ticket_configuration setting_item">
                        <div class="_fj_between">
                            <h5 class="_abp"><?php esc_html_e( 'Ticket Type List', 'abp-transportforge' ); ?></h5>
							<?php ABPTF_Layout::button_popup_global( 'ticket', __( 'Add New Ticket Type', 'abp-transportforge' ) ); ?>
                        </div>
                        <div class="_divider_xxs"></div>
                        <div class="ticket_list _group_list">
							<?php $this->ticket_list(); ?>
                        </div>
                    </div>
					<?php
				}
				if ( ABPTF_Function::on_off( 'sp' ) ) {
					?>
                    <div class="seat_plan_configuration setting_item">
                        <div class="_fj_between">
                            <h5 class="_abp">💺 <?php esc_html_e( 'Seat Plan', 'abp-transportforge' ); ?></h5>
                            <button class="_btn_active_xxs" onclick="abptf_sp_create()">
								<?php esc_html_e( 'Add New Seat Plan', 'abp-transportforge' ); ?>
                            </button>
                        </div>
                        <div class="_divider_xs"></div>
                        <div id="abptf_sp_builder"><?php abptf_ajax_get_builder_html(); ?></div>
                    </div>
					<?php
				}
			}

			public function ticket_list(): void {
				$abptf_tickets = ABPTF_Function::get_option( 'abptf_ticket' );
				//echo '<pre>';				print_r( $features );				echo '</pre>';
				if ( sizeof( $abptf_tickets ) > 0 ) {
					foreach ( $abptf_tickets as $key => $abptf_ticket ) {
						$label  = $abptf_ticket['label'] ?? '';
						$prefix = $abptf_ticket['prefix'] ?? '';
						if ( ! empty( $label ) ) { ?>
                            <div class="_list_item">
                                <h6 class="_abp">
									<?php ABPTF_Layout::image_icon( $abptf_ticket['icon'] ?? '' );
										echo esc_html( $label . ' ' . ( ! empty( $prefix ) ? '(' . $prefix . ')' : '' ) ); ?>
                                </h6>
                                <div class="_group_content">
                                    <button type="button" class="_btn_light_yellow_xxs" data-id="<?php echo esc_attr( $key ); ?>" data-target-popup="#abptf_global_popup" data-type="ticket" title="<?php echo esc_attr__( 'Edit : ', 'abp-transportforge' ) . ' ' . esc_attr( $label ); ?>">✍️</button>
                                    <button type="button" class="_btn_light_danger_xxs delete_ticket" data-id="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr__( 'Trash : ', 'abp-transportforge' ) . ' ' . esc_attr( $label ); ?>">❌</button>
                                </div>
                            </div>
							<?php
						}
					}
				} else {
					ABPTF_Layout::layout_warning_info_xs( 'no_ticket_type' );
				}
			}

			public function add_ticket_type(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				ob_start();
				$tic_id        = isset( $_POST['tax_id'] ) ? absint( wp_unslash( $_POST['tax_id'] ) ) : 0;
				$abptf_tickets = ABPTF_Function::get_option( 'abptf_ticket' );
				$abptf_tickets = is_array( $abptf_tickets ) ? $abptf_tickets : [];
				$ticket        = $abptf_tickets[ $tic_id ] ?? [];
				?>
                <div class="configuration_content">
                    <table class="_abp ">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Icon', 'abp-transportforge' ); ?></th>
                            <th><?php esc_html_e( 'Ticket Name', 'abp-transportforge' ); ?><sup class="_color_required">*</sup></th>
                            <th><?php esc_html_e( 'Color', 'abp-transportforge' ); ?></th>
                            <th><?php esc_html_e( 'Prefix', 'abp-transportforge' ); ?></th>
                            <th class="_w_10"><?php esc_html_e( 'Action', 'abp-transportforge' ); ?></th>
                        </tr>
                        </thead>
                        <tbody class="insertable_area sortable_area">
						<?php self::form_ticket( $ticket, $tic_id ); ?>
                        </tbody>
                    </table>
                    <div class="_divider_xs"></div>
                    <div class="_fj_between">
						<?php ABPTF_Layout::button_add_xs( __( 'Add New Ticket Type Item', 'abp-transportforge' ) ); ?>
                        <button type="button" class="_btn_theme_xs  save_ticket_types"><span class="_mar_r_xxs">💾</span><?php esc_html_e( 'Save Ticket Types', 'abp-transportforge' ); ?></button>
                    </div>
                    <div class="abptf_d_none">
                        <table class="_abp">
                            <tbody class="hidden_content">
							<?php self::form_ticket(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
				<?php
				$html = ob_get_clean();
				wp_send_json_success( [ 'html' => $html, 'msg' => __( 'Ticket Type Form Loaded Successfully .....! ', 'abp-transportforge' ) ] );
			}

			public function save_ticket_type(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$post_int      = fn( $key, $default = 0 ) => isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
				$post_array    = fn( $key ) => ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) ) : [];
				$old_data      = ABPTF_Function::get_option( 'abptf_ticket' );
				$old_data      = is_array( $old_data ) ? $old_data : [];
				$ids           = $post_array( 'ticket_id' );
				$names         = $post_array( 'ticket_name' );
				$icon          = $post_array( 'ticket_icon' );
				$color         = $post_array( 'ticket_color' );
				$prefix        = $post_array( 'ticket_prefix' );
				$abptf_post_id = $post_int( 'abptf_post_id' );
				if ( ! empty( $names ) ) {
					foreach ( $names as $key => $name ) {
						if ( $name !== '' ) {
							$old_id = isset( $ids[ $key ] ) ? (int) $ids[ $key ] : '';
							if ( ! empty( $old_id ) && isset( $old_data[ $old_id ] ) ) {
								$id = $old_id;
							} else {
								$id = 1;
								while ( isset( $old_data[ $id ] ) ) {
									$id ++;
								}
							}
							$old_data[ $id ] = [
								'label'  => $name,
								'icon'   => $icon[ $key ] ?? '',
								'color'  => $color[ $key ] ?? '',
								'prefix' => $prefix[ $key ] ?? '',
							];
						}
					}
				}
				update_option( 'abptf_ticket', $old_data );
				$html = '';
				if ( empty( $abptf_post_id ) || $abptf_post_id <= 0 ) {
					ob_start();
					$this->ticket_list();
					$html = ob_get_clean();
				}
				wp_send_json_success( [
					'html' => $html,
					'msg'  => __( 'Ticket types Saved Successfully..........!!', 'abp-transportforge' ),
					//'feature_js' => (!empty($abptf_post_id) && $abptf_post_id>0?ABPTF_Function::get_option( 'abptf_feature_js' ):''),
				] );
			}

			public function delete_ticket_type(): void {
				if ( ! check_ajax_referer( 'abptf_admin_ajax_nonce', 'nonce', false ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Invalid security token.', 'abp-transportforge' ) ], 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( [ 'html' => '', 'msg' => __( 'Insufficient permissions.', 'abp-transportforge' ) ], 403 );
				}
				$id           = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
				$abptf_ticket = ABPTF_Function::get_option( 'abptf_ticket' );
				$abptf_ticket = is_array( $abptf_ticket ) ? $abptf_ticket : [];
				if ( ! empty( $id ) && isset( $abptf_ticket[ $id ] ) ) {
					unset( $abptf_ticket[ $id ] );
					update_option( 'abptf_ticket', $abptf_ticket );
				}
				ob_start();
				$this->ticket_list();
				$html = ob_get_clean();
				wp_send_json_success( [
					'html' => $html,
					'msg'  => __( 'Ticket type Deleted Successfully!', 'abp-transportforge' ),
				] );
			}

			public static function form_ticket( $ticket = [], $id = '' ): void {
				?>
                <tr class="delete_area">
                    <th><?php do_action( 'abptf_add_icon', 'ticket_icon[]', ( $ticket['icon'] ?? '' ) ); ?></th>
                    <th>
                        <label>
                            <input type="hidden" name="ticket_id[]" value="<?php echo esc_attr( $id ); ?>"/>
                            <input type="text" class="_form_control validation_name _max_150" name="ticket_name[]" placeholder="<?php esc_attr_e( 'EX: Ticket Name', 'abp-transportforge' ); ?>" value="<?php echo esc_attr( $ticket['label'] ?? '' ); ?>" required/>
                        </label>
                    </th>
                    <th>
                        <label>
                            <input type="text" name="ticket_color[]" disabled class="_form_control abptf_color_picker" value="<?php echo esc_attr( $ticket['color'] ?? '' ); ?>" data-default-color="#fff"/>
                        </label>
                    </th>
                    <th>
                        <label>
                            <input type="text" class="_form_control validation_name _max_100" name="ticket_prefix[]" placeholder="<?php esc_attr_e( 'EX: A', 'abp-transportforge' ); ?>" value="<?php echo esc_attr( $ticket['prefix'] ?? '' ); ?>"/>
                        </label>
                    </th>
                    <td><?php ABPTF_Layout::button_delete_sort(); ?></td>
                </tr>
				<?php
			}

			public static function get_ticket_type_js(): array {
				$abptf_tickets = ABPTF_Function::get_option( 'abptf_ticket' );
				$abptf_tickets = is_array( $abptf_tickets ) ? $abptf_tickets : [];
				$data          = [];
				if ( sizeof( $abptf_tickets ) > 0 ) {
					foreach ( $abptf_tickets as $key => $feature ) {
						$data[] = [ 'id' => $key, 'icon' => ( $feature['icon'] ?? '' ), 'label' => ( $feature['label'] ?? '' ), 'prefix' => ( $feature['prefix'] ?? '' ), 'color' => ( $feature['color'] ?? '#333' ) ];
					}
				}

				return $data;
			}
		}
		new ABPTF_Seat_Plan();
	}
	/*══ 3. STRINGS (fully translatable) ════════════════════ */
	function abptf_sp_strings() {
		return [
			'plans'                => __( 'Seat Plans', 'abptf' ),
			'builder'              => __( 'Builder', 'abptf' ),
			'new_plan'             => __( 'New Plan', 'abptf' ),
			'back'                 => __( 'Back to Plans', 'abptf' ),
			'save'                 => __( 'Save Plan', 'abptf' ),
			'clear'                => __( 'Clear', 'abptf' ),
			'plan_bg'              => __( 'Plan BG', 'abptf' ),
			'rm_bg'                => __( 'Remove BG', 'abptf' ),
			'add_row'              => __( '+ Row', 'abptf' ),
			'add_col'              => __( '+ Col', 'abptf' ),
			'rem_row'              => __( '− Row', 'abptf' ),
			'rem_col'              => __( '− Col', 'abptf' ),
			'total_seats'          => __( 'Total Seats', 'abptf' ),
			'status_note'          => __( 'Status → frontend only', 'abptf' ),
			'rows_x_cols'          => __( 'Grid', 'abptf' ),
			'cell_type'            => __( 'Cell Type', 'abptf' ),
			'grid_size'            => __( 'Grid Size', 'abptf' ),
			'rows'                 => __( 'Rows', 'abptf' ),
			'cols'                 => __( 'Cols', 'abptf' ),
			'selected_cell'        => __( 'Selected Cell', 'abptf' ),
			'click_cell'           => __( 'Click a cell to edit', 'abptf' ),
			'label'                => __( 'Label', 'abptf' ),
			'custom_text'          => __( 'Custom', 'abptf' ),
			'width_cells'          => __( 'Width', 'abptf' ),
			'cells'                => __( 'cells', 'abptf' ),
			'rotate'               => __( 'Rotate', 'abptf' ),
			'delete_cell'          => __( 'Delete Cell', 'abptf' ),
			'active_group'         => __( 'Active Group', 'abptf' ),
			'group_config'         => __( 'Group Config', 'abptf' ),
			'group_icon'           => __( 'Group Icon', 'abptf' ),
			'group_fa'             => __( 'Font Awesome', 'abptf' ),
			'group_image'          => __( 'Group BG Image', 'abptf' ),
			'no_group'             => __( 'No Group', 'abptf' ),
			'vip'                  => __( 'VIP', 'abptf' ),
			'normal'               => __( 'Normal', 'abptf' ),
			'special'              => __( 'Special', 'abptf' ),
			'adult'                => __( 'Adult', 'abptf' ),
			'female'               => __( 'Female', 'abptf' ),
			'couple'               => __( 'Couple', 'abptf' ),
			'business'             => __( 'Business', 'abptf' ),
			'economy'              => __( 'Economy', 'abptf' ),
			'cell_icon'            => __( 'Cell Icon', 'abptf' ),
			'emoji_icon'           => __( 'Emoji', 'abptf' ),
			'fa_icon'              => __( 'Font Awesome', 'abptf' ),
			'fa_placeholder'       => __( 'fa-solid fa-star', 'abptf' ),
			'current_icon'         => __( 'Current:', 'abptf' ),
			'remove_icon'          => __( 'Remove', 'abptf' ),
			'upload'               => __( 'Upload', 'abptf' ),
			'remove_bg'            => __( 'Remove BG', 'abptf' ),
			'auto_number'          => __( 'Auto Number', 'abptf' ),
			'prefix'               => __( 'Prefix', 'abptf' ),
			'apply'                => __( 'Apply', 'abptf' ),
			'apply_to_group'       => __( 'Applies to active group only', 'abptf' ),
			'groups_in_plan'       => __( 'Groups in Plan', 'abptf' ),
			'no_groups'            => __( 'No groups yet', 'abptf' ),
			'available'            => __( 'Available', 'abptf' ),
			'blocked'              => __( 'Blocked', 'abptf' ),
			'sold'                 => __( 'Sold', 'abptf' ),
			'reserved'             => __( 'Reserved', 'abptf' ),
			'delete_confirm'       => __( 'Delete this plan?', 'abptf' ),
			'clear_confirm'        => __( 'Clear entire grid?', 'abptf' ),
			'name_required'        => __( 'Plan name is required', 'abptf' ),
			'dup_label'            => __( 'Duplicate seat label!', 'abptf' ),
			'saved_ok'             => __( 'Plan saved ✓', 'abptf' ),
			'saved_local'          => __( 'Saved locally ✓', 'abptf' ),
			'deleted'              => __( 'Plan deleted', 'abptf' ),
			'numbers_ok'           => __( 'Numbers applied ✓', 'abptf' ),
			'no_plans'             => __( 'No seat plans yet', 'abptf' ),
			'no_plans_sub'         => __( 'Create your first seat plan.', 'abptf' ),
			'create_first'         => __( 'Create Plan', 'abptf' ),
			'edit'                 => __( 'Edit', 'abptf' ),
			'delete'               => __( 'Delete', 'abptf' ),
			'seats_label'          => __( 'seats', 'abptf' ),
			'click_to_restore'     => __( 'Click active tool to restore', 'abptf' ),
			'multisel_drag'        => __( '🖱 Drag → range select', 'abptf' ),
			'multisel_ctrl'        => __( 'Ctrl+Click → toggle', 'abptf' ),
			'multisel_shift'       => __( 'Shift+Click → range from last', 'abptf' ),
			'multisel_apply'       => __( 'Apply to Selection', 'abptf' ),
			'multisel_clear'       => __( 'Clear Selection', 'abptf' ),
			'tool_seat'            => __( 'Seat', 'abptf' ),
			'tool_driver'          => __( 'Driver', 'abptf' ),
			'tool_door'            => __( 'Door', 'abptf' ),
			'tool_toilet'          => __( 'Toilet', 'abptf' ),
			'tool_window'          => __( 'Window', 'abptf' ),
			'tool_food'            => __( 'Food', 'abptf' ),
			'tool_luggage'         => __( 'Luggage', 'abptf' ),
			'tool_stairs'          => __( 'Stairs', 'abptf' ),
			'tool_aisle'           => __( 'Aisle', 'abptf' ),
			'tool_exit'            => __( 'Exit', 'abptf' ),
			'tool_blank'           => __( 'Blank', 'abptf' ),
			'builder_html_loading' => __( 'Loading builder…', 'abp-transportforge' ),
			'builder_html_error'   => __( 'Failed to load builder HTML.', 'abptf-transportforge' ),
			'save_in_progress'     => __( 'Saving…', 'abptf-transportforge' ),
			'save_success'         => __( 'Saved successfully', 'abptf-transportforge' ),
			'save_error'           => __( 'Save failed', 'abptf-transportforge' ),
			'load_plans_error'     => __( 'Failed to load plans', 'abptf-transportforge' ),
			'edit_plan'            => __( 'Edit Plan', 'abptf-transportforge' ),
			'delete_plan'          => __( 'Delete Plan', 'abptf-transportforge' ),
			'cancel'               => __( 'Cancel', 'abptf-transportforge' ),
			'confirm'              => __( 'Confirm', 'abptf-transportforge' ),
			'upload_image'         => __( 'Upload Image', 'abptf-transportforge' ),
			'remove_image'         => __( 'Remove Image', 'abptf-transportforge' ),
		];
	}
	/* ══ 4. AUTH ═════════════════════════════════════════════ */
	function abptf_sp_auth( $require_manage = true ) {
		if ( ! check_ajax_referer( 'abptf_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce.' ], 403 );
		}
		if ( $require_manage && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied.' ], 403 );
		}
	}
	function abptf_sp_table_columns( $table ) {
		global $wpdb;
		$columns = $wpdb->get_col( "DESCRIBE {$table}", 0 ); // phpcs:ignore

		return is_array( $columns ) ? $columns : [];
	}
	function abptf_sp_column_name( $table, $preferred, $legacy ) {
		$columns = abptf_sp_table_columns( $table );
		if ( in_array( $preferred, $columns, true ) ) {
			return $preferred;
		}
		if ( in_array( $legacy, $columns, true ) ) {
			return $legacy;
		}

		return $preferred;
	}
	/* ══ 5. SAVE ═════════════════════════════════════════════ */
	add_action( 'wp_ajax_abptf_save_sp', 'abptf_ajax_save_sp' );
	add_action( 'wp_ajax_nopriv_abptf_save_sp', 'abptf_ajax_save_sp' );
	/* AJAX: return builder HTML for admin UI */
	add_action( 'wp_ajax_abptf_get_builder_html', 'abptf_ajax_get_builder_html' );
	add_action( 'wp_ajax_nopriv_abptf_get_builder_html', 'abptf_ajax_get_builder_html' );
	function abptf_ajax_get_builder_html() {
		//abptf_sp_auth( false );
		// Build the same HTML skeleton that JS expects. Keep it minimal — JS will attach behaviour.
		//ob_start();
		?>
        <div class="sp_toolbar">
            <label>
                <input class="_form_control" id="abp_sp_name" type="text" placeholder="<?php esc_attr_e( 'Seat Plan Name...', 'abp-transportforge' ); ?>">
            </label>
            <div class="toolbar-sep"></div>
            <div class="_group_content">
                <div class= _ag_content" onclick="abptfSetRows(parseInt(abptf_sp_get_val('abptf_sp_rows'))-1)"> ➖</div>
                <label>
                    <input type="number" class="_form_control" id="abptf_sp_rows" value="5"  min="1" onchange="abptfSetRows(this.value)"  />
                </label>
                <div class= _ag_content" onclick="abptfSetRows(parseInt(abptf_sp_get_val('abptf_sp_rows'))+1)">➕</div>
            </div>


            <div class="toolbar-rc-group" style="display:flex;align-items:center;gap:4px;">
                <span style="font-size:12px;color:var(--text3);margin-right:6px"><?php echo esc_html__( 'Cols', 'abp-transportforge' ); ?></span>
                <button class="btn btn-sm btn-ghost" onclick="abptfSetCols(parseInt(getVal('rc-input-cols'))-1)">−</button>
                <input class="prop-input" id="rc-input-cols" type="number" value="5" min="1" style="width:46px;text-align:center" onchange="abptfSetCols(this.value)">
                <button class="btn btn-sm" onclick="abptfSetCols(parseInt(getVal('rc-input-cols'))+1)">+</button>
            </div>
            <div class="toolbar-sep"></div>
            <label class="btn btn-sm" title="<?php echo esc_attr__( 'Plan BG', 'abp-transportforge' ); ?>">🖼 <?php echo esc_html__( 'Plan BG', 'abp-transportforge' ); ?>
                <input type="file" accept="image/*" style="display:none" onchange="abptfSetPlanBG(event)">
            </label>
            <button class="btn btn-sm btn-ghost" id="btn-rm-bg" style="display:none" onclick="abptfRemovePlanBG()">✕ <?php echo esc_html__( 'Remove BG', 'abp-transportforge' ); ?></button>
            <div class="sp_starts" id="sp_starts">
                <button class="btn btn-xs btn-ghost stats-clear-btn" onclick="abptfClearGrid()">✕ <?php echo esc_html__( 'Clear', 'abp-transportforge' ); ?></button>
                <div class="sp_starts-content" id="sp_starts-content"></div>
            </div>
            <div style="flex:1"></div>
            <button type="button" class="btn btn-sm btn-primary" id="btn-save-plan">💾 <?php echo esc_html__( 'Save Plan', 'abp-transportforge' ); ?></button>
        </div>
        <div class="abptf-content">
            <div class="abptf-sidebar">
                <div class="multisel-hint"><?php echo esc_html__( '🖱 Drag → range select', 'abp-transportforge' ); ?><br><?php echo esc_html__( 'Ctrl+Click → toggle', 'abp-transportforge' ); ?><br><?php echo esc_html__( 'Shift+Click → range from last', 'abp-transportforge' ); ?></div>
                <div class="sb-section">
                    <div class="sb-title"><?php echo esc_html__( 'Cell Type', 'abp-transportforge' ); ?></div>
                    <div id="tool_palette" class="_group_content_f_equal_w_full"></div>
                </div>
                <div class="sb-section" id="sb-group-section">
                    <div class="sb-title"><?php echo esc_html__( 'Active Group', 'abp-transportforge' ); ?></div>
                    <div id="active-group-btns" class="group-btns-grid"></div>
                </div>
                <div class="sb-section abptf-auto-number-panel" id="sb-autonumber-section" style="display:none">
                    <div class="sb-title"><?php echo esc_html__( 'Auto Number', 'abp-transportforge' ); ?></div>
                    <div class="auto-number-row">
                        <input class="prop-input" id="auto-prefix" type="text" placeholder="<?php echo esc_attr__( 'Prefix', 'abp-transportforge' ); ?>" style="width:80px;flex:none">
                        <input class="prop-input" id="auto-start" type="number" value="1" min="1" style="width:70px;flex:none">
                        <button class="btn btn-sm" onclick="abptfAutoNumber()"><?php echo esc_html__( 'Apply', 'abp-transportforge' ); ?></button>
                    </div>
                </div>
            </div>
            <div class="abptf-main-area">
                <div class="canvas-outer">
                    <div class="canvas-wrap" id="canvas-wrap">
                        <div id="canvas-bg-overlay" class="canvas-bg-overlay" style="display:none"></div>
                        <div class="grid-inner" id="grid-inner"></div>
                    </div>
                </div>
                <div class="abptf-legend" id="abptf-legend"></div>
            </div>
        </div>
		<?php
		//$html = ob_get_clean();
		//wp_send_json_success( [ 'html' => $html ] );
	}
	function abptf_ajax_save_sp() {
		abptf_sp_auth();
		global $wpdb;
		$t          = $wpdb->prefix . 'abptf_sp';
		$plan_name  = sanitize_text_field( wp_unslash( $_POST['plan_name'] ?? '' ) );
		$rows       = absint( $_POST['rows'] ?? 0 );
		$cols       = absint( $_POST['cols'] ?? 0 );
		$seat_count = absint( $_POST['seat_count'] ?? 0 );
		$groups     = wp_unslash( $_POST['groups_json'] ?? '[]' );
		$grid_raw   = wp_unslash( $_POST['grid_json'] ?? '[]' );
		$labels     = wp_unslash( $_POST['seat_labels_json'] ?? '[]' );
		$group_cfg  = wp_unslash( $_POST['group_config_json'] ?? '{}' );
		$bg         = wp_unslash( $_POST['plan_bg_image'] ?? '' );
		$plan_db_id = absint( $_POST['plan_db_id'] ?? 0 );
		if ( empty( $plan_name ) ) {
			wp_send_json_error( [ 'message' => 'Plan name required.' ] );
		}
		$grid = json_decode( $grid_raw, true );
		if ( ! is_array( $grid ) ) {
			wp_send_json_error( [ 'message' => 'Invalid grid_json.' ] );
		}
		$sp_table = $wpdb->prefix . 'abptf_sp';
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $sp_table ) ) !== $sp_table ) {
			if ( class_exists( 'ABPTF_Dependencies' ) ) {
				ABPTF_Dependencies::create_table();
			}
		}
		$row_field = abptf_sp_column_name( $sp_table, 'plan_rows', 'rows' );
		$col_field = abptf_sp_column_name( $sp_table, 'plan_cols', 'cols' );
		// Strip status — NEVER persisted
		foreach ( $grid as &$row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			foreach ( $row as &$cell ) {
				if ( is_array( $cell ) ) {
					unset( $cell['status'] );
				}
			}
		}
		unset( $row, $cell );
		$data = [
			'plan_name'         => $plan_name,
			$row_field          => $rows,
			$col_field          => $cols,
			'seat_count'        => $seat_count,
			'groups_json'       => $groups,
			'seat_labels_json'  => $labels,
			'group_config_json' => $group_cfg,
			'cell_width'        => absint( $_POST['cell_width'] ?? 44 ),
			'cell_height'       => absint( $_POST['cell_height'] ?? 44 ),
			'plan_bg_image'     => $bg ?: null,
			'grid_json'         => wp_json_encode( $grid ),
			'updated_at'        => current_time( 'mysql' ),
		];
		$fmt  = [ '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s' ];
		if ( $plan_db_id > 0 ) {
			$r = $wpdb->update( $t, $data, [ 'id' => $plan_db_id ], $fmt, [ '%d' ] ); // phpcs:ignore
			if ( false === $r ) {
				wp_send_json_error( [ 'message' => $wpdb->last_error ] );
			}
			wp_send_json_success( [ 'plan_db_id' => $plan_db_id, 'action' => 'updated' ] );
		} else {
			$data['created_at'] = current_time( 'mysql' );
			$fmt[]              = '%s';
			$r                  = $wpdb->insert( $t, $data, $fmt ); // phpcs:ignore
			if ( ! $r ) {
				wp_send_json_error( [ 'message' => $wpdb->last_error ] );
			}
			wp_send_json_success( [ 'plan_db_id' => $wpdb->insert_id, 'action' => 'created' ] );
		}
	}
	/* ══ 6. GET ALL ══════════════════════════════════════════ */
	add_action( 'wp_ajax_abptf_get_plans', 'abptf_ajax_get_plans' );
	add_action( 'wp_ajax_nopriv_abptf_get_plans', 'abptf_ajax_get_plans' );
	function abptf_ajax_get_plans() {
		abptf_sp_auth( false );
		global $wpdb;
		$t    = $wpdb->prefix . 'abptf_sp';
		$rows = $wpdb->get_results( "SELECT * FROM {$t} ORDER BY updated_at DESC", ARRAY_A ); // phpcs:ignore
		if ( null === $rows ) {
			wp_send_json_error( [ 'message' => $wpdb->last_error ] );
		}
		foreach ( $rows as &$row ) {
			$row['id']                = (int) $row['id'];
			$row['rows']              = (int) ( $row['plan_rows'] ?? $row['rows'] ?? 0 );
			$row['cols']              = (int) ( $row['plan_cols'] ?? $row['cols'] ?? 0 );
			$row['seat_count']        = (int) $row['seat_count'];
			$row['groups_json']       = json_decode( $row['groups_json'] ?? '[]', true ) ?: [];
			$row['seat_labels_json']  = json_decode( $row['seat_labels_json'] ?? '[]', true ) ?: [];
			$row['group_config_json'] = json_decode( $row['group_config_json'] ?? '{}', true ) ?: [];
			$row['grid_json']         = json_decode( $row['grid_json'], true ) ?: [];
		}
		unset( $row );
		wp_send_json_success( $rows );
	}
	/* ══ 7. GET SINGLE ═══════════════════════════════════════ */
	add_action( 'wp_ajax_abptf_get_sp', 'abptf_ajax_get_sp' );
	add_action( 'wp_ajax_nopriv_abptf_get_sp', 'abptf_ajax_get_sp' );
	function abptf_ajax_get_sp() {
		abptf_sp_auth( false );
		global $wpdb;
		$t  = $wpdb->prefix . 'abptf_sp';
		$id = absint( $_POST['plan_db_id'] ?? 0 );
		if ( ! $id ) {
			wp_send_json_error( [ 'message' => 'plan_db_id required.' ] );
		}
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE id = %d", $id ), ARRAY_A ); // phpcs:ignore
		if ( ! $row ) {
			wp_send_json_error( [ 'message' => 'Not found.' ], 404 );
		}
		$row['id']                = (int) $row['id'];
		$row['rows']              = (int) ( $row['plan_rows'] ?? $row['rows'] ?? 0 );
		$row['cols']              = (int) ( $row['plan_cols'] ?? $row['cols'] ?? 0 );
		$row['seat_count']        = (int) $row['seat_count'];
		$row['groups_json']       = json_decode( $row['groups_json'] ?? '[]', true ) ?: [];
		$row['seat_labels_json']  = json_decode( $row['seat_labels_json'] ?? '[]', true ) ?: [];
		$row['group_config_json'] = json_decode( $row['group_config_json'] ?? '{}', true ) ?: [];
		$row['grid_json']         = json_decode( $row['grid_json'], true ) ?: [];
		wp_send_json_success( $row );
	}
	/* ══ 8. DELETE ═══════════════════════════════════════════ */
	add_action( 'wp_ajax_abptf_delete_sp', 'abptf_ajax_delete_sp' );
	function abptf_ajax_delete_sp() {
		abptf_sp_auth();
		global $wpdb;
		$id = absint( $_POST['plan_db_id'] ?? 0 );
		if ( ! $id ) {
			wp_send_json_error( [ 'message' => 'plan_db_id required.' ] );
		}
		$r = $wpdb->delete( $wpdb->prefix . 'abptf_sp', [ 'id' => $id ], [ '%d' ] ); // phpcs:ignore
		if ( false === $r ) {
			wp_send_json_error( [ 'message' => $wpdb->last_error ] );
		}
		wp_send_json_success( [ 'deleted' => $id ] );
	}
	/* ══ 9. FRONTEND HELPER — merge booking status ══════════ */
	/**
	 * Returns plan data with seat status merged from abptf_bookings table.
	 * Use this in your booking shortcode or block.
	 *
	 * @param int $plan_id The abptf_sp.id value.
	 *
	 * @return array|null   Plan data with grid_json cells having 'status' injected, or null.
	 */
	function abptf_get_plan_with_status( $plan_id ) {
		global $wpdb;
		$sp   = $wpdb->prefix . 'abptf_sp';
		$bk   = $wpdb->prefix . 'abptf_bookings';
		$plan = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$sp} WHERE id = %d", (int) $plan_id ), ARRAY_A ); // phpcs:ignore
		if ( ! $plan ) {
			return null;
		}
		$grid   = json_decode( $plan['grid_json'], true );
		$booked = [];
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $bk ) ) === $bk ) { // phpcs:ignore
			$rows = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore
				"SELECT seat_label, status FROM {$bk} WHERE plan_id=%d AND status IN('sold','reserved','blocked')",
				(int) $plan_id
			), ARRAY_A );
			foreach ( $rows as $b ) {
				$booked[ $b['seat_label'] ] = $b['status'];
			}
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
		$plan['groups_json']       = json_decode( $plan['groups_json'] ?? '[]', true ) ?: [];
		$plan['seat_labels_json']  = json_decode( $plan['seat_labels_json'] ?? '[]', true ) ?: [];
		$plan['group_config_json'] = json_decode( $plan['group_config_json'] ?? '{}', true ) ?: [];
		$plan['id']                = (int) $plan['id'];

		return $plan;
	}


