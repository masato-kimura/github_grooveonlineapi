<?php
namespace main\model\dao;

use main\model\dto\CommentDto;
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
class CommentStatusDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_comment_status';
	}


	public function set_information()
	{
		\Log::debug('[start]'. __METHOD__);

		$comment_dto = CommentDto::get_instance();

		$arr_values = array(
			'comment_id' => $comment_dto->get_comment_id(),
			'review_id'  => $comment_dto->get_review_id(),
			'about'      => $comment_dto->get_about(),
		);
		if ($comment_dto->get_review_user_id() === $comment_dto->get_comment_user_id())
		{
			$arr_values['is_self'] = true;
		}

		list($id, $count) = $this->save($arr_values);

		return $id;
	}


	public function delete_information()
	{
		\Log::debug('[start]'. __METHOD__);

		$comment_dto = CommentDto::get_instance();

		$arr_where = array(
			'comment_id' => $comment_dto->get_comment_id(),
		);

		$return = $this->delete(array(), $arr_where, false);

		return $return;
	}


	public function get_information($review_user_id, $offset=0, $limit=100)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_columns = array(
			array('i.id',              'information_id'),
			array('v.id',              'review_id'),
			array('v.about',           'about'),
			array('v.about_name',      'review_about_name'),
			array('c.id',              'comment_id'),
			array('c.comment_user_id', 'comment_user_id'),
			array('u.user_name',       'comment_user_name'),
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'i'));
		$query->join(array('trn_comment', 'c'));
		$query->on('i.comment_id', '=', 'c.id');
		$query->join(array('trn_user', 'u'));
		$query->on('c.comment_user_id', '=', 'u.id');
		$query->join(array('view_review_music', 'v'));
		$query->on('c.review_id', '=', 'v.id');
		$query->on('c.about', '=', 'v.about');

		$query->where('v.user_id', '=', $review_user_id);

		$query->where('c.is_available', '=', '1');
		$query->where('i.is_deleted', '=', '0');
		$query->where('c.is_deleted', '=', '0');
		$query->where('u.is_deleted', '=', '0');

		$query->offset($offset);
		$query->limit($limit);

		$arr_result = $query->execute()->as_array();

		return $arr_result;
	}


	public function get_user_review_count()
	{
		\Log::debug('[start]'. __METHOD__);

		$comment_status_dao = new CommentStatusDao();

		$arr_columns = array(
			array(\DB::expr('count(c.review_user_id)'), 'cnt'),
			'c.review_user_id'
		);
		/*
		 * cs : trn_commnt_status
		 * c  : trn_comment
		*/
		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'cs'));
		$query->join(array('trn_comment', 'c'));
		$query->on('cs.comment_id', '=', 'c.id');

		$query->where('cs.is_read', '=', '0');
		$query->where('cs.is_self', '=', '0');
		$query->where('cs.is_deleted', '=', '0');
		$query->where('c.is_deleted', '=', '0');

		$query->group_by('c.review_user_id');

		return $query->execute()->as_array();
	}



}