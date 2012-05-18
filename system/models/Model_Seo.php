<?php  if ( ! defined('SITE')) exit('No direct script access allowed');

class Model_Seo
{
	function __construct()
	{
		
	}
	
	public function viewBreadcrumbs()
	{
		$meta = Core::getKey('meta');
		Load::View('seo/breadcrumbs', Core::getKey('meta'));
	}
	
	public function viewPageHead()
	{
		$meta = Core::getKey('meta');
		
		if ( isset($meta['js']) AND count($meta['js']) > 0 )
			foreach ($meta['js'] as $c ){ ?><script type="text/javascript" src="<?=BASEURL?>js/<?=$c?>.js"></script><?php }

		if ( isset($meta['css']) AND count($meta['css']) > 0 )
			foreach ($meta['css'] as $c ) { ?><link rel="stylesheet" href="<?=BASEURL?>css/<?=$c?>.css" type="text/css" /><?php }
		
		echo '<meta name="generator" content="Bike-Framework" />';
		if ( isset($meta['robots']) AND $meta['robots'] !== '' ) echo '<meta name="robots" content="'.$meta['robots'].'" />';
		if ( isset($meta['description']) AND $meta['description'] !== '' ) echo '<meta name="description" content="'.$meta['description'].'" />';
		if ( isset($meta['keywords']) AND $meta['keywords'] !== '' ) echo '<meta name="keywords" content="'.$meta['description'].'" />';
	}
}