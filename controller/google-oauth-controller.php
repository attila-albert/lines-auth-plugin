<?php
/**
 * Google OAuth Controller for Lines Auth Plugin.
 *
 * Handles Google login/signup via OAuth.
 *
 * @package LinesAuthPlugin
 */
class Google_OAuth_Controller {

	/**
	 * Handle the Google OAuth request.
	 *
	 * @return void
	 */
	public function handle_request() {
		if ( ! isset( $_GET['code'] ) ) {
			$this->redirect_to_google();
		} else {
			$this->handle_callback();
		}
	}

	/**
	 * Redirect the user to Google's OAuth consent screen.
	 *
	 * @return void
	 */
	private function redirect_to_google() {
		$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth';
		$params = array(
			'response_type' => 'code',
			'client_id'     => LINES_AUTH_GOOGLE_CLIENT_ID,
			'redirect_uri'  => LINES_AUTH_GOOGLE_REDIRECT_URI,
			'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
			'access_type'   => 'online',
			'prompt'        => 'select_account',
		);
		$url = $auth_url . '?' . http_build_query( $params );
		wp_redirect( $url );
		exit;
	}

	/**
	 * Handle the OAuth callback from Google.
	 *
	 * @return void
	 */
	private function handle_callback() {
		$code = sanitize_text_field( $_GET['code'] );

		$token_response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
			'body' => array(
				'code'          => $code,
				'client_id'     => LINES_AUTH_GOOGLE_CLIENT_ID,
				'client_secret' => LINES_AUTH_GOOGLE_CLIENT_SECRET,
				'redirect_uri'  => LINES_AUTH_GOOGLE_REDIRECT_URI,
				'grant_type'    => 'authorization_code',
			),
		) );

		if ( is_wp_error( $token_response ) ) {
			wp_die( 'Token exchange error: ' . $token_response->get_error_message() );
		}

		$token_data = json_decode( wp_remote_retrieve_body( $token_response ), true );
		if ( ! isset( $token_data['access_token'] ) ) {
			wp_die( 'No access token returned.' );
		}

		$access_token = $token_data['access_token'];

		$user_response = wp_remote_get( 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json', array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
			),
		) );

		if ( is_wp_error( $user_response ) ) {
			wp_die( 'User info retrieval error: ' . $user_response->get_error_message() );
		}

		$user_data = json_decode( wp_remote_retrieve_body( $user_response ), true );
		if ( ! isset( $user_data['email'] ) ) {
			wp_die( 'Google user data incomplete.' );
		}

		$this->process_google_user( $user_data );
	}

	/**
	 * Process the Google user data: create a new user if needed or log in the existing user.
	 *
	 * @param array $user_data Data returned from Google.
	 * @return void
	 */
	private function process_google_user( $user_data ) {
		global $wpdb;
		$email = sanitize_email( $user_data['email'] );
		$name  = sanitize_text_field( $user_data['name'] );

		$user_table = $wpdb->prefix . User_Model::TABLE_NAME;
		$existing_user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $user_table WHERE email = %s", $email ) );

		if ( $existing_user ) {
			$user = $existing_user;
		} else {
			$username = sanitize_user( current( explode( '@', $email ) ) );
			if ( ! preg_match( User_Model::USERNAME_VALIDATION_PATTERN, $username ) ) {
				$username = 'google_' . $username;
			}
			$random_password = wp_generate_password( 12, false );
			$password_hash = password_hash( $random_password, PASSWORD_BCRYPT );
			$insert_data = array(
				'username'           => $username,
				'passwordHash'       => $password_hash,
				'role'               => User_Model::ROLE_USER,
				'inserted'           => current_time( 'mysql' ),
				'invitedBy'          => null,
				'email'              => $email,
				'name'               => $name,
				'notificationsFlags' => 0,
				'flags'              => 0,
				'birth'              => '1970-01-01', // Default birth.
				'source'             => 'google',
			);
			$result = $wpdb->insert( $user_table, $insert_data );
			if ( ! $result ) {
				wp_die( 'Failed to create user account.' );
			}
			$user_id = $wpdb->insert_id;
			$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $user_table WHERE id = %d", $user_id ) );
		}

		// Log in the user via token-based session.
		$token = bin2hex( random_bytes( 30 ) );
		$ip = $_SERVER['REMOTE_ADDR'];
		$useragent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '';
		$expires = date( 'Y-m-d H:i:s', strtotime( '+3 months' ) );
		$session_model = new Session_Model();
		$session_created = $session_model->create_session( $user->id, $token, $ip, $useragent, $expires );
		if ( $session_created ) {
			setcookie( 'lines_auth_token', $token, time() + ( 3 * 30 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
			// Instead of a standard redirect, output a small HTML page that closes the popup.
			echo '<html><body><script>
				if(window.opener){
					window.opener.location.href = "' . esc_url( home_url( '/profile' ) ) . '";
				}
				window.close();
				</script></body></html>';
			exit;
		} else {
			wp_die( 'Failed to create session.' );
		}
	}
}
