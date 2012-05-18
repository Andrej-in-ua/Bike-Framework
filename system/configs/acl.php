<?php if ( ! defined('SITE') ) exit('No direct script access allowed');

return array(
	'rules' => array(
		'admin' => array(
			'is_admin' => true,
			'pages_add' => true,
			'pages_moderator' => true,
		),
		'test' => array(
			'is_admin' => true,
			'trash' => true,
			'pages_add' => true,
		),
		'guest' => array(
		),
	),
	'actions' => array(
		'is_admin' => 'Базовый доступ к административным функциям',
		'app_config_write' => 'Возможность редактировать конфигурационные файлы фреймворка',
		'site_config_write' => 'Возможность редактировать конфигурационные файлы текущего сайта',
		'pages_add' => 'Доступ к добавлению страниц',
		'pages_publish' => 'Публикация и редактирование своих страниц без модерирования',
		'pages_draft' => 'Просмотр чужих черновиков',
		'pages_moderator' => 'Модерирование страниц',
	),
);

/* End of config file */
/* Created automatic by Lib_Config.php (2012/02/11 18:23:19) */