<?php

/*
	400 Ошибка валидации
	401 Необходима авторизация
	402 Недостаточно прав доступа
	403 Незапланированное использование (возможно взлом)
	404 Страница не найдена
	410 Данный запрос больше не будет поддерживаться
	
	500 Критическая ошибка, выполнение прервано
*/
class Model_Error
{
	public function e($msg, $code)
	{
		if ( DEBUG ) { 
			ob_end_clean();
			ob_start();
			echo '<div style="color:#CD0A0A;
			border: 1px solid #FFCF9F;background: url(../img/error-icon.png) no-repeat 8px 7px;
			font-size: 95%;padding: 10px 10px 10px 40px;margin: 20px 0;border-radius: 4px;"><strong>DEBUG</strong><pre>['.$code.'] '.$msg.'</pre></div>';
			Core::creatpage();
			exit;
		} else if ( $code === 0 || $code === 8 ) {
			return;
		}
		
		switch ( $code )
		{
			case 400: break;
			case 401: break;
			case 402: break;
			case 403: $this->e403($msg);break;
			case 404: $this->e404(); break;
			case 410: $this->e410(); break;
			default: $this->any($msg, $code);
 			
		}
	}
	
	public function any($msg, $code)
	{
		ob_end_clean();
		ob_start();
		header('HTTP/1.0 500 Internal Server Error ');
		echo '<h2>500 Internal Server Error</h2>';
		
		Core::creatpage();
		exit;
	}
	
	
	public function e403($msg)
	{
		ob_end_clean();
		ob_start();
		header('HTTP/1.0 404 Not Faund');
		echo '<h2>403 подозрение на взлом</h2>'.$msg;
		
		Core::creatpage();
		exit;
	}
	
	public function e404()
	{
		ob_end_clean();
		ob_start();
		header('HTTP/1.0 404 Not Faund');
		echo '<h2>404 страница не найдена</h2>';
		
		Core::creatpage();
		exit;
	}
	
	public function e410()
	{
		header('HTTP/1.0 410 Gone');
		echo '<h2>410 вот и все, теперь это не поддерживается</h2>';
		
		Core::creatpage();
		exit;
	}
	
	public function e500($msg)
	{
		ob_end_clean();
		ob_start();
		header('HTTP/1.0 404 Not Faund');
		echo '<h2>FATAL ERROR</h2>'.$msg;
		
		Core::creatpage();
		exit;
	}
}