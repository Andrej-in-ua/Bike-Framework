<script>
var roles = {
	data: $.parseJSON('<?=json_encode($rules)?>'),
	actions: $.parseJSON('<?=json_encode($actions)?>'),
	used: {},
	cur: null,
	load: function(){
		if ( roles.cur == null) return false;
		var data = '';
		var role = roles.data[roles.cur];
		roles.used = {};

		for ( var action in role )
		{
			data += '<tr><td>' + action + '</td><td><select name="action[' + action + ']">';
			if ( role[action] == true )
			{
				data += '<option value="true" selected>Да</option>'
						+'<option value="false">Нет</option>';
			} else {
				data += '<option value="true">Да</option>'
						+'<option value="false" selected>Нет</option>';
			}
			data += '<option value="null">Удалить</option></select></td>'
			+'<td>' + roles.actions[action] + '</td></tr>';
			roles.used[action] = true;
		}
		$(".result_table > tbody").html(data);
		$("#role").val(roles.cur);
	},
	newAction: function(){
		var data = '';
		for ( var action in roles.actions )
		{
			if ( roles.used[action] ) continue;
			data += '<option value="'+action+'">'+action+'</option>';
		}
		if ( data == '' ) return false;
		
		data = '<tr><td><select class="add_action" name="add_action[]"><option value=""></option>'
				+ data + '</select></td>'
				+'<td><select name="new_action[]"><option value="true">Да</option>'
				+'<option value="false"  selected>Нет</option></select></td>'
				+'<td></td></tr>';
		$(".result_table > tbody:last").append(data);
	},
	del: function(role){
		$( "#dialog:ui-dialog" ).dialog( "destroy" );
		$("#dialog-confirm").remove();
		$("body").append('<div id="dialog-confirm" title=""><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Вы уверены, что хотите удалить роль <strong>&quot;'+role+'&quot;</strong>?</p></div>');
		$( "#dialog-confirm" ).dialog({
			resizable: false,
			height:140,
			width:300,
			modal: true,
			title: 'Удаление...',
			buttons: {
				"Удалить": function() {
					$("body").append('<form method="POST" id="del_role"><input type="hidden" name="del_role" /></form>');
					$( "#del_role > input" ).val(role);
					$( "#del_role" ).submit();
					$( this ).dialog( "close" );
				},
				"Отмена": function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
}

$(document).ready(function(){
	$('.files > li > span').click(function(){
		$('.file_active').removeClass('file_active');
		$(this).addClass('file_active');
	
		roles.cur = $(this).attr('id');
		localStorage.setItem('last-roles', roles.cur);
		roles.load();
	});
	
	$('.files > li > span > .delete-icon').click(function(){
		roles.del($(this).parent().attr('id'));	
	});
	
	$(document.getElementById(localStorage.getItem('last-roles'))).click();
	
	$(".add_input").live('click',roles.newAction);
	
	$(".add_action").live('change',function(){
		var e = $(this).parent().next().next();
		var v = $(this).val();
		if ( v == '') e.html(''); else e.html(roles.actions[v]);
		
	});
}); 
</script>

<h2>Права доступа</h2>
<?php Load::Msg()->show(); ?>
<ul class="files"><?php

if ( true === $writable )
{
	?><li class="fr"><form method="POST">
	Новая роль: <input type="text" name="new_role" /><input type="submit" value="Добавить" />
	</form></li><?php
}

foreach ( $rules as $role => $action)
{
	?><li><span id="<?=$role?>"><?=$role?><div class="delete-icon"></div></span></li><?php
}

?></ul><?php

if ( true === $writable )
{
	?><form method="POST"><?php
}

?><table class="result_table">
<thead><th>Операция</th><th>Разрешения</th><th>Описание</th></thead>
<tbody></tbody></table><?php

if ( true === $writable )
{
	?><input type="hidden" id="role" name="role" />
	<p><span class="add_input fl">Новое правило</span>
	<input type="submit" value="Сохранить роль" class="fr" /></p>
	<div class="clearfix"></div>
	</form><?php
}

?><hr />
<small><h4>Базовый принцип работы</h4>
<p>1. У каждого пользователя может быть неограниченное количество ролей.</p>
<p>2. Если оперция запрещена хотя бы в одной из ролей считается, что субъекту данная операция запрещена.</p>
<p>3. Если операция не разрешена явно ни в одной из ролей, но и не запрещена явно, считается, что операция не описана.<br />
В данном случае сайт сам принимать решение.</p>
<strong>Важно!</strong><br />
Все роли равноправные, поэтому система не может сама контролировать противоречия.<br />
Например, если запретить гостям действие is_admin то администратор не сможет попасть в панель управления,
пока библиотека пользователей ему будет присваивать роль гостя.
<p><strong>Если вы не уверены, лучше проконсультируйтесь с разработчиком!</strong></p></small>