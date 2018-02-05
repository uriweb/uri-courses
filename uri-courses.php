<?php
/*
Plugin Name: URI Courses
Plugin URI: http://www.uri.edu
Description: Implements a shortcode to display course data from URI's API. [courses subject="AAF"]
Version: 0.1
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

// include the Gutenberg block
// currently, quotation marks in a gutenberg break the update post process dunno why.
// include_once( URI_COURSES_PATH . 'inc/uri-courses-gutenberg.php' );

// include the TinyMCE button
include_once( URI_COURSES_PATH . 'inc/uri-courses-tinymce.php' );


/**
 * Add a few generic course styles
 */
function uri_courses_enqueue() {
	wp_enqueue_style( 'uri-courses-styles', plugins_url( 'assets/courses.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'uri_courses_enqueue' );


/**
 * Create a shortcode for displaying courses.
 * The shortcode accepts arguments: group (the category slug), posts_per_page, before, after
 * e.g. [uri-courses group="faculty"]
 */
function uri_courses_shortcode( $attributes, $content, $shortcode ) {
	// normalize attribute keys, lowercase
	$attributes = array_change_key_case( (array)$attributes, CASE_LOWER );

	// default attributes
	$attributes = shortcode_atts( array(
		'subject' => 'AAF', // slug, slug2, slug3
		'before' => '<div class="uri-courses">',
		'after' => '</div>',
	), $attributes, $shortcode );

	$course_data = uri_courses_get_courses( $attributes );
	$courses = $course_data['courses'];

	//echo '<pre>data: ', print_r($courses, TRUE), '</pre>';
	return uri_courses_display_list( $courses, $attributes );

}
add_shortcode( 'courses', 'uri_courses_shortcode' );


/**
 * Render the list of courses
 * @param arr array of courses
 * @param arr attributes
 * @return str HTML list of courses
 */
function uri_courses_display_list( $courses, $attributes ) {
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
	$url = _uri_courses_build_url ( $attributes['subject'] );
	$hash = uri_courses_hash_url( $url );
	
	if ( array_key_exists($hash, $course_cache ) ) {
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
		$course_data = _uri_courses_query_api_by_subject( $attributes['subject'] );
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
function _uri_courses_build_url( $subject ) {
	$api_base = get_option( 'uri_courses_url' );
	$url = $api_base . '/catalog/courses/' . $subject;
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
 * @return mixed arr on success; FALSE on failure
 */
function _uri_courses_query_api_by_subject( $subject ) {

	$client_id = get_option( 'uri_courses_client_id' );
	$user_agent = 'URI REST API WordPress Plugin; ' . get_bloginfo('url'); // So api.uri.edu can easily figure out who we are


	if ( ! empty ( $client_id ) ) {
		// Set ClientID in header here
		$args = array(
			'user-agent'  => $user_agent,
			'headers'     => [ "id" => $client_id ]
		);
	}
	
	$url = _uri_courses_build_url ( $subject );
	
	$response = wp_safe_remote_get ( $url, $args );
	
	if ( isset( $response['body'] ) && !empty( $response['body'] ) && wp_remote_retrieve_response_code($response) == '200' ) {
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
		if ( wp_remote_retrieve_response_code($response) != '200' ) {
			echo $response;
			return FALSE;
		}

		// still here?  the error condition is indeed unexpected
		echo "Empty response from server?";
		return FALSE;
	}
}