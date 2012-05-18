<?php
/**
 * Used acl actions:
 * 
 * is_admin
 * app_config_write
 * site_config_write
 **/
 
class Admin
{
	protected
		$admin,
		$acl,
		$log,
		$meta;
	
	function __construct()
	{
		if ( ! ( $this->acl = Load::Acl() ) OR ! ( $this->admin = Load::Admin() ) )
		{
			throw new Exception('Need admin or acl model', 500);
		}
		$this->log = Load::Log();

		$meta =& Core::getKey('meta');
		$meta['css'][] = 'admin';
		$meta['robots'] = 'noindex,nofollow';
	}
	
	/**
	 * Method generated main admin menu
	 * ---------------------------------
	 * @return $this
	 **/
	function indexAction()
	{
		if ( ! $this->acl->check('is_admin', false) ) Core::redirect('admin/login');
		
		$meta =& Core::getKey('meta');
		$meta['breadcrumbs'][] = array('admin', 'Control panel');
		
		$data['menu'] = $this->admin->getMenu();
		
		Load::View('admin/index', $data);
	}
	
	/**
	 * Login GET
	 * ---------------------------------
	 * @return $this
	 **/
	public function loginActionGET()
	{
		if ( $this->acl->check('is_admin', false) ) Core::redirect('admin/index');
		
		Load::Msg()->notice('Требуется авторизация профиля с правами администратора');
		
		$meta =& Core::getKey('meta');
		$meta['breadcrumbs'][] = array('admin', 'Control panel');
		$meta['breadcrumbs'][] = array('admin/login', 'Login');
		
		Load::View('admin/login');
	}
	
	/**
	 * Login POST
	 * ---------------------------------
	 **/
	public function loginActionPOST()
	{
		Core::event('login');
		
		if ( $this->log ) $this->log->write('Admin login failed', 'access-error');
		Load::Msg()->error('Admin login failed');
		return $this->loginActionGET();
	}

	public function logoutAction()
	{
		Core::event('logout');
		
		if ( $this->log ) $this->log->write('Admin logout');
		Core::redirect('admin/login');
	}

	
	/**
	 * Run controllers admin
	 * ---------------------------------
	 * @param string	controller name
	 * @param string	method, default index
	 * @return $this
	 **/
 	public function cAction($type, $name, $method = 'index')
 	{
		
 		if ( ! ( $type = ( $type === 'c' ? 'controller' : ( $type === 'm' ? 'model' : false ) ) )
 			OR ! ( $item = $this->admin->checkMenu($type, $name, $method) ) )
		{
			if ( $this->log ) $this->log->write('Access denied');
			Load::Msg()->error('Нехватает прав доступа, поговорите с главным администратором.');
			Core::redirect('admin/index');
		}
		
		$meta =& Core::getKey('meta');
		$meta['breadcrumbs'][] = array('admin', 'Control panel');
		$meta['breadcrumbs'][] = array('#', $item['title']);
		
		$args = array_slice(func_get_args(), 3);
		
		if ( ! ($app = Load::Single($type, $name)) ) throw new Exception('Cannot find '.$type.' '.$name, 404);

		if ( method_exists($app, $method.'Admin'.$_SERVER['REQUEST_METHOD']) )
		{
			call_user_func_array(array(&$app, $method.'Admin'.$_SERVER['REQUEST_METHOD']), $args);
		}
		else if ( method_exists($app, $method.'Admin') )
		{
			call_user_func_array(array(&$app, $method.'Admin'), $args);
		}
		else
		{
			throw new Exception('Cannot find '.$type.'/'.$name.'->'.$method, 404);
		}
 	}


	
/**
 * ------------------------------------------------------------------
 * Admin methods
 * ------------------------------------------------------------------
 **/
	
	/**
	 * Edit my profile
 	 * ---------------------------------
	 * @return $this
	 **/
	public function profileAdminGET()
	{
		$data = array(
			'login' => $this->admin->getCurrentLogin(),
			'writable' => $this->admin->canEditProfile()
		); 
		
		if ( $data['writable'] === false )
		{
			Load::Msg()->Add('Профиль изменить невозможно. Обратитесь к главному администратору', 'notice');
		}
		
		Load::View('admin/profile', $data);
	}
	
	public function profileAdminPOST()
	{
		if ( $this->admin->editProfile() )
		{
			if ( $this->log ) $this->log->write('New password save');
			Load::Msg()->Add('Новый пароль сохранен', 'ok');
		}
		Core::refresh();
	}
}