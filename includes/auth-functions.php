<?php
/**
 * Auth Functions for Lines Auth Plugin.
 *
 * Provides helper functions to check the login state using token-based authentication.
 *
 * @package LinesAuthPlugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if a user is logged in.
 *
 * @return bool True if logged in, false otherwise.
 */
function lines_auth_plugin_is_logged_in() {
	return ( lines_auth_plugin_get_current_user() !== false );
}

/**
 * Retrieve the currently logged in user.
 *
 * @return object|false User object if logged in, false otherwise.
 */
function lines_auth_plugin_get_current_user() {
	if ( isset( $_COOKIE['lines_auth_token'] ) ) {
		$token = sanitize_text_field( $_COOKIE['lines_auth_token'] );
		// Use the Session_Model to look up the token.
		if ( class_exists( 'Session_Model' ) ) {
			$session_model = new Session_Model();
			$session = $session_model->get_session( $token );
			// Check if the session exists and is not expired.
			if ( $session && strtotime( $session->expires ) > time() ) {
				// Retrieve the user from the user table.
				global $wpdb;
				$user_table = $wpdb->prefix . User_Model::TABLE_NAME;
				$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $user_table WHERE id = %d", $session->userId ) );
				return $user;
			}
		}
	}
	return false;
}
