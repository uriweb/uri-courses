<?php
/*
Plugin Name: URI Courses
Plugin URI: https://www.uri.edu
Description: Implements a shortcode to display course data from URI's API. [courses subject="AAF"]
Version: 1.1
Author: John Pennypacker
Author URI:
*/

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

define( 'URI_COURSES_PATH', plugin_dir_path( __FILE__ ) );
define( 'URI_COURSES_URL', str_replace('/assets', '/', plugins_url( 'assets', __FILE__ ) ) );


// require the code to handle where to find template files
require_once URI_COURSES_PATH . 'inc/uri-courses-templating.php';

// set up the admin settings screen
include_once( URI_COURSES_PATH . 'inc/uri-courses-settings.php' );

// include the TinyMCE button
include_once( URI_COURSES_PATH . 'inc/uri-courses-tinymce.php' );

// include the Gutenberg files
include_once( URI_COURSES_PATH . 'inc/uri-courses-gutenberg.php' );


function uri_courses_print($v) {
	echo '<pre style="padding: 2rem; max-width: 500px; margin: 3rem auto 1rem; background: #fff; color: #000; border: 4px solid red;">', print_r($v, TRUE), '</pre>';
}


/**
 * Add a few generic course styles
 */
function uri_courses_enqueue() {
	wp_enqueue_style( 'uri-courses-styles', plugins_url( 'assets/courses.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'uri_courses_enqueue' );


/**
 * Create a shortcode for displaying courses.
 * The shortcode accepts arguments: subject (the course code), min, max, before, after
 * NOTE: if max is unspecified, min will act as both min and max.
 * e.g. [courses subject="BUS"]
 */
/*function uri_courses_shortcode( $attributes, $content, $shortcode ) {
	// normalize attribute keys, lowercase
	$attributes = array_change_key_case( (array)$attributes, CASE_LOWER );

	// default attributes
	$attributes = shortcode_atts( array(
		'subject' => 'AAF', // slug, slug2, slug3
		'min' => NULL,
		'max' => NULL,
		'before' => '<div class="uri-courses">',
		'after' => '</div>',
	), $attributes, $shortcode );

	$course_data = uri_courses_get_courses( $attributes );

	$courses = $course_data['courses'];

	//echo '<pre>data: ', print_r($courses, TRUE), '</pre>';
	return uri_courses_display_list( $courses, $attributes );

	/* RETURN ERROR NOTICE TEMPORARILY - 10/5/21 BF
	$error_string = $attributes['subject'];
	$error_syntax = 'descriptions';
	if ( ! empty( $attributes['min'] ) ) {
		$error_string .= ' ' . $attributes['min'];
		$error_syntax = 'description';
	}
	if ( ! empty( $attributes['max'] ) ) {
		$error_string .= '-' . $attributes['max'];
		$error_syntax = 'descriptions';
	}
	return do_shortcode('[cl-notice dismissible="false"]Course descriptions are temporarily unavailable. Until the issue is resolved, you can <a rel="noreferrer noopener" href="https://web.uri.edu/catalog/files/2021-2022URICatalogCourseDescriptions.pdf" target="_blank">see the course ' . $error_syntax . ' for  <strong>' . $error_string . '</strong> here</a>.[/cl-notice]');
	*/

//}
//add_shortcode( 'courses', 'uri_courses_shortcode' );


/**
 * Render the list of courses
 * @param arr array of courses
 * @param arr attributes
 * @return str HTML list of courses
 */
function uri_courses_display_list( $courses, $attributes ) {
	if ( ! is_array ( $courses ) ) {
		// courses isn't an array...
		return '<p class="error">I couldn’t find courses matching <kbd>' . $attributes['subject'] . '</kbd>.</p>';
	}
	ob_start();

	print $attributes['before'];
	foreach($courses as $course) {
		//echo '<pre>data: ', print_r($course, TRUE), '</pre>';
		uri_courses_display_one( $course );
	}
	print $attributes['after'];

	$output = ob_get_clean();

	return $output;
}


/**
 * Load up the individual course template file and provide the course data to it.
 */
function uri_courses_display_one( $course ) {
	uri_courses_get_template( 'course-card.php', $course );
}



/**
 * Controller of the plugin.
 * Checks for a cache
 * if we have a good cache, we use that.
 * otherwise, we query new course data, and if it's good, we cache it.
 */
function uri_courses_get_courses( $attributes ) {

	$refresh_cache = FALSE;

	// 1. load all cached courses
	$course_cache = get_option( 'uri_courses_cache' );
	if ( empty( $course_cache ) ) {
		$course_cache = array();
	}

	// 2. check if we have a cache for this resource
	$url = _uri_courses_build_url ( $attributes );
	$hash = uri_courses_hash_url( $url );

	if ( array_key_exists( $hash, $course_cache ) ) {
		// we've got cached data!
		$course_data = $course_cache[$hash];
		// 3. check if the cache has sufficient recency
		if ( uri_courses_is_expired( $course_cache[$hash]['date'] ) ) {
			// cache is older than the specified recency, refresh it
			// 4. refresh courses / update cache if needed
			$refresh_cache = TRUE;
		}

	} else { // no cache data
		$refresh_cache = TRUE;
	}

	if( $refresh_cache ) {
		//echo '<pre>Pull fresh courses and cache them</pre>';
		$course_data = _uri_courses_query_api_by_subject( $attributes );
		if ( $course_data !== FALSE ) {
			uri_courses_cache_courses($course_data);
		}
	}

	return $course_data;

}


/**
 * Save the data retrieved from the API as a WordPress option
 * we use the same array to store data from every course shortcode on the site
 * each has its own key (based on the URL hash) and date when it was stored
 */
function uri_courses_cache_courses( $course_data ) {
	$course_data['date'] = strtotime('now');
	$hash = uri_courses_hash_url( $course_data['url'] );
	$data = get_option( 'uri_courses_cache' );
	if ( empty ( $data ) ) {
		$data = array();
	}
	$data[$hash] = $course_data;
	update_option( 'uri_courses_cache', $data, TRUE );
}


/**
 * hash the URL.  currently md5, someday something else.
 * @param str $url the URL for the API
 * @return str
 */
function uri_courses_hash_url ( $url ) {
	$hash = md5( $url );
	return $hash;
}
/**
 * Build the URL for the API
 * @param str $subject is the three letter subject code
 * @return str
 */
function _uri_courses_build_url( $attributes ) {
	$api_base = get_option( 'uri_courses_url' );
	$url = $api_base . '/catalog/courses/' . $attributes['subject'];
	if ( NULL !== $attributes['min'] && is_numeric( $attributes['min'] ) ) {
		$url .= '/' . $attributes['min'];
	}
	if ( NULL !== $attributes['max'] && is_numeric( $attributes['max'] ) ) {
		$url .= '/' . $attributes['max'];
	}
	return $url;
}
/**
 * check if a date has recency
 * @param int date
 * @return bool
 */
function uri_courses_is_expired( $date ) {
	$recency = get_option( 'uri_courses_recency', '1 day' );
	$expiry = strtotime( '-'.$recency, strtotime('now') );
	return ( $date < $expiry );
}

/**
 * Query the API for course data
 * Thanks to Heath Loder for the query code
 *
 * @todo: handle condition when client ID is blank or rejected
 *
 * @return mixed arr on success; FALSE on failure
 */
function _uri_courses_query_api_by_subject( $attributes ) {
	$client_id = get_option( 'uri_courses_client_id' );
	$user_agent = 'URI REST API WordPress Plugin; ' . get_bloginfo('url'); // So api.uri.edu can easily figure out who we are


	if ( ! empty ( $client_id ) ) {
		// Set ClientID in header here
		$args = array(
			'user-agent'  => $user_agent,
			'headers'     => [ "id" => $client_id ]
		);
	}

	$url = _uri_courses_build_url ( $attributes );

	$response = wp_safe_remote_get ( $url, $args );

	if ( isset( $response['body'] ) && !empty( $response['body'] ) && '200' == wp_remote_retrieve_response_code( $response ) ) {
		// hooray, all is well!
		$results = array();
		$results['url'] = $url;
		$results['courses'] = json_decode ( wp_remote_retrieve_body ( $response ) );
		return $results;
	} else {

		// still here?  Then we have an error condition

		if ( is_wp_error ( $response ) ) {
			$error_message = $response->get_error_message();
			echo 'There was an error with the URI Courses Plugin: ' . $error_message;
			return FALSE;
		}
		if ( '200' != wp_remote_retrieve_response_code( $response ) ) {
			echo $response;
			return FALSE;
		}

		// still here?  the error condition is indeed unexpected
		echo "Empty response from server?";
		return FALSE;
	}
}
