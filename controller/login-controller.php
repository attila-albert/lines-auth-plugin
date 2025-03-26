<?php
/**
 * Login Controller for Lines Auth Plugin.
 *
 * Handles user login.
 *
 * @package LinesAuthPlugin
 */
class Login_Controller {

	/**
	 * Handle the login request.
	 *
	 * @return void
	 */
	public function handle_request() {
		// If already logged in, redirect to profile.
		if ( lines_auth_plugin_is_logged_in() ) :
			wp_redirect( home_url( '/profile' ) );
			exit;
		endif;

		if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) :
			$this->process_login();
		else :
			$this->display_login_form();
		endif;
	}

	/**
	 * Process the login form submission.
	 *
	 * @return void
	 */
	private function process_login() {
		$identifier = isset( $_POST['identifier'] ) ? sanitize_email( $_POST['identifier'] ) : '';
		$password   = isset( $_POST['password'] ) ? $_POST['password'] : '';

		$errors = array();
		if ( empty( $identifier ) ) :
			$errors[] = 'Email is required.';
		endif;
		if ( empty( $password ) ) :
			$errors[] = 'Password is required.';
		endif;

		if ( ! empty( $errors ) ) :
			$this->display_login_form( $errors );
			return;
		endif;

		global $wpdb;
		$table_name = $wpdb->prefix . User_Model::TABLE_NAME;
		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE email = %s", $identifier ) );

		if ( $user && password_verify( $password, $user->passwordHash ) ) :
			// Generate a token (60 hex characters).
			$token = bin2hex( random_bytes( 30 ) );
			$ip = $_SERVER['REMOTE_ADDR'];
			$useragent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '';
			// Set expiry date to 3 months from now.
			$expires = date( 'Y-m-d H:i:s', strtotime( '+3 months' ) );
			$session_model = new Session_Model();
			$session_created = $session_model->create_session( $user->id, $token, $ip, $useragent, $expires );
			if ( $session_created ) :
				// Set a secure, HTTP-only cookie with the token.
				setcookie( 'lines_auth_token', $token, time() + ( 3 * 30 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
				wp_redirect( home_url( '/profile' ) );
				exit;
			else :
				$errors[] = 'Could not create session, please try again.';
				$this->display_login_form( $errors );
			endif;
		else :
			$errors[] = 'Invalid credentials.';
			$this->display_login_form( $errors );
		endif;
	}

	/**
	 * Display the login form.
	 *
	 * @param array $errors Optional errors to display.
	 * @return void
	 */
	private function display_login_form( $errors = array() ) {
		include LINES_AUTH_PLUGIN_PATH . 'view/login-view.php';
	}
}
