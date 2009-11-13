<?
class ContactController extends Action 
{
	private $mailto = 'indra@alkausar.org, habib@alkausar.org, edwar@alkausar.org';
	private $cc = 'alkausar.boardingschool@yahoo.co.id';

	public function index()
	{
		if (isset($_POST['submit'])) {
			$mailfrom = $_POST['name'] . ' <' . str_replace("\n", '', $_POST['email']) . '>';
			$headers['From'] =& $mailfrom;
			$headers['Subject'] = sprintf('Form Kontak Alkausar - %s', $_POST['to']);
			$headers['To'] =& $this->mailto;
			$headers['Cc'] =& $this->cc;
			$body = $_POST['body'];

			require_once 'Mail.php';
			$mail =& Mail::factory('sendmail', array('sendmail_path' => '/usr/sbin/sendmail'));
			$mail->send($this->mailto, $headers, $body);
			return '/contact/terimakasih';
		}
		return $this;
	}
	
	public function terimakasih()
	{
		return $this;
	}

}
