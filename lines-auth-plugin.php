<?php
/**
 * Plugin Name: Lines Auth Plugin
 * Plugin URI: https://example.com/
 * Description: Secure, production-ready authentication for WordPress pages with separate users.
 * Version: 1.0.0
 * Author: Attila Albert
 * License: GPL2
 * Text Domain: lines-auth-plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) :
	exit;
endif;

// Define plugin constants.
if ( ! defined( 'LINES_AUTH_PLUGIN_PATH' ) ) :
	define( 'LINES_AUTH_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
endif;
if ( ! defined( 'LINES_AUTH_PLUGIN_URL' ) ) :
	define( 'LINES_AUTH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
endif;

// Include the autoloader (which defines lines_auth_plugin_autoloader).
require_once LINES_AUTH_PLUGIN_PATH . 'autoloader.php';
spl_autoload_register( 'lines_auth_plugin_autoloader' );

// Include rewrite rules.
require_once LINES_AUTH_PLUGIN_PATH . 'includes/rewrite-rules.php';

// Include our auth helper functions.
require_once LINES_AUTH_PLUGIN_PATH . 'includes/auth-functions.php';
// Include Google OAuth config.
require_once LINES_AUTH_PLUGIN_PATH . 'includes/google-config.php';

// Enqueue plugin assets.
add_action( 'wp_enqueue_scripts', 'lines_auth_plugin_enqueue_assets' );
/**
 * Enqueue scripts and styles for plugin pages.
 *
 * @return void
 */
function lines_auth_plugin_enqueue_assets() {
	if ( is_page( 'login-sign-up' ) ) :
		wp_enqueue_style( 'lines-auth-signup', LINES_AUTH_PLUGIN_URL . 'assets/css/signup.css' );
		wp_enqueue_script( 'lines-auth-signup', LINES_AUTH_PLUGIN_URL . 'assets/js/signup.min.js', array(), null, true );
		// Enqueue OAuth script for popup handling.
		wp_enqueue_script( 'lines-auth-oauth', LINES_AUTH_PLUGIN_URL . 'assets/js/oauth.min.js', array(), null, true );
		wp_localize_script( 'lines-auth-signup', 'linesAuth', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'lines_auth_signup_nonce' )
		) );
	elseif ( is_page( 'login' ) ) :
		wp_enqueue_style( 'lines-auth-login', LINES_AUTH_PLUGIN_URL . 'assets/css/login.css' );
		wp_enqueue_script( 'lines-auth-login', LINES_AUTH_PLUGIN_URL . 'assets/js/login.min.js', array(), null, true );
		// Enqueue OAuth script on login page as well.
		wp_enqueue_script( 'lines-auth-oauth', LINES_AUTH_PLUGIN_URL . 'assets/js/oauth.min.js', array(), null, true );
	elseif ( is_page( 'login-forgot' ) ) :
		wp_enqueue_style( 'lines-auth-forgot', LINES_AUTH_PLUGIN_URL . 'assets/css/forgot-password.css' );
		wp_enqueue_script( 'lines-auth-forgot', LINES_AUTH_PLUGIN_URL . 'assets/js/forgot-password.min.js', array(), null, true );
	elseif ( is_page( 'login-verify-email' ) ) :
		wp_enqueue_style( 'lines-auth-verify-email', LINES_AUTH_PLUGIN_URL . 'assets/css/verify-email.css' );
		wp_enqueue_script( 'lines-auth-verify-email', LINES_AUTH_PLUGIN_URL . 'assets/js/verify-email.min.js', array(), null, true );
	elseif ( get_query_var( 'lines_auth_profile' ) == 1 ) :
		wp_enqueue_style( 'lines-auth-profile', LINES_AUTH_PLUGIN_URL . 'assets/css/profile.css' );
		wp_enqueue_script( 'lines-auth-profile', LINES_AUTH_PLUGIN_URL . 'assets/js/profile.min.js', array(), null, true );
		wp_localize_script( 'lines-auth-profile', 'linesAuth', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'lines_auth_update_profile' ),
		) );
	elseif ( get_query_var( 'lines_auth_reset' ) == 1 ) :
		wp_enqueue_style( 'lines-auth-reset', LINES_AUTH_PLUGIN_URL . 'assets/css/reset-password.css' );
		wp_enqueue_script( 'lines-auth-reset', LINES_AUTH_PLUGIN_URL . 'assets/js/reset-password.min.js', array(), null, true );
		wp_localize_script( 'lines-auth-reset', 'linesAuth', array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'lines_auth_reset_password' ),
			'redirectUrl' => home_url( '/login' )
		) );
	endif;

}

// Activation hook: create database tables and pages.
register_activation_hook( __FILE__, 'lines_auth_plugin_activate' );
/**
 * Plugin activation function.
 *
 * @return void
 */
function lines_auth_plugin_activate() {
	// Create user table.
	if ( class_exists( 'User_Model' ) ) :
		$user_model = new User_Model();
		$user_model->create_table();
	endif;
	// Create session table.
	if ( class_exists( 'Session_Model' ) ) :
		$session_model = new Session_Model();
		$session_model->create_table();
	endif;
	// Create password reset table.
	if ( class_exists( 'Reset_Password_Model' ) ) {
		( new Reset_Password_Model() )->create_table();
	}
	// Create required pages (except profile, which is handled via rewrite).
	$pages = array(
		'login-sign-up'      => 'Sign Up',
		'login'              => 'Login',
		'login-forgot'       => 'Forgot Password',
		'login-verify-email' => 'Verify Email',
	);
	foreach ( $pages as $slug => $title ) :
		$page = get_page_by_path( $slug );
		if ( ! $page ) :
			$page_data = array(
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			);
			wp_insert_post( $page_data );
		endif;
	endforeach;
	flush_rewrite_rules();
}

// Deactivation hook.
register_deactivation_hook( __FILE__, 'lines_auth_plugin_deactivate' );
/**
 * Plugin deactivation function.
 *
 * @return void
 */
function lines_auth_plugin_deactivate() {
	flush_rewrite_rules();
}

// Template redirect: load our custom controllers.
add_action( 'template_redirect', 'lines_auth_plugin_template_redirect' );
/**
 * Template redirect function for Lines Auth Plugin.
 *
 * Dispatches the appropriate controller based on the request.
 *
 * @return void
 */
function lines_auth_plugin_template_redirect() {
	if ( get_query_var( 'lines_auth_profile' ) == 1 ) :
		if ( class_exists( 'Profile_Controller' ) ) :
			$controller = new Profile_Controller();
			$controller->handle_request();
			exit;
		endif;
	elseif ( get_query_var( 'lines_auth_google' ) == 1 ) :
		if ( class_exists( 'Google_OAuth_Controller' ) ) :
			$controller = new Google_OAuth_Controller();
			$controller->handle_request();
			exit;
		endif;
	elseif ( get_query_var( 'lines_auth_logout' ) == 1 ) :
		if ( class_exists( 'Logout_Controller' ) ) :
			$controller = new Logout_Controller();
			$controller->handle_request();
			exit;
		endif;
	elseif ( is_page( 'login-sign-up' ) ) :
		if ( class_exists( 'Signup_Controller' ) ) :
			if ( lines_auth_plugin_is_logged_in() ) :
				wp_redirect( home_url( '/profile' ) );
				exit;
			endif;
			$controller = new Signup_Controller();
			$controller->handle_request();
			exit;
		endif;
	elseif ( is_page( 'login-forgot' ) ) :
		if ( class_exists( 'Forgot_Password_Controller' ) ) :
			if ( lines_auth_plugin_is_logged_in() ) :
				wp_redirect( home_url( '/profile' ) );
				exit;
			endif;
			$controller = new Forgot_Password_Controller();
			$controller->handle_request();
			exit;
		endif;
	elseif ( is_page( 'login-verify-email' ) ) :
		if ( class_exists( 'Verify_Email_Controller' ) ) :
			$controller = new Verify_Email_Controller();
			$controller->handle_request();
			exit;
		endif;
	elseif ( is_page( 'login' ) ) :
		if ( class_exists( 'Login_Controller' ) ) :
			if ( lines_auth_plugin_is_logged_in() ) :
				wp_redirect( home_url( '/profile' ) );
				exit;
			endif;
			$controller = new Login_Controller();
			$controller->handle_request();
			exit;
		endif;
	elseif(get_query_var('lines_auth_reset')==1 && class_exists('Reset_Password_Controller')) :
		(new Reset_Password_Controller())->handle_request();
		exit;		
	endif;
}

// AJAX endpoints for signup.
add_action( 'wp_ajax_nopriv_lines_auth_signup', 'lines_auth_plugin_ajax_signup' );
add_action( 'wp_ajax_lines_auth_signup', 'lines_auth_plugin_ajax_signup' );
/**
 * AJAX handler for user signup.
 *
 * @return void
 */
function lines_auth_plugin_ajax_signup() {
	if ( class_exists( 'Signup_Controller' ) ) :
		$controller = new Signup_Controller();
		$controller->process_ajax_signup();
	endif;
	wp_die();
}

add_action( 'wp_ajax_lines_auth_update_profile', 'lines_auth_plugin_ajax_update_profile' );

function lines_auth_plugin_ajax_update_profile() {
    if ( class_exists( 'Profile_Controller' ) ) {
        ( new Profile_Controller() )->process_ajax_update_profile();
    }
    wp_die();
}

// AJAX endpoint for reset password
add_action('wp_ajax_nopriv_lines_auth_reset_password', 'lines_auth_plugin_ajax_reset');
add_action('wp_ajax_lines_auth_reset_password', 'lines_auth_plugin_ajax_reset');
function lines_auth_plugin_ajax_reset(){
    $controller = new Reset_Password_Controller();
    $controller->process_ajax_reset();
    wp_die();
}
