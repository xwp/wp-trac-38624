<?php
/**
 * Plugin Name: Load Starter Content in Customizer (Trac #38624)
 * Description: Allow starter content to apply after a site has already been set up and is no longer "fresh".
 * Plugin URL: https://core.trac.wordpress.org/ticket/38624
 */

/**
 * Add quick and dirty buttons to load starter content from panel header.
 */
function wp_trac_38624_customize_register() {
	if ( ! get_theme_starter_content() ) {
		return;
	}
	add_action( 'customize_controls_enqueue_scripts', 'wp_trac_38624_customize_controls_enqueue_scripts' );
	add_action( 'customize_controls_print_footer_scripts', 'wp_trac_38624_print_templates' );
	add_action( 'wp_ajax_customize_load_starter_content', 'wp_trac_38624_ajax_customize_load_starter_content' );
}
add_action( 'customize_register', 'wp_trac_38624_customize_register' );

/**
 * Enqueue scripts.
 */
function wp_trac_38624_customize_controls_enqueue_scripts() {
	$handle = 'customize-starter-content';
	$src = plugin_dir_url( __FILE__ ) . 'customize-starter-content.js';
	$deps = array( 'customize-controls' );
	wp_enqueue_script( $handle, $src, $deps );

	$handle = 'customize-starter-content';
	$src = plugin_dir_url( __FILE__ ) . 'customize-starter-content.css';
	$deps = array( 'customize-controls' );
	wp_enqueue_style( $handle, $src, $deps );
}

/**
 * Print templates.
 */
function wp_trac_38624_print_templates() {
	?>
	<script type="text/html" id="tmpl-customize-starter-content-actions">
		<div class="theme-starter-content-actions">
			<!-- @todo Add a button for each set of sample data? -->
			<button type="button" class="button button-secondary"><?php _e( 'Load Starter Content' ) ?></button>
		</div>
	</script>
	<?php
}

/**
 * Handle ajax request for loading starter content.
 */
function wp_trac_38624_ajax_customize_load_starter_content() {
	global $wp_customize;
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( 'unauthenticated' );
	}
	if ( empty( $wp_customize ) || ! $wp_customize->is_preview() ) {
		wp_send_json_error( 'not_preview' );
	}
	$action = 'preview-customize_' . $wp_customize->get_stylesheet();
	if ( ! check_ajax_referer( $action, 'nonce', false ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}

	$starter_content_applied = 0;
	$wp_customize->import_theme_starter_content();
	foreach ( $wp_customize->changeset_data() as $setting_id => $setting_params ) {
		if ( ! empty( $setting_params['starter_content'] ) ) {
			$starter_content_applied += 1;
		}
	}

	if ( 0 === $starter_content_applied ) {
		wp_send_json_error( 'no_starter_content' );
	} else {
		wp_send_json_success();
	}
}
