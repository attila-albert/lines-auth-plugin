<?php
/**
 * Session Model for Lines Auth Plugin.
 *
 * Handles database operations for session tokens.
 *
 * @package LinesAuthPlugin
 */
class Session_Model {

	const TABLE_NAME = 'lines_auth_session';

	/**
	 * Create the session table.
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;
		$table_name     = $wpdb->prefix . self::TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			userId INT(11) NOT NULL,
			token VARCHAR(60) NOT NULL,
			created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			ip4 VARCHAR(255) NULL,
			useragent VARCHAR(255) NULL,
			expires TIMESTAMP NULL DEFAULT NULL,
			PRIMARY KEY (id),
			KEY token (token)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Create a session record.
	 *
	 * @param int    $userId
	 * @param string $token
	 * @param string $ip4
	 * @param string $useragent
	 * @param string $expires
	 * @return bool|int Insert ID on success, false on failure.
	 */
	public function create_session( $userId, $token, $ip4, $useragent, $expires ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$data = array(
			'userId'    => $userId,
			'token'     => $token,
			'ip4'       => $ip4,
			'useragent' => $useragent,
			'expires'   => $expires,
		);
		$format = array( '%d', '%s', '%s', '%s', '%s' );
		$result = $wpdb->insert( $table_name, $data, $format );
		if ( $result ) {
			return $wpdb->insert_id;
		}
		return false;
	}

	/**
	 * Retrieve a session by token.
	 *
	 * @param string $token
	 * @return object|null The session record or null if not found.
	 */
	public function get_session( $token ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE token = %s", $token ) );
	}

	/**
	 * Delete expired sessions.
	 *
	 * @return void
	 */
	public function delete_expired_sessions() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$wpdb->query( "DELETE FROM $table_name WHERE expires < NOW()" );
	}

	/**
	 * Delete a session by token.
	 *
	 * @param string $token
	 * @return int Number of rows deleted.
	 */
	public function delete_session_by_token( $token ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		return $wpdb->delete( $table_name, array( 'token' => $token ), array( '%s' ) );
	}
}
