<?php
namespace main\model\dto;

class UserCommentDto
{
	private static $instance = null;

	private $user_comment_id;
	private $user_id;
	private $priority;
	private $about;
	private $user_comment;
	private $arr_comment;

	private function construct()
	{

	}

	public static function get_instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function set_user_comment_id($val)
	{
		$this->user_comment_id = $val;
	}
	public function get_user_comment_id()
	{
		return $this->user_comment_id;
	}

	public function set_user_id($val)
	{
		$this->user_id = $val;
	}
	public function get_user_id()
	{
		return $this->user_id;
	}

	public function set_priority($val)
	{
		$this->priority = $val;
	}
	public function get_priority()
	{
		return $this->priority;
	}

	public function set_about($val)
	{
		$this->about = $val;
	}
	public function get_about()
	{
		return $this->about;
	}

	public function set_user_comment($val)
	{
		$this->user_comment = $val;
	}
	public function get_user_comment()
	{
		return $this->user_comment;
	}

	public function set_arr_comment($val)
	{
		$this->arr_comment = $val;
	}
	public function get_arr_comment()
	{
		return $this->arr_comment;
	}


}