<?php if ( ! defined('SITE') ) exit('No direct script access allowed');

return array(
	'salt' => 'hash-salt',
	'use_db' => false,
	'admins' => array(
		'admin' => array(
			'id' => 1,
			'password' => '',
			'roles' => array(
				'god',
				'test',
			),
		),
		'test' => array(
			'id' => 2,
			'password' => 'test',
			'roles' => array(
				
			),
		),
	),
	'main_menu' => array(
		array(
			'title' => 'Мой профиль',
			'acl' => 'is_admin',
			'menu' => array(
				array(
					'title' => 'Мой профиль администратора',
					'desc' => 'Изминить настройки своего администраторского профиля',
					'img' => 'http://bike//img/admin/menu-profile.png',
					'type' => 'controller',
					'class' => 'admin',
					'method' => 'profile',
				),
				array(
					'title' => 'Мой лог доступа',
					'desc' => 'Посмотреть все действи совершенные мной в панели управления',
					'img' => 'http://bike//img/admin/menu-log.png',
					'type' => 'model',
					'class' => 'log',
					'method' => 'activity',
				),
				array(
					'title' => 'Помощь',
					'desc' => 'Справка по сайту и документация фреймворка',
					'img' => 'http://bike//img/admin/menu-help.png',
					'type' => 'link',
					'link' => 'http://andrej.in.ua/bike/help',
				),
				array(
					'title' => 'Выход',
					'desc' => 'Выйти из аккаунта администратора',
					'img' => 'http://bike//img/admin/menu-logout.png',
					'type' => 'logout',
					'class' => '',
				),
			),
		),
		array(
			'title' => 'Наполнение сайта',
			'menu' => array(
				array(
					'acl' => 'pages_add',
					'title' => 'Добавить страницу',
					'desc' => 'Добавить новую страницу на сайт',
					'img' => 'http://bike//img/admin/menu-pages-add.png',
					'type' => 'controller',
					'class' => 'pages',
					'method' => 'add',
				),
				array(
					'acl' => 'pages_moderator',
					'title' => 'Все страницы',
					'desc' => 'Управление страницами сайта',
					'img' => 'http://bike//img/admin/menu-pages.png',
					'type' => 'controller',
					'class' => 'pages',
					'method' => 'list',
				),
				array(
					'acl' => 'trash',
					'title' => 'Корзина',
					'desc' => 'Восстановление удаленных данных',
					'img' => 'http://bike//img/admin/menu-trash.png',
					'type' => 'model',
					'class' => 'trash',
					'method' => 'list',
				),
			),
		),
		array(
			'title' => 'Управление сайтом',
			'acl' => 'god',
			'menu' => array(
				array(
					'title' => 'Конфигурационные файлы',
					'desc' => 'Ручное редактирование конфигурационных файлов.<br />Требуются знаний языка PHP и структуры данного сайта!',
					'img' => 'http://bike//img/admin/menu-configs.png',
					'type' => 'model',
					'class' => 'configs',
					'method' => 'configs',
				),
				array(
					'title' => 'Системные записи',
					'desc' => 'Просмотр логов системных ошибок и активности администраторов данного сайта',
					'img' => 'http://bike//img/admin/menu-logs.png',
					'type' => 'model',
					'class' => 'log',
					'method' => 'logs',
				),
			),
		),
		array(
			'title' => 'Управление пользователями',
			'acl' => 'god',
			'menu' => array(
				array(
					'title' => 'Права доступа',
					'desc' => 'Настройка прав доступа к разделам сайта на основе пользовательских ролей',
					'img' => 'http://bike//img/admin/menu-acl.png',
					'type' => 'model',
					'class' => 'acl',
					'method' => 'roles',
				),
			),
		),
	),
);

/* End of config file */
/* Created automatic by Lib_Config.php (2012/02/10 16:40:26) */