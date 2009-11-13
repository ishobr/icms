<?

class TestController extends Action
{
	public function index()
	{
		setlocale(LC_ALL, 'ar');
		echo strftime('%A %d %B %Y');
		die('Test');
	}
}
