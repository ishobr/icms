<?
class JadwalShalat extends Dbh
{
	function __construct()
	{
	}

	public function index()
	{
	}
	
	public function today()
	{
		return self::$dbh->getRow('SELECT * FROM jshalat WHERE day=?', array((int)strftime('%j')));
	}
}
