<?php // vim:ts=3:sw=3

class AdminController extends Action
{
	public function index()
	{
		if (!User::$id || !User::root()) {
			return '/user/login/';
		}

		View::$theme = '';
		return array();
	}
}
