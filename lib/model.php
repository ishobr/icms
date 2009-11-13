<?php

// Article/News Component Constants,
// as field on data revision;
define('SUBJECT', 1);
define('LEAD', 2);
define('BODY', 3);
define('ANSWER', 4);
define('QUESTION', 5);
define('KICKER', 6);
define('DECK', 7);
define('CALLOUT', 8);
define('SRC', 9);
define('TAG', 10);
define('PRICE', 11);
define('ALIAS', 12);

require 'DB.php';

class Model {

	protected static $dbh = false;
	public $folder;

	function __construct(&$folder)
	{
		$this->folder =& $folder;
		if (!self::$dbh) $this->connect();
		$this->init();
	}

	public function init()
	{
	}

	protected function connect($dsn='') {
		global $config;
		$opt = array('debug' => 3, 'persistent' => TRUE);
		self::$dbh = DB::connect($config['dsn'], $opt);
		if (PEAR::isError(self::$dbh))
			self::fatalError(self::$dbh);
		self::$dbh->setFetchMode(DB_FETCHMODE_ASSOC);
	}

	/*
	 * Tampilkan fatal error di layar
	 * Rincian error dikirim ke error_log
	 * $_SERVER['REQUEST_TIME'] sbg penghubung
	 */
	protected function fatalError(&$err) {
		error_log($err->getDebugInfo() . ' (' . $_SERVER['REQUEST_TIME'] . ')', 0);
		/*echo 'Fatal Error: ' . $_SERVER['REQUEST_TIME'];
		$bt = debug_backtrace();
		foreach($bt as $line) {
			$args = var_export($line['args'], true);
			echo "{$line['function']}($args) at {$line['file']}:{$line['line']}\n";
		}
		echo "</pre>";*/
		die('Internal database error');
	}

	protected static function same_txt(&$txt1, &$txt2)
	{
		return !strcmp($txt1, $txt2);
	}


	/* Long time format
	 * Parameter:
	 * $tm : Unix TimeStamp
	 */
	protected static function &longTime($tm)
	{
		//return strftime('%e %b %y %H:%M WIB', $tm);
		$ret =  strftime('%A, %d %b %Y %H:%M', $tm);
		return $ret;
	}

	public static function permalink_str($str)
	{
		$str = strtolower(trim($str));
		$s = array(
			'/[^\w. -]/',
			'/\b(ini|itu|yang|kepada|untuk|di|dari|dari|oleh)\b/i',
			'/  /',
			'/ /');
			$r = array('','',' ','-');
		$str = preg_replace($s, $r, $str);
		$str = preg_replace('/-+/', '-', $str);
		return $str;
	}
}
