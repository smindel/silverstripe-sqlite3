<?php

if(defined('SS_DATABASE_CLASS') && (SS_DATABASE_CLASS == 'SQLiteDatabase' || SS_DATABASE_CLASS == 'SQLite3Database' || SS_DATABASE_CLASS == 'SQLitePDODatabase')) {

	global $databaseConfig;
	$databaseConfig = array(
		'database' => (defined('SS_DATABASE_PREFIX') ? SS_DATABASE_PREFIX : '') . $database . (defined('SS_DATABASE_SUFFIX') ? SS_DATABASE_SUFFIX : ''),
		'path' => defined('SS_SQLITE_DATABASE_PATH') && SS_SQLITE_DATABASE_PATH ? SS_SQLITE_DATABASE_PATH : ASSETS_PATH,   // where to put the database file
		'memory' => true,    // run tests in memory
	);
	
	/**
	 * set pragma values on the connection.
	 * @see http://www.sqlite.org/pragma.html
	 */
	SQLite3Database::$default_pragma = array(
		'encoding' => '"UTF-8"',
		'locking_mode' => 'NORMAL',
	);
	

	// The SQLite3 class is available in PHP 5.3 and newer
	if(SS_DATABASE_CLASS == 'SQLitePDODatabase' || version_compare(phpversion(), '5.3.0', '<')) {
		$databaseConfig['type'] = 'SQLitePDODatabase';
	} else {
		$databaseConfig['type'] = 'SQLite3Database';
		$databaseConfig['key']  = defined('SS_SQLITE_DATABASE_KEY')  && SS_SQLITE_DATABASE_KEY  ? SS_SQLITE_DATABASE_KEY :  'SQLite3DatabaseKey';
	}
}

