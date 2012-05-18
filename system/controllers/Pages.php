<?php
/**
 * Used acl actions:
 * 
 * pages_add 		- доступ к добавлению страниц
 * pages_publish	- публикация и редактирование своих страниц без модерирования
 * pages_draft		- просмотр чужих черновиков
 * pages_moderator	- модерирование страниц
 **/
class Pages
{
	protected 	$lockTime 	= 300,
				$listPP		= 20,
				
				$pages;
	
	function __construct(){
		if ( ! $this->pages = Load::Pages() ) throw new Exception('Page model not faund', 404);
	}
	
	public function indexAction()
	{
		if ( Core::isAjax() ) return call_user_func_array(array(&$this, 'ajax'), func_get_args());
		
		if ( func_num_args() == 0 ) throw new Exception('Page not faund', 404);
		$uri = implode('/', func_get_args());
		$data = $this->pages->getByUri($uri);

		if ( ! ( $acl = Load::Acl() ) )  throw new Exception('Acl model not faund', 500);
		
		if ( ! $data ) 
		{
			if ( ! $acl->check('pages_add', false) ) throw new Exception('Page not faund', 404);
			
			return Load::Msg()->notice('Страница с адресом <strong>'.$uri.'</strong> еще не создана. Вы хотите ее создать?
			<form action="'.BASEURL.'pages/add/" method="POST"><input name="uri" type="hidden" value="'.$uri.'" />
			<input type="submit" value="Создать" /></form>');
		}
		
		$data->currentUser['moderator'] 	= $acl->check('pages_moderator', false);
		$data->currentUser['owner']		= ( $acl->isCurrentUser($data->owner_id) AND $acl->check('pages_add', false) ? true : false );

		switch ( $data->status )
		{
			case '0':
				if ( ! $data->currentUser['moderator'] AND ! $data->currentUser['owner'] )
				{
					throw new Exception('Page not faund', 404);
				}
				
				Load::Msg()->notice('Данная страница еще не опубликована.');
				Load::View('pages/editor', $data);
				break;
				
			case '1':
				if ( $data->currentUser['moderator'] OR $data->currentUser['owner'] )
				{
					Load::View('pages/editor', $data);
				}
				break;
				
			case '2':
				if ( $data->currentUser['moderator'] )
				{
					Load::View('pages/editor', $data);
					Load::Msg()->notice('Страница принята на проверку');
				} else if ( $data->currentUser['owner'] )
				{
					Load::Msg()->add('Ваша страница в данный момент проверяется модератором. Дождитесь окончания проверки.', 'notice');
				} else {
					Load::Error()->e404();
				}
				break;
				
			case '3':
				if ( ! $data->currentUser['moderator'] AND ! $data->currentUser['owner'] )
				{
					throw new Exception('Page not faund', 404);
				}
				
				Load::Msg()->notice('Страница находится в очереди на модерацию');
				Load::View('pages/editor', $data);
				break;
			default: throw new Exception('Page not faund', 404); break;
			
		}
		
		$meta =& Core::getKey('meta');
		$data->meta = unserialize($data->meta);
		$meta = ( is_array($data->meta)
						? ( is_array($meta) ? $meta + $data->meta : $data->meta )
						: $meta);
		
		$meta['breadcrumbs'][] = array('pages/'.$uri, (isset($meta['title']) ? $meta['title'] : $uri) );

		Load::View('pages/single', $data);
	}
	
	public function addAction()
	{				
		if ( ! Load::Acl()->check('pages_add', false) ) Load::Error()->e404();
		
		if ( ! isset($_POST['uri']) OR $_POST['uri'] == '' )
		{
			return Load::View('pages/admin_add');
		}
		
		$_POST['uri'] = trim(str_replace( '\\', '/', $_POST['uri']), '/');
		if ( false !== $this->pages->getByUri($_POST['uri']) ) 
		{
			Load::Msg()->add('По указанному адресу уже существует страница', 'error');
			Core::redirect('pages/'.$_POST['uri']);
		}
		
		$meta = serialize(array('title'=>'Новая страница'));
		list($uid, $ulogin) = Load::Acl()->getCurrentUser();
		
		$this->pages->addPage($_POST['uri'], $uid, $ulogin, $meta);
		Load::Msg()->add('<strong>Новая страница создана!</strong><br />Теперь вы можете ее редактировать.', 'ok');
		Load::Log()->write('Add new page (URI:'.$_POST['uri'].')');
		Core::redirect('pages/'.$_POST['uri']);
	}

/**
 * ------------------------------------------------------------------
 * Ajax methods
 * ------------------------------------------------------------------
 **/
 
	protected function ajax($action, $id)
	{
		$ajax = Load::Ajax()->setFormat('json');
		
		try {
			$action = 'ajax'.ucfirst(strtolower($action));
			if ( ! method_exists($this, $action) ) throw new Exception('Действие невозможно', 410);
			
			$response = $this->$action($id);
			
			$ajax->send($response);
		} catch(Exception $e) {
		    $ajax->error($e->getMessage(), $e->getCode());
		}
	}
	
	protected function ajaxCanEdit($id, $response = array())
	{
		if ( ! $data = $this->pages->getById($id) )
		{
			throw new Exception('Страница не найдена', 404);
		}
		
		$acl = Load::Acl();
		if ( ! $id = $this->validId($id) 
			OR (
				! $acl->check('pages_moderator', false)
				AND (
					! $add = $acl->check('pages_add', false)
					OR ! $acl->checkCurrentUser($data->owner_id, $data->owner_login)
					)
				)
			)
		{
			throw new Exception('Access denied', 402);
		}
		
		return array($data, $response);
	}
/**
 * ------------------------------------------------------------------
 * Editor methods
 * ------------------------------------------------------------------
 **/	
	/**
	 * Locked pages for monopoly editing
	 * 
	 * @param	int	id
	 **/ 
	public function ajaxLocked($id)
	{
		
		$response['locked'] = 0;
		list($data, $response) = $this->ajaxCanEdit($id, $response);
		
		$acl = Load::Acl();
		if ( strtotime($data->locked_time) + $this->lockTime >= microtime(true) AND ! $acl->isCurrentUser($data->locked_id, $data->locked_login) )
		{
			throw new Exception($data->locked_login.' уже редактирует данную страницу', 402);
		}
		
		$response['locked'] = $this->lockTime;
		$user = $acl->getCurrentUser(); 
		$this->pages->setLocked($id, $user['id'], $user['login']);
		
		return $response;
	}
	
	public function ajaxLatest($id)
	{
		list($data, $response) = $this->ajaxCanEdit($id);
		$response['version'] = $this->pages->getLatestVersion($id);
		return $response;
	}
	
	public function ajaxSave($id)
	{
		list($data, $response) = $this->ajaxCanEdit($id);
		$user = Load::Acl()->getCurrentUser();
		$this->pages->addRevision($id, $user['id'], $user['login'], $_POST['content']);
		Load::Log()->write('Page (ID:'.$id.') added new revision');
		return $response;
	}
	
	
	public function ajaxGethistory($id)
	{
		list($data, $response) = $this->ajaxCanEdit($id);
		$response['data'] = $this->pages->getHistory($id);
		return $response;

	}
	
	public function ajaxGetversion($id)
	{
		
		if ( ! $version = $this->validVersion($_GET['version']) ) {
			throw new Exception('Access denied', 403);
		}

		list($data, $response) = $this->ajaxCanEdit($id);
		$response = $this->pages->getVersion($id, $version);
		return $response;
	}
	
	public function ajaxCompare($id)
	{
		list($data, $response) = $this->ajaxCanEdit($id);
		$response['data'] = $this->pages->getVersions($id, $_POST['versions']);
		$response['data'] = Load::Text()->htmlDiff($response['data'][0]['content'], $response['data'][1]['content']);
		return $response;
	}

	public function ajaxPublish($id)
	{
		if ( ! $version = $this->validVersion($_POST['version']) ) {
			throw new Exception('Access denied', 403);
		}
		
		list($data, $response) = $this->ajaxCanEdit($id);
		
		$acl = Load::Acl();
		if ( $acl->check('pages_moderator', false) OR (
				$acl->check('pages_publish', false) AND $acl->checkCurrentUser($data->owner_id, $data->owner_login)
			) )
		{
			$this->pages->setActive($id, $version);
			$response['info'] = 'Версия '.$version.' опубликована';
			$response['version'] = $_POST['version'];
			Load::Log()->write('Page (ID:'.$id.') has publish version '.$version);	
		} else {
			$this->pages->setModeration($id, $version);
			$response['info'] = 'Страница ожидает решения модератора';
			$response['version'] = $data->version;
			Load::Log()->write('Page (ID:'.$id.') request for publish version '.$version);
		}
		
		
		return $response;
	}

/**
 * ------------------------------------------------------------------
 * Moderator methods
 * ------------------------------------------------------------------
 **/	
	public function ajaxGetmeta($id)
	{
		
		if ( ! $id = $this->validId($id) OR  ! Load::Acl()->check('pages_moderator', false)  ) {
			throw new Exception('Access denied', 402);
		}
		
		if ( ! $response = $this->pages->getById($id) )	{
			throw new Exception('Страница не найдена', 404);
		}
		
		$response->meta = unserialize($response->meta);
		
		return $response;
	}
	
	public function ajaxSetmeta($id)
	{
		
		if ( ! $id = $this->validId($id) OR  ! Load::Acl()->check('pages_moderator', false)  ) {
			throw new Exception('Access denied', 402);
		}
	
		$uri = trim($_POST['uri'], '/');
		$this->pages->setById(
			$id,
			array(
				'meta' => serialize($_POST['meta']),
				'uri' => $uri
			)
		);
		$response['redirect'] = BASEURL.'pages/'.$uri;
		Load::Log()->write('Page (ID:'.$id.') set new meta data');
		return $response;
	}
	
	public function ajaxTodraft($id)
	{
		if ( ! $id = $this->validId($id) OR  ! Load::Acl()->check('pages_moderator', false)  ) {
			throw new Exception('Access denied', 402);
		}
		$this->pages->toDraft($id);
		Load::Log()->write('Page (ID:'.$id.') send to draft');
		return;
	}
	
	public function ajaxDelete($id)
	{
		if ( ! $id = $this->validId($id) OR  ! Load::Acl()->check('pages_moderator', false)  ) {
			throw new Exception('Access denied', 402);
		}
		
		if ( ! $response = $this->pages->getById($id) )	{
			throw new Exception('Страница не найдена', 404);
		}
		$meta = unserialize($response->meta);
		
		list($uid, $ulogin) = Load::Acl()->getCurrentUser();
		Load::Trash()->add($id, $meta['title'], 'pages', 'restorePage', 'deletePage', 'pages_moderator');
		$this->pages->addToTrash($id, $uid, $ulogin);
		
		Load::Log()->write('Page (ID:'.$id.') send to trash');
		return;
	}
/**
 * ------------------------------------------------------------------
 * Admin methods
 * ------------------------------------------------------------------
 **/

	public function addAdmin() { return $this->addAction(); }
	
	public function listAdmin($start = 0)
	{
		$paginator = Load::Paginator();
		$start = $paginator->validate($start, $this->listPP);
		
		$data = $this->pages->getList($start, $this->listPP);

		$data['paginator'] = $paginator->creat($start, $data['total'], 4, $this->listPP);
		$data['lockTime'] = $this->lockTime;

		Load::View('pages/admin_list', $data);		
	}
/**
 * ------------------------------------------------------------------
 * Protected methods
 * ------------------------------------------------------------------
 **/
 	protected function validId($id)
 	{
 		return ( ! $id = (int)$id OR $id  <= 0 OR $id == '' ) ? 'false' : $id ; 
 	}
 	
 	protected function validVersion($id)
 	{
 		return $this->validId($id); 
 	}
}