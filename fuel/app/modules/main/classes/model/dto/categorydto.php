<?php
namespace main\model\dto;

class CategoryDto
{
	private static $instance = null;

	private $id;
	private $name;
	private $english;
	private $aliases;
	private $sort;
	private $is_enabled;


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

	public function set_name($str)
	{
		return $this->name = $str;
	}
	public function get_name()
	{
		return $this->name;
	}

	public function set_english($str)
	{
		return $this->english = $str;
	}
	public function get_english()
	{
		return $this->english;
	}

	public function set_aliases($str)
	{
		return $this->aliases = $str;
	}
	public function get_aliases()
	{
		return $this->aliases;
	}

	public function set_sort($str)
	{
		return $this->sort = $str;
	}
	public function get_sort()
	{
		return $this->sort;
	}

	public function set_is_enabled($str)
	{
		return $this->is_enabled = $str;
	}
	public function get_is_enabled()
	{
		return $this->is_enabled;
	}
}