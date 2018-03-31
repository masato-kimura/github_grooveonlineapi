<?php
namespace main\model\dao;

use main\model\dto\TrackDto;
use main\model\dto\AlbumDto;
use main\model\dto\ArtistDto;
use Fuel\Core\Date;


/**
 * @throws \Exception
 *  1001 正常
 *  9001
 * @author masato
 *
 */
class TrackDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	const DELIMITER = ",:@"; // same_namesカラム内区切り文字

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'mst_track';
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
		\Log::debug(__CLASS__. '::'. __FUNCTION__);

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
				$sql .= "genre_itunes     = '". htmlentities($i_val['genre_itunes'], ENT_QUOTES, mb_internal_encoding()). "',";
				$sql .= "duration         = '". $i_val['duration']. "',";
				$sql .= "preview_itunes   = '". htmlentities($i_val['preview_itunes'], ENT_QUOTES, mb_internal_encoding()). "',";
				$sql .= "number           = '". $i_val['number']. "',";
				$sql .= "updated_at       = '". $now. "'";
			}
			$res = \DB::query($sql)->execute();
		}

		return true;
	}


	public function update_values(array $arr_values, array $arr_where)
	{
		\Log::debug(__CLASS__. '::'. __FUNCTION__);

		foreach ($arr_where as $i => $val)
		{
			$this->update($arr_values, array($i => $val));
		}
		return true;
	}

	public function is_empty_same_artist_same_album_by_title($track_name)
	{
		\Log::debug(__CLASS__. '::'. __FUNCTION__);

		$track_dto = TrackDto::get_instance();
		$album_dto = AlbumDto::get_instance();

		$album_id = $album_dto->get_album_id();
		$album_name = $album_dto->get_album_name();

		$query = \DB::select(array('t.id', 'id'));
		$query->from(array($this->_table_name,'t'));
		$query->join(array('mst_album','a'));
		$query->on('t.album_id', '=', 'a.id');

		if (empty($album_id))
		{
			if ( ! empty($album_name)) // album_idが空であり、アルバム名が指定されている時
			{
				$query->where('a.name', $album_name);
			}
		}
		else
		{
			$query->where('t.album_id', $album_id);// album_idが存在するときはアルバムを確定する
		}

		if (! empty($track_name))// track_nameは必須にしたいなー
		{
			$query->where('t.name', $track_name);
		}

		$query->where('t.is_deleted', '0');
		$query->where('a.is_deleted', '0');

		$result = $query->execute()->current();

		if (empty($result))
		{
			return true;
		}
		else
		{
			$track_dto->set_track_id($result['id']);
			return false;
		}
	}

	public function is_empty_same_artist_same_album_by_track_album($track_name)
	{
		\Log::debug(__CLASS__. '::'. __FUNCTION__);

		$track_dto = TrackDto::get_instance();

		$album_id   = $track_dto->get_album_id(); // lastfmからの取得時はnull
		$album_name = $track_dto->get_album_name();
		$track_mbid_itunes = $track_dto->get_mbid_itunes();
		$track_mbid_lastfm = $track_dto->get_mbid_lastfm();

		$query = \DB::select('t.id', 't.mbid_itunes', 't.mbid_lastfm', 't.image_url');
		$query->from(array($this->_table_name,'t')); // mst_track
		$query->join(array('mst_album','a'));        // mst_album joined
		$query->on('t.album_id', '=', 'a.id');

		if (empty($album_id))
		{
			if ( ! empty($album_name)) // album_idが空であり、アルバム名が指定されている時
			{
				$query->where('a.name', $album_name);
			}
		}
		else
		{
			$query->where('t.album_id', $album_id);// album_idが存在するときはアルバムを確定する
		}

		if ( ! empty($track_name))// track_nameは必須にしたいなー
		{
			$query->where('t.name', $track_name);
		}

		$query->where('t.is_deleted', '0');
		$query->where('a.is_deleted', '0');

		if ( ! empty($track_mbid_itunes))
		{
			$query->or_where('t.mbid_itunes', $track_mbid_itunes);
		}

		if ( ! empty($track_mbid_lastfm))
		{
			$query->or_where('t.mbid_lastfm', $track_mbid_lastfm);
		}

		$result = $query->execute()->current();

		if (empty($result))
		{
			return true;
		}
		else
		{
			$track_dto->set_track_id($result['id']);
			return $result;
		}
	}


	public function get_track_list()
	{
		\Log::debug(__METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$album_dto  = AlbumDto::get_instance();
		$track_dto  = TrackDto::get_instance();

		$track_name = $track_dto->get_track_name();
		$artist_id  = $artist_dto->get_artist_id();
		$album_id   = $album_dto->get_album_id();
		$limit      = $track_dto->get_limit();
		$page       = $track_dto->get_page();
		$offset     = 0;

		$arr_columns = array(
			'a.id',
			'a.artist_id',
			'a.album_id',
			'a.name',
			'a.kana',
			'a.english',
			'a.same_names',
			'a.mbid_api',
			'a.mbid_itunes',
			'a.mbid_lastfm',
			'a.url_itunes',
			'a.url_lastfm',
			'a.image_url',
			'a.image_small',
			'a.image_medium',
			'a.image_large',
			'a.image_extralarge',
			'a.content',
			'a.number',
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'a'));

		if ( ! empty($artist_id))
		{
			$query->where('artist_id', $artist_id);
		}

		if ( ! empty($album_id))
		{
			$query->where('album_id', $album_id);
		}

		if ( ! empty($track_name))
		{
			$query->and_where_open();
			$query->or_where('a.name',       'like', '%'. trim($track_name). '%');
			$query->or_where('a.kana',       'like', '%'. trim($track_name). '%');
			$query->or_where('a.english',    'like', '%'. trim($track_name). '%');
			$query->or_where('a.same_names', 'like', '%'. trim($track_name). '%');
			$query->and_where_close();
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

		$query->order_by('number', 'ASC');

		$result = $query->execute()->as_array();

		return $result;
	}


	/**
	 * アルバムIDから収録トラックリストを取得する
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get_track_list_by_album_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$album_dto = AlbumDto::get_instance();
		$arr_where = array(
			'album_id' => $album_dto->get_album_id()
		);
		$arr_result = $this->search($arr_where, array(), array('number'=>'ASC'));

		return $arr_result;
	}


	/**
	 * 検索ワードから収録トラックリストを取得する
	 * @return Ambigous <multitype:, unknown>
	 */
	public function search_track_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$track_dto  = TrackDto::get_instance();

		$query = \DB::select();
		$query->from($this->_table_name);
		$query->where('artist_id', $artist_dto->get_artist_id());
		$query->where('name', 'like', '%'. $track_dto->get_track_name(). '%');
		$query->where('is_deleted', '0');
		$query->limit(50);

		$arr_result = $query->as_object()->execute()->as_array();

		return $arr_result;
	}



	public function get_track_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dto  = TrackDto::get_instance();

		$arr_columns = array(
			't.id',
			't.artist_id',
			array('a.name', 'artist_name'),
			array('a.image_small', 'artist_image_small'),
			array('a.image_medium', 'artist_image_medium'),
			array('a.image_large', 'artist_image_large'),
			array('a.image_extralarge', 'artist_image_extralarge'),
			't.album_id',
			array('al.name', 'album_name'),
			't.name',
			't.kana',
			't.english',
			't.mbid_itunes',
			't.mbid_lastfm',
			't.url_itunes',
			't.url_lastfm',
			't.image_small',
			't.image_medium',
			't.image_large',
			't.image_extralarge',
			't.number',
			't.content',
			't.release_itunes',
			't.release_lastfm',
			't.genre_itunes',
			't.duration',
			't.preview_itunes',
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 't'));
		$query->join(array('mst_artist', 'a'));
		$query->on('t.artist_id', '=', 'a.id');
		$query->join(array('mst_album', 'al'));
		$query->on('t.album_id', '=', 'al.id');
		$query->where('t.id', '=', $track_dto->get_track_id());
		$query->where('t.is_deleted', '=', '0');
		$query->where('a.is_deleted', '=', '0');
		$query->where('al.is_deleted', '=', '0');
		$result = $query->execute()->current();
		return $result;
	}
}