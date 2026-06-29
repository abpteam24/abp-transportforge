<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Frontend' ) ) {
		class ABPTF_Frontend {
			public function __construct() {
				add_filter( 'single_template', [ $this, 'load_single_page' ] );
				add_filter( 'template_include', array( $this, 'load_taxonomy_page' ) );
			}

			public function load_single_page( $template ): string {
				if ( is_singular( ABPTF_Function::get_cpt() ) ) {
					$custom_template = ABPTF_Function::template_path( 'page/details_page.php' );
					if ( ! empty( $custom_template ) ) {
						return $custom_template;
					}
				}

				return (string) $template;
			}

			public function load_taxonomy_page( $template ): string {
				if ( is_tax( 'abptf_category' ) ) {
					return ABPTF_Function::template_path( 'page/category.php' );
				}
				if ( is_tax( 'abptf_location' ) ) {
					return ABPTF_Function::template_path( 'page/location.php' );
				}
				if ( is_tax( 'abptf_brand' ) ) {
					return ABPTF_Function::template_path( 'page/brand.php' );
				}

				return (string) $template;
			}
		}
		new ABPTF_Frontend();
	}