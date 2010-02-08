<?php

global $databaseConfig;
if(defined('SS_DATABASE_CLASS')) $databaseConfig['type'] = SS_DATABASE_CLASS;

if(array_search($databaseConfig['type'], array('SQLiteDatabase', 'SQLite3Database', 'SQLitePDODatabase')) !== false) {

	$databaseConfig = array(
		'type' => $databaseConfig['type'],
		'database' => (defined('SS_DATABASE_PREFIX') ? SS_DATABASE_PREFIX : '') . $database . (defined('SS_DATABASE_SUFFIX') ? SS_DATABASE_SUFFIX : ''),
		'path' => defined('SS_SQLITE_DATABASE_PATH') && SS_SQLITE_DATABASE_PATH ? SS_SQLITE_DATABASE_PATH : ASSETS_PATH . '/.sqlitedb/',   // where to put the database file
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
	if($databaseConfig['type'] == 'SQLitePDODatabase' || version_compare(phpversion(), '5.3.0', '<')) {
		$databaseConfig['type'] = 'SQLitePDODatabase';
	} else {
		$databaseConfig['type'] = 'SQLite3Database';
		$databaseConfig['key']  = defined('SS_SQLITE_DATABASE_KEY')  && SS_SQLITE_DATABASE_KEY  ? SS_SQLITE_DATABASE_KEY :  'SQLite3DatabaseKey';
	}
}

