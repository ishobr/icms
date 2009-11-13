<?

Class Cache
{
	private static $handler = null;
	private static $backend;
	private static $opts = array();

	public static function init($backend, $opt) 
	{
		self::$backend = $backend;
		$backend_func = self::$backend . '_init';
		self::$backend_func($opt);
	}


	private function file_init($opt)
	{
		
	}

	private function memcache_init($opt) 
	{
		if (!$opt['host']) return;

		self::$handler = new Memcache;
		self::$handler->connect($opt['host'], $opt['port']);
	}


	public static function set($key, $val, $expire=90000) 
	{
		$set_func = self::$backend . '_set';
		return self::$set_func($key, $val, $expire);
	}

	public static function get($key) 
	{
		$get_func = self::$backend . '_get';
		return self::$get_func($key);
	}

	public static function delete($key, $timeout=0) 
	{
		$get_func = self::$backend . '_delete';
		return self::$get_func($key, $timeout);
	}

	public static function delete_all()
	{
		$get_func = self::$backend . '_delete_all';
		return self::$get_func();
	}

	public static function reg($key)
	{
		if (!$reg = self::get('reg'))
			$reg = array();

		if (!in_array($key, $reg))
			$reg[] = $key;

		self::set('reg', $reg);	
	}

	public static function regflush(&$path)
	{
		if (!$reg = self::get('reg')) return;

		$slash2 = strpos($path, '/', 1); // find 2nd slash
		$p1 = substr($path, 0, $slash2);
		$n = 0;
		foreach ($reg as &$r) {
			if (strpos($r, $p1) == 0) {
				self::delete($r, 17);
				unset($reg[$n]);
			}
			++$n;	
		}
		self::set('reg', $reg);
	}

	private function file_name($key)
	{
		if (!$key) return;
		$folder = sprintf(ROOT_DIR . '/cache/%x/', ord(substr($key, -1)) & 0xf); 
		if (!file_exists($folder))
			mkdir($folder);
		return $folder . preg_replace('@\W+@', '_', $key);
	}

	private function file_set($key, $val, $expire)
	{
		if ($expire)
			$expire += $_SERVER['REQUEST_TIME'];
		file_put_contents(self::file_name($key), $expire . ';' . serialize($val));
	}

	private function file_get($key)
	{
		$cache_file = self::file_name($key);
		if (!file_exists($cache_file) || !$cache = file_get_contents($cache_file)) return false;

		list($expire, $val) = explode(';', $cache, 2);
		if ($expire && $expire < $_SERVER['REQUEST_TIME'])
				return false;
		return unserialize($val);
	}

	private function file_delete($key)
	{
		unlink(self::file_name($key));
	}

	private function file_delete_all()
	{
		exec('find ' . ROOT_DIR . '/cache -type f -exec rm -vf {} \;');
	}

	private function memcache_set($key, $val, $expire)
	{
		if (!self::$handler) {
			return false;
		}
		$comp = (is_object($val)) ?  MEMCACHE_COMPRESSED : 0;
		return self::$handler->set($key, $val, $comp, $expire);
	}
	
	private function memcache_get($key)
	{
		if (!self::$handler) return false;
		return self::$handler->get($key);
	}

	private function memcache_delete($key, $timeout)
	{
		if (!self::$handler) return false;
		return self::$handler->delete($key, $timeout);
	}
}
