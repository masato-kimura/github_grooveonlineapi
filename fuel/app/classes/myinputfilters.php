<?php

class MyInputFilters
{
	/**
	 * 文字エンコーディングの検証フィルタ
	 *
	 * @param string|array $value
	 * @throws HttpInvalidInputException
	 * @return string|array
	 */
	public static function check_encoding($value)
	{
		// 配列の場合は再帰的に処理
		if (is_array($value))
		{
			array_map(array('MyInputFilters', 'check_encoding'), $value);
			return $value;
		}

		// 文字エンコーディングを検証
		\Log::error('enc : '. Fuel::$encoding);
		\Log::error($value);
		\Log::error(mb_detect_encoding($value));


		if (mb_check_encoding($value, Fuel::$encoding))
		{
			return $value;
		}
		else
		{
			// エラーの場合はログに記録
			static::log_error('Invalid character encoding', $value);
			// エラーを表示して終了
			throw new HttpInvalidInputException('Invalid input data');
		}
	}


	public static function check_control($value)
	{
		// 配列の場合は再帰的に処理
		if (is_array($value))
		{
			array_map(array('MyInputFilters', 'check_control'), $value);
			return $value;
		}
/*
		// 改行コードとタブを覗く制御文字が含まれないか
		if (preg_match('/\A[\r\n\t[:^cntrl:]]*\z/u', $value) === 1)
		{
			return $value;
		}
		else
		{
			// 含まれている場合はログに記録
			static::log_error('Invalid control characters', $value);
			// エラーを表示して終了
			throw new HttpInvalidInputException('Invalid input data');
		}
*/
	}


	public static function log_error($msg, $value)
	{
		\Log::error(
			$msg, ': '. Input::url(). ' '. rawurlencode($value). ' '.
			Input::ip() . ' "'. Input::user_agent() . '"'
		);
	}
}