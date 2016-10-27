<?php
/**
 * Plugin Name: Trac #38114 Temp. Feature
 * Description: Make it easier to visualize where to put your content in a given theme (aka "starter content").
 * Plugin URL: https://core.trac.wordpress.org/ticket/38114
 */

/**
 * Add quick and dirty buttons to load starter content from panel header.
 *
 * @param WP_Customize_Manager $wp_customize
 */
function wp_trac_38144_customize_register( WP_Customize_Manager $wp_customize ) {
	add_action( 'customize_controls_enqueue_scripts', 'wp_trac_38144_customize_controls_enqueue_scripts' );
	add_action( 'customize_controls_print_footer_scripts', 'wp_trac_38144_print_templates' );
	add_action( 'wp_ajax_customize_load_starter_content', 'wp_trac_38144_ajax_customize_load_starter_content' );
}
add_action( 'customize_register', 'wp_trac_38144_customize_register' );

/**
 * Enqueue scripts.
 */
function wp_trac_38144_customize_controls_enqueue_scripts() {
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
function wp_trac_38144_print_templates() {
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
function wp_trac_38144_ajax_customize_load_starter_content() {
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

	$result = wp_trac_38144_customize_load_starter_content( $wp_customize );
	if ( is_wp_error( $result ) ) {
		wp_send_json_error( $result->get_error_code() );
	} else  {
		wp_send_json_success( $result );
	}
}

/**
 * Get starter content for a theme.
 *
 * @param string $stylesheet Theme to get starter content for.
 * @return array Starter content.
 */
function wp_trac_38144_get_theme_starter_content( $stylesheet = null ) {
	if ( ! $stylesheet ) {
		$stylesheet = get_stylesheet();
	}

	$starter_content = array();

	if ( 'twentyseventeen' === $stylesheet ) {
		$starter_content = array(
			'widgets' => array(
				'sidebar-1' => array(
					array( 'text', array(
						'title' => __( 'Find Us' ),
						'text' => join( '', array(
							'<p><strong>' . __( 'Address' ) . '</strong><br />',
							__( '123 Main Street' ) . '<br />' . __( 'New York, NY 10001' ) . '</p>',
							'<p><strong>' . __( 'Hours' ) . '</strong><br />',
							__( 'Monday&mdash;Friday: 9:00AM&ndash;5:00PM' ) . '<br />' . __( 'Saturday &amp; Sunday: 11:00AM&ndash;3:00PM' ) . '</p>'
						) ),
					) ),
					array( 'search', array(
						'title' => __( 'Site Search' ),
					) ),
					array( 'text', array(
						'title' => __( 'Site Credits' ),
						'text' => sprintf( __( 'This site was created on %s' ), get_date_from_gmt( current_time( 'mysql', 1 ), 'c' ) ),
					) ),
				),

				'sidebar-2' => array(
					array( 'text', array(
						'title' => __( 'Footer 1 text widget' ),
						'text' => __( 'Hello world' ),
					) ),
				),

				'sidebar-3' => array(
					array( 'text', array(
						'title' => __( 'Footer 2 text widget' ),
						'text' => __( 'Hello world' ),
					) ),
				),
			),

			'posts' => array(
				'home' => array(
					'post_type' => 'page',
					'post_title' => __( 'Homepage' ),
					'post_content' => __( 'Welcome home.' ),
				),
				'about-us' => array(
					'post_type' => 'page',
					'post_title' => __( 'About Us' ),
					'post_content' => __( 'More than you ever wanted to know.' ),
				),
				'contact-us' => array(
					'post_type' => 'page',
					'post_title' => __( 'Contact Us' ),
					'post_content' => __( 'Call us at 999-999-9999.' ),
				),
				'blog' => array(
					'post_type' => 'page',
					'post_title' => __( 'Blog' ),
				),

				'panel_1' => array(
					'post_type' => 'page',
					'post_title' => __( 'First' ),
					'post_content' => __( 'First panel content' ),
				),
				'panel_2' => array(
					'post_type' => 'page',
					'post_title' => __( 'Second' ),
					'post_content' => __( 'Second panel content' ),
				),
				'panel_3' => array(
					'post_type' => 'page',
					'post_title' => __( 'Third' ),
					'post_content' => __( 'Third panel content' ),
				),
				'panel_4' => array(
					'post_type' => 'page',
					'post_title' => __( 'Fourth' ),
					'post_content' => __( 'Fourth panel content' ),
				),
			),

			'options' => array(
				'show_on_front' => 'page',
				'page_on_front' => '{{home}}',
				'page_for_posts' => '{{blog}}',
			),

			'theme_mods' => array(
				'panel_1' => '{{panel_1}}',
				'panel_2' => '{{panel_2}}',
				'panel_3' => '{{panel_3}}',
				'panel_4' => '{{panel_4}}',
			),

			'nav_menus' => array(
				'top' => array(
					'name' => __( 'Top' ),
					'items' => array(
						array(
							'type' => 'post_type',
							'object' => 'page',
							'object_id' => 'home',
						),
						array(
							'type' => 'post_type',
							'object' => 'page',
							'object_id' => 'about-us',
						),
						array(
							'type' => 'post_type',
							'object' => 'page',
							'object_id' => 'blog',
						),
						array(
							'type' => 'post_type',
							'object' => 'page',
							'object_id' => 'contact-us',
						),
					),
				),
				'social' => array(
					'name' => __( 'Social' ),
					'items' => array(
						array(
							'title' => __( 'Yelp' ),
							'url' => 'https://www.yelp.com',
						),
						array(
							'title' => __( 'Facebook' ),
							'url' => 'https://www.facebook.com/wordpress',
						),
						array(
							'title' => __( 'Twitter' ),
							'url' => 'https://twitter.com/wordpress',
						),
						array(
							'title' => __( 'Instagram' ),
							'url' => 'https://www.instagram.com/explore/tags/wordcamp/',
						),
						array(
							'title' => __( 'Email' ),
							'url' => 'mailto:wordpress@example.com',
						),
					),
				),
			),
		);
	}

	$starter_content = apply_filters( 'customize_theme_starter_content', $starter_content, $stylesheet );

	return $starter_content;
}

/**
 * Load starter content into the changeset.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 * @return array|WP_Error Array on success and WP_Error on failure.
 */
function wp_trac_38144_customize_load_starter_content( WP_Customize_Manager $wp_customize ) {

	$starter_content = wp_trac_38144_get_theme_starter_content( $wp_customize->get_stylesheet() );

	$widgets = isset( $starter_content['widgets'] ) && ! empty( $wp_customize->widgets ) ? $starter_content['widgets'] : array();
	$posts = isset( $starter_content['posts'] ) ? $starter_content['posts'] : array();
	$options = isset( $starter_content['options'] ) ? $starter_content['options'] : array();
	$nav_menus = isset( $starter_content['nav_menus'] ) && ! empty( $wp_customize->nav_menus ) ? $starter_content['nav_menus'] : array();
	$theme_mods = isset( $starter_content['theme_mods'] ) ? $starter_content['theme_mods'] : array();

	// Widgets.
	$max_widget_numbers = array();
	foreach ( $widgets as $sidebar_id => $widgets ) {
		$sidebar_widget_ids = array();
		foreach ( $widgets as $widget ) {
			list( $id_base, $instance ) = $widget;

			if ( ! isset( $max_widget_numbers[ $id_base ] ) ) {

				// When $settings is an array-like object, get an intrinsic array for use with array_keys().
				$settings = get_option( "widget_{$id_base}", array() );
				if ( $settings instanceof ArrayObject || $settings instanceof ArrayIterator ) {
					$settings = $settings->getArrayCopy();
				}

				// Find the max widget number for this type.
				$max_widget_numbers[ $id_base ] = call_user_func_array( 'max',
					array_merge( array( 1 ), array_keys( $settings ) )
				);
			}
			$max_widget_numbers[ $id_base ] += 1;

			$widget_id = sprintf( '%s-%d', $id_base, $max_widget_numbers[ $id_base ] );
			$setting_id = sprintf( 'widget_%s[%d]', $id_base, $max_widget_numbers[ $id_base ] );

			$wp_customize->add_dynamic_settings( array( $setting_id ) );
			$setting = $wp_customize->get_setting( $setting_id );
			if ( $setting ) {
				$setting_value = call_user_func( $setting->sanitize_js_callback, $instance, $setting );
				$wp_customize->set_post_value( $setting_id, $setting_value );
				$sidebar_widget_ids[] = $widget_id;
			}
		}

		$wp_customize->set_post_value( sprintf( 'sidebars_widgets[%s]', $sidebar_id ), $sidebar_widget_ids );
	}

	// Posts & pages.
	foreach ( array_keys( $posts ) as $post_symbol ) {
		$posts[ $post_symbol ]['ID'] = wp_insert_post( wp_slash( array_merge(
			$posts[ $post_symbol ],
			array( 'post_status' => 'auto-draft' )
		) ) );
	}
	$wp_customize->set_post_value( 'nav_menus_created_posts', wp_list_pluck( $posts, 'ID' ) );

	// Nav menus.
	$placeholder_id = -1;
	foreach ( $nav_menus as $nav_menu_location => $nav_menu ) {
		$nav_menu_term_id = $placeholder_id--;
		$nav_menu_setting_id = sprintf( 'nav_menu[%d]', $nav_menu_term_id );
		$wp_customize->set_post_value( $nav_menu_setting_id, array(
			'name' => isset( $nav_menu['name'] ) ? $nav_menu['name'] : $nav_menu_location
		) );

		// @todo Add support for menu_item_parent.
		$position = 0;
		foreach ( $nav_menu['items'] as $nav_menu_item ) {
			$nav_menu_item_setting_id = sprintf( 'nav_menu_item[%d]', $placeholder_id-- );
			if ( ! isset( $nav_menu_item['position'] ) ) {
				$nav_menu_item['position'] = $position++;
			}
			$nav_menu_item['nav_menu_term_id'] = $nav_menu_term_id;

			if ( isset( $nav_menu_item['object_id'] ) ) {
				if ( 'post_type' === $nav_menu_item['type'] && isset( $posts[ $nav_menu_item['object_id'] ] ) ) {
					$nav_menu_item['object_id'] = $posts[ $nav_menu_item['object_id'] ]['ID'];
				} else {
					continue;
				}

				// @todo This needs to be part of WP_Customize_Nav_Menu_Item_Setting::value_as_wp_post_nav_menu_item().
				$nav_menu_item['url'] = get_permalink( $nav_menu_item['object_id'] );
			} else {
				$nav_menu_item['object_id'] = 0;
			}
			$wp_customize->set_post_value( $nav_menu_item_setting_id, $nav_menu_item );
		}

		$wp_customize->set_post_value( sprintf( 'nav_menu_locations[%s]', $nav_menu_location ), $nav_menu_term_id );
	}

	// Options.
	foreach ( $options as $name => $value ) {
		if ( preg_match( '/^{{(?P<symbol>.+)}}$/', $value, $matches ) && isset( $posts[ $matches['symbol'] ] ) ) {
			$value = $posts[ $matches['symbol'] ]['ID'];
		}
		$wp_customize->set_post_value( $name, $value );
	}

	// Theme mods.
	foreach ( $theme_mods as $name => $value ) {
		if ( preg_match( '/^{{(?P<symbol>.+)}}$/', $value, $matches ) && isset( $posts[ $matches['symbol'] ] ) ) {
			$value = $posts[ $matches['symbol'] ]['ID'];
		}
		$wp_customize->set_post_value( $name, $value );
	}

	return $wp_customize->save_changeset_post();
}
