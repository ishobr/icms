<?php

class ErrorController
{
	public $referer;
	public $folder;

	function __construct()
	{
		$this->referer = isset($_SESSION['error_uri']) ? $_SESSION['error_uri'] : '';
		$this->msg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : 'Error occured';
		unset($_SESSION['error_uri']);
		unset($_SESSION['error_msg']);
		Ctrl::$setCache = 0;
	}

	public function index()
	{
		return $this;
	}

	public function denied()
	{
		return $this;
	}
	public function noitem()
	{
		return $this;
	}
	public function notfound()
	{
		return $this;
	}

	public function reqlogin()
	{
		$_SESSION['REFERER'] = $this->referer;
		return $this;
	}
}
