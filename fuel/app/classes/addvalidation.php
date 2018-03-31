<?php
use main\model\dto\LoginDto;
use main\model\dao\LoginDao;
use main\model\dao\UserDao;
use main\model\dto\CoolDto;
use main\model\dao\ReviewMusicArtistDao;
use main\model\dao\ReviewMusicAlbumDao;
use main\model\dao\ReviewMusicTrackDao;
use main\model\dto\UserDto;
use main\domain\service\LoginService;
use main\domain\service\UserService;
use main\model\dao\PasswordreissueDao;
use main\model\dto\PasswordreissueDto;


class AddValidation
{
	/**
	 * API認証キーを確認
	 * @param md5 $api_key
	 * @throws \Exception
	 */
	public static function _validation_check_api_key($api_key)
	{
		\Log::debug('[start]'. __METHOD__);

		$last_initiated_api_key = self::_initiated_api_key(time()-60);
		$just_initiated_api_key = self::_initiated_api_key();
		$next_initiated_api_key = self::_initiated_api_key(time()+60);
		$arr_initiated_api_key = array(
				$just_initiated_api_key,
				$last_initiated_api_key,
				$next_initiated_api_key,
		);
		if ( ! in_array(trim($api_key), $arr_initiated_api_key))
		{
			throw new \Exception('authenticated error', 2001);
		}
	}


	/**
	 * API認証キー許可値を生成
	 * @param string $timestamp
	 * @return string
	 */
	private static function _initiated_api_key($timestamp=null)
	{
		$arr_keys = array(
			0 => 'm',
			1 => 'a',
			2 => 's',
			3 => 'a',
			4 => 't',
			5 => 'k',
			6 => 'i',
			7 => 'm',
			8 => 'u',
			9 => 'r',
		);

		$YmdHi = \Date::forge($timestamp)->format("%Y%m%d%H%M");
		$mid = (int)substr(\Date::forge($timestamp)->format("%M"), -1, 1);
		$initialized_key = md5($arr_keys[$mid] + $YmdHi);

		return $initialized_key;
	}


	/**
	 * ユーザログインハッシュ値のチェック
	 * ここが通ればログイン済となる
	 * @param md5 $login_hash
	 * @return boolean
	 */
	public static function _validation_check_login_hash($login_hash, $user_id=null)
	{
		\Log::debug('[start]'. __METHOD__);

		if (empty($user_id))
		{
			$user_id = trim(\Input::post('user_id'));
		}

		$login_dto = LoginDto::get_instance();
		$login_dto->set_user_id($user_id);
		$login_dto->set_login_hash(trim($login_hash));

		if (empty($login_hash))
		{
			return true;
		}

		$login_dao = new LoginDao();
		$login_dao->check_user_login_hash();

		return true;
	}


	/**
	 * email存在チェック
	 * decide=1のみで抽出
	 * @param unknown $email
	 * @throws \Exception
	 * @return boolean 存在時: true
	 */
	public static function _validation_is_exist_email($email)
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$user_dto->set_email(\Input::post('email'));
		$user_dto->set_auth_type(\Input::post('auth_type'));

		$user_dao = new UserDao();
		$result = $user_dao->is_exist_email();

		if (empty($result))
		{
			throw new \Exception('is not exist email', 7005);
		}

		return true;
	}


	/**
	 * email存在チェック
	 * decide=1のみで抽出
	 * @param unknown $email
	 * @throws \Exception
	 * @return boolean 存在時: true
	 */
	public static function _validation_is_exist_email_password_reissue($email)
	{
		\Log::debug('[start]'. __METHOD__);

		$password_reissue_dto = PasswordreissueDto::get_instance();
		$password_reissue_dto->set_email(trim($email));

		$password_reissue_dao = new PasswordreissueDao();
		$is_exist_email = $password_reissue_dao->is_exist_valid_email(\Config::get('login.passreissue_expired_min'));
		if (empty($is_exist_email))
		{
			throw new \Exception('email not exist at password_reissue_table error', 7005);
		}
		return true;
	}


	/**
	 * グルーヴオンラインログインでのemailのユニークをチェック
	 * decide=1のみで抽出
	 * @param unknown $email
	 * @throws \Exception
	 * @return boolean 未存在時：true
	 */
	public static function _validation_check_unique_email_grooveonline($email)
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$user_dto->set_email(\Input::post('email'));
		$user_dto->set_auth_type(\Input::post('auth_type'));

		$user_dao = new UserDao();

		if ($user_dao->is_exist_email())
		{
			throw new \Exception('email unique error', 7005);
		}

		return true;
	}


	/**
	 * グルーヴオンラインログインでのemailのユニークをチェック
	 * decide=1のみで抽出
	 * 自身のメールアドレスは除く
	 * @param unknown $email
	 * @throws \Exception
	 * @return boolean
	 */
	public static function _validation_check_unique_email_grooveonline_for_edit($email)
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$user_dto->set_email(\Input::post('email'));
		$user_dto->set_auth_type(\Input::post('auth_type'));

		$user_dao = new UserDao();

		if ($user_dao->is_exist_email(\Input::post('user_id')))
		{
			throw new \Exception('email unique error', 7005);
		}

		return true;
	}


	/**
	 * ユーザがパスワードまたはoAuth_idでログイン可能な状態かを確認する
	 * 退会済かも確認
	 * @param string $auth_type
	 * @return boolean
	 */
	public static function _validation_is_possible_login($auth_type)
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$user_dto->set_auth_type(trim($auth_type));
		$user_dto->set_oauth_id(trim(\Input::post('oauth_id')));
		$user_dto->set_email(trim(\Input::post('email')));
		$user_dto->set_password(md5(trim(\Input::post('password'))));

		# ユーザ情報を取得(ユーザ認証できなかったらexception処理)
		LoginService::get_user_info_for_login();

		# 退会済みかを確認する
		LoginService::check_already_leave();

		return true;
	}


	/**
	 * ユーザIDでユーザ情報をuser_dtoに取得
	 * 退会済かも確認
	 * @param string $auth_type
	 * @return boolean
	 */
	public static function _validation_is_possible_login_by_user_id($user_id)
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$user_dto->set_user_id(trim($user_id));

		# ユーザ情報をDTOに取得(ユーザ認証できなかったらexception処理)
		UserService::get_user_info_by_user_id();

		# 退会済みかを確認する
		LoginService::check_already_leave();

		return true;
	}


	/**
	 * 自身の投稿でないことの確認する
	 * (投稿者とクールしたユーザが同一であれば例外対応、不一致であればtrueを返す)
	 * @throws \Exception
	 * @return boolean
	 */
	public static function _validation_is_not_self_review()
	{
		\Log::debug('[start]'. __METHOD__);

		$cool_dto = CoolDto::get_instance();
		$cool_dto->set_review_id(\Input::post('review_id'));
		$cool_dto->set_cool_user_id(\Input::post('cool_user_id'));
		$cool_dto->set_about(\Input::post('about'));

		$review_dao = null;
		switch ($cool_dto->get_about())
		{
			case 'artist':
				$review_dao = new ReviewMusicArtistDao();
				break;
			case 'album':
				$review_dao = new ReviewMusicAlbumDao();
				break;
			case 'track':
				$review_dao = new ReviewMusicTrackDao();
				break;
		}

		$arr_where = array(
			'id'      => $cool_dto->get_review_id(),
			'user_id' => $cool_dto->get_cool_user_id(),
		);

		$arr_result = $review_dao->get_one($arr_where, array('id'));
		if ( ! empty($arr_result))
		{
			throw new \Exception('自身のレビューにはcoolできません');
		}

		return true;
	}
}