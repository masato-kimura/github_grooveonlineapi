<?php
namespace main\domain\service\review;

use main\model\dto\ReviewMusicDto;
use main\model\dao\ReviewMusicAlbumDao;
class AlbumReview implements ReviewInterface
{
	public static function get_all_user_review()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_review_dto = ReviewMusicDto::get_instance();
		$limit  = $obj_review_dto->get_limit();
		$page   = $obj_review_dto->get_page();
		$obj_review_album_dao = new ReviewMusicAlbumDao();

		return $obj_review_album_dao->get_review_list();
	}


	public static function get_review_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_album_dao = new ReviewMusicAlbumDao();

		return $review_album_dao->get_review_detail();
	}


	public static function get_all_user_review_count()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_review_album_dao = new ReviewMusicAlbumDao();
		$arr_result = $obj_review_album_dao->get_review_list(true);
		return $arr_result[0]['cnt'];
	}


	/**
	 * トップレビューリスト
	 */
	public static function get_top_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_review_album_dao = new ReviewMusicAlbumDao();
		return $obj_review_album_dao->get_top_list();
	}
}