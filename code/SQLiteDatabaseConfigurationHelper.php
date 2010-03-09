<?php
/**
 * This is a helper class for the SS installer.
 * 
 * It does all the specific checking for SQLiteDatabase
 * to ensure that the configuration is setup correctly.
 * 
 * @package sqlite3
 */
class SQLiteDatabaseConfigurationHelper implements DatabaseConfigurationHelper {

	/**
	 * Ensure that one of the database classes
	 * is available. If it is, we assume the PHP module for this
	 * database has been setup correctly.
	 * 
	 * @param array $databaseConfig Associative array of database configuration, e.g. "type", "path" etc
	 * @return boolean
	 */
	public function requireDatabaseFunctions($databaseConfig) {
		if($databaseConfig['type'] == 'SQLitePDODatabase' || version_compare(phpversion(), '5.3.0', '<')) return class_exists('PDO') ? true : false;
		return class_exists('SQLite3');
	}

	/**
	 * Ensure that the database server exists.
	 * @param array $databaseConfig Associative array of db configuration, e.g. "type", "path" etc
	 * @return array Result - e.g. array('success' => true, 'error' => 'details of error')
	 */
	public function requireDatabaseServer($databaseConfig) {
		if(is_writable($databaseConfig['path'])) {
			$success = true;
		} else {
			$success = false;
			$error = 'Webserver can\'t write database file to ' . $databaseConfig['path'];
		}

		return array(
			'success' => $success,
			'error' => $error
		);
	}

	/**
	 * Ensure a database connection is possible using credentials provided.
	 * @param array $databaseConfig Associative array of db configuration, e.g. "type", "path" etc
	 * @return array Result - e.g. array('success' => true, 'error' => 'details of error')
	 */
	public function requireDatabaseConnection($databaseConfig) {

		$success = false;
		$error = '';

		SQLite3Database::safe_dir($databaseConfig['path']);
		$file = $databaseConfig['path'] . '/' . $databaseConfig['database'];
		
		if($databaseConfig['type'] == 'SQLitePDODatabase' || version_compare(phpversion(), '5.3.0', '<')) {
			$conn = @(new PDO("sqlite:$file"));
		} else {
			$conn = @(new SQLite3($file, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE, $parameters['key']));
		}
		
		if($conn) {
			$success = true;
		} else {
			$success = false;
			$error = '';
		}
		
		return array(
			'success' => $success,
			'connection' => $conn,
			'error' => $error
		);
	}

	/**
	 * Ensure that the database connection is able to use an existing database,
	 * or be able to create one if it doesn't exist.
	 *
	 * Unfortunately, PostgreSQLDatabase doesn't support automatically creating databases
	 * at the moment, so we can only check that the chosen database exists.
	 * 
	 * @param array $databaseConfig Associative array of db configuration, e.g. "server", "username" etc
	 * @return array Result - e.g. array('success' => true, 'alreadyExists' => 'true')
	 */
	public function requireDatabaseOrCreatePermissions($databaseConfig) {
		$success = false;
		$alreadyExists = false;
		$canCreate = false;
		
		$check = $this->requireDatabaseConnection($databaseConfig);
		$conn = $check['connection'];
		
		if($conn) {
			$success = true;
			$alreadyExists = true;
		} else {
			$success = false;
			$alreadyExists = false;
		}

		return array(
			'success' => $success,
			'alreadyExists' => $alreadyExists,
		);
	}

}
