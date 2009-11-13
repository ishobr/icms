<?php

class StoreController extends ArticleController
{
	protected $_model = 'Store';

	public function init()
	{
		self::$config = $this->folder->config = array_merge(self::$config, $this->folder->config);
		$this->model = new $this->_model($this->folder);
	}

	public function order()
	{
		$product = $this->model->getBySlug($this->params[0]);
		$product->saveOrder();
		return $this->folder->path . 'chart/';
	}
}
