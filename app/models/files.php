<?
class Files extends Model
{
	public $id;
	public $subject;
	public $name;
	public $note;
	public $type;
	public $size;

	private $baseDir = 'files';

	function __construct($id=0)
	{
		if (!self::$dbh)
			$this->connect();
		if ($id)
			$this->get($id);
	}

	private function get($id)
	{
		if ($row =& self::$dbh->getRow('SELECT * FROM file WHERE id=?', array($id))) {
			$this->id =& $row['id'];
			$this->subject =& $row['subject'];
			$this->name =& $row['name'];
			$this->note =& $row['note'];
			$this->type =& $row['type'];
			$this->size = filesize($this->name);
		}
	}
	public function save(&$file, &$folder, $subject='', $note='', $key='')
	{
		$saveFolder = $this->baseDir . $folder->path;
		if (!file_exists($saveFolder))
			mkdir($saveFolder, 0755, true);
		$file['name'] = preg_replace('/\s/', '-', strtolower($file['name']));
		$fileName = $saveFolder . $file['name'];
		if (file_exists($fileName)) {
			$_SESSION['error_uri'] =& $_SERVER['REQUEST_URI'];
			$_SESSION['ERROR_TXT'] = 'File ' . $fileName . ' sudah diupload. Gunakan file dengan nama lain.';
			return 0;
		}
		if (!move_uploaded_file($file['tmp_name'], $fileName))
			return 0;
		
		$res =& self::$dbh->query('INSERT INTO file (subject, name, note, type, `key`) VALUES (?, ?, ?, ?, ?)', array($subject, $fileName, $note, $file['type'], $key));
		if(PEAR::isError($res)) {
			error_log($res->getDebugInfo(), 0);
			return 0;
		} else {
			return self::$dbh->getOne('SELECT id FROM file WHERE name=?', array($fileName));
		}
	}

	public function del()
	{
		unlink($this->name);
	}
}
