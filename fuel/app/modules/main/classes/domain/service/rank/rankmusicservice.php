<?php
namespace main\domain\service\rank;

use Fuel\Core\Validation;
use main\model\dao\ReviewMusicTrackDao;
use main\model\dto\RankMusicDto;
use main\domain\service\Service;
/**
 * @author masato
 *
 */
class RankMusicService extends Service
{
	public static function validation_for_rank()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		# オブジェクト化
		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('page', 'ページ');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('numeric_min', '1');
		$v->add_rule('numeric_max', '100000000');

		$v = $obj_validate->add('limit', '１ページあたりのアルバム表示数');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('numeric_min', '1');
		$v->add_rule('numeric_max', '200');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function set_dto_for_rank()
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_dto = RankMusicDto::get_instance();

		$rank_dto->set_page(trim(static::$_obj_request->page));
		$rank_dto->set_limit(trim(static::$_obj_request->limit));

		return true;
	}


	public static function get_track_ranking_week()
	{
		\Log::debug('[start]'. __METHOD__);

		$rank_music_dto = RankMusicDto::get_instance();
		$from = \Date::forge(time() - 60*60*24*7)->format('%Y-%m-%d');
		$to = \Date::forge()->format('%Y-%m-%d 23:59:59');
		$rank_music_dto->set_rank_from_date($from);
		$rank_music_dto->set_rank_to_date($to);

		$obj_review_track = new ReviewMusicTrackDao();
		$arr_dao_result = $obj_review_track->get_rank();

		$arr_result = array();
		$rank = 0;
		$point_tmp = 0;
		foreach ($arr_dao_result as $i => $val)
		{
			$val['point'] = 21 + round($val['star'] * 314.56); // 適当

			if ($val['point'] != $point_tmp)
			{
				$rank++;
			}

			$val['rank_number'] = $rank;
			$point_tmp = $val['point'];

			$arr_result[] = $val;
		}

		$rank_music_dto->set_arr_list($arr_result);

		return true;
	}
}