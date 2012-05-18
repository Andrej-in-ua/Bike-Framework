<?php if ( ! defined('SITE') ) exit('No direct script access allowed');

return array(

	'auth' => array(
		array('load' => 'admin', 'method' => 'auth')
	),
	
	'login' => array(
		array('load' => 'admin', 'method' => 'login')
	),
	
	'logout' => array(
		array('load' => 'admin', 'method' => 'logout')
	),
	
	
	'breadcrumbs' => array(
		array('load' => 'seo', 'method' => 'viewBreadcrumbs')
	),
	
	'head' => array(
		array('load' => 'seo', 'method' => 'viewPageHead')
	),

);

/* End of file call.php */
/* Location: ./configs/call.php */