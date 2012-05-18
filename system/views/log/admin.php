<script>
var log = {
	count: 0,
	patch: '',
	load: function() {
		$.ajax({
			url: '<?=BASEURL?>admin/c/m/log/logs/load/' + log.count,
  			type: "POST",
			data: {patch : log.patch},
			success: function(response) {
				if ( response == null ) return false;
				var data = ''
				var tmp = log.count;
					for (var i in response)
					{
						tmp += 1; 
						data += '<tr><td class="numbering">' + tmp + '</td><td>' + response[i][0] 
						+'</td><td>' + response[i][1] 
						+'</td><td>' + response[i][3]
						+'</td><td>' + response[i][2] +'</td></tr>';
					}
	
					$(".result_table > tbody").html(data);
					$(".result_table > tbody > tr").click(function(){
						if ( $(this).hasClass('selected') )	{
							$(this).removeClass('selected');
						} else {
							$(this).addClass('selected');
						}
					});
					if (response.length < 20){$('.getnext').hide();}else{$('.getnext').show();}
					if (log.count == 0){$('.getprev').hide();}else{$('.getprev').show();}
				},
			dataType: 'json'
		});
	}
}
$(document).ready(function(){
	$('.files > li > span').click(function(){
		$('.file_active').removeClass('file_active');
		$(this).addClass('file_active');
		log.patch = $(this).html();
		localStorage.setItem('last-log', log.patch);
		log.count = 0;
		log.load();
	});
	$('.getnext').click(function(){
		log.count += 20;
		log.load();
	});
	$('.getprev').click(function(){
		log.count -= 20;
		log.load();
	});
	if ( log.patch = localStorage.getItem('last-log') )
	{
		$(document.getElementById(log.patch)).addClass('file_active');
		log.load();
	}
}); 
</script>
<h2><a href="<?=BASEURL?>admin/"><span class="icon-previous"></span></a> Просмотр системных логов</h2>
<?php Load::Msg()->show(); ?>
<ul class="files">
<?php foreach ( $files as $file ): ?>
<li><span id="<?=$file?>"><?=$file?></span></li>
<?php endforeach; ?>
</ul>

<table class="result_table">
<thead>
	<th class="numbering">#</th>
	<th title="Дата и время по серверу">Дата</th>
	<th>IP</th>
	<th>Сообщение системы</th>
	<th>Адрес страницы</th>
</thead>
<tbody></tbody>
</table>
<span class="getprev">Более новые записи</span>
<span class="getnext">Более старые записи</span>
<div class="clearfix"></div>