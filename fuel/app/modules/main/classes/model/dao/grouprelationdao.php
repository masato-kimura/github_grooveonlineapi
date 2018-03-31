<?php
namespace main\model\dao;

use main\model\dto\UserDto;
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
class GroupRelationDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;
	protected $_group_table_name;
	protected $_category_table_name;
	protected $_user_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_group_relation';
		$this->_group_table_name = 'trn_group';
		$this->_category_table_name = 'mst_category';
		$this->_user_table_name = 'trn_user';
	}

	/**
	 *
	 * @param array $arr_profile
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function insert_group_member(array $arr_group_member)
	{
		$result = $this->save($arr_group_member);
		if (empty($result))
		{
			throw new \Exception('no return db_request', 8001);
		}
		return $result;
	}

	public function get_group_member()
	{
		$group_dto = GroupDto::get_instance();
		$arr_where = array(
			'group_id' => $group_dto->get_id(),
			'is_deleted' => 0,
		);

		/*
		 * tgl  : trn_group_relation
		 * tur  : trn_user
		 */
		$arr_columns = array(
			'user_id',
			'user_name',
			'first_name',
			'last_name',
			'member_type',
			'is_decided',
		);
		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'tgl'));
		$query->join(array($this->_user_table_name, 'tur'));
		$query->on('tgl.user_id', '=', 'tur.id');
		$query->where('tgl.group_id', '=', $group_dto->get_id());
		$query->where('tgl.is_deleted', '=', 0);
		$query->where('tur.is_deleted', '=', 0);
		$query->where('tur.is_leaved', '=', 0);

		$obj_return = $query->as_object()->execute();

		return $obj_return;
	}

	/**
	 *
	 * @return stdClass object
	 */
	public function get_group()
	{
		$group_dto = GroupDto::get_instance();

		/*
		 * tgl:  trn_group_relation
		 * tg :  trn_group
		 * mct:  mst_category
		 */
		$arr_columns = array(
			'tgl.user_id',
			'tgl.group_id',
			'tgl.chief_flag',
			array('tg.name', 'group_name'),
			array('tg.link', 'group_link'),
			array('tg.profile_fields', 'group_profile_fields'),
			array('mct.name', 'category_name'),
			array('mct.english', 'category_english'),
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'tgl'));
		$query->join(array($this->_group_table_name, 'tg'));
		$query->on('tgl.group_id', '=', 'tg.id');
		$query->join(array($this->_category_table_name, 'mct'));
		$query->on('tg.category_id', '=', 'mct.id');
		$query->where('tgl.id', '=', $group_dto->get_id());
		$query->where('tgl.is_deleted', '=', 0);
		$query->where('tg.is_deleted', '=', 0);
		$query->where('tg.is_leaved', '=', 0);
		$query->where('mct.is_deleted', '=', 0);
		$query->where('mct.is_enabled', '=', 1);

		$obj_return = $query->as_object()->execute();

		return $obj_return;
	}





	/**
	 *
	 * @return stdClass object
	 */
	public function get_group_from_user_id()
	{
		$user_dto = UserDto::get_instance();

		/*
		 * tgl:  trn_group_relation
		 * tg :  trn_group
		 * mct:  mst_category
		 */
		$arr_columns = array(
			'tgl.user_id',
			'tgl.group_id',
			'tgl.chief_flag',
			array('tg.name', 'group_name'),
			array('tg.link', 'group_link'),
			array('tg.profile_fields', 'group_profile_fields'),
			array('mct.name', 'category_name'),
			array('mct.english', 'category_english'),
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'tgl'));
		$query->join(array($this->_group_table_name, 'tg'));
		$query->on('tgl.group_id', '=', 'tg.id');
		$query->join(array($this->_category_table_name, 'mct'));
		$query->on('tg.category_id', '=', 'mct.id');
		$query->where('tgl.user_id', '=', $user_dto->get_id());
		$query->where('tgl.is_deleted', '=', 0);
		$query->where('tg.is_deleted', '=', 0);
		$query->where('tg.is_leaved', '=', 0);
		$query->where('mct.is_deleted', '=', 0);
		$query->where('mct.is_enabled', '=', 1);

		$obj_return = $query->as_object()->execute();

		return $obj_return;
	}

	public function member_add()
	{
		$group_relation_dto = GroupRelationDto::get_instance();

		foreach ($group_relation_dto->get_arr_members() as $i => $_arr_member)
		{
			$arr_columns = array(
				'user_id'  => $_arr_member->user_id,
				'group_id' => $group_relation_dto->get_group_id(),
			);

			$result = $this->save($arr_columns);
			if (empty($result))
			{
				throw new \Exception('no return db_request', 8002);
			}
		}
	}

	public function member_delete(array $arr_member_id)
	{
		$group_relation_dto = GroupRelationDto::get_instance();

		$query = \DB::update($this->_table_name);
		$query->value('is_deleted', '1');
		$query->where('user_id', 'in', $arr_member_id);
		$query->where('group_id', '=', $group_relation_dto->get_group_id());
		$query->where('is_deleted', '=', '0');

		return $query->execute();
	}

	/**
	 * 招待ユーザが有効であることを確認
	 */
	public function is_invited_user_in_group()
	{
		$user_dto = UserDto::get_instance();
		$group_dto = GroupDto::get_instance();

		$arr_where = array(
			'group_id' => $group_dto->get_id(),
			'user_id' => $user_dto->get_invite_id(),
		);
		$arr_result_invite = $this->search_one($arr_where, array('id'));
		if (empty($arr_result_invite))
		{
			throw new \Exception('not exist invite_user in group');
		}

		$arr_where = array(
			'group_id' => $group_dto->get_id(),
			'user_id'  => $user_dto->get_target_id(),
		);
		$arr_result_target = $this->search_one($arr_where, array('id'));
		if (empty($arr_result_target))
		{
			throw new \Exception('not exist target_user in group');
		}

		return true;
	}
}