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
class RankTrackWeeklyDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_rank_track_weekly';
	}


	public function get_rank($from)
	{
		\Log::debug('[start]'. __METHOD__);

		$aggregate_from = \Date::forge(strtotime($from))->format('%Y-%m-%d 00:00:00');
		$arr_columns = array(
			'r.track_id',
			array('t.name', 'track_name'),
			't.artist_id',
			array('a.name', 'artist_name'),
			't.mbid_itunes',
			't.mbid_lastfm',
			't.image_medium',
			't.image_large',
			array('t.genre_itunes', 'genre'),
			't.preview_itunes',
			'r.aggregate',
			'r.rank',
		);
		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('mst_track', 't'));
		$query->on('r.track_id', '=', 't.id');
		$query->join(array('mst_artist', 'a'));
		$query->on('t.artist_id', '=', 'a.id');
		$query->where('r.aggregate_from', '=', $aggregate_from);
		$query->where('r.is_deleted', '0');
		$query->where('t.is_deleted', '0');
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