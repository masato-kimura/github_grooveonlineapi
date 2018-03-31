<?php
namespace main\domain\service;

use Fuel\Core\Validation;
use main\model\dao\FavoriteUserDao;
use main\model\dto\FavoriteUserDto;
use main\model\dto\UserDto;
/**
 * プライマリーのidは基本使用しない
 * @author masato
 *
 */
class FavoriteUserService extends Service
{
	public static function validation_for_set()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);
		$_POST['user_id'] = \Input::param('client_user_id');

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		# client_user_id
		$v = $obj_validate->add('client_user_id', 'クライアントユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# client_user_id
		$v = $obj_validate->add('favorite_user_id', 'お気に入りユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# status
		$v = $obj_validate->add('status', 'お気に入り登録ステータス');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('nmumeric'));
		$v->add_rule('numeric_min', 0);
		$v->add_rule('numeric_max', 1);

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


	public static function validation_for_get()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		# client_user_id
		$v = $obj_validate->add('user_id', 'クライアントユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# login_hash
/*
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('required');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash');
*/
		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}



	public static function set_dto_for_set()
	{
		\Log::debug('[start]'. __METHOD__);

		$favorite_dto = FavoriteUserDto::get_instance();
		$favorite_dto->set_client_user_id(trim(static::$_obj_request->client_user_id));
		$favorite_dto->set_favorite_user_id(trim(static::$_obj_request->favorite_user_id));
		$favorite_dto->set_status(static::$_obj_request->status);

		return true;
	}


	public static function set_dto_for_get()
	{
		\Log::debug('[start]'. __METHOD__);

		$favorite_dto = FavoriteUserDto::get_instance();
		$favorite_dto->set_client_user_id(trim(static::$_obj_request->user_id));
		$favorite_dto->set_offset(trim(static::$_obj_request->offset));
		$favorite_dto->set_limit(trim(static::$_obj_request->limit));

		return true;
	}


	public static function set_favorite_user()
	{
		\Log::debug('[start]'. __METHOD__);

		$favorite_dao = new FavoriteUserDao();
		$favorite_dto = FavoriteUserDto::get_instance();
		$favorite_user_id = $favorite_dto->get_favorite_user_id();
		$client_user_id = $favorite_dto->get_client_user_id();
		switch ($favorite_dto->get_status())
		{
			case 0:
				$favorite_dao->unset_favorite_user($favorite_user_id, $client_user_id);
				break;
			case 1:
				$favorite_dao->set_favorite_user($favorite_user_id, $client_user_id);
				break;
		}

		return true;
	}


	public static function get_favorite_user_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$favorite_dao = new FavoriteUserDao();
		$favorite_dto = FavoriteUserDto::get_instance();
		$user_dto     = UserDto::get_instance();

		$arr_where = array(
			'client_user_id' => $user_dto->get_user_id(),
		);

		$arr_result = array();
		foreach ($favorite_dao->get_favorite_users($arr_where) as $i => $val)
		{
			$arr_result[$val['favorite_user_id']] = $val['favorite_user_id'];
		}

		$favorite_dto->set_arr_favorite_users($arr_result);

		return true;
	}


	public static function get_favorite_users()
	{
		\Log::debug('[start]'. __METHOD__);

		$favorite_dao = new FavoriteUserDao();
		$favorite_dto = FavoriteUserDto::get_instance();
		$user_dto     = UserDto::get_instance();

		$arr_where = array(
			'client_user_id' => $favorite_dto->get_client_user_id(),
		);

		$arr_result = array();
		foreach ($favorite_dao->get_favorite_users($arr_where) as $i => $val)
		{
			$arr_result[$val['favorite_user_id']] = $val['favorite_user_name'];
		}

		$favorite_dto->set_arr_favorite_users($arr_result);

		return true;
	}

}