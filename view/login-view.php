<?php
/**
 * Login View for Lines Auth Plugin.
 *
 * @package LinesAuthPlugin
 */
if ( ! defined( 'ABSPATH' ) ) :
    exit;
endif;
get_header();
?>

<div class="login">
    <h1 class="login__title">Login</h1>
    <?php if ( isset( $_GET['forgot'] ) && $_GET['forgot'] === 'success' ) : ?>
        <div class="login__message">You successfully requested a password reset. Check your email for further instructions.</div>
    <?php endif; ?>
    <?php if ( isset( $errors ) && ! empty( $errors ) ) : ?>
        <div class="login__errors">
            <?php foreach ( $errors as $error ) : ?>
                <p class="login__error"><?php echo esc_html( $error ); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form class="login__form" method="post" action="">
        <div class="login__field">
            <label class="login__label" for="identifier">Email</label>
            <input class="login__input" type="email" id="identifier" name="identifier" required>
        </div>
        <div class="login__field">
            <label class="login__label" for="password">Password</label>
            <input class="login__input" type="password" id="password" name="password" required>
        </div>
        <div class="login__actions">
            <button class="login__button" type="submit">Login</button>
        </div>
    </form>
    <div class="login__forgot">
        <a class="login__forgot-link" href="<?php echo esc_url( home_url( '/login/forgot' ) ); ?>">Forgot Password?</a>
    </div>
    <div class="login__oauth">
        <p class="login__oauth-text">Or login with:</p>
        <a class="login__oauth-button" href="javascript:void(0)" onclick="openGoogleOAuth()">Google</a>
        <a class="login__oauth-button" href="<?php echo esc_url( home_url( '/oauth/facebook' ) ); ?>">Facebook</a>
        <a class="login__oauth-button" href="<?php echo esc_url( home_url( '/oauth/apple' ) ); ?>">Apple ID</a>
    </div>
</div>
<?php get_footer(); ?>
