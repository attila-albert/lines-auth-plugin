<?php
/**
 * Verify Email View for Lines Auth Plugin.
 *
 * @package LinesAuthPlugin
 */
if ( ! defined( 'ABSPATH' ) ) :
    exit;
endif;
get_header();
?>
<div class="verify-email">
    <h1 class="verify-email__title">Verify Email</h1>
    <div class="verify-email__message">
        <!-- The controller outputs the verification status here -->
    </div>
</div>
<?php get_footer(); ?>
