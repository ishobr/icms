<?
Class FlvController extends ArticleController
{
	protected $_model = 'Flv';

	public function init()
	{
		$this->folder->config = array_merge(self::$config, $this->folder->config);
		$this->model = new $this->_model($this->folder);
	}
}
