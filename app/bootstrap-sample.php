<?php

define('ROOT_DIR', dirname(dirname(__FILE__)));

include ROOT_DIR . '/app/config.php';

set_include_path(ROOT_DIR . '/lib/' . PATH_SEPARATOR . ROOT_DIR . '/app/controllers/' . PATH_SEPARATOR . ROOT_DIR . '/app/models/' . PATH_SEPARATOR . get_include_path());
ini_set('display_errors', '0');
set_magic_quotes_runtime(0);

setlocale(LC_ALL, 'id_ID');

if (isset($_GET['theme'])) $config['theme'] =& $_GET['theme'];
// for m.namadomain.com
if (strpos($_SERVER['HTTP_HOST'], 'm.') === 0) $config['theme'] = 'm';

function __autoload($class)
{
	if (!$class) return;
	$lib = strtolower($class) . '.php';
	require $lib;
}

// Cache
Cache::init($config['cache']['backend'], $config['cache']['options']);

Session::start();
User::start();

require ROOT_DIR . '/lib/controller.php';

ob_start();

Ctrl::dispatch();

$html = str_replace(array('/[\r\n\t]/', '/\s{2,}/'), array(' ', ' '), ob_get_clean());

echo $html;
