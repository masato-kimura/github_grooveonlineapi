<?php
namespace main\model\dto;

class GlobalInformationDto
{
	private static $instance = null;

	private $id;
	private $date;
	private $comment;
	private $offset   = 0;
	private $limit    = 10;
	private $count    = 0;
	private $arr_list = array();

	private function __construct() {}


	public static function get_instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function set_global_information_id($val)
	{
		$this->id = $val;
	}
	public function get_global_information_id()
	{
		return $this->id;
	}

	public function set_date($val)
	{
		$this->date = $val;
	}
	public function get_date()
	{
		return $this->date;
	}

	public function set_comment($val)
	{
		$this->comment = $val;
	}
	public function get_comment()
	{
		return $this->comment;
	}

	public function set_offset($val)
	{
		$this->offset = $val;
	}
	public function get_offset()
	{
		return $this->offset;
	}

	public function set_limit($val)
	{
		$this->limit = $val;
	}
	public function get_limit()
	{
		return $this->limit;
	}

	public function set_count($val)
	{
		$this->count = $val;
	}
	public function get_count()
	{
		return $this->count;
	}

	public function set_arr_list($val)
	{
		$this->arr_list = $val;
	}
	public function get_arr_list()
	{
		return $this->arr_list;
	}

}