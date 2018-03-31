<?php
namespace main\model\dao;

use main\model\dto\PasswordreissueDto;
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
class PasswordreissueDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_password_reissue';
	}


	/**
	 * 仮パスワード格納テーブルへインサート
	 * @throws \Exception
	 * @return boolean
	 */
	public function set_tentative_password_reissue()
	{
		\Log::debug('[start]'. __METHOD__);

		$password_reissue_dto = PasswordreissueDto::get_instance();

		$arr_params = array(
			'email'              => $password_reissue_dto->get_email(),
			'tentative_password' => $password_reissue_dto->get_tentative_password(),
		);

		$result = $this->save($arr_params);

		if (empty($result))
		{
			throw new \Exception('DB::can not password reissue', 8001); // DBエラー
		}

		$password_reissue_dto->set_id($result[0]);

		return true;
	}

	public function is_exist_email($isset_dto=false)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$password_reissue_dto  = \main\model\dto\PasswordreissueDto::get_instance();

		$arr_where = array(
				'email' => $password_reissue_dto->get_email(),
				'tentative_password' => $password_reissue_dto->get_tentative_password(),
		);

		if (empty($isset_dto))
		{
			$obj_result = $this->search_one($arr_where);
		}
		else
		{
			$obj_result = $this->search_one($arr_where, array(), array(), $password_reissue_dto);
		}

		if (empty($obj_result))
		{
			throw new \Exception('can not email reissue', 8006);
		}

		return true;
	}

	public function is_exist_email_by_password_reissue_dto()
	{
		$password_reissue_dto = \main\model\dto\PasswordreissueDto::get_instance();
		$email = $password_reissue_dto->get_email();
		$id = $password_reissue_dto->get_id();
		$tentative_password = $password_reissue_dto->get_tentative_password();
		if (empty($email)) return false;
		if (empty($id)) return false;
		if (empty($tentative_password)) return false;
		$arr_result = $this->search(array(
				'email' => $password_reissue_dto->get_email(),
				'id' => $password_reissue_dto->get_id(),
				'tentative_password' => $password_reissue_dto->get_tentative_password(),
			)
		);
		if ( ! empty($arr_result)) return true;
		return false;
	}


	/**
	 * リクエストされたemailが存在するか（パスワードでは照合しない）
	 * @return boolean
	 */
	public function is_exist_valid_email($enabled_before_min=null)
	{
		\Log::debug('[start]'. __METHOD__);

		$password_reissue_dto = PasswordreissueDto::get_instance();
		$email = $password_reissue_dto->get_email();

		if (empty($email))
		{
			return false;
		}

		$query = \DB::select();
		$query->from($this->_table_name);
		$query->where('email', '=', $password_reissue_dto->get_email());

		if (isset($enabled_before_min))
		{
			$obj_date = new \DateTime();
			$obj_date->add(\DateInterval::createFromDateString('-'. $enabled_before_min. ' min'));
			$enabled_before_datetime = $obj_date->format('Y-m-d H:i:s');
			$now_datetime = \Date::forge()->format('%Y-%m-%d %H:%M:%S');
			$query->where('created_at', 'between', array($enabled_before_datetime, $now_datetime));
		}
		$query->where('is_deleted', '=', 0);
\Log::info($query->compile());
		$arr_result = $query->execute()->as_array();
		if ( ! empty($arr_result))
		{
			return true;
		}

		return false;
	}


	public function logical_delete()
	{
		\Log::debug('[start]'. __METHOD__);

		$password_reissue_dto  = PasswordreissueDto::get_instance();

		$arr_values = array(
			'is_deleted' => 1
		);

		$arr_where = array(
			'email' => $password_reissue_dto->get_email(),
			'tentative_password' => $password_reissue_dto->get_tentative_password(),
		);

		return $this->update($arr_values, $arr_where);
	}

}