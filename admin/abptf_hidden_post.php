<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if direct access
	}
	if ( ! class_exists( 'ABPTF_Hidden_Post' ) ) {
		class ABPTF_Hidden_Post {
			public function __construct() {
				add_action( 'wp_insert_post', [ $this, 'insert_wc_hidden_post' ], 10, 3 );
				add_action( 'save_post', [ $this, 'save_hidden_post' ], 99 );
				add_action( 'parse_query', [ $this, 'hide_hidden_post' ] );
				add_action( 'wp', [ $this, 'hide_hidden_post_frontend' ] );
				add_action( 'wp_head', [ $this, 'exclude_url_from_search_engine' ] );
				add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', [ $this, 'get_all_hidden_product_id' ] );
			}
			public function insert_wc_hidden_post( $post_id, $post ): void {
				$cpt = ABPTF_Function::get_cpt();
				if ( $post->post_type === $cpt && $post->post_status === 'publish' ) {
					if ( empty( ABPTF_Function::get_post_info( $post_id, 'exit_wc_hidden_post' ) ) ) {
						$this->create_wc_hidden_post( $post_id, $post->post_title );
					}
				}
			}
			public function save_hidden_post( $post_id ): void {
				if ( get_post_type( $post_id ) !== ABPTF_Function::get_cpt() ) {
					return;
				}
				if ( ! isset( $_POST['abptf_post_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['abptf_post_nonce'] ) ), 'abptf_post_nonce' ) ) {
					return;
				}
				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return;
				}
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
				$title = get_the_title( $post_id );
				if ( $this->count_hidden_post( $post_id ) === 0 || empty( ABPTF_Function::get_post_info( $post_id, 'link_wc_id' ) ) ) {
					$this->create_wc_hidden_post( $post_id, $title );
				}
				$product_id = ABPTF_Function::get_post_info( $post_id, 'link_wc_id', $post_id );
				if ( ! $product_id ) {
					return;
				}
				$post_val     = fn( $key, $default = '' ) => isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
				$thumbnail_id = get_post_thumbnail_id( $post_id );
				if ( $thumbnail_id ) {
					set_post_thumbnail( $product_id, $thumbnail_id );
				}
				wp_publish_post( $product_id );
				update_post_meta( $product_id, '_tax_status', $post_val( '_tax_status', 'none' ) );
				update_post_meta( $product_id, '_tax_class', $post_val( '_tax_class' ) );
				update_post_meta( $product_id, '_stock_status', 'instock' );
				update_post_meta( $product_id, '_manage_stock', 'no' );
				update_post_meta( $product_id, '_virtual', 'yes' );
				update_post_meta( $product_id, '_sold_individually', 'yes' );
				remove_action( 'save_post', [ $this, 'save_hidden_post' ], 99 );
				wp_update_post( [
					'ID' => $product_id,
					'post_title' => $title,
					'post_name' => uniqid( 'prod_', false ),
				] );
				add_action( 'save_post', [ $this, 'save_hidden_post' ], 99 );
			}
			public function hide_hidden_post( $query ): void {
				if ( ! is_admin() ) {
					return;
				}
				global $pagenow;
				$q_vars = &$query->query_vars;
				if ( $pagenow === 'edit.php' && ( $q_vars['post_type'] ?? '' ) === 'product' ) {
					$tax_query   = ( $query->get( 'tax_query' ) ?? null ) ?: [];
					$tax_query[] = [
						'taxonomy' => 'product_visibility',
						'field' => 'slug',
						'terms' => 'exclude-from-catalog',
						'operator' => 'NOT IN',
					];
					$query->set( 'tax_query', $tax_query );
				}
			}
			public function hide_hidden_post_frontend(): void {
				if ( is_admin() || ! is_product() ) {
					return;
				}
				global $post, $wp_query;
				if ( ! $post ) {
					return;
				}
				$visibility = get_the_terms( $post->ID, 'product_visibility' );
				if ( is_array( $visibility ) && ! empty( $visibility ) ) {
					if ( $visibility[0]->slug === 'exclude-from-catalog' || $visibility[0]->name === 'exclude-from-catalog' ) {
						$check_event_hidden = (int) ABPTF_Function::get_post_info( $post->ID, 'abptf_link_id', 0 );
						if ( $check_event_hidden > 0 ) {
							$wp_query->set_404();
							status_header( 404 );
							include( get_query_template( '404' ) );
							exit();
						}
					}
				}
			}
			public function create_wc_hidden_post( $post_id, $title ): void {
				if ( ! class_exists( 'WC_Product_Simple' ) ) {
					return;
				}
				$product = new WC_Product_Simple();
				$product->set_name( $title );
				$product->set_status( 'publish' );
				$product->set_slug( uniqid( 'prod_', false ) );
				$product->set_regular_price( 0.01 );
				$product->set_price( 0.01 );
				$product->set_sold_individually( true );
				$product->set_virtual( true );
				$product->set_catalog_visibility( 'hidden' );
				$product->update_meta_data( 'abptf_link_id', $post_id );
				$pid = $product->save();
				if ( $pid ) {
					wp_set_object_terms( $pid, (int) $post_id, 'abptf_link_taxonomy' );
					if ( get_post_meta( $post_id, 'link_wc_id', true ) !== (string) $pid ) {
						update_post_meta( $post_id, 'link_wc_id', $pid );
					}
					update_post_meta( $post_id, 'exit_wc_hidden_post', true );
					wp_cache_delete( 'abptf_count_hidden_post_' . $post_id, 'abptf_counts' );
					delete_transient( 'abptf_count_hidden_post_' . $post_id );
				}
			}
			public function count_hidden_post( $post_id ): int {
				$cache_group = 'abptf_counts';
				$cache_key   = 'abptf_count_hidden_post_' . $post_id;
				$count       = wp_cache_get( $cache_key, $cache_group );
				if ( false === $count ) {
					$count = get_transient( $cache_key );
					if ( false === $count ) {
						global $wpdb;
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
						$count = (int) $wpdb->get_var( $wpdb->prepare(
							"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'abptf_link_id' AND meta_value = %s",
							(string) $post_id
						) );
						set_transient( $cache_key, $count, 0 );
					}
					wp_cache_set( $cache_key, $count, $cache_group );
				}

				return (int) $count;
			}
			public function exclude_url_from_search_engine(): void {
				if ( ! is_single() || ! is_product() ) {
					return;
				}
				global $post;
				if ( ! $post ) {
					return;
				}
				$visibility = get_the_terms( $post->ID, 'product_visibility' );
				$visibility = is_array( $visibility ) ? $visibility : [];
				if ( ! empty( $visibility ) && is_object( $visibility[0] ) ) {
					if ( $visibility[0]->slug === 'exclude-from-catalog' || $visibility[0]->name === 'exclude-from-catalog' ) {
						$check_hidden = (int) ABPTF_Function::get_post_info( $post->ID, 'abptf_link_id', 0 );
						if ( $check_hidden > 0 ) {
							echo '<meta name="robots" content="noindex, nofollow">' . "\n";
						}
					}
				}
			}
			public function get_all_hidden_product_id(): array {
				$product_ids = [];
				$query       = ABPTF_Query::query_post_type( ABPTF_Function::get_cpt() );
				if ( isset( $query->posts ) && is_array( $query->posts ) ) {
					foreach ( $query->posts as $result ) {
						$wc_id = ABPTF_Function::get_post_info( $result->ID, 'link_wc_id' );
						if ( $wc_id ) {
							$product_ids[] = (int) $wc_id;
						}
					}
				}

				return array_filter( $product_ids );
			}
		}
		new ABPTF_Hidden_Post();
	}