<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-language" content="ru">
<title><?=(!empty($title)?$title.'  :: ':'')?>Bike Framework</title>


<link rel="stylesheet" href="<?=BASEURL?>css/style.css" type="text/css" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
<script language="javascript">var BASEURL = '<?=BASEURL?>';</script>
<script type="text/javascript" src="<?=BASEURL?>js/main.js"></script>
<?php Core::event('head'); ?>
</head>

<?php ?>
<body>
<div id="loading"><img src="<?=BASEURL?>img/ajax.gif" /> Обмен данными с сервером</div>
<div id="wrapper">
	<div id="header">
		<h1>Bike Framework</h1>
	</div>
	
	<div id="breadcrumbs"><?php
	
	Core::event('breadcrumbs'); 
	if ( $user = Load::Acl()->getCurrentUser() ):
		?><div class="right"><strong><?=$user['login']?></strong> <small>(ID:<?=$user['id']?>)</small> (<a href="<?=BASEURL?>admin/logout">Выход</a>)</div><?php
	endif;
	
	if ( Load::Acl()->check('is_admin') ):
		?><div class="right"><a href="<?=BASEURL?>admin/">Панель управления</a></div><?php
		
	endif;
	
	if ( DEBUG ):
		?><div class="right"><a href="javascript:void(0);" id="console-open">Console</a></div><?php
	endif;
	?></div><div id="content"><?php if ( $msg = Load::Msg() ){ $msg->show(); } ?><?=$content?></div>
</div>
</body>
</html>