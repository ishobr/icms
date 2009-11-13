<?

class Admin
{
	function __construct(&$folder)
	{
		if (!User::$id) {
			$_SESSION['login_uri'] = $_SERVER['REQUEST_URI'];
			header('Location: /user/login');
			die();
		}
		View::$theme = 'admin';
		$folder->path = '';
	}

	public function index()
	{
		return $this;
	}
}
