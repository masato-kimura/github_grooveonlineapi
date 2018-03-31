<?php
namespace main\model\dto;

class AuthDto
{
	private static $instance = null;
	
	private $api_key = null;
	
	private function __construct()
	{}

	public static function get_instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	public function set_api_key($str)
	{
		$this->api_key = $str;
		return true;
	}
	public function get_api_key()
	{
		return $this->api_key;
	}
}