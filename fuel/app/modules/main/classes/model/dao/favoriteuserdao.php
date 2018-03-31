<?php
namespace main\model\dao;

/**
 * @throws \Exception
 *  1001 正常
 *  8001 システムエラー
 *  8002 DBエラー
 *  9001
 *
 * @author masato
 *
 */
class FavoriteUserDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_favorite_user';
	}

	/**
	 *
	 * @param array $arr_profile
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function set_favorite_user($favorite_user_id, $client_user_id)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_values = array(
				'favorite_user_id' => $favorite_user_id,
				'client_user_id'   => $client_user_id,
		);

		$result = $this->save($arr_values, true);
		if (empty($result))
		{
			throw new \Exception('no return db_request', 8002);
		}

		return $result;
	}

	/**
	 *
	 * @param array $arr_profile
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function unset_favorite_user($favorite_user_id, $client_user_id)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_where = array(
				'favorite_user_id' => $favorite_user_id,
				'client_user_id'   => $client_user_id,
		);

		$result = $this->delete(array(), $arr_where, false);
		if (empty($result))
		{
			throw new \Exception('no return db_request', 8002);
		}

		return $result;
	}


	public function get_favorite_users($arr_where)
	{
		\Log::debug('[start]'. __METHOD__);

		$query = \DB::select_array(array('f.favorite_user_id', array('u.user_name', 'favorite_user_name')));
		$query->from(array($this->_table_name, 'f'));
		$query->join(array('trn_user', 'u'));
		$query->on('f.favorite_user_id', '=', 'u.id');
		$query->where('f.client_user_id', '=', $arr_where['client_user_id']);
		$query->where('f.is_deleted', '=', '0');
		$query->where('u.is_deleted', '=', '0');
		$query->order_by('f.sort', 'ASC');
		$query->order_by('f.id', 'DESC');

		return $query->execute()->as_array();
	}

}