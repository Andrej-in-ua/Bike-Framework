<?php if ( ! defined('SITE')) exit('No direct script access allowed');
/**
 * System Initialization File
 *
 * Loads the base classes and executes the request.
 *
 * @author		Andrej Sevastianov
 * @copyright	Copyright (c) 2011
 * @version		0.1.0
 */

/**
 * ------------------------------------------------------
 *  Buffering output
 * ------------------------------------------------------
 */
//sleep(5);
/* TO-DO: Time zone */
	date_default_timezone_set('Europe/Kiev');

/**
 * ------------------------------------------------------
 *  Define dasic constants
 * ------------------------------------------------------
 */
 	if ( ! defined('CORE_VERSION') )	define('CORE_VERSION', '0.2.0');
 	if ( ! defined('DEBUG') ) 			define('DEBUG', false);
	if ( ! defined('DIR_APP') ) 		define('DIR_APP', __DIR__.'/');
	if ( ! defined('DIR_SITE') )		define('DIR_SITE', __DIR__.'/'.SITE.'/');
	if ( ! defined('BASEPATH') )
	{
		$tmp = parse_url(BASEURL);
		define('BASEPATH', rtrim($tmp['path'], '/'));
		unset($tmp);
	}
/**
 * ------------------------------------------------------
 *  Require system files
 * ------------------------------------------------------
 */
	require DIR_APP.'Core.php';
	require DIR_APP.'Load.php';
/**
 * ------------------------------------------------------
 *  If we are still alive - run Bike Core
 * ------------------------------------------------------
 */
 	Core::run();
	Core::creatpage();

/* End of file Bootstrap.php */
/* Location: ./Bootstrap.php */

function pr($v){echo '<pre>'.print_r($v,1).'</pre>';}