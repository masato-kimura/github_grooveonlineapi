<?php
namespace main\model\dto;

class RankDto
{
	private static $instance = null;

	private $about;
	private $offset;
	private $limit;
	private $arr_list = array();
	private $aggregate_from;
	private $aggregate_to;

	private function __construct() {
	}

	public static function get_instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function set_about($val)
	{
		$this->about = $val;
	}
	public function get_about()
	{
		return $this->about;
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

	public function set_arr_list($val)
	{
		$this->arr_list = $val;
	}
	public function get_arr_list()
	{
		return $this->arr_list;
	}

	public function set_aggregate_from($val)
	{
		$this->aggregate_from = $val;
	}
	public function get_aggregate_from()
	{
		return $this->aggregate_from;
	}

	public function set_aggregate_to($val)
	{
		$this->aggregate_to = $val;
	}
	public function get_aggregate_to()
	{
		return $this->aggregate_to;
	}
}
