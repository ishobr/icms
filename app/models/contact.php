<?
class Contact extends Dbh
{
	private $mailto = 'indra@alkausar.org, habib@alkausar.org, edwar@alkausar.org';
	public $folder;

	function __construct(&$folder)
	{
		$this->folder =& $folder;
	}

	public function index()
	{
		if (isset($_POST['submit'])) {
			$mailfrom = $_POST['name'] . ' <' . str_replace("\n", '', $_POST['email']) . '>';
			$headers['From'] =& $mailfrom;
			$headers['Subject'] = 'Form Kontak Alkausar';
			$headers['To'] =& $this->mailto;
			if ($this->bcc)
				$headers['Cc'] =& $this->bcc;
			$body = $_POST['body'];

			require_once 'Mail.php';
			$mail =& Mail::factory('sendmail', array('sendmail_path' => '/usr/sbin/sendmail'));
			$mail->send($this->mailto, $headers, $body);
			return '/kontak/terimakasih';
		}
		return $this;
	}
	
	public function terimakasih()
	{
		return $this;
	}
}
