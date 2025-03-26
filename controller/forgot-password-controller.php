<?php
/**
 * Forgot Password Controller for Lines Auth Plugin.
 *
 * Handles forgot password requests.
 *
 * @package LinesAuthPlugin
 */
class Forgot_Password_Controller {

    /**
     * Handle the forgot password request.
     *
     * @return void
     */
    public function handle_request() {
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) :
            $this->process_forgot_password();
        else :
            $this->display_forgot_password_form();
        endif;
    }

    /**
     * Process the forgot password form submission.
     *
     * @return void
     */
    public function process_forgot_password() {
        $email = sanitize_email( $_POST['email'] );
        if ( ! is_email( $email ) ) {
            $this->display_forgot_password_form(['A valid email is required.']);
            return;
        }
    
        global $wpdb;
        $user = $wpdb->get_row( $wpdb->prepare(
            "SELECT id,email FROM {$wpdb->prefix}" . User_Model::TABLE_NAME . " WHERE email=%s",
            $email
        ) );
    
        if ( $user ) {
            $token = bin2hex(random_bytes(30));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $reset_model = new Reset_Password_Model();
            $reset_model->create_reset( $user->id, $token, $expires );
    
            $link = home_url('/login/reset-password') . '?e=' . urlencode($user->email) . '&token=' . urlencode($token);
            wp_mail( $user->email, 'Reset Your Password', "Click here to reset: $link", ['Content-Type: text/html'] );
        }
    
        wp_redirect( home_url('/login?forgot=success') );
        exit;
    }

    /**
     * Display the forgot password form.
     *
     * @param array $errors Optional errors to display.
     * @return void
     */
    private function display_forgot_password_form( $errors = array() ) {
        include LINES_AUTH_PLUGIN_PATH . 'view/forgot-password-view.php';
    }
}
