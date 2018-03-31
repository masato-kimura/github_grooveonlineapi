<?php
namespace main\model\dao;

use main\model\dto\TrackDto;
use main\model\dto\AlbumDto;
use main\model\dto\ArtistDto;


/**
 * @throws \Exception
 *  1001 正常
 *  9001
 * @author masato
 *
 */
class AlbumDao extends MySqlDao
{
	protected $_table_name;

	const DELIMITER = ",:@"; // same_namesカラム内区切り文字

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'mst_album';
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
	public function set_insert_update_values(array $arr_request, $is_ignore=false)
	{
		$now = \Date::forge()->format('%Y-%m-%d %H:%M:%S');
		foreach ($arr_request as $i => $i_val)
		{
			unset($i_val['id']);
			if (empty($i_val['image_url']))
			{
				$i_val['sort'] = 0;
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
				$sql .= "mbid_itunes      = '". htmlentities($i_val['mbid_itunes'], ENT_QUOTES, mb_internal_encoding()). "',";
				$sql .= "mbid_lastfm      = '". htmlentities($i_val['mbid_lastfm'], ENT_QUOTES, mb_internal_encoding()). "',";
				$sql .= "url_itunes       = '". htmlentities($i_val['url_itunes'], ENT_QUOTES, mb_internal_encoding()). "',";
				$sql .= "url_lastfm       = '". htmlentities($i_val['url_lastfm'], ENT_QUOTES, mb_internal_encoding()). "',";
				$sql .= "image_url        = '". $i_val['image_url']. "',";
				$sql .= "image_small      = '". $i_val['image_small']. "',";
				$sql .= "image_medium     = '". $i_val['image_medium']. "',";
				$sql .= "image_large      = '". $i_val['image_large']. "',";
				$sql .= "image_extralarge = '". $i_val['image_extralarge']. "',";
				$sql .= "release_itunes   = '". htmlentities($i_val['release_itunes'], ENT_QUOTES, mb_internal_encoding()). "',";
				$sql .= "copyright_itunes = '". htmlentities($i_val['copyright_itunes'], ENT_QUOTES, mb_internal_encoding()). "',";
				$sql .= "genre_itunes     = '". htmlentities($i_val['genre_itunes'], ENT_QUOTES, mb_internal_encoding()). "',";
				$sql .= "sort             = '". $i_val['sort']. "',";
				$sql .= "api_type         = '". $i_val['api_type']. "',";
				$sql .= "updated_at       = '". $now. "'";
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


	/**
	 * 同じアーティストで同名のアルバムが登録されていた場合はfalseを返す
	 *
	 * @return boolean
	 */
	public function is_empty_same_artist_album_by_title()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$album_dto  = AlbumDto::get_instance();

		$result = $this->search_one(
					array(
						'artist_id' => $album_dto->get_artist_id(),
						'name' => trim($album_dto->get_album_name()),
					),
					array(
						'id'
					)
				);

		if (empty($result))
		{
			return true;
		}
		else
		{
			$album_dto->set_album_id($result->id);
			return false;
		}
	}

	/**
	 * 同じアーティストで同名のアルバムが登録されていた場合はfalseを返す
	 *
	 * @return boolean
	 */
	public function is_empty_same_artist_album_by_track_album()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$artist_dto = ArtistDto::get_instance();
		$track_dto = TrackDto::get_instance();
	\Log::info("アーティストID:". $artist_dto->get_artist_id());
	\Log::info($track_dto->get_album_name());

		$result = $this->search_one(
					array(
						'artist_id' => $track_dto->get_artist_id(),
						'name' => trim($track_dto->get_album_name()),
					),
					array(
						'id'
					)
				);

		if (empty($result))
		{
			return true;
		}
		else
		{
			$track_dto->set_album_id($result->id);
			return false;
		}
	}


	public function get_album($arr_where=array(), $arr_columns=array(), $arr_order=array())
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$album_dto = AlbumDto::get_instance();
		$limit  = $album_dto->get_limit();
		$page   = $album_dto->get_page();
		$offset = 0;
		$limit  = null;

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


	public function search_album_name()
	{
		\Log::info('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$album_dto  = AlbumDto::get_instance();

		$query = \DB::select();
		$query->from($this->_table_name);
		$query->where('is_deleted', '0');
		$query->where('artist_id',  $artist_dto->get_artist_id());
		$query->where('name', 'like', '%'. $album_dto->get_album_name().'%');
		$query->limit(50);
		$arr_result = $query->as_object()->execute()->as_array();

		return $arr_result;
	}


	public function get_album_list_by_artist_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto  = ArtistDto::get_instance();
		$album_dto   = AlbumDto::get_instance();

		$artist_id  = $artist_dto->get_artist_id();
		$limit      = $album_dto->get_limit();
		$page       = $album_dto->get_page();
		$offset     = 0;
		$sort       = $album_dto->get_sort();
		$arr_columns = array(
			'id',
			'artist_id',
			'name',
			'kana',
			'english',
			'same_names',
			'mbid_itunes',
			'mbid_lastfm',
			'url_itunes',
			'url_lastfm',
			'image_url',
			'image_small',
			'image_medium',
			'image_large',
			'image_extralarge',
			'release_itunes',
			'copyright_itunes',
			'genre_itunes',
			'sort',
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'a'));
		if ( ! empty($artist_id))
		{
			$query->where('artist_id', $artist_id);
		}

		if (empty($limit) or $limit > 100)
		{
			$limit = 100;
		}
		$query->limit($limit);

		if (isset($page))
		{
			$offset = ($page - 1) * $limit;
		}
		$query->offset($offset);

		if ( ! empty($sort))
		{
			foreach ($sort as $i => $val)
			{
				$query->order_by($i, $val);
			}
		}
\Log::info($query->compile());
		$result = $query->execute()->as_array();

		return $result;
	}


	public function get_album_info_current()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$album_dto = AlbumDto::get_instance();
		$name_api = $album_dto->get_name_api();
		$mbid_api = $album_dto->get_mbid_api();
		$url_api = $album_dto->get_url_api();

		$query = \DB::select();
		$query->from($this->_table_name);
		$query->and_where_open();
		$query->or_where('name', '=', $name_api);
		$query->or_where('name_api', '=', $name_api);
		$query->and_where_close();
		if (isset($mbid_api))
		{
			$query->where('mbid_api', '=', $mbid_api);
		}
		if (isset($url_api))
		{
			$query->where('url_api', '=', $url_api);
		}

		$query->where('is_deleted', 0);

		$arr_result = $query->execute()->current();

		return $arr_result;
	}


}