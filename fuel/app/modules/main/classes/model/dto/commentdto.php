<?php
namespace main\model\dto;

class CommentDto
{
	private static $instance = null;

	private $id;
	private $review_id;
	private $about;
	private $review_user_id;
	private $comment_user_id;
	private $comment;
	private $comment_datetime;
	private $ip;
	private $is_available;
	private $offset = 0;
	private $limit = 10;
	private $count = 0;
	private $mode;
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

	public function set_comment_id($val)
	{
		$this->id = $val;
	}
	public function get_comment_id()
	{
		return $this->id;
	}

	public function set_review_id($val)
	{
		$this->review_id = $val;
	}
	public function get_review_id()
	{
		return $this->review_id;
	}

	public function set_about($val)
	{
		$this->about = $val;
	}
	public function get_about()
	{
		return $this->about;
	}

	public function set_review_user_id($val)
	{
		$this->review_user_id = $val;
	}
	public function get_review_user_id()
	{
		return $this->review_user_id;
	}

	public function set_comment_user_id($val)
	{
		$this->comment_user_id = $val;
	}
	public function get_comment_user_id()
	{
		return $this->comment_user_id;
	}

	public function set_comment($val)
	{
		$this->comment = $val;
	}
	public function get_comment()
	{
		return $this->comment;
	}

	public function set_comment_datetime($val)
	{
		$this->comment_datetime = $val;
	}
	public function get_comment_datetime()
	{
		return $this->comment_datetime;
	}

	public function set_is_available($val)
	{
		$this->is_available = $val;
	}
	public function get_is_available()
	{
		return $this->is_available;
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

	public function set_mode($val)
	{
		$this->mode = $val;
	}
	public function get_mode()
	{
		return $this->mode;
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