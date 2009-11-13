<?
class CacheController extends Action 
{

	public function index()
	{
		echo 'No action';
		die;
	}

	public function deleteAll()
	{
		Cache::delete_all();
		die('All cache file deleted');
	}
}
