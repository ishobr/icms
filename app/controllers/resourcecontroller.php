<?
class ResourceController extends Action
{
	public function index()
	{
		return '';
	}

	public function css()
	{
		global $config;

		$key = 'css' . $this->params[0];
		if (!$ret = Cache::get($key)) {
			$ret = '';
			$files = explode(',', $this->params[1]);
			foreach ($files as $f) {
				if (file_exists($css = "tpl/{$config['theme']}/css/$f.css")) {
					$ret .= file_get_contents($css);
				}
			}
			//Cache::set($key, $ret);
		}
		header('Content-Type: text/css');
		header('Vary: Accept-Encoding');
		header('Cache-Control: max-age: 86400');
		header('Pragma: public');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time()+86400) . ' GMT');
		die($ret);
	}

	public function js()
	{
		global $config;

		$key = 'js' . $this->params[0];
		if (!$ret = Cache::get($key)) {
			$ret = '';
			$files = explode(',', $this->params[1]);
			foreach ($files as $f) {
				if (file_exists($css = "tpl/{$config['theme']}/js/$f.js")) {
					$ret .= file_get_contents($css);
				}
			}
			//Cache::set($key, $ret);
		}
		header('Content-Type: application/x-javascript');
		header('Vary: Accept-Encoding');
		header('Cache-Control: max-age: 86400');
		header('Pragma: public');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time()+86400) . ' GMT');
		die($ret);
	}

}
?>
