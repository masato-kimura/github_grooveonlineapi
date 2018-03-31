<?php
namespace main\domain\service;

use main\model\dto\LoginDto;
use main\model\dto\GroupDto;
use main\model\dto\GroupRelationDto;
use main\model\dto\UserDto;
use main\model\dao\CategoryDao;
use main\model\dto\CategoryDto;
use main\model\dao\GroupDao;
use main\model\dao\GroupRelationDao;

/**
 * @author masato
 *
 */
class GroupService extends Service
{
	public static function validation_for_create()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		# group_name
		$v = $obj_validate->add('group_name', 'グループ名');
		$v->add_rule('required');
		$v->add_rule('max_length', '100');

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

		# arr_members
		$v = $obj_validate->add('arr_members', 'arr_members');
		$v->add_rule('required');

		# chief_user_id
		$v = $obj_validate->add('chief_user_id', 'チーフユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# category_id
		$v = $obj_validate->add('category_id', 'カテゴリID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# profie_fields
		$v = $obj_validate->add('profie_fields', 'プロフィール');
		$v->add_rule('required');
		$v->add_rule('max_length', '2000');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function set_dto_for_create()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto  = UserDto::get_instance();
		$login_dto = LoginDto::get_instance();
		$group_dto = GroupDto::get_instance();
		$group_relation_dto = GroupRelationDto::get_instance();

		foreach (static::$_obj_request as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}

			if ($key === 'group_name')
			{
				$group_dto->set_name(trim($val));
			}
			if ($key === 'user_id')
			{
				$user_dto->set_user_id(trim($val));
			}
			if ($key === 'login_hash')
			{
				$login_dto->set_login_hash(trim($val));
			}
			if ($key === 'arr_members')
			{
				$group_dto->set_members(trim($val));
				$group_relation_dto->set_arr_members(trim($val));
			}
			if ($key === 'chief_user_id')
			{
				$group_relation_dto->set_chief_user_id(trim($val));
			}
			if ($key === 'cateogry_id')
			{
				$group_dto->set_category_id(trim($val));
			}
			if ($key === 'profile_fields')
			{
				$group_dto->set_profile_fields(trim($val));
			}
		}

		return true;
	}



	public static function set_group_dto_from_request($obj_request)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$group_dto = GroupDto::get_instance();

		foreach ($obj_request as $key => $str)
		{
			if (empty($str)) continue;
			if ($key === 'group_id') $group_dto->set_id($str);
			if ($key === 'group_name') $group_dto->set_name(trim($str));
			if ($key === 'profile_fields') $group_dto->set_profile_fields(trim($str));
			if ($key === 'category_id') $group_dto->set_category_id(trim($str));
			if ($key === 'link') $group_dto->set_link(trim($str));
		} // endforeach

		return true;
	}

	public static function set_group_relation_dto_from_request($obj_request)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$group_relation_dto = GroupRelationDto::get_instance();

		foreach ($obj_request as $key => $str)
		{
			if (empty($str)) continue;
			if ($key === 'group_id') $group_relation_dto->set_group_id($str);
			if ($key === 'user_id') $group_relation_dto->set_user_id($str);
			if ($key === 'chief_user_id') $group_relation_dto->set_chief_user_id($str);
			if ($key === 'arr_members') $group_relation_dto->set_arr_members($str);
		} // endforeach

		return true;
	}


	public static function validation_for_edit()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		//---------------------------
		// 必須項目エラーチェック
		//---------------------------
		$group_dto = GroupDto::get_instance();
		$group_relation_dto = GroupRelationDto::get_instance();
		$login_dto = LoginDto::get_instance();

		$group_id = $group_dto->get_id();
		$group_name = $group_dto->get_name();
		$profile_fields = $group_dto->get_profile_fields();
		$chief_user_id = $group_relation_dto->get_chief_user_id();
		$arr_members = $group_relation_dto->get_arr_members();
		$login_hash = $login_dto->get_login_hash();

		if (empty($group_id)) throw new \Exception('required error[group_id]', 7002);
		if (empty($group_name)) throw new \Exception('required error[group_name]', 7002);
		if (empty($login_hash)) throw new \Exception('required error[login_hash]', 7002);
		if (empty($arr_members)) throw new \Exception('required error[arr_members]', 7002);
		if (empty($chief_user_id)) throw new \Exception('required error[chief_user_id]', 7002);

		# login_hash , user_idの型チェック
		LoginService::validation_for_logined();

		return true;
	}


	public static function validation_for_us()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		//---------------------------
		// 必須項目エラーチェック
		//---------------------------
		$group_dto = GroupDto::get_instance();
		$user_dto   = UserDto::get_instance();
		$login_dto = LoginDto::get_instance();

		$group_id   = $group_dto->get_id();
		$user_id    = $user_dto->get_id();
		$login_hash = $login_dto->get_login_hash();

		if (empty($group_id)) throw new \Exception('required error[group_id]', 7002);
		if (empty($user_id)) throw new \Exception('required error[user_id]', 7002);
		if (empty($login_hash)) throw new \Exception('required error[login_hash]', 7002);

		# login_hash , user_idの型チェック
		LoginService::validation_for_logined();

		return true;
	}

	public static function validation_for_memberadd()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		//---------------------------
		// 必須項目エラーチェック
		//---------------------------
		$group_dto = GroupDto::get_instance();
		$group_relation_dto = GroupRelationDto::get_instance();
		$user_dto   = UserDto::get_instance();
		$login_dto = LoginDto::get_instance();

		$group_id   = $group_dto->get_id();
		$user_id    = $user_dto->get_id();
		$login_hash = $login_dto->get_login_hash();
		$arr_members = $group_relation_dto->get_arr_members();

		if (empty($group_id)) throw new \Exception('required error[group_id]', 7002);
		if (empty($user_id)) throw new \Exception('required error[user_id]', 7002);
		if (empty($login_hash)) throw new \Exception('required error[login_hash]', 7002);
		if (empty($arr_members)) throw new \Exception('required error[$arr_members]', 7002);

		# login_hash , user_idの型チェック
		LoginService::validation_for_logined();

		return true;
	}

	public static function validation_for_isinvited()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		//---------------------------
		// 必須項目エラーチェック
		//---------------------------
		$group_dto = GroupDto::get_instance();
		$user_dto   = UserDto::get_instance();

		$group_id   = $group_dto->get_id();
		$invited_by = $user_dto->get_invited_by();
		$invite_user_id  = $user_dto->get_invite_id();
		$target_user_id  = $user_dto->get_target_id();

		if (empty($group_id)) throw new \Exception('required error[group_id]', 7002);
		if (empty($invited_by)) throw new \Exception('required error[invited_by]', 7002);
		if (empty($invite_user_id)) throw new \Exception('required error[invite_id]', 7002);
		if (empty($target_user_id)) throw new \Exception('required error[target_id]', 7002);

		if ($invited_by != 'group')
		{
			throw new \Exception('type error[invited_by]', 7003);
		}

		return true;
	}


	public static function insert_group()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_group_dao = new GroupDao();

		return $obj_group_dao->create_group();
	}

	public static function edit_group()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$obj_group_dao = new GroupDao();

		return $obj_group_dao->edit_group();
	}


	public static function insert_group_relation()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$group_dto          = GroupDto::get_instance();
			$group_relation_dto = GroupRelationDto::get_instance();
			$obj_group_relation_dao = new GroupRelationDao();

			$obj_group_relation_dao->start_transaction();

			foreach ($group_relation_dto->get_arr_members() as $i => $member)
			{
				$arr_detail = array();
				if ($member->user_id === $group_relation_dto->get_chief_user_id())
				{
					$arr_detail['chief_flag'] = true;
				}
				$arr_detail['user_id']  = $member->user_id;
				$arr_detail['group_id'] = $group_dto->get_group_id();

				$obj_group_relation_dao->insert_group_member($arr_detail);
			}

			$obj_group_relation_dao->commit_transaction();

			return true;
		}
		catch (\Exception $e)
		{
			if ($obj_group_relation_dao->in_transaction())
			{
				$obj_group_relation_dao->rollback_transaction();
			}
			throw new \Exception($e);
		}
	}

	public static function get_group_info()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$obj_group_dao = new GroupDao();

		return $obj_group_dao->get_group();
	}

	public static function get_group_info_from_user_id()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$obj_group_relation_dao = new GroupRelationDao();

		return $obj_group_relation_dao->get_group_from_user_id();
	}

	public static function get_category_info()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$group_dto = GroupDto::get_instance();
		$category_dto = CategoryDto::get_instance();
		$category_dto->set_id($group_dto->get_category_id());

		$obj_category_dao = new CategoryDao();

		return $obj_category_dao->get_category_by_id();
	}

	public static function get_members()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$group_dto =GroupDto::get_instance();
		$group_relation_dto = GroupRelationDto::get_instance();
		$group_relation_dto->set_group_id($group_dto->get_id());

		$obj_group_relation_dao = new GroupRelationDao();

		return $obj_group_relation_dao->get_group_member();
	}

	/**
	 * 招待ユーザが招待主とともにグループに存在するかをチェック
	 */
	public static function is_enabled_unregisted_user_by_group()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$group_relation_dao = new GroupRelationDao();

		return $group_relation_dao->is_invited_user_in_group();
	}

	/**
	 * ユーザが全グループ内または特定グループ内に存在するかをチェック
	 *
	 * @throws \Exception
	 * @return boolean
	 */
	public static function is_authorized_user_in_group()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$group_relation_dto = GroupRelationDto::get_instance();

		$obj_group_relation_dao = new GroupRelationDao();
		$arr_result = $obj_group_relation_dao->get_group_from_user_id();

		if (empty($arr_result))
		{
			throw new \Exception('this user not belong group anywhere', 7020);
		}

		$is_exist = false;
		foreach ($arr_result as $i => $obj_result)
		{
			if ($obj_result->group_id == $group_relation_dto->get_group_id())
			{
				$is_exist = true;
			}
		}
		if ($is_exist === false)
		{
			throw new \Exception('this user not belog this group : '.
				$group_relation_dto->get_group_id(), 7021);
		}

		return true;
	}

	public static function member_add()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$group_relation_dao = new GroupRelationDao();

		return $group_relation_dao->member_add();
	}


	public static function member_delete(array $arr_member_id)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$group_relation_dao = new GroupRelationDao();

		return $group_relation_dao->member_delete($arr_member_id);
	}
}