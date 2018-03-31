<?php
namespace main\model\dao;

use main\model\dto\UserCommentDto;
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
class UserCommentDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_user_comment';
	}


	public function set_user_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_comment_dto = UserCommentDto::get_instance();
		$arr_values = array(
			'user_id'      => $user_comment_dto->get_user_id(),
			'about'        => $user_comment_dto->get_about(),
			'priority'     => $user_comment_dto->get_priority(),
			'user_comment' => $user_comment_dto->get_user_comment(),
		);

		list($id, $count) = $this->save($arr_values);

		return $id;
	}


	public function remove_user_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_comment_dto = UserCommentDto::get_instance();
		$arr_where = array(
			'user_id' => $user_comment_dto->get_user_id(),
			'id'      => $user_comment_dto->get_user_comment_id(),
		);

		$return = $this->delete(array(), $arr_where, false);

		return $return;
	}


	public function get_user_comment($user_id, $about=null)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_where = array(
			'user_id' => $user_id,
		);
		$arr_columns = array(
			'id',
			'user_id',
			'priority',
			'about',
			'user_comment',
		);
		$arr_order = array(
			'priority' => 'ASC',
		);
		$arr_result = $this->search_limit($arr_where, $arr_columns, $arr_order, 0, 20);

		return $arr_result;
	}

}