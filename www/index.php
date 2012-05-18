<?php
/**
 * Bike - framework
 *
 * An open source application development framework for PHP 5.3 or newer
 *
 * @author		Andrej Sevastianov
 * @copyright	Copyright (c) 2012
 * @since		Version 1.0
 * @filesource
 */

define('DEBUG', true );

// Site name
define('SITE', 'andrej.in.ua' );

// Basis url
define('BASEURL', 'http://' . $_SERVER['SERVER_NAME'] . '/' );

define('DIR_SITE', __DIR__.'/'.SITE.'/');
define('DIR_APP', '../system/');


// Starting
require_once DIR_APP.'Bootstrap.php';

/* End of file index.php */
/* Location: ./index.php */