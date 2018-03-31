<?php
namespace main;

use main\model\user;
use Fuel\Core\Response;
use main\domain\service\AuthService;
use main\domain\service\GroupService;
use main\domain\service\UserService;
use main\domain\service\LoginService;
use main\model\dto\GroupDto;
use main\model\dto\CategoryDto;
use main\model\dto\GroupRelationDto;
use main\model\dto\UserDto;

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
final class Controller_Group extends \Controller_Rest
{
	public function post_create()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			GroupService::get_json_request();

			# バリデーションチェック
			GroupService::validation_for_create();

			# DTOにリクエストをセット
			GroupService::set_dto_for_create();

			# グループを登録
			GroupService::insert_group();

			# グループへメンバーを登録
			GroupService::insert_group_relation();

			$group_dto = GroupDto::get_instance();

			$arr_response = array(
				'success' 	=> true,
				'code' 		=> '1001',
				'response' => 'your group create complate !',
				'result' => array(
					'group_id' 	=> $group_dto->get_group_id(),
				),
			);

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine(). ']');

			$arr_response = array(
				'result'   => null,
				'success'  => false,
				'code'     => $e->getCode(),
				'response' => $e->getMessage(),
			);

			$this->response($arr_response);
			\Log::error('[end]'. PHP_EOL. PHP_EOL);

			return false;
		}
	}


	public function post_edit()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

			# JSONリクエストを取得
			$handle = fopen('php://input', 'r');
			$json_request = fgets($handle);
			fclose($handle);

			# リクエストのオブジェクト化
			$obj_request = json_decode($json_request);

			# API認証キーを確認
			AuthService::set_request_api_key($obj_request);
			AuthService::check_api_key();

			# DTOにリクエストをセット
			GroupService::set_group_dto_from_request($obj_request);
			GroupService::set_group_relation_dto_from_request($obj_request);
			UserService::set_user_dto_from_request($obj_request);
			LoginService::set_login_dto_from_request($obj_request);

			# リクエストバリデーション
			GroupService::validation_for_edit();

			# ログイン済みチェック
			LoginService::check_user_login_hash();

			# ユーザのグループ権限チェック
			GroupService::is_authorized_user_in_group();

			# グループを更新
			GroupService::edit_group();

			$group_dto = GroupDto::get_instance();

			$arr_response = array(
					'success' 	=> true,
					'code' 		=> '1001',
					'response' => 'your group edit complate !',
					'result' => array(
					'group_id' 	=> $group_dto->get_id(),
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


	public function post_us()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

			# JSONリクエストを取得
			$handle = fopen('php://input', 'r');
			$json_request = fgets($handle);
			fclose($handle);

			# リクエストのオブジェクト化
			$obj_request = json_decode($json_request);

			# API認証キーを確認
			AuthService::set_request_api_key($obj_request);
			AuthService::check_api_key();

			# DTOにリクエストをセット
			GroupService::set_group_dto_from_request($obj_request);
			GroupService::set_group_relation_dto_from_request($obj_request);
			UserService::set_user_dto_from_request($obj_request);
			LoginService::set_login_dto_from_request($obj_request);

			# バリデーション
			GroupService::validation_for_us();

			# ログイン済みチェック
			LoginService::check_user_login_hash();

			# ユーザのグループ権限チェック
			GroupService::is_authorized_user_in_group();

			# グループ情報を取得
			GroupService::get_group_info();
			GroupService::get_category_info();

			$group_dto = GroupDto::get_instance();
			$category_dto = CategoryDto::get_instance();

			$arr_response = array(
				'success' 	=> true,
				'code' 		=> '1001',
				'response' => 'your group create complate !',
				'result' => array(
					'group_id' 	=> $group_dto->get_id(),
					'group_name' => $group_dto->get_name(),
					'category_id' => $group_dto->get_category_id(),
					'category_name' => $category_dto->get_name(),
					'category_english' => $category_dto->get_english(),
					'link' => $group_dto->get_link(),
					'profile_fields' => $group_dto->get_profile_fields(),
					'is_leaved' => $group_dto->get_is_leaved(),
					'leave_date' => $group_dto->get_leave_date(),
					'members' => GroupService::get_members(),
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
	 * グループにメンバを追加する
	 * @return boolean
	 */
	public function post_memberadd()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

			# JSONリクエストを取得
			$handle = fopen('php://input', 'r');
			$json_request = fgets($handle);
			fclose($handle);

			# リクエストのオブジェクト化
			$obj_request = json_decode($json_request);

			# API認証キーを確認
			AuthService::set_request_api_key($obj_request);
			AuthService::check_api_key();

			# DTOにリクエストをセット
			GroupService::set_group_dto_from_request($obj_request);
			GroupService::set_group_relation_dto_from_request($obj_request);
			UserService::set_user_dto_from_request($obj_request);
			LoginService::set_login_dto_from_request($obj_request);

			# バリデーション
			GroupService::validation_for_memberadd();

			# ログイン済みチェック
			LoginService::check_user_login_hash();

			# ユーザのグループ権限チェック
			GroupService::is_authorized_user_in_group();

			# メンバーをユーザ情報に仮登録
			UserService::set_unregisted_user();

			# メンバーをグループに登録
			GroupService::member_add();

			$group_dto = GroupDto::get_instance();
			$arr_response = array(
					'success' 	=> true,
					'code' 		=> '1001',
					'response' => 'group member add complate',
					'result' => array(
						'group_id' => $group_dto->get_id(),
					) ,
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
	 *
	 * @return boolean
	 */
	public function post_isinvited()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

			# JSONリクエストを取得
			$handle = fopen('php://input', 'r');
			$json_request = fgets($handle);
			fclose($handle);

			# リクエストのオブジェクト化
			$obj_request = json_decode($json_request);

			# API認証キーを確認
			AuthService::set_request_api_key($obj_request);
			AuthService::check_api_key();

			# DTOにリクエストをセット
			GroupService::set_group_dto_from_request($obj_request);
			GroupService::set_group_relation_dto_from_request($obj_request);
			UserService::set_user_dto_from_request($obj_request);
			LoginService::set_login_dto_from_request($obj_request);

			# バリデーション
			GroupService::validation_for_isinvited();

			# ユーザのリレーションテーブルの存在確認
			GroupService::is_enabled_unregisted_user_by_group();

			$user_dto = UserDto::get_instance();

			$user_dto->set_id($user_dto->get_invite_id());
			UserService::is_exist_user_by_user_id();

			$user_dto->set_id($user_dto->get_target_id());
			UserService::is_exist_user_by_user_id();

			$group_dto = GroupDto::get_instance();
			$arr_response = array(
					'result' => true,
					'success' 	=> true,
					'code' 		=> '1001',
					'response' => 'exist user',
			);

			$this->response($arr_response);

			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			$arr_response = array('result' => false, 'success' => false, 'code' => $e->getCode(), 'response' => $e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine().']');
			\Log::error($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);
			$this->response($arr_response);

			return false;
		}
	}

	public function post_memberdelete()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

			# JSONリクエストを取得
			$handle = fopen('php://input', 'r');
			$json_request = fgets($handle);
			fclose($handle);

			# リクエストのオブジェクト化
			$obj_request = json_decode($json_request);

			# API認証キーを確認
			AuthService::set_request_api_key($obj_request);
			AuthService::check_api_key();

			# DTOにリクエストをセット
			GroupService::set_group_dto_from_request($obj_request);
			GroupService::set_group_relation_dto_from_request($obj_request);
			UserService::set_user_dto_from_request($obj_request);
			LoginService::set_login_dto_from_request($obj_request);

			# バリデーション
			GroupService::validation_for_us();

			# ログイン済みチェック
			LoginService::check_user_login_hash();

			# ユーザのグループ権限チェック
			GroupService::is_authorized_user_in_group();

			# メンバーを論理削除 @todo メンバが属してるかチェック
			GroupService::member_delete($obj_request->arr_member_id);

			$group_relation_dto = GroupRelationDto::get_instance();
			$arr_response = array(
				'success' 	=> true,
				'code' 		=> '1001',
				'response' => 'user delete complate',
				'result' => array(
					'group_id' => $group_relation_dto->get_group_id(),
					'arr_delete_member_id' => $obj_request->arr_member_id,
				) ,
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