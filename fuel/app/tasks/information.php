<?php
namespace Fuel\Tasks;

use main\domain\service\InformationService;
class Information
{
	/**
	 * インフォメーションに表示するレビューコメント数を集計しセットする
	 */
	public static function set_user_review_count()
	{
		\Log::debug('[start]'. __METHOD__);

		# DTOにリクエストをセット
		InformationService::set_dto_for_getuserreviewcount();

		# トラックランキングを取得
		InformationService::get_user_review_count();

		# データベース格納
		InformationService::set_user_review_count();

		return true;
	}
}
