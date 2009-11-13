<?php // vim: ts=4

class UserController extends Action
{
	protected $_model = 'User';

	public function init()
	{
		Ctrl::$setCache = 0;
		$this->model = new $this->_model($this->folder);
	}

	public function authLogin()
	{
		if ($this->model->auth($_POST['uid'], $_POST['pwd']))
			return '{success: true}';
		else {
			return '{success: false}';
		}
	}

	public function is_login()
	{
		return User::$id;
	}

	public function logout()
	{
		$_SESSION = array();
		setcookie(session_name(),'',time()-42000,'/');
		session_destroy();
		return '/user/login';
	}

	public function login()
	{
		View::$title = 'Login';	

		if (isset($_POST['submit'])) {
			if ($this->model->auth($_POST['uid'], $_POST['pwd'])) {
				if (isset($_SESSION['REFERER'])) {
					$ref = $_SESSION['REFERER'];
					$_SESSION['REFERER'] = NULL;
					return $ref;
				}
				return '/';
			}
		} else {
			if (!isset($_SESSION['REFERER']) && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'user') === FALSE)
				$_SESSION['REFERER'] =& $_SERVER['HTTP_REFERER'];
		}
		return array();
	}

	public function forgetPwd()
	{

		View::$title = 'Lupa Password';

		if (isset($_POST['submit'])) {

			if (strpos($_POST['email'], '@') === FALSE) {
				$_SESSION['ERR_FORM'] = 'Alamat email tidak valid';
				return array();
			}

			if (!$user_id = User::uidExists($_POST['email'])) {
				$_SESSION['ERR_FORM'] = sprintf('Alamat email anda belum terdaftar, silahkan <a href="/user/reg/?mail=%s">registrasi</a>', urlencode($_POST['email']));
				return array();
			}

			if ($pass = $this->model->setRandomPwd($user_id)) {
				global $config;

				$mailfrom = sprintf('%s <%s>', $config['site'], $config['mail']);
				$headers['From'] =& $mailfrom;
				$headers['To'] = $_POST['email'];
				$headers['Subject'] =& $config['mail']['forget_subject'];
				$body = sprintf($config['mail']['forget_body'], $pass);
				require_once 'Mail.php';
				$mail =& Mail::factory('sendmail', array('sendmail_path' => '/usr/sbin/sendmail'));
				$mail->send($_POST['email'], $headers, $body);
				return '/user/forgetsuccess/';
			} else {
				$_SESSION['ERR_FORM'] = 'Internal system error';
			}
		}
		return array();
	}

	public function forgetSuccess()
	{
		View::$title = 'Lupa Password Success';
		return array();
	}

	public function index()
	{
		return '/';
	}

	public function panel()
	{
		return array();
	}

	/*
	 * Select alluser 
	 * 
	 * @return string Json data
	 */
	public function jsonList()
	{
	}

	/* 
	 * Save New User or Edited User data
	 *
	 ( @return json data
	 */
	public function jsonSave()
	{
	}

	public function save()
	{
		if (!$this->model->save($user)) {
			error_log($res->getDebugInfo(), 0);
			return '{success: false}';
		} else
			return '{success: true}';
	}

	public function reg()
	{
		global $config;

		View::$title = 'Registrasi';

		if (isset($_POST['submit'])) {

			if (strpos($_POST['email'], '@') === FALSE) {
				$_SESSION['ERR_FORM'] = 'Alamat email tidak valid';
				return $this;
			}

			if (User::uidExists($_POST['email'])) {
				$_SESSION['ERR_FORM'] = 'Alamat email anda sudah terdaftar, silahkan langsung <a href="/user/login/">login</a>';
				return $this->model;
			}
			if ($this->model->saveReg($_POST)) {
				$mailfrom = sprintf('%s <%s>', $config['site'], $config['mail']);
				$headers['From'] =& $mailfrom;
				$headers['To'] = $_POST['email'];
				$headers['Subject'] = $config['mail']['reg_subject'];
				$body = sprintf($config['mail']['reg_body'], $_POST['name'], session_id());
				require_once 'Mail.php';
				$mail =& Mail::factory('sendmail', array('sendmail_path' => '/usr/sbin/sendmail'));
				$mail->send($_POST['email'], $headers, $body);
				return '/user/regsuccess/';
			} else {
				$_SESSION['ERR_FORM'] = 'Internal sistem Error';
			} 
		}
		return $this->model;
	}

	public function regSuccess()
	{
		return array();
	}

	public function activate()
	{
		if ($this->model->activate($this->params[0])) {
			return array('Account anda sudah diaktifkan, silahkan <a href="/user/login/">login</a>');
		} else {
			return array('Tidak ada account yang akan diaktifkan');
		}
	}

	public function info()
	{
		return User::getUserIP() . '<br/>' . $_SERVER['HTTP_USER_AGENT'];
	}

	public function ajaxGet()
	{
		$start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
		$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 20;
		$sortBy = isset($_POST['sort']) ? $_POST['sort'] : 'name';
		$dir = isset($_POST['dir']) ? $_POST['dir'] : 'ASC';

		$res = new stdClass();
		$res->total = $this->model->count();
		$res->data = $this->model->getAll($start, $limit, $sortBy, $dir);
		return json_encode($res);
	}

	public function ajaxSave()
	{
		$res = new stdClass();
        $res->success = true;
		$user = json_decode($_POST['data']);
        if (!$id = $this->model->save($user)) {
            $res->success = false;
        } else {
			$user->id = $id;
			$res->data = $user;
        }
        return json_encode($res);
	}

	public function ajaxUpdate()
	{
		$res = new stdClass();
        $res->success = true;
		$res->data = $user = json_decode($_POST['data']);
		$this->model->update($user->id, $user);
        return json_encode($res);
	}

} 
