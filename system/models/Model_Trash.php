<?php
class Model_Trash
{
	protected $listPP = 20;
	protected 	$trash = 'trash',
				$DB;
	
	public function __construct() {
		if ( ! $this->DB = Load::Database() ) exit;
	}
	
	public function listAdmin($start = 0, $id = 0)
	{	
		if ( is_string($start) )
			switch($start)
			{
				case 'delete': return $this->execute($id, 'delete'); break;
				case 'restore': return $this->execute($id, 'restore'); break;
				default: break;
			}
			
		if ( ! ($paginator = Load::Paginator() ) ) throw new Exception('Need paginator model', 404);

		$start = $paginator->validate($start, $this->listPP);

		$data = $this->getList($start, $this->listPP);

		$data['paginator'] = $paginator->creat($start, $data['total'], 4, $this->listPP);

		Load::View('trash/list', $data);	
	}
	
	/**
	 * @var	mixed	id or array of id
	 **/
	public function execute($id, $action)
	{	
		if ( $id === 'all' )
		{
			$log = 'Trash '.$action.' all.';
			$data = $this->getAll();
		}
		else if ( is_array($id) )
		{
			$log = 'Trash '.$action.' ID: '.implode(',', $id);
			$data = $this->getMany($id);
		}
		else if ( $data = $this->getOne($id) )
		{
			$log = 'Trash '.$action.' ID: '.$id;
			$data = array($data);
		}
		
		if ( ! is_array($data) ) {
			Load::Log()->write($log.' Object not faund');
			Load::Msg()->error('Объект не найден!');
			Core::redirect('admin/library/trash/list/');
		}

		$acl = Load::Acl();
		$check = true;
		
		$delete = array();
		foreach ( $data as $num => $val )
		{
			if ( $val['acl'] AND ! $acl->check($val['acl'], false) ){
				$check = false;
				continue;
			}
			if ( $model = Load::Single('model', $val['model']) AND $model->$val[$action]($val['data']) )
				$delete[] = $val['id'];
		}

		if ( $check === false )
		{
			Load::Msg()->error('Нехватает прав на '
						.( $action === 'restore' ? 'восстановление' : 'удаление' )
						.' некоторых объектов.');
			$log .= ' Acl failed.';
		}
		
		if ( count($delete) > 0 )
		{
			$this->delete($delete);
			if ( count($delete) !== count($id) )
			{
				Load::Log()->write($log.' Completed in part: '.implode(',', $delete));
				Load::Msg()->error('Действие выполнено частично. Количество '
						.( $action === 'restore' ? 'восстановленных' : 'удаленных' )
						.' объектов: '.count($delete).' из '.count($id));
			} else {
				Load::Log()->write($log.' Done');
				Load::Msg()->ok('Действие выполнено. Количество '
						.( $action === 'restore' ? 'восстановленных' : 'удаленных' )
						.' объектов: '.count($delete));
			}
		} else {
			Load::Log()->write($log.' Fail');
			Load::Msg()->error('Действие не выполнено. Не один из объектов небыл '
						.( $action === 'restore' ? 'восстановлен' : 'удален' ) .'.');
		}
		
		Core::redirect('admin/c/m/trash/list/');
	}
	
	/**
	 * @var	array	data for restore and delete methods
	 * @var	string 	model
	 * @var	string 	method for restore
	 * @var	string	method for delete
 	 * @var	string	needed role for use restore/delete
	 **/
	public function add($data, $title, $model, $restore, $delete, $acl = false)
	{
		$stmt = $this->DB->prepare(
			"INSERT INTO `".$this->trash."`	(`acl`, `title`, `model`, `restore`, `delete`, `data`)
				VALUES (:acl, :title, :model, :restore, :delete, :data)");
		$stmt->bindParam(':acl', $acl, PDO::PARAM_STR);
		$stmt->bindParam(':title', $title, PDO::PARAM_STR);
		$stmt->bindParam(':model', $model, PDO::PARAM_STR);
		$stmt->bindParam(':restore', $restore, PDO::PARAM_STR);
		$stmt->bindParam(':delete', $delete, PDO::PARAM_STR);
		$stmt->bindParam(':data', $data, PDO::PARAM_STR);
		return $stmt->execute();
	}
	
	/**
	 * @var	int		start
	 * @var	int		limit
	 **/
	public function getList($start, $limit)
	{
		$stmt = $this->DB->prepare("SELECT SQL_CALC_FOUND_ROWS t.*, t.* FROM `".$this->trash."` t LIMIT ".$start.", ".$limit);
		$stmt->execute();
		if ( $stmt->rowCount() == 0) return false;
		$data['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$data['total'] = $this->DB->query('SELECT FOUND_ROWS() as total')->fetch(PDO::FETCH_OBJ)->total;
		return $data;
	}
	
	/**
	 * @var	int		row id
	 **/
	public function getOne($id)
	{
		$stmt = $this->DB->prepare("SELECT * FROM `".$this->trash."` WHERE `id` = :id LIMIT 1");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		if ( $stmt->rowCount() == 0) return false;
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	/**
	 * @var	array	array of id
	 **/
	public function getMany($id)
	{
		$stmt = $this->DB->prepare("SELECT * FROM `".$this->trash."` WHERE `id` IN (".implode( ',' , $id ).")");
		$stmt->execute();
		
		if ( $stmt->rowCount() == 0) return false;
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 **/
	public function getAll($id)
	{
		$stmt = $this->DB->prepare("SELECT * FROM `".$this->trash."`");
		$stmt->execute();
		if ( $stmt->rowCount() == 0) return false;
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 * @var	array	array of id
	 **/
	public function delete($id)
	{
		return $this->DB->prepare("DELETE FROM `".$this->trash."` WHERE `id` IN (".implode( ',' , $id ).")")->execute();
	}
}