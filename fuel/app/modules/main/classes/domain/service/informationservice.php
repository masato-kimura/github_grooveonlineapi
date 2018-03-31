<?php
namespace main\domain\service;

use Fuel\Core\Validation;
use main\model\dao\FavoriteUserDao;
use main\model\dao\CommentStatusDao;
use main\model\dao\UserInformationDao;
use main\model\dao\GlobalInformationDao;
use main\model\dto\FavoriteUserDto;
use main\model\dto\UserDto;
use main\model\dto\UserInformationDto;
use main\model\dto\GlobalInformationDto;
/**
 * プライマリーのidは基本使用しない
 * @author masato
 *
 */
class InformationService extends Service
{

	public static function validation_for_getreview()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		# client_user_id
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# login_hash
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('required');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash');

		# バリデート実行
		$arr_params = array(
			'user_id'    => static::$_obj_request->user_id,
			'login_hash' => static::$_obj_request->login_hash,
			'api_key'    => static::$_obj_request->api_key,
		);
		static::_validate_run($obj_validate, $arr_params);

		return true;
	}


	public static function validation_for_getuserinformation()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		# client_user_id
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# login_hash
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('required');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash', static::$_obj_request->user_id);

		# バリデート実行
		$arr_params = array(
			'user_id'    => static::$_obj_request->user_id,
			'login_hash' => static::$_obj_request->login_hash,
			'api_key'    => static::$_obj_request->api_key,
		);
		static::_validate_run($obj_validate, $arr_params);

		return true;
	}


	public static function validation_for_getglobalinformation()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		# オフセット
		$v = $obj_validate->add('offset', 'オフセット');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# リミット
		$v = $obj_validate->add('limit', 'リミット');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# バリデート実行
		$arr_params = array(
			'offset'    => isset(static::$_obj_request->offset)? static::$_obj_request->offset: null,
			'limit'     => isset(static::$_obj_request->limit)? static::$_obj_request->limit: null,
			'api_key'   => static::$_obj_request->api_key,
		);
		static::_validate_run($obj_validate, $arr_params);

		return true;
	}


	public static function set_dto_for_getreview()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$user_dto->set_user_id(trim(static::$_obj_request->user_id));

		return true;
	}


	public static function set_dto_for_getuserinformation()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$information_dto = UserInformationDto::get_instance();

		$user_dto->set_user_id(trim(static::$_obj_request->user_id));
		$information_dto->set_user_id(trim(static::$_obj_request->user_id));

		return true;
	}


	public static function set_dto_for_getglobalinformation()
	{
		\Log::debug('[start]'. __METHOD__);

		$global_information_dto = GlobalInformationDto::get_instance();

		$offset = isset(static::$_obj_request->offset)? trim(static::$_obj_request->offset): 0;
		$limit  = isset(static::$_obj_request->limit)? trim(static::$_obj_request->limit): 10;
		$global_information_dto->set_offset($offset);
		$global_information_dto->set_limit($limit);

		return true;
	}



	public static function set_dto_for_getuserreviewcount()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_information_dto = UserInformationDto::get_instance();
		$user_information_dto->set_offset(0);
		$user_information_dto->set_limit(1000000);

		return true;
	}


	public static function get_user_review_count()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_information_dto = UserInformationDto::get_instance();
		$comment_status_dao = new CommentStatusDao();
		$arr_result = $comment_status_dao->get_user_review_count();
		$user_information_dto->set_arr_list($arr_result);

		return true;
	}

	public static function set_user_review_count()
	{
		\Log::debug('[start]'. __METHOD__);

		$information_dto = UserInformationDto::get_instance();
		$informartion_dao = new UserInformationDao();

		$arr_result = $information_dto->get_arr_list();
		foreach ($arr_result as $i => $val)
		{
			$arr_where = array('user_id' => $val['review_user_id']);
			if ($informartion_dao->search_one($arr_where))
			{
				// update
				$informartion_dao->update(array('comment_count' => $val['cnt'], 'is_read' => '0'), array('user_id' => $val['review_user_id']));
			}
			else
			{
				// insert
				$informartion_dao->save(array(
					'user_id'       => $val['review_user_id'],
					'comment_count' => $val['cnt'],
				));
			}
		}

		return true;
	}


	public static function get_user_information()
	{
		\Log::debug('[start]'. __METHOD__);

		$information_dto = UserInformationDto::get_instance();
		$user_dto = UserDto::get_instance();

		$information_dao = new UserInformationDao();
		$arr_result = $information_dao->search_one(array(
			'user_id' => $user_dto->get_user_id(),
			'is_read' => '0',
		));
		if ( ! empty($arr_result))
		{
			// DTOにセット
			$information_dto->set_comment_count($arr_result->comment_count);
			$information_dto->set_user_id($arr_result->user_id);
			$information_dto->set_updated_at($arr_result->updated_at);
		}

		return true;
	}


	public static function get_global_information()
	{
		\Log::debug('[start]'. __METHOD__);

		$information_dto = GlobalInformationDto::get_instance();

		$global_information_dao = new GlobalInformationDao();

		$arr_where   = array();

		$arr_count = $global_information_dao->search_one(array(), array(array(\DB::expr('count(id)'), 'cnt')));

		$arr_columns = array(
			'id', 'date', 'comment'
		);
		$arr_result = $global_information_dao->search_offset($arr_where, $arr_columns, array('date' => 'DESC'), $information_dto->get_offset(), $information_dto->get_limit());
		if ( ! empty($arr_result))
		{
			// DTOにセット
			$information_dto->set_count($arr_count->cnt);
			$information_dto->set_arr_list($arr_result);
		}

		return true;
	}

	public static function getreview()
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


}