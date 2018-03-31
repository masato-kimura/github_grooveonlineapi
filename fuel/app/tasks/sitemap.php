<?php
namespace Fuel\Tasks;

use main\domain\service\SitemapService;
class Sitemap
{
	/**
	 * インフォメーションに表示するレビューコメント数を集計しセットする
	 */
	public static function make()
	{
		\Log::debug('[start]'. __METHOD__);

		# DTOにリクエストをセット
		SitemapService::set_dto_for_make();

		# トラックランキングを取得
		SitemapService::get_reviews();
		SitemapService::get_users();
		SitemapService::get_artists();
		SitemapService::get_albums();

		SitemapService::set_cache();

		return true;
	}
}
