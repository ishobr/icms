<?

class User extends Model
{
	public static $id = 0;
	public $uid = '';
	public $name = '';
	public $mail;
	public $hp;
	public $level;
	public $groups = array();
	public static $user = array();

	public function init()
	{
		Ctrl::$setCache = 0;
	}

	public static function start()
	{
		if (!self::$dbh)
			self::connect();

		if (isset($_SESSION['uid']) && (int)$_SESSION['uid'] ) {
			self::$id =& $_SESSION['uid'];
			self::$user =& self::$dbh->getRow('SELECT * FROM user WHERE id=?', array(self::$id));
		}

		self::logIncoming();
	}

	private function logIncoming()
	{
		if (strpos($_SERVER['REQUEST_URI'], 'resource/')) return;

		if (!isset($_COOKIE['vid'])) {
			$expire = pow(2, 31)-1;
			setcookie('vid', $_SERVER['REQUEST_TIME'], $expire, '/');
		}
		$vid = isset($_COOKIE['vid']) ? $_COOKIE['vid'] : 0;
		
		$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$view = strpos($_SERVER['REQUEST_URI'], '.htm') ? 1 : 0;
		$res = self::$dbh->query('INSERT DELAYED INTO access_log (url,view, ref, ip, user_agent, sess_id, visitor_id, user_id, log_on) VALUES (?,?,?, INET_ATON(?),?,?,?,?,?)', array($_SERVER['REQUEST_URI'], $view, $referer, self::getUserIP(), $user_agent, session_id(), $vid, self::$id, $_SERVER['REQUEST_TIME']));
	}

	private function get() 
	{
		$user = self::$dbh->getRow('SELECT * FROM user WHERE id=?', array(self::$id));
		$this->name = $user['name'];
		$this->uid = $user['uid'];
		$this->mail = $user['mail'];
		$this->hp = $user['hp'];
		$this->level = $user['level'];

		$this->getGroup();
	}

	private function getGroup()
	{

		$this->groups = self::$dbh->getCol('SELECT group_id FROM user_group_link WHERE user_id=?', array($this->id));
	}

	/*
	 * Cek password
	 */
	public function auth($uid, $pwd)
	{
		$user = self::$dbh->getRow('SELECT * FROM user WHERE uid=?', array($uid));
		if (!$user['id']){
			$_SESSION['ERR_FORM'] = "User $uid tidak ditemukan";
			return false;
		}

		if (md5($pwd) != $user['pwd']){
			$_SESSION['ERR_FORM'] = 'Maaf, password anda salah';
			return false;
		}

		$_SESSION['uid'] =& $user['id'];
		return true;
	}

	public function authLogin()
	{
		if ($this->auth())
			return '{success: true}';
		else {
			return '{success: false}';
		}
	}

	public function is_login()
	{
		return $this->id;
	}

	public static function root()
	{
		return (1 == self::$user['level']);
	}

	public function name($id)
	{
		return self::$dbh->getOne('SELECT name FROM user WHERE id=?', array($id));
	}

	public static function getName($id)
	{
		return self::$dbh->getOne('SELECT name FROM user WHERE id=?', array($id));
	}

	public static function getId($uid)
	{
		return self::$dbh->getOne('SELECT id FROM user WHERE uid=?', array($uid));
	}

	public static function level($id)
	{
		return self::$dbh->getOne('SELECT level FROM user WHERE id=?', array($id));
	}

	public function login()
	{
		$this->uid = strip_tags($_POST['uid']);
		$this->pwd =& $_POST['pwd'];

		if ($_POST['submit']) {
			if ($this->auth()) {
				unset($_SESSION['ERR_FORM']);
				if (isset($_SESSION['login_uri']))
					return $_SESSION['login_uri'];
				return '/';
			}
		}
		return $this;
	}

	public function &index()
	{
		return $this;
	}
	public function &panel()
	{
		return array();
	}

	public function lists()
	{
		if (isset($_POST['sort']))
			$order = " ORDER BY {$_POST['sort']} {$_POST['dir']}";
		else $order = '';
		$users = array();
		$total = self::$dbh->getOne('SELECT count(*) FROM user');	
		$res =& self::$dbh->query('SELECT id, uid, name, mail, hp, level FROM user' . $order);
		while ($row =& $res->fetchRow()) {
			$row['id'] = (int) $row['id'];
			$row['level'] = (int) $row['level'];
			$users[] = $row;
		}
		return sprintf('{"total": %d, "data": %s}', $total, json_encode($users));
	}

	/*
	 * Select alluser 
	 * 
	 * @return string Json data
	 */
	public function jsonList()
	{
		$obj = new stdClass();
		$obj->total = self::$dbh->getOne('SELECT count(*) FROM user');

		$obj->data = array();
		$res = self::$dbh->query('SELECT * FROM user');
		while ($row = $res->fetchRow()) {
			$obj->data[] = $row;
		}

		return json_encode($obj);
	}

	/* 
	 * Save New User or Edited User data
	 *
	 ( @return json data
	 */
	public function jsonSave()
	{
		$ret = new stdClass;
		$ret->success = true;
		if (isset($_POST['id']) && $_POST['id']) {
			$res = self::$dbh->query('UPDATE user SET pwd=MD5(?), name=?, level=? WHERE id=?', array($_POST['pwd1'], $_POST['name'], $_POST['level'], $_POST['id'])); 
		} else {
			$res = self::$dbh->query('INSERT INTO user (uid, pwd, name, level, since) VALUES (?, MD5(?), ?, ?, NOW())', array($_POST['uid'], $_POST['pwd1'], $_POST['name'], $_POST['level']));
			if (PEAR::isError($res) && stristr($res->getDebugInfo(), 'duplicate')) {
				$ret->success = false;
				$ret->errormsg = "UserID {$_POST['uid']} already exists";
			}
		}

		return json_encode($ret);
	}
	public function save($data)
	{
		$res =& self::$dbh->query('INSERT INTO user (uid, name, pwd, mail, phone, level, since, newsletter) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', array($data->uid, $data->name, md5($data->pwd), $data->mail, $data->phone, $data->level, time(), $data->newsletter));
		if (PEAR::isError($res)) {
			error_log($res->getDebugInfo(), 0);
			return 0;
		} else {
			return self::$dbh->getOne('SELECT id FROM user WHERE uid=?', $data->uid);
		}
	}
	public function update($id, $data) {
		$res =& self::$dbh->query('UPDATE user SET name=?, mail=?, phone=?, level=?, newsletter=? WHERE id=?', array($data->name, $data->mail, $data->phone, $data->level, $data->newsletter, $id));
		if ($data->pwd) {
			$res =& self::$dbh->query('UPDATE user SET pwd=? WHERE id=?', array(md5($data->pwd), $id));
		}
	}

	public static function uidExists($uid)
	{
		return self::$dbh->getOne('SELECT id FROM user WHERE uid=?', array($uid));
	}

	public function saveReg(&$user)
	{
		$res =& self::$dbh->query('INSERT INTO user_tmp (id, uid, name, pwd, clear, address, city, since) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())', array(session_id(), $user['email'], $_POST['name'], md5($_POST['pwd']), str_rot13($_POST['pwd']), $_POST['address'], $_POST['city']));
		if (PEAR::isError($res)) {
			return FALSE;
		} else {
			return self::$dbh->affectedRows();
		}
	}

	public function activate($id)
	{
		$user =& self::$dbh->getRow('SELECT * FROM user_tmp WHERE id=?', array($id));

		if (is_array($user)) {

			if ($user['done']) {
				return array('Account anda sudah diaktifkan, silahkan <a href="/user/login/">login</a>');
			}

			$res =& self::$dbh->query('INSERT INTO user (uid, name, address, city, level, since) VALUES (?, ?, ?, ?, 127, ?)', array($user['uid'], $user['name'], $user['address'], $user['city'], $_SERVER['REQUEST_TIME']));
			if (!PEAR::isError($res) && self::$dbh->affectedRows()) {
				$user_id = self::$dbh->getOne('SELECT id FROM user WHERE uid=?', array($user['uid']));
				self::$dbh->query('INSERT INTO user_pwd (user_id, pwd, clear) VALUES (?, ?, ?)', array($user_id, $user['pwd'], $user['clear']));
				self::$dbh->query('UPDATE user_tmp SET done=1 WHERE id=?', array($id));
				return array('Account anda sudah diaktifkan, silahkan <a href="/user/login/">login</a>');
			} else {
				return array('Internal system error');
			}
		} else {
			return array('Tidak ada account yang akan diaktifkan');
		}
	}

	public static function getUid($id)
	{
		return self::$dbh->getOne('SELECT uid FROM user WHERE id=?', array($id));
	}

	public static function getUserIP()
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ip =& $_SERVER['HTTP_X_FORWARDED_FOR'];
		elseif (isset($_SERVER['HTTP_CLIENT_IP']))
			$ip =& $_SERVER['HTTP_CLIENT_IP'];
		else 
			$ip =& $_SERVER['REMOTE_ADDR'];
		if ($pos = strpos($ip, ','))
			$ip = substr($ip, 0, $pos);
		return $ip;
	}

	public function setRandomPwd($id)
	{
			$pass = $this->randomPwd();
			$res = self::$dbh->query('UPDATE user_pwd SET pwd=?, clear=? WHERE user_id=?', array(md5($pass), str_rot13($pass), $id));	
			if (!PEAR::isError($res) && self::$dbh->affectedRows())
				return $pass;
			return '';
	}

	protected function randomPwd()
	{
		$s = 'abcdefghijkmnopqrstuvwxyz023456789';
    	$i = 8;
		$p = '';

		while ($i--) { 
	      $n = rand() % 36;
			$p = $p . $s[$n];
		}
		$o = '!@#$%^&';
		$p[rand(0,8)] = $o[rand(0,6)];
		return $p;
	}

	public function count()
	{
		return self::$dbh->getOne('SELECT count(*) FROM user');	
	}

	public function getAll($start, $limit, $orderby, $dir)
	{
		$users = self::$dbh->getAll("SELECT * FROM user ORDER BY $orderby $dir LIMIT $start, $limit");
		foreach($users as &$u) {
			$u['pwd'] = '';
		}
		return $users;
	}

	public function getByNewsletter($type=1)
	{
		$users = self::$dbh->getAll('SELECT * FROM user WHERE newsletter=?', array($type));
		return $users;
	}
} 
