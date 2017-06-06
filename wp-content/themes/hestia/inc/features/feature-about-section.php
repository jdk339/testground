<?php
/**
 * Customizer functionality for the About section.
 *
 * @package Hestia
 * @since Hestia 1.0
 */

// Register customizer page editor functions
$page_editor_path = trailingslashit( get_template_directory() ) . 'inc/customizer-page-editor/customizer-page-editor.php';
if ( file_exists( $page_editor_path ) ) {
	require_once( $page_editor_path );
}

/**
 * Hook controls for About section to Customizer.
 *
 * @since Hestia 1.0
 */
function hestia_about_customize_register( $wp_customize ) {

	$wp_customize->add_section( 'hestia_about', array(
		'title'    => esc_html__( 'About', 'hestia' ),
		'panel'    => 'hestia_frontpage_sections',
		'priority' => apply_filters( 'hestia_section_priority', 15, 'hestia_about' ),
	) );

	$wp_customize->add_setting( 'hestia_about_hide', array(
		'sanitize_callback' => 'hestia_sanitize_checkbox',
		'default'           => false,
	) );

	$wp_customize->add_control( 'hestia_about_hide', array(
		'type'     => 'checkbox',
		'label'    => esc_html__( 'Disable section', 'hestia' ),
		'section'  => 'hestia_about',
		'priority' => 1,
	) );

	$frontpage_id = get_option( 'page_on_front' );
	$default      = '';
	if ( ! empty( $frontpage_id ) ) {
		$default = get_post_field( 'post_content', $frontpage_id );
	}
	$wp_customize->add_setting( 'hestia_page_editor', array(
		'default'           => $default,
		'sanitize_callback' => 'wp_kses_post',
	) );

	$wp_customize->add_control( new Hestia_Page_Editor( $wp_customize, 'hestia_page_editor', array(
		'label'                      => esc_html__( 'About Content', 'hestia' ),
		'section'                    => 'hestia_about',
		'priority'                   => 10,
		'needsync'                   => true,
		'include_admin_print_footer' => true,
	) ) );

	$default = '';
	if ( ! empty( $frontpage_id ) ) {
		if ( has_post_thumbnail( $frontpage_id ) ) {
			$default = get_the_post_thumbnail_url( $frontpage_id );
		}
	}
	$wp_customize->add_setting( 'hestia_feature_thumbnail', array(
		'sanitize_callback' => 'esc_url_raw',
		'default'           => $default,
	) );

	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'hestia_feature_thumbnail', array(
		'label'           => esc_html__( 'About background', 'hestia' ),
		'section'         => 'hestia_about',
		'priority'        => 15,
		'active_callback' => 'hestia_is_static_page',
	) ) );
}

add_action( 'customize_register', 'hestia_about_customize_register' );

/**
 * Render callback for about section content selective refresh
 *
 * @return mixed
 */
function hestia_about_content_selective_refresh_render_callback() {
	return get_theme_mod( 'hestia_about_content' );
}

/**
 * Page editor control active callback function
 *
 * @return bool
 */
function hestia_is_static_page() {
	return 'page' === get_option( 'show_on_front' );
}
