<?php
namespace main\model\dao;

use main\model\dto\ArtistDto;


/**
 * @throws \Exception
 *  1001 正常
 *  9001
 * @author masato
 *
 */
class ArtistDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	const DELIMITER = ",:@"; // same_namesカラム内区切り文字

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'mst_artist';
	}

	public function get_delimiter()
	{
		return static::DELIMITER;
	}


	/**
	 *
	 * @param array $arr_profile
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function set_values(array $arr_request)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$arr_values = array();
		foreach ($arr_request as $i => $val)
		{
			if ( ! is_null($val))
			{
				$arr_values[$i] = $val;
			}
		}

		list($key, $count) = $this->save($arr_values);
		if (empty($count))
		{
			return false;
		}
		return array($key, $count);
	}


	/**
	 *
	 * @param array $arr_profile
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function set_multi_values(array $arr_request, $is_ignore=false)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		list($key, $count) = $this->save_multi($arr_request, $is_ignore);
		if (empty($count))
		{
			return false;
		}
		return array($key, $count);
	}


	/**
	 *
	 * @param array $arr_profile
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function set_insert_update_values(array $arr_request, $search, $is_ignore=false)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$now = \Date::forge()->format('%Y-%m-%d %H:%M:%S');
		$artist_dto = ArtistDto::get_instance();
		foreach ($arr_request as $i => $i_val)
		{
			unset($i_val['id']);
			if (empty($i_val['image_url']))
			{
				$i_val['sort'] = 0;
			}
			else
			{
				$i_val['sort'] = 1;
			}

			if (strcasecmp($i_val['name'], $search) == 0)
			{
				$i_val['sort'] = $i_val['sort'] + 1;
			}

			if (strcasecmp($i_val['name'], $artist_dto->get_artist_name()) == 0)
			{
				$i_val['sort'] = $i_val['sort'] + 1;
			}

			$i_val['created_at'] = $now;
			$i_val['updated_at'] = $now;
			$arr_columns = array_keys($i_val);

			$query = \DB::insert($this->_table_name);
			$query->columns($arr_columns);
			$query->values($i_val);
			$sql = $query->compile();
			$sql = preg_replace('/^insert/i', 'insert ignore', $sql);
			if ( ! empty($i_val['image_url']))
			{
				$sql .= " ON DUPLICATE KEY UPDATE ";
				$sql .= "updated_at = '". $now. "',";
				$sql .= "image_url    = '". $i_val['image_url']. "',";
				$sql .= "image_small      = '". $i_val['image_small']. "',";
				$sql .= "image_medium     = '". $i_val['image_medium']. "',";
				$sql .= "image_large      = '". $i_val['image_large']. "',";
				$sql .= "image_extralarge = '". $i_val['image_extralarge']. "'";
				if (strcasecmp($i_val['name'], $search) == 0)
				{
					$sql .= ", sort         = " .\DB::expr('sort'). " + 1 ";
				}
			}
			$res = \DB::query($sql)->execute();
		}
		return true;
	}


	public function update_values(array $arr_values, $id)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		return $this->update($arr_values, array('id' => $id));
	}

	public function get_artist_list($arr_where=array(), $arr_columns=array(), $arr_order=array())
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$artist_dto = ArtistDto::get_instance();
		$limit = $artist_dto->get_limit();
		$page = $artist_dto->get_page();
		$offset = 0;
		$limit = null;

		if (empty($limit) or $limit > 1000)
		{
			$limit = 100;
		}

		if (isset($page))
		{
			$offset = ($page - 1) * $limit;
		}

		return $this->search_limit($arr_where, $arr_columns, $arr_order, $offset, $limit);
	}

	public function get_artist_by_id($id)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_where = array('id' => $id);
		return $this->search($arr_where);
	}

	public function get_just_artist_list($name)
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$page  = $artist_dto->get_page();
		$limit = $artist_dto->get_limit();
		if (empty($limit) or $limit > 1000)
		{
			$limit = 100;
		}
		$name = trim($name);
		$query = \DB::select();
		$query->from($this->_table_name);
		$query->and_where_open();
		$query->or_where('name', $name);
		$query->and_where_close();
		$query->where('is_deleted', 0);
		if (isset($page))
		{
			$offset = ($page - 1) * $limit;
			$query->offset($offset);
		}
		$query->limit($limit);

		return $query->as_object()->execute();
	}


	/**
	 * 前方検索（インデックスが効く）
	 * @param unknown $name
	 */
	public function get_same_artist_list($name, array $arr_order=array(), $is_full_text=false)
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();

		$page = $artist_dto->get_page();
		$limit = $artist_dto->get_limit();
		if (empty($limit) or $limit > 100)
		{
			$limit = 100;
		}

		$query = \DB::select();
		$query->from($this->_table_name);
		$query->and_where_open();
		$val = trim($name);
		$query->or_where('name', 'like', $val. '%');
		$query->or_where('name', 'like', 'the '. $val. '%');
		$query->or_where('kana', 'like', $val. '%');
		$query->or_where('english', 'like', $val. '%');
		$query->or_where('search', 'like', $val. '%');
		$query->or_where('same_names', 'like', $val. '%');
		if ($is_full_text)
		{
			$query->or_where('same_names', 'like', '%'. $val. '%');
		}
		$query->and_where_close();
		$query->where('is_deleted', 0);
		if (isset($page))
		{
			$offset = ($page - 1) * $limit;
			$query->offset($offset);
		}
		$query->limit($limit);

		if (isset($arr_order))
		{
			foreach ($arr_order as $key => $val)
			{
				$query->order_by($key, $val);
			}
		}

		return $query->as_object()->execute();
	}

	/**
	 * 前方検索（インデックスが効く）
	 * @param unknown $name
	 */
	public function get_arr_same_artist_list(array $name, array $arr_order=array(), $is_full_text=false)
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$page = $artist_dto->get_page();
		$limit = $artist_dto->get_limit();
		if (empty($limit) or $limit > 100)
		{
			$limit = 100;
		}

		$query = \DB::select();
		$query->from($this->_table_name);
		$query->and_where_open();
		if (is_array($name))
		{
			foreach ($name as $val)
			{
				$val = trim($val);
				$query->or_where('name'   , 'like', $val. '%');
				$query->or_where('name'   , 'like', 'the '. $val. '%');
				$query->or_where('kana'   , 'like', $val. '%');
				$query->or_where('english', 'like', $val. '%');
				$query->or_where('search' , 'like', $val. '%');
				if ($is_full_text)
				{
					$query->or_where('same_names', 'like', '%'. $val. '%');
				}
			}
		}
		else
		{
			$val = trim($name);
			$query->or_where('name', 'like', $val. '%');
			$query->or_where('name', 'like', 'the '. $val. '%');
			$query->or_where('kana', 'like', $val. '%');
			$query->or_where('english', 'like', $val. '%');
			$query->or_where('search', 'like', $val. '%');
			if ($is_full_text)
			{
				$query->or_where('same_names', 'like', '%'. $val. '%');
			}
		}

		$query->and_where_close();
		$query->where('is_deleted', '0');

		if ($artist_dto->get_available_play() == true)
		{
			$query->where('mbid_itunes', '>', 0);
		}

		if (isset($page))
		{
			$offset = ($page - 1) * $limit;
			$query->offset($offset);
		}

		$query->limit($limit);
		if (isset($arr_order))
		{
			foreach ($arr_order as $key => $val)
			{
				$query->order_by($key, $val);
			}
		}
		return $query->as_object()->execute();
	}

}