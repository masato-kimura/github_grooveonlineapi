<?php
namespace main\model\dto;

class LoginDto
{
	private static $instance = null;
	
	private $id = null;
	private $user_id = null; // user::id (primary)
	private $login_hash = null;
	private $remark = null;
	private $is_deleted = null;
	
	private function __construct(){}

	public static function get_instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	public function set_user_id($str)
	{
		$this->user_id = $str;
		return true;
	}
	public function get_user_id()
	{
		return $this->user_id;
	}
	
	public function set_login_hash($str)
	{
		$this->login_hash = $str;
		return true;
	}
	public function get_login_hash()
	{
		return $this->login_hash;
	}
	
	public function set_remark($str)
	{
		$this->remark = $str;
		return true;
	}
	public function get_remark()
	{
		return $this->remark;
	}
	
	
}