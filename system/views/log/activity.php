<script>
var activity = {
	count: 0,
	load: function() {
		$.ajax({
		  url: '<?=BASEURL?>admin/c/m/log/activity/load/' + activity.count,
		  type: "POST",
		  success: function(response) {
			var data = ''
			var tmp = activity.count;
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
				if (activity.count == 0){$('.getprev').hide();}else{$('.getprev').show();}
			},
		  dataType: 'json'
		});
	}
}
$(document).ready(function(){
	$('.getnext').click(function(){
		activity.count += 20;
		activity.load();
	});
	$('.getprev').click(function(){
		activity.count -= 20;
		activity.load();
	});
	activity.load();
}); 
</script>
<h2><a href="<?=BASEURL?>admin/"><span class="icon-previous"></span></a> Лог активности моего аккаунта</h2>
<?php Load::Msg()->show(); ?>
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