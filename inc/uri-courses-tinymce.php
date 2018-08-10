<?php
/**
 * Description: File to create a button in the WordPress TinyMCE interface
 * Author: John Pennypacker <john@pennypacker.net>
 */

defined( 'ABSPATH' ) || exit;

/**
 *
 */
function uri_courses_register_tinymce_plugin( $plugin_array ) {
	// load up the noneditable plugin from TinyMCE
	// $plugin_array['noneditable'] = plugins_url( '/js/noneditable/plugin.min.js', __FILE__ );

	// load the courses plugin
	if ( _uri_courses_add_scripts() ) { 
		$plugin_array['uri_courses_button'] = URI_COURSES_URL . 'assets/tinymce/uri-courses-plugin.js';
	}
	return $plugin_array;
}
// Load the TinyMCE plugin
add_filter( 'mce_external_plugins', 'uri_courses_register_tinymce_plugin' );


/**
 *
 */
function uri_courses_register_buttons( $buttons ) {
	if ( _uri_courses_add_scripts() ) { 
		array_push( $buttons, 'uri_courses_button' );
	}
	return $buttons;
}
// add new buttons
add_filter( 'mce_buttons_3', 'uri_courses_register_buttons' );


/**
 * Enqueue a script in the WordPress admin
 * @param str $hook Hook suffix for the current admin page.
 */
function uri_courses_add_scripts( $hook ) {
	
	if ( _uri_courses_add_scripts() ) { 
	  //wp_enqueue_style('uri-courses-admin-styles', URI_COURSES_URL . 'assets/tinymce/uri-courses-admin.css', array(), strtotime('now') );
	  // this is the only way to get this to go... it'll work until Gutenberg is ready
	  add_editor_style( URI_COURSES_URL . 'assets/tinymce/uri-courses-admin.css' );
		wp_enqueue_script( 'uri-courses-helpers', URI_COURSES_URL . 'assets/tinymce/uri-courses-helpers.js', array(), '1.0' );
	}
}
add_action( 'admin_enqueue_scripts', 'uri_courses_add_scripts' );


/**
 * Determine whether or not to load the courses button.
 * @return bool
 */
function _uri_courses_add_scripts() {
	if ( function_exists('get_current_screen')) { 
		$screen = get_current_screen();
		return ( 'page' === $screen->base || 'post' === $screen->base );
	}
	return FALSE;
}