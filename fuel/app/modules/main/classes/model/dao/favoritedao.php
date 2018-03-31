<?php
namespace main\model\dao;

use main\model\dto\LoginDto;
use main\model\dto\UserDto;
/**
 * @throws \Exception
 *  1001 正常
 *  8001 システムエラー
 *  8002 DBエラー
 *  9001
 *
 * @author masato
 *
 */
class FavoriteUserDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_login';
	}


	/**
	 *
	 * @param array $arr_profile
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function set_profile(array $arr_profile)
	{
		\Log::debug('[start]'. __METHOD__);

		$result = $this->save($arr_profile);
		if (empty($result))
		{
			throw new \Exception('no return db_request', 8002);
		}
		return $result;
	}


	public function set_login($login_hash=null)// 引数のlogin_hashはいずれdto化にすべし
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto  = UserDto::get_instance();
		$login_dto = LoginDto::get_instance();

		if (empty($login_hash))
		{
			if ($login_dto->get_login_hash())
			{
				$login_hash = $login_dto->get_login_hash();
			}
			else
			{
				throw new \GolException('リクエストにlogin_hashが設定されてません');
			}
		}

		$id = $user_dto->get_id();
		if (empty($id))
		{
			return false;
		}

		$remark = $login_dto->get_remark();
		if (empty($remark))
		{
			$remark = 'login';
		}
		else
		{
			$remark = $login_dto->get_remark();
		}

		$arr_params = array(
			'user_id'    => $user_dto->get_id(),
			'login_hash' => $login_hash,
			'remark'     => $remark,
		);

		$result = $this->save($arr_params);

		if (empty($result))
		{
			throw new \Exception('ログイン情報を登録できません', 8002); // DBエラー
		}
		else
		{
			// login_dtoにセット
			$login_dto = \main\model\dto\LoginDto::get_instance();
			$login_dto->set_user_id($arr_params['user_id']);
			$login_dto->set_login_hash($arr_params['login_hash']);
			$login_dto->set_remark($arr_params['remark']);
			return true;
		}
	}


	/**
	 * ログアウト処理を行う
	 * @return int 更新件数
	 */
	public function set_logout()
	{
		$dto = \main\model\dto\LoginDto::get_instance();
		$arr_values = array('is_deleted' => 1, 'remark' => 'logout');
		$arr_where  = array(
				'user_id' => $dto->get_user_id(),
				'login_hash' => $dto->get_login_hash(),
		);
		$result = $this->update($arr_values, $arr_where);

		return $result;
	}


	public function remove_login_hash_for_reflash()
	{
		$login_dto = LoginDto::get_instance();
		$arr_where = array(
				'login_hash' => $login_dto->get_login_hash(),
				'user_id' => $login_dto->get_user_id(),
		);
		$arr_values = array(
				'is_deleted' => 1,
				'remark' => 'reflesh',
		);

		return $this->update($arr_values, $arr_where);
	}


	public function set_login_hash_for_reflash($login_hash)
	{
		$login_dto = LoginDto::get_instance();
		$arr_params = array(
			'user_id' => $login_dto->get_user_id(),
			'login_hash' => $login_hash,
			'remark' => 'reflesh',
		);

		return $this->save($arr_params);
	}


	/**
	 * ユーザIDとログインハッシュが有効であることの確認
	 * @throws \Exception
	 * @return boolean
	 */
	public function check_user_login_hash()
	{
		$login_dto = LoginDto::get_instance();

		$arr_where = array(
			'user_id'    => $login_dto->get_user_id(),
			'login_hash' => $login_dto->get_login_hash(),
		);
		$result = $this->search($arr_where, array('id'));

		if (empty($result))
		{
			throw new \Exception('login hash error[unknown]'. $login_dto->get_login_hash(), 7010);
		}
		if (count($result) > 1)
		{
			throw new \Exception('login hash error[duplicate]'. $login_dto->get_login_hash(), 7010);
		}

		return true;
	}
}