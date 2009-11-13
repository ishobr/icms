<?
class SearchController extends Action
{
	public static $keyword;
	protected $_model = 'Search';

	protected function init()
	{
		Ctrl::$setCache = 0;
		$this->model = new $this->_model($this->folder);
	}
	
	public function index()
	{
		self::$keyword = $_GET['keyword'];
		if (!isset($this->params[0])) $this->params[0] = 0;
		$page = (int) $this->params[0];
		return $this->model->get(self::$keyword, $page);
	}

	public function strictRelated()
	{
		$keyword = urldecode($this->folder->params[0]);
		$keyword = preg_replace(array('/^/', '/ /'), array('+', ' +'), $keyword);
		$res =& self::$dbh->query("SELECT * FROM article_search WHERE match(subject, kicker, body) AGAINST ('$keyword' IN BOOLEAN MODE) LIMIT 7");
		$ret = array();
		while ($row =& $res->fetchRow())
			$ret[] = $row;
		return $ret;
	}

	public function related()
	{
		$id = (int) $this->params[0];
		$keyword = urldecode($this->params[1]);
		$limit = (int) $this->params[2];
		return $this->model->getRelated($id, $keyword, $limit);
	}


	public function panel()
	{
		return array();
	}

	/**
	 * Today top articles
	 */
	public function top()
	{
		$limit = $this->params[0] % 33;
		return $this->model->getTop($limit);
	}

	public function news()
	{
		$limit = (int) $this->params[0];
		$news = $this->model->getNews($limit);
		$ret = array();
		foreach ($news as $new) {
			$folder = Folder::getById($new['folder_id']);
			$uri =  $folder->path . '/' . $new['slug'] . '.htm';
			$obj = Ctrl::fetchData($uri);
			if (!is_object($obj) || !$obj->subject) continue;
			$ret[] = $obj;
		}
		return $ret;
	}

	public function fpnews()
	{
		return $this->news();
	}
}
