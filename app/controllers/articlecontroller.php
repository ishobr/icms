<?php

class ArticleController extends Action 
{
	public static $config = array(
		'indexLimit' => 7,
		'arcLimit' => 15,
		'default' => '',
		'index' => '',
		'imgWidth' => 480,
		'imgHeight' => 0,
		'imgThumbWidth' => 168,
		'imgThumbHeight' => 102,
		'fpThumbWidth' => 250,
		'fpThumbHeight' => 0,
		'enableComment' => 1,
		'modComment' => 1,
		'commentLimit' => 33,
		'publicPost' => 0,
		'anonPost' => 0
	);

	protected $_model = 'Article';
	
	public function init()
	{
		$this->folder->config = array_merge(self::$config, $this->folder->config);
		$this->model = new $this->_model($this->folder);
	}

	public function index()
	{
		if($this->folder->config['index'])
			return $this->{$this->folder->config['index']}();

		if ($this->folder->config['default']) {
			$this->action = 'view';
			$this->params[0] = $this->folder->config['default'];
			return $this->view();
		}

		View::$title = 'Index';
		$ret = $this->model->getLast(0, $this->folder->config['indexLimit']);
		if (!$ret) {
			$_SESSION['error_uri'] = $_SERVER['REQUEST_URI'];
			$_SESSION['error_msg'] = 'Belum ada <i>content</i> dalam kategori ini: ' . $this->folder->name;
			if ($this->folder->isContributor(User::$id))
				$_SESSION['error_msg'] .= sprintf('<br/><a href="/%sadd">Tambahkan %s</a>', $this->folder->path, $this->folder->name);
			return '/error/noitem';
		}
		return $ret;
	}

	public function draft()
	{
		if (!$this->folder->isEditor(User::$id))
			return $this->folder->path;
		
		if (!isset($this->params[0])) $this->params[0] = 0;
		$page = (int) $this->params[0];

		$ret = array();
		$pub = false;
		$offset = $this->folder->config['arcLimit'] * $page;	
		$ret = $this->model->getLast($offset, $this->folder->config['arcLimit'], false);
		return $ret;
	}


	public function add()
	{
		if (!$this->folder->isContributor(User::$id)) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			return '/error/denied/';
		}

		if (isset($_POST['submit'])) {
			$article = $this->model->insert($_POST, 0, $this->params[0]);
			if ($article->id) {
				if (isset($_FILES))	{
					foreach($_FILES as $k => $v) {
						$article->saveFile($k, $_FILES[$k], $_POST);
					}
				}
				return $article->permalink;
			}
		}
		return $this->model;
	}

	public function edit()
	{
		$article = $this->model->get($this->params[0], false);

		if (!$article->editable()) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			return '/error/denied/';
		}

		View::$title = $article->subject . ' (Edit)';
		if (isset($_POST['submit']) && $article->update($_POST)) {
			if (isset($_FILES))	{
				foreach($_FILES as $k => $v) {
					$article->saveFile($k, $_FILES[$k], $_POST);
				}
			}
			return $article->permalink;
		}
		
		return $article;
	}

	public function del()
	{
		if (!$this->folder->isApprover(User::$id)) {
			return '/error/denied/';
		}

		$data = $this->model->get($this->params[0]);

		$data->del();

		return $this->folder->path;
	}

	public function pub()
	{
		if (!$this->folder->isApprover(User::$id)) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			return '/error/denied/';
		}

		$data = $this->model->get($this->params[0]);
		if ($data->pub_on > 0) return $data->permalink;

		$data->pub(); 
		$this->sendNewsletter($data);
		return $data->permalink;
	}

	public function sendNewsletter(&$data)
	{
		$body = "
		Berita/Artikel terbaru dari Al-Kausar Boarding School:\n\n
		$data->subject\n
		$data->lead\n\n
		Selengkapnya: http://alkausar.org/$data->permalink
		";
		$headers = array();
		$headers['From'] = 'info@alkausar.org';
		$headers['Subject'] = 'Alkausar - ' . $data->subject;
		$mail =& Mail::factory('sendmail', array('sendmail_path' => '/usr/sbin/sendmail'));
		$user = new User($this->folder);
		$users = $user->getByNewsletter();
		foreach ($users as &$u) {
			if (!$u['mail']) continue;
			$headers['To'] =&  $u->mail;
			$mail->send($u['mail'], $headers, $body);
		}
	}

	public function unpub()
	{
		if (!$this->folder->isApprover(User::$id)) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			return '/error/denied/';
		}

		$data = $this->model->get($this->params[0]);
		if ($data->pub_on <= 0) return $data->permalink;
		
		$data->unpub();
		return $data->permalink;
	}

	public function rawLead()
	{
		$data = $this->model->getBySlug($this->params[0]);
		return $data->lead;
	}

	public function dataByUri()
	{
		$data = $this->model->getBySlug($this->params[0]);
		return $data;
	}

	public function view()
	{
		$data = $this->model->getBySlug($this->params[0]);

		if (!$data->id) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			return '/error/notfound/';
		}

		View::$title =& $data->subject;

		if(!$data->pub_on) {
			Ctrl::$setCache = 0;
			if (!$data->editable()) {
				$_SESSION['error_uri'] =& $this->params[0];
				return '/error/denied/';
			}
		} else {
			$data->updateHit();
		}
		return $data;
	}

	public function fpLastWithHeadline()
	{
		$limit = (int) $this->params[0];
		return $this->getLastWithHeadline($limit);
	}

	public function last()
	{
		$limit = (int) $this->params[0];
		return $this->model->getLast(0, $limit);
	}

	public function fplast()
	{
		return $this->last();
	}

	public function lastHeadline()
	{
		$limit = (int) $this->params[0];
		return $this->model->getLast(0, $limit, true, true);
	}

	public function fpLastHeadline()
	{
		$limit = (int) $this->params[0];
		return $this->model->getLast(0, $limit, true, true);
	}

	public function post()
	{
		if ($this->folder->config['anonPost']) {

			if (!$anonId = User::uidExists('anonymous'))
				return $this->folder->path;
				
			if (isset($_POST['submit'])) {
				$data = $this->model->insert($_POST, $anonId);
				if ($data->id)
					return $data->permalink;
			}
			return $this->model;

		} elseif ($this->folder->config['publicPost']) {

			if (!User::$id) {
				$_SESSION['error_uri'] = $_SERVER['REQUEST_URI'];
				$_SESSION['error_msg'] = 'Silahkan <a href="/user/login/">Login</a> sebelum mengirim ' . $this->folder->name;
				return '/error/reqlogin/';
			}
				
			if (isset($_POST['submit'])) {
				$data = $this->model->insert($_POST);
				if ($data->id)
					return $data->permalink;
			}
			return $this->model;
		} else {
			return $this->folder->path;
		}
	}

	public function comment()
	{
		Ctrl::$setCache = 0;
		
		if (!isset($this->params[1])) $this->params[1] = 0;
		$page = $this->params[1];

		$ret = array();
		$article = $this->model->getBySlug($this->params[0]);
		View::$title = $article->subject . ' (Komentar)';

		if (isset($_POST['submit']) && $this->folder->config['enableComment']) {
			if (isset($_POST['token']) && isset($_COOKIE['token']) && $_COOKIE['token'] == $this->model->tokenHash($_POST['token'])) {
				$article->addComment($_POST);
			}
			return $_SERVER['REQUEST_URI'];
		} 

		$article->getComment($page);
		$ret['total'] = $article->countComment();
		$ret['data'] =& $article;
		return $ret;
	}

	public function token()
	{
		$token = time();
		$expire = $token + (60*30); 
		setcookie('token', $this->model->tokenHash($token), $expire, '/');
	 	return $token;
	}

	public function random()
	{
		return $this->model->getRandom();
	}

	public function more()
	{
		$offset = (int) $this->params[0]; // start from zero
		$limit = (int) $this->params[1];
		return $this->model->getLast($offset, $limit);
	}

	public function fpMore()
	{
		return $this->more();
	}

	public function before()
	{
		$id = (int) $this->params[0]; // $id
		$limit = (int) $this->params[1];

		$article = $this->model->get($id);

		return $article->getBefore($limit);
	}
	
	public function panel(&$data)
	{
		$ret = array();
		if (is_array($data)) {
			if (!count($data)) return $ret;
			$obj = reset($data);
		} else $obj =& $data;

		if (!isset($obj->folder)) return $ret;
		if ($obj->folder->isContributor(User::$id)) {
			// Draft
			$ret[] = array('href' => $obj->folder->path . 'draft', 'title' => 'Draft', 'class' => 'draft', 'onclick' => '');
			// Add
			$ret[] = array('href' => $obj->folder->path . 'add', 'title' => 'Add Page', 'class' => 'add', 'onclick' => '');
		}
		// Edit
		if (is_object($data) && $data->editable()) {
			$ret[] = array('href' => $data->folder->path . 'edit/' . $data->id, 'title' => 'Edit', 'class' => 'edit', 'onclick' => '');
			// Publish
			if ($data->pub_on <= 0 && $data->folder->isApprover(User::$id)) {
				$ret[] = array('href' => $data->folder->path . 'pub/' . $data->id, 'title' => 'Publish', 'class' => 'pub', 'onclick' => '');
				$ret[] = array('href' => $data->folder->path . 'del/' . $data->id, 'title' => 'Delete', 'class' => 'del', 'onclick' => 'Are you sure to delete data page?');
			}
			if ($data->pub_on > 0 && $data->folder->isApprover(User::$id)) {
				$ret[] = array('href' => $data->folder->path . 'unpub/' . $data->id, 'title' => 'Unpublish', 'class' => 'unpub', 'onclick' => 'Are you sure to unpublish data page?');
				if ($data->folder->config['modComment'])
					$ret[] = array('href' => $data->folder->path . 'commentlist/' . $data->id, 'title' => 'Comments', 'class' => 'comment', 'onclick' => '');
			}
		}
		return $ret;
	}

	public function arc()
	{
		View::$title = 'Arsip';
		if (!isset($this->params[0])) $this->params[0] = 0;
		$page = (int) $this->params[0];

		$ret = array();
		$offset = $this->folder->config['arcLimit'] * $page;	
		return $this->model->getLast($offset, $this->folder->config['arcLimit']);
	}

	public function rss()
	{
		setlocale(LC_ALL, 'en_US');
		return $this->model->getLast(0, $this->folder->config['indexLimit']);
	}

	public function send()
	{
		Ctrl::$setCache = 0;

		$article = $this->model->getBySlug($this->params[0]);
		View::$title = $article->subject . ' (Mail)';

		if (isset($_POST['submit'])) {

			if (!strstr($_POST['mailto'], '@') && !strstr($_POST['mailfrom'], '@') && !$_POST['from']) {
				$_SESSION['ERR_FORM'] = 'Nama dan semua alamat email harus diisi';
				return $article;
			}

			global $config;

			$mailfrom = $config['mail'];
			$headers['From'] = $_POST['mailfrom'];
			$headers['To'] = $_POST['mailto'];
			$headers['Subject'] = $config['site'] . ' - ' . $article->subject;
			$body = sprintf($config['mail']['send_body'], $_POST['from'], $_POST['mailfrom'], $_POST['msg'], $article->subject, $article->lead, $article->permalink);

			require_once 'Mail.php';
			$mail =& Mail::factory('sendmail', array('sendmail_path' => '/usr/sbin/sendmail'));
			$mail->send($_POST['mailto'], $headers, $body);
			$_SESSION['ERR_FORM'] = sprintf('Rekomendasi anda sudah dikirim ke %s. Anda dapat mengirim ke rekan yang lain.', $_POST['mailto']);
			$this->model->mailLog($article->permalink, $_POST);
		}
		return $article;
	}

	public function commentList()
	{
		if (!User::root()) {
			return '/error/denied/';
		}

		$ret = array();

		$article = $this->model->get($this->params[0]);

		if ($_POST['approve']) {
			foreach($_POST['checked'] as $cid) {
				$article->approveComment($cid);
			}
		}

		if ($_POST['unapprove']) {
			foreach($_POST['checked'] as $cid) {
				$article->unapproveComment($cid);
			}
		}

		$page = 0;
		if (isset($this->params[1]))
			$page = (int) $this->params[1];

		$article->getComment($page, false);
		$ret['total'] = $article->countComment(false);
		$ret['data'] =& $article;
		return $ret;
	}

	public function delFile()
	{
		$article = $this->model->get($this->params[0], false);

		if (!$article->editable()) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			return '/error/denied/';
		}

		$file_id =& $this->params[1];
		if ($file_id)
			$this->model->delFile($file_id);
		return $_SERVER['HTTP_REFERER'];
	}

	public function getFile()
	{
		$file = new Files($this->params[0]);
		ob_clean();
		header('Content-Disposition: attachment; filename=' . basename($file->name) . '; size=' . $file->size);
		header('Content-Type: ' . $file->type);
		readfile($file->name);
		die();
	}

}
