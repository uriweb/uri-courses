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
		URI_COURSES_PATH . 'assets/gutenberg/subject/block.js',
		array( 'wp-blocks', 'wp-element' ),
		strtotime('now') // cache buster
	);

	register_block_type(
		'uri-courses/by-subject',
		array( 'editor_script' => 'uri-courses-by-subject', )
	);
}
add_action( 'init', 'uri_courses_subject_block' );