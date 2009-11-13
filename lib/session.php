<?
class Session extends Model
{
	function __construct()
	{
	}

	public static function start()
	{
		if (!self::$dbh) self::connect();
		
		session_set_save_handler(array('Session', 'open'), array('Session', 'close'), array('Session', 'read'), array('Session', 'write'), array('Session', 'destroy'), array('Session', 'gc'));
		session_start();
	}

	public static function open()
	{
		return true;
	}

	public static function close()
	{
		return true;
	}
	public static function read($id)
	{
		$data = self::$dbh->getOne('SELECT data FROM sessions WHERE id=?', array($id));
		if ($data) return $data;
		else return '';
	}
	public static function write($id, $data)
	{
		$res = self::$dbh->query('REPLACE INTO sessions VALUES (?, ?, ?)', array($id, $_SERVER['REQUEST_TIME'], $data));
		return (bool) self::$dbh->affectedRows();
	}
	public static function destroy($id)
	{
		$res = self::$dbh->query('DELETE FROM sessions WHERE id=?', $id);
		return (bool) self::$dbh->affectedRows();
	}
	public static function gc($max)
	{
  		$old = $_SERVER['REQUEST_TIME'] - $max;
		$res = self::$dbh->query('DELETE FROM sessions WHERE access < ?', $old);
		return (bool) self::$dbh->affectedRows();
	}
}
