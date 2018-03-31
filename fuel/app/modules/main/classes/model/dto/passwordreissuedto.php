<?php
namespace main\model\dto;

class PasswordreissueDto
{
	private static $instance = null;

	private $id = null;
	private $email = null;
	private $tentative_password = null; // 仮パスワード
	private $auth_type = null;

	private function __construct(){}

	public static function get_instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function set_id ($str)
	{
		$this->id = $str;
		return true;
	}
	public function get_id()
	{
		return $this->id;
	}

	public function set_tentative_id ($str)
	{
		$this->id = $str;
		return true;
	}
	public function get_tentative_id()
	{
		return $this->id;
	}

	public function set_email($str)
	{
		$this->email = $str;
		return true;
	}
	public function get_email()
	{
		return $this->email;
	}

	public function set_tentative_password($str)
	{
		$this->tentative_password = $str;
		return true;
	}
	public function get_tentative_password()
	{
		return $this->tentative_password;
	}

	public function set_auth_type($str)
	{
		$this->auth_type = $str;
		return true;
	}
	public function get_auth_type()
	{
		return $this->auth_type;
	}

}