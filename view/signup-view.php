<?php
/**
 * Signup View for Lines Auth Plugin.
 *
 * @package LinesAuthPlugin
 */
if ( ! defined( 'ABSPATH' ) ) :
    exit;
endif;
get_header();
?>
<div class="signup">
    <h1 class="signup__title">Sign Up</h1>
    <div id="signup-response"></div>
    <form class="signup__form" id="signup-form" method="post" action="#">
        <!-- Hidden fields for AJAX action and nonce -->
        <input type="hidden" name="action" value="lines_auth_signup">
        <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'lines_auth_signup_nonce' ) ); ?>">
        <div class="signup__field">
            <label class="signup__label" for="username">Username</label>
            <input class="signup__input" type="text" id="username" name="username" required>
        </div>
        <div class="signup__field">
            <label class="signup__label" for="name">Name</label>
            <input class="signup__input" type="text" id="name" name="name" required>
        </div>
        <div class="signup__field">
            <label class="signup__label" for="email">Email</label>
            <input class="signup__input" type="email" id="email" name="email" required>
        </div>
        <div class="signup__field">
            <label class="signup__label" for="password">Password</label>
            <input class="signup__input" type="password" id="password" name="password" required>
        </div>
        <div class="signup__field">
            <label class="signup__label" for="birth">Birth Date</label>
            <input class="signup__input" type="date" id="birth" name="birth" required>
        </div>
        <div class="signup__actions">
            <button class="signup__button" type="submit">Sign Up</button>
        </div>
    </form>
    <div class="signup__oauth">
        <p class="signup__oauth-text">Or sign up with:</p>
        <a class="signup__oauth-button" href="javascript:void(0)" onclick="openGoogleOAuth()">Google</a>
        <a class="signup__oauth-button" href="<?php echo esc_url( home_url( '/oauth/facebook' ) ); ?>">Facebook</a>
        <a class="signup__oauth-button" href="<?php echo esc_url( home_url( '/oauth/apple' ) ); ?>">Apple ID</a>
    </div>
</div>
<?php get_footer(); ?>
