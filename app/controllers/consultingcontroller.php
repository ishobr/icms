<?
define('FLAG_HAVE_ANSWER', 1);

class ConsultingController extends ArticleController
{
	protected $_model = 'Consulting';

	public function init()
	{
		self::$config = $this->folder->config = array_merge(self::$config, $this->folder->config);
		$this->model = new $this->_model($this->folder);
	}

	public function add()
	{
		return $this->folder->path . '/ask';
	}

	public function ask()
	{
		$this->folder->config['publicPost'] = 1;
		return parent::post();
	}

	public function panel(&$data)
	{
		$ret = parent::panel($data);

		if (is_array($data)) {
			if (!count($data)) return $ret;
			$obj = reset($data);
		} else $obj =& $data;
		if ($obj->folder->isContributor(User::$id)) {
			// Inbox
			array_unshift($ret, array('href' => $this->folder->path . 'inbox', 'title' => 'Inbox', 'class' => 'inbox', 'onclick' => ''));
		}
		return $ret;
	}

	public function draft()
	{
		if (!$this->folder->isEditor(User::$id))
			return $this->folder->path;
		
		if (!isset($this->params[0])) $this->params[0] = 0;
		$page = (int) $this->params[0];

		$ret = array();
		$pub = false;
		$offset = $this->folder->config['arcLimit'] * $page;
		return $this->model->getLast($offset, $this->folder->config['arcLimit'], $pub, false, FLAG_HAVE_ANSWER);
	}

	public function inbox()
	{
		if (!$this->folder->isEditor(User::$id))
			return $this->folder->path;
		
		if (!isset($this->params[0])) $this->params[0] = 0;
		$page = (int) $this->params[0];

		$ret = array();
		$pub = false;
		$offset = $this->folder->config['arcLimit'] * $page;	
		return $this->model->getLast($offset, $this->folder->config['arcLimit'], false);
	}

}
