<?php

class Article extends Model 
{
	public $id = 0; 
	public $folder_id = 0;
	public $slug;
	public $subject;
	public $kicker;
	public $body;
	public $lead;
	public $deck;
	public $src;
	public $permalink;
	public $publink;
	public $unpublink;

	public $pub_on;
	public $pub_time = 'Belum dipublikasi';
	public $pub_date = 'Belum dipublikasi';
	public $pub_datetime = 'Belum dipublikasi';
	public $unpub = 1;
	protected $pub_by;
	public $pub_user;

	protected $rev_on;
	public $rev_time;
	public $rev_date;
	public $rev_datetime;
	protected $rev_by;
	public $rev_user;

	protected $post_on;
	public $post_time;
	public $post_date;
	public $post_datetime;
	public $post_by;
	public $post_user;
	protected $real_post_by;
	public $real_post_user;

	public $headline = 0;
	protected static $headlineIds = array();

	public $img ='';
	public $imgThumb = array();
	public $fpThumb = array();
	public $comments = array();

	public $hit;
	protected $flag = 0;

	/**
	 * File attachments
	 * @var array
	 */
	public $files = array();

	public function del()
	{
		$key = $this->folder->id . crc32($slug);

		$obj = $field = $rev = array();
		$obj['article'] =& self::$dbh->getRow('SELECT * FROM article WHERE id=?', array($this->id));

		$res =& self::$dbh->query('SELECT * FROM article_field WHERE article_id=?', array($this->id));
		while ($row =& $res->fetchRow()) {
			$field[] =& $row;
			$rev[] =& self::$dbh->getAll('SELECT * FROM article_rev WHERE article_field=?', array($row['id']));
		}

		$obj['article_field'] =& $field; 
		$obj['article_rev'] =& $rev; 

		$res =& self::$dbh->query('INSERT INTO trash (id, tbl, object, del_by) VALUES (?, ?, ?, ?)', array($this->id, 'article', serialize($obj), User::$id));
		if (!PEAR::isError($res) && self::$dbh->affectedRows()) {
			self::$dbh->query('DELETE FROM article WHERE id=?', array($this->id));
			foreach ($this->files as &$fa) {
				foreach ($fa as &$f) {
					$this->delFile($f->id);
				}
			}
			Cache::delete($key);
			Cache::delete($this->id);
			return true;
		}
		return false;
	}

	public function pub()
	{
		$res =& self::$dbh->query('UPDATE article SET pub_on=?, pub_by=? WHERE id=?', array($_SERVER['REQUEST_TIME'], User::$id, $this->id));
		if (!PEAR::isError($res) && self::$dbh->affectedRows()) {
			$this->get($this->id, false);
			$this->insertSearch();
			Cache::set($this->id, $this);
			Cache::regflush($this->folder->path);
			return true;
		}
		return false;	
	}

	public function unpub()
	{
		$res =& self::$dbh->query('UPDATE article SET pub_on=pub_on*-1, pub_by=? WHERE id=?', array(User::$id, $this->id));
		if (!PEAR::isError($res) && self::$dbh->affectedRows()) {
			$this->delSearch();
			Cache::delete($this->id);
			return true;
		}
		return false;
	}

	public function fpLastWithHeadline()
	{
		$limit = (int) $this->params[0];
		return $this->getLastWithHeadline($limit);
	}

	public function last()
	{
		$limit = (int) $this->params[0];
		return $this->getLast(0, $limit);
	}

	public function fplast()
	{
		return $this->last();
	}

	public function lastHeadline()
	{
		$limit = (int) $this->params[0];
		return $this->getLast(0, $limit, true, true);
	}

	public function fpLastHeadline()
	{
		$limit = (int) $this->params[0];
		return $this->getLast(0, $limit, true, true);
	}

	public function tokenHash($token)
	{
		return md5('em' . $token);
	}

	public function countComment($ok=true)
	{
		if ($ok === true) {
			$ok = ' ok=1 ';
		} elseif ($ok === false) {
			$ok = ' ok=0 ';
		} else {
			 $ok = ' ok >= 0';
		}

		return self::$dbh->getOne("SELECT count(*) FROM article_comment WHERE article_id=$this->id AND $ok");
		#return self::$dbh->getOne("SELECT count(*) FROM article_comment WHERE article_id=$this->id AND ok=1");	
	}

	public function getComment($page=0, $ok=true)
	{
		$limit = $this->folder->config['commentLimit'];
		$offset =  $limit * $page;

		if ($ok === true) {
			$ok = ' ok=1 ';
		} elseif ($ok === false) {
			$ok = ' ok=0 ';
		} else {
			 $ok = ' ok >= 0';
		}

		$res = self::$dbh->query("SELECT * FROM article_comment WHERE article_id=? AND $ok LIMIT $offset, $limit", $this->id);
		while($row = $res->fetchRow()){
			$cmt = new Comment($row['comment_id']);
			if (!$cmt->comment) continue;
			$this->comments[] = clone $cmt;
		}

		// display unapproved comments for current active user
		if ($row = self::$dbh->getRow('SELECT * FROM article_comment LEFT JOIN comment ON id=comment_id WHERE article_id=? AND ok=0 AND post_sess_id=?', array($this->id, session_id()))) {
			$cmt = new Comment($row['comment_id']);
			$this->comments[] = clone $cmt;
		}
	}

	public function addComment(&$data)
	{
		$cmt = new Comment();
		if ($cmt_id = $cmt->save($data)) {
			$ok = $this->folder->config['modComment'] ? 1 : 0;
			self::$dbh->query('INSERT INTO article_comment (comment_id, article_id, ok) VALUES (?, ?, ?)', array($cmt_id, $this->id, $ok));
		}
	}

	public function approveComment($cid) {
		self::$dbh->query('UPDATE article_comment SET ok=1 WHERE article_id=? AND comment_id=?', array($this->id, $cid));
		Cache::regflush($this->folder->path);
	}
	public function unapproveComment($cid) {
		$res = self::$dbh->query('UPDATE article_comment SET ok=0 WHERE article_id=? AND comment_id=?', array($this->id, $cid));
		Cache::regflush($this->folder->path);
	}

	public function getRandom()
	{
		$contents =& self::$dbh->getAll('SELECT id FROM article WHERE pub_on > 0 AND folder_id=?', array($this->folder->id));
		shuffle($contents);
		$ret = array_shift($contents);
		unset($contents);
		return $this->get($ret['id']);
	}

	public function getBefore($limit)
	{
		$res =& self::$dbh->query("SELECT id FROM article WHERE folder_id=? AND pub_on > 0 AND pub_on < ? ORDER BY pub_on DESC LIMIT $limit", array($this->folder->id, $this->pub_on));
		$ret = array();
		while ($row =& $res->fetchRow()) {
			$ret[] = clone $this->get($row['id']);
		}
		return $ret;
	}

	public function getBySlug(&$slug)
	{
		if ($dot = strpos($slug, '.')) {
			$slug = substr($slug, 0, $dot);
		}
		$key = $this->folder->id . crc32($slug);
		if (!$id = Cache::get($key)) {
			$id = self::$dbh->getOne('SELECT id FROM article WHERE folder_id=? AND slug=?', array($this->folder->id, $slug));
			Cache::set($key, $id);
		}
		return $this->get($id);
	}

	/**
	 * Return id from known slug
	 *
	 * Strip '.htm'	if any
	 *
	 * @param string slug
	 * @return int id
	 */
	public function getId(&$slug)
	{
		if ($dot = strpos($slug, '.')) {
			$slug = substr($slug, 0, $dot);
		}
		$key = md5($this->folder->id . $slug);
		if (!$id = Cache::get($key)) {
			$id = self::$dbh->getOne('SELECT id FROM article WHERE folder_id=? AND slug=?', array($this->folder->id, $slug));
			Cache::set($key, $id);
		}
		return $id;
	}

	public function getById($id=0)
	{
		if (!$id) return $this;
		return $this->get($id);
	}

	public function get($id=0, $useCache=true)
	{
		if (!$id) $id =& $this->id;
		if ($useCache && $cache = Cache::get($id))
			return $cache;

		global $config;
		static $atc;

		$atc =& self::$dbh->getRow('SELECT * FROM article WHERE id=?', array($id));
		if (!$atc)
			return $this;

		$this->id =& $atc['id'];
		$this->folder_id =& $atc['folder_id'];
		$this->slug =& $atc['slug'];
		$this->hit =& $atc['hit'];
		$this->flag =& $atc['flag'];
		$this->subject = $this->getLastItem(SUBJECT);
		$this->body = $this->getLastItem(BODY);
		
		$this->lead = strip_tags($this->getLastItem(LEAD));
		$this->kicker = $this->getLastItem(KICKER);
		$this->deck = $this->getLastItem(DECK);
		$this->src = $this->getLastItem(SRC);
		$this->tag = $this->getLastItem(TAG);

		$this->headline =& $atc['headline']; 

		$this->post_by =& $atc['post_by'];
		$this->post_user = User::getName($this->post_by);
		if (strtolower($this->post_user) == $this->post_user || strtoupper($this->post_user) == $this->post_user) {
			$this->post_user = ucwords(strtolower($this->post_user));
		}

		$this->real_post_by = $this->getPostBy();
		$this->real_post_user = User::getName($this->real_post_by);

		$this->post_on = $this->getPostOn();
		$this->post_time = strftime($config['time_fmt'], $this->post_on); 
		$this->post_date = strftime($config['date_fmt'], $this->post_on); 
		$this->post_datetime = strftime($config['datetime_fmt'], $this->post_on); 
		$this->pub_on =& $atc['pub_on'];
		if ($this->pub_on > 0) {
			$this->unpub = 0;
			$this->pub_time = strftime($config['time_fmt'], $this->pub_on);
			$this->pub_date = strftime($config['date_fmt'], $this->pub_on);
			$this->pub_datetime = strftime($config['datetime_fmt'], $this->pub_on);
		}
		$this->pub_by =& $atc['pub_by'];
		$this->pub_user = User::getName($this->pub_by);

		$this->rev_on = $this->getRevTm() ;
		$this->rev_time = strftime($config['time_fmt'], $this->rev_on);
		$this->rev_date = strftime($config['date_fmt'], $this->rev_on);
		$this->rev_datetime = strftime($config['datetime_fmt'], $this->rev_on);
		$this->rev_by = $this->getRevBy();
		$this->rev_user = User::getName($this->rev_by);

		$this->permalink = $this->permalink();

		$this->body = preg_replace('|<img[^>]*?src="([^"]*?)"[^>]*?>|i', '<img src="\\1" width="180" align="left" alt="" />', $this->body); 

		if (preg_match('@<img[^>]+src="/([^"]+)".*>@i', $this->body, $match)) {
			$this->img = $match[1];
			$this->imgThumb = Image::createFixThumbnail($this->img, $this->folder->config['imgThumbWidth'], $this->folder->config['imgThumbHeight']);
			$this->fpThumb = Image::createFixThumbnail($this->img, $this->folder->config['fpThumbWidth'], $this->folder->config['fpThumbHeight']);
		} else {
			$this->img = '';
			$this->imgThumb = array();
			$this->fpThumb = array();
		};

		$this->getFile();

		if ($this->pub_on > 0) {
			Cache::set($id, $this);
		}
		return $this;
	}

	protected function getPostBy()
	{
		$article_field = $this->getArticleField(SUBJECT);

		return self::$dbh->getOne('SELECT rev_by FROM article_rev WHERE article_field=? ORDER BY rev_on ASC', array($article_field));
	}

	protected function getPostOn()
	{
		$article_field = $this->getArticleField(SUBJECT);

		return self::$dbh->getOne('SELECT rev_on FROM article_rev WHERE article_field=? ORDER BY rev_on ASC', array($article_field));
	}

	protected function getRevBy()
	{
		return self::$dbh->getOne('SELECT rev_by FROM article_rev AS rev, article_field AS field WHERE rev.article_field=field.id AND article_id=? ORDER BY rev_on DESC', array($this->id));
	}

	protected function getRevTm()
	{
		return self::$dbh->getOne('SELECT rev_on FROM article_rev AS rev, article_field AS field WHERE rev.article_field=field.id AND article_id=? ORDER BY rev_on DESC', array($this->id));
	}

	protected function getLastItem($field)
	{
		$article_field =& $this->getArticleField($field);

		return self::$dbh->getOne('SELECT content FROM article_rev WHERE article_field=? ORDER BY rev_on DESC', array($article_field));
	}

	public function update(&$data)
	{
		if (!$data['subject']) {
			$_SESSION['ERR_FORM'] = 'Judul harus diisi';
			return false;
		}

		$headline = isset($data['headline']) ? 1 : 0;
		if ($this->headline != $headline) {
			$res = self::$dbh->query('UPDATE article SET headline=? WHERE id=?', array($headline, $this->id));
		}

		$subject = htmlspecialchars($data['subject'], ENT_QUOTES);
		if (strtolower($subject) == $data['subject'] || strtoupper($subject) == $data['subject'] || ucfirst(strtolower($subject)) == $data['subject']) {
			$subject = ucwords(strtolower($subject));
		}
		if (!self::same_txt($this->subject, $subject)) 
			$this->insertItem(SUBJECT, $subject);
		if (isset($data['body']) && !self::same_txt($this->body, $data['body']))
			$this->insertItem(BODY, $data['body']);
		if (isset($data['lead']) && !self::same_txt($this->lead, strip_tags($data['lead'])))
 			$this->insertItem(LEAD, strip_tags($data['lead']));
		if (isset($data['kicker']) && !self::same_txt($this->kicker, $data['kicker']))
			$this->insertItem(KICKER, $data['kicker']);
		if (isset($data['deck']) && !self::same_txt($this->deck, $data['deck']))
			$this->insertItem(DECK, $data['deck']);
		if (isset($data['src']) && !self::same_txt($this->src, $data['src']))
			$this->insertItem(SRC, $data['src']);
		if (isset($data['tag']) && !self::same_txt($this->tag, $data['tag']))
			$this->insertItem(TAG, $data['tag']);

		if ($this->pub_on > 0) {
			$this->get($this->id, !USE_CACHE);
			$this->updateSearch();
			Cache::regflush($this->folder->path);
		}
		return true;
	}

	public function insert(&$data, $userId=0, $slug='')
	{
		if (!$data['subject']) {
			$_SESSION['ERR_FORM'] = 'Judul harus diisi';
			return $this;
		}

		if ($slug) {
			$slug = $this->toSlug($slug);
		} else {
			$slug = $this->toSlug($data['subject']);
		}

		if ($this->slugExists($slug)) {
			$_SESSION['ERR_FORM'] = 'Judul yang sama sudah ada';
			return $this;
		}

		$this->id =& $_SERVER['REQUEST_TIME'];

		if (!$userId) $userId = User::$id;
		
		$headline = isset($data['headline']) ? 1 : 0;
		$res =& self::$dbh->query('INSERT INTO article (id, folder_id, slug, headline, post_by) VALUES (?, ?, ?, ?, ?)', array($this->id, $this->folder->id, $slug, $headline, $userId));
		if (PEAR::isError($res) || !self::$dbh->affectedRows()) {
			$_SESSION['ERR_FORM'] = 'Internal System Error';
			return $this;
		}

		$subject = htmlspecialchars($data['subject'], ENT_QUOTES);
		if (strtolower($subject) == $data['subject'] || strtoupper($subject) == $data['subject'] || ucfirst(strtolower($subject)) == $data['subject']) {
			$subject = ucwords(strtolower($subject));
		}
		$this->insertItem(SUBJECT, $subject, $userId);
		if (isset($data['body']) && $data['body'])
			$this->insertItem(BODY, $data['body'], $userId);
		if (isset($data['lead']) && strip_tags($data['lead']))
			$this->insertItem(LEAD, strip_tags($data['lead']), $userId);
		if (isset($data['kicker']) && $data['kicker'])
			$this->insertItem(KICKER, $data['kicker'], $userId);
		if (isset($data['deck']) && $data['deck'])
			$this->insertItem(DECK, $data['deck'], $userId);
		if (isset($data['src']) && $data['src'])
			$this->insertItem(SRC, $data['src'], $userId);
		if (isset($data['tag']) && $data['tag'])
			$this->insertItem(TAG, $data['tag'], $userId);

		return $this->get();
	}

	protected function insertItem($field, &$content, $userId=0)
	{
		if (!$content) return true;
		if (preg_match('/msonormal|style=/i', $content)) {
			$content = strip_tags($content, '<p><b><i><em><strong><img><ol><ul><li><h3>');
			$content = preg_replace('/ style="[^"]+"/i', '', $content);
		}
		if (get_magic_quotes_gpc()) {
			$content = stripslashes($content);
		}

		$article_field =& $this->getArticleField($field);

		if (!$userId) $userId = User::$id;

		if ((User::$user['uid'] == 'ishobr') &&  $this->rev_by)
			$userId = $this->rev_by;

		if ($article_field) {
			$res =& self::$dbh->query('INSERT INTO article_rev (article_field, content, rev_by, rev_on) VALUES (?, ?, ?, ?)', array($article_field, $content, $userId, $_SERVER['REQUEST_TIME']));
		}
	}

	protected function getArticleField($field)
	{
		$i = 0;
		while (!$ret =& self::$dbh->getOne('SELECT id FROM article_field WHERE article_id=? AND field=?', array($this->id, $field))) {
			if (++$i > 5) break;
			self::$dbh->query('INSERT INTO article_field (article_id, field) VALUES (?, ?)', array($this->id, $field));
		}
		return $ret;
	}

	public function editable()
	{
		if (!isset($this->id)) return false;
		if (!User::$id) return false;
		if (User::root()) return true;
		if ($this->rev_by == User::$id) return true;
		if (!$this->folder->isEditor(User::$id))
			return false;
		if (User::level($this->rev_by) == User::$user['level'])
			return true;
		return User::$user['level'] < User::level($this->rev_by);
	}

	protected function permalink()
	{
		return $this->folder->path . $this->slug . '.htm';
	}

	public function getTotal($pub=true)
	{
		$pub_on = $pub ? 'pub_on > 0' : 'pub_on = 0';
		return self::$dbh->getOne("SELECT COUNT(*) FROM article WHERE folder_id=? AND $pub_on", array($this->folder->id));
	}

	public function getLast($offset, $limit, $pub=true, $headline=false, $flag=0)
	{
		$ret = array();
		$pub_on = $pub ? 'pub_on > 0' : 'pub_on <= 0';
		$order_by = $pub ? 'pub_on' : 'id';
		$is_headline = $headline ? ' AND headline=1' : '';
		$flag = $flag ? " AND flag=$flag " : '';

		$ret['total'] = self::$dbh->getOne("SELECT count(*) FROM article WHERE folder_id=? AND $pub_on $is_headline $flag", array($this->folder->id));

		$res =& self::$dbh->query("SELECT id FROM article WHERE folder_id=? AND $pub_on $is_headline $flag ORDER BY $order_by DESC LIMIT $offset,$limit", array($this->folder->id));
		$n = 0;
		$last = array();
		while ($row =& $res->fetchRow()) {
			if ($obj = Cache::get($row['id'])) {
				$pubOn = $obj->pub_on;
				$postOn = $obj->id; 
				if ($pubOn)
					$last[$pubOn] = $obj;
				else
					$last[$postOn] = $obj;
			} else {
				$this->get($row['id']);
				$pubOn = $this->pub_on;
				$postOn = $this->id;
				if ($pubOn) {
					$last[$pubOn] = clone $this;
				} else {
					$last[$postOn] = clone $this;
				}
			}
		}
		$ret['data'] = $last;
		return $ret;
	}

	protected function getLastWithHeadline($limit)
	{
		$ret = array();
		self::$headlineIds[0] =& self::$dbh->getOne('SELECT id FROM article WHERE folder_id=? AND pub_on > 0 AND headline=1 ORDER BY pub_on DESC LIMIT 1', array($this->folder->id));
		$this->get(self::$headlineIds[0]);
		$ret[] = clone $this;

		$res =& self::$dbh->query('SELECT id FROM article WHERE folder_id=? AND pub_on > 0 ORDER BY pub_on DESC LIMIT !', array($this->folder->id, $limit));
		while ($row =& $res->fetchRow()) {
			if (in_array($row['id'], self::$headlineIds)) continue;
			$this->get($row['id']);
			$ret[] = clone $this;
		}
		unset($this->id);
		return $ret;
	}

	public function indexSearch()
	{
		$res = self::$dbh->query('SELECT id FROM article WHERE pub_on > 0 AND folder_id=?', $this->folder->id);
		while ($row = $res->fetchRow()) {
			if (self::$dbh->getOne('SELECT id FROM article_search WHERE article_id=?', array($row['id']))) continue;
			$this->get($row['id']);
			$this->insertSearch();
			printf('%s<br/>', $row['id']);
		}
		echo '<p>Completed</p>';
	}

	/* Remove non alpha numeric from string */
	protected function strCompact(&$str) 
	{
		return preg_replace(array('@&[^;]+;@', '@[^\w]+@'), array('', ' '), strip_tags($str));
	}

	protected function insertSearch()
	{
		$res =& self::$dbh->query('INSERT DELAYED INTO article_search (article_id, permalink, subject, kicker, body, rev_on) VALUES (?, ?, ?, ?, ?, ?)', array($this->id, $this->permalink, $this->strCompact($this->subject), $this->strCompact($this->kicker), $this->strCompact($this->body), $this->rev_on));
	}

	protected function updateSearch()
	{
		$res =& self::$dbh->query('UPDATE LOW_PRIORITY article_search SET permalink=?, subject=?, kicker=?, body=?, rev_on=? WHERE article_id=?', array($this->permalink, $this->strCompact($this->subject), $this->strCompact($this->kicker), $this->strCompact($this->body), $this->rev_on, $this->id));
		if (!PEAR::isError($res) && !self::$dbh->affectedRows())
			$this->insertSearch();
	}

	protected function delSearch()
	{
		$res =& self::$dbh->query('DELETE FROM article_search WHERE article_id=?', array($this->id));
	}

	public static function lastUpdate()
	{
		return self::longTime(self::$dbh->getOne('SELECT rev_on FROM article_rev ORDER BY rev_on DESC LIMIT 1'));
	}

	public function updateHit()
	{
		$doy = strftime('%j');
		if (!self::$dbh->getOne('SELECT article_id FROM article_hits WHERE article_id=? AND dayofyear=?', array($this->id, $doy))) {
			self::$dbh->query('INSERT INTO article_hits (article_id, dayofyear, hits) VALUES (?, ?, 1)', array($this->id, $doy));
			return;
		}
		self::$dbh->query('UPDATE article_hits SET hits=hits+1 WHERE article_id=? AND dayofyear=?', array($this->id, $doy));
	}

	public function getHit()
	{
		return self::$dbh->getOne('SELECT sum(hits) FROM  article_hits WHERE article_id=?', array($this->id));
	}

	/**
	 * Convert string to URL slug
	 *
	 * Convert a string (usually subject) to URL slug
	 *
	 * @param	String $str
	 * @return String slug
	 */
	protected function toSlug(&$str)
	{
		$slug = preg_replace('/[^a-zA-Z0-9-]/', '-', strtolower($str));
		return preg_replace('/-{2,}/', '-', trim($slug, '-'));
	}

	/**
	 * Set article flag
	 *
	 * flag can use to inhirated class for custom use
	 *
	 * @param int $flag
	 * @return boolean
	 */
	protected function setFlag($flag = 0)
	{
		if (!$flag || !$this->id) {
			return false;
		}
		self::$dbh->query('UPDATE article SET flag=? WHERE id=?', array($flag, $this->id));
		if (!PEAR::isError($res) && self::$dbh->affectedRows()) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getPostUid()
	{
		return User::getUid($this->post_by);
	}

	protected function slugExists($slug)
	{
		return self::$dbh->getOne('SELECT id FROM article WHERE slug=?', array($slug));
	}

	public function mailLog(&$link, &$data)
	{
			self::$dbh->query('INSERT INTO mail_log (url, mailfrom, name, mailto, msg, sess_id, log_on) VALUES (?, ?, ?, ?, ?, ?, ?)', array($link, $data['mailfrom'], $data['from'], $data['mailto'], $data['msg'], session_id(), $_SERVER['REQUEST_TIME']));
	}

	public function saveFile($key, &$filedata, &$postdata)
	{
		
		$subject = isset($postdata[$key . 'subject']) ? $postdata[$key . 'subject'] : '';
		$note = isset($postdata[$key . 'note']) ? $postdata[$key . 'note'] : '';
		$file = new Files();
		if ($file_id = $file->save($filedata, $this->folder, $subject, $note, $key)) {
			self::$dbh->query('INSERT INTO article_file (file_id, article_id) VALUES (?, ?)', array($file_id, $this->id));
				return true;
		} else return false;
	}

	protected function getFile()
	{
		$this->files = array();

		$res = self::$dbh->query("SELECT file.* FROM article_file LEFT JOIN file ON file.id=file_id WHERE article_id=$this->id ORDER BY id");
		while ($row = $res->fetchRow())	{
			$key =& $row['key'];
			if (!array_key_exists($key, $this->files)) {
				$this->files[$key] = array();
			}
			$this->files[$key][] = new Files($row['id']);
		}
	}

	public function delFile($id)
	{
		$file = new Files($id);
		$res = self::$dbh->query("DELETE FROM file WHERE id=$id");
		if (!PEAR::isError($res)) {
			unlink($file->name);
		}
	}

}
