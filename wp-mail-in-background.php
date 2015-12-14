<?php
/*
Plugin Name: Send emails in background
Plugin URI: http://dvk.co/
Description: Process emails sent by `wp_mail` in a "background queue"
Author: Danny van Kooten
Version: 1.0
Author URI: http://dvk.co/
*/

namespace dvk;

/**
 * @return array
 */
function get_email_queue() {
	$queue = (array) get_option( 'dvk_email_queue', array() );
	$queue = array_filter( $queue );
	return $queue;
}

/**
 * @param $args
 *
 * @return bool
 */
function add_to_email_queue( $args ) {
	$queue = get_email_queue();
	$queue[] = $args;
	return set_email_queue( $queue );
}

/**
 * @param $queue
 *
 * @return bool
 */
function set_email_queue( $queue ) {
	return update_option( 'dvk_email_queue', $queue );
}

/**
 * @param $args
 *
 * @return bool
 */
function queue_wp_mail( $args ) {

	add_to_email_queue( $args );

	// schedule event to process all queued emails
	if( ! wp_next_scheduled( 'dvk_process_email_queue' ) ) {

		// schedule event to be fired right away
		wp_schedule_single_event( time(), 'dvk_process_email_queue' );

		// send off a request to wp-cron on shutdown
		add_action( 'shutdown', 'spawn_cron' );
	}

	/**
	 * Return empty `to` and `message` values as this stops the email from being sent
	 *
	 * Once `wp_mail` can be short-circuited using falsey values, we can return false here.
	 *
	 * @see https://core.trac.wordpress.org/ticket/35069
	 */
	return array(
		'to' => '',
		'message' => ''
	);
}

/**
 * Processes the email queue
 */
function process_email_queue() {

	// remove filter as we don't want to short circuit ourselves
	remove_filter( 'wp_mail', 'dvk\\queue_wp_mail' );

	$queue = get_email_queue();

	if( ! empty( $queue ) ) {

		// send each queued email
		foreach( $queue as $key => $args ) {
			wp_mail( $args['to'], $args['subject'], $args['message'], $args['headers'], $args['attachments'] );
			unset( $queue[ $key ] );
		}

		// update queue with removed values
		set_email_queue( $queue );
	}
}

// scheduling
if( ! defined( 'WP_CRON' ) || ! WP_CRON ) {
	add_filter( 'wp_mail', 'dvk\\queue_wp_mail' );
}

// processing
add_action( 'dvk_process_email_queue', 'dvk\\process_email_queue');