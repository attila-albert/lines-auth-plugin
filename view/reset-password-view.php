<?php
/**
 * Reset Password View for Lines Auth Plugin.
 *
 * @package LinesAuthPlugin
 */
?>
<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php get_header(); ?>

<div class="reset-password">
    <h1>Reset Password</h1>
    <p>Email: <?php echo esc_html( $_GET['e'] ?? '' ); ?></p>

    <form id="reset-form" action="javascript:void(0);" method="POST">
        <input type="hidden" name="action" value="lines_auth_reset_password">
        <input type="hidden" name="email" value="<?php echo esc_attr( $_GET['e'] ?? '' ); ?>">
        <input type="hidden" name="token" value="<?php echo esc_attr( $_GET['token'] ?? '' ); ?>">

        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>

        <button type="submit">Submit</button>
    </form>

    <!-- Error / success message container -->
    <div id="reset-message" class="reset-password__message"></div>
</div>

<script src="<?php echo esc_url( LINES_AUTH_PLUGIN_URL . 'assets/js/reset-password.min.js' ); ?>"></script>
<?php get_footer(); ?>
