<?php
/**
 * Forgot Password View for Lines Auth Plugin.
 *
 * @package LinesAuthPlugin
 */
if ( ! defined( 'ABSPATH' ) ) :
    exit;
endif;
get_header();
?>
<div class="forgot-password">
    <h1 class="forgot-password__title">Forgot Password</h1>
    <?php if ( isset( $errors ) && ! empty( $errors ) ) : ?>
        <div class="forgot-password__errors">
            <?php foreach ( $errors as $error ) : ?>
                <p class="forgot-password__error"><?php echo esc_html( $error ); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form class="forgot-password__form" method="post" action="">
        <div class="forgot-password__field">
            <label class="forgot-password__label" for="email">Email</label>
            <input class="forgot-password__input" type="email" id="email" name="email" required>
        </div>
        <div class="forgot-password__actions">
            <button class="forgot-password__button" type="submit">Reset Password</button>
        </div>
    </form>
</div>
<?php get_footer(); ?>
