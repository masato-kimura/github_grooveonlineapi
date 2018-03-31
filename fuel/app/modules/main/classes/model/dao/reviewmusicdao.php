<?php
namespace main\model\dao;


use main\model\dto\ReviewMusicDto;
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
class ReviewMusicDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;
	protected $_table_name_for_count;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'view_review_music';
		$this->_table_name_for_count = 'view_review_music_id';
	}


	public function get_count()
	{
		\Log::debug('[start]'. __METHOD__);

		$query = \DB::select(array(\DB::expr('count(r.id)'), 'cnt'));
		$query->from(array($this->_table_name_for_count, 'r'));
		$query->join(array('trn_user', 'u'));
		$query->on('r.user_id', '=', 'u.id');
		$query->on('u.is_leaved', '=', \DB::expr(0));
		$query->on('u.is_deleted', '=', \DB::expr(0));
		$arr_result = $query->execute()->current();
		$cnt = empty($arr_result['cnt'])? 0: $arr_result['cnt'];

		return $cnt;
	}


	public function get_review_list_count()
	{
		$review_music_dto = ReviewMusicDto::get_instance();
		$sql = $this->_getlist_subquery(true);
		$result = \DB::query($sql)->execute()->current();
		return current(array_values($result));
	}


	public function get_review_list()
	{
		$review_music_dto = ReviewMusicDto::get_instance();

		$arr_columns = array(
				'r.id',
				'r.about',
				'r.artist_id',
				'r.artist_name',
				'r.about_id',
				'r.about_name',
				'r.star',
				'r.cool_count',
				'r.comment_count',
				'r.review',
				'r.image_small',
				'r.image_medium',
				'r.image_large',
				'r.image_extralarge',
				'r.artist_image_medium',
				'r.user_id',
				'r.created_at',
				'r.updated_at',
				'r.user_name',
				'i.comment_id',
				'i.is_read', // 既読フラグ
		);
		$query = \DB::select_array($arr_columns);
		$query->from(\DB::expr("(". $this->_getlist_subquery(). ") as r"));
		$query->join(array('trn_comment_status', 'i'), 'LEFT');
		$query->on('r.id', '=', 'i.review_id');
		$query->on('r.about', '=', 'i.about');
		$query->on('i.is_self',    '=', \DB::expr('0'));
		$query->on('i.is_deleted', '=', \DB::expr('0'));

		return $query->execute()->as_array();
	}


	private function _getlist_subquery($is_count=false)
	{
		\Log::info('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();

		$page        = $review_music_dto->get_page();
		$limit       = $review_music_dto->get_limit();
		$offset      = $review_music_dto->get_offset();
		if (empty($offset))
		{
			$offset      = ($page - 1) * $limit;
		}
		if (empty($limit))
		{
			$limit = 10;
		}
		$about       = $review_music_dto->get_about();
		$about_id    = $review_music_dto->get_about_id();
		$search_word = $review_music_dto->get_search_word();
		$user_id     = $review_music_dto->get_review_user_id();
		$artist_id   = $review_music_dto->get_artist_id();
		if (empty($artist_id))
		{
			if ($about === 'artist_all')
			{
				$artist_id = $about_id;
				$about_id = null;
			}
		}

		if ($is_count)
		{
			$arr_columns = array(\DB::expr('count(r.id)', 'cnt'));
		}
		else
		{
			/**
			 *  r : view_review_music
			 *  u : trn_user
			 *  a : mst_artist
			 */
			$arr_columns = array(
				'r.id',
				'r.about',
				'r.artist_id',
				'r.artist_name',
				'r.about_id',
				'r.about_name',
				'r.star',
				'r.cool_count',
				'r.comment_count',
				'r.review',
				'r.image_small',
				'r.image_medium',
				'r.image_large',
				'r.image_extralarge',
				array('a.image_medium', 'artist_image_medium'),
				'r.user_id',
				'r.created_at',
				'r.updated_at',
				'u.user_name',
			);
		}

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('trn_user', 'u'), 'inner');
		$query->on('r.user_id', '=', 'u.id');
		$query->on('u.is_deleted', '=', \DB::expr(0));
		$query->on('u.is_leaved',  '=', \DB::expr(0));
		$query->join(array('mst_artist', 'a'));
		$query->on('a.id', '=', 'r.artist_id');

		if ( ! empty($search_word))
		{
			$query->and_where_open();
			$query->or_where('r.artist_name', 'like', $search_word. '%');
			$query->or_where('r.artist_name', 'like', 'the '. $search_word. '%');
			$query->or_where('r.about_name', 'like', $search_word. '%');
			$query->or_where('r.review', 'like', '%'. $search_word. '%');
			$query->or_where('u.user_name', 'like', '%'. $search_word. '%');
			$query->and_where_close();
		}
		if ( ! empty($user_id))
		{
			$query->where('r.user_id', '=', $user_id);
		}
		if ( ! empty($artist_id))
		{
			$query->where('r.artist_id', '=', $artist_id);
		}
		if ($about === 'artist_all' and ! empty($about_id))
		{
			$query->where('r.artist_id', $about_id);
		}
		if ( ! empty($about_id) and ! empty($about))
		{
			$query->where('r.about_id', '=', $about_id);
			if ($about !== 'artist_all')
			{
				$query->where('r.about', '=', $about);
			}
		}

		$query->where('a.is_deleted', '=', '0');

		if ( ! $is_count)
		{
			$query->order_by('r.created_at', 'DESC');
			$query->offset($offset);
			$query->limit($limit);
		}

		return $query->compile();
	}


	/**
	 * 全レビューからトップレビューを取得
	 */
	public function get_top_list()
	{
		$review_music_dto = ReviewMusicDto::get_instance();
		$offset      = $review_music_dto->get_offset();
		$limit       = $review_music_dto->get_count();
		$arr_columns = array(
				'r.id',
				'r.about',
				'r.artist_id',
				'r.artist_name',
				'r.about_id',
				'r.about_name',
				'r.star',
				'r.cool_count',
				'r.comment_count',
				'r.review',
				'r.image_small',
				'r.image_medium',
				'r.image_large',
				'r.image_extralarge',
				array('a.image_medium', 'artist_image_medium'),
				'r.user_id',
				'u.user_name',
				'c.aggregate',
				'r.created_at',
				'r.updated_at',
		);

		/*
		 * c : sub_query(trn_cool)
		 * r : view_review_music
		 * u : trn_user
		 * a : mst_artist
		 * cs: trn_comment_status
		 */
		$query = \DB::select_array($arr_columns);
		$query->from(\DB::expr("(". $this->get_top_list_subquery(). ") as c"));
		$query->join(array($this->_table_name, 'r'));
		$query->on('c.review_id', '=', 'r.id');
		$query->on('c.about', '=', 'r.about');
		$query->join(array('trn_user', 'u'), 'inner');
		$query->on('r.user_id', '=', 'u.id');
		$query->on('u.is_deleted', '=', \DB::expr(0));
		$query->on('u.is_leaved',  '=', \DB::expr(0));
		$query->join(array('mst_artist', 'a'));
		$query->on('r.artist_id', '=', 'a.id');
		$query->join(array('trn_comment_status', 'cs'), 'left');
		$query->on('r.id', '=', 'cs.review_id');
		$query->on('r.about', '=', 'cs.about');
		$query->on('cs.is_self', '=', \DB::expr('0'));
		$query->group_by('r.id', 'r.about');
		$query->order_by('cs.created_at', 'DESC');
		$query->order_by('r.cool_count', 'DESC');
		if ( ! empty($limit))
		{
			$query->offset($offset);
			$query->limit($limit);
		}

		return $query->execute()->as_array();
	}

	private function get_top_list_subquery()
	{
		\Log::debug('[start]'. __METHOD__);

		$column = '
			c0.about,
			c0.review_id,
			sum(case c0.user_id
				when 0 then 0.01
				else 1
			end) as aggregate
		';

		$query = \DB::select(\DB::expr($column));
		$query->from(array('trn_cool', 'c0'));
		$query->where('c0.is_deleted', '0');
		$query->group_by('c0.review_id', 'c0.about');
		$query->order_by('aggregate', 'DESC');

		return $query->compile();
	}


	public function get_list($arr_where, $arr_columns, $arr_order=null)
	{
		return $this->search($arr_where, $arr_columns, $arr_order);
	}

	public function get_one($arr_where, $arr_columns, $arr_order=null)
	{
		return $this->search_one($arr_where, $arr_columns, $arr_order);
	}

}