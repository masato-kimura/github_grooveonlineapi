<?php
namespace main\domain\service;

use Fuel\Core\Validation;
use Fuel\Core\Date;
use main;
use main\model\dto\UserDto;
use main\model\dto\LoginDto;
use main\model\dto\CoolDto;
use main\model\dto\GroupRelationDto;
use main\model\dto\PasswordreissueDto;
use main\model\dao\UserDao;
use main\model\dao\PasswordreissueDao;
use main\model\dao\CoolDao;
use main\model\dao\main\model\dao;
use main\model\dto\ArtistDto;
use main\model\dto\TracklistDto;

class UserService extends Service
{
	/**
	 * バリデーション
	 * @throws \Exception
	 * @return boolean
	 */
	public static function validation_for_regist()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		# auth_type
		$v = $obj_validate->add('auth_type', 'auth_type');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(grooveonline)|(facebook)|(google)|(twitter)|(yahoo)/');

		# oauth_id
		$v = $obj_validate->add('oauth_id', 'oauth_id');
		$v->add_rule('min_length', '3');
		$v->add_rule('max_length', '255');
		$arr_oauth = \Config::get('login.auth_type');
		if (isset($arr_oauth[\Input::post('auth_type')]))
		{
			if (\Input::post('auth_type') !== 'grooveonline')
			{
				$v->add_rule('required');
			}
		}

		# email
		$v = $obj_validate->add('email', 'email');
		$v->add_rule('valid_email');
		if (\Input::post('auth_type') === 'grooveonline')
		{
			$v->add_rule('required');
			$v->add_rule('check_unique_email_grooveonline');
		}

		# password
		$v = $obj_validate->add('password', 'password');
		$v->add_rule('max_length', '100');
		if (\Input::post('auth_type') === 'grooveonline')
		{
			$v->add_rule('required');
		}

		# user_name
		$v = $obj_validate->add('user_name', 'ユーザ名');
		$v->add_rule('max_length', '100');
		$v->add_rule('required');

		# first_name
		$v = $obj_validate->add('first_name', '名');
		$v->add_rule('max_length', '50');

		# last_name
		$v = $obj_validate->add('last_name', '苗字');
		$v->add_rule('max_length', '50');

		# date
		$v = $obj_validate->add('date', '登録日');
		$v->add_rule('max_length', '10');

		# link
		$v = $obj_validate->add('link', 'リンク');
		$v->add_rule('max_length', '255');
		$v->add_rule('valid_url');

		# gender
		$v = $obj_validate->add('gender', '性別');
		$v->add_rule('max_length', '10');

		# birthday @todo 検証validate
		$v = $obj_validate->add('birthday', '誕生年月日');
		$v->add_rule('max_length', '100');

		# birthday_year
		$v = $obj_validate->add('birthday_year', '誕生年');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '4');

		# birthday_month
		$v = $obj_validate->add('birthday_month', '誕生月');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '2');
		$v->add_rule('numeric_min', 0);
		$v->add_rule('numeric_max', 12);

		# birthday_day
		$v = $obj_validate->add('birthday_day', '誕生日');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '2');
		$v->add_rule('numeric_min', 0);
		$v->add_rule('numeric_max', 31);

		# birthday_secret
		$v = $obj_validate->add('birthday_secret', '誕生日表示フラグ');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('exact_length', 1);

		# old
		$v = $obj_validate->add('old', '年齢');
		$v->add_rule('valid_string', array('numeric'));

		# old_secret
		$v = $obj_validate->add('old_secret', '年齢表示フラグ');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('exact_length', 1);

		# locale
		$v = $obj_validate->add('locale', 'ロケール');
		$v->add_rule('max_length', '100');

		# country
		$v = $obj_validate->add('country', '国名');
		$v->add_rule('max_length', '50');

		# postal_code
		$v = $obj_validate->add('postal_code', '郵便番号');
		$v->add_rule('max_length', '100');

		# pref
		$v = $obj_validate->add('pref', '都道府県名');
		$v->add_rule('max_length', '50');

		# locality
		$v = $obj_validate->add('locality', '住所');
		$v->add_rule('max_length', '255');

		# street
		$v = $obj_validate->add('street', '番地');
		$v->add_rule('max_length', '100');

		# profile_fields
		$v = $obj_validate->add('profile_fields', 'プロフィール');
		$v->add_rule('max_length', '2000');

		# facebook_url
		$v = $obj_validate->add('facebook_url', 'フェイスブックURL');
		$v->add_rule('max_length', 255);
		$v->add_rule('valid_url');

		# google_url
		$v = $obj_validate->add('google_url', 'グーグルURL');
		$v->add_rule('max_length', 255);
		$v->add_rule('valid_url');

		# twitter_url
		$v = $obj_validate->add('twitter_url', 'TwitterURL');
		$v->add_rule('max_length', 255);
		$v->add_rule('valid_url');

		# instagram_url
		$v = $obj_validate->add('instagram_url', 'instagramURL');
		$v->add_rule('max_length', 255);
		$v->add_rule('valid_url');

		# site_url
		$v = $obj_validate->add('site_url', 'siteURL');
		$v->add_rule('max_length', 255);
		$v->add_rule('valid_url');

		# picture_url
		$v = $obj_validate->add('picture_url', '画像URL');
		$v->add_rule('max_length', '255');
		$v->add_rule('valid_url');

		# member_type
		$v = $obj_validate->add('member_type', 'メンバー区分');
		$v->add_rule('exact_length', 1);

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_decide()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		# user_id
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# login_hash
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('required');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash');

		# auth_type
		$v = $obj_validate->add('auth_type', 'auth_type');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(grooveonline)|(facebook)|(google)|(twitter)|(yahoo)/');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	/**
	 * バリデーション
	 * @throws \Exception
	 * @return boolean
	 */
	public static function validation_for_edit()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		# auth_type
		$v = $obj_validate->add('auth_type', 'auth_type');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(grooveonline)|(facebook)|(google)|(twitter)|(yahoo)/');
/*
		# oauth_id
		$v = $obj_validate->add('oauth_id', 'oauth_id');
		$v->add_rule('min_length', '3');
		$v->add_rule('max_length', '255');
		$arr_oauth = \Config::get('login.auth_type');
		if (isset($arr_oauth[\Input::post('auth_type')]))
		{
			if (\Input::post('auth_type') !== 'grooveonline')
			{
				$v->add_rule('required');
			}
		}
*/
		# email
		$v = $obj_validate->add('email', 'email');
		$v->add_rule('valid_email');
		if (\Input::post('auth_type') === 'grooveonline')
		{
			$v->add_rule('check_unique_email_grooveonline_for_edit'); // AddValidation
		}

		# password
		$v = $obj_validate->add('password', 'password');
		$v->add_rule('max_length', '100');

		# user_id
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');
		$v->add_rule('required');

		# login_hash
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('required');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash'); // AddValidation

		# user_name
		$v = $obj_validate->add('user_name', 'ユーザ名');
		$v->add_rule('max_length', '100');

		# first_name
		$v = $obj_validate->add('first_name', '名');
		$v->add_rule('max_length', '50');

		# last_name
		$v = $obj_validate->add('last_name', '苗字');
		$v->add_rule('max_length', '50');

		# date
		$v = $obj_validate->add('date', '登録日');
		//$v->add_rule('max_length', '10');

		# link
		$v = $obj_validate->add('link', 'リンク');
		$v->add_rule('max_length', '255');
		$v->add_rule('valid_url');

		# gender
		$v = $obj_validate->add('gender', '性別');
		$v->add_rule('max_length', '10');

		# birthday @todo 検証validate
		$v = $obj_validate->add('birthday', '誕生年月日');
		$v->add_rule('max_length', '100');

		# birthday_year
		$v = $obj_validate->add('birthday_year', '誕生年');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '4');

		# birthday_month
		$v = $obj_validate->add('birthday_month', '誕生月');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '2');
		$v->add_rule('numeric_min',0);
		$v->add_rule('numeric_max', 12);

		# birthday_day
		$v = $obj_validate->add('birthday_day', '誕生日');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '2');
		$v->add_rule('numeric_min', 0);
		$v->add_rule('numeric_max', 31);

		# birthday_secret
		$v = $obj_validate->add('birthday_secret', '誕生日表示フラグ');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('exact_length', 1);

		# old
		$v = $obj_validate->add('old', '年齢');
		$v->add_rule('valid_string', array('numeric'));

		# old_secret
		$v = $obj_validate->add('old_secret', '年齢表示フラグ');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('exact_length', 1);

		# locale
		$v = $obj_validate->add('locale', 'ロケール');
		$v->add_rule('max_length', '100');

		# country
		$v = $obj_validate->add('country', '国名');
		$v->add_rule('max_length', '50');

		# postal_code
		$v = $obj_validate->add('postal_code', '郵便番号');
		$v->add_rule('max_length', '100');

		# pref
		$v = $obj_validate->add('pref', '都道府県名');
		$v->add_rule('max_length', '50');

		# locality
		$v = $obj_validate->add('locality', '住所');
		$v->add_rule('max_length', '255');

		# street
		$v = $obj_validate->add('street', '番地');
		$v->add_rule('max_length', '100');

		# profile_fields
		$v = $obj_validate->add('profile_fields', 'プロフィール');
		$v->add_rule('max_length', '2000');

		# facebook_url
		$v = $obj_validate->add('facebook_url', 'フェイスブックURL');
		$v->add_rule('max_length', 255);
		$v->add_rule('valid_url');

		# google_url
		$v = $obj_validate->add('google_url', 'グーグルURL');
		$v->add_rule('max_length', 255);
		$v->add_rule('valid_url');

		# twitter_url
		$v = $obj_validate->add('twitter_url', 'TwitterURL');
		$v->add_rule('max_length', 255);
		$v->add_rule('valid_url');

		# instagram_url
		$v = $obj_validate->add('instagram_url', 'instagramURL');
		$v->add_rule('max_length', 255);
		$v->add_rule('valid_url');

		# site_url
		$v = $obj_validate->add('site_url', 'siteURL');
		$v->add_rule('max_length', 255);

		# picture_url
		$v = $obj_validate->add('picture_url', '画像URL');
		$v->add_rule('max_length', '255');
		$v->add_rule('valid_url');

		# member_type
		$v = $obj_validate->add('member_type', 'メンバー区分');
		$v->add_rule('exact_length', 1);

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_leave()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		# user_id
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# login_hash
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('required');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash'); // AddValidation

		# auth_type
		$v = $obj_validate->add('auth_type', 'auth_type');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(grooveonline)|(facebook)|(google)|(twitter)|(yahoo)/');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_me()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		# user_id
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');
		$v->add_rule('is_possible_login_by_user_id'); // AddValidation

		# login_hash
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('required');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash'); // AddValidation

		# auth_type
		$v = $obj_validate->add('auth_type', 'auth_type');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(grooveonline)|(facebook)|(google)|(twitter)|(yahoo)/');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_you()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		# user_id
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');
		$v->add_rule('is_possible_login_by_user_id'); // AddValidation

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_isregistemail()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		# email
		$v = $obj_validate->add('email', 'メールアドレス');
		$v->add_rule('required');
		$v->add_rule('valid_email');
		$v->add_rule('max_length', 200);

		# auth_type
		$v = $obj_validate->add('auth_type', 'auth_type');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(grooveonline)|(facebook)|(google)|(twitter)|(yahoo)/');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_isexistemailatpasswordreissue()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		# email
		$v = $obj_validate->add('email', 'メールアドレス');
		$v->add_rule('required');
		$v->add_rule('valid_email');
		$v->add_rule('max_length', 200);

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_passwordreissuerequest()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		# email
		$v = $obj_validate->add('email', 'メールアドレス');
		$v->add_rule('required');
		$v->add_rule('max_length', 200);
		$v->add_rule('is_exist_email'); // AddValidation

		# auth_type
		$v = $obj_validate->add('auth_type', 'auth_type');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(grooveonline)|(facebook)|(google)|(twitter)|(yahoo)/');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_passwordreissueupdate()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php
		$obj_validate->add_callable('main\domain\service\UserService'); //self

		# email
		$v = $obj_validate->add('email', 'メールアドレス');
		$v->add_rule('required');
		$v->add_rule('max_length', 200);
		$v->add_rule('is_exist_email'); // AddValidation
		$v->add_rule('is_exist_email_password_reissue'); // AddValidation

		# auth_type
		$v = $obj_validate->add('auth_type', 'auth_type');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(grooveonline)/');

		# tentative_id
		$v = $obj_validate->add('tentative_id', '仮発行ID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');
		$v->add_rule('check_tentative_record', \Input::param('email'), \Input::param('tentative_password')); // @self::_validation_check_tentative_record()

		# tentative_password
		$v = $obj_validate->add('tentative_password', '仮発行パスワード');
		$v->add_rule('required');
		$v->add_rule('min_length', '4');
		$v->add_rule('max_length', '100');

		# password
		$v = $obj_validate->add('password', 'パスワード');
		$v->add_rule('required');
		$v->add_rule('min_length', '4');
		$v->add_rule('max_length', '100');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_grooveonlineavailablelogin()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		# email
		$v = $obj_validate->add('email', 'email');
		$v->add_rule('required');
		$v->add_rule('valid_email');

		# password
		$v = $obj_validate->add('password', 'password');
		$v->add_rule('required');
		$v->add_rule('max_length', '100');
		$v->add_rule('valid_string', array('numeric', 'alpha', 'dashes'));

		# user_id
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# login_hash
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('required');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash', static::$_obj_request->user_id); // AddValidation

		# バリデート実行
		$arr_params = array(
			'api_key'    => static::$_obj_request->api_key,
			'user_id'    => static::$_obj_request->user_id,
			'login_hash' => static::$_obj_request->login_hash,
			'email'      => static::$_obj_request->email,
			'password'   => static::$_obj_request->password,
		);
		static::_validate_run($obj_validate, $arr_params);

	}


	public static function _validation_check_tentative_record($tentative_id, $email, $tentative_password)
	{
		\Log::debug('[start]'. __METHOD__);

		$password_reissue_dto = PasswordreissueDto::get_instance();
		$password_reissue_dto->set_email($email);
		$password_reissue_dto->set_tentative_id($tentative_id);
		$password_reissue_dto->set_tentative_password($tentative_password);

		$password_reissue_dao = new PasswordreissueDao();

		return $password_reissue_dao->is_exist_email_by_password_reissue_dto();
	}



	public static function set_dto_for_regist()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		foreach (static::$_obj_request as $key => $str)
		{
			if ( ! isset($str)) continue;

			if ($key === 'user_name')       $user_dto->set_user_name(trim($str));
			if ($key === 'first_name')      $user_dto->set_first_name(trim($str));
			if ($key === 'last_name')       $user_dto->set_last_name(trim($str));
			if ($key === 'date')            $user_dto->set_date(trim($str));
			if ($key === 'password')
			{
				$user_dto->set_password(md5(trim($str)));
				$user_dto->set_password_digits(mb_strwidth(trim($str)));
				$user_dto->set_password_org(trim($str));
			}
			if ($key === 'email')           $user_dto->set_email(trim($str));
			if ($key === 'link')            $user_dto->set_link(trim($str));
			if ($key === 'gender')          $user_dto->set_gender(trim($str));
			if ($key === 'birthday_year')   $user_dto->set_birthday_year(trim($str));
			if ($key === 'birthday_month')  $user_dto->set_birthday_month(trim($str));
			if ($key === 'birthday_day')    $user_dto->set_birthday_day(trim($str));
			if ($key === 'birthday_secret') $user_dto->set_birthday_secret(trim($str));
			if ($key === 'old')             $user_dto->set_old(trim($str));
			if ($key === 'old_secret')      $user_dto->set_old_secret(trim($str));
			if ($key === 'local')           $user_dto->set_locale(trim($str));
			if ($key === 'country')         $user_dto->set_country(trim($str));
			if ($key === 'postal_code')     $user_dto->set_postal_code(trim($str));
			if ($key === 'pref')            $user_dto->set_pref(trim($str));
			if ($key === 'locality')        $user_dto->set_locality(trim($str));
			if ($key === 'street')          $user_dto->set_street(trim($str));
			if ($key === 'profile_fields')  $user_dto->set_profile_fields($str);
			if ($key === 'facebook_url')    $user_dto->set_facebook_url($str);
			if ($key === 'google_url')      $user_dto->set_google_url($str);
			if ($key === 'twitter_url')     $user_dto->set_twitter_url($str);
			if ($key === 'site_url')        $user_dto->set_site_url($str);
			if ($key === 'instagram_url')   $user_dto->set_instagram_url($str);
			if ($key === 'auth_type')       $user_dto->set_auth_type(trim($str));
			if ($key === 'oauth_id')        $user_dto->set_oauth_id(trim($str));
			if ($key === 'picture_url')     $user_dto->set_pitcure_url(trim($str));
			if ($key === 'is_leaved')       $user_dto->set_is_leaved(trim($str));
			if ($key === 'leave_date')      $user_dto->set_leave_date($str);
			if ($key === 'last_login')      $user_dto->set_last_login(trim($str));
			if ($key === 'last_logout')     $user_dto->set_last_logout(trim($str));
			if ($key === 'member_type')     $user_dto->set_member_type($str);

			# 招待登録時
			if ($key === 'invited_by')      $user_dto->set_invited_by($str);
			if ($key === 'invite_id')       $user_dto->set_invite_id($str);
			if ($key === 'target_id')       $user_dto->set_target_id($str);
		}

		$user_name = $user_dto->get_user_name();
		if (empty($user_name))
		{
			$user_dto->set_user_name(trim($user_dto->get_last_name()). ' '. trim($user_dto->get_first_name()));
		}

		if ($user_dto->get_birthday_year() && $user_dto->get_birthday_month() && $user_dto->get_birthday_day())
		{
			$birthday = $user_dto->get_birthday_year(). '-'. $user_dto->get_birthday_month(). '-'. $user_dto->get_birthday_day();
			$user_dto->set_birthday($birthday);
		}

		$get_old = $user_dto->get_old();
		$get_birthday = $user_dto->get_birthday();
		if (empty($get_old) && $get_birthday)
		{
			$old = (int)((date('Ymd') - preg_replace('/[-\/]/i', '', $user_dto->get_birthday())) / 10000);
			$user_dto->set_old($old);
		}

		return true;
	}


	public static function set_dto_for_decide()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto  = UserDto::get_instance();
		$login_dto = LoginDto::get_instance();

		$user_dto->set_user_id(trim(\Input::post('user_id')));
		$user_dto->set_auth_type(trim(\Input::post('auth_type')));
		$login_dto->set_user_id(trim(\Input::post('user_id')));
		$login_dto->set_login_hash(trim(\Input::post('login_hash')));

		return true;
	}


	public static function set_dto_for_edit()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		foreach (static::$_obj_request as $key => $str)
		{
			if ( ! isset($str)) continue;

			if ($key === 'user_id')         $user_dto->set_user_id(trim($str));
			if ($key === 'user_name')       $user_dto->set_user_name(trim($str));
			if ($key === 'first_name')      $user_dto->set_first_name(trim($str));
			if ($key === 'last_name')       $user_dto->set_last_name(trim($str));
			if ($key === 'date')            $user_dto->set_date(trim($str));

			if ($key === 'password')
			{
				if ( ! empty($str))
				{
					$user_dto->set_password(md5(trim($str)));
					$user_dto->set_password_digits(mb_strwidth(trim($str)));
					$user_dto->set_password_org(trim($str));
				}
			}

			if ($key === 'email')           $user_dto->set_email(trim($str));
			if ($key === 'link')            $user_dto->set_link(trim($str));
			if ($key === 'gender')          $user_dto->set_gender(trim($str));
			if ($key === 'birthday_year')   $user_dto->set_birthday_year(trim($str));
			if ($key === 'birthday_month')  $user_dto->set_birthday_month(trim($str));
			if ($key === 'birthday_day')    $user_dto->set_birthday_day(trim($str));
			if ($key === 'birthday_secret') $user_dto->set_birthday_secret(trim($str));
			if ($key === 'old')             $user_dto->set_old(trim($str));
			if ($key === 'old_secret')      $user_dto->set_old_secret(trim($str));
			if ($key === 'local')           $user_dto->set_locale(trim($str));
			if ($key === 'country')         $user_dto->set_country(trim($str));
			if ($key === 'postal_code')     $user_dto->set_postal_code(trim($str));
			if ($key === 'pref')            $user_dto->set_pref(trim($str));
			if ($key === 'locality')        $user_dto->set_locality(trim($str));
			if ($key === 'street')          $user_dto->set_street(trim($str));
			if ($key === 'profile_fields')  $user_dto->set_profile_fields($str);
			if ($key === 'facebook_url')    $user_dto->set_facebook_url($str);
			if ($key === 'google_url')      $user_dto->set_google_url($str);
			if ($key === 'twitter_url')     $user_dto->set_twitter_url($str);
			if ($key === 'instagram_url')   $user_dto->set_instagram_url($str);
			if ($key === 'site_url')        $user_dto->set_site_url($str);
			if ($key === 'auth_type')       $user_dto->set_auth_type(trim($str));
			if ($key === 'oauth_id')        $user_dto->set_oauth_id(trim($str));
			if ($key === 'picture_url')     $user_dto->set_pitcure_url(trim($str));
			if ($key === 'is_leaved')       $user_dto->set_is_leaved(trim($str));
			if ($key === 'leave_date')      $user_dto->set_leave_date($str);
			if ($key === 'last_login')      $user_dto->set_last_login(trim($str));
			if ($key === 'last_logout')     $user_dto->set_last_logout(trim($str));
			if ($key === 'member_type')     $user_dto->set_member_type($str);

			# 招待登録時
			if ($key === 'invited_by')      $user_dto->set_invited_by($str);
			if ($key === 'invite_id')       $user_dto->set_invite_id($str);
			if ($key === 'target_id')       $user_dto->set_target_id($str);
		}

		$user_name = $user_dto->get_user_name();
		if (empty($user_name))
		{
			$user_dto->set_user_name(trim($user_dto->get_last_name()). ' '. trim($user_dto->get_first_name()));
		}

		if ($user_dto->get_birthday_year() && $user_dto->get_birthday_month() && $user_dto->get_birthday_day())
		{
			$birthday = $user_dto->get_birthday_year(). '-'. $user_dto->get_birthday_month(). '-'. $user_dto->get_birthday_day();
			$user_dto->set_birthday($birthday);
		}

		$get_old = $user_dto->get_old();
		$get_birthday = $user_dto->get_birthday();
		if (empty($get_old) && $get_birthday)
		{
			$old = (int)((date('Ymd') - preg_replace('/[-\/]/i', '', $user_dto->get_birthday())) / 10000);
			$user_dto->set_old($old);
		}

		return true;
	}


	public static function set_dto_for_leave()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto  = UserDto::get_instance();
		$login_dto = LoginDto::get_instance();

		$user_dto->set_user_id(trim(\Input::post('user_id')));
		$user_dto->set_auth_type(trim(\Input::post('auth_type')));
		$login_dto->set_user_id(trim(\Input::post('user_id')));
		$login_dto->set_login_hash(trim(\Input::post('login_hash')));

		return true;
	}


	public static function set_dto_for_me()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto  = UserDto::get_instance();
		$login_dto = LoginDto::get_instance();

		$user_dto->set_user_id(trim(\Input::post('user_id')));
		$user_dto->set_auth_type(trim(\Input::post('auth_type')));
		$login_dto->set_user_id(trim(\Input::post('user_id')));
		$login_dto->set_login_hash(trim(\Input::post('login_hash')));

		return true;
	}


	public static function set_dto_for_you()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto   = UserDto::get_instance();
		$login_dto  = LoginDto::get_instance();
		$cool_dto   = CoolDto::get_instance();
		$artist_dto = ArtistDto::get_instance();

		$user_dto->set_user_id(trim(\Input::post('user_id')));
		$cool_dto->set_offset(0);
		$cool_dto->set_limit(30);
		$artist_dto->set_offset(0);
		$artist_dto->set_limit(100);

		return true;
	}


	public static function set_dto_for_isregistemail()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto  = UserDto::get_instance();

		$user_dto->set_email(trim(\Input::post('email')));
		$user_dto->set_auth_type(trim(\Input::post('auth_type')));

		return true;
	}


	public static function set_dto_for_isexistemailatpasswordreissue()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto  = UserDto::get_instance();
		$password_reissue_dto = PasswordreissueDto::get_instance();

		$user_dto->set_email(trim(\Input::post('email')));
		$password_reissue_dto->set_email(trim(\Input::post('email')));

		return true;
	}


	public static function set_dto_for_passwordreissuerequest()
	{
		\Log::debug('[start]'. __METHOD__);

		$password_reissue_dto  = PasswordreissueDto::get_instance();

		$password_reissue_dto->set_email(trim(\Input::post('email')));
		$password_reissue_dto->set_auth_type(trim(\Input::post('auth_type')));

		return true;
	}


	public static function set_dto_for_passwordreissueupdate()
	{
		\Log::debug('[start]'. __METHOD__);

		$password_reissue_dto  = PasswordreissueDto::get_instance();
		$user_dto              = UserDto::get_instance();

		$password_reissue_dto->set_email(trim(\Input::post('email')));
		$password_reissue_dto->set_auth_type(trim(\Input::post('auth_type')));
		$password_reissue_dto->set_tentative_id(trim(\Input::post('tentative_id')));
		$password_reissue_dto->set_tentative_password(trim(\Input::post('tentative_password')));
		$user_dto->set_password(md5(trim(\Input::post('password'))));
		$user_dto->set_password_digits(mb_strwidth(trim(\Input::post('password'))));
		$user_dto->set_password_org(trim(\Input::post('password')));

		return true;
	}



	public static function set_dto_for_grooveonlineavailablelogin()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$user_dto->set_user_id(trim(static::$_obj_request->user_id));
		$user_dto->set_email(trim(static::$_obj_request->email));
		$user_dto->set_password(md5(trim(static::$_obj_request->password)));

		return true;
	}














	/**
	 * グループメンバーの仮登録処理
	 */
	public static function set_unregisted_user()
	{
		try
		{
			\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

			$user_dao = new UserDao();
			$user_dao->start_transaction();

			# ユーザ情報をインサート
			static::_set_unregist_user();

			$user_dao->commit_transaction();

			return true;
		}
		catch (\Exception $e)
		{
			//$user_dao->rollback_transaction();
			throw new \Exception($e->getMessage(), $e->getCode());
		}

	}


	public static function transaction_for_set_user_info()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$user_dao = new UserDao();

			$user_dao->start_transaction();

			# ユーザ情報をインサート
			$arr_result = static::_set_user_table_from_dto();

			# UserDtoにプライマリーキーをセット
			$user_dto = UserDto::get_instance();

			\Log::error('ユーザ登録を行います');
			\Log::error($user_dto);

			$user_dto->set_id($arr_result[0]);

			# ログイン情報をインサート
			LoginService::set_login();

			$user_dao->commit_transaction();

			return true;
		}
		catch (\Exception $e)
		{
			$user_dao->rollback_transaction();
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}


	public static function transaction_for_regist_decide()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$user_dao = new UserDao();

			$user_dao->start_transaction();

			# ユーザ情報を更新
			static::_decide_user_table();

			# ログインハッシュ値を変更
			LoginService::update_login_hash();

			$user_dao->commit_transaction();

			return true;
		}
		catch (\Exception $e)
		{
			$user_dao->rollback_transaction();
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}


	public static function transaction_for_update_user_info()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$user_dao = new UserDao();

			$user_dao->start_transaction();

			# ユーザ情報を更新
			static::_update_user_table_from_dto();

			# ログインハッシュ値を変更
			LoginService::update_login_hash();

			$user_dao->commit_transaction();

			return true;
		}
		catch (\Exception $e)
		{
			$user_dao->rollback_transaction();
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}


	/**
	 * userテーブルのパスワード更新
	 * loginテーブルへのインサート
	 * password_reissueテーブルの論理削除
	 *
	 * @throws \Exception
	 * @return boolean
	 */
	public static function transaction_for_password_reissue()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$user_dao = new UserDao();

			# トランザクション開始
			$user_dao->start_transaction();

			// -----------------------------
			// userテーブルのパスワードを更新
			// -----------------------------
			$user_dto = UserDto::get_instance();
			$arr_profile = array(
				'password'        => $user_dto->get_password(),
				'password_digits' => $user_dto->get_password_digits(),
			);
			$arr_where = array(
				'id'        => $user_dto->get_user_id(),
				'auth_type' => $user_dto->get_auth_type(),
			);
			$user_dao->update_profile($arr_profile, $arr_where);

			// -----------------------------
			// loginテーブルへ登録
			// -----------------------------
			$login_dto = LoginDto::get_instance();
			$login_dto->set_remark('after password reissue');
			LoginService::set_login();

			// -----------------------------------------------
			// password_reissueテーブルの該当レコードを論理削除
			// -----------------------------------------------
			$password_reissue_dao = new PasswordreissueDao();
			$password_reissue_dao->logical_delete();

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


	/**
	 *
	 * @throws \Exception
	 * @return $user_info stdClass
	 */
	public static function get_user_info_from_table_by_user_id()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$user_dao = new UserDao();
		if ( ! $obj_user_info = $user_dao->get_userinfo_by_user_id())
		{
			throw new \Exception('can not get userinfo error', 7007);
		}
		return $obj_user_info;
	}


	/**
	 *
	 * @throws \Exception
	 * @return $user_info stdClass
	 */
	public static function get_user_info_from_table_for_decide()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dao = new UserDao();
		if ( ! $obj_user_info = $user_dao->get_userinfo_for_decide())
		{
			throw new \Exception('can not get userinfo error', 7007);
		}
		return $obj_user_info;
	}


	/**
	 *
	 * @throws \Exception
	 * @return $user_info stdClass
	 */
	public static function get_user_info_from_table_for_edit()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dao = new UserDao();

		if ( ! $obj_user_info = $user_dao->get_userinfo_for_edit())
		{
			throw new \Exception('can not get userinfo error', 7007);
		}

		return $obj_user_info;
	}


	/**
	 * 仮パスワードとemailのセットを仮パスワード発行格納テーブルにインサート
	 */
	public static function set_tentative_password_reissue()
	{
		\Log::debug('[start]'. __METHOD__);

		$password_reissue_dto = PasswordreissueDto::get_instance();

		$tentative_password = static::_generate_tentative_password();
		$password_reissue_dto->set_tentative_password($tentative_password);

		$password_reissue_dao = new PasswordreissueDao();
		$password_reissue_dao->set_tentative_password_reissue();

		return true;
	}


	public static function get_user_info_by_user_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dao = new UserDao();
		if ( ! $obj_user_info = $user_dao->get_user_info_by_user_id())
		{
			throw new \Exception('can not get userinfo error', 7007);
		}

		return $obj_user_info;
	}


	/**
	 * user_dtoに格納
	 * @throws \Exception
	 * @return boolean
	 */
	public static function get_user_info_by_email()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dao = new UserDao();
		$obj_result = $user_dao->get_userinfo_by_email();
		if (empty($obj_result))
		{
			throw new \Exception('email not exist error', 7005);
		}

		$user_dto = UserDto::get_instance();
		$user_dto->set_user_id($obj_result->id);

		return true;
	}


	public static function get_user_info_for_me()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$arr_user_info = array(
			'user_id'         => $user_dto->get_user_id(),
			'user_name'       => $user_dto->get_user_name(),
			'first_name'      => $user_dto->get_first_name(),
			'last_name'       => $user_dto->get_last_name(),
			'password_digits' => $user_dto->get_password_digits(),
			'email'           => $user_dto->get_email(),
			'link'            => $user_dto->get_link(),
			'gender'          => $user_dto->get_gender(),
			'birthday'        => $user_dto->get_birthday(),
			'birthday_year'   => $user_dto->get_birthday_year(),
			'birthday_month'  => $user_dto->get_birthday_month(),
			'birthday_day'    => $user_dto->get_birthday_day(),
			'birthday_secret' => $user_dto->get_birthday_secret(),
			'old'             => $user_dto->get_old(),
			'old_secret'      => $user_dto->get_old_secret(),
			'locale'          => $user_dto->get_locale(),
			'country'         => $user_dto->get_country(),
			'postal_code'     => $user_dto->get_postal_code(),
			'pref'            => $user_dto->get_pref(),
			'locality'        => $user_dto->get_locality(),
			'street'          => $user_dto->get_street(),
			'profile_fields'  => $user_dto->get_profile_fields(),
			'facebook_url'    => $user_dto->get_facebook_url(),
			'google_url'      => $user_dto->get_google_url(),
			'twitter_url'     => $user_dto->get_twitter_url(),
			'instagram_url'   => $user_dto->get_instagram_url(),
			'site_url'        => $user_dto->get_site_url(),
			'auth_type'       => $user_dto->get_auth_type(),
			'oauth_id'        => $user_dto->get_oauth_id(),
			'picture_url'     => $user_dto->get_picture_url(),
			'group'           => GroupService::get_group_info_from_user_id(),
			'favorite_users'  => $user_dto->get_favorite_artists(),
		);

		return $arr_user_info;
	}


	public static function get_user_info_for_you()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$tracklist_dto = TracklistDto::get_instance();

		$arr_user_info['user_id']         = $user_dto->get_user_id();
		$arr_user_info['user_name']       = $user_dto->get_user_name();
		$arr_user_info['first_name']      = $user_dto->get_first_name();
		$arr_user_info['last_name']       = $user_dto->get_last_name();
		$arr_user_info['link']            = $user_dto->get_link();
		$arr_user_info['gender']          = $user_dto->get_gender();
		$arr_user_info['old_secret']      = $user_dto->get_old_secret();
		$arr_user_info['locale']          = $user_dto->get_locale(); // 国
		$arr_user_info['country']         = $user_dto->get_country();
		$arr_user_info['pref']            = $user_dto->get_pref();
		$arr_user_info['profile_fields']  = $user_dto->get_profile_fields();
		$arr_user_info['facebook_url']    = $user_dto->get_facebook_url();
		$arr_user_info['google_url']      = $user_dto->get_google_url();
		$arr_user_info['twitter_url']     = $user_dto->get_twitter_url();
		$arr_user_info['instagram_url']   = $user_dto->get_instagram_url();
		$arr_user_info['site_url']        = $user_dto->get_site_url();
		$arr_user_info['picture_url']     = $user_dto->get_picture_url();
		$arr_user_info['birthday_secret'] = $user_dto->get_birthday_secret();
		$arr_user_info['group']           = GroupService::get_group_info_from_user_id();
		if ($user_dto->get_old_secret() !== 1)
		{
			$arr_user_info['old'] = $user_dto->get_old();
		}
		if ($user_dto->get_birthday_secret() !== 1)
		{
			$arr_user_info['birthday']      = $user_dto->get_birthday();
			$arr_user_info['birthday_year'] = $user_dto->get_birthday_year();
			$arr_user_info['birthday_month'] = $user_dto->get_birthday_month();
			$arr_user_info['birthday_day']   = $user_dto->get_birthday_day();
		}

		$arr_user_info['arr_thanks'] = static::_get_thanks();
		$arr_user_info['arr_cools']  = static::_get_cools();
		$arr_user_info['favorite_artists'] = $user_dto->get_favorite_artists();
		$arr_user_info['track_list']       = $tracklist_dto->get_arr_list();

		return $arr_user_info;
	}


	private static function _get_thanks()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$cool_dto = CoolDto::get_instance();
		$cool_dao = new CoolDao();
		return $cool_dao->get_thanks($user_dto->get_user_id(), $cool_dto->get_offset(), $cool_dto->get_limit());
	}


	private static function _get_cools()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$cool_dto = CoolDto::get_instance();
		$cool_dao = new CoolDao();
		return $cool_dao->get_cools($user_dto->get_user_id(), $cool_dto->get_offset(), $cool_dto->get_limit());
	}


	/**
	 * ユーザの退会手続きを行う
	 */
	public static function leave_user_table_from_dto()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		$arr_profile = array(
			'is_leaved' => 1,
			'leave_date' => \Date::forge()->format('%Y-%m-%d %H:%M:%S')
		);

		$arr_where = array(
			'id'        => $user_dto->get_user_id(),
			'auth_type' => $user_dto->get_auth_type(),
		);

		$dao = new UserDao();
		$affected_row = $dao->update_profile($arr_profile, $arr_where);

		if (empty($affected_row))
		{
			throw new \Exception('can not user leave', 8002);
		}

		return $affected_row;
	}


	/**
	 * 対象のemailの存在確認
	 * 存在する：true, 未存在：throw exception
	 * @param $isset_dto boolean true:検索結果がdtoに代入される
	 * @throws \Exception
	 * @return boolean
	 */
	public static function is_exist_email($isset_dto=false)
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dao = new UserDao();
		$result = $user_dao->is_exist_email(null, $isset_dto);
		if (empty($result))
		{
			throw new \Exception('email not exist error', 7005);
		}
		return true;
	}


	/**
	 * 対象のemailの存在確認(password_reissueテーブル)
	 * 存在する：true, 未存在：throw exception
	 * @throws \Exception
	 * @return boolean
	 */
	public static function is_exist_email_on_password_reissue()
	{
		\Log::debug('[start]'. __METHOD__);

		$password_reissue_dao = new PasswordreissueDao();
		$expired_min = \Config::get('login.passreissue_expired_min'); // 10
		$is_exist_email = $password_reissue_dao->is_exist_valid_email($expired_min);
		if (empty($is_exist_email))
		{
			return false;
		}
		return true;
	}


	/**
	 * パスワード再発行テーブルに有効であるemailが存在するか
	 */
	public static function is_exist_valid_email_on_password_reissue()
	{
		\Log::debug('[start]'. __METHOD__);

		$password_reissue_dao = new \main\model\dao\PasswordreissueDao();
		$is_exist_email = $password_reissue_dao->is_exist_valid_email();
		if (empty($is_exist_email))
		{
			throw new \Exception('email not exist at password_reissue_table error', 7005);
		}
		return true;
	}


	/**
	 *
	 * @throws \Exception
	 * @return $user_info stdClass
	 */
	public static function is_exist_user_by_user_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dao = new UserDao();
		if ( ! $user_dao->is_exist_user_by_user_id())
		{
			throw new \Exception('can not get userinfo error', 7007);
		}
		return true;
	}


	public static function is_available_login_user()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$user_id  = $user_dto->get_user_id();
		$email    = $user_dto->get_email();
		$password = $user_dto->get_password();

		$user_dao = new UserDao();
		$arr_result = $user_dao->get_available_grooveonline_user($user_id, $email, $password);
		if (empty($arr_result))
		{
			return false;
		}

		return true;

	}


	/**
	 * emailが登録済みであることを確認
	 * 登録済み：true, 未登録：exception
	 *
	 * @throws \Exception
	 * @return boolean
	 */
	public static function check_email_exists()
	{
		$user_dao = new UserDao();
		if (false === $user_dao->is_exist_email())
		{
			return false;
		}
		return true;
	}


	/**
	 * emailが未登録であることを確認
	 * 未登録:true, 登録済み：exception
	 *
	 * @throws \Exception
	 * @return boolean
	 */
	public static function check_email_not_exists()
	{
		$user_dao = new UserDao();
		if (true === $user_dao->is_exist_email())
		{
			return false;
		}
		return true;
	}




	private static function _decide_user_table()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		$arr_values = array(
			'is_decided'  => 1,
			'decide_date' => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
		);

		$arr_where = array(
			'id'        => $user_dto->get_user_id(),
			'auth_type' => $user_dto->get_auth_type(),
		);

		$dao = new UserDao();
		$arr_result = $dao->update_profile($arr_values, $arr_where);
		if ($arr_result === false)
		{
			throw new \Exception('can not user decide', 8002);
		}

		return $arr_result;
	}


	private static function _update_user_table_from_dto()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		$arr_request = array();
		$user_name = $user_dto->get_user_name();
		if ( ! empty($user_name))
		{
			$arr_request['user_name'] = $user_dto->get_user_name();
		}

		$date = $user_dto->get_date();
		if ( ! empty($date))
		{
			$arr_request['date'] = $user_dto->get_date();
		}

		$first_name = $user_dto->get_first_name();
		if ( ! empty($first_name))
		{
			$arr_request['first_name'] = $user_dto->get_first_name();
		}

		$last_name = $user_dto->get_last_name();
		if ( ! empty($last_name))
		{
			$arr_request['last_name'] = $user_dto->get_last_name();
		}

		$password = $user_dto->get_password();
		if ( ! empty($password))
		{
			$arr_request['password']        = $user_dto->get_password();
			$arr_request['password_digits'] = $user_dto->get_password_digits();
		}

		$arr_request['email']           = $user_dto->get_email();
		$arr_request['link']            = $user_dto->get_link();
		$arr_request['gender']          = $user_dto->get_gender();
		$arr_request['birthday']        = $user_dto->get_birthday();
		$arr_request['birthday_year']   = $user_dto->get_birthday_year();
		$arr_request['birthday_month']  = $user_dto->get_birthday_month();
		$arr_request['birthday_day']    = $user_dto->get_birthday_day();
		$arr_request['old']             = $user_dto->get_old();
		$arr_request['birthday_secret'] = $user_dto->get_birthday_secret();
		$arr_request['old_secret']      = $user_dto->get_old_secret();
		$arr_request['locale']          = $user_dto->get_locale();
		$arr_request['country']         = $user_dto->get_country();
		$arr_request['postal_code']     = $user_dto->get_postal_code();
		$arr_request['pref']            = $user_dto->get_pref();
		$arr_request['locality']        = $user_dto->get_locality();
		$arr_request['street']          = $user_dto->get_street();
		$arr_request['profile_fields']  = $user_dto->get_profile_fields();
		$arr_request['facebook_url']    = $user_dto->get_facebook_url();
		$arr_request['google_url']      = $user_dto->get_google_url();
		$arr_request['twitter_url']     = $user_dto->get_twitter_url();
		$arr_request['instagram_url']   = $user_dto->get_instagram_url();
		$arr_request['site_url']        = $user_dto->get_site_url();
		$arr_request['auth_type']       = $user_dto->get_auth_type();
		$arr_request['oauth_id']        = $user_dto->get_oauth_id();
		$arr_request['picture_url']     = $user_dto->get_picture_url();

		if ($user_dto->get_invited_by() === 'group')
		{
			$arr_where = array(
				'id' => $user_dto->get_user_id(),
			);
		}
		else
		{
			$arr_where = array(
				'id'        => $user_dto->get_user_id(),
				'auth_type' => $user_dto->get_auth_type(),
			);
		}

		$dao = new UserDao();
		$arr_result = $dao->update_profile($arr_request, $arr_where);
		if ($arr_result === false)
		{
			throw new \Exception('can not user update', 8002);
		}
		return $arr_result;
	}


	private static function _set_user_table_from_dto()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		$arr_request = array();

		$user_name = $user_dto->get_user_name();
		if ( ! empty($user_name))
		{
			$arr_request['user_name'] = $user_dto->get_user_name();
		}

		$first_name = $user_dto->get_first_name();
		if ( ! empty($first_name))
		{
			$arr_request['first_name'] = $user_dto->get_first_name();
		}

		$last_name = $user_dto->get_last_name();
		if ( ! empty($last_name))
		{
			$arr_request['last_name'] = $user_dto->get_last_name();
		}

		$password = $user_dto->get_password();
		if ( ! empty($password))
		{
			$arr_request['password'] = $user_dto->get_password();
			$arr_request['password_digits'] = $user_dto->get_password_digits();
		}

		$email = $user_dto->get_email();
		$arr_request['email'] = $user_dto->get_email();

		$arr_request['date']            = Date::forge()->format('%Y-%m-%d');
		$arr_request['link']            = $user_dto->get_link();
		$arr_request['gender']          = $user_dto->get_gender();
		$arr_request['birthday']        = $user_dto->get_birthday();
		$arr_request['birthday_year']   = $user_dto->get_birthday_year();
		$arr_request['birthday_month']  = $user_dto->get_birthday_month();
		$arr_request['birthday_day']    = $user_dto->get_birthday_day();
		$arr_request['birthday_secret'] = $user_dto->get_birthday_secret();
		$arr_request['old']             = $user_dto->get_old();
		$arr_request['old_secret']      = $user_dto->get_old_secret();
		$arr_request['locale']          = $user_dto->get_locale();
		$arr_request['country']         = $user_dto->get_country();
		$arr_request['postal_code']     = $user_dto->get_postal_code();
		$arr_request['pref']            = $user_dto->get_pref();
		$arr_request['locality']        = $user_dto->get_locality();
		$arr_request['street']          = $user_dto->get_street();
		$arr_request['profile_fields']  = $user_dto->get_profile_fields();
		$arr_request['facebook_url']    = $user_dto->get_facebook_url();
		$arr_request['google_url']      = $user_dto->get_google_url();
		$arr_request['twitter_url']     = $user_dto->get_twitter_url();
		$arr_request['instagram_url']   = $user_dto->get_instagram_url();
		$arr_request['site_url']        = $user_dto->get_site_url();
		$arr_request['auth_type']       = $user_dto->get_auth_type();
		$arr_request['oauth_id']        = $user_dto->get_oauth_id();
		$arr_request['picture_url']     = $user_dto->get_picture_url();
		$arr_request['member_type']     = $user_dto->get_member_type();

		$dao = new UserDao();
		$arr_result = $dao->set_profile($arr_request);
		if ($arr_result === false)
		{
			throw new \Exception('can not user insert', 8001);
		}

		return $arr_result;
	}


	private static function _set_unregist_user()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$group_relation_dto = GroupRelationDto::get_instance();
		$arr_members = $group_relation_dto->get_arr_members();

		$user_dao = new UserDao();

		foreach ($arr_members as $i => $_members)
		{
			$arr_request = array();
			$arr_request['user_name'] = $_members->name;
			$arr_request['member_type'] = '0';

			$arr_result = $user_dao->set_profile($arr_request);
			$arr_members[$i]->user_id = $arr_result[0];

			if ($arr_result === false)
			{
				throw new \Exception('can not user insert', 8001);
			}
		}

		$group_relation_dto->set_arr_members($arr_members);

		return true;
	}


	private static function _generate_tentative_password()
	{
		$pass = md5(date('YisdHm'));

		return $pass;
	}
}