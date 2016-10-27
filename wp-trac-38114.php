<?php
/**
 * Plugin Name: Trac #38114 Temp. Feature
 * Description: Make it easier to visualize where to put your content in a given theme (aka "dummy content").
 * Plugin URL: https://core.trac.wordpress.org/ticket/38114
 */

/**
 * Add quick and dirty buttons to load dummy content from panel header.
 *
 * @param WP_Customize_Manager $wp_customize
 */
function wp_trac_38144_customize_register( WP_Customize_Manager $wp_customize ) {
	if ( $wp_customize->is_theme_active() ) {
		return;
	}
	add_action( 'customize_controls_enqueue_scripts', 'wp_trac_38144_customize_controls_enqueue_scripts' );
	add_action( 'customize_controls_print_footer_scripts', 'wp_trac_38144_print_templates' );
	add_action( 'wp_ajax_customize_load_dummy_content', 'wp_trac_38144_ajax_customize_load_dummy_content' );

	wp_trac_38144_insert_dummy_posts( $wp_customize );
}
add_action( 'customize_register', 'wp_trac_38144_customize_register' );

/**
 * Enqueue scripts.
 */
function wp_trac_38144_customize_controls_enqueue_scripts() {
	$handle = 'customize-dummy-content';
	$src = plugin_dir_url( __FILE__ ) . 'customize-dummy-content.js';
	$deps = array( 'customize-controls' );
	wp_enqueue_script( $handle, $src, $deps );

	$handle = 'customize-dummy-content';
	$src = plugin_dir_url( __FILE__ ) . 'customize-dummy-content.css';
	$deps = array( 'customize-controls' );
	wp_enqueue_style( $handle, $src, $deps );
}

/**
 * Print templates.
 */
function wp_trac_38144_print_templates() {
	?>
	<script type="text/html" id="tmpl-customize-dummy-content-actions">
		<div class="theme-dummy-content-actions">
			<!-- @todo Add a button for each set of sample data? -->
			<button type="button" class="button button-secondary"><?php _e( 'Load Dummy Content' ) ?></button>
		</div>
	</script>
	<?php
}

/**
 * Handle ajax request for loading dummy content.
 */
function wp_trac_38144_ajax_customize_load_dummy_content() {
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

	$result = wp_trac_38144_customize_load_dummy_content( $wp_customize );
	if ( is_wp_error( $result ) ) {
		wp_send_json_error( $result->get_error_code() );
	} else  {
		wp_send_json_success( $result );
	}
}

/**
 * Load dummy content into the changeset.
 *
 * @todo Allow passing a specific set of dummy content to load?
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 * @return array|WP_Error Array on success and WP_Error on failure.
 */
function wp_trac_38144_customize_load_dummy_content( WP_Customize_Manager $wp_customize ) {
	$wp_customize->set_post_value( 'blogname', current_time( 'mysql' ) );

	// @todo The main logic for loading dummy content goes here.

	return $wp_customize->save_changeset_post();
}

/**
 * Insert dummy auto_draft posts.
 *
 * @todo Move $dummy_post_titles into a separate file or getter function.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 * @return array Newly inserted post IDs.
 */
function wp_trac_38144_insert_dummy_posts( $wp_customize ) {

	$dummy_post_titles = array(
		__( 'This Is A Blog Post' ),
		__( 'Another Example Post Title' ),
		__( 'A Placeholder Post' ),
		__( 'Another Placeholder Post' ),
	);

	$new_post_ids = array();
	foreach ( $dummy_post_titles as $post_title ) {
		$new_post = $wp_customize->nav_menus->insert_auto_draft_post( array(
			'post_type' => 'post',
			'post_title' => $post_title,
		) );
		if ( isset( $new_post->ID ) ) {
			array_push( $new_post_ids, $new_post->ID );
		}
	}

	$existing_post_setting = $wp_customize->get_setting( 'nav_menus_created_posts' );
	if ( ( null !== $existing_post_setting ) && is_array( $existing_post_setting->post_value() ) ) {
		$existing_post_ids = $existing_post_setting->post_value();
		$merged_post_ids = array_merge( $new_post_ids, $existing_post_ids );
	} else {
		$merged_post_ids = $new_post_ids;
	}
	$wp_customize->set_post_value( 'nav_menus_created_posts', $merged_post_ids );

	return $new_post_ids;

}
