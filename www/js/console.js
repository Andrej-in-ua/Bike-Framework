$(document).ready(function(){
	if ( ! timers ) var timers = [];

	$("#console").draggable({
		cursor: "move", containment: [0,0, $(window).width() - 50, $(window).height() - 50],
		scrollSensitivity: 20,
		cancel: '.box',
		stop: function(event, ui) {
			localStorage.setItem('console-top', ui.position.top);
			localStorage.setItem('console-left', ui.position.left);
		}
	});

	if (
		! localStorage.getItem('console-top') 
		|| localStorage.getItem('console-left') > $(window).width() - 50
		|| localStorage.getItem('console-top') > $(window).height() - 50
		)
	{
		localStorage.setItem('console-top', '28');
		localStorage.setItem('console-left', '0');
	}
	
	$("#console" ).offset({top:localStorage.getItem('console-top'), left:localStorage.getItem('console-left')});
	
	$('#console > .box-title').each(function(){
		if ( ! localStorage.getItem($(this).next().attr('id')) ) $(this).next().hide();
	});

	$('#console > .box-title').click(function(){
		var box = $(this).next();
		if (box.css('display') == 'none')
		{
			localStorage.setItem(box.attr('id'), '1');
			box.show();
		} else {
			localStorage.removeItem(box.attr('id'));
			box.hide();
		}
		return false;
	});

	storage = function()
	{
		if ( ! localStorage.getItem('console') ) return false;

		$("#storage-count").html(localStorage.length);
		
		var key;
		var value;
		var box = '[<a href="javascript:storage();">refresh</a>]<pre>';
		for ( var i = 0; i < localStorage.length; i++ )
		{
			key = localStorage.key(i);
			value = localStorage.getItem(key);
			box += '[' + key + '] => ' + value + '\n\r';
		}
		
		$("#box-local").html(box + '</pre>');
	}

	showConsole = function()
	{
		if ( ! localStorage.getItem('console') ) {	
			$("#console").hide();
			window.clearInterval(timers['storage']);
			return false;
		}
		
		timers['storage'] = window.setInterval(storage, 1500);
		$("#console").show();
	}
	
	showConsole();

	$("#console-open").click(function(){
		if ($("#console").css('display') == 'none' || ! localStorage.getItem('console') )
		{
			localStorage.setItem('console', '1');
		} else {
			localStorage.removeItem('console');
		}
		showConsole();
	});

});