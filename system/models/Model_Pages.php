<?php
class Model_Pages
{
	protected 	$tPages = 'pages',
				$tRevisions = 'pages_revisions',
				$DB;
	
	function __construct() {
		if ( ! $this->DB = Load::Database() ) throw new Exception('Database model not faund', 404);
	}
		
	public function getByUri($uri)
	{
		$stmt = $this->DB->prepare(
			"SELECT p.*, r.date, r.content, r.status
			FROM `".$this->tPages."` p
				INNER JOIN `".$this->tRevisions."` r
					ON `r`.`pages_id` = `p`.`id` AND `r`.`version` = `p`.`version` 
			WHERE `p`.`uri` = :uri AND `p`.`deleted` = '0'
			LIMIT 1"
			);
		$stmt->bindParam(':uri', $uri, PDO::PARAM_STR);
		$stmt->execute();
		if ( $stmt->rowCount() == 0) return false;
		return $stmt->fetch(PDO::FETCH_OBJ);
	}
	
	public function getById($id)
	{
		$stmt = $this->DB->prepare(
			"SELECT p.*, r.date, r.status
			FROM `".$this->tPages."` p
				INNER JOIN `".$this->tRevisions."` r
					ON `r`.`pages_id` = `p`.`id` AND `r`.`version` = `p`.`version` 
			WHERE `p`.`id` = :id AND `p`.`deleted` = '0'
			LIMIT 1"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		if ( $stmt->rowCount() == 0) return false;
		return $stmt->fetch(PDO::FETCH_OBJ);
	}
	
	public function getList($start, $limit)
	{
		$stmt = $this->DB->prepare(
			"SELECT SQL_CALC_FOUND_ROWS p.*, p.*, r.date, r.status
			FROM `".$this->tPages."` p
				INNER JOIN `".$this->tRevisions."` r
					ON `r`.`pages_id` = `p`.`id` AND `r`.`version` = `p`.`version` 
			WHERE `p`.`deleted` = '0'
			ORDER BY `p`.`uri` ASC
			LIMIT ".$start.", ".$limit
		);
		$stmt->execute();
		if ( $stmt->rowCount() == 0) return false;
		$data['pages'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$data['total'] = $this->DB->query('SELECT FOUND_ROWS() as total')->fetch(PDO::FETCH_OBJ)->total;
		return $data;
	}
	
	public function setById($id, $arr)
	{
		foreach ( $arr as $key => $val )
		{
			if ( ! isset($set) ) { $set = ''; } else { $set .= ', '; }
			$set .= "`".$key."` = '".$val."'";
		}

		if ( ! isset($set) ) return false;
		$stmt = $this->DB->prepare(
			"UPDATE `".$this->tPages."` SET ".$set."
			WHERE `id` = :id AND `deleted` = '0'
			LIMIT 1"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		return $stmt->execute();
	}
	
	public function getHistory($id)
	{
		$stmt = $this->DB->prepare(
			"SELECT date, id, status, version, user_id, user_login
			FROM `".$this->tRevisions."` 
			WHERE `pages_id` = :id
			ORDER BY `version` DESC"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		if ( $stmt->rowCount() == 0) return false;
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function setLocked($id, $uid, $ulogin)
	{
		$stmt = $this->DB->prepare(
			"UPDATE `".$this->tPages."` SET `locked_time` = NOW(), `locked_id` = '".$uid."', `locked_login` = '".$ulogin."'
			WHERE `id` = :id AND `deleted` = '0'
			LIMIT 1"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		if ( $stmt->rowCount() == 0) return false;
		return true;
	}
	
	public function addRevision($id, $uid, $ulogin, $content, $version = false)
	{	
		if ( $version === false )
		{
			$version = $this->getLatestVersion($id) + 1;
		}

		$stmt = $this->DB->prepare(
			"INSERT INTO `".$this->tRevisions."`
				(`pages_id`, `user_id`, `user_login`, `status`, `version`, `content`)
			VALUES (:id, :uid, :ulogin, '0', :version, :content)"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':version', $version, PDO::PARAM_INT);
		$stmt->bindParam(':ulogin', $ulogin, PDO::PARAM_STR);
		$stmt->bindParam(':content', $content, PDO::PARAM_STR);
		
		$stmt->execute();
		return true;
	}
	
	public function getLatestVersion($id)
	{
		$stmt = $this->DB->prepare("SELECT MAX(version) FROM `".$this->tRevisions."` WHERE `pages_id` = :id");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		if ( $stmt->rowCount() == 0) return 0;
		return $stmt->fetchColumn();

	}
	
	public function getVersion($id, $version)
	{
		$stmt = $this->DB->prepare(
			"SELECT p.*, r.date, r.content, r.status
			FROM `".$this->tPages."` p
				INNER JOIN `".$this->tRevisions."` r
					ON `r`.`pages_id` = `p`.`id` AND `r`.`version` = :version 
			WHERE `p`.`id` = :id AND `p`.`deleted` = '0'
			LIMIT 1"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->bindParam(':version', $version, PDO::PARAM_INT);
		$stmt->execute();
		if ( $stmt->rowCount() == 0) return false;
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public function getVersions($id, $versions)
	{
		if ( count($versions) == 1 ) return false;
		
		$sql = '';
		foreach ( $versions as $ver) {
			if ( $ver != intval($ver) ) return false;
			if ( $sql != '' ) $sql .= " OR ";
			$sql .= "`r`.`version` = '".$ver."'";
		}
		 
		$stmt = $this->DB->prepare(
			"SELECT r.content
			FROM `".$this->tPages."` p
				INNER JOIN `".$this->tRevisions."` r
					ON `r`.`pages_id` = `p`.`id` AND (".$sql.")
			WHERE `p`.`id` = :id AND `p`.`deleted` = '0'
			ORDER BY `r`.`version` ASC
			LIMIT 2"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		if ( $stmt->rowCount() == 0) return false;
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function setActive($id, $version)
	{
		$stmt = $this->DB->prepare(
			"UPDATE `".$this->tRevisions."` SET `status` = '0'
			WHERE `pages_id` = :id AND `status` = '1'
			LIMIT 1"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		
		$stmt = $this->DB->prepare(
			"UPDATE `".$this->tRevisions."` SET `status` = '1'
			WHERE `pages_id` = :id AND `version` = :version
			LIMIT 1"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->bindParam(':version', $version, PDO::PARAM_INT);
		$stmt->execute();
		
		$stmt = $this->DB->prepare(
			"UPDATE `".$this->tPages."` SET `version` = :version
			WHERE `id` = :id
			LIMIT 1"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->bindParam(':version', $version, PDO::PARAM_INT);
		$stmt->execute();

		return true;
	}
	
	public function toDraft($id)
	{
		$stmt = $this->DB->prepare(
			"UPDATE `".$this->tRevisions."` SET `status` = '0'
			WHERE `pages_id` = :id AND `status` != '0'
			LIMIT 1"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	public function setModeration($id, $version)
	{
		$stmt = $this->DB->prepare(
			"UPDATE `".$this->tRevisions."` SET `status` = '0'
			WHERE `pages_id` = :id AND `status` = '3'
			LIMIT 1"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		
		$stmt = $this->DB->prepare(
			"UPDATE `".$this->tRevisions."` SET `status` = '3'
			WHERE `pages_id` = :id AND `version` = :version
			LIMIT 1"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->bindParam(':version', $version, PDO::PARAM_INT);
		$stmt->execute();

		return true;
	}
	
	public function addPage($uri, $uid, $ulogin, $meta)
	{
		$stmt = $this->DB->prepare(
			"INSERT INTO `".$this->tPages."`
				(`owner_id`, `owner_login`, `uri`, `meta`)
			VALUES (:uid, :ulogin, :uri, :meta)"
			);
		$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindParam(':ulogin', $ulogin, PDO::PARAM_STR);
		$stmt->bindParam(':uri', $uri, PDO::PARAM_STR);
		$stmt->bindParam(':meta', $meta, PDO::PARAM_STR);
		
		$stmt->execute();
		$id = $this->DB->lastInsertId();
		return $this->addRevision($id, $uid, $ulogin, '', 1);
	}
	
	public function restorePage($id)
	{
		$stmt = $this->DB->prepare("UPDATE `".$this->tPages."` SET `deleted` = '0' WHERE `id` = :id LIMIT 1");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		return $stmt->execute();
	}
	
	public function deletePage($id)
	{
		$stmt = $this->DB->prepare("DELETE FROM `".$this->tPages."` WHERE `id` = :id LIMIT 1");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		if ( ! $stmt->execute() ) return false;
		
		$stmt = $this->DB->prepare("DELETE FROM `".$this->tRevisions."` WHERE `pages_id` = :id LIMIT 1");
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		return $stmt->execute();
	}
	
	public function addToTrash($id, $uid, $ulogin)
	{
		$stmt = $this->DB->prepare(
			"UPDATE `".$this->tPages."` SET `deleted` = '1', `locked_time` = NOW(), `locked_id` = '".$uid."', `locked_login` = '".$ulogin."'
			WHERE `id` = :id
			LIMIT 1"
			);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
	}
}