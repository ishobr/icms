<?php

// Constants
define('SET_CACHE', true);
define('USE_CACHE', true);

class Ctrl
{
	public static $action;
	public static $folder;
	public static $data;
	public static $setCache = 1;
	protected static $actionFound = 1;

	public static function dispatch()
	{
		global $config;

		preg_match('@([^?]+)@', $_SERVER['REQUEST_URI'], $match);
		$uri = preg_replace('/\/\//', '/', $match[1]);
		list(self::$folder, self::$action, $params) = self::explodeUri($uri);
		View::$theme =& $config['theme'];
		View::$site_content = self::fetchContent(self::$folder, self::$action, $params, true);

		View::out(self::$folder);

	}

	protected function explodeUri($uri)
	{
		$folder = Folder::getByUri($uri);
		if (!$folder->ctrl) { 
			// file based controller
			list(,$ctrl,) = explode('/', $uri, 3);
			if ($ctrl && include_once $ctrl . 'controller.php') {
				$folder->ctrl =& $ctrl;
				$folder->path = '/' . $ctrl . '/';
			} else {
				$folder->ctrl = 'index';
				$folder->path = '/index/';
			}
		}
		$action = 'index';
		$params = trim(substr($uri, strlen($folder->path)), '/');

		if ($params) {
			$params = explode('/', $params);

			if ($params)
				$action = array_shift($params);
		}

		// View action case
		if (strpos($action, '.htm')) {
			$params = array($action);
			$action = 'view';
		}
		return array($folder, $action, $params);
	}

	public static function fetch($uri, $setCache=true)
	{
		$key = $uri;
		if ($setCache && $obj = Cache::get($key)) {
			Cache::reg($key);
			return $obj;
		} 

		list($folder, $action, $params) = self::explodeUri($uri);
		$obj = self::fetchContent($folder, $action, $params);

		if (strpos($uri, 'search') || strpos($uri, 'service')) {
			$exp = 300;
		} else {
			$exp = 0;
			if (self::$actionFound && $setCache)
				Cache::reg($key);
		}

		if (self::$actionFound && $setCache)
			Cache::set($key, $obj, $exp);

		self::$actionFound = 1;
		return $obj;
	}

	/*
	 * Fetch only model
	 */
	public static function fetchData(&$uri)
	{
		list($folder, $action, $params) = self::explodeUri($uri);
		if (!$folder->ctrl) return '';

		if ($action == 'view')
			$action = 'dataByUri';

		$ctrl = $folder->ctrl . 'Controller';
		$c = new $ctrl($folder, $action, $params);

		if (method_exists($c, $action)) {
			return $c->$action();
		}
		return '';
	}

	/* Main fetch content (populated template) by uri
	 *
	 * @param f folder object
	 * @param a action
         * @param p parameters array
         * @param main true if main content
	 * @return array or object as view feed
	 */
	protected static function fetchContent(&$f, &$a, &$p, $main=false)
	{
		if (!$f->ctrl) return array();

		$ctrl = $f->ctrl . 'Controller';
		$C = new $ctrl($f, $a, $p);

		if (!method_exists($C, $a)) {
			self::$setCache = 0;
			self::$actionFound = 0;
			array_unshift($p, $a);
			$a = 'index';
		}
		
		$data = $C->$a();
		if (is_object($data) || is_array($data)) {
			if (is_array($data) && isset($data['total'])) {
				$total = $data['total'];
				$data = $data['data'];
			} else {
				$total = 0;
			}
			View::$frame = sprintf('tpl/%s%sframe.tpl.php', View::$theme, $f->path);
			$tpl = sprintf('tpl/%s%s/%s.tpl.php', View::$theme, $f->path,  $a);
			$input = array('folder' => $f, 'params' => $p, 'data' => $data, 'total' => $total);
			$ret = View::render($tpl, $input);
			if ($main) {
				self::$data =& $data;

				if (User::$id && !in_array($a, array('add', 'edit', 'post')) &&  method_exists($C, 'panel')) {
					$ret .= View::renderPanel($C->panel($data));
				}
			}
			return $ret;
		}
		
		if (strpos($data, '/') === 0 || strpos($data, 'http') === 0) {
			if (strpos($data, '/user/login') === 0 || strpos($data, '/error/denied') === 0)
				$_SESSION['LOGIN_REFERER'] = $_SERVER['REQUEST_URI'];	
			self::redirect($data);
		}

		// anything else
		echo $data;
		die();
	}

	public static function redirect(&$url)
	{
		ob_clean();
		header("Location: $url");
		die();
	}
}
