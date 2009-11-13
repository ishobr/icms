<?
class RSSController extends Action
{
	protected $_model = 'Search';

	protected function init()
	{
		$this->model = new $this->_model($this->folder);
		setlocale(LC_ALL, 'en_US');
	}

	public function index()
	{
		$limit = isset($this->params[0]) ? $this->params[0] : 7;
		$news = $this->model->getNews($limit);
		$ret = array();
		foreach ($news as &$new) {
			$folder = Folder::getById($new['folder_id']);
			$uri =  $folder->path . '/' . $new['slug'] . '.htm';
			$obj = Ctrl::fetchData($uri);
			if (!is_object($obj) || !isset($obj->subject)) continue;
			$ret[] = $obj;
		}
		return $ret;
	}


}
