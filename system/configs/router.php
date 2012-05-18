<?php if ( ! defined('SITE') ) exit('No direct script access allowed');

return array(
	'index' => 'blog',
	'standart' => array(
		'Admin' => true,
		'Blog' => true
	),
	'routes' => array(
//		'page/(:any)' => 'page/page/$1',

// Главная странаца блога с пагинацией
		'blog/([0-9]+)([a-z0-9\-/]*)' => 'blog/index/$1',
// Фильтр тега
		'blog/tag-([a-z0-9\-]+)([/0-9]*)' => 'blog/tag/$1/$2',
		'blog/tag-([a-z0-9\-]+)(.*)' => 'blog/tag/$1',
// Фильтр категории
		'blog/([a-z0-9\-]+)([/0-9]*)' => 'blog/category/$1/$2',
// Отдельная запись
		'blog/([a-z0-9\-]+)/([a-z0-9\-]+)' => 'blog/read/$2/$1',
	)
);

/* End of file router.php */
/* Location: ./configs/router.php */