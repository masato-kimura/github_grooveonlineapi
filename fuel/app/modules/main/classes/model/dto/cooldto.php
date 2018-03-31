<?php
namespace main\model\dto;

class CoolDto
{
	private static $instance = null;

	private $id;
	private $about;
	private $review_id;
	private $review_user_id;
	private $cool_user_id;
	private $cool_count;
	private $ip;

	private $arr_cool = array();
	private $arr_list = array();
	private $reflection;
	private $is_done;
	private $offset;
	private $limit;
	private $all_count = 0;

	private function __construct() {}

	public static function get_instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function set_cool_id($val)
	{
		$this->id = $val;
	}
	public function get_cool_id()
	{
		return $this->id;
	}

	public function set_about($val)
	{
		$this->about = $val;
	}
	public function get_about()
	{
		return $this->about;
	}

	public function set_review_id($val)
	{
		$this->review_id = $val;
	}
	public function get_review_id()
	{
		return $this->review_id;
	}

	public function set_review_user_id($val)
	{
		$this->review_user_id = $val;
	}
	public function get_review_user_id()
	{
		return $this->review_user_id;
	}

	public function set_cool_user_id($val)
	{
		$this->cool_user_id = $val;
	}
	public function get_cool_user_id()
	{
		return $this->cool_user_id;
	}

	public function set_cool_count($val)
	{
		$this->cool_count = $val;
	}
	public function get_cool_count()
	{
		return $this->cool_count;
	}

	public function set_reflection($val)
	{
		$this->reflection = $val;
	}
	public function get_reflection()
	{
		return $this->reflection;
	}

	public function set_ip($val)
	{
		$this->ip = $val;
	}
	public function get_ip()
	{
		return $this->ip;
	}

	public function set_arr_list($val)
	{
		$this->arr_list = $val;
	}
	public function get_arr_list()
	{
		return $this->arr_list;
	}

	public function set_is_done($val)
	{
		$this->is_done = $val;
	}
	public function get_is_done()
	{
		return $this->is_done;
	}

	public function set_all_count($val)
	{
		$this->all_count = $val;
	}
	public function get_all_count()
	{
		return $this->all_count;
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

}