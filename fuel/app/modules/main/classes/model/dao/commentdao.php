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
class CommentDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_comment';
	}


	public function set_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		$comment_dto = CommentDto::get_instance();
		$arr_values = array(
			'about'           => $comment_dto->get_about(),
			'review_id'       => $comment_dto->get_review_id(),
			'review_user_id'  => $comment_dto->get_review_user_id(),
			'comment_user_id' => $comment_dto->get_comment_user_id(),
			'comment'         => $comment_dto->get_comment(),
		);

		list($id, $count) = $this->save($arr_values);

		return $id;
	}

	public function delete_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		$comment_dto = CommentDto::get_instance();
		$arr_where = array(
			'id'              => $comment_dto->get_comment_id(),
			'comment_user_id' => $comment_dto->get_comment_user_id(),
			'is_available'    => true,
		);
		$arr_columns = array(
			'is_available' => false,
		);

		$return = $this->update($arr_columns, $arr_where, false);

		return $return;
	}


	public function get_comment_count($review_id, $about)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_columns = array(
				array(\DB::expr("count('c.id')"), 'cnt'),
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'c'));
		$query->join(array('trn_user', 'u'));
		$query->on('c.comment_user_id', '=', 'u.id');
		$query->where('c.review_id', '=', $review_id);
		$query->where('c.about', '=', $about);
		$query->where('c.is_available', '=', '1');
		$query->where('u.is_deleted', '=', '0');

		$arr_result = $query->execute()->current();

		return $arr_result;
	}


	public function get_comment_list($review_id, $about, $offset=0, $limit=10)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_columns = array(
				'c.id',
				'u.user_name',
				'c.review_id',
				'c.about',
				'c.review_user_id',
				'c.comment_user_id',
				'c.comment',
				'c.is_available',
				'c.updated_at',
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'c'));
		$query->join(array('trn_user', 'u'));
		$query->on('c.comment_user_id', '=', 'u.id');
		$query->where('c.review_id', '=', $review_id);
		$query->where('c.about', '=', $about);
		$query->where('c.is_available', '=', '1');
		$query->where('u.is_deleted', '=', '0');
		$query->order_by('c.id', 'DESC');
		$query->offset($offset);
		$query->limit($limit);

		$arr_result = $query->execute()->as_array();

		return $arr_result;
	}

}