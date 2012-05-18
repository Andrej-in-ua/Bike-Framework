<script>
function loadConfig(name)
{
	$('.file_active').removeClass('file_active');
	$(document.getElementById(name)).addClass('file_active');
	localStorage.setItem('admin-configs-last', name);
	
	$.get('<?=BASEURL?>admin/c/m/configs/configs/load/' + name, function(data) {
		$('#name').val(name);
		$('#config').html('<textarea name="config">'+data+'</textarea>');
	});
}
$(document).ready(function(){
	if ( localStorage.getItem('admin-configs-last') ) {
		loadConfig( localStorage.getItem('admin-configs-last') );
	}
}); 
</script>
<h2><a href="<?=BASEURL?>admin/"><span class="icon-previous"></span></a> Конфигурационные файлы</h2>
<?php Load::Msg()->show(); ?>
<ul class="files">
<?php foreach ( $configs as $name ): ?>
<li><span id="<?=$name?>" onclick="loadConfig('<?=$name?>');"><?=$name?></span></li>
<?php endforeach; ?>
</ul>
<form action="<?=BASEURL?>admin/c/m/configs/configs" method="POST">
<input type="hidden" name="name" id="name" />
<div id="config"><textarea name="config"></textarea></div>
<input type="submit" value="Сохранить изменения" />
</form>