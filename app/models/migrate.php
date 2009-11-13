<?

class Migrate extends Dbh
{
	public function user()
	{
		$db =& DB::connect('mysql://em:islam,nan,jaya@219.83.123.212/em');
		if (PEAR::isError($db))
			die($db->getDebugInfo());
		$db->setFetchMode(DB_FETCHMODE_ASSOC);

		$res = $db->query('SELECT id, name, joint, level, pwd, clear_pwd, last FROM user LEFT JOIN user_pwd ON id=user_id WHERE id!="admin" ORDER BY id');
		if (PEAR::isError($res))
			die($res->getDebugInfo());

		while ($row = $res->fetchRow()) {
			$row['id'] = trim($row['id']);
			if (!$row['id'] || preg_match('/[^0-9a-zA-Z-_@.]/', $row['id'])) {
				echo "<p>Exclude '{$row['id']}'</p>";
				continue;
			}
			$res2 = self::$dbh->query('INSERT INTO user (uid, name, mail, level, since) VALUES (?, ?, ?, ?, ?)', array($row['id'], $row['name'], $row['mail'], $row['level'], $row['joint']));
			if (PEAR::isError($res2))
				printf('%s<br/>', $res2->getDebugInfo());
			$user_id = self::$dbh->getOne('SELECT id FROM user WHERE uid=?', array($row['id']));
			$res3 = self::$dbh->query('INSERT INTO user_pwd (user_id, pwd, clear) VALUES (?, ?, ?)', array($user_id, $row['pwd'], str_rot13($row['clear_pwd'])));
			if (PEAR::isError($res3))
				printf('%s<br/>', $res3->getDebugInfo());
			++$count;
		}
		printf("<p>$count Processed</p>");
	} 

}
