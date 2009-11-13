<?
class Download extends Article
{
	public $files = array();

	function __construct(&$folder)
	{
		parent::__construct($folder);
	}

	protected function get($id)
	{
		parent::get($id);
		$this->files = array();
		$res =& self::$dbh->query('SELECT * FROM article_file WHERE article_id=?', array($this->id));
		$i = 0;
		while ($row =& $res->fetchRow()) {
			$file = new Files($row['file_id']);
			$this->files[] = $file;
		}
	}

	public function &panel()
	{
		$ret =& parent::panel();
		if ($this->editable()) {
			$ret[] = array('href' => '/' . $this->folder->path . 'addfile/' . $this->id, 'title' => 'Add File', 'class' => 'addfile', 'onclick' => '');
		}
		return $ret;
	}

	public function &add()
	{
		parent::add();
		if ($_POST['file'])
			$this->addFile();
		return $this;
	}
	public function addFile()
	{
		if (!$this->id)
			$this->get((int)$this->folder->params[0]);

		if (!$this->editable()) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			return '/error/denied/';
		}

		if ($_POST['submit']) {
			$file = new Files();
			if ($file_id = $file->save($_FILES['file'], $this->folder->path)) {
				self::$dbh->query('INSERT INTO article_file (file_id, article_id) VALUES (?, ?)', array($file_id, $this->id));
				return $this->permalink;
			}
		}
		return $this;
	}
	
	public function &view()
	{
		parent::view();
		return $this;
	}

	public function &getFile()
	{
		if (!$id = (int)$this->folder->params[0])
			return array();
		$file = new Files($id);
		ob_clean();
		header('Content-Disposition: attachment; filename=' . basename($file->name) . '; size=' . $file->size);
		header('Content-Type: ' . $file->type);
		readfile($file->name);
		die();
	}

	public function &delfile()
	{
		$this->get((int)$this->folder->params[0]);
		$file_id = (int) $this->folder->params[1];

		if (!$this->editable()) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			return '/error/denied/';
		}
		$file = new Files($file_id);
		$file->del();
		self::$dbh->query('DELETE FROM article_file WHERE article_id=? AND file_id=?', array($this->id, $file_id));
		return $this->permalink;
	}
	public function &editfile()
	{
		$this->get((int)$this->folder->params[0]);

		if (!$this->editable()) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			return '/error/denied/';
		}

		if ($_POST['submit']) {
			$file = new Files();
			if ($file_id = $file->save($_POST['photo'], $this->folder->path)) {
				$dbh->query('INSERT INTO article_file (file_id, article_id) VALUES (?, ?)', array($file_id, $this->id));
				return $this->permalink;
			}
		}
		return $this;
	}
}
