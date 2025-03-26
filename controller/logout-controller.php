<?php
/**
 * Logout Controller for Lines Auth Plugin.
 *
 * Handles user logout.
 *
 * @package LinesAuthPlugin
 */
class Logout_Controller {

	/**
	 * Handle the logout request.
	 *
	 * @return void
	 */
	public function handle_request() {
		// Remove the token cookie and delete session from database.
		if ( isset( $_COOKIE['lines_auth_token'] ) ) {
			$token = sanitize_text_field( $_COOKIE['lines_auth_token'] );
			// Delete session using Session_Model.
			if ( class_exists( 'Session_Model' ) ) {
				$session_model = new Session_Model();
				$session_model->delete_session_by_token( $token );
			}
			// Clear the cookie.
			setcookie( 'lines_auth_token', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		}
		// Redirect to the login page.
		wp_redirect( home_url( '/login' ) );
		exit;
	}
}
