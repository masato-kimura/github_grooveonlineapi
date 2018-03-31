<?php
namespace main\model\dao;

use main\model\dto\TracklistDto;
/**
 * @throws \Exception
 *  1001 正常
 *  9001
 * @author masato
 *
 */
class TracklistDetailDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_tracklist_detail';
	}

	public function set_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$arr_values = array();

		foreach ($tracklist_dto->get_arr_list() as $i => $val)
		{
			$arr_values[$i] = array(
				'tracklist_id' => $tracklist_dto->get_tracklist_id(),
				'track_id'     => $val->track_id,
				'artist_id'    => $val->artist_id,
				'album_id'     => $val->album_id,
				'sort'         => ++$i,
			);
		}

		$this->save_multi($arr_values);

		return true;
	}

	public function delete_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$arr_where = array('tracklist_id' => $tracklist_dto->get_tracklist_id());

		return $this->delete(array(), $arr_where, false);
	}

	public function get_detail()
	{
		\Log::debug('[start]'. __METHOD__);

	}
}