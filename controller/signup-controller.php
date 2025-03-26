<?php
/**
 * Signup Controller for Lines Auth Plugin.
 *
 * Handles user registration.
 *
 * @package LinesAuthPlugin
 */
class Signup_Controller {

    /**
     * Handle the signup request.
     *
     * @return void
     */
    public function handle_request() {
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' && ! wp_doing_ajax() ) :
            $this->process_signup();
        else :
            $this->display_signup_form();
        endif;
    }

    /**
     * Process the signup form submission (non-AJAX).
     *
     * @return void
     */
    private function process_signup() {
        // This method can be used for non-AJAX processing if needed.
    }

    /**
     * Process the signup form submission via AJAX.
     *
     * @return void
     */
    public function process_ajax_signup() {
        // Check nonce to verify the request.
        check_ajax_referer( 'lines_auth_signup_nonce', 'nonce' );

        // Sanitize and validate input.
        $username = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
        $name     = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $password = isset( $_POST['password'] ) ? $_POST['password'] : '';
        $birth    = isset( $_POST['birth'] ) ? sanitize_text_field( $_POST['birth'] ) : '';
        $email    = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

        $errors = array();
        if ( empty( $username ) ) :
            $errors[] = 'Username is required.';
        endif;
        if ( empty( $name ) ) :
            $errors[] = 'Name is required.';
        endif;
        if ( empty( $password ) || strlen( $password ) < User_Model::MIN_PASSWORD_LENGTH ) :
            $errors[] = 'Password must be at least ' . User_Model::MIN_PASSWORD_LENGTH . ' characters long.';
        endif;
        if ( empty( $birth ) ) :
            $errors[] = 'Birth date is required.';
        endif;
        if ( empty( $email ) || ! is_email( $email ) ) :
            $errors[] = 'A valid email is required.';
        endif;
        if ( ! preg_match( User_Model::USERNAME_VALIDATION_PATTERN, $username ) ) :
            $errors[] = 'Invalid username format.';
        endif;

        // *** Added uniqueness checks ***
        $user_model = new User_Model();
        if ( $user_model->get_by_username( $username ) ) {
            $errors[] = 'Username already in use.';
        }
        if ( $user_model->get_by_email( $email ) ) {
            $errors[] = 'Email already in use.';
        }
        // *** End additions ***

        if ( ! empty( $errors ) ) :
            wp_send_json_error( array( 'errors' => $errors ) );
        endif;

        // Hash the password.
        $password_hash = password_hash( $password, PASSWORD_BCRYPT );

        // Prepare user data.
        $user_data = array(
            'username'           => $username,
            'passwordHash'       => $password_hash,
            'role'               => User_Model::ROLE_USER,
            'inserted'           => current_time( 'mysql' ),
            'invitedBy'          => null,
            'email'              => $email,
            'name'               => $name,
            'notificationsFlags' => 0,
            'flags'              => 0,
            'birth'              => $birth,
            'source'             => 'local',
        );

        // Insert user into the custom table.
        global $wpdb;
        $table_name = $wpdb->prefix . User_Model::TABLE_NAME;
        $inserted   = $wpdb->insert( $table_name, $user_data );

        if ( $inserted ) :
            // Send verification email.
            $this->send_verification_email( $user_data );
            wp_send_json_success( array( 'message' => 'Registration successful. Please check your email to verify your account.' ) );
        else :
            wp_send_json_error( array( 'message' => 'Registration failed. Please try again.' ) );
        endif;
    }

    /**
     * Display the signup form.
     *
     * @param array $errors Optional errors to display.
     * @return void
     */
    private function display_signup_form( $errors = array() ) {
        include LINES_AUTH_PLUGIN_PATH . 'view/signup-view.php';
    }

    /**
     * Send verification email to the user.
     *
     * @param array $user_data User data array.
     * @return void
     */
    private function send_verification_email( $user_data ) {
        // Generate a verification key and hash.
        $verification_key = md5( $user_data['email'] . time() );
        $hash             = hash( 'sha256', $user_data['username'] . $user_data['inserted'] );
        $verify_url       = home_url( '/login/verify-email' ) . '?e=' . urlencode( $user_data['email'] ) . '&k=' . $verification_key . '&h=' . $hash . '&p=' . urlencode( '/login/verify-email' );

        $subject = 'Verify your email address';
        $message = 'Please verify your email by clicking the following link: ' . $verify_url;
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        wp_mail( $user_data['email'], $subject, $message, $headers );
    }
}
