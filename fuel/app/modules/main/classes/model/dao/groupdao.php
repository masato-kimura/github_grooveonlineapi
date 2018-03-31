<?php
namespace main\model\dao;

use main\model\dto\GroupDto;
use main\model\dto\GroupRelationDto;

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
class GroupDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_group';
	}

	/**
	 *
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function create_group()
	{
		$group_dto = GroupDto::get_instance();
		$group_relation_dto = GroupRelationDto::get_instance();

		$arr_profile = array(
			'name'           => $group_dto->get_name(),
			'category_id'    => $group_dto->get_category_id(),
			'link'           => $group_dto->get_link(),
			'profile_fields' => $group_dto->get_profile_fields(),
		);

		$result = $this->save($arr_profile);

		if (empty($result))
		{
			throw new \Exception('no return db_request', 8001);
		}

		$group_dto->set_id($result[0]);
		$group_relation_dto->set_group_id($result[0]);

		return $result;
	}

	/**
	 *
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function edit_group()
	{
		$group_dto = GroupDto::get_instance();

		$arr_profile = array(
			'name' => $group_dto->get_name(),
			'category_id' => $group_dto->get_category_id(),
			'link' => $group_dto->get_link(),
			'profile_fields' => $group_dto->get_profile_fields(),
		);

		$arr_where = array(
			'id' => $group_dto->get_id(),
			'is_deleted' => '0',
		);

		$result = $this->update($arr_profile, $arr_where);

		if (empty($result))
		{
			throw new \Exception('no return db_request', 8002);
		}

		return $result;
	}

	public function get_group()
	{
		$group_dto = GroupDto::get_instance();
		$arr_where = array('id' => $group_dto->get_id());

		return $this->search_one($arr_where, array(), array(), $group_dto);
	}
}