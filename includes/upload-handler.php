<?php
/**
 * Handles image uploads for FilePond in Lines Auth Plugin.
 *
 * Supports a 2-step flow:
 * 1) lines_auth_temp_upload_image => saves to tmp subfolder
 * 2) lines_auth_finalize_upload   => move/rename to final subfolder & update user
 *
 * @package LinesAuthPlugin
 */

add_action( 'wp_ajax_lines_auth_temp_upload_image', 'lines_auth_plugin_handle_temp_upload' );
add_action( 'wp_ajax_nopriv_lines_auth_temp_upload_image', 'lines_auth_plugin_handle_temp_upload' );

add_action( 'wp_ajax_lines_auth_finalize_upload', 'lines_auth_plugin_finalize_upload' );
add_action( 'wp_ajax_nopriv_lines_auth_finalize_upload', 'lines_auth_plugin_finalize_upload' );

/**
 * Step 1: Upload file to /user-profiles/tmp/
 */
function lines_auth_plugin_handle_temp_upload() {
    $user = lines_auth_plugin_get_current_user();
    if ( ! $user ) {
        wp_send_json_error( 'Not logged in.' );
    }

    if ( empty( $_FILES['filepond'] ) || $_FILES['filepond']['error'] !== UPLOAD_ERR_OK ) {
        wp_send_json_error( 'Temp upload failed.' );
    }

    $file = $_FILES['filepond'];

    // Basic extension check (no fileinfo).
    $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
    $allowed_exts = [ 'jpg','jpeg','png','webp','gif' ];
    if ( ! in_array( $ext, $allowed_exts, true ) ) {
        wp_send_json_error( 'Unsupported file type.' );
    }

    // Save in /user-profiles/tmp/
    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/user-profiles/tmp/';
    $target_url = $upload_dir['baseurl'] . '/user-profiles/tmp/';

    if ( ! file_exists( $target_dir ) ) {
        wp_mkdir_p( $target_dir );
    }

    // Unique name in tmp subfolder
    $tempName = sha1_file( $file['tmp_name'] . microtime(true)) . '.' . $ext;
    $tmpPath = $target_dir . $tempName;

    if ( ! move_uploaded_file( $file['tmp_name'], $tmpPath ) ) {
        wp_send_json_error( 'Could not write temp file.' );
    }

    wp_send_json_success([
        'tempUrl'  => $target_url . $tempName,
        'tempName' => $tempName,
    ]);
}

/**
 * Step 2: Move final from /tmp/ => /user-profiles/
 *         Update user profile field
 */
function lines_auth_plugin_finalize_upload() {
    $user = lines_auth_plugin_get_current_user();
    if ( ! $user ) {
        wp_send_json_error( 'Not logged in.' );
    }

    $tempName = sanitize_text_field( $_POST['tempName'] ?? '' );
    $field = preg_replace( '/[^a-zA-Z0-9_]/', '', $_POST['field'] ?? '' );

    $allowed_fields = [
        'profileImage' => 'profile_photo_',
        'coverImage'   => 'cover_photo_',
    ];
    if ( ! isset( $allowed_fields[$field] ) ) {
        wp_send_json_error( 'Invalid field.' );
    }

    if ( empty( $tempName ) ) {
        wp_send_json_error( 'No temp file provided.' );
    }

    // Move from /tmp -> final
    $upload_dir = wp_upload_dir();
    $tmpDir   = $upload_dir['basedir'] . '/user-profiles/tmp/';
    $finalDir = $upload_dir['basedir'] . '/user-profiles/';
    $finalUrl = $upload_dir['baseurl']  . '/user-profiles/';

    if ( ! file_exists( $finalDir ) ) {
        wp_mkdir_p( $finalDir );
    }

    $tempPath = $tmpDir . $tempName;
    if ( ! file_exists( $tempPath ) ) {
        wp_send_json_error( 'Temp file not found.' );
    }

    // Generate final hashed name
    $ext = strtolower( pathinfo( $tempName, PATHINFO_EXTENSION ) );
    $hash = sha1_file( $tempPath . microtime(true));
    $prefix = $allowed_fields[$field];
    $finalName = $prefix . $hash . '.' . $ext;

    $finalPath = $finalDir . $finalName;

    if ( ! rename( $tempPath, $finalPath ) ) {
        wp_send_json_error( 'Could not finalize file.' );
    }

    // Update user field
    $model = new User_Model();
    $model->update_user( $user->id, [
        $field => $finalUrl . $finalName
    ]);

    wp_send_json_success([
        'url' => $finalUrl . $finalName
    ]);
}
