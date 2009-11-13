<?
class Links extends Article
{
	function __construct(&$folder)
	{
		parent::__construct($folder);
	}

	public function goto()
	{
		$this->get((int)$this->folder->params[0]);
		return $this->src;
	}
}
