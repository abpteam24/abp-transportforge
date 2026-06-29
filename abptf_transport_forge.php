<?php
	/**
	 * Plugin Name: ABP TransportForge
	 * Description: TransportForge transforms WooCommerce into a complete transportation ticketing platform, enabling businesses to manage routes, reservations, and online ticket sales efficiently from a single dashboard.
	 * Version: 1.0.0
	 * Author: abpteam
	 * Author URI: https://abp-team.com
	 * Text Domain: abp-transportforge
	 * Domain Path: /languages
	 * WC requires at least: 8.0.0
	 *  WC tested up to: latest
	 *  Requires PHP: 7.4
	 *  Requires MySQL: 5.7+
	 *  License: GPLv3
	 *  License URI: https://www.gnu.org/licenses/gpl-3.0.html
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Transport_Forge' ) ) {
		class ABPTF_Transport_Forge {
			public function __construct() {
				add_action( 'admin_init', function () {
					if ( ! function_exists( 'is_plugin_active' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
				} );
				$this->load_plugin();
			}

			private function load_plugin(): void {
				if ( ! defined( 'ABPTF_PLUGIN_FILE' ) ) {
					define( 'ABPTF_PLUGIN_FILE', __FILE__ );
				}
				if ( ! defined( 'ABPTF_VERSION' ) ) {
					define( 'ABPTF_VERSION', '1.0.0' );
				}
				if ( ! defined( 'ABPTF_DIR' ) ) {
					define( 'ABPTF_DIR', plugin_dir_path( __FILE__ ) );
				}
				if ( ! defined( 'ABPTF_URL' ) ) {
					define( 'ABPTF_URL', plugin_dir_url( __FILE__ ) );
				}
				if ( ! defined( 'ABPTF_BASE' ) ) {
					define( 'ABPTF_BASE', basename( __FILE__ ) );
				}
				if ( ! defined( 'ABPTF_BLANK_IMG_URL' ) ) {
					define( 'ABPTF_BLANK_IMG_URL', ABPTF_URL . 'assets/images/blank_image.png' );
				}
				require_once ABPTF_DIR . 'includes/abptf_dependencies.php';
				if ( ! defined( 'ABPTF_WC' ) ) {
					define( 'ABPTF_WC', ABPTF_Function::check_wc() );
				}
				if ( ! defined( 'ABPTF_Configuration' ) ) {
					define( 'ABPTF_Configuration', ABPTF_Function::get_option( 'abptf_configuration' ) );
				}
				if ( ! defined( 'ABPTF_Date_Config' ) ) {
					define( 'ABPTF_Date_Config', ABPTF_Function::get_option( 'abptf_date_config' ) );
				}
				if ( ! defined( 'ABPTF_Dates' ) ) {
					define( 'ABPTF_Dates', ABPTF_Function::get_option( 'abptf_dates' ) );
				}
				if ( ! defined( 'ABPTF_Category' ) ) {
					define( 'ABPTF_Category', ABPTF_Function::get_option( 'abptf_category' ) );
				}
				if ( ! defined( 'ABPTF_Organizer' ) ) {
					define( 'ABPTF_Organizer', ABPTF_Function::get_option( 'abptf_organizer' ) );
				}
				if ( ! defined( 'ABPTF_Features' ) ) {
					define( 'ABPTF_Features', ABPTF_Function::get_option( 'abptf_feature' ) );
				}
				if ( ! defined( 'ABPTF_Locations' ) ) {
					define( 'ABPTF_Locations', ABPTF_Function::get_option( 'abptf_location' ) );
				}
				if ( ! defined( 'ABPTF_Brands' ) ) {
					define( 'ABPTF_Brands', ABPTF_Function::get_option( 'abptf_brand' ) );
				}
				if ( ! defined( 'ABPTF_Min_Price' ) ) {
					define( 'ABPTF_Min_Price', ABPTF_Function::get_option( 'abptf_min_price' ) );
				}
				if ( ! defined( 'ABPTF_On_Off' ) ) {
					define( 'ABPTF_On_Off', ABPTF_Function::get_option( 'abptf_on_off' ) );
				}
				if ( ! defined( 'ABPTF_JS_Date_Format' ) ) {
					define( 'ABPTF_JS_Date_Format', ABPTF_Function::date_format_js() );
				}
				if ( ! defined( 'ABPTF_Time_Format' ) ) {
					define( 'ABPTF_Time_Format', ABPTF_Date_Config['time_format'] ?? get_option( 'time_format' ) );
				}
			}
		}
		new ABPTF_Transport_Forge();
		register_activation_hook( __FILE__, function () {
			if ( class_exists( 'ABPTF_Dependencies' ) ) {
				ABPTF_Dependencies::activation();
			}
		} );
		register_deactivation_hook( __FILE__, function () {
			if ( class_exists( 'ABPTF_Dependencies' ) ) {
				ABPTF_Dependencies::deactivate();
			}
		} );
	}