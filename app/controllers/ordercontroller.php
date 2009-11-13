<?
class OrderController extends Action
{
	public static $config = array(
		'arcLimit' => 15
	);

	protected $_model = 'Store';

	public function init()
	{
		Ctrl::$setCache = 0;
		$this->folder->config = array_merge(self::$config, $this->folder->config);
		$this->model = new $this->_model($this->folder);
	}

	public function index()
	{
		if (!User::root()) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			return '/error/denied/';
		}

		View::$title = 'Order List';
		if (!isset($this->params[0])) $this->params[0] = 0;
		$page = (int) $this->params[0];

		$offset = $this->folder->config['arcLimit'] * $page;	

		return $this->model->getOrder($offset, $this->folder->config['arcLimit']);
	}
}
