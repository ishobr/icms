<?
class Comment extends Model
{
	public $id;
	public $name;
	public $mail;
	public $website;
	public $comment;
	public $postTime;
	public $pubTime;
	public $pubUser;
	
	function __construct($id=0)
	{
		if ($id)
			$this->get($id);
	}

	public function get($id)
	{
		global $config;

		$row = self::$dbh->getRow('SELECT * FROM comment WHERE id=?', array($id));
		$this->id =& $row['id'];
		$this->name =& $row['name'];
		$this->mail =& $row['mail'];
		$this->website =& $row['website'];
		$this->comment =& $row['comment'];
		$this->postTime = strftime($config['datetime_fmt'], $row['post_on']);
		$this->pubTime = strftime($config['datetime_fmt'], $row['pub_on']);
		$this->pubUser =& User::getName($row['pub_by']);
	}

	public function save(&$data)
	{
		if (!$data['name'] || !$data['comment']) return false;
		if (!$name = self::$dbh->getOne('SELECT name FROM comment WHERE post_sess_id=?', session_id()))
			$name =& $data['name'];
		$res = self::$dbh->query('INSERT INTO comment (name, mail, website, comment, post_on, post_sess_id) VALUES (?, ?, ?, ?, ?, ?)', array(strip_tags($name), $data['mail'], $data['website'], htmlspecialchars($data['comment'], ENT_QUOTES), $_SERVER['REQUEST_TIME'], session_id()));
		return self::$dbh->getOne('SELECT id FROM comment WHERE post_on=? AND post_sess_id=?', array($_SERVER['REQUEST_TIME'], session_id()));
	}
}
