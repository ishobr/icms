<?
class Image
{
	/**
	 *  Return thumbnail array:
	 *
	 *  Create thumbnail from $img image with new width $width and height
	 *  $height. In case $height is ommited set height proporsional
	 *
	 *  @param string $img
	 *  @param integer $width
	 *  @param integer $height
	 *  @return array
	 */ 
	public static function createThumbnail($img, $width, $height=0)
	{
		$thumbDir = dirname($img) . '/thumb/';
		if (!file_exists($thumbDir) && !mkdir($thumbDir, 0755, true)) {
			error_log('Unable to create ' . $thumbDir, 0);
			return '';
		}
		$basename = basename($img);
		$dot = strrpos($basename, '.');
		$name = substr($basename, 0, $dot);
		$ext = substr($basename, $dot);
		if (!$height)
			$height = $width;
		$thumbName = $thumbDir . $name . '_' . $width . 'x' . $height . $ext;
		if (!file_exists($thumbName)) {
			$cmd = sprintf('/usr/bin/convert %s -resize %dx%d %s', escapeshellcmd($img), $width , $height, escapeshellcmd($thumbName)); 
			$out = system($cmd);
		}
		$size = getimagesize($thumbName);
		return array('name' => $thumbName, 'width' => $size[0], 'height' => $size[1]);
	}

	/**
	 *  Return fix height thumbnail array:
	 *
	 *  Create thumbnail from $img image with fix height
	 *
	 *  @param string $img
	 *  @param integer $width
	 *  @param integer $height
	 *  @return array
	 */ 
	public static function createFixThumbnail($img, $width, $height)
	{
		if (!file_exists($img)) return array();


		list($w, $h, $type, $attr) = getimagesize($img);
		if ($height && $w/$h > $width/$height) {
			$resize = 'x' . $height;
		} else {
			$resize = $width . 'x';
		}
		$thumbDir = dirname($img) . '/thumb/';
		if (!file_exists($thumbDir) && !mkdir($thumbDir, 0755)) {
			error_log('Unable to create ' . $thumbDir, 0);
			return array();
		}
		$basename = basename($img);
		$dot = strrpos($basename, '.');
		$name = substr($basename, 0, $dot);
		$ext = substr($basename, $dot);
		$thumbName = $thumbDir . $name . '_' . $width . 'x' . $height . $ext;
		if (!file_exists($thumbName)) {
			$cmd = sprintf('/usr/bin/convert %s -resize %s - | /usr/bin/convert - -crop %dx%d+0+0 %s', escapeshellcmd($img), $resize, $width, $height, escapeshellcmd($thumbName));
			$status = '';
			$out = exec($cmd, $status);
		}
		return array('name' => $thumbName, 'width' => $width, 'height' => $height);
	}

}
