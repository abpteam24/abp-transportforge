<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( wp_is_block_theme() ) { ?>
        <!DOCTYPE html>
        <html  lang="" <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo( 'charset' ); ?>">
            <title></title>
			<?php
				do_blocks( '<div class="wp-block-group"></div>' );
				wp_head();
			?>
        </head>
        <body <?php body_class(); ?>>
		<?php wp_body_open(); ?>
        <div class="wp-site-blocks">
            <header class="wp-block-template-part site-header">
				<?php block_header_area(); ?>
            </header>
        </div>
		<?php
	} else {
		get_header();
		the_post();
	}
	//do_action( 'woocommerce_before_single_product' );
    do_action( 'abptf_load_details_template' ,get_the_id() );
	if ( wp_is_block_theme() ) {
		?>
        <footer class="wp-block-template-part">
			<?php block_footer_area(); ?>
        </footer>
		<?php wp_footer(); ?>
        </body>
		<?php
	} else {
		get_footer();
	}