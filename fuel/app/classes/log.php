<?php
class Log extends Fuel\Core\Log
{
	public static function info($msg, $method=null)
	{
		if ( ! is_scalar($msg)) $msg = print_r($msg, true);
		return parent::info($msg, $method);
	}

	public static function debug($msg, $method=null)
	{
		if ( ! is_scalar($msg)) $msg = print_r($msg, true);
		return parent::debug($msg, $method);
	}

	public static function warning($msg, $method=null)
	{
		if ( ! is_scalar($msg)) $msg = print_r($msg, true);
		return parent::warning($msg, $method);
	}

	public static function error($msg, $method=null)
	{
		if ( ! is_scalar($msg)) $msg = print_r($msg, true);
		return parent::error($msg, $method);
	}
}