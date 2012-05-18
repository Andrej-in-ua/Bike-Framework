<?php  if ( ! defined('SITE')) exit('No direct script access allowed');

class Model_Acl
{
	protected
		$cash = array(),
		$roles = array(),
		
		$currentUser = null,
		
		$flagAuth = false;
	
	function __construct() {}
	
	/**
	 * Данный метод вызывает системное событие auth
	 * Сделать это на этапе инициализации нельзя, т.к. все слушатели данного события используют текущую модель
	 **/
	protected function auth()
	{
		if ( $this->flagAuth !== false ) return;
		
		$this->flagAuth = true;
		Core::event('auth');
	}
	
	public function check($action, $default = null)
	{
		$this->auth();
		
		Core::event('acl_check', $action);
		
		if ( count($this->roles) === 0 ) return $default;
				
		$acl = ( isset($this->cash[$action]) ) ? $this->cash[$action] : $this->cash[$action] = $this->_processing($action); 
		
		return ( $acl !== null ? $acl : $default);
	}
	
	protected function _processing($action)
	{
		if ( in_array("god", $this->roles) ) return true;
		if ( in_array("pre_ban", $this->roles) ) return false;
		
		$config = Load::Configs()->get('acl');
		
		$acl = null;
		
		foreach ( $this->roles as $key => $val )
		{
			if ( ! isset($config['rules'][$val]) OR ! isset($config['rules'][$val][$action])) continue;
			
			if ( $config['rules'][$val][$action] === true ){
				$acl = true;	
			} else if ( $config['rules'][$val][$action] === false ){
				return false;
			}
		}

		return $acl;
	}
	
	public function addRoles($roles)
	{
		if ( ! is_array($roles) OR count($roles) === 0 ) return false;
		$this->roles += $roles;
	}
	
	public function addUser($id, $login, $roles = false)
	{
		if ( $roles !== false ) $this->addRoles($roles);
		$this->currentUser = array('id' => $id, 'login' => $login);
	}
	
	public function isCurrentUser( $id, $login = false)
	{
		$this->auth();
		return ( $this->currentUser['id'] === (int) $id && ( $login === false || $this->currentUser['login'] == $login ) ) ? true : false;
	}
	
	public function getCurrentUser()
	{
		$this->auth();
		return ( $this->currentUser !== null ) ? $this->currentUser : false;
	} 
	
/** ----------------------------------------------------------------------- **/
/** ----------------------------------------------------------------------- **/
/** ----------------------------------------------------------------------- **/

	public function rolesAdminGET()
	{
		$config = Load::Configs()->get('acl');
		
		if ( false === ( $writable = Load::Configs()->writable('acl') ) ){
			Load::Msg()->Add('Файл конфигурации библиотеки Acl защищен от записи. Изменения не будут сохранены', 'notice');
		}
		
		Load::View('admin/roles', array('actions' => $config['actions'], 'rules' => $config['rules'], 'writable' => $writable));
	}

	public function rolesAdminPOST()
	{
		$config = Load::Configs()->get('acl');
	
		if ( false === Load::Configs()->writable('acl') ){
			Load::Log()->write('Acl configs not writable');
			Core::refresh();
		}
		
		if ( isset($_POST['del_role']) )
		{
			if ( ! isset($config['rules'][$_POST['del_role']]) )
			{
				Load::Msg()->Add('Указанная роль не существует!');
				Core::refresh();
			}
			unset($config['rules'][$_POST['del_role']]);
			$_POST['role'] = $_POST['del_role'];
		}
		else if ( isset($_POST['new_role']) )
		{
			if ( $_POST['new_role'] == '' )
			{
				Load::Msg()->Add('Укажите имя новой роли!');
				Core::refresh();
			}
			
			if ( isset($config['rules'][$_POST['new_role']]) )
			{
				Load::Msg()->Add('Указанная роль уже существует!');
				Core::refresh();
			}
			$config['rules'][$_POST['new_role']] = array();
			$_POST['role'] = $_POST['new_role'];
		}
		else
		{
			if ( ! isset ($config['rules'][$_POST['role']]) )
			{
				Load::Log()->write('ERROR: Role '.$_POST['role'].' has save', 'admins/'.$_SESSION['admin_login']);
				Load::Msg()->Add('Указанная роль отсутствует. Может кто-то успел ее удалить?');
				Core::refresh();
			}
			
			foreach ( $config['rules'][$_POST['role']] as $action => $bool )
			{
				switch ($_POST['action'][$action])
				{
					case 'null': unset($config['rules'][$_POST['role']][$action]); break;
					case 'true': $config['rules'][$_POST['role']][$action] = true; break;
					case 'false': $config['rules'][$_POST['role']][$action] = false; break;
				} 
			}
			
			if ( isset($_POST['add_action']) )
				foreach ( $_POST['add_action'] as $num => $action )
				{
					if ( isset($config['rules'][$_POST['role']][$action]) OR $action == '' ) continue;
					
					switch ( $_POST['new_action'][$num] )
					{
						case 'true': $config['rules'][$_POST['role']][$action] = true; break;
						case 'false': $config['rules'][$_POST['role']][$action] = false; break;
					} 
				}
		}
		if ( Load::Configs()->write('acl', $config) )
		{
			Load::Log()->write('Role '.$_POST['role'].' has save', 'admins/'.$_SESSION['admin_login']);
			Load::Msg()->Add('Настройки доступа сохранены', 'ok');
			Core::refresh();
		} else {
			Load::Log()->write('Can not write Acl config', 'errors');
			Core::error('Can not write Acl config', 404);
		}

		return $this->rolesAdminGET();
	}
}