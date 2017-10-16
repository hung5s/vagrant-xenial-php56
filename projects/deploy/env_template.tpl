<?php
$debugIPs = [
	// local environment
    '127.0.0.1',
    // remote ENV, this should be commented out in production environmant
    isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'',
];

// directory that holds all code, including customer specific code, this should NOT be inside web directory
defined ('ROOT_DIR') or define('ROOT_DIR', '{rootDir}');
defined ('CODE_DIR') or define('CODE_DIR', '{codeDir}');

// relative path to the site specific web accessible folder web/sites/
defined ('SITE_DIR') or define('SITE_DIR', '{siteDir}');

// database table prefixes
defined ('SITE_OWNER') or define('SITE_OWNER', '{siteOwner}');
defined ('SITE_ID') or define('SITE_ID', '{siteId}');
/***** STARTING DEFINITION FOR ALL DATABASES ******/
$dbs=array (
  'db' => 
	  array (
		'connectionString' => 'pgsql:host={dbHost};port={dbPort};dbname={dbName};',
		'username' => '{dbUser}',
		'password' => '{dbPassword}',
	  )
)
?>