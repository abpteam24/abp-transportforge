<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_slider_template', function ( $img_infos = [], $abptf_slider = [] ) {
		if ( empty( $img_infos ) || ! is_array( $img_infos ) ) {
			return;
		}
		$total_images        = count( $img_infos );
		$slider_indicator    = ( $abptf_slider['indicator_visible'] ?? 'on' ) ?: 'on';
		$indication_position = ( $abptf_slider['indication_position'] ?? 'bottom' ) ?: 'bottom';
		$indication_position = ( $slider_indicator === 'on' ) ? $indication_position : '';
		$popup_id            = uniqid( '#abptf_slider_' );
		$active_popup        = ( $abptf_slider['visible_popup'] ?? 'on' ) ?: 'on';
		?>
        <div class="abptf_slider">
            <div class="<?php echo esc_attr( $indication_position ); ?>" data-slider>
                <div class="slider_show">
                    <div class="_circle_icon slide_counter">
                        <span class="slide_current_num">1</span> / <span><?php echo esc_html( $total_images ); ?></span>
                    </div>
                    <img src="#" class="slide_resize" alt="" aria-hidden="true"/>
					<?php
						foreach ( $img_infos as $img_info ) {
							if ( ! is_array( $img_info ) ) {
								continue;
							}
							$id = $img_info['id'] ?? '';
							if ( empty( $id ) ) {
								continue;
							}
							$url       = ABPTF_Function::get_image_url( '', $id ) ?: ABPTF_BLANK_IMG_URL;
							$img_post  = $img_info['post'] ?? '';
							$img_label = $img_info['label'] ?? '';
							?>
                            <div class="slider_item" <?php if ( $active_popup === 'on' ) { ?>data-target-popup="<?php echo esc_attr( $popup_id ); ?>"<?php } ?> data-img="<?php echo esc_url( $url ); ?>">
                                <div class="slider_loading"></div>
                                <img src="#" alt="<?php echo esc_html( $img_post ); ?>"/>
                                <div class="item_caption">
                                    <div class="caption_label"><?php echo esc_html( $img_post ); ?></div>
                                    <div class="caption_title"><?php echo esc_html( $img_label ); ?></div>
                                </div>
                            </div>
						<?php } ?>
                </div>
                <div class="progress_bar">
                    <div class="progress_fill"></div>
                </div>
                <div class="icon_direction prev_item">
                    <span class="fas fa-chevron-left"></span>
                </div>
                <div class="icon_direction next_item">
                    <span class="fas fa-chevron-right"></span>
                </div>
				<?php if ( $slider_indicator === 'on' ) { ?>
                    <div class="image_indicator"></div>
				<?php } ?>
            </div>
        </div>
		<?php
		do_action( 'abptf_slider_popup', $abptf_slider, $img_infos, $popup_id );
	}, 10, 2 );