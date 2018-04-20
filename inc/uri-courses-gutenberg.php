<?php
/**
 * Description: File to create a Gutenberg interface
 * Author: John Pennypacker <john@pennypacker.net>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register block for course list by subject
 */
function uri_courses_subject_block() {
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
}
add_action( 'init', 'uri_courses_subject_block' );


/**
 * Add styles for the gutenberg block
 */
function uri_courses_block_assets() {
	wp_enqueue_style( 'uri-courses-editor-styles', plugins_url( 'assets/gutenberg/subject/block.css', __FILE__ ) );
}
add_action( 'enqueue_block_assets', 'uri_courses_block_assets' );


