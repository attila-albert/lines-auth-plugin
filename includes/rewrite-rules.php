<?php
/**
 * Rewrite rules for Lines Auth Plugin.
 *
 * @return void
 */
function lines_auth_plugin_add_rewrite_rules() {
    add_rewrite_rule( '^login/sign-up/?$', 'index.php?pagename=login-sign-up', 'top' );
    add_rewrite_rule( '^login/forgot/?$', 'index.php?pagename=login-forgot', 'top' );
    add_rewrite_rule( '^login/verify-email/?$', 'index.php?pagename=login-verify-email', 'top' );
    add_rewrite_rule( '^login/?$', 'index.php?pagename=login', 'top' );
    // Profile handled via custom query var.
    add_rewrite_rule( '^profile/?$', 'index.php?lines_auth_profile=1', 'top' );
    // Google OAuth.
    add_rewrite_rule( '^oauth/google/?$', 'index.php?lines_auth_google=1', 'top' );
    // Logout route.
    add_rewrite_rule( '^logout/?$', 'index.php?lines_auth_logout=1', 'top' );
    // Password reset
    add_rewrite_rule( '^login/reset-password/?$', 'index.php?lines_auth_reset=1', 'top' );

}
add_action( 'init', 'lines_auth_plugin_add_rewrite_rules' );

/**
 * Register custom query vars.
 *
 * @param array $vars Current query vars.
 * @return array Modified query vars.
 */
function lines_auth_plugin_query_vars( $vars ) {
    $vars[] = 'lines_auth_profile';
    $vars[] = 'lines_auth_google';
    $vars[] = 'lines_auth_logout';
    $vars[] = 'lines_auth_reset';
    
    return $vars;
}
add_filter( 'query_vars', 'lines_auth_plugin_query_vars' );
