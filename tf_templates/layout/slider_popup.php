<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abptf_slider_popup_template', function ( $abptf_slider, $img_infos, $popup_id = '' ) {
		$active_popup = ( $abptf_slider['visible_popup'] ?? 'on' ) ?: 'on';
		$indicator    = ( $abptf_slider['popup_image_indicator'] ?? 'on' ) ?: 'on';
		if ( $active_popup !== 'on' || empty( $img_infos ) || ! is_array( $img_infos ) ) {
			return;
		}
		$total_images = count( $img_infos );
		?>
        <div class="abptf_popup" data-popup="<?php echo esc_attr( $popup_id ); ?>">
            <div class="popup_area abptf_slider">
                <span class="popup_close"><i class="fas fa-times"></i></span>
                <div data-slider>
                    <div class="popup_body">
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
                                    <div class="slider_item" data-img="<?php echo esc_url( $url ); ?>">
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
                    </div>
					<?php if ( $indicator === 'on' ) { ?>
                        <div class="popup_foot">
                            <div class="image_indicator"></div>
                        </div>
					<?php } ?>
                </div>
            </div>
        </div>
		<?php
	}, 10, 3 );