<?php
namespace main;

use Fuel\Core\Response;
use main\domain\service\FavoriteUserService;
use main\model\dto\FavoriteUserDto;

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
final class Controller_Favorite extends \Controller_Rest
{
	public function post_set()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			FavoriteUserService::get_json_request();

			# バリデーションチェック
			FavoriteUserService::validation_for_set();

			# DTOにリクエストをセット
			FavoriteUserService::set_dto_for_set();

			# データベースインサートを実行
			FavoriteUserService::set_favorite_user();

			$favorite_user_dto = FavoriteUserDto::get_instance();

			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'set favorite done!',
				'result'   => array(
					'favorite_user_id' => $favorite_user_dto->get_favorite_user_id(),
				),
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


	public function post_get()
	{
		\Log::debug('--------------------------------------');
		\Log::debug('[start]'. __METHOD__);

		# JSONリクエストを取得
		FavoriteUserService::get_json_request();

		# バリデーションチェック
		FavoriteUserService::validation_for_get();

		# DTOにリクエストをセット
		FavoriteUserService::set_dto_for_get();

		# データベースから取得
		FavoriteUserService::get_favorite_users();

		$favorite_user_dto = FavoriteUserDto::get_instance();
		$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'get favorite done!',
				'result'   => array(
						'favorite_users' => $favorite_user_dto->get_arr_favorite_users(),
				),
		);
		$this->response($arr_response);
		\Log::debug('[end]'. PHP_EOL. PHP_EOL);

		return true;
	}
}