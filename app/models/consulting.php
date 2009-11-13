<?
class Consulting extends Article
{
	public $post_user_alias;	

	public function insert(&$data)
	{
		parent::insert($data);

		if ($this->id) {
	
			if (isset($data['question']) && $data['question'])
				$this->insertItem(QUESTION, $data['question']);
			if (isset($data['alias']) && $data['alias'])
				$this->insertItem(ALIAS, $data['alias']);
			return $this->get();
		}
		return $this;
	}

	public function update(&$data)
	{
		if (parent::update($data)) {
			if (!self::same_txt($this->question, $data['question']))
				$this->insertItem(QUESTION, $data['question']);
			if (!self::same_txt($this->alias, $data['alias']))
				$this->insertItem(ALIAS, $data['alias']);
			if (!self::same_txt($this->answer, $data['answer'])) {
				$this->insertItem(ANSWER, $data['answer']);
				$this->setFlag(FLAG_HAVE_ANSWER);
			}

			if ($this->pub_on > 0) {
				$this->get($this->id, false);
				$this->updateSearch();
				Cache::set($this->id, $this);
				Cache::regflush($this->folder->path);
			}
			return true;
		}
		return false;
	}

	public function get($id=0, $useCache=true)
	{
		if ($useCache && $cache = Cache::get($id))
			return $cache;

		if (!$id) $id =& $this->id;
		parent::get($id, false);
		$this->question = $this->getLastItem(QUESTION);
		$this->answer = $this->getLastItem(ANSWER);
		$this->alias = $this->getLastItem(ALIAS);

		if ($this->pub_on > 0)
			Cache::set($id, $this);
		return $this;
	}


	protected function insertSearch()
	{
		$body = $this->question . ' ' . $this->answer;
		$res =& self::$dbh->query('INSERT INTO article_search (article_id, permalink, subject, kicker, body, rev_on) VALUES (?, ?, ?, ?, ?, ?)', array($this->id, $this->permalink, $this->strCompact($this->subject), $this->strCompact($this->kicker), $this->strCompact($body), $this->rev_on));
	}

	protected function updateSearch()
	{
		$body = $this->question . ' ' . $this->answer;
		$res =& self::$dbh->query('UPDATE article_search SET permalink=?, subject=?, kicker=?, body=?, rev_on=? WHERE article_id=?', array($this->permalink, $this->strCompact($this->subject), $this->strCompact($this->kicker), $this->strCompact($body), $this->rev_on, $this->id));
		if (!$res)
			$this->insertSearch();
	}
}
