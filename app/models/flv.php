<?
Class Flv extends Article
{
	public $video;
	public $image;
	
	public function get($id=0, $useCache=true)
	{
		if (!$id) $id =& $this->id;
		if ($useCache && $cache = Cache::get($id)) {
			return $cache;
		}

		parent::get($id, false);

		$this->video = @array_shift($this->files['video']);
		$this->image = @array_shift($this->files['image']);

		if ($this->pub_on > 0) {
			Cache::set($id, $this);
		}
		return $this;
	}
}
