<?
class PollController extends ArticleController
{
	public $options = array();
	public $results = array();
	public $total = 0;
	public $color = array('red', 'green', 'blue', 'yellow', 'orange');

	protected $_model = 'Poll';

	function init()
	{
		$this->folder->config = array_merge(self::$config, $this->folder->config);
		$this->model = new $this->_model($this->folder);
	}

	public function vote()
	{
		$article = $this->model->get($_POST['id']);	
		$result_uri = $this->folder->path . 'result/' . $article->slug;
		if(!isset($_SESSION['vote'])) return $result_uri;
		$cookie = 'poll' . $_POST['id'];
		if (isset($_COOKIE[$cookie])) return $result_uri;

		if ($_SERVER['HTTP_USER_AGENT'] && isset($_POST['opt'])) {
			$article->saveVote($_POST);
		}
		return $result_uri;
	}

	public function result()
	{
		$article = $this->model->getBySlug($this->params[0]);

		foreach($article->options AS $k => $v) {
			$count = $this->model->getCount($k);
			$article->results[$k]['count'] = $count;
			$article->total += $count;
		}
		$i = 0;
		foreach($article->results as $k => $v) {
			$article->results[$k]['pct'] = 100 * $v['count'] / $article->total;
			$article->results[$k]['color'] = $article->color[$i++];
		}
			
		return $article;
	}

}
