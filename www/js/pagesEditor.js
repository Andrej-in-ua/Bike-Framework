var pagesEditor = {
	url: BASEURL + 'pages/',		// Ajax uri
	status: 0,							// Текущая вкладка
	moderator: false,					// Возможность модерирования
	
	locked: 0,							// Время блокировки
	lockedTimer: null,					// Таймер продления блокировки
	
	contentOriginal: null,				// Контент перед началом редактирования
	editor: null,						// Объект визуального редактора
	
	version: null,						// Текущая версия страницы
	activeVersion: null,				// Опубликованная версия страницы
	latestVersion: null,				// Последняя версия страницы
	id: '0',							// ID номер страницы
	
	historyCash: null,					// Закладка истории
	
	tabHide: function(){},				// Функция завершения текущей вкладки
	
/**
 * Initialization
 **/
	init: function(v, aV, id, moderator)
	{
	
		
		this.version = v;
		this.activeVersion = aV;
		this.id = id;
		this.moderator = ( moderator === 'true' ? true : false );

		
		$('#pages-content').before('<div class="panel" id="editor">'
			+ '<span id="editor-view" class="active">Просмотр</span>'
			+ '<span id="editor-edit" class="point">Редактирование</span>'
			+ ( this.moderator ? '<span id="editor-moderate" class="point">Управление</span>' : '' )
			+ '<span id="editor-history" class="point">История</span>'
			+ '<span id="editor-cancel" style="display: none;" class="point fr">Отменить</span>'
			+ '<span id="editor-save" style="display: none;" class="point fr">Сохранить</span>'
			+ '</div>'
		
			+ '<div class="panel-meta" id="editor-meta"></div>'
		
			+ '<div id="editor-info"></div><div class="clearfix"></div>'
		
			+ '<div id="editor-history-content" style="display:none;"><img src="' + BASEURL + '/img/ajax.gif"> Идет загрузка данных...</div>'
			+ '<div id="editor-moderate-content" style="display:none;"><img src="' + BASEURL + '/img/ajax.gif"> Идет загрузка данных...</div>');
		
		
		$('#editor-view').on('click', function(){pagesEditor.tabView()});
		$('#editor-edit').on('click', function(){pagesEditor.tabEdit()});
		$('#editor-history').on('click', function(){pagesEditor.tabHistory()});
		$('#editor-moderate').on('click', function(){pagesEditor.tabModerate()});
		
		$('#editor-save').bind('click', function(){pagesEditor.save()});
		$('#editor-cancel').bind('click', function(){pagesEditor.cancel()});
		$('#editor-meta').html('Текущая версия страницы: ' + this.version);
	},
	
/**
 * Закладка просмотра страниці
 **/	
	tabView: function()
	{
		if ( this.status == 0 ) return false;
		this.status = 0;
		
		// Завершаем активную вкладку
		this.tabHide();
		
		// Переключаем закладки
		$('#editor span.active').toggleClass('point active')
		$('#editor-view').toggleClass('point active');
	},

/**
 * Закладка редактирования
 **/
	tabEdit: function()
	{
		if ( this.status == 1 ) return false;
		
		// Проверяем возможность монополизировать (заблокировать) страницу
		if ( this.locked <= new Date().getTime() )	{
			return this.lock( function(){ pagesEditor.tabEdit() } );
		}
		
		// Завершаем активную вкладку
		this.tabHide();
		
		// Сохраняем первоначальное состояние страницы
		if ( this.contentOriginal == null) {
			this.contentOriginal = $('#pages-content').html();
		}
		
		// Узнаем последнюю версию страницы
		if ( this.latestVersion == null ) {
			this.checkLatestVersion();
		}

		// Визуальный редактор
		this.editor = new nicEditor({fullPanel : true}).panelInstance('pages-content',{hasPanel : true});
		
		// Переключаем вкладку
		$('#editor-view').html('Предпросмотр');

		$('#editor span.active').toggleClass('point active');
		$('#editor-edit').toggleClass('point active');
		
		$('#editor-save').show();
		$('#editor-cancel').show();
		this.status = 1;
		
		// Регистрируем функцию завершения вкладки
		this.tabHide = function()
		{
			this.editor.removeInstance('pages-content');
	 		this.editor = null;

			pagesEditor.tabHide = function(){};
		};
	
	},

	lock: function(callback)
	{
		$.ajax({
			url: pagesEditor.url + 'locked/' + pagesEditor.id,
			type: "POST",
			success: function(r)
			{
				pagesEditor.locked = 0;
				if ( r.error || ! r.locked || r.locked == 0 )
				{
					msg.ajaxError(r.error, 10, '#editor-info')
					return false;
				}
								
				pagesEditor.locked = new Date().getTime() + ( r.locked * 1000 );
				pagesEditor.lockedTimer = window.setTimeout(pagesEditor.lock, ((r.locked/2)*1000));
				
				if ( typeof callback == 'function' ) callback();
			},
			dataType: 'json'
		});
	},
	
	cancel: function()
	{
		this.tabHide();
		
		$('#editor-view').html('Просмотр');
		$('#editor-save').hide();
		$('#editor-cancel').hide();
			
		this.locked = 0;
		window.clearTimeout(this.lockedTimer);
		$('#pages-content').html(this.contentOriginal);
		this.tabView();
	},
	
	save: function()
	{
		this.tabHide();
		
		var content = $('#pages-content').html()
		
		this.cancel();
		
		
		if ( this.contentOriginal == content || ( this.contentOriginal == '' && content == '<br>' ) ){
			msg.add('Версия страницы не изменилась', 'notice', 10, '#editor-info');
			return false;
		}
		
		$.ajax({
			url: pagesEditor.url + 'save/' + pagesEditor.id,
			type: "POST",
			data: { content: content },
			dataType: 'json',
			success: function(r)
			{
				if ( r.error )
				{
					msg.ajaxError(r.error, 10, '#editor-info')
					return false;
				}
				
				var id = msg.add('<strong>Новая версия страницы сохранена!</strong><br />'
									+ 'Вы можете ее опубликовать во вкладке "История версий"',
									'ok', 0, '#editor-info');
				$('.panel .point').click(function(){ id.hide(); })
				pagesEditor.getHistory();
			}
		});
	},

	checkLatestVersion: function()
	{
		$.ajax({
			url: pagesEditor.url + 'latest/' + pagesEditor.id,
			type: "GET",
			dataType: 'json',
			success: function(r)
			{
				if ( r.error )
				{
					msg.ajaxError(r.error, 10, '#editor-info')
					return false;
				}
				
				pagesEditor.latestVersion = r.version; 
				if ( pagesEditor.latestVersion != pagesEditor.version )
				{
					var id = msg.add('В черновиках есть более новая версия страницы. Хотите ее загрузить?<br />'
						+ '<span class="button" id="latestversion-load">Да, загрузить</span>'
						+ '<span class="button" id="latestversion-hide">Нет</span>', 'notice', 0, '#editor-info');
					
					$('.panel .point').click(function(){ id.hide(); })
					
					$('#latestversion-hide').click(function(){ id.hide(); });
					
					$('#latestversion-load').click(function(){
						id.hide();
						pagesEditor.getVersion(pagesEditor.latestVersion);							
					});
				}
			},
		});
	},
	
	getVersion: function(version)
	{
		$.ajax({
			url: pagesEditor.url + 'getversion/' + pagesEditor.id,
			type: "GET",
			data: {version: version},
			dataType: 'json',
			success: function(r)
			{
				if ( r.error )
				{
					msg.ajaxError(r.error, 10, '#editor-info')
					return false;
				}
				
				pagesEditor.cancel();
				pagesEditor.version = version;
				pagesEditor.contentOriginal = null;
				$('#pages-content').html(r.content);
				$('#editor-meta').html('Текущая версия страницы: ' + pagesEditor.version + '<br />Опубликованная версия: ' + pagesEditor.activeVersion);
			},
		});
	},

/**
 * Закладка модерирования
 **/
	tabModerate: function()
	{
		if ( this.status == 4 ) return false;
		this.status = 4;
		this.tabHide();
		
		// Переключаем закладки
		$('#editor span.active').toggleClass('point active')
		$('#editor-moderate').toggleClass('point active');
		
		$('#pages-content').hide();
		$('#editor-moderate-content').show();
		this.tabHide = function()
		{
			$('#editor-moderate-content').hide();
			$('#pages-content').show();
			pagesEditor.tabHide = function(){};
		};
		
		if( this.moderate.data === null ) this.moderate.form();
	},
	
	moderate: {
		data: null,
		
		form: function()
		{
			if( this.data === null ) return this.getMeta(function(){pagesEditor.moderate.form();});
			
			var bc = '';
			for ( var b in this.data.meta.breadcrumbs )
			{
				bc += '<li>» <a href="' + this.data.meta.breadcrumbs[b][0] + '">' + this.data.meta.breadcrumbs[b][1] + '</a></li>';
			}
			
			$('#editor-moderate-content').html(
				//------------------
				'<fieldset class="standart fr" style="width:260px">'
				+ '<legend>Действия</legend>'
				+ '<span class="button" id="editor-moderate-save"><span class="icon-save">Сохранить все изменения</span></span>'
				+ ( this.data.status != '0' ? ' <span class="button" id="editor-moderate-draft"><span class="icon-draft">Отправить в черновики</span></span>' : '' )
		//		+ '<span class="button"><span class="icon-users">Сменить владельца</span></span>'
/* TODO:
	Реализовать смену владельца
	после того, как будет готов контроллер пользователей
*/			
				+ '<span class="button" id="editor-moderate-delete"><span class="icon-trash">Удалить страницу</span></span>'
				+ '</fieldset>'
				//------------------
				+ '<fieldset class="standart">'
				+ '<legend>Общая информация</legend>'
				+ '<p>Статус страницы: ' + ( this.data.status == '0' ? 'Черновик'
						: ( this.data.status == '1' ? '<strong>Опубликована</strong>'
							: ( this.data.status == '2' ? '<em>Модерируется</em>'
								: ( this.data.status == '3' ? 'В очереди на модерацию' : 'Неизвестен' )
						)
					)
				)
				+ '</p>'
				+ '<p>Владелец страницы: <strong>' + this.data.owner_login + '</strong> '
					+ '<small>(ID:' + this.data.owner_id + ')</small></p>'
				+ '<p>Актуальная версия страницы: <strong>' + this.data.version + '</strong></p>'
				+ '<hr /><label>Адрес: <input name="uri" type="text" value="' + this.data.uri + '" /></label>'
				+ '</fieldset><div class="clearfix"></div>'
				//------------------
				+ '<fieldset class="standart">'
				+ '<legend>Мета-теги</legend>'
				+ '<label>title: <input name="title" type="text" value="' + this.data.meta.title + '" /></label>'
				+ '<label>meta-description: <input name="description" type="text" value="' + this.data.meta.description + '" /></label>'
				+ '<label>meta-keywords: <input name="keywords" type="text" value="' + this.data.meta.keywords + '" /></label>'
				+ '<label>meta-robots: <input name="robots" type="text" value="' + this.data.meta.robots + '" /></label>'
				+ '</fieldset>'
				//------------------
				+ '<fieldset class="standart">'
				+ '<legend>Навигационная цепочка</legend>'
				+ '<div class="breadcrumbs"><ul class="selectable" id="editor-breadcrumbs">'
				+ '<li class="disabled">Главная</li>'
				+ bc
				+ '<li class="disabled">» ' + this.data.meta.title + '</li></ul>'
				+ '<span class="button" id="editor-add-breadcrumb"><span class="icon-add">Еще один пункт</span></span></div>'
				+ '</fieldset>'
			);
			
			$('#editor-breadcrumbs').sortable({
				items: 'li:not(.disabled)',
				placeholder: "ui-state-highlight"
			});
			
			$('#editor-add-breadcrumb').on('click', function(){
				$('#editor-breadcrumbs li:last').before('<li>» <a href="">Новая ссылка</a></li>');
			});
			
			$('#editor-breadcrumbs li:not(.disabled) a').live('click', function(){
				var obj = $(this);
				
				obj.parent().html(
					'Ссылка: <input type="text" value="' + obj.attr('href') +'" /> Текст: <input type="text" value="' + obj.html() +'" />'
					+ '<span class="button editor-ok">Ok</span>'
					+ '<span class="button editor-delete"><span class="icon-delete">Удалить</span></span>'
				);
				return false;
			});
			
			$('#editor-breadcrumbs li:not(.disabled) span.editor-ok').live('click', function(){
				var obj = $(this).parent();
				obj.html(
					'» <a href="' + obj.children('input:first').val() + '">' + obj.children('input:last').val() + '</a>'
				);
			});

			$('#editor-breadcrumbs li:not(.disabled) span.editor-delete').live('click', function(){
				$(this).parent().remove();
			});
			
			$('#editor-moderate-save').on('click', function(){pagesEditor.moderate.save();});
			$('#editor-moderate-draft').on('click', function(){pagesEditor.moderate.toDraft();});
			$('#editor-moderate-delete').on('click', function(){pagesEditor.moderate.del();});
		},
		
		getMeta: function(callback)
		{
			$.ajax({
				url: pagesEditor.url + 'getmeta/' + pagesEditor.id,
				type: "GET",
				dataType: 'json',
				success: function(r)
				{
					if ( r.error )
					{
						msg.ajaxError(r.error, 10, '#editor-info')
						return false;
					}
					
					pagesEditor.moderate.data = {
						status: ( r.status ? r.status : '' ),
						version: ( r.version ? r.version : '0' ),
						uri: ( r.uri ? r.uri : '' ),
						owner_login: ( r.owner_login ? r.owner_login : '' ),
						owner_id: ( r.owner_id ? r.owner_id : '' ),
						meta: ( r.meta === undefined ? {
								title: '',
								description: '',
								keywords: '',
								robots: '',
								breadcrumbs: []
							} : {
								title: ( r.meta.title ? r.meta.title : '' ),
								description: ( r.meta.description ? r.meta.description : '' ),
								keywords: ( r.meta.keywords ? r.meta.keywords : '' ),
								robots: ( r.meta.robots ? r.meta.robots : '' ),
								breadcrumbs: ( r.meta.breadcrumbs ? r.meta.breadcrumbs : [] )
							}
						)
					};
					
					if ( typeof callback === 'function' ) callback();
				}
			});
		},
		
		save: function()
		{
			var meta = {};
			$('#editor-breadcrumbs li:not(.disabled) a').each(function(i){
				meta['breadcrumbs][' + i + '][0'] = $(this).attr('href');
				meta['breadcrumbs][' + i + '][1'] = $(this).html();
			});
			
			meta['title'] = $('#editor-moderate-content input[name = title]').val();
			meta['description'] = $('#editor-moderate-content input[name = description]').val();
			meta['keywords'] = $('#editor-moderate-content input[name = keywords]').val();
			meta['robots'] = $('#editor-moderate-content input[name = robots]').val();
			
			var uri = $('#editor-moderate-content input[name = uri]').val();
			
			$.ajax({
				url: pagesEditor.url + 'setmeta/' + pagesEditor.id,
				type: "POST",
				data: {
					meta: meta,
					uri: uri
				},
				dataType: 'json',
				success: function(r)
				{
					if ( r.error )
					{
						msg.ajaxError(r.error, 10, '#editor-info')
						return false;
					}
					
					msg.add('<strong>Изменения сохранены!</strong><br />'
							+ 'Через 5 секунд страница будет обновлена!',
							'ok', 0, '#editor-info');
					window.setTimeout(function(){
						window.location.replace(r.redirect);
						
					}, 5000);
				}
			});
			
		},
		
		toDraft: function()
		{
			$.ajax({
				url: pagesEditor.url + 'todraft/' + pagesEditor.id,
				type: "GET",
				dataType: 'json',
				success: function(r)
				{
					if ( r.error )
					{
						msg.ajaxError(r.error, 10, '#editor-info')
						return false;
					}
					
					msg.add('<strong>Страница снята с публикации</strong><br />'
						+ 'Через 5 секунд страница будет обновлена!',
						'ok', 10, '#editor-info');

					window.setTimeout("window.location.reload(true)", 5000);
				}
			});
		},
		
		del: function()
		{
			$('#editor-info').html(
				'<div id="dialog-confirm" title="Внимание!">'
				+ '<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'
				+ 'Вы действительно хотите удалить данную страницу?'
				+ '</div>'
			);
			$('#dialog-confirm').dialog({
				resizable: false,
				height:140,
				modal: true,
				buttons: {
					"Удалить": function() {
						$( this ).dialog( "close" );
						$.ajax({
							url: pagesEditor.url + 'delete/' + pagesEditor.id,
							type: "GET",
							dataType: 'json',
							success: function(r)
							{
								if ( r.error )
								{
									msg.ajaxError(r.error, 10, '#editor-info')
									return false;
								}
								
								$('#editor, #editor-meta, #editor-history-content, #editor-moderate-content, #pages-content').remove();
								msg.add('<strong>Страница удалена!</strong>', 'ok', 0, '#editor-info');
								pagesEditor = null;
							}
						});
					},
					"Отмена": function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}
	},

/**
 * Закладка истории версий
 **/
	tabHistory: function()
	{
		if ( pagesEditor.status == 2 ) return false;
		this.status = 2;
		
		this.tabHide();

		$('#editor span.active').toggleClass('point active')
		$('#editor-history').toggleClass('point active');
		$('#pages-content').hide();
		
		this.tabHide = function()
		{
			$('#editor-history-content').hide();
			$('#pages-content').show();
			this.tabHide = function(){};
		};
		
		if ( this.historyCash == null ) {
			this.getHistory();
		}

		$('#editor-history-content').show();
	},	
	
	getHistory: function()
	{
		$.ajax({
			url: pagesEditor.url + 'gethistory/' + pagesEditor.id,
			type: "GET",
			success: function(r)
			{
				if ( r.error )
				{
					msg.ajaxError(r.error, 10, '#editor-info')
					return pagesEditor.tabView();
				}

				pagesEditor.historyCash = r.data;
				return pagesEditor.updateHistory();
			},
			dataType: 'json'
		});
	},
	
	updateHistory: function()
	{
		var html = ''; 
		for ( var row in pagesEditor.historyCash )
		{
			html += '<tr id="version-' + this.historyCash[row]['version'] + '">'
					+ '<td>' + this.historyCash[row]['version'] + '</td>'
					+ '<td>' + this.historyCash[row]['date'] + '</td>'
					+ '<td>' + this.historyCash[row]['user_login'] + '</td>'
					+ '<td>';
			switch (this.historyCash[row]['status'])
			{
				case '0': html += 'Черновик'; break;
				case '1': html += '<strong>Опубликована</strong>'; break;
				case '2': html += '<em>Модерируется</em>'; break;
				case '3': html += 'В очереди на модерацию'; break;
			}
			html += '</td>'
					+ '<td><span class="button" onClick="pagesEditor.getVersion(' + this.historyCash[row]['version'] + ')">Показать</span>'
					+ '<span class="button compare" onClick="pagesEditor.compare.add(' + this.historyCash[row]['version'] + ', this)">Сравнить</span>'
			
			if ( this.historyCash[row]['status'] != '1' ){
				html += '<span class="button" onClick="pagesEditor.publish(' + this.historyCash[row]['version'] + ')">Опубликовать</span>';
			}
			html += '</td></tr>';
		}
		html = '<span class="button" id="editor-history-update">Обновить</span>'
				+ '<table class="result_table" id="history"><thead>'
				+ '<th>#</th><th>Дата</th><th>Редактор</th><th>Статус</th><th>'
				+ '</thead><tbody>'
				+ html + '</tbody></table>';
		$('#editor-history-content').html(html);
		$('#editor-history-update').click(function(){pagesEditor.getHistory()});
	},
	
	publish: function(version)
	{
		$.ajax({
			url: pagesEditor.url + 'publish/' + pagesEditor.id,
			type: "POST",
			data: { version: version },
			
			success: function(r)
			{
				if ( r.error )
				{
					msg.ajaxError(r.error, 10, '#editor-info')
					return false;
				}
				msg.add(r.info, 'notice', 10, '#editor-info');
				pagesEditor.getHistory();
				if ( r.version != pagesEditor.activeVersion ){
					pagesEditor.activeVersion = r.version;
					
				}
			},
			dataType: 'json'
		});
		pagesEditor.getVersion(version);
	},
	
	compare: {
		versions: [null, null],
		buttons: [null, null],
		
		add: function(version, button)
		{
			if ( this.versions[1] == version && this.versions[0] == null ) {
				return false;
			}
			
			if ( ( this.versions[1] == version || this.versions[0] == version ) && this.versions[0] != null ) {
				return this.execute();
			}
			
			
			this.versions[0] = this.versions[1];
			this.versions[1] = version;
			
			this.buttons[0] = this.buttons[1];
			this.buttons[1] = button;
			
			this.view();
				
		},
		
		view: function()
		{
			$('#editor-history-content table tr').removeClass('selected');
			$('#editor-history-content table .compare').html('Сравнить');
			
			$('#version-' + this.versions[1]).addClass('selected');
			if ( this.versions[0] == null ) {
				$(this.buttons[1]).html('...');
				return;
			}
			
			$('#version-' + this.versions[0]).addClass('selected');
			
			$(this.buttons).html('Начать сравнение');
			
		},
		
		execute: function()
		{
			$.ajax({
				url: pagesEditor.url + 'compare/' + pagesEditor.id,
				type: "POST",
				data: {
					versions: this.versions
				},
				
				success: function(r)
				{
					if ( r.error )
					{
						msg.ajaxError(r.error, 10, '#editor-info')
						return false;
					}
					$('#compare-execute').remove();
					$('#editor-history-content').html('<span class="button" onClick="pagesEditor.updateHistory()">⇐ Вернуться к истории</span>'
						+ '<div id="compare-execute">'+r.data+'</div>');
				},
				dataType: 'json'
			});
			
			this.versions = [null, null];
			this.buttons = [null, null];
		}
	}
}