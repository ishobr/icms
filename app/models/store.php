<?
class Store extends Article
{
	public $price;	

	public function insert(&$data)
	{
		parent::insert($data);

		if ($this->id) {
	
			if (isset($data['price']) && $data['price'])
				$this->insertItem(PRICE, preg_replace('/\D/', '', $data['price']));
			return $this->get();
		}
		return $this;
	}

	public function update(&$data)
	{
		if (parent::update($data)) {
			if (!self::same_txt($this->price, $data['price'])) {
				$this->insertItem(PRICE, preg_replace('/\D/', '', $data['price']));
			}

			if ($this->pub_on > 0) {
				$this->get($this->id, false);
				$this->updateSearch();
				Cache::set($this->id, $this);
				Cache::regflush($this->folder->path);
			}
			return true;
		}
		return false;
	}

	public function get($id=0, $useCache=true)
	{
		if (!$id) $id =& $this->id;
		if ($useCache && $cache = Cache::get($id))
			return $cache;

		parent::get($id, false);
		$this->price = $this->getLastItem(PRICE);

		if ($this->pub_on > 0)
			Cache::set($id, $this);
		return $this;
	}

	public function saveOrder()
	{
		if (!$sess_id = session_id()) return false;
		if ($item_id  = self::$dbh->getOne('SELECT id FROM order_tmp WHERE session_id=? AND article_id=?', array($sess_id, $this->id))) {
			$res = self::$dbh->query('UPDATE order_tmp SET qty=qty+1 WHERE id=?', $item_id);
		} else
			$res = self::$dbh->query('INSERT INTO order_tmp (session_id, article_id, price) VALUES (?, ?, ?)', array($sess_id, $this->id, $this->price));
		if (PEAR::isError($res))
			die($res->getDebugInfo());
		return true;
			
	}

	public function getTmpOrder()
	{
		$ret = new stdClass();
		$ret->items = array();
		$ret->qty = $ret->price = 0;
		$res = self::$dbh->query('SELECT * FROM order_tmp WHERE session_id=? ORDER BY id', session_id());
		while ($row = $res->fetchRow()) {
			$item = $this->get($row['article_id']);
			$row['name'] = $item->subject;
			$ret->items[] = $row;
			$ret->qty += $row['qty'];
			$ret->price += $row['qty'] * $row['price'];
		}
		return $ret;
	}
	
	public function getOrder($offset, $limit)
	{
		$ret = array();
		$ret['total'] = self::$dbh->getOne('SELECT count(*) FROM order_buyer');
		$ret['data'] = array();

		$res = self::$dbh->query("SELECT * FROM order_buyer ORDER BY id DESC LIMIT $offset, $limit");
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
			$row->items = array();
			$row->qty = $row->price = 0;
			$res2 = self::$dbh->query('SELECT article_id, qty, price FROM order_item WHERE buyer_id=? ORDER BY id', $row->id);
			while ($row2 = $res2->fetchRow(DB_FETCHMODE_OBJECT)) {
				$item = $this->get($row2->article_id);
				$row2->name = $item->subject;
				$row->items[] = $row2;
				$row->qty += $row2->qty;
				$row->price += $row2->qty * $row2->price;
			}
			$ret['data'][] = $row;
		}
		return $ret;
	}

	public function delOrder($order_id)
	{
		self::$dbh->query('DELETE FROM order_tmp WHERE id=?', $order_id);
	}
	
	public function updateOrder(&$qty)
	{
		$res = self::$dbh->query('SELECT * FROM order_tmp WHERE session_id=? ORDER BY id', session_id());
		$i = 0;
		while ($row = $res->fetchRow()) {
			if ($row['qty'] != $qty[$i])
				self::$dbh->query('UPDATE order_tmp SET qty=? WHERE id=?', array($qty[$i], $row['id']));
			++$i;
		}
	}

	public function doCheckout($seller)
	{
		if (!isset($seller['email']) || !strpos($seller['email'], '@'))
			return false;
		$res = self::$dbh->query('INSERT INTO order_buyer (name, mail, shipping_address, order_on) VALUES (?, ?, ?, ?)', array($seller['name'], $seller['email'], $seller['address'], $_SERVER['REQUEST_TIME']));
		if (!$buyer_id = self::$dbh->getOne('SELECT id FROM order_buyer WHERE mail=?', array($seller['email'])))
			return false;

		$res = self::$dbh->query('SELECT * FROM order_tmp WHERE session_id=? ORDER BY id', session_id());
		while ($row = $res->fetchRow()) {
			$res2 = self::$dbh->query('INSERT INTO order_item (buyer_id, article_id, qty, price) VALUES (?, ?, ?, ?)', array($buyer_id, $row['article_id'], $row['qty'], $row['price']));
			if (!PEAR::isError($res2))
				self::$dbh->query('DELETE FROM order_tmp WHERE id=?', $row['id']);	
		}
			
		return true;
	}
}
