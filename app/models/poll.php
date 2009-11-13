<?
class Poll extends Article
{
	public $options = array();
	public $results = array();
	public $total = 0;
	public $color = array('red', 'green', 'blue', 'yellow', 'orange');

	public function get($id=0, $useCache=true)
	{
		$_SESSION['vote'] =& $_SERVER['REQUEST_TIME'];
		if (!$id) $id =& $this->id;
		if ($useCache && $cache = Cache::get($id))
			return $cache;

		parent::get($id, false);

		$res =& self::$dbh->query('SELECT id, name FROM poll_opt WHERE article_id=? ORDER BY id', array($this->id));
		while ($row =& $res->fetchRow()) {
			$k = $row['id'];
			$this->options[$k] = $row['name'];
		}
		if ($this->pub_on > 0) {
			Cache::set($id, $this);
		}
		return $this;
	}

	public function insert(&$data, $userId=0, $slug='')
	{
		$poll = parent::insert($data);
		if ($poll->id) {
			foreach($data['opt'] as $opt)
				if ($opt)
					self::$dbh->query('INSERT INTO poll_opt (article_id, name) VALUES (?, ?)', array($poll->id, $opt));
		}
		return $poll->get();
	}

	public function update(&$data)
	{
		if (parent::update($data)) {
			foreach($this->options as $k => &$v) {
				$i = 'opt' . $k;
				if (isset($data[$i])) {
					if ($data[$i] && $data[$i] != $v)
						$this->updateOption($k, $data[$i]);
				} else
					$this->delOption($k);
			}
			foreach($data['opt'] as &$opt)
				if ($opt)
					$this->insertOption($opt);
			return true;
		}
		return false;
	}

	public function saveVote(&$data)
	{
			self::$dbh->query('INSERT INTO poll_vote (opt_id, ip, user_agent, poll_id, sess_id, vote_on) VALUES (?, INET_ATON(?), ?, ?, ?, ?)', array($data['opt'], User::getUserIP(), $_SERVER['HTTP_USER_AGENT'], $data['id'], session_id(), $_SERVER['REQUEST_TIME']));

			$cookie = 'poll' . $data['id'];
			setcookie($cookie, $_SERVER['REQUEST_TIME'], $expire, '/');
			unset($_SESSION['vote']);
	}

	public function getCount($opt_id)
	{
		return (int) self::$dbh->getOne('SELECT COUNT(*) FROM poll_vote WHERE opt_id=? GROUP BY opt_id', array($opt_id));
	}

	private function insertOption(&$opt)
	{
		self::$dbh->query('INSERT INTO poll_opt (article_id, name) VALUES (?, ?)', array($this->id, $opt));
	}
	private function updateOption($id, &$opt)
	{
		self::$dbh->query('UPDATE poll_opt SET name=? WHERE id=?', array($opt, $id));
	}

	private function delOption($id)
	{
		self::$dbh->query('DELETE FROM poll_opt WHERE id=?', $id);
	}
}
