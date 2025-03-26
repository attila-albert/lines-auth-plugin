<?php
/**
 * Verify Email Controller for Lines Auth Plugin.
 *
 * Handles email verification.
 *
 * @package LinesAuthPlugin
 */
class Verify_Email_Controller {

    /**
     * Handle the verify email request.
     *
     * @return void
     */
    public function handle_request() {
        if ( isset( $_GET['e'] ) && isset( $_GET['k'] ) && isset( $_GET['h'] ) ) :
            $this->process_verification();
        else :
            echo '<div class="verify-error">Invalid verification link.</div>';
        endif;
    }

    /**
     * Process email verification.
     *
     * @return void
     */
    private function process_verification() {
        $email = sanitize_email( $_GET['e'] );
        $key   = sanitize_text_field( $_GET['k'] );
        $hash  = sanitize_text_field( $_GET['h'] );

        // In a production system, validate the key and hash, update the user record, etc.
        echo '<div class="verify-success">Your email has been verified successfully.</div>';
    }
}
