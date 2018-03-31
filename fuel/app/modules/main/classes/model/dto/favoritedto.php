<?php
namespace main\model\dto;

use model\dto\BaseDto;
class FavoriteUserDto extends BaseDto
{
	private static $instance = null;

	private $client_user_id;
	private $favorite_user_id;

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
}