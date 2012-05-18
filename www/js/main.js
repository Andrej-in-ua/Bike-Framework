$(document).ready(function(){
	$("body").ajaxStart(loading.start);
	$("body").ajaxComplete(loading.stop);
	
	loading.stop();
});


var loading = {
	start: function(){ $("#loading").show(); },
	stop: function(){ $("#loading").hide();	}
};

var msg = {
	add: function(msg, type, timer, target)
	{
		if ( typeof msg == 'object' ){
			target 	= msg.target;
			timer 	= msg.timer;
			type 	= msg.type;
			msg 	= msg.msg;
		}
		
		if (typeof target == 'undefined'){
			target = $('#content');
		} else {
			target = $(target);
		}
		
		var id = 'msg-'+type+'-'+Math.floor(Math.random()*10000);

		target.prepend('<div class="' + type + '" style="display:none" id="' + id + '">' + msg + '</div>');
		
		id = $('#'+id);
		
		id.slideDown('slow');
		
		if ( timer > 0 ){
			window.setTimeout(function(){
				id.slideUp('slow', function(){
					$(this).remove();
				});
			}, timer * 1000);
		}
		
		return id;
	},
	
	ajaxError: function(e, timer, target)
	{
		if ( ! e ) return false;
		return msg.add(
			( e.code && e.code != 0  ? '[' + e.code + '] ' : '' )
				+ ( e.msg ? e.msg : 'Произошла непредвиденная ошибка'),
			'error', timer, target);
	}
}

//-----------------------
function count(a){var b=0;for(var i in a)if(i)++b;return b;}