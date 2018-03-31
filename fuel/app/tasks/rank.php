<?php
namespace Fuel\Tasks;

use main\domain\service\RankService;
class Rank
{
	public static function weekly_track()
	{
		\Log::debug('[start]'. __METHOD__);

		# DTOにリクエストをセット
		RankService::set_dto_for_weekly_track();

		# トラックランキングを取得
		RankService::get_rank();

		# データベース格納
		RankService::set_weekly_rank();

		return true;
	}


	public static function weekly_album()
	{
		\Log::debug('[start]'. __METHOD__);

		# DTOにリクエストをセット
		RankService::set_dto_for_weekly_album();

		# アルバムランキングを取得
		RankService::get_rank();

		# データベース格納
		RankService::set_weekly_rank();

		return true;
	}
}
