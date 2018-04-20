<?php
/**
 * Description: File to create a Gutenberg interface
 * Author: John Pennypacker <john@pennypacker.net>
 */

defined( 'ABSPATH' ) || exit;


/**
 * Register scripts and styles for the gutenberg block
 */
function uri_courses_block_assets() {
	wp_register_script(
		'uri-courses-by-subject',
		URI_COURSES_URL . 'assets/gutenberg/subject/block.js',
		array( 'wp-blocks', 'wp-element' ),
		1 // cache buster
	);

	register_block_type(
		'uri-courses/by-subject',
		array( 'editor_script' => 'uri-courses-by-subject', )
	);
	
	wp_enqueue_style( 'uri-courses-editor-styles', URI_COURSES_PATH . 'assets/gutenberg/subject/block.css' );
}
add_action( 'enqueue_block_assets', 'uri_courses_block_assets' );


