<?php
if ( ! function_exists( 'materialadmin_setup' ) ) :
function materialadmin_setup() {

	load_theme_textdomain( 'materialadmin' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	add_theme_support( 'title-tag' );

	add_theme_support( 'custom-logo', array(
		'height'      => 240,
		'width'       => 240,
		'flex-height' => true,
	) );

	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1200, 9999 );

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'materialadmin' ),
		'social'  => __( 'Social Links Menu', 'materialadmin' ),
	) );

	
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	
	add_theme_support( 'post-formats', array(
		'aside',
		'image',
		'video',
		'quote',
		'link',
		'gallery',
		'status',
		'audio',
		'chat',
	) );

}
endif; // twentysixteen_setup
add_action( 'after_setup_theme', 'materialadmin_setup' );

function materialadmin_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'materialadmin_content_width', 840 );
}
add_action( 'after_setup_theme', 'materialadmin_content_width', 0 );

function materialadmin_javascript_detection() {
	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action( 'wp_head', 'materialadmin_javascript_detection', 0 );


add_action( 'wp_enqueue_scripts', 'materialadmin_scripts' );
function materialadmin_scripts() {
	wp_enqueue_script('jquery-script', get_template_directory_uri() . '/js/libs/jquery/jquery-1.11.2.min.js');
	wp_enqueue_script( 'migrate-script', get_template_directory_uri() . '/js/libs/jquery/jquery-migrate-1.2.1.min.js','');
	wp_enqueue_script('jqueryui-script', get_template_directory_uri() . '/js/libs/jquery-ui/jquery-ui.min.js');
	wp_enqueue_script( 'bootstrap-script', get_template_directory_uri() . '/js/libs/bootstrap/bootstrap.min.js','');
	wp_enqueue_script( 'app-script', get_template_directory_uri() . '/js/core/source/App.js','');
	wp_enqueue_script( 'appnav-script', get_template_directory_uri() . '/js/core/source/AppNavigation.js','');
	wp_enqueue_script( 'appform-script', get_template_directory_uri() . '/js/core/source/AppForm.js','');
	wp_enqueue_script( 'appcard-script', get_template_directory_uri() . '/js/core/source/AppCard.js','');
    wp_enqueue_script( 'validate-script', get_template_directory_uri() . '/js/libs/jquery-validation/dist/jquery.validate.js','');
    wp_enqueue_script( 'autosize', get_template_directory_uri() . '/js/libs/autosize/jquery.autosize.min.js','');
	wp_enqueue_script( 'nano', get_template_directory_uri() . '/js/libs/nanoscroller/jquery.nanoscroller.min.js','');
    
}

?>