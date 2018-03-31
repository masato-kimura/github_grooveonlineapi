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
class SearchArtistDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_search_artist';
	}

	public function get_search_artist(array $arr_where)
	{
		return $this->search_one($arr_where);
	}

	public function get_history_new($offset, $limit)
	{
		$arr_columns = array(
				array('a.id', 'artist_id'),
				array('a.name', 'artist_name'),
				array('a.image_url', 'artist_image'),
		);
		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 's'));
		$query->join(array('mst_artist', 'a'));
		$query->or_on('s.exchange_word', '=', 'a.name');
		$query->or_on('s.word', '=', 'a.name');
		$query->where('s.is_deleted', '=', '0');
		$query->where('a.is_deleted', '=', '0');
		$query->order_by('s.updated_at', 'DESC');
		$query->offset($offset);
		$query->limit($limit * 100);

		return $query->execute()->as_array();
	}

	public function set_lock(array $arr_where)
	{
		$query = \DB::select();
		$query->from($this->_table_name);
		$query->where('word', '=', $arr_where['word']);
		$sql = $query->compile();
		$sql .= " FOR UPDATE";
		return \DB::query($sql)->execute()->current();
	}


	/**
	 *
	 * @param array $arr_profile
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function set_values(array $arr_request)
	{
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
		list($key, $count) = $this->save_multi($arr_request, $is_ignore);
		if (empty($count))
		{
			return false;
		}
		return array($key, $count);
	}


	public function update_values(array $arr_values, $id)
	{
		return $this->update($arr_values, array('id' => $id));
	}


	public function get_artist_list($arr_where=array(), $arr_columns=array(), $arr_order=array())
	{
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
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$arr_where = array('id' => $id);
		return $this->search($arr_where);
	}


	public function get_just_artist_list($name)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

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
		if (empty($limit) or $limit > 1000)
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
				$query->or_where('name', 'like', $val. '%');
				$query->or_where('name', 'like', 'the '. $val. '%');
				$query->or_where('kana', 'like', $val. '%');
				$query->or_where('english', 'like', $val. '%');
				//$query->or_where('search', 'like', $val. '%');
				if ($is_full_text)
				{
					$query->or_where('same_names', 'like', $val. '%');
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
				$query->or_where('same_names', 'like', $val. '%');
			}
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

}