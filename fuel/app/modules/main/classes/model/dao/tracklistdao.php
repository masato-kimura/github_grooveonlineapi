<?php
namespace main\model\dao;

use main\model\dto\TracklistDto;
use main\model\dto\UserDto;
use main\model\dto\ArtistDto;
/**
 * @throws \Exception
 *  1001 正常
 *  9001
 * @author masato
 *
 */
class TracklistDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_tracklist';
	}

	public function set_list_title()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$artist_dto    = ArtistDto::get_instance();
		$user_dto      = UserDto::get_instance();
		$arr_params = array(
			'title'     => $tracklist_dto->get_title(),
			'user_name' => $tracklist_dto->get_user_name(),
			'user_id'   => $user_dto->get_user_id(),
			'artist_id' => $artist_dto->get_artist_id(),
		);
		return $this->save($arr_params);
	}

	public function update_list_title()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$artist_dto    = ArtistDto::get_instance();
		$user_dto      = UserDto::get_instance();
		$arr_params = array(
				'title'     => $tracklist_dto->get_title(),
				'user_name' => $tracklist_dto->get_user_name(),
				'user_id'   => $user_dto->get_user_id(),
				'artist_id' => $artist_dto->get_artist_id(),
		);
		$arr_where = array(
				'id' => $tracklist_dto->get_track_id(),
		);
		return $this->update($arr_params, $arr_where);
	}

	public function delete_list_title()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$user_dto      = UserDto::get_instance();

		$arr_where = array(
			'id'      => $tracklist_dto->get_tracklist_id(),
			'user_id' => $user_dto->get_user_id(),
		);

		return $this->delete(array(), $arr_where, false);
	}

	public function get_list_title_count()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto    = ArtistDto::get_instance();
		$tracklist_dto = TracklistDto::get_instance();

		$artist_id = $artist_dto->get_artist_id();
		$user_id   = $tracklist_dto->get_user_id();
		$offset    = $tracklist_dto->get_offset();
		$limit     = $tracklist_dto->get_limit();

		$arr_columns = array(
				\DB::expr('count(distinct t.id)'), 'cnt'
		);

		/**
		 * t : trn_tracklist
		 * d : trn_tracklist_detail
		 * u : trn_user
		 * a : mst_artist
		 */
		$query = \DB::select($arr_columns);
		$query->from(array($this->_table_name, 't'));
		$query->join(array('trn_tracklist_detail', 'd'));
		$query->on('d.tracklist_id', '=', 't.id');
		$query->join(array('trn_user', 'u'), 'left');
		$query->on('t.user_id', '=', 'u.id');
		$query->on('u.is_deleted', '=', \DB::expr('0'));
		$query->join(array('mst_artist', 'a'), 'left');
		$query->on('t.artist_id', '=', 'a.id');
		$query->on('a.is_deleted', '=', \DB::expr('0'));
		$query->where('t.is_deleted', '=', '0');
		if ($artist_id)
		{
			$query->where('d.artist_id', '=', $artist_id);
		}

		if ($user_id)
		{
			$query->where('t.user_id', '=', $user_id);
		}

		return $query->as_object()->execute()->current();
	}


	public function get_list_title()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto    = ArtistDto::get_instance();
		$tracklist_dto = TracklistDto::get_instance();

		$artist_id = $artist_dto->get_artist_id();
		$user_id   = $tracklist_dto->get_user_id();
		$offset    = $tracklist_dto->get_offset();
		if ($offset < 0)
		{
			$offset = 0;
		}
		$limit     = $tracklist_dto->get_limit();

		/**
		 * t  : trn_tracklist
		 * d  : trn_tracklist_detail
		 * u  : trn_user
		 * a  : mst_artist(header)
		 * ad : mst_artist(detail)
		 * tr : mst_track
		 */
		$arr_columns = array(
			't.id',
			't.title',
			't.artist_id',
			array('a.name', 'artist_name'),
			't.user_name',
			't.user_id',
			array('u.user_name', 'user_login_name'),
			't.created_at',
			array('tr.name', 'track_name'),
			array('ad.name', 'track_artist_name'),
			array('ad.image_medium', 'track_artist_image'),
		);
		$arr_sub_columns = array(
			'sub_t.id',
			'sub_t.title',
			'sub_t.artist_id',
			'sub_t.user_name',
			'sub_t.user_id',
			'sub_t.created_at'
		);
		$sub_query = \DB::select_array($arr_sub_columns);
		$sub_query->from(array($this->_table_name, 'sub_t'));
		$sub_query->join(array('trn_tracklist_detail', 'sub_d'));
		$sub_query->on('sub_t.id', '=', 'sub_d.tracklist_id');
		$sub_query->where('sub_t.is_deleted', '=', '0');
		($artist_id)? $sub_query->where('sub_d.artist_id', '=', $artist_id): null;
		($user_id)  ? $sub_query->where('sub_t.user_id', '=', $user_id): null;
		$sub_query->group_by('sub_t.id');
		$sub_query->order_by('sub_t.id', 'DESC');
		($offset)   ? $sub_query->offset($offset): null;
		($limit)    ? $sub_query->limit($limit): $sub_query->limit(100);
		$sub_query_sql = "(". $sub_query->compile(). ")";

		$query = \DB::select_array($arr_columns);
		$query->from(array(\DB::expr($sub_query_sql), 't'));
		$query->join(array('trn_tracklist_detail', 'd'));
		$query->on('d.tracklist_id', '=', 't.id');
		$query->join(array('mst_artist', 'ad'));
		$query->on('d.artist_id', '=', 'ad.id');
		$query->join(array('mst_track', 'tr'));
		$query->on('tr.id', '=', 'd.track_id');
		$query->join(array('trn_user', 'u'), 'left');
		$query->on('t.user_id', '=', 'u.id');
		$query->on('u.is_deleted', '=', \DB::expr('0'));
		$query->join(array('mst_artist', 'a'), 'left');
		$query->on('t.artist_id', '=', 'a.id');
		$query->on('a.is_deleted', '=', \DB::expr('0'));
		$query->order_by('t.id', 'DESC');

		return $query->as_object()->execute()->as_array();
	}


	public function get_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$artist_dto    = ArtistDto::get_instance();
		$tracklist_id = $tracklist_dto->get_tracklist_id();

		/**
		 * t : trn_tracklist
		 * d : trn_tracklist_detail
		 * at : mst_artist
		 * ad : mst_artist
		 * al : mst_album
		 * tr: mst_track
		 * u : trn_user
		*/
		$arr_columns = array(
			't.id',
			't.title',
			't.artist_id',
			array('at.name',             'artist_name'),
			array('at.image_medium',     'artist_image_medium'),
			array('at.image_large',      'artist_image_large'),
			array('at.image_extralarge', 'artist_image_extralarge'),
			array('at.mbid_itunes'     , 'artist_mbid_itunes'),
			array('at.mbid_lastfm'     , 'artist_mbid_lastfm'),
			array('at.url_itunes'      , 'artist_url_itunes'),
			array('at.url_lastfm'      , 'artist_url_lastfm'),
			array('d.album_id'         , 'album_id'),
			array('al.name'            , 'album_name'),
			't.user_name',
			't.user_id',
			't.created_at',
			't.updated_at',
			array('ad.image_medium',     'track_artist_image_medium'),
			array('ad.image_large',      'track_artist_image_large'),
			array('ad.image_extralarge', 'track_artist_image_extralarge'),
			array('ad.id',               'track_artist_id'),
			array('ad.name',             'track_artist_name'),
			array('tr.id',               'track_id'),
			array('tr.name',             'track_name'),
			'd.sort',
			'tr.mbid_itunes',
			'tr.preview_itunes',
			array('u.user_name',         'user_login_name'),
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 't'));
		$query->join(array('mst_artist', 'at'), 'left');
		$query->on('t.artist_id', '=', 'at.id');
		$query->join(array('trn_tracklist_detail', 'd'));
		$query->on('t.id', '=', 'd.tracklist_id');
		$query->join(array('mst_track', 'tr'));
		$query->on('d.track_id', '=', 'tr.id');
		$query->join(array('mst_artist', 'ad'));
		$query->on('d.artist_id', '=', 'ad.id');
		$query->join(array('trn_user', 'u'), 'left');
		$query->on('t.user_id', '=', 'u.id');
		$query->on('u.is_deleted', '=', \DB::expr('0'));
		$query->join(array('mst_album', 'al'));
		$query->on('al.id', '=', 'd.album_id');
		$query->where('t.id', '=', $tracklist_id);
		$query->where('t.is_deleted', '=', '0');
		$query->where('d.is_deleted', '=', '0');
		$query->where('ad.is_deleted', '=', '0');
		$query->where('tr.is_deleted', '=', '0');
		$query->order_by('d.sort', 'ASC');

		return $query->as_object()->execute()->as_array();
	}

}