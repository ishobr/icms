<?php

class Folder extends Model
{
	public $id = 0;
	public $parent_id = 0;
	public $short = '';
	public $name = '';
	public $model = '';
	public $ctrl = '';
	public $sort_id = 0;
	public $path = '';
	public $contributor = array();
	public $editor = array();
	public $approver = array();
	public $subFolders = array();
	public $uri;

	function __construct()
	{
		if (!self::$dbh)
			$this->connect();
	}

	public static function getByUri(&$uri)
	{
		$ret = new self();
		if (!$folders = Cache::get('f')) {
			$folders = $ret->getFolder(0);	
			Cache::set('f', $folders);
		}
		foreach ($folders as $k => $v) {
			if (strncmp($k, $uri, $v[1]) == 0){
				$ret->id = $v[0];
				$ret->path = $k . '/';
			}
		}

		$key = 'f' . $ret->id;
		if ($c = Cache::get($key))
			return $c;

		$ret->get();
		Cache::set($key, $ret);

		return $ret;
	}

	public static function getById($id)
	{
		$key = 'f' . $id;
		if ($c = Cache::get($key))
			return $c;

		$ret = new self();
		$ret->id = $id;
		$ret->path = self::path($id);
		$ret->get();
		Cache::set($key, $ret);
		return $ret;
	}

	protected function getFolder($parent_id, $path='')
	{
		static $folders = array();

		$res = self::$dbh->query('SELECT short, id FROM folder WHERE parent_id=? ORDER BY id', array($parent_id));

		while ($row = $res->fetchRow()) {
			$newpath = $path . '/' . $row['short'];
			$folders[$newpath] = array($row['id'], strlen($newpath));
			$this->getFolder($row['id'], $newpath);
		}
		return $folders;
	}

	protected function get()
	{
		$this->initAdmin();

		$row = self::$dbh->getRow('SELECT * FROM folder WHERE id=?', array($this->id));

		$this->getAdmin($row['id']);

		$this->parent_id =& $row['parent_id'];
		$this->short =& $row['short'];
		$this->name =& $row['name'];
		$this->model =& $row['model'];
		$this->ctrl =& $row['ctrl'];
		$this->sort_id =& $row['sort_id'];

		$this->config = self::getConfig($this->id);
		if ($this->ctrl) {
			$ctrlConfig = get_class_vars($this->ctrl . 'Controller');
		//	if (isset($ctrlConfig['config'])) {
				$this->config = array_merge($ctrlConfig['config'], $this->config);
		//	}
		}
		$this->getSubFolders($this->id);
	}

	public static function getConfig($id)
	{
		//$ctrl = self::$ctrl . 'Controller';
		//print_r(get_class_vars($ctrl));die;
		return $dbCfg = self::$dbh->getAssoc('SElECT name, value FROM folder_config WHERE folder_id=?', false, array($id));
		//$cfg = $ctrl::$config;
		$dbCfg = self::$dbh->getAssoc('SElECT name, value FROM folder_config WHERE folder_id=?', false, array($id));
		return array_merge($cfg, $dbCfg);
	}


	private function initAdmin()
	{
		$res = self::$dbh->query('SELECT id FROM user WHERE level <= 3'); 		while ($row =& $res->fetchRow()) {
			$this->contributor[] = $row['id'];
			$this->editor[] = $row['id'];
			$this->approver[] = $row['id'];
		}
	}

	private function getAdmin($id)
	{
			$res = self::$dbh->query('SELECT * FROM folder_admin WHERE folder_id=?', array($id));
			while ($row =& $res->fetchRow()) {
				$this->contributor[] = $row['user_id'];
				if ($row['edit'])
					$this->editor[] = $row['user_id'];
				if ($row['publish'])
					$this->approver[] = $row['user_id'];
			}
	}

	public function isContributor($uid)
	{
		return (in_array($uid, $this->contributor));
	}
	public function isEditor($uid)
	{
		return (in_array($uid, $this->editor));
	}
	public function isApprover($uid)
	{
		return (in_array($uid, $this->approver));
	}

	private function getTree($id, $path='')
	{
		$ret = array();
		$res = self::$dbh->query('SELECT * FROM folder JOIN folder_tree ON id=folder_id WHERE special=0 AND parent_id=?', array($id));
		if (!$res->numRows()) 
			return array();

		while ($row =& $res->fetchRow()) {
			$node = array();
			$node['id'] = (int) $row['id'];
			$node['text'] = $row['name'];
			$node['path'] = $path . '/' . $row['short'];
			$node['qtip'] = sprintf('path: %s id: %s', $node['path'], $node['id']);
			if ($child = $this->getTree($row['id'], $node['path']))
				$node['children'] = $child;
			else
				$node['leaf'] = (bool) true;
			$ret[] = $node;
		}
		return $ret;
	}
	
	public function tree()
	{
		return json_encode($this->getTree(0));
	}

	public function jsonGet()
	{		
		$ret = array();
		$res =& self::$dbh->getRow('SELECT * FROM folder WHERE id=?', array(5));
		$ret[] = $res;
		return sprintf('{total: 1, data: %s}', json_encode($ret));
	}

	public static function path($id)
	{
		$paths = array();
		$path = '';
		while ($row =& self::$dbh->getRow('SELECT short, parent_id FROM folder WHERE id AND id=?', array($id))) {
			$paths[] = $row['short'];
			$id = $row['parent_id'];
			if (!$id) break;
		}
		if ($paths = array_reverse($paths))
			$path = '/' . implode('/', $paths);
		return $path . '/';
	}


	private function getSubFolders($id)
	{
		$res = self::$dbh->query('SELECT * FROM folder WHERE special=0 AND parent_id=?', array($id));
		while ($row = $res->fetchRow()) {
			if ($row['model'] != 'Dummy') {
				$this->subFolders[] = $row['id'];
			}
			$this->getSubFolders($row['id']);
		}
	}

	public function jsonSave()
	{
		if ($_POST['model']) {
			$folder = strtolower($_POST['short']);
			$model = strtolower($_POST['model']);
			$last_id = self::$dbh->getOne('SELECT id FROM folder ORDER BY id DESC LIMIT 1');
			self::$dbh->query('INSERT INTO folder (id, parent_id, short, name, model, active) VALUES (?, ?, ?, ?, ?, 1)', array(++$last_id, $_POST['parent_id'], $folder, $_POST['name'], $_POST['model']));	
			if (self::$dbh->affectedRows()) {
				$folder = 'tpl/' . View::$theme . '/' . $folder;
				$model = 'tpl/std/' . $model;
				if (mkdir($folder, 0777, true)) {
					chmod($folder, 0777);	
					$d = dir($model);
					while (false !== ($entry = $d->read())) {
						if (preg_match('/^\./', $entry)) continue;
						copy($model . '/' . $entry, $folder . '/' . $entry);
						chmod($folder . '/' . $entry, 0666);	
					}
				}
				return '{success: true}';
			}
		}
		return '{success: false}';
	}
}
