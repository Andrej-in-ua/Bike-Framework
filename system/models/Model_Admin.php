<?php if ( ! defined('SITE') ) exit('No direct script access allowed');
/**
 * Model Admin
 * 
 * @author		Andrej Sevastianov
 * @copyright	Copyright (c) 2012
 * @version		0.2.0
 * 
 * Отвечает за авторизацию администраторов и админ-панель.
 * Учетные записи администраторов храняться в конфигурационных файла.
 */
class Model_Admin
{
	protected
		$acl,
		$configs;

	public function __construct()
	{
		if ( ! $config = Load::Configs() ) throw new Exception('Need configs model', 500);
		$this->configs = $config->get('admin'); 
		
		if ( ! $this->acl = Load::Acl() ) throw new Exception('Need acl model', 500);		
	}
	
	/**
	 * Check admin authorization
	 **/
	 public function auth()
	 {
	 	if ( ! Load::Session() ) throw new Exception('Need session model', 500);
	 	
	 	if ( ! isset($_SESSION['admin_login']) OR ! isset($this->configs['admins'][$_SESSION['admin_login']]) ) return false;
		
		$this->acl->addUser(
			$this->configs['admins'][$_SESSION['admin_login']]['id'],
			$_SESSION['admin_login'],
			( $this->configs['admins'][$_SESSION['admin_login']]['roles']
				? $this->configs['admins'][$_SESSION['admin_login']]['roles']
				: array()
			)
		);
	 }
	
	/**
	 * Login admin
	 **/
	public function login()
	{
		if ( ! Load::Session() ) throw new Exception('Need session model', 500);
		
		$log = Load::Log();
		$login = $_POST['login'];
		$password = $_POST['password'];
		
		if ( ! isset($this->configs['admins'][$login]) OR $this->configs['admins'][$login]['password'] !== $password )
		{
			if ( $log !== false AND isset($this->configs['admins'][$login]) )
			{
				$log->write('Login failed ('.$login.')', 'admins/'.$login);
			}
			return false;
		}

		$_SESSION['admin_login'] = $_POST['login'];
		if ( $log !== false ) $log->write('Admin login', 'admins/'.$_POST['login']);
		Core::refresh();		
	}
	
	/**
	 * Logout admin
	 **/
	public function logout() { unset($_SESSION['admin_login']);	}
	
	
	/**
	 * @return string	current admin login
	 **/
	public function getCurrentLogin(){ return $_SESSION['admin_login']; }
	
	/**
	 * Проверяется может ли быть отрадактирован конфиг.файл
	 **/
	public function canEditProfile()
	{
		if ( ! isset($this->configs['use_db']) || false === $this->configs['use_db'] )
		{
			return Load::Configs()->writable('admin');
		} else {
			return true;
// :TODO: если админы храняться в базе данных
		}
	}
	
	/**
	 * Изменение пароля администратора
	 * Получает данные из глобальной переменной POST
	 **/
	public function editProfile()
	{
		if ( ! isset($this->configs['use_db']) || false === $this->configs['use_db'] )
		{
			if ( false === $this->canEditProfile() ){
				if ( $this->log ) $this->log->write('Admin profile not writable');
				return false;
			} else if ( ! isset($_POST['password']) ) {
				Load::Msg()->Add('Вы не указали текущий пароль');
				return false;
			} else if ( $this->configs['admins'][$_SESSION['admin_login']]['password'] !== $_POST['password']) {
				if ( $this->log ) $this->log->write('Current password is not correct');
				Load::Msg()->Add('Current password is not correct');
				$this->logout();
				return false;
			} else if ( $_POST['npassword'] !== $_POST['npassword2'] ) {
				Load::Msg()->Add('Новый пароль введен неверно в одном из полей. Буддте внимательней');
				return false;
			}
			
			$this->configs['admins'][$_SESSION['admin_login']]['password'] = $_POST['npassword'];

			return Load::Configs()->write('admin', $this->configs);			
		} else {
			return false;
// :TODO: если админы храняться в базе данных		
		}
	}
	
	/**
	 * @return array	admin menu for current user
	 **/
	public function getMenu()
	{	
		$data = array();
		
		foreach ( $this->configs['main_menu'] as $key => $category )
		{
			if ( isset($category['acl']) AND ! $this->acl->check($category['acl'], false) ) continue;
			
			$data[$key]['title'] = $category['title'];
			
			foreach ($category['menu'] as $item )
			{
				if ( isset($item['acl']) AND ! $this->acl->check($item['acl'], false) ) continue;
				
				$data[$key]['menu'][] = $item;
			}
		}
		
		return $data;
	}
	
	/**
	 * Check admin menu config
	 * Проверяет, имеет ли администратор доступ к запрашиваему пункту меню
	 * 
	 * @param string	type (controller/library)
	 * @param string	name
	 * @param string	method
	 * @return array or false
	 **/
	public function checkMenu($type, $class, $method)
	{
		foreach ( $this->configs['main_menu'] as $category )
		{
			if ( isset($category['acl']) AND ! $this->acl->check($category['acl'], false) ) continue;
			
			foreach ($category['menu'] as $item )
			{
				if (
					$item['type'] == $type
					AND $item['class'] == $class
					AND ( $item['method'] == $method OR ( $item['method'] == '' AND $method == 'index'  ) )
				) {
					if ( ! isset($item['acl']) OR true === $this->acl->check($item['acl'], false) )
					{
						return $item;
					} else {
						return false;
					}
				}
			}
		}
		
		return false;
	}
}