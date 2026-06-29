<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTF_Hooks' ) ) {
		class ABPTF_Hooks {
			public function __construct() {
				add_action( 'abptf_load_details_template', [ $this, 'details_template' ] );
				add_action( 'abptf_title', [ $this, 'title' ], 10, 2 );
				add_action( 'abptf_sub_title', [ $this, 'sub_title' ], 10, 2 );
				add_action( 'abptf_category', [ $this, 'category' ], 10, 3 );
				add_action( 'abptf_location', [ $this, 'location' ], 10, 3 );
				add_action( 'abptf_search_form', [ $this, 'search_form' ], 10, 2 );
				add_action( 'abptf_post_filter', [ $this, 'post_filter' ], 10, 2 );
				add_action( 'abptf_property_item', [ $this, 'property_item' ], 10, 2 );
				add_action( 'abptf_property_item_group', [ $this, 'property_item_group' ], 10, 2 );
				add_action( 'abptf_duration', [ $this, 'rental_duration' ], 10, 2 );
				add_action( 'abptf_registration', [ $this, 'registration' ] );
				add_action( 'abptf_additional', [ $this, 'additional' ], 10, 2 );
				add_action( 'abptf_client_form', [ $this, 'client_form' ], 10, 2 );
				add_action( 'abptf_total_price', [ $this, 'total_price' ] );
				add_action( 'abptf_content', [ $this, 'the_content' ] );
				add_action( 'abptf_pagination', [ $this, 'pagination' ] );
				add_action( 'abptf_display_cart_item', [ $this, 'display_cart_item' ] );
				add_action( 'abptf_faq', [ $this, 'faq' ], 10, 2 );
				add_action( 'abptf_term_condition', [ $this, 'term_condition' ], 10, 2 );
				add_action( 'abptf_related_item', [ $this, 'related_item' ], 10, 2 );
				add_action( 'abptf_slider', [ $this, 'slider' ], 10, 3 );
				add_action( 'abptf_slider_popup', [ $this, 'slider_popup' ], 10, 3 );
			}
			public function details_template( $post_id ): void {
				require_once ABPTF_Function::details_template_path( $post_id );
				$template_name = ABPTF_Function::get_post_info( $post_id, 'abptf_template', 'grid' );
				do_action( 'abptf_details_' . $template_name . '_template', $post_id );
			}
			public function title( $post_id, $abptf_infos = [] ): void {
				include_once ABPTF_Function::template_path( 'layout/title.php' );
				do_action( 'abptf_title_template', $post_id, $abptf_infos );
			}
			public function sub_title( $post_id, $abptf_infos = [] ): void {
				include_once ABPTF_Function::template_path( 'layout/sub_title.php' );
				do_action( 'abptf_sub_title_template', $post_id, $abptf_infos );
			}
			public function category( $post_id, $ribbon = '' ): void {
				include_once ABPTF_Function::template_path( 'layout/category.php' );
				do_action( 'abptf_category_template', $post_id, $ribbon );
			}
			public function location( $post_id, $ribbon = '' ): void {
				include_once ABPTF_Function::template_path( 'layout/location.php' );
				do_action( 'abptf_location_template', $post_id, $ribbon );
			}
			public function search_form( $abptf_infos = [] ): void {
				include_once ABPTF_Function::template_path( 'layout/search_form.php' );
				do_action( 'abptf_search_form_template', $abptf_infos );
			}
			public function post_filter( $params = [] ): void {
				include_once ABPTF_Function::template_path( 'layout/post_filter.php' );
				do_action( 'abptf_post_filter_template', $params );
			}
			public function property_item( $abptf_infos, $property = [] ): void {
				include_once ABPTF_Function::template_path( 'layout/property_item.php' );
				do_action( 'abptf_property_item_template', $abptf_infos, $property );
			}
			public function property_item_group( $abptf_infos, $properties = [] ): void {
				include_once ABPTF_Function::template_path( 'layout/property_item_group.php' );
				do_action( 'abptf_property_item_group_template', $abptf_infos, $properties );
			}
			public function registration( $abptf_infos = [] ): void {
				include_once ABPTF_Function::template_path( 'layout/registration.php' );
				do_action( 'abptf_registration_template', $abptf_infos );
			}
			public function rental_duration( $abptf_infos = [] ): void {
				include_once ABPTF_Function::template_path( 'layout/rental_duration.php' );
				do_action( 'abptf_duration_template', $abptf_infos );
			}
			public function additional( $post_id, $abptf_infos = [] ): void {
				include_once ABPTF_Function::template_path( 'layout/additional_services.php' );
				do_action( 'abptf_additional_template', $post_id, $abptf_infos );
			}
			public function client_form( $post_id, $abptf_infos = [] ): void {
				include_once ABPTF_Function::template_path( 'layout/client_form.php' );
				do_action( 'abptf_client_form_template', $post_id, $abptf_infos );
			}
			public function total_price( $abptf_infos ): void {
				include_once ABPTF_Function::template_path( 'layout/total_price.php' );
				do_action( 'abptf_total_price_template', $abptf_infos );
			}
			public function the_content( $post_id ): void {
				include_once ABPTF_Function::template_path( 'layout/the_content.php' );
				do_action( 'abptf_content_template', $post_id );
			}
			public function pagination( $args ): void {
				include_once ABPTF_Function::template_path( 'layout/pagination.php' );
				do_action( 'abptf_pagination_template', $args );
			}
			public function display_cart_item( $cart_item = [] ): void {
				include_once ABPTF_Function::template_path( 'layout/display_cart_item.php' );
				do_action( 'abptf_display_cart_item_template', $cart_item );
			}
			public function faq( $abptf_infos = [], $type = '' ): void {
				include_once ABPTF_Function::template_path( 'layout/faq.php' );
				do_action( 'abptf_faq_template', $abptf_infos, $type );
			}
			public function term_condition( $abptf_infos = [], $type = '' ): void {
				include_once ABPTF_Function::template_path( 'layout/term_condition.php' );
				do_action( 'abptf_term_condition_template', $abptf_infos, $type );
			}
			public function related_item( $related_item = '' ): void {
				include_once ABPTF_Function::template_path( 'layout/related_item.php' );
				do_action( 'abptf_related_item_template', $related_item );
			}
			public function slider( $img_infos = [], $params = [] ): void {
				if ( ! empty( $img_infos ) ) {
					$style        = $params['slider_style'] ?? '';
					$image_column = $params['column'] ?? '';
					$abptf_slider = ABPTF_Function::get_option( 'abptf_slider' );
					if ( ! empty( $image_column ) ) {
						$abptf_slider['image_column'] = $image_column;
						$abptf_slider['show_item']    = ( $params['show'] ?? null ) ?: $image_column * 3;
					}
					if ( ! empty( $style ) ) {
						$slider_style = $style == 'gallery' ? 'gallery' : 'slider';
					} else {
						$slider_style = ( $abptf_slider['slider_style'] ?? null ) ?: 'gallery';
					}
					include_once ABPTF_Function::template_path( 'layout/' . $slider_style . '.php' );
					do_action( 'abptf_' . $slider_style . '_template', $img_infos, $abptf_slider );
				}
			}
			public function slider_popup( $abptf_slider, $img_infos, $popup_id = '#abptf_slider_' ): void {
				include_once ABPTF_Function::template_path( 'layout/slider_popup.php' );
				do_action( 'abptf_slider_popup_template', $abptf_slider, $img_infos, $popup_id );
			}
		}
		new ABPTF_Hooks();
	}