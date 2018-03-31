<?php
namespace main\model\dto;

class GroupRelationDto
{
	private static $instance = null;

	private $id = null;
	private $group_id = null;
	private $user_id = null;
	private $chief_flag = null;

	private $arr_members = array();
	private $chief_user_id = null;

	private function __construct(){}

	public static function get_instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function set_id($str)
	{
		$this->id = $str;
		return true;
	}
	public function get_id()
	{
		return $this->id;
	}

	public function set_group_id($str)
	{
		$this->group_id = $str;
		return true;
	}
	public function get_group_id()
	{
		return $this->group_id;
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

	public function set_chief_flag($str)
	{
		$this->chief_flag = $str;
		return true;
	}
	public function get_chief_flag()
	{
		return $this->chief_flag;
	}

	public function set_arr_members(array $str)
	{
		$this->arr_members = $str;
		return true;
	}
	public function get_arr_members()
	{
		return $this->arr_members;
	}

	public function set_chief_user_id($str)
	{
		$this->chief_user_id = $str;
		return true;
	}
	public function get_chief_user_id()
	{
		return $this->chief_user_id;
	}

}