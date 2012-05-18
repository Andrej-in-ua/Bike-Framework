<?php if ( ! defined('SITE') ) exit('No direct script access allowed');

return array(
	'index' => 'pages/index',
	'standart' => array(
		'Admin' => true,
		'Blog' => true,
		'Pages' => true
	),
	'routes' => array(
		'pages/add' => 'pages/add',
		'pages/(:any)' => 'pages/index/$1',

// Главная странаца блога с пагинацией
		'blog/([0-9]+)([a-z0-9\\-/]*)' => 'blog/index/$1',
// Фильтр тега
		'blog/tag-([a-z0-9\\-]+)([/0-9]*)' => 'blog/tag/$1/$2',
		'blog/tag-([a-z0-9\\-]+)(.*)' => 'blog/tag/$1',
// Фильтр категории
		'blog/([a-z0-9\\-]+)([/0-9]*)' => 'blog/category/$1/$2',
// Отдельная запись
		'blog/([a-z0-9\\-]+)/([a-z0-9\\-]+)' => 'blog/read/$2/$1',
	)
);

/* End of file router.php */
/* Location: ./configs/router.php */