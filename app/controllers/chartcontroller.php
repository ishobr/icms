<?
class ChartController extends Action
{
	protected $_model = 'Store';

	public function init()
	{
		Ctrl::$setCache = 0;
		$this->model = new $this->_model($this->folder);
	}

	public function index()
	{
		if (isset($_POST['delete'])) {
			foreach ($_POST['items'] as $item)
				$this->model->delOrder($item);
		}

		if (isset($_POST['recalculate']))
			$this->model->updateOrder($_POST['qty']);

		return $this->model->getTmpOrder();
	}

	public function box()
	{
		return $this->model->getTmpOrder();
	}

	public function checkout()
	{
		if ($_POST['submit'] && $this->model->doCheckout($_POST))
			return $this->folder->path . 'checkoutsuccess';
		$ret = new stdClass();
		$ret->order = $this->model->getTmpOrder();
		$ret->user = array();
		return $ret;
	}

	public function checkoutSuccess()
	{
		return array();
	}

}
