<?php
namespace main;

use Fuel\Core\Response;
use main\domain\service\LoginService;
use main\model\dto\UserDto;
use main\model\dto\LoginDto;
use main\model\dto\FavoriteUserDto;
use main\domain\service\FavoriteUserService;

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
final class Controller_Login extends \Controller_Rest
{
	public function post_login()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			LoginService::get_json_request();

			# バリデーションチェック
			LoginService::validation_for_login();

			# DTOにリクエストをセット
			LoginService::set_dto_for_login();

			# ログイン処理を実行
			LoginService::set_login();

			# お気に入りユーザを取得
			FavoriteUserService::get_favorite_user_id();

			$user_dto  = UserDto::get_instance();
			$login_dto = LoginDto::get_instance();
			$favorite_user_dto = FavoriteUserDto::get_instance();
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'you login done !',
				'result'   => array(
					'user_id'    => $login_dto->get_user_id(),
					'user_name'  => $user_dto->get_user_name(),
					'auth_type'  => $user_dto->get_auth_type(),
					'login_hash' => $login_dto->get_login_hash(),
					'favorite_users' => $favorite_user_dto->get_arr_favorite_users(),
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


	/**
	 * ログアウト処理
	 * 必須項目 user_id, login_hash, api_key
	 */
	public function post_logout()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

			# JSONリクエストを取得
			LoginService::get_json_request();

			# バリデーションチェック
			LoginService::validation_for_logout();

			# DTOにリクエストをセット
			LoginService::set_dto_for_logout();

			# ログアウト処理
			LoginService::set_logout();

			$login_dto = LoginDto::get_instance();
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'see you again !',
				'result'   => array(
					'user_id' => $login_dto->get_user_id(),
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
}