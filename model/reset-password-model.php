<?php
/**
 * Reset Password Model for Lines Auth Plugin.
 *
 * @package LinesAuthPlugin
 */
class Reset_Password_Model {

    const TABLE_NAME = 'lines_auth_password_reset';

    public function create_table() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            userId INT NOT NULL,
            token VARCHAR(60) NOT NULL,
            created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires DATETIME NOT NULL,
            PRIMARY KEY(id),
            UNIQUE KEY(token)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public function create_reset( $userId, $token, $expires ) {
        global $wpdb;
        return $wpdb->insert( $wpdb->prefix . self::TABLE_NAME, [
            'userId'=>$userId, 'token'=>$token, 'expires'=>$expires
        ], ['%d','%s','%s'] );
    }

    public function get_by_token( $token ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}".self::TABLE_NAME." WHERE token=%s",
            $token
        ) );
    }

    public function delete_by_token( $token ) {
        global $wpdb;
        return $wpdb->delete( $wpdb->prefix . self::TABLE_NAME, ['token'=>$token], ['%s'] );
    }
}
