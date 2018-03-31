<?php
namespace main\domain\service\review;

use main\model\dao\ReviewMusicTrackDao;
use main\model\dto\ReviewMusicDto;
class TrackReview implements ReviewInterface
{
	public static function get_all_user_review()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_review_dto       = ReviewMusicDto::get_instance();
		$obj_review_track_dao = new ReviewMusicTrackDao();
		$limit = $obj_review_dto->get_limit();
		$page  = $obj_review_dto->get_page();

		return $obj_review_track_dao->get_review_list();
	}


	public static function get_review_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_track_dao = new ReviewMusicTrackDao();

		return $review_track_dao->get_review_detail();
	}


	public static function get_all_user_review_count()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_review_track_dao = new ReviewMusicTrackDao();
		$arr_result = $obj_review_track_dao->get_review_list(true);
		return $arr_result[0]['cnt'];
	}


	/**
	 * トップレビューリストを取得
	 */
	public static function get_top_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_review_track_dao = new ReviewMusicTrackDao();
		return $obj_review_track_dao->get_top_list();
	}
}