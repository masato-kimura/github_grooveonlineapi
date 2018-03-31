<?php
namespace main;

use main\domain\service\CategoryService;
/**
 * @throws \Exception
 *  1001 正常
 *  8001 システムエラー
 *  8002 DBエラー
 *  9001 認証エラー
 *  9002 リクエスト内容未存在
 *  9003 必須項目エラー
 * @author masato
 * @params httpリクエスト email, password, api_key, auth_type
 *
 */
final class Controller_Category extends \Controller_Rest
{
	public function post_get()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			CategoryService::get_json_request();

			# バリデーションチェック
			CategoryService::validation_for_get();

			# DTOにリクエストをセット
			CategoryService::set_dto_for_get();

			# ログイン情報を取得
			$arr_result_dto = CategoryService::get_all_names();

			$arr_response = array(
				'success' 	=> true,
				'code' 		=> '1001',
				'response' => 'do you like music?',
				'result' => array(
					'arr_list' => $arr_result_dto,
				)
			);
			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			$arr_response = array('result' => null, 'success' => false, 'code' => $e->getCode(), 'response' => $e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine().']');
			\Log::error($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);
			$this->response($arr_response);

			return false;
		}
	}
}