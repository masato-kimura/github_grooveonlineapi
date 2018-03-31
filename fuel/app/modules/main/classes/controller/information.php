<?php
namespace main;

use Fuel\Core\Response;
use main\domain\service\FavoriteUserService;
use main\model\dto\FavoriteUserDto;
use main\domain\service\InformationService;
use main\model\dto\UserInformationDto;
use main\model\dto\GlobalInformationDto;

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
final class Controller_Information extends \Controller_Rest
{
	public function post_getreview()
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


	/**
	 * レビューコメント件数をユーザ毎に取得する
	 */
	public function post_getuserinformation()
	{
		\Log::debug('--------------------------------------');
		\Log::debug('[start]'. __METHOD__);

		# JSONリクエストを取得
		InformationService::get_json_request();

		InformationService::validation_for_getuserinformation();

		InformationService::set_dto_for_getuserinformation();

		# データベースから取得
		InformationService::get_user_information();

		$information_dto = UserInformationDto::get_instance();
		$arr_response = array(
			'success'  => true,
			'code'     => '1001',
			'response' => 'get_user_information',
			'result'   => array(
				'user_id'             => $information_dto->get_user_id(),
				'comment_count'       => $information_dto->get_comment_count(),
				'artist_review_count' => 0,
				'updated_at'          => $information_dto->get_updated_at(),
			),
		);

		$this->response($arr_response);
		\Log::debug('[end]'. PHP_EOL. PHP_EOL);

		return true;
	}


	/**
	 * グルーブオンラインからのお知らせを取得
	 */
	public function post_getglobalinformation()
	{
		\Log::debug('--------------------------------------');
		\Log::debug('[start]'. __METHOD__);

		# JSONリクエストを取得
		InformationService::get_json_request();

		InformationService::validation_for_getglobalinformation();

		InformationService::set_dto_for_getglobalinformation();

		# データベースから取得
		InformationService::get_global_information();

		$information_dto = GlobalInformationDto::get_instance();
		$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'get_global_information',
				'result'   => array(
					'count'    => $information_dto->get_count(),
					'arr_list' => $information_dto->get_arr_list(),
				),
		);

		$this->response($arr_response);
		\Log::debug('[end]'. PHP_EOL. PHP_EOL);

		return true;
	}
}