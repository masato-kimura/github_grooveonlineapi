<?php
namespace main\domain\service;

class Util
{
	/**
	 * 英語として認識するか
	 */
	public static function is_english($name)
	{
		if (preg_match('/^[a-z0-9\s\s\&\[\]\'\-:\/]*$/i', $name))
		{
			return true;
		}
		return false;
	}

	/**
	 * 検索用としてスペースやカッコを省いた値を返す
	 */
	public static function same_name_replace($name)
	{
		return preg_replace('/[\s_\(\)\[\]\.]*/i', '', $name);
	}
}