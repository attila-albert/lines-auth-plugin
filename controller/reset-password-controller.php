<?php
class Reset_Password_Controller {

    public function handle_request() {
        if ( ! session_id() ) {
            session_start();
        }
        $token = sanitize_text_field( $_GET['token'] ?? '' );
        $model = new Reset_Password_Model();
        $reset = $model->get_by_token( $token );

        if ( ! $reset || strtotime( $reset->expires ) < time() ) {
            echo '<div class="reset-error">Reset link is invalid or expired.</div>';
            exit;
        }

        include LINES_AUTH_PLUGIN_PATH . 'view/reset-password-view.php';
    }

    public function process_ajax_reset() {
        check_ajax_referer( 'lines_auth_reset_password', 'nonce' );

        $token   = sanitize_text_field( $_POST['token'] ?? '' );
        $new     = sanitize_text_field( $_POST['new_password'] ?? '' );
        $confirm = sanitize_text_field( $_POST['confirm_password'] ?? '' );

        if ( $new !== $confirm ) {
            wp_send_json_error( 'New passwords do not match.' );
        }

        if ( strlen( $new ) < User_Model::MIN_PASSWORD_LENGTH ) {
            wp_send_json_error( 'Password must be at least ' . User_Model::MIN_PASSWORD_LENGTH . ' characters long.' );
        }

        $model = new Reset_Password_Model();
        $reset = $model->get_by_token( $token );

        if ( ! $reset || strtotime( $reset->expires ) < time() ) {
            wp_send_json_error( 'Reset link is invalid or expired.' );
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . User_Model::TABLE_NAME,
            [ 'passwordHash' => password_hash( $new, PASSWORD_BCRYPT ) ],
            [ 'id' => $reset->userId ],
            [ '%s' ], [ '%d' ]
        );

        $model->delete_by_token( $token );
        wp_mail( sanitize_email( $_POST['email'] ), 'Password Reset Confirmation', 'Your password was reset successfully.' );
        wp_send_json_success( 'Password reset successfully.' );
    }
}
