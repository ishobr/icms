<?
class PhotoController extends ArticleController
{
	protected $_model = 'Photo';

	function init()
	{
		$this->folder->config = array_merge(self::$config, $this->folder->config);
		$this->model = new $this->_model($this->folder);
	}
/*
	public function addPhoto()
	{
		$article = $this->model->get($this->params[0], false);

		if (!$article->editable()) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			return '/error/denied/';
		}

		View::$title = $article->subject . ' (Add Photo)';

		if (isset($_POST['submit']) && $article->insertPhoto($_FILES['photo'])) {
			return $article->permalink;
		}
		return $article;
	}
	
	public function del()
	{
		$ret = parent::del();

		$res = self::$dbh->query('SELECT * FROM article_file JOIN file ON article_file.file_id=file.id AND article_id=?', $this->id);
		while($row = $res->fetchRow()) {
			unlink($row['name']);
			self::$dbh->query('DELETE FROM file WHERE id=?', $row['id']);
		}

		return $ret;
	}

	public function delphoto()
	{
		$this->get((int)$this->folder->params[0]);
		if (!$this->editable()) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			return '/error/denied/';
		}
		return $this->permalink;
	}

	public function editphoto()
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
	}*/
}
