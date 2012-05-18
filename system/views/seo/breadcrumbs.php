<a href="<?=BASEURL?>">Главная</a>
<?php if ( ! isset($breadcrumbs) ) return;

foreach ($breadcrumbs as $b):
	if ( $b[0] !== '#' AND ! preg_match('#^https?://#i', $b[0]))
	{
		 $b[0] = BASEURL.ltrim($b[0], '/|\\');
	}
	?><span> » <a href="<?=$b[0]?>"><?=$b[1]?></a></span><?php
endforeach; ?>