<?php
namespace main\domain\service;

use main\model\dto\RankDto;
use main\model\dao\CoolDao;
use main\model\dao\ReviewMusicTrackDao;
use main\model\dao\ReviewMusicAlbumDao;
use Fuel\Core\Date;
use main\model\dao\RankTrackWeeklyDao;
use main\model\dao\RankAlbumWeeklyDao;

class RankService extends Service
{
	public static function validation_for_week()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = \Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('offset', 'offset');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('limit', 'limit');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}

	/**
	 * API使用時
	 * @param unknown $about
	 * @return boolean
	 */
	public static function set_dto_for_week($about)
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();

		$rank_dto->set_about($about);

		if (property_exists(static::$_obj_request, 'offset'))
		{
			$rank_dto->set_offset(trim(static::$_obj_request->offset));
		}
		if (property_exists(static::$_obj_request, 'limit'))
		{
			$rank_dto->set_limit(trim(static::$_obj_request->limit));
		}
		if (property_exists(static::$_obj_request, 'from'))
		{
			$from = static::$_obj_request->from;
			if ( ! empty($from))
			{
				$rank_dto->set_aggregate_from(trim(static::$_obj_request->from));
			}
		}
		if (empty($from))
		{
			$w = \Date::forge()->format('%w');
			if ($w == 0)
			{
				$diff = 13;
			}
			else
			{
				$diff = $w + 6;
			}

			$rank_dto->set_aggregate_from(\Date::forge(time() - 60 * 60 * 24 * $diff)->format('%Y-%m-%d'));
		}

		return true;
	}


	public static function set_dto_for_weekly_track($offset=0, $limit=100)
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();

		$rank_dto->set_about('track');
		$rank_dto->set_offset(trim($offset));
		$rank_dto->set_limit(trim($limit));
		$w = \Date::forge()->format('%w');
		if ($w == 0)
		{
			$diff = 13;
		}
		else
		{
			$diff = $w + 6;
		}
		// 一週間前の月曜日
		$rank_dto->set_aggregate_from(\Date::forge(time() - 60 * 60 * 24 * $diff)->format('%Y-%m-%d'));
		$rank_dto->set_aggregate_to(\Date::forge(time() - (60 * 60 * 24 * ($diff - 6)))->format('%Y-%m-%d 23:59:59'));

		return true;
	}


	public static function set_dto_for_weekly_album($offset=0, $limit=100)
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();
		$rank_dto->set_about('album');
		$rank_dto->set_offset(trim($offset));
		$rank_dto->set_limit(trim($limit));
		$w = \Date::forge()->format('%w');
		if ($w == 0)
		{
			$diff = 13;
		}
		else
		{
			$diff = $w + 6;
		}
		// 一週間前の月曜日
		$rank_dto->set_aggregate_from(\Date::forge(time() - 60 * 60 * 24 * $diff)->format('%Y-%m-%d'));
		$rank_dto->set_aggregate_to(\Date::forge(time() - (60 * 60 * 24 * ($diff - 6)))->format('%Y-%m-%d 23:59:59'));

		return true;
	}

	/**
	 * by API
	 * @return boolean
	 */
	public static function get_by_weekly_rank_table()
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();
		switch ($rank_dto->get_about())
		{
			case 'track':
				static::_get_by_weekly_rank_track_table();
				break;
			case 'album':
				static::_get_by_weekly_rank_album_table();
				break;
			default:
		}


		return true;
	}

	/**
	 * by API
	 * @return boolean
	 */
	public static function _get_by_weekly_rank_track_table()
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_track_dao = new RankTrackWeeklyDao();
		$rank_dto = RankDto::get_instance();
		$arr_result = $rank_track_dao->get_rank($rank_dto->get_aggregate_from());
		$rank_dto->set_arr_list($arr_result);

		return true;
	}

	/**
	 * by API
	 * @return boolean
	 */
	public static function _get_by_weekly_rank_album_table()
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_album_dao = new RankAlbumWeeklyDao();
		$rank_dto = RankDto::get_instance();
		$arr_result = $rank_album_dao->get_rank($rank_dto->get_aggregate_from());
		$rank_dto->set_arr_list($arr_result);

		return true;
	}


	/**
	 * by Batch
	 * @return boolean
	 */
	public static function get_rank()
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();
		switch ($rank_dto->get_about())
		{
			case 'track':
				static::_get_track_rank();
				break;
			case 'album':
				static::_get_album_rank();
				break;
			default:
		}

		return true;
	}


	public static function set_weekly_rank()
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();

		switch ($rank_dto->get_about())
		{
			case 'track':
				static::_set_weekly_rank_track();
				break;
			case 'album':
				static::_set_weekly_rank_album();
				break;
		}

		return true;
	}


	private static function _set_weekly_rank_track()
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();
		$arr_insert_data = array();
		foreach ($rank_dto->get_arr_list() as $i => $val)
		{
			$arr_insert_data[$i]['rank']           = $val['rank'];
			$arr_insert_data[$i]['track_id']       = $val['track_id'];
			$arr_insert_data[$i]['aggregate_from'] = $rank_dto->get_aggregate_from();
			$arr_insert_data[$i]['aggregate_to']   = $rank_dto->get_aggregate_to();
			$arr_insert_data[$i]['aggregate']      = $val['aggregate'];
		}

		$rank_track_weekly_dao = new RankTrackWeeklyDao();
		$rank_track_weekly_dao->start_transaction();
		$rank_track_weekly_dao->reset_rank_track($rank_dto->get_aggregate_from());
		$rank_track_weekly_dao->insert_rank_track($arr_insert_data);
		$rank_track_weekly_dao->commit_transaction();

		return true;
	}


	private static function _set_weekly_rank_album()
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();
		$arr_insert_data = array();
		foreach ($rank_dto->get_arr_list() as $i => $val)
		{
			$arr_insert_data[$i]['rank']           = $val['rank'];
			$arr_insert_data[$i]['album_id']       = $val['album_id'];
			$arr_insert_data[$i]['aggregate_from'] = $rank_dto->get_aggregate_from();
			$arr_insert_data[$i]['aggregate_to']   = $rank_dto->get_aggregate_to();
			$arr_insert_data[$i]['aggregate']      = $val['aggregate'];
		}

		$rank_album_weekly_dao = new RankAlbumWeeklyDao();
		$rank_album_weekly_dao->start_transaction();
		$rank_album_weekly_dao->reset_rank_track($rank_dto->get_aggregate_from());
		$rank_album_weekly_dao->insert_rank_track($arr_insert_data);
		$rank_album_weekly_dao->commit_transaction();

		return true;
	}

	/**
	 * by Batch
	 * @return boolean
	 */
	private static function _get_track_rank()
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();

		// starレビュー
		$arr_top_star = static::_get_top_star_track($rank_dto->get_aggregate_from(), $rank_dto->get_aggregate_to());

		// クールレビュー
		$arr_top_cool = static::_get_top_cool_track($rank_dto->get_aggregate_from(), $rank_dto->get_aggregate_to());

		// starレビューを基準にクールレビューをまーじ
		$arr_rank = static::_merge_top_rank($arr_top_star,  $arr_top_cool);

		$rank_dto = RankDto::get_instance();
		$arr_result = array();
		$rank = 1;
		$cnt  = 0;
		foreach ($arr_rank as $i => $arr_1)
		{
			$arr_list = array();
			foreach ($arr_1 as $j => $arr_rank_detail)
			{
				$arr_list['track_id']       = $arr_rank_detail['track_id'];
				$arr_list['track_name']     = $arr_rank_detail['track_name'];
				$arr_list['artist_id']      = $arr_rank_detail['artist_id'];
				$arr_list['artist_name']    = $arr_rank_detail['artist_name'];
				$arr_list['mbid_itunes']    = $arr_rank_detail['mbid_itunes'];
				$arr_list['mbid_lastfm']    = $arr_rank_detail['mbid_lastfm'];
				$arr_list['image_medium']   = $arr_rank_detail['image_medium'];
				$arr_list['image_large']    = $arr_rank_detail['image_large'];
				$arr_list['genre']          = $arr_rank_detail['genre_itunes'];
				$arr_list['preview_itunes'] = $arr_rank_detail['preview_itunes'];
				$arr_list['aggregate']      = $arr_rank_detail['aggregate'];
				$arr_list['rank']           = $rank;

				$arr_result[] = $arr_list;
				$cnt++;
			}
			$rank = $rank + $cnt;
			$cnt = 0;
			if ($rank > $rank_dto->get_limit())
			{
				break;
			}
		}

		$rank_dto->set_arr_list($arr_result);

		return true;
	}


	private static function _get_album_rank()
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();

		// starレビュー
		$arr_top_star = static::_get_top_star_album($rank_dto->get_aggregate_from(), $rank_dto->get_aggregate_to());

		// クールレビュー
		$arr_top_cool = static::_get_top_cool_album($rank_dto->get_aggregate_from(), $rank_dto->get_aggregate_to());

		$arr_rank = array();
		foreach ($arr_top_star as $track_id => $arr_1)
		{
			foreach ($arr_1 as $star => $arr_star_detail)
			{
				$arr_merge_detail = array();
				if (empty($arr_star_detail['review']))
				{
					$star_for_aggregate = $arr_star_detail['star']/20; // 1/10 star(コメントなし)
				}
				else
				{
					$star_for_aggregate = $arr_star_detail['star']/10; // 1/10 star
				}

				if (isset($arr_top_cool[$track_id]))
				{
					$arr_cool_detail  = current($arr_top_cool[$track_id]);
					$arr_merge_detail = array_merge($arr_star_detail, $arr_cool_detail);
					$arr_merge_detail['aggregate'] += $star_for_aggregate;
				}
				else
				{
					$arr_merge_detail = $arr_star_detail;
					$arr_merge_detail['aggregate'] = $star_for_aggregate;
				}

				$arr_rank[$arr_merge_detail['aggregate'] * 100][] = $arr_merge_detail;
			}
		}
		krsort($arr_rank);
		unset($track_id, $j, $arr_1, $arr_star_detail);

		$arr_result = array();
		$rank = 1;
		$cnt  = 0;
		foreach ($arr_rank as $i => $arr_1)
		{
			$arr_list = array();
			foreach ($arr_1 as $j => $arr_rank_detail)
			{
				$arr_list['album_id']       = $arr_rank_detail['id'];
				$arr_list['album_name']     = $arr_rank_detail['name'];
				$arr_list['artist_id']      = $arr_rank_detail['artist_id'];
				$arr_list['artist_name']    = $arr_rank_detail['artist_name'];
				$arr_list['mbid_itunes']    = $arr_rank_detail['mbid_itunes'];
				$arr_list['mbid_lastfm']    = $arr_rank_detail['mbid_lastfm'];
				$arr_list['image_medium']   = $arr_rank_detail['image_medium'];
				$arr_list['image_large']    = $arr_rank_detail['image_large'];
				$arr_list['genre']          = $arr_rank_detail['genre_itunes'];
				$arr_list['aggregate']      = $arr_rank_detail['aggregate'];
				$arr_list['rank']           = $rank;

				$arr_result[] = $arr_list;
				$cnt++;
			}
			$rank = $rank + $cnt;
			$cnt = 0;
		}

		$rank_dto = RankDto::get_instance();
		$rank_dto->set_arr_list($arr_result);

		return true;
	}


	private static function _merge_top_rank($arr_top_star,  $arr_top_cool)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_rank = array();
		foreach ($arr_top_star as $track_id => $arr_star)
		{
			foreach ($arr_star as $star => $arr_star_detail)
			{
				$arr_merge_detail = array();
				if (empty($arr_star_detail['review']))
				{
					$star_for_aggregate = $arr_star_detail['star']/20; // 1/10 star(コメントなし)
				}
				else
				{
					$star_for_aggregate = $arr_star_detail['star']/10; // 1/10 star
				}

				if (isset($arr_top_cool[$track_id]))
				{
					$arr_cool_detail  = current($arr_top_cool[$track_id]);
					$arr_merge_detail = array_merge($arr_star_detail, $arr_cool_detail);
					$arr_merge_detail['aggregate'] += $star_for_aggregate;
					unset($arr_top_cool[$track_id]);
				}
				else
				{
					$arr_merge_detail = $arr_star_detail;
					$arr_merge_detail['aggregate'] = $star_for_aggregate;
				}

				$arr_rank[$arr_merge_detail['aggregate'] * 100][] = $arr_merge_detail;
			}
		} // endforeach

		foreach ($arr_top_cool as $track_id => $arr_cool)
		{
			foreach ($arr_cool as $cool => $arr_cool_detail)
			{
				$arr_rank[$arr_cool_detail['aggregate'] * 100][] = $arr_cool_detail;
			}
		}

		krsort($arr_rank);

		return $arr_rank;
	}


	/**
	 * by Batch
	 * @return array $arr_return['track_id']['star'] = array()
	 */
	private static function _get_top_star_track($from, $to)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_result = array();

		$review_track_dao = new ReviewMusicTrackDao();
		$arr_result = array();

		foreach ($review_track_dao->get_top_star($from, $to) as $i => $val)
		{
			$arr_result[$val['id']][$val['star']] = $val;
		}

		return $arr_result;
	}


	/**
	 * by Batch
	 * @return array $arr_return['track_id']['star'] = array()
	 */
	private static function _get_top_star_album($from, $to)
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();
		$offset = $rank_dto->get_offset();
		$limit  = $rank_dto->get_limit();
		$review_album_dao = new ReviewMusicAlbumDao();
		$arr_result = array();
		foreach ($review_album_dao->get_top_star($offset, $limit) as $i => $val)
		{
			$arr_result[$val['id']][$val['star']] = $val;
		}

		return $arr_result;
	}



	/**
	 * @return array $arr_return['track_id']['aggregate'] = array()
	 */
	private static function _get_top_cool_track($from, $to)
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();
		$offset = $rank_dto->get_offset();
		$limit  = $rank_dto->get_limit();

		$cool_dao = new CoolDao();
		$arr_result = array();
		foreach ($cool_dao->get_cool_top_week('track', $from, $to) as $i => $val)
		{
			$val['aggregate'] = $val['aggregate'] * ($val['star']/5); // cool * star/5
			$arr_result[$val['id']][$val['aggregate']] = $val;
		}

		return $arr_result;
	}


	/**
	 * @return array $arr_return['track_id']['aggregate'] = array()
	 */
	private static function _get_top_cool_album($from, $to)
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankDto::get_instance();
		$offset = $rank_dto->get_offset();
		$limit  = $rank_dto->get_limit();
		$cool_dao = new CoolDao();
		$arr_result = array();
		foreach ($cool_dao->get_cool_top_week('album', $from, $to, $offset, $limit) as $i => $val)
		{
			$val['aggregate'] = $val['aggregate'] * ($val['star']/5); // cool * star/5
			$arr_result[$val['id']][$val['aggregate']] = $val;
		}

		return $arr_result;
	}


}