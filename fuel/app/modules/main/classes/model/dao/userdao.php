<?php
namespace main\model\dao;

use main\model\dto\UserDto;


/**
 * @throws \Exception
 *  1001 正常
 *  9001
 * @author masato
 *
 */
class UserDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_user';
	}

	/**
	 *
	 * @param array $arr_profile
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function set_profile(array $arr_request)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_values = array();
		foreach ($arr_request as $i => $val)
		{
			if ( ! is_null($val))
			{
				$arr_values[$i] = $val;
			}
		}

		list($key, $count) = $this->save($arr_values);
		if (empty($count))
		{
			return false;
		}
		return array($key, $count);
	}


	/**
	 * 対象のuser_idのログイン記録をセットします
	 * @param unknown $user_id
	 * @throws \Exception
	 * @return boolean
	 */
	public function update_last_login()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = \main\model\dto\UserDto::get_instance();
		$arr_where = array(
				'id' => $user_dto->get_user_id(),
				'auth_type' => $user_dto->get_auth_type(),
		);
		$arr_value = array(
				'last_login' => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
				'updated_at' => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
		);
		$result = $this->update($arr_value, $arr_where);
		if ($result === false)
		{
			throw new \Exception('該当するユーザは存在しません', 9001);
		}
		return true;
	}


	/**
	 * 対象のuser_idのログアウト記録をセットします
	 * @param unknown $user_id
	 * @throws \Exception
	 * @return boolean
	 */
	public function update_last_logout()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$arr_where  = array(
			'id' => $user_dto->get_user_id(),
		);
		$arr_values = array(
			'last_logout' => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
			'updated_at'  => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
		);
		$result = $this->update($arr_values, $arr_where);
		if ($result === false)
		{
			throw new \Exception('該当するユーザは存在しません', 9001);
		}
		return true;
	}


	/**
	 * ユーザ情報を更新する
	 * @param array $arr_profile
	 * @param array $arr_where
	 * @return Ambigous <boolean, unknown>
	 */
	public function update_profile(array $arr_profile, array $arr_where=array())
	{
		\Log::debug('[start]'. __METHOD__);

		$affected_row = $this->update($arr_profile, $arr_where);

		return $affected_row;
	}



	/**
	 * idとauth_typeからユーザ情報を取得する
	 * 退会済みのレコードも取得する。
	 * @return boolean & UserDto
	 */
	public function get_userinfo_by_user_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$id = $user_dto->get_user_id();

		if (empty($id)) return false;

		$arr_where = array(
			'id' => $user_dto->get_user_id(),
		);

		$obj_result = $this->search_one($arr_where, '', '', $user_dto);

		if (empty($obj_result))
		{
			return false;
		}

		return $obj_result;
	}

	/**
	 * idとauth_typeからユーザ情報を取得する
	 * 退会済みのレコードも取得する。
	 * @return boolean
	 */
	public function is_exist_user_by_user_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		$id = $user_dto->get_user_id();

		if (empty($id)) return false;

		$arr_where = array(
			'id'         => $user_dto->get_user_id(),
		);

		$obj_result = $this->search_one($arr_where);

		if (empty($obj_result))
		{
			return false;
		}

		return true;
	}


	/**
	 * user_id, auth_type, email & password or oauth_id でユーザ情報を取得する
	 */
	public function get_userinfo_for_decide()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		if ($user_dto->get_invited_by() === 'group')
		{
			$arr_where = array(
					'id'          => $user_dto->get_user_id(),
					'member_type' => '0',
			);
		}
		else
		{
			$arr_where = array(
					'id'         => $user_dto->get_user_id(),
					'auth_type'  => $user_dto->get_auth_type(),
					'is_leaved'  => '0',
					'is_decided' => '0',
			);
		}

		$obj_result = $this->search_one($arr_where);

		return $obj_result;
	}

	/**
	 * user_id, auth_type, email & password or oauth_id でユーザ情報を取得する
	 */
	public function get_userinfo_for_edit()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		if ($user_dto->get_invited_by() === 'group')
		{
			$arr_where = array(
				'id' => $user_dto->get_user_id(),
				'member_type' => '0',
			);
		}
		else
		{
			$arr_where = array(
				'id'        => $user_dto->get_user_id(),
				'auth_type' => $user_dto->get_auth_type(),
			);
		}

		$obj_result = $this->search_one($arr_where);

		return $obj_result;
	}


	public function get_available_grooveonline_user($user_id, $email, $password)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_where = array(
			'id'  => $user_id,
			'email'    => $email,
			'password' => $password,
			'is_decided' => 1,
			'is_leaved'  => 0,
			'auth_type' => 'grooveonline',
		);

		return $this->search_one($arr_where);
	}


	/**
	 * user_idでユーザ情報を取得する
	 */
	public function get_user_info_by_user_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$arr_where = array(
			'id' => $user_dto->get_user_id(),
		);

		return $obj_result = $this->search_one($arr_where, '', '', $user_dto);
	}


	public function get_userinfo_by_email()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		$email     = $user_dto->get_email();
		$auth_type = $user_dto->get_auth_type();
		if (empty($email)) return false;
		if (empty($auth_type)) return false;

		$arr_where = array(
			'email'      => $user_dto->get_email(),
			'auth_type'  => $auth_type,
			'is_decided' => '1',
		);

		$obj_result = $this->search_one($arr_where);
		if (empty($obj_result))
		{
			return false;
		}

		return $obj_result;
	}


	public function get_userinfo_by_email_and_password()
	{
		\Log::debug('[start]'. __METHOD__);

		$dto = UserDto::get_instance();

		$email = $dto->get_email();
		$password = $dto->get_password();
		$auth_type = $dto->get_auth_type();
		if (empty($email)) return false;
		if (empty($password)) return false;
		if (empty($auth_type)) return false;

		$arr_where = array(
				'email'      => $dto->get_email(),
				'password'   => $dto->get_password(),
				'auth_type'  => 'grooveonline',
				'is_decided' => '1',
		);

		$obj_result = $this->search_one($arr_where,'', '', $dto);
		if (empty($obj_result))
		{
			return false;
		}

		return $obj_result;
	}


	public function get_userinfo_by_oauth()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		$auth_type = $user_dto->get_auth_type();
		$oauth_id  = $user_dto->get_oauth_id();
		if (empty($auth_type)) return false;
		if (empty($oauth_id)) return false;

		$arr_where = array(
			'oauth_id'  => $user_dto->get_oauth_id(),
			'auth_type' => $user_dto->get_auth_type(),
		);

		$id = $user_dto->get_user_id();
		if ( ! empty($id))
		{
			$arr_where['id'] = $user_dto->get_user_id();
		}
		$obj_result = $this->search_one($arr_where, '', array('id' => 'DESC'), $user_dto);

		if (empty($obj_result))
		{
			return false;
		}
		return $obj_result;
	}


	/**
	 * すでにemailが登録されているかとチェックします。
	 * 登録済み: true,  未登録: false
	 * @param array $arr_request['email']
	 * @return boolean
	 */
	public function is_exist_password_reissue_email()
	{
		\Log::debug('[start]'. __METHOD__);

		$auth_type = 'grooveonline';

		$password_reissue_dto = \main\model\dto\PasswordreissueDto::get_instance();
		$email = $password_reissue_dto->get_email();
		if (empty($email)) return false;
		$arr_result = $this->search(array(
				'email'      => $password_reissue_dto->get_email(),
				'auth_type'  => $auth_type,
				'is_decided' => '1',
			)
		);
		if ( ! empty($arr_result)) return true;
		return false;
	}


	public function is_exist_oauth_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = \main\model\dto\UserDto::get_instance();
		$oauth_id = $user_dto->get_oauth_id();
		$auth_type = $user_dto->get_auth_type();
		if (empty($oauth_id)) return false;
		if (empty($auth_type)) return false;
		$arr_where = array(
			'oauth_id'   => $user_dto->get_oauth_id(),
			'auth_type'  => $user_dto->get_auth_type(),
			'is_decided' => '1',
		);
		$arr_result = $this->search();
		if ( ! empty($arr_result)) return true;
		return false;
	}


	/**
	 * emailがuserテーブルに存在することの確認
	 * @param isset_dto boolean true:dtoに結果が代入される
	 * @return boolean 存在時: true, 未存在時：false
	 */
	public function is_exist_email($ignore_user_id=null, $isset_dto=false)
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();

		$email     = $user_dto->get_email();
		$auth_type = $user_dto->get_auth_type();
		if (empty($email)) return false;
		if (empty($auth_type)) return false;

		$arr_where = array(
			'email'      => $user_dto->get_email(),
			'auth_type'  => $user_dto->get_auth_type(),
			'is_decided' => '1',
		);
		if (! empty($ignore_user_id))
		{
			$arr_where['id!'] = $ignore_user_id;
		}

		$arr_result = $this->search(
			$arr_where,
			array(),
			array(),
			$user_dto
		);

		if ( ! empty($arr_result))
		{
			return true;
		}

		return false;
	}
}