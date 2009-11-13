<?
class Photo extends Article
{
	public $photos = array();
	public $main_photo = array();
	public $thumb_photos = array();

	public function get($id=0, $useCache=true)
	{
		if (!$id) $id =& $this->id;
		if ($useCache && $cache = Cache::get($id))
			return $cache;

		parent::get($id, !USE_CACHE);
		$this->photos = array();
		$i = 0;
		if (!is_array($this->files['photo'])) return $this;
		foreach ($this->files['photo'] as &$f) {
			$f->main =& Image::createThumbnail($f->name, $this->folder->config['imgWidth']); 
			$f->thumb =& Image::createThumbnail($f->name, $this->folder->config['imgThumbWidth']); 
			if (++$i == 1)
				$f->fpthumb =& Image::createThumbnail($f->name, $this->folder->config['fpThumbWidth']); 
			$this->photos[] = $f;
		}
		if ($this->pub_on > 0) {
			Cache::set($id, $this);
		}
		return $this;
	}
}
