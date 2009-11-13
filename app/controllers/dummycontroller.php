<?
class DummyController extends Action 
{
	protected static $headlineIds = array();

	public static $config = array(
		'indexLimit' => 7,
		'arcLimit' => 15
	);

	public function init()
	{
		$this->folder->config = array_merge(self::$config, $this->folder->config);
	}
	
	public function index()
	{
		$limit = $this->folder->config['arcLimit'];
		$ret = array();
		foreach ($this->folder->subFolders as $s) {
			$path = Folder::path($s);
			$uri = $path . '/last/' . $limit;
			$obj = Ctrl::fetchData($uri);
			foreach ($obj['data'] as $k => $v)
				$ret[$k] = $v;
		}
		krsort($ret);
		return array_splice($ret, 0, $limit);
	}

	public function panel()
	{
		return array();
	}

	public function fplast()
	{
		$limit = $this->params[0];
		$ret = array();
		foreach ($this->folder->subFolders AS $s) {
			$path = Folder::path($s);
			unset($folder);
			$folder = Folder::getByUri($path);
			$model = $folder->ctrl;
			unset($m);
			$m = new $model($folder);
			$last = $m->getLast(0, $limit);
			if (!isset($last['data'])) continue;
			foreach ($last['data'] AS $k => $v) {
				if (!in_array($v->id, self::$headlineIds)) {
					$ret[$k] = $v;
				}
			}
		}
		krsort($ret);	
		return array_splice($ret, 0, $limit);
	}

	public function fpLastHeadline()
	{
		$limit = $this->params[0];
		$ret = array();
		foreach ($this->folder->subFolders AS $s) {
			$path = Folder::path($s);
			$uri = $path . '/lastheadline/' . $limit;
			$obj = Ctrl::fetchData($uri);
			foreach ($obj['data'] as $k => $v)
				$ret[$k] = $v;
		}
		krsort($ret);
		return array_splice($ret, 0, $limit);
	}

	/*
	 * Content terakhir dikurangi sejumlah headline
	*/
	public function fpLastMinHeadline()
	{
		$limit = (int) $this->folder->params[0];
		$minHeadline = (int) $this->folder->params[1];
		$ret = array();
		foreach ($this->folder->subFolders AS $s) {
			$path = Folder::path($s);
			unset($folder);
			$folder = new Folder($path);
			$folder->params[0] = $limit + $minHeadline; 
			$model = $folder->model;
			unset($m);
			$m = new $model($folder);
			$last = $m->last();
			if (!isset($last['data'])) continue;
			foreach ($last['data'] AS $k => $v) {
				$ret[$k] = $v;
			}
		}
		krsort($ret);
		foreach ($ret as $k => $v) {
			if ($minHeadline && $v->headline) {
				$minHeadline--;
				unset($ret[$k]);
			}
		}
		return array_splice($ret, 0, $limit);
	}

	public function rss()
	{
		setlocale(LC_ALL, 'en_US');
		$limit = 10;
		$ret = array();
		foreach ($this->folder->subFolders AS $s) {
			$path = Folder::path($s);
			$uri = $path . '/last/' . $limit;
			$ret += Ctrl::fetchData($uri);
		}
		krsort($ret);	
		return array_splice($ret, 0, $limit);
	}
}
