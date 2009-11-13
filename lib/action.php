<?php

abstract class Action {

	protected $folder;
	protected $action;
	protected $params;
	protected $model;

	function __construct(&$folder, &$action, &$params)
	{
		$this->folder =& $folder;
		$this->action =& $action;
		$this->params =& $params;
		$this->init();
	}

	protected function init()
	{
	}
}
