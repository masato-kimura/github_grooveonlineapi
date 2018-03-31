<?php
namespace main\model\dto;

class FavoriteUserDto
{
	private static $instance = null;

	private $client_user_id;
	private $favorite_user_id;
	private $status;
	private $offset;
	private $limit;
	private $arr_favorite_users = array();

	private function __construct()
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

	public function set_client_user_id($val)
	{
		$this->client_user_id = $val;

		return true;
	}

	public function get_client_user_id()
	{
		return $this->client_user_id;
	}

	public function set_favorite_user_id($val)
	{
		$this->favorite_user_id = $val;

		return true;
	}
	public function get_favorite_user_id()
	{
		return $this->favorite_user_id;
	}

	public function set_status($val)
	{
		$this->status = $val;

		return true;
	}
	public function get_status()
	{
		return $this->status;
	}

	public function set_offset($val)
	{
		$this->offset = $val;

		return true;
	}
	public function get_offset()
	{
		return $this->offset;
	}

	public function set_limit($val)
	{
		$this->limit = $val;

		return true;
	}
	public function get_limit()
	{
		return $this->limit;
	}

	public function set_arr_favorite_users($val)
	{
		$this->arr_favorite_users = $val;

		return true;
	}
	public function get_arr_favorite_users()
	{
		return $this->arr_favorite_users;
	}
}