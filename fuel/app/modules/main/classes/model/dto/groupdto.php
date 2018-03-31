<?php
namespace main\model\dto;

class GroupDto
{
	private static $instance = null;

	private $id = null;
	private $name = null;
	private $category_id = null;
	private $link = null;
	private $profile_fields = null;
	private $picture_url = null;
	private $is_leaved = null;
	private $leave_date = null;
	private $members = array();

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
		$this->id = $str;
		return true;
	}
	public function get_group_id()
	{
		return $this->id;
	}

	public function set_name($str)
	{
		$this->name = $str;
		return true;
	}
	public function get_name()
	{
		return $this->name;
	}

	public function set_category_id($str)
	{
		$this->category_id = $str;
		return true;
	}
	public function get_category_id()
	{
		return $this->category_id;
	}

	public function set_link($str)
	{
		$this->link = $str;
		return true;
	}
	public function get_link()
	{
		return $this->link;
	}

	public function set_profile_fields($str)
	{
		$this->profile_fields = $str;
		return true;
	}
	public function get_profile_fields()
	{
		return $this->profile_fields;
	}

	public function set_picture_url($str)
	{
		$this->picture_url = $str;
		return true;
	}
	public function get_picture_url()
	{
		return $this->picture_url;
	}

	public function set_is_leaved($str)
	{
		$this->is_leaved = $str;
		return true;
	}
	public function get_is_leaved()
	{
		return $this->is_leaved;
	}

	public function set_leave_date($str)
	{
		$this->leave_date = $str;
		return true;
	}
	public function get_leave_date()
	{
		return $this->leave_date;
	}

	public function set_members($str)
	{
		$this->members = $str;
		return true;
	}
	public function get_members()
	{
		return $this->members;
	}

}