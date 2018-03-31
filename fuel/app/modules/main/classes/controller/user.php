<?php
namespace main;

use main\domain\service\UserService;
use main\model\dto\PasswordreissueDto;
use main\model\dto\LoginDto;
use main\model\dto\UserDto;
use main\domain\service\ArtistService;
use main\domain\service\TracklistService;

final class Controller_User extends \Controller_Rest
{
	/**
	 * ユーザを仮登録する。（decideフラグは0）
	 */
	public function post_regist()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			UserService::get_json_request();

			# バリデーションチェック
			UserService::validation_for_regist();

			# DTOにリクエストをセット
			UserService::set_dto_for_regist();

			# データベースに登録
			UserService::transaction_for_set_user_info();

			# ログイン情報を取得
			$login_dto = LoginDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success' => true,
				'code'=> '1001',
				'response' => 'user first_regist complate!',
				'result' =>array(
					'user_id'    => $login_dto->get_user_id(),
					'login_hash' => $login_dto->get_login_hash(),
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


	/**
	 * ユーザを登録を決定する。
	 */
	public function post_decide()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			UserService::get_json_request();

			# バリデーションチェック
			UserService::validation_for_decide();

			# DTOにリクエストをセット
			UserService::set_dto_for_decide();

			# ユーザ情報をDBより取得（取得できない場合はException処理）user_id, auth_type
			$obj_user_info_from_db = UserService::get_user_info_from_table_for_decide();

			# データベースを更新
			UserService::transaction_for_regist_decide();

			$user_dto  = UserDto::get_instance();
			$login_dto = LoginDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success'    => true,
				'code'       => '1001',
				'response'   => 'user regist_decide complate!',
				'result'     => array(
					'user_id'    => $user_dto->get_id(),
					'login_hash' => $login_dto->get_login_hash(),
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


	/**
	 * ユーザ情報を更新する
	 * api_key(サーバ認証キー), user_id(ユーザID), login_hash(ユーザ認証キー), auth_type
	 * email&password(グルーヴオンラインのみ、なくてもいいかな〜), oauth_id(oauth認証のみ、なくてもいいかな〜)
	 * @return boolean
	 */
	public function post_edit()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			UserService::get_json_request();

			# バリデーションチェック
			UserService::validation_for_edit();

			# DTOにリクエストをセット
			UserService::set_dto_for_edit();

			# データベースを更新
			UserService::transaction_for_update_user_info();

			$user_dto  = UserDto::get_instance();
			$login_dto = LoginDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success' => true,
				'code'=> '1001',
				'response' => 'user edit complate!',
				'result' => array(
					'user_id'    => $user_dto->get_id(),
					'login_hash' => $login_dto->get_login_hash(),
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


	/**
	 * ユーザ情報の退会手続きを行う
	 * 論理削除をおこなったユーザはある期間再登録を行うことができない。
	 * @return boolean
	 */
	public function post_leave()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			UserService::get_json_request();

			# バリデーションチェック
			UserService::validation_for_leave();

			# DTOにリクエストをセット
			UserService::set_dto_for_leave();

			# データベースの退会フラグをTRUEへ
			UserService::leave_user_table_from_dto();

			$user_dto = UserDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'user leave complate!',
				'result'   => array(
					'user_id' => $user_dto->get_id(),
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


	public function post_me()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			UserService::get_json_request();

			# バリデーションチェック
			UserService::validation_for_me();

			# DTOにリクエストをセット
			UserService::set_dto_for_me();

			# ユーザ情報をDBより取得
			UserService::get_user_info_by_user_id();

			# お気に入りアーティスト情報を取得
			ArtistService::get_favorite_artist_by_user_id();

			# APIレスポンス
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'OK!',
				'result'   => UserService::get_user_info_for_me(),
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


	/**
	 * 他人のユーザ情報を取得する
	 * 条件：ログイン必要なし
	 * @return boolean
	 */
	public function post_you()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			UserService::get_json_request();

			# バリデーションチェック
			UserService::validation_for_you();

			# DTOにリクエストをセット
			UserService::set_dto_for_you();

			# ユーザ情報を取得
			// (バリデーションチェックis_possible_login_by_user_id()で取得済み)

			# お気に入りアーティスト情報を取得
			ArtistService::get_favorite_artist_by_user_id();

			# トラックリスト一覧を取得（タイトルのみ）
			TracklistService::get_track_list_titles();

			# APIレスポンス
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'OK!',
				'result'   => UserService::get_user_info_for_you(),
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



	/**
	 * メールアドレスが登録済みであることを確認する
	 * (is_decided=0は未登録とする)
	 * 登録済み:true, 未登録:false
	 * @return boolean
	 */
	public function post_isregistemail()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			UserService::get_json_request();

			# バリデーションチェック
			UserService::validation_for_isregistemail();

			# DTOにリクエストをセット
			UserService::set_dto_for_isregistemail();

			# Emailアドレスの有効性を確認
			$result = UserService::check_email_exists();

			# APIレスポンス
			if ($result === true)
			{
				$arr_response = array(
					'success'  => true,
					'code'     => '1001',
					'response' => 'this email exist',
					'result'   => array(
						'is_exist' => true,
					),
				);
			}
			else
			{
				$arr_response = array(
					'success'  => true,
					'code'     => '1001',
					'response' => 'this email not exist',
					'result'   => array(
						'is_exist' => false,
					),
				);
			}

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine(). ']');

			$arr_response = array(
				'result'   => false,
				'success'  => false,
				'code'     => $e->getCode(),
				'response' => $e->getMessage(),
			);

			$this->response($arr_response);
			\Log::error('[end]'. PHP_EOL. PHP_EOL);

			return false;
		}
	}


	/**
	 * メールアドレスが未登録であることを確認する
	 * (is_decided=0は未登録とする)
	 * 未登録:true, 登録済み:false,
	 * @return boolean
	 */
	public function post_isnotregistemail()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			UserService::get_json_request();

			# バリデーションチェック
			UserService::validation_for_isregistemail();

			# DTOにリクエストをセット
			UserService::set_dto_for_isregistemail();

			# Emailアドレスの有効性を確認
			$result = UserService::check_email_not_exists();

			# APIレスポンス
			if ($result === true)
			{
				$arr_response = array(
					'success'  => true,
					'code'     => '1001',
					'response' => 'this not email exist',
					'result'   => array(
						'is_not_exist' => true,
					),
				);
			}
			else
			{
				$arr_response = array(
						'success'  => true,
						'code'     => '1001',
						'response' => 'this email exist',
						'result'   => array(
							'is_not_exist' => false,
						),
				);
			}

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine(). ']');

			$arr_response = array(
				'result'   => false,
				'success'  => false,
				'code'     => $e->getCode(),
				'response' => $e->getMessage(),
			);

			$this->response($arr_response);
			\Log::error('[end]'. PHP_EOL. PHP_EOL);

			return false;
		}
	}


	/**
	 * パスワード再発行テーブルに有効なemailが存在しているか
	 * @return 存在する:true, 存在しない：false
	 *
	 */
	public function post_isexistemailatpasswordreissue()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			UserService::get_json_request();

			# バリデーションチェック
			UserService::validation_for_isexistemailatpasswordreissue();

			# DTOにリクエストをセット
			UserService::set_dto_for_isexistemailatpasswordreissue();

			# Emailアドレスの有効性を確認
			if (UserService::is_exist_email_on_password_reissue())
			{
				$arr_response = array(
					'success'  => true,
					'code'     => '1001',
					'response' => 'is exists email at passwordreissue',
					'result'   => array(
						'is_exist' => true,
						'expired_min' => \Config::get('login.passreissue_expired_min'),
					),
				);
			}
			else
			{
				$arr_response = array(
					'success'  => true,
					'code'     => '1001',
					'response' => 'email not exist at password_reissue_table error',
					'result'   => array(
						'is_exist' => false,
						'expired_min' => \Config::get('login.passreissue_expired_min'),
					),
				);
			}

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine(). ']');

			$arr_response = array(
				'result'   => false,
				'success'  => false,
				'code'     => $e->getCode(),
				'response' => $e->getMessage(),
			);

			$this->response($arr_response);
			\Log::error('[end]'. PHP_EOL. PHP_EOL);

			return false;
		}
	}


	/**
	 * パスワード紛失時に仮パスワードを再発行し
	 * 仮発行パスワード格納テーブルへインサートする
	 * @return boolean
	 */
	public function post_passwordreissuerequest()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			UserService::get_json_request();

			# バリデーションチェック
			UserService::validation_for_passwordreissuerequest();

			# DTOにリクエストをセット
			UserService::set_dto_for_passwordreissuerequest();

			# 仮パスワード格納テーブルへセット
			UserService::set_tentative_password_reissue();

			$password_reissue_dto = PasswordreissueDto::get_instance();

			$arr_response = array(
				'success' => true,
				'code'=> '1001',
				'response' => 'set password reissue OK',
				'result' => array(
					'email'              => $password_reissue_dto->get_email(),
					'expired_min'        => \Config::get('login.passreissue_expired_min'),
					'tentative_id'       => $password_reissue_dto->get_id(),
					'tentative_password' => $password_reissue_dto->get_tentative_password(),
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


	/**
	 * 仮パスワードとemailをキーにuserテーブルのパスワードを新パスワードで更新する
	 * @return boolean
	 */
	public function post_passwordreissueupdate()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			UserService::get_json_request();

			# バリデーションチェック
			UserService::validation_for_passwordreissueupdate();

			# DTOにリクエストをセット
			UserService::set_dto_for_passwordreissueupdate();

			# emailからユーザ情報を取得
			UserService::get_user_info_by_email();

			# 新パスワードを適用
			UserService::transaction_for_password_reissue();

			$login_dto = LoginDto::get_instance();
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'password reissue complate!',
				'result'   => array(
					'user_id'    => $login_dto->get_user_id(),
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


	/**
	 * メールアドレスとパスワードでログインが有効であることを確認
	 * (is_decided=0は未登録とする)
	 * 登録済み:true, 未登録:false
	 * @return boolean
	 */
	public function post_grooveonlineavailablelogin()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			UserService::get_json_request();

			# バリデーションチェック
			UserService::validation_for_grooveonlineavailablelogin();

			# DTOにリクエストをセット
			UserService::set_dto_for_grooveonlineavailablelogin();

			# Emailアドレスの有効性を確認
			$result = UserService::is_available_login_user();

			# APIレスポンス
			if ($result === true)
			{
				$arr_response = array(
						'success'  => true,
						'code'     => '1001',
						'response' => 'this login user exist',
						'result'   => array(
								'is_available' => true,
						),
				);
			}
			else
			{
				$arr_response = array(
						'success'  => true,
						'code'     => '1001',
						'response' => 'this login user not exist',
						'result'   => array(
								'is_available' => false,
						),
				);
			}

			$this->response($arr_response);

			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine(). ']');

			$arr_response = array(
					'result'   => false,
					'success'  => false,
					'code'     => $e->getCode(),
					'response' => $e->getMessage(),
			);

			$this->response($arr_response);
			\Log::error('[end]'. PHP_EOL. PHP_EOL);

			return false;
		}
	}


}