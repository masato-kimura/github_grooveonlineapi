<?php
namespace main;

use Fuel\Core\Response;
use main\domain\service\SitemapService;

/**
 * @throws \Exception
 *  1001 正常
 *  8001 システムエラー
 *  8002 DBエラー
 *  9001 認証エラー
 *  9002 リクエスト内容未存在
 *  9003 必須項目エラー
 * @author masato
 */
final class Controller_Sitemap extends \Controller_Rest
{
	public function post_get()
	{
		\Log::debug('--------------------------------------');
		\Log::debug('[start]'. __METHOD__);

		# JSONリクエストを取得
		SitemapService::get_json_request();

		# バリデーションチェック
		SitemapService::validation_for_get();

		$arr_response = array(
			'success'  => true,
			'code'     => '1001',
			'response' => 'get site_map!',
			'result'   => array(
				'arr_list' => \Cache::get('site_map'),
			),
		);
		$this->response($arr_response);
		\Log::debug('[end]'. PHP_EOL. PHP_EOL);

		return true;
	}
}