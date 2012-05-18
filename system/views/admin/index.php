<ul class="admin_main_menu"><?php
foreach ( $menu as $category ):
	?><li><span><?=$category['title']?></span><ul><?php
	foreach ($category['menu'] as $item ):
		?><li><a href="<?=( isset($item['link']) ? $item['link']
						: BASEURL.'admin/c/'.($item['type'] === 'controller' ? 'c' : 'm').'/'.$item['class']
							.( isset($item['method']) ? '/'.$item['method']	: '' )
			)?>"><?=( isset($item['img']) ? '<img src="'.$item['img'].'" />' : '' )?><?=$item['title']?><?=(
				isset($item['desc']) ? '<span>'.$item['desc'].'</span>' : '' )?></a></li><?php
	endforeach;
?></ul></li><?php
endforeach;
?></ul>