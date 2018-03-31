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
class FavoriteArtistDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_favorite_artist';
	}


	/**
	 *
	 * @param array $arr_profile
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function set_favorite_artist($favorite_artist_id, $client_user_id)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_search = $this->search(array(
			'favorite_artist_id' => $favorite_artist_id,
			'client_user_id'     => $client_user_id,
		));

		if (empty($arr_search))
		{
			$arr_values = array(
					'favorite_artist_id' => $favorite_artist_id,
					'client_user_id'     => $client_user_id,
					'is_enabled'         => '1',
			);
			$result = $this->save($arr_values, true);
		}
		else
		{
			$arr_where = array(
				'favorite_artist_id' => $favorite_artist_id,
				'client_user_id'     => $client_user_id,
			);
			$result = $this->update(array('is_enabled' => '1'), $arr_where);
		}

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
	public function unset_favorite_artist($favorite_artist_id, $client_user_id)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_where = array(
			'favorite_artist_id' => $favorite_artist_id,
			'client_user_id'     => $client_user_id,
		);

		$result = $this->update(array('is_enabled' => '0'), $arr_where, false);
		if (empty($result))
		{
			throw new \Exception('no return db_request', 8002);
		}

		return $result;
	}


	public function get_favorite_artists($arr_where)
	{
		\Log::debug('[start]'. __METHOD__);

		$query = \DB::select_array(array('f.favorite_artist_id', array('a.name', 'favorite_artist_name')));
		$query->from(array($this->_table_name, 'f'));
		$query->join(array('mst_artist', 'a'));
		$query->on('f.favorite_artist_id', '=', 'a.id');
		$query->where('f.client_user_id', '=', $arr_where['client_user_id']);
		$query->where('f.favorite_artist_id', '=', $arr_where['favorite_artist_id']);
		$query->where('f.is_enabled', '=', '1');
		$query->where('f.is_deleted', '=', '0');
		$query->where('a.is_deleted', '=', '0');
		$query->order_by('f.sort', 'ASC');
		$query->order_by('f.id', 'DESC');

		return $query->execute()->as_array();
	}


	public function get_favorite_artists_by_user_id($arr_where, $offset=0, $limit=30)
	{
		\Log::debug('[start]'. __METHOD__);

		$query = \DB::select_array(array(array('f.favorite_artist_id', 'artist_id'), array('a.name', 'artist_name')));
		$query->from(array($this->_table_name, 'f'));
		$query->join(array('mst_artist', 'a'));
		$query->on('f.favorite_artist_id', '=', 'a.id');
		$query->where('f.client_user_id', '=', $arr_where['client_user_id']);
		$query->where('f.is_enabled', '=', '1');
		$query->where('f.is_deleted', '=', '0');
		$query->where('a.is_deleted', '=', '0');
		$query->offset($offset);
		$query->limit($limit);
		$query->order_by('f.sort', 'ASC');
		$query->order_by('f.id', 'DESC');

		return $query->execute()->as_array();
	}


}