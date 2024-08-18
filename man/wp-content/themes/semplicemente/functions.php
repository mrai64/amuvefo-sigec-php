<?php
/**
 * semplicemente functions and definitions
 *
 * @package semplicemente
 */

if ( ! function_exists( 'semplicemente_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function semplicemente_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on semplicemente, use a find and replace
	 * to change 'semplicemente' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'semplicemente', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );
	
	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );
	
	add_theme_support( 'customize-selective-refresh-widgets' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'normal-post' , 720, 9999);

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'semplicemente' ),
	) );
	
	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style',
	) );

	// Setup the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'semplicemente_custom_background_args', array(
		'default-color' => 'f2f2f2',
		'default-image' => '',
	) ) );
	
	// Adds support for editor font sizes.
	add_theme_support( 'editor-font-sizes', array(
		array(
			'name'      => __( 'Small', 'semplicemente' ),
			'shortName' => __( 'S', 'semplicemente' ),
			'size'      => 12,
			'slug'      => 'small'
		),
		array(
			'name'      => __( 'Regular', 'semplicemente' ),
			'shortName' => __( 'M', 'semplicemente' ),
			'size'      => 14,
			'slug'      => 'regular'
		),
		array(
			'name'      => __( 'Large', 'semplicemente' ),
			'shortName' => __( 'L', 'semplicemente' ),
			'size'      => 18,
			'slug'      => 'large'
		),
		array(
			'name'      => __( 'Larger', 'semplicemente' ),
			'shortName' => __( 'XL', 'semplicemente' ),
			'size'      => 20,
			'slug'      => 'larger'
		)
	) );
}
endif; // semplicemente_setup
add_action( 'after_setup_theme', 'semplicemente_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function semplicemente_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'semplicemente_content_width', 670 );
}
add_action( 'after_setup_theme', 'semplicemente_content_width', 0 );

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function semplicemente_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'semplicemente' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<div class="widget-title"><h3>',
		'after_title'   => '</h3></div>',
	) );
}
add_action( 'widgets_init', 'semplicemente_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function semplicemente_scripts() {
	wp_enqueue_style( 'semplicemente-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version') );
	wp_enqueue_style( 'font-awesome', get_template_directory_uri() .'/css/font-awesome.min.css', array(), '4.7.0');
	$query_args = array(
		'family' => 'Source+Sans+Pro:wght@300;400;700',
		'display' => 'swap'
	);

	$googleFontsLocal = get_theme_mod('semplicemente_theme_options_googlefontslocal', '');
	if ($googleFontsLocal == 1) {
		require_once get_theme_file_path( 'inc/wptt-webfont-loader.php' );
		wp_enqueue_style( 'semplicemente-googlefonts', wptt_get_webfont_url(add_query_arg( $query_args, 'https://fonts.googleapis.com/css2' ) ), array(), null );
	} else {
		wp_enqueue_style( 'semplicemente-googlefonts', add_query_arg( $query_args, "//fonts.googleapis.com/css2" ), array(), null );
	}

	wp_enqueue_script( 'semplicemente-custom', get_template_directory_uri() . '/js/jquery.semplicemente.min.js', array('jquery'), wp_get_theme()->get('Version'), true );
	wp_enqueue_script( 'semplicemente-navigation', get_template_directory_uri() . '/js/navigation.min.js', array(), '20151215', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'semplicemente_scripts' );

function semplicemente_gutenberg_scripts() {
	wp_enqueue_style( 'semplicemente-gutenberg-css', get_theme_file_uri( '/css/gutenberg-editor-style.css' ), array(), wp_get_theme()->get('Version') );
}
add_action( 'enqueue_block_editor_assets', 'semplicemente_gutenberg_scripts' );

/**
 * Register all Elementor locations
 */
function semplicemente_register_elementor_locations( $elementor_theme_manager ) {
	$elementor_theme_manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'semplicemente_register_elementor_locations' );

/**
 * Replace more Excerpt
 */
if ( ! function_exists( 'semplicemente_new_excerpt_more' ) ) {
	function semplicemente_new_excerpt_more($more) {
		if ( is_admin() ) {
			return $more;
		}
		return '&hellip;';
	}
}
add_filter('excerpt_more', 'semplicemente_new_excerpt_more');

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Load Semplicemente Dynamic.
 */
require get_template_directory() . '/inc/semplicemente-dynamic.php';

/**
 * Load PRO Button in the customizer
 */
require get_template_directory() . '/inc/pro-button/class-customize.php';


/* Calling in the admin area for the Welcome Page */
if ( is_admin() ) {
	require get_template_directory() . '/inc/admin/semplicemente-admin-page.php';
}