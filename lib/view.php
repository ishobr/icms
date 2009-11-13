<?php

class View
{
	public static $theme;
	public static $frame;
	public static $title;
	public static $site_content;
	public static $view_admin = 0;

	public static function out(&$folder='')
	{
		if (file_exists(self::$frame)) {
			include self::$frame;
		} else {
			echo 'Oop! Template file (' . self::$frame . ') file not found';
			die;
		}
	}

	public static function render($tpl, &$input)
	{
		global $config;

		ob_start();

		$folder =& $input['folder'];
		$params =& $input['params'];
		$data =& $input['data'];
		$total =& $input['total'];
		
		if (file_exists($tpl))
			include $tpl;
		return ob_get_clean();
	}

	public static function renderPanel(&$data)
	{
		if (!$data) return;

		$ret = '<div id="ctrl"><ul>';
		foreach($data as &$d) {
			$ret .= sprintf('<li class="%s"><a href="%s" title="%s"%s>%s</a></li>', $d['class'], $d['href'], $d['title'], $d['onclick'] ? " onclick=\"return confirm('{$d['onclick']}')\"" : '', $d['title']); 
		}
		return $ret .= '</ul></div>';
	}

	public static function renderPageNav($base, $page, $total, $limit)
	{
		(int)$page++;
		$totalPage = ceil($total/$limit);
		$ret ='<div id="pagenav"><ul>';
		if ($page > 1) {
			$ret .= sprintf('<li><a href="%s">|&lt;</a></li>', $base);
			$ret .= sprintf('<li><a href="%s/%d" title="Page %d of %d">&lt;</a></li>', $base, $page-2, $page-1, $totalPage);
		}
			$ret .= sprintf('<li>Page %d of %d</li>', $page, $totalPage);
		if ($page < $totalPage) {
			$ret .= sprintf('<li><a href="%s/%d" title="Page %d of %d">&gt;</a></li>', $base, $page, $page+1, $totalPage);
			$ret .= sprintf('<li><a href="%s/%d">&gt;|</a></li>', $base, $totalPage-1);
		}
		
		return $ret .= '</ul></div>';
	}

	public static function subLead(&$lead, $count)
	{
		if (str_word_count($lead) <= $count) return $lead;
		$lead = strip_tags($lead);
		return '<p class="lead">' . self::strWordCount($lead, $count) . '...</p>';
	}

	public static function subSubject(&$lead, $count)
	{
		if (str_word_count($lead) <= $count) return $lead;
		$lead = strip_tags($lead);
		return self::strWordCount($lead, $count) . '...';
	}

	public static function strWordCount(&$str, $count)
	{
		$ret = explode(' ', $str);
		$ret = array_slice($ret, 0, $count);
		return implode(' ', $ret);
	}

	public static function fetch($uri, $setCache=true)
	{
		return Ctrl::fetch($uri, $setCache);
	}
}
