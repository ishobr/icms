<?
class Search extends Article
{
	public static $keyword;

	public function index()
	{
		self::$keyword = $_GET['keyword'];
		$page = (int) $this->folder->params[0];
		$res =& self::$dbh->query('SELECT * FROM article_search WHERE match(subject, kicker, body) AGAINST (?) LIMIT 17', array(self::$keyword));
		$total = $res->numRows();
		$ret = array();
		while ($row =& $res->fetchRow())
			$ret[] = $row;
		return $ret;
	}
	
	public function get($keyword, $page=0)
	{
		$ret = array();
		$res =& self::$dbh->query('SELECT * FROM article_search WHERE match(subject, kicker, body) AGAINST (?) LIMIT 17', array($keyword));
		while ($row =& $res->fetchRow())
			$ret[] = $row;
		return $ret;
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

	public function getRelated($id, $keyword, $limit=5)
	{
		$limit = $limit % 100; // Max limit 100
		$keyword = preg_replace(array('/,\s*/', '/^/'), array(' ', '+'), $keyword);
		$day100 = $_SERVER['REQUEST_TIME'] - 8640000;
		$res =& self::$dbh->query('SELECT * FROM article_search WHERE rev_on > ! AND match(subject, kicker, body) AGAINST (?) AND article_id<>? LIMIT !', array($day100, $keyword, $id, $limit));
		$ret = array();
		while ($row =& $res->fetchRow())
			$ret[] = $row;
		return $ret;
	}


	/*public function panel()
	{
		return array();
	}*/

	/**
	 * Top articles last 24 hours
	 */
	public function getTop($limit)
	{
		global $config;

		$key = 'top100';

		if ($config['use_cron'] && $cache = Cache::get($key)) {
			return array_slice($cache, 0, $limit);
		}

		$today = strtotime('today');
		$top100 = self::$dbh->getAll("SELECT url, count(*) AS hits FROM access_log WHERE view = 1 AND log_on > $today GROUP BY url ORDER BY hits DESC LIMIT 0,100"); 
		
		$objs = $cph = array();
		foreach ($top100 as &$top) {
			$obj = Ctrl::fetchData($top['url']);
			if (!is_object($obj) || !isset($obj->subject)) continue;
			$pub_on = $obj->pub_on < $today ? $today : $obj->pub_on;
			$live = $_SERVER['REQUEST_TIME'] - $pub_on;
			$obj->cph = $top['hits']*3600/$live; // clicks per hour
			$objs[] = $obj;
			$cph[] = $obj->cph;
		}

		array_multisort($cph, SORT_DESC, $objs);
		return array_slice($objs, 0, $limit);
	}

	/*
 	 * New Content
	 */
	public function getNews($limit)
	{
		$key = 'news' . $limit;
		if (!$obj = Cache::get($key)) {
			$obj = self::$dbh->getAll("SELECT folder_id, slug FROM article WHERE pub_on > 0 AND pub_on < ? ORDER BY pub_on DESC LIMIT 0,$limit", array($_SERVER['REQUEST_TIME'])); 
			Cache::set($key, $obj, 300);
		}
		return $obj;
	}
}
