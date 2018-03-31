<?php
namespace main\model\dto;

class UserInformationDto
{
	private static $instance = null;

	private $id;
	private $user_id;
	private $review_count;
	private $comment_count;
	private $offset = 0;
	private $limit = 10;
	private $count = 0;
	private $updated_at;
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

	public function set_user_information_id($val)
	{
		$this->id = $val;
	}
	public function get_user_information_id()
	{
		return $this->id;
	}

	public function set_review_count($val)
	{
		$this->review_count = $val;
	}
	public function get_review_count()
	{
		return $this->review_count;
	}

	public function set_comment_count($val)
	{
		$this->comment_count = $val;
	}
	public function get_comment_count()
	{
		return $this->comment_count;
	}

	public function set_user_id($val)
	{
		$this->user_id = $val;
	}
	public function get_user_id()
	{
		return $this->user_id;
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

	public function set_updated_at($val)
	{
		$this->updated_at = $val;
	}
	public function get_updated_at()
	{
		return $this->updated_at;
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