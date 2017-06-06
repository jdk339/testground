<?php
add_action( 'after_setup_theme', 'hometard_setup' );

if ( ! function_exists( 'hometard_setup' ) ) :

	/**
	 * Global functions
	 */
	function hometard_setup() {

		// Theme lang.
		load_theme_textdomain( 'hometard', get_template_directory() . '/languages' );

		// Add Title Tag Support.
		add_theme_support( 'title-tag' );

		// Register Menus.
		register_nav_menus(
			array(
				'main_menu' => esc_html__( 'Main Menu', 'hometard' ),
			)
		);

		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 300, 300, true );
		add_image_size( 'hometard-single', 1170, 460, true );

		// Add Custom Background Support.
		$args = array(
			'default-color' => 'ffffff',
		);
		add_theme_support( 'custom-background', $args );

		add_theme_support( 'custom-logo', array(
			'height'		 => 60,
			'width'			 => 200,
			'flex-height'	 => true,
			'flex-width'	 => true,
			'header-text'	 => array( 'site-title', 'site-description' ),
		) );

		// Adds RSS feed links to for posts and comments.
		add_theme_support( 'automatic-feed-links' );
		
		// WooCommerce support.
		add_theme_support( 'woocommerce' );
		
		// Recommend plugins.
        add_theme_support( 'recommend-plugins', array(
			'kingcomposer' => array(
                'name' => esc_html__( 'KingComposer', 'hometard' ),
                'active_filename' => 'kingcomposer/kingcomposer.php',
            ),
        ) );
	}

endif;

/**
 * Set Content Width
 */
function hometard_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'hometard_content_width', 1170 );
}

add_action( 'after_setup_theme', 'hometard_content_width', 0 );

/**
 * Register custom fonts.
 */
function hometard_fonts_url() {
	$fonts_url = '';

	/**
	 * Translators: If there are characters in your language that are not
	 * supported by Libre Franklin, translate this to 'off'. Do not translate
	 * into your own language.
	 */
	$libre_franklin = _x( 'on', 'Roboto Condensed font: on or off', 'hometard' );

	if ( 'off' !== $libre_franklin ) {
		$font_families = array();

		$font_families[] = 'Roboto Condensed:300,300i,400,400i,600,600i,800,800i';

		$query_args = array(
			'family' => urlencode( implode( '|', $font_families ) ),
			'subset' => urlencode( 'latin,latin-ext' ),
		);

		$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
	}

	return esc_url_raw( $fonts_url );
}

/**
 * Enqueue Styles (normal style.css and bootstrap.css)
 */
function hometard_theme_stylesheets() {
	// Add custom fonts, used in the main stylesheet.
	wp_enqueue_style( 'hometard-fonts', hometard_fonts_url(), array(), null );
	wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/css/bootstrap.css', array(), '3.3.7' );
	// Theme stylesheet.
	wp_enqueue_style( 'hometard-stylesheet', get_stylesheet_uri() );
}

add_action( 'wp_enqueue_scripts', 'hometard_theme_stylesheets' );

/**
 * Register Bootstrap JS with jquery
 */
function hometard_theme_js() {
	wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array( 'jquery' ), '3.3.7', true );
	wp_enqueue_script( 'hometard-theme-js', get_template_directory_uri() . '/js/customscript.js', array( 'jquery' ), '1.0.0', true );
}

add_action( 'wp_enqueue_scripts', 'hometard_theme_js' );


/**
 * Register Custom Navigation Walker include custom menu widget to use walkerclass
 */
require_once( trailingslashit( get_template_directory() ) . 'lib/wp_bootstrap_navwalker.php' );


add_action( 'widgets_init', 'hometard_widgets_init' );

/**
 * Register the Sidebar(s)
 */
function hometard_widgets_init() {
	register_sidebar(
		array(
			'name'			 => esc_html__( 'Right Sidebar', 'hometard' ),
			'id'			 => 'hometard-right-sidebar',
			'before_widget'	 => '<div id="%1$s" class="widget %2$s">',
			'after_widget'	 => '</div>',
			'before_title'	 => '<h3 class="widget-title">',
			'after_title'	 => '</h3>',
		)
	);
}

function hometard_main_content_width_columns() {

	$columns = '12';

	if ( is_active_sidebar( 'hometard-right-sidebar' ) ) {
		$columns = $columns - 3;
	}

	echo absint( $columns );
}

if ( ! function_exists( 'hometard_posted_on' ) ) :

	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function hometard_posted_on() {

		// Get the author name; wrap it in a link.
		$byline = sprintf(
			/* translators: %s: post author */
			__( 'by %s', 'hometard' ), '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . get_the_author() . '</a></span>'
		);

		// Finally, let's write all of this to the page.
		echo '<span class="posted-on">' . hometard_time_link() . '</span><span class="byline"> ' . $byline . '</span>';
	}

endif;


if ( ! function_exists( 'hometard_time_link' ) ) :

	/**
	 * Gets a nicely formatted string for the published date.
	 */
	function hometard_time_link() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf( $time_string, get_the_date( DATE_W3C ), get_the_date(), get_the_modified_date( DATE_W3C ), get_the_modified_date()
		);

		// Wrap the time string in a link, and preface it with 'Posted on'.
		return sprintf(
			/* translators: %s: post date */
			__( 'Posted on %s', 'hometard' ), '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);
	}

endif;

if ( ! function_exists( 'hometard_entry_footer' ) ) :

	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function hometard_entry_footer() {

		/* translators: used between list items, there is a space after the comma */
		$separate_meta = __( ', ', 'hometard' );

		// Get Categories for posts.
		$categories_list = get_the_category_list( $separate_meta );

		// Get Tags for posts.
		$tags_list = get_the_tag_list( '', $separate_meta );

		// We don't want to output .entry-footer if it will be empty, so make sure its not.
		if ( $categories_list || $tags_list ) {

			echo '<div class="entry-footer">';

			if ( 'post' === get_post_type() ) {
				if ( $categories_list || $tags_list ) {

					// Make sure there's more than one category before displaying.
					if ( $categories_list ) {
						echo '<div class="cat-links"><span class="space-right">' . __( 'Category:', 'hometard' ) . '</span>' . $categories_list . '</div>';
					}

					if ( $tags_list ) {
						echo '<div class="tags-links"><span class="space-right">' . __( 'Tagged', 'hometard' ) . '</span>' . $tags_list . '</div>';
					}
				}
			}
			if ( comments_open() ) :
				echo '<div class="comments-template">';
				comments_popup_link( esc_html__( 'Leave a Comment', 'hometard' ), esc_html__( 'One Comment', 'hometard' ), esc_html__( '% Comments', 'hometard' ), 'comments-link', esc_html__( 'Comments are off for this post', 'hometard' ) );
				echo '</div>';
			endif;

			edit_post_link();

			echo '</div>';
		}
	}

endif;

/**
 * Add theme info page
 */
require_once( trailingslashit( get_template_directory() ) . 'lib/dashboard.php' );

/**
 * Add pro link
 */
require_once( trailingslashit( get_template_directory() ) . 'lib/customize-pro/class-customize.php' );