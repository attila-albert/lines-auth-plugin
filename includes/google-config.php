<?php
/**
 * Google OAuth configuration for Lines Auth Plugin.
 *
 * @package LinesAuthPlugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// IMPORTANT: Replace these placeholder values with your actual Google API credentials.
define( 'LINES_AUTH_GOOGLE_CLIENT_ID', '524250596389-o2v81m5gaef1igldb5617e290fd4ds0h.apps.googleusercontent.com' );
define( 'LINES_AUTH_GOOGLE_CLIENT_SECRET', 'wHp6eFFtvJecde-y25Jld5PH' );
// This redirect URI must be added in your Google API Console as an authorized URI.
define( 'LINES_AUTH_GOOGLE_REDIRECT_URI', home_url( '/oauth/google' ) );
