<?php
/**
 * Profile Controller for Lines Auth Plugin.
 *
 * Handles profile display and AJAX updates.
 *
 * @package LinesAuthPlugin
 */
class Profile_Controller {

    /**
     * Handle the profile page request.
     *
     * @return void
     */
    public function handle_request() {
        if ( ! lines_auth_plugin_is_logged_in() ) {
            wp_redirect( home_url( '/login' ) );
            exit;
        }
        $current_user = lines_auth_plugin_get_current_user();
        include LINES_AUTH_PLUGIN_PATH . 'view/profile-view.php';
    }

    /**
     * AJAX handler to update a single profile field.
     *
     * @return void
     */
    public function process_ajax_update_profile() {
        check_ajax_referer( 'lines_auth_update_profile', 'nonce' );

        $current_user = lines_auth_plugin_get_current_user();
        if ( ! $current_user ) {
            wp_send_json_error( 'Not logged in.' );
        }

        $field = sanitize_key( $_POST['field'] ?? '' );
        $allowed = [ 'username', 'name', 'email', 'birth', 'password' ];
        if ( ! in_array( $field, $allowed, true ) ) {
            wp_send_json_error( 'Invalid field.' );
        }

        $user_model = new User_Model();
        $data = [];

        if ( 'password' === $field ) {
            $old     = sanitize_text_field( $_POST['old_password'] ?? '' );
            $new     = sanitize_text_field( $_POST['new_password'] ?? '' );
            $confirm = sanitize_text_field( $_POST['confirm_password'] ?? '' );

            if ( empty( $old ) || empty( $new ) || $new !== $confirm ) {
                wp_send_json_error( 'Passwords must match and cannot be empty.' );
            }
            if ( ! password_verify( $old, $current_user->passwordHash ) ) {
                wp_send_json_error( 'Old password incorrect.' );
            }
            if ( strlen( $new ) < User_Model::MIN_PASSWORD_LENGTH ) {
                wp_send_json_error( 'New password must be at least ' . User_Model::MIN_PASSWORD_LENGTH . ' characters long.' );
            }

            $data['passwordHash'] = password_hash( $new, PASSWORD_BCRYPT );
        } else {
            $value = sanitize_text_field( $_POST['value'] ?? '' );

            switch ( $field ) {
                case 'email':
                    if ( ! is_email( $value ) ) {
                        wp_send_json_error( 'Invalid email.' );
                    }
                    // Check if the email is already used by another user.
                    $existing_user = $user_model->get_by_email( $value );
                    if ( $existing_user && $existing_user->id != $current_user->id ) {
                        wp_send_json_error( 'Email already in use.' );
                    }
                    $data['email'] = $value;
                    break;
                case 'username':
                    if ( ! preg_match( User_Model::USERNAME_VALIDATION_PATTERN, $value ) ) {
                        wp_send_json_error( 'Invalid username format.' );
                    }
                    // Check if the username is already used by another user.
                    $existing_user = $user_model->get_by_username( $value );
                    if ( $existing_user && $existing_user->id != $current_user->id ) {
                        wp_send_json_error( 'Username already in use.' );
                    }
                    $data['username'] = $value;
                    break;
                case 'birth':
                    $data['birth'] = $value;
                    break;
                case 'name':
                    $data['name'] = $value;
                    break;
            }
        }

        $updated = $user_model->update_user( $current_user->id, $data );
        if ( false === $updated ) {
            wp_send_json_error( 'Update failed.' );
        }

        wp_send_json_success( [ 'value' => ( 'password' === $field ? '*****' : $data[ $field ] ) ] );
    }
}
