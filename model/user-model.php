<?php
/**
 * User Model for Lines Auth Plugin.
 *
 * @package LinesAuthPlugin
 */
class User_Model {

    const TABLE_NAME = 'lines_auth_users';
    const MIN_PASSWORD_LENGTH = 8;

    /**
     * Create the custom users table.
     */
    public function create_table() {
        global $wpdb;
        $table_name     = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();
    
        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            username VARCHAR(32) NOT NULL,
            passwordHash VARCHAR(60) NOT NULL,
            role TINYINT(4) NOT NULL,
            inserted DATETIME NOT NULL,
            invitedBy INT(11) DEFAULT NULL,
            email VARCHAR(255) NOT NULL,
            name VARCHAR(64) NOT NULL,
            notificationsFlags INT(11) DEFAULT 0,
            flags INT(11) DEFAULT 0,
            birth DATE NOT NULL,
            profileImage VARCHAR(255) DEFAULT NULL,
            source VARCHAR(255) DEFAULT '',
            PRIMARY KEY  (id),
            UNIQUE KEY username (username),
            UNIQUE KEY email (email)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }    

    /**
     * Create a new user.
     *
     * @param array $data Associative array: username, passwordHash, role, email, name, birth, source.
     * @return int|false Insert ID or false.
     */
    public function create_user( array $data ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;
        $formats = array(
            '%s','%s','%d','%s','%s','%s','%s'
        );
        return $wpdb->insert( $table, $data, $formats ) ? $wpdb->insert_id : false;
    }

    /**
     * Update user record.
     *
     * @param int   $user_id
     * @param array $fields
     * @return int|false Rows updated or false.
     */
    public function update_user( $user_id, array $fields ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;
        $formats = array_map( fn($v) => $v === 'passwordHash' ? '%s' : '%s', array_keys($fields) );
        return $wpdb->update( $table, $fields, [ 'id' => $user_id ], $formats, [ '%d' ] );
    }

    /**
     * Fetch user by ID.
     */
    public function get_by_id( int $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 
            "SELECT * FROM {$wpdb->prefix}".self::TABLE_NAME." WHERE id = %d", $id 
        ) );
    }

    /**
     * Fetch user by email.
     */
    public function get_by_email( string $email ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}".self::TABLE_NAME." WHERE email = %s", $email
        ) );
    }

    /**
     * Fetch user by username.
     */
    public function get_by_username( string $username ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}".self::TABLE_NAME." WHERE username = %s", $username
        ) );
    }

    /**
     * Delete user by ID.
     */
    public function delete_user( int $id ) {
        global $wpdb;
        return $wpdb->delete( $wpdb->prefix . self::TABLE_NAME, [ 'id' => $id ], [ '%d' ] );
    }

}
