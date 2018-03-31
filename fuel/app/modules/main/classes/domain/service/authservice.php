<?php
namespace main\domain\service;

use main\model\dto\AuthDto;
final class AuthService
{
	private static $_arr_inner_api_keys = array(
			0 => 'm',
			1 => 'a',
			2 => 's',
			3 => 'a',
			4 => 't',
			5 => 'k',
			6 => 'i',
			7 => 'm',
			8 => 'u',
			9 => 'r',
		);

	public static function set_request_api_key($object)
	{
		$auth_dto = \main\model\dto\AuthDto::get_instance();

		if (empty($object->api_key))
		{
			\Log::error('api_keyがありません');
			throw new \Exception('authenticated error', 2001);
		}
		$auth_dto->set_api_key($object->api_key);
		return true;
	}

	/**
	 * api_keyをチェック
	 * 正常:true, 異常:false
	 * @throws \Exception
	 * @return boolean
	 */
	public static function check_api_key()
	{

		$auth_dto = AuthDto::get_instance();
		$request_api_key = $auth_dto->get_api_key();

		$last_initiated_api_key = self::_initiated_api_key(time()-60);
		$just_initiated_api_key = self::_initiated_api_key();
		$next_initiated_api_key = self::_initiated_api_key(time()+60);
		$arr_initiated_api_key = array(
				$just_initiated_api_key,
				$last_initiated_api_key,
				$next_initiated_api_key,
		);

		if ( ! in_array($request_api_key, $arr_initiated_api_key))
		{
			throw new \Exception('authenticated error', 2001);
		}
		return true;
	}

	private static function _initiated_api_key($timestamp=null)
	{
		$arr_keys = self::$_arr_inner_api_keys;
		$YmdHi = \Date::forge($timestamp)->format("%Y%m%d%H%M");
		$mid = (int)substr(\Date::forge($timestamp)->format("%M"), -1, 1);
		$initialized_key = md5($arr_keys[$mid] + $YmdHi);
		return $initialized_key;
	}

}