<?php if ( ! defined('SITE') ) exit('No direct script access allowed');

return array(
	'use_login'		=> 'email',			// Login, email or false
	'confirm_email'	=> true,			// Only if use_login = email
	
	'use_oauth'		=> true,			// Use social services
	'oauth_libs'	=> array(			// Library for social services
	
		'vk'	=> 'Vk_api',			// vk.com
		'fb'	=> 'Fb_api',			// facebook.com
		'tw'	=> 'Tw_api',			// twitter.com
		'ok'	=> 'Ok_api',			// odnoklassniki.ru
		'gl'	=> 'Google_api',		// google.com
		'ya'	=> 'Yandex_api'			// yandex.ru
		
	),
);

/* End of config file */
/* Created automatic by Lib_Config.php (2011/12/08 22:05:08) */