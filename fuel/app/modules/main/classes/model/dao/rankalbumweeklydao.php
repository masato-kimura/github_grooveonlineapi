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
class RankAlbumWeeklyDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_rank_album_weekly';
	}


	public function get_rank($from)
	{
		\Log::debug('[start]'. __METHOD__);

		$aggregate_from = \Date::forge(strtotime($from))->format('%Y-%m-%d 00:00:00');
		$arr_columns = array(
				'r.album_id',
				array('l.name', 'album_name'),
				'l.artist_id',
				array('a.name', 'artist_name'),
				'l.mbid_itunes',
				'l.mbid_lastfm',
				'l.image_medium',
				'l.image_large',
				array('l.genre_itunes', 'genre'),
				'r.aggregate',
				'r.rank',
		);
		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('mst_album', 'l'));
		$query->on('r.album_id', '=', 'l.id');
		$query->join(array('mst_artist', 'a'));
		$query->on('l.artist_id', '=', 'a.id');
		$query->where('r.aggregate_from', '=', $aggregate_from);
		$query->where('r.is_deleted', '0');
		$query->where('l.is_deleted', '0');
		$query->where('a.is_deleted', '0');
		$arr_result = $query->execute()->as_array();

		return $arr_result;
	}


	public function reset_rank_track($from)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_values = array(
				'is_deleted' => '1',
		);
		$arr_where  = array(
				'aggregate_from' => $from,
				'is_deleted' => '0'
		);

		return $this->update($arr_values, $arr_where);
	}


	public function insert_rank_track(array $arr_values)
	{
		\Log::debug('[start]'. __METHOD__);

		$this->save_multi($arr_values);

		return true;
	}
}