<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Dependencies' ) ) {
		class ABPTF_Dependencies {
			public function __construct() {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ), 90 );
				add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ), 90 );
				$this->load_file();
				add_action( 'init', [ $this, 'register_cpt' ] );
				add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );
				add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );
				add_action( 'upgrader_process_complete', [ $this, 'flush_rewrite' ] );
				add_action( 'admin_init', array( $this, 'activation_redirect' ) );
			}

			public function admin_enqueue(): void {
				$this->global_enqueue();
				wp_enqueue_editor();
				wp_enqueue_media();
				//admin script
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_style( 'wp-codemirror' );
				wp_enqueue_script( 'wp-codemirror' );
				//=============================//
				wp_enqueue_script( 'abptf_admin', ABPTF_URL . 'assets/js/abptf_admin.js', array( 'jquery' ), time(), true );
				wp_localize_script( 'abptf_admin', 'abptf_admin_data', [
					'ajax_url'     => admin_url( 'admin-ajax.php' ),
					'nonce'        => wp_create_nonce( 'abptf_admin_ajax_nonce' ),
					'icon_url'     => ABPTF_URL . 'assets/js/abptf_icons.json',
					'related_info' => wp_json_encode( ABPTF_Function::related_info_js( get_the_ID() ) ),
					'feature_data' => wp_json_encode( ABPTF_Function::get_option( 'abptf_feature_js' ) ),
					'msg'          => [
						'confirm_delete'       => __( 'Are you sure you want to delete this item?', 'abp-transportforge' ),
						'confirm_ok'           => __( '1. Ok : To Remove Item .', 'abp-transportforge' ),
						'confirm_cancel'       => __( '2. Cancel : To Cancel .', 'abp-transportforge' ),
						'saving'               => __( 'Saving.............!', 'abp-transportforge' ),
						'saved'                => __( 'Saved...............!', 'abp-transportforge' ),
						'importing'            => __( 'Importing........', 'abp-transportforge' ),
						'imported'             => __( 'Imported Successfully............. !', 'abp-transportforge' ),
						'loading'              => __( 'Loading........', 'abp-transportforge' ),
						'loaded'               => __( 'Loaded Successfully............. !', 'abp-transportforge' ),
						'order_loading'        => __( 'Order Loading........ !', 'abp-transportforge' ),
						'error'                => __( 'An error occurred. Please try again.', 'abp-transportforge' ),
						'deleting'             => __( 'Deleting.............', 'abp-transportforge' ),
						'delete_success'       => __( 'Delete Successfully.............', 'abp-transportforge' ),
						'property_loading'     => __( 'Property List Loading.............', 'abp-transportforge' ),
						'post_loading'         => __( 'Post List Loading.............', 'abp-transportforge' ),
						'post_deleting'        => __( 'Post Permanent Deleting.........!', 'abp-transportforge' ),
						'post_trashing'        => __( 'Post move to Trashing.........!', 'abp-transportforge' ),
						'post_restoring'       => __( 'Post Restoring.........!', 'abp-transportforge' ),
						'wc_install'           => __( 'Woocommerce Downloading And Installing.........!', 'abp-transportforge' ),
						'wc_installing'        => __( 'Woocommerce  Installing.........!', 'abp-transportforge' ),
						'wc_installed_success' => __( 'Woocommerce Downloaded And Installed successfully ..... !! ', 'abp-transportforge' ),
						'wc_installed'         => __( 'Woocommerce Installed successfully.... !  ', 'abp-transportforge' ),
						'create_post_page'     => __( 'Page Creating ........!', 'abp-transportforge' ),
						'no_item'              => __( 'No More Item Found !', 'abp-transportforge' ),
						'no_item_selected'     => __( 'No Item selected !', 'abp-transportforge' ),
					],
				] );
				wp_enqueue_style( 'abptf_admin', ABPTF_URL . 'assets/css/abptf_admin.css', array(), time() );
				wp_enqueue_script( 'abptf_sp', ABPTF_URL . 'assets/js/abptf_sp.js', array( 'jquery' ), time(), true );
				wp_localize_script( 'abptf_sp', 'abptf_config', [
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'abptf_nonce' ),
					'strings'  => abptf_sp_strings(),
				] );
				//=============================//
				do_action( 'abptf_admin_enqueue' );
			}

			public function frontend_enqueue(): void {
				if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
					wp_enqueue_script( 'wc-checkout' );
					wp_enqueue_style( 'select2' );
					wp_enqueue_script( 'select2' );
				}
				wp_enqueue_script( 'abptf_frontend', ABPTF_URL . 'assets/js/abptf_frontend.js', array( 'jquery' ), time(), true );
				wp_enqueue_script( 'abptf_slick', ABPTF_URL . 'assets/js/slick.min.js', array( 'jquery' ), ABPTF_VERSION, true );
				$this->global_enqueue();
				do_action( 'abptf_frontend_enqueue' );
			}

			public function global_enqueue(): void {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_style( 'abptf_jquery_ui', ABPTF_URL . 'assets/css/jquery-ui.min.css', array(), '1.13.2' );
				wp_enqueue_style( 'abptf_font_awesome', ABPTF_URL . 'assets/css/font_awesome.min.css', array(), '5.15.4' );
				wp_enqueue_style( 'abptf_lib', ABPTF_URL . 'assets/css/abptf_lib.css', array(), time() );
				wp_enqueue_script( 'abptf_lib', ABPTF_URL . 'assets/js/abptf_lib.js', array( 'jquery' ), time(), true );
				if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
					wp_localize_script( 'abptf_lib', 'abptf_var', [
						'currency_symbol'     => get_woocommerce_currency_symbol(),
						'currency_position'   => get_option( 'woocommerce_currency_pos' ),
						'currency_decimal'    => wc_get_price_decimal_separator(),
						'thousands_separator' => wc_get_price_thousand_separator(),
						'decimal_num'         => ABPTF_Function::get_option( 'woocommerce_price_num_decimals', 2 ),
						'currency_suffix'     => ABPTF_Function::get_option( 'woocommerce_price_display_suffix', '' ),
						'blank_image'         => ABPTF_BLANK_IMG_URL,
						'date_format'         => ABPTF_JS_Date_Format,
					] );
				} else {
					wp_localize_script( 'abptf_lib', 'abptf_var', [
						'currency_symbol'     => '',
						'currency_position'   => '',
						'currency_decimal'    => '',
						'thousands_separator' => '',
						'decimal_num'         => '',
						'wc_suffix'           => '',
						'blank_image'         => ABPTF_BLANK_IMG_URL,
						'date_format'         => ABPTF_JS_Date_Format,
					] );
				}
				$abptf_css_var   = ABPTF_Function::get_option( 'abptf_css_var' );
				$default_color   = ( $abptf_css_var['color_default'] ?? null ) ?: '#303030';
				$color_theme     = ( $abptf_css_var['color_theme'] ?? null ) ?: '#95951c';
				$alternate_color = ( $abptf_css_var['color_theme_alternate'] ?? null ) ?: '#fff';
				$color_warning   = ( $abptf_css_var['color_warning'] ?? null ) ?: '#E67C30';
				$bg_section      = ( $abptf_css_var['bg_section'] ?? null ) ?: '#FAFCFE';
				$bg_button       = ( $abptf_css_var['bg_button'] ?? null ) ?: '#222';
				$color_button    = ( $abptf_css_var['color_button'] ?? null ) ?: $alternate_color;
				$color_theme_ee  = $color_theme . 'ee';
				$color_theme_cc  = $color_theme . 'cc';
				$color_theme_aa  = $color_theme . 'aa';
				$color_theme_88  = $color_theme . '88';
				$color_theme_77  = $color_theme . '77';
				$default_br      = ! empty( $abptf_css_var['br_default'] ) ? $abptf_css_var['br_default'] . 'px' : '0';
				$br_xl           = ! empty( $abptf_css_var['br_default'] ) ? $abptf_css_var['br_default'] * 2 . 'px' : '0';
				$fs_h1           = ! empty( $abptf_css_var['fs_h1'] ) ? $abptf_css_var['fs_h1'] . 'px' : '35px';
				$fs_h2           = ! empty( $abptf_css_var['fs_h2'] ) ? $abptf_css_var['fs_h2'] . 'px' : '30px';
				$fs_h3           = ! empty( $abptf_css_var['fs_h3'] ) ? $abptf_css_var['fs_h3'] . 'px' : '25px';
				$fs_h4           = ! empty( $abptf_css_var['fs_h4'] ) ? $abptf_css_var['fs_h4'] . 'px' : '20px';
				$fs_h5           = ! empty( $abptf_css_var['fs_h5'] ) ? $abptf_css_var['fs_h5'] . 'px' : '17px';
				$fs_h6           = ! empty( $abptf_css_var['fs_h6'] ) ? $abptf_css_var['fs_h6'] . 'px' : '15px';
				$fs_label        = ! empty( $abptf_css_var['fs_label'] ) ? $abptf_css_var['fs_label'] . 'px' : '14px';
				$default_fs      = ! empty( $abptf_css_var['fs_default'] ) ? $abptf_css_var['fs_default'] . 'px' : '12px';
				$button_fs       = ! empty( $abptf_css_var['fs_button'] ) ? $abptf_css_var['fs_button'] . 'px' : '14px';
				$off             = esc_html__( 'OFF', 'abp-transportforge' );
				$on              = esc_html__( 'ON', 'abp-transportforge' );
				$abptf_var       =
					":root {
						--tf_br: {$default_br};						
						--tf_br_xl: {$br_xl};						
						--tf_text_off:'{$off}';
						--tf_text_on: '{$on}';
						--tf_fs: {$default_fs};				
						--tf_fs_label: {$fs_label};
						--tf_fs_h6: {$fs_h6};
						--tf_fs_h5: {$fs_h5};
						--tf_fs_h4: {$fs_h4};
						--tf_fs_h3: {$fs_h3};
						--tf_fs_h2: {$fs_h2};
						--tf_fs_h1: {$fs_h1};						
						--tf_button_bg: {$bg_button};
						--tf_button_color: {$color_button};
						--tf_button_fs: {$button_fs};						
						--tf_color_default: {$default_color};						
						--tf_color_section: {$bg_section};
						--tf_color_theme: {$color_theme};
						--tf_color_theme_ee: {$color_theme_ee};
						--tf_color_theme_cc: {$color_theme_cc};
						--tf_color_theme_aa: {$color_theme_aa};
						--tf_color_theme_88: {$color_theme_88};
						--tf_color_theme_77: {$color_theme_77};
						--tf_color_theme_alter: {$alternate_color};
						--tf_color_warning:{$color_warning};						
					}";
				wp_add_inline_style( 'abptf_lib', wp_kses_post( $abptf_var ) );
				wp_enqueue_style( 'abptf', ABPTF_URL . 'assets/css/abptf.css', array(), time() );
				$all_time = ABPTF_Function::get_time( get_the_id(), 'js' );
				wp_enqueue_script( 'abptf_infos', ABPTF_URL . 'assets/js/abptf.js', array( 'jquery' ), time(), true );
				$rental_data = array(
					'ajax_url'  => admin_url( 'admin-ajax.php' ),
					'nonce'     => wp_create_nonce( 'abptf_ajax_nonce' ),
					'date_info' => wp_json_encode( $all_time ),
					'now'       => current_time( 'Y-m-d H:i' ),
					'msg'       => [
						'end_date_loading'    => __( 'End Date  Loading.............', 'abp-transportforge' ),
						'property_loading'    => __( 'Property List Loading.............', 'abp-transportforge' ),
						'select_post'         => __( 'Please Select', 'abp-transportforge' ) . ' ' . ABPTF_Function::label(),
						'select_journey_date' => __( 'Please Select Journey Date', 'abp-transportforge' ),
						'select_return_date'  => __( 'Please Select Return Date', 'abp-transportforge' ),
						'select_journey_time' => __( 'Please Select Journey Time', 'abp-transportforge' ),
						'select_return_time'  => __( 'Please Select Return Time', 'abp-transportforge' ),
						'free'                => __( 'FREE', 'abp-transportforge' ),
						'loading'             => __( 'Loading..............!', 'abp-transportforge' ),
					],
				);
				wp_localize_script( 'abptf_infos', 'abptf_infos', $rental_data );
				do_action( 'abptf_global_script' );
			}

			private function load_file(): void {
				require_once ABPTF_DIR . 'includes/abptf_function.php';
				require_once ABPTF_DIR . 'includes/abptf_query.php';
				require_once ABPTF_DIR . 'includes/abptf_layout.php';
				if ( is_admin() ) {
					require_once ABPTF_DIR . 'admin/abptf_admin.php';
					require_once ABPTF_DIR . 'admin/abptf_post.php';
					require_once ABPTF_DIR . 'admin/abptf_dashboard.php';
					require_once ABPTF_DIR . 'admin/abptf_orders.php';
					require_once ABPTF_DIR . 'admin/abptf_dates.php';
					require_once ABPTF_DIR . 'admin/abptf_additional.php';
					require_once ABPTF_DIR . 'admin/abptf_form.php';
					require_once ABPTF_DIR . 'admin/abptf_seat_plan.php';
					require_once ABPTF_DIR . 'admin/abptf_resource.php';
					require_once ABPTF_DIR . 'admin/abptf_configuration.php';
					require_once ABPTF_DIR . 'admin/abptf_status.php';
					require_once ABPTF_DIR . 'admin/abptf_category.php';
					require_once ABPTF_DIR . 'admin/abptf_organizer.php';
					require_once ABPTF_DIR . 'admin/abptf_location.php';
					require_once ABPTF_DIR . 'admin/abptf_brand.php';
					require_once ABPTF_DIR . 'admin/abptf_feature.php';
				}
				if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
					require_once ABPTF_DIR . 'includes/abptf_hooks.php';
					require_once ABPTF_DIR . 'includes/abptf_ajax.php';
					require_once ABPTF_DIR . 'includes/abptf_frontend.php';
					require_once ABPTF_DIR . 'includes/abptf_shortcodes.php';
					require_once ABPTF_DIR . 'includes/abptf_woocommerce.php';
					require_once ABPTF_DIR . 'admin/abptf_hidden_post.php';
				}
			}

			public function register_cpt(): void {
				$cpt   = ABPTF_Function::get_cpt();
				$label = ABPTF_Function::label();
				register_post_type( $cpt, [
					'public'              => true,
					'labels'              => [
						'name'                  => esc_html( $label ),
						'singular_name'         => esc_html( $label ),
						'menu_name'             => esc_html( $label ),
						'name_admin_bar'        => esc_html( $label ),
						'archives'              => __( 'Post List', 'abp-transportforge' ),
						'attributes'            => __( 'Post List', 'abp-transportforge' ),
						'parent_item_colon'     => __( 'Post Item:', 'abp-transportforge' ),
						'all_items'             => __( 'Post', 'abp-transportforge' ),
						'add_new_item'          => __( 'Add Post', 'abp-transportforge' ),
						'add_new'               => __( 'Add Post', 'abp-transportforge' ),
						'new_item'              => __( 'Add Post', 'abp-transportforge' ),
						'edit_item'             => __( 'Edit Post', 'abp-transportforge' ),
						'update_item'           => __( 'Update Post', 'abp-transportforge' ),
						'view_item'             => __( 'View Post', 'abp-transportforge' ),
						'view_items'            => __( 'View Post', 'abp-transportforge' ),
						'search_items'          => __( 'Search Post', 'abp-transportforge' ),
						'not_found'             => __( 'Post Not Found', 'abp-transportforge' ),
						'not_found_in_trash'    => __( 'Post Not found in Trash', 'abp-transportforge' ),
						'featured_image'        => __( 'Post Image', 'abp-transportforge' ),
						'set_featured_image'    => __( 'Post Image', 'abp-transportforge' ),
						'remove_featured_image' => __( 'Remove Post Image', 'abp-transportforge' ),
						'use_featured_image'    => __( 'Use image Post as featured image', 'abp-transportforge' ),
						'insert_into_item'      => __( 'Insert  Post', 'abp-transportforge' ),
						'uploaded_to_this_item' => __( 'Uploaded  Post', 'abp-transportforge' ),
						'items_list'            => __( 'Post List', 'abp-transportforge' ),
						'items_list_navigation' => __( 'Category list navigation', 'abp-transportforge' ),
						'filter_items_list'     => __( 'Filter Post List', 'abp-transportforge' )
					],
					'menu_icon'           => ABPTF_Function::icon_wp(),
					'supports'            => [ 'title', 'editor', 'thumbnail' ],
					'rewrite'             => [ 'slug' => ABPTF_Function::slug(), 'with_front' => true, 'pages' => true, 'feeds' => true, ],
					'show_in_rest'        => true,
					'rest_base'           => 'abptf_post',
					'capability_type'     => 'post',
					'publicly_queryable'  => true,  // you should be able to query it
					'show_ui'             => true,  // you should be able to edit it in wp-admin
					'show_in_menu'        => false,
					'exclude_from_search' => true,  // you should exclude it from search results
					'show_in_nav_menus'   => true,  // you should be able to add it to menus
					'has_archive'         => true,  // it should have archive page
				] );
				register_taxonomy( 'abptf_category', $cpt, [
					'hierarchical'      => true,
					"public"            => true,
					'labels'            => [
						'name'          => $label . ' ' . ABPTF_Function::category_label(),
						'singular_name' => $label . ' ' . ABPTF_Function::category_label(),
					],
					'show_ui'           => true,
					'show_admin_column' => false,
					'show_in_menu'      => false,
					'query_var'         => true,
					'rewrite'           => [ 'slug' => ABPTF_Function::category_slug() ],
					'show_in_rest'      => true,
					'rest_base'         => 'abptf_category',
					'meta_box_cb'       => false,
				] );
				register_taxonomy( 'abptf_organizer', $cpt, [
					'hierarchical'      => true,
					"public"            => true,
					'labels'            => [
						'name'          => $label . ' ' . ABPTF_Function::organizer_label(),
						'singular_name' => $label . ' ' . ABPTF_Function::organizer_label(),
					],
					'show_ui'           => true,
					'show_admin_column' => false,
					'show_in_menu'      => false,
					'query_var'         => true,
					'rewrite'           => [ 'slug' => ABPTF_Function::organizer_slug() ],
					'show_in_rest'      => true,
					'rest_base'         => 'abptf_organizer',
					'meta_box_cb'       => false,
				] );
				register_taxonomy( 'abptf_location', $cpt, [
					'hierarchical'      => true,
					"public"            => true,
					'labels'            => [
						'name'          => $label . ' ' . ABPTF_Function::location_label(),
						'singular_name' => $label . ' ' . ABPTF_Function::location_label(),
					],
					'show_ui'           => true,
					'show_admin_column' => false,
					'show_in_menu'      => false,
					'query_var'         => true,
					'rewrite'           => [ 'slug' => ABPTF_Function::location_slug() ],
					'show_in_rest'      => true,
					'rest_base'         => 'abptf_location',
					'meta_box_cb'       => false,
				] );
				register_taxonomy( 'abptf_brand', $cpt, [
					'hierarchical'      => true,
					"public"            => true,
					'labels'            => [
						'name'          => $label . ' ' . ABPTF_Function::brand_label(),
						'singular_name' => $label . ' ' . ABPTF_Function::brand_label(),
					],
					'show_ui'           => true,
					'show_admin_column' => false,
					'show_in_menu'      => false,
					'query_var'         => true,
					'rewrite'           => [ 'slug' => ABPTF_Function::brand_slug() ],
					'show_in_rest'      => true,
					'rest_base'         => 'abptf_brand',
					'meta_box_cb'       => false,
				] );
				flush_rewrite_rules();
			}

			public static function activation(): void {
				self::create_table();
				flush_rewrite_rules();
			}

			public static function deactivate(): void {
				flush_rewrite_rules();
			}

			public static function create_table(): void {
				global $wpdb;
				$order_table = $wpdb->prefix . 'abptf_orders';
				$sp_table    = $wpdb->prefix . 'abptf_sp';
				$collate     = $wpdb->get_charset_collate();
				// Orders Table
				$abptf_orders = "CREATE TABLE $order_table (
					        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					        order_id bigint(20) unsigned NOT NULL,
					        item_id bigint(20) unsigned NOT NULL,
					        post_id bigint(20) unsigned NOT NULL,
					        user_id bigint(20) unsigned NOT NULL,
					        origin varchar(100) DEFAULT NULL,
					        origin_time datetime DEFAULT NULL,
					        bp varchar(100) DEFAULT NULL,
					        bp_time datetime DEFAULT NULL,
					        dp varchar(100) DEFAULT NULL,
					        dp_time datetime DEFAULT NULL,        
					        pick_up varchar(100) DEFAULT NULL,
					        drop_off varchar(100) DEFAULT NULL,
					        category varchar(50) DEFAULT NULL,
					        location varchar(50) DEFAULT NULL,
					        brand varchar(50) DEFAULT NULL,        
					        price_info longtext NOT NULL,
					        ticket longtext NOT NULL,
					        qty int(5) NOT NULL DEFAULT 1,
					        ticket_info longtext NOT NULL,
					        ex_id longtext NOT NULL,
					        ex_info longtext NOT NULL,
					        pass_info longtext NOT NULL,
					        checkin TINYINT(1) NOT NULL DEFAULT 0,
					        female TINYINT(1) NOT NULL DEFAULT 0,
					        order_status varchar(20) NOT NULL,
					        payment_method varchar(100) DEFAULT NULL,
					        billing_name varchar(100) DEFAULT NULL,
					        billing_email varchar(100) DEFAULT NULL,
					        billing_phone varchar(20) DEFAULT NULL,
					        billing_address varchar(255) DEFAULT NULL,
					        others longtext DEFAULT NULL,
					        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
					        updated_at datetime DEFAULT NULL,
					        PRIMARY KEY  (id),
					        KEY order_id (order_id),
					        KEY user_id (user_id),
					        KEY item_id (item_id)
					    ) $collate;";
				$sp           = "CREATE TABLE $sp_table (
				        id mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
				        plan_name varchar(100) DEFAULT NULL,
				        rows  mediumint(4)  NOT NULL DEFAULT 0,
       					 cols  mediumint(4)  NOT NULL DEFAULT 0,
       					 cell_width_default  SMALLINT(4)  NOT NULL DEFAULT 44,
       					 cell_height_default SMALLINT(4)  NOT NULL DEFAULT 44,
       					 seat_count mediumint(4)  NOT NULL DEFAULT 0,
				        groups_json    TEXT                  DEFAULT NULL,
				        seat_labels_json  TEXT                  DEFAULT NULL,
				        plan_bg_image  LONGTEXT              DEFAULT NULL,
				        grid_json      LONGTEXT     NOT NULL,
				        created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
				        updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
				        ON UPDATE CURRENT_TIMESTAMP,
        				PRIMARY KEY    (id)
				    ) $collate;";
				if ( ! function_exists( 'dbDelta' ) ) {
					require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				}
				dbDelta( $abptf_orders );
				dbDelta( $sp );
			}

			public function plugin_settings_link( $links_array, $plugin_file_name ) {
				if ( strpos( $plugin_file_name, ABPTF_BASE ) ) {
					array_unshift( $links_array, '<a class="_abp" href="' . esc_url( ABPTF_Function::build_url( 'configuration' ) ) . '">' . __( 'Configuration', 'abp-transportforge' ) . '</a>' );
				}

				return $links_array;
			}

			public function flush_rewrite(): void {
				flush_rewrite_rules();
			}

			public function disable_gutenberg( $current_status, $post_type ) {
				if ( $post_type === ABPTF_Function::get_cpt() ) {
					return false;
				}

				return $current_status;
			}

			public function activation_redirect(): void {
				$active_tab = '';
				$page       = '';
				if ( isset( $_GET['_abptf_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_abptf_nonce'] ) ), 'abptf_url_action' ) ) {
					$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'status';
					$page       = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
				}
				if ( $page === ABPTF_Function::slug() && ABPTF_WC < 2 && $active_tab != 'status' ) {
					wp_safe_redirect( ABPTF_Function::build_url( 'status' ) );
					exit;
				}
			}
		}
		new ABPTF_Dependencies();
	}