<?php
namespace main\domain\service;

use main\model\dto\LoginDto;
use main\model\dto\UserDto;
use main\model\dto\GroupDto;
use main\model\dao\LoginDao;
use main\model\dao\UserDao;
use Fuel\Core\Validation;
/**
 * プライマリーのidは基本使用しない
 * @author masato
 *
 */
class LoginService extends Service
{
	public static function validation_for_login()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		# oauth
		$v = $obj_validate->add('oauth_id', 'oauth_id');
		$v->add_rule('min_length', '3');
		$v->add_rule('max_length', '255');
		$arr_oauth = \Config::get('login.auth_type');
		unset($arr_oauth['grooveonline']);
		if (isset($arr_oauth[\Input::post('auth_type')]))
		{
			$v->add_rule('required');
		}

		# email
		$v = $obj_validate->add('email', 'email');
		$v->add_rule('valid_email');
		if (\Input::post('email') === 'grooveonline')
		{
			$v->add_rule('required');
		}

		# password
		$v = $obj_validate->add('password', 'password');
		$v->add_rule('max_length', '100');
		if (\Input::post('email') === 'grooveonline')
		{
			$v->add_rule('required');
		}

		# auth_type
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php
		$v = $obj_validate->add('auth_type', 'auth_type');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(grooveonline)|(facebook)|(google)|(twitter)|(yahoo)/');
		$v->add_rule('is_possible_login'); //ユーザ情報を取得(ユーザ認証できなかったらexception処理)

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_logout()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		# user_id
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# login_hash
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('required');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function set_dto_for_login()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto  = UserDto::get_instance();

		foreach (static::$_obj_request as $key => $val)
		{
			if ( ! isset($val))
			{
				continue;
			}
			if ($key === 'auth_type')
			{
				$user_dto->set_auth_type(trim($val));
			}
			if ($key === 'oauth_id')
			{
				$user_dto->set_oauth_id(trim($val));
			}
			if ($key === 'email')
			{
				$user_dto->set_email(trim($val));
			}
			if ($key === 'password')
			{
				$user_dto->set_password(md5(trim($val)));
			}
		}

		return true;
	}


	public static function set_dto_for_logout()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto  = UserDto::get_instance();
		$login_dto = LoginDto::get_instance();

		$user_dto->set_id(trim(\Input::post('user_id')));
		$login_dto->set_user_id(trim(\Input::post('user_id')));
		$login_dto->set_login_hash(trim(\Input::post('login_hash')));

		return true;
	}



	/**
	 * ログイン情報は明示的にLoginDtoにセットしているwa-
	 * @throws \Exception
	 */
	public static function set_login()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$user_dto = UserDto::get_instance();
			$user_dao = new UserDao();

			switch ($user_dto->get_auth_type())
			{
				case \Config::get('login.auth_type.grooveonline'):
					$strategy = new \main\domain\service\login\ConcreateStrategyGrooveonline();
					break;
				case \Config::get('login.auth_type.facebook'):
					$strategy = new \main\domain\service\login\ConcreateStrategyFacebook();
					break;
				case \Config::get('login.auth_type.twitter'):
					$strategy = new \main\domain\service\login\ConcreateStrategyTwitter();
					break;
				case \Config::get('login.auth_type.yahoo'):
					$strategy = new \main\domain\service\login\ConcreateStrategyYahoo();
					break;
				case \Config::get('login.auth_type.google'):
					$strategy = new \main\domain\service\login\ConcreateStrategyGoogle();
					break;
				default:
					throw new \Exception('required error[auth_type]', 7002);
			}

			$obj_login_context = new \main\domain\service\login\LoginContext($strategy);

			# トランザクション開始
			$user_dao->start_transaction();

			# loginテーブルにログイン情報をインサート
			$obj_login_context->login();

			# userテーブルの最終ログイン日時を更新
			$user_dao->update_last_login();

			# トランザクションコミット
			$user_dao->commit_transaction();

			return true;
		}
		catch (\Exception $e)
		{
			# トランザクションロールバック
			$user_dao->rollback_transaction();
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}


	public static function set_login_dto_from_request($obj_request)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$dto = \main\model\dto\LoginDto::get_instance();

		foreach ($obj_request as $key => $str)
		{
			if (empty($str)) continue;
			if ($key === 'user_id') $dto->set_user_id(trim($str));
			if ($key === 'login_hash') $dto->set_login_hash(trim($str));
			if ($key === 'remark') $dto->set_remark(trim($str));
		} // endforeach
		return true;
	}


	/**
	 * ログアウト処理を行う
	 */
	public static function set_logout()
	{
		try
		{
			\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

			$login_dao = new \main\model\dao\LoginDao();
			$user_dao  = new \main\model\dao\UserDao();

			$login_dao->start_transaction();
			$logout = $login_dao->set_logout();
			if (empty($logout)) throw new \Exception('can not logout', 7011);
			$user_dao->update_last_logout();
			$login_dao->commit_transaction();

			return true;
		}
		catch (\Exception $e)
		{
			$login_dao->rollback_transaction();
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}


	public static function update_login_hash()
	{
		\Log::debug('[start]'. __METHOD__);

		$login_hash = static::generate_login_hash();

		$user_dto = UserDto::get_instance();
		$login_dao = new LoginDao();

		if ($user_dto->get_invited_by() != 'group')
		{
			if ( ! $login_dao->remove_login_hash_for_reflash())
			{
				throw new \Exception('can not update', 8002);
			}
		}

		if ( ! $login_dao->set_login_hash_for_reflash($login_hash))
		{
			throw new \Exception('can not insert', 8001);
		}

		$login_dto = LoginDto::get_instance();
		$login_dto->set_login_hash($login_hash);

		return true;
	}


	public static function get_user_info_for_login()
	{
		$dto = UserDto::get_instance();
		switch ($dto->get_auth_type())
		{
			case \Config::get('login.auth_type.grooveonline'):
				$strategy = new \main\domain\service\login\ConcreateStrategyGrooveonline();
				break;
			case \Config::get('login.auth_type.facebook'):
				$strategy = new \main\domain\service\login\ConcreateStrategyFacebook();
				break;
			case \Config::get('login.auth_type.twitter'):
				$strategy = new \main\domain\service\login\ConcreateStrategyTwitter();
				break;
			case \Config::get('login.auth_type.yahoo'):
				$strategy = new \main\domain\service\login\ConcreateStrategyYahoo();
				break;
			case \Config::get('login.auth_type.google'):
				$strategy = new \main\domain\service\login\ConcreateStrategyGoogle();
				break;
			default:
				throw new \Exception('required error[auth_type]', 7002);
		}

		$obj_login_context = new \main\domain\service\login\LoginContext($strategy);
		$obj_result = $obj_login_context->get_user_info();

		if (empty($obj_result))
		{
			\Log::error('ユーザ登録が確認できないのでこのあとユーザ登録にします');
			\Log::error($dto);
			throw new \Exception('can not get userinfo error', 7007);
		}
		return $obj_result;
	}


	/**
	 * 共通：ログイン済みである状態でのAPIアクセスの基本バリデーション
	 */
	public static function validation_for_logined()
	{
		return static::_validation_type_base();
	}


	private static function _validation_type_base()
	{
		$login_dto = LoginDto::get_instance();

		# user_id
		$user_id = $login_dto->get_user_id();
		if (isset($user_id))
		{
			if ( ! preg_match('/^[\d]+$/i', $user_id)) throw new \Exception('type error[user_id]', 7003);
			if (strlen($user_id) >= 255) throw new \Exception('type length error[user_id]', 7003);
		}

		# login_hash
		$login_hash = $login_dto->get_login_hash();
		if (isset($login_hash))
		{
			if ( ! preg_match('/^[a-z0-9]{32}$/i', $login_hash, $match)) throw new \Exception('type error[login_hash]', 7003);
		}

		return true;
	}


	public static function generate_login_hash()
	{
		$rand = rand(1, 399);
		$hash = md5(\Date::forge()->format('%Y%m%d%H%M%S'). $rand);
		return $hash;
	}


	public static function check_user_authorize()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$user_dto = UserDto::get_instance();
		$invited_by = $user_dto->get_invited_by();
		if ( ! empty($invited_by))
		{
			switch ($invited_by)
			{
				case 'group':
					$group_dto = GroupDto::get_instance();
					$group_id  = $group_dto->get_id();
					$target_id = $user_dto->get_target_id();
					$invite_id = $user_dto->get_invite_id();
					if (empty($group_id) or empty($target_id) or empty($invite_id))
					{
						throw new \Exception('required error[invited]', 7002);
					}
					GroupService::is_enabled_unregisted_user_by_group();

				break;
			}
		}

		return true;
	}


	/**
	 * # ログイン済みチェック
	 *
	 * @return boolean
	 */
	public static function check_user_login_hash()
	{
		\Log::debug('[start]'. __METHOD__);

		$login_dao = new LoginDao();

		return $login_dao->check_user_login_hash();
	}


	public static function check_already_leave()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		if ($user_dto->get_is_decided() == 0)
		{
			throw new \Exception('user is not decide regist', 7009);
		}

		if ($user_dto->get_is_leaved() == 1)
		{
			throw new \Exception('user was already leaved', 7008);
		}
		return true;
	}



}