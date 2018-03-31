<?php
namespace main\model\dao;


use main\model\dto\ReviewMusicDto;
use main\model\dto\LoginDto;
use main\model\dto\CoolDto;
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
class ReviewMusicArtistDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_review_music_artist';
	}

	/**
	 *
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function insert_review_music(array $arr_values)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$result = $this->save($arr_values);

		return $result;
	}

	public function update_review_music_by_id(array $arr_values)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$result = $this->update($arr_values, array('id' => $review_music_dto->get_review_id()));

		return $result;
	}

	public function update_review_music_by_same_title(array $arr_values)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$login_dto = LoginDto::get_instance();
		$arr_where = array(
				'artist_id' => $review_music_dto->get_artist_id(),
				'user_id' => $login_dto->get_user_id(),
		);
		$result = $this->update($arr_values, $arr_where);

		return true;
	}

	public function set_cool()
	{
		\Log::debug('[start]'. __METHOD__);

		$cool_dto = CoolDto::get_instance();

		$query = \DB::update($this->_table_name);
		$query->set(array(
			'cool_count' => \DB::expr('cool_count + 1'),
			'updated_at' => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
		));
		$query->where('id', $cool_dto->get_review_id());
		return $query->execute();
	}

	public function get_cool_count()
	{
		\Log::debug('[start]'. __METHOD__);

		$cool_dto = CoolDto::get_instance();

		$query = \DB::select('cool_count');
		$query->from($this->_table_name);
		$query->where('id', $cool_dto->get_review_id());

		$arr_result = $query->execute()->current();

		return $arr_result['cool_count'];
	}

	public function get_review_list($is_count=false)
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$review_id   = $review_music_dto->get_review_id();
		$about_id    = $review_music_dto->get_about_id();
		$search_word = $review_music_dto->get_search_word();
		$user_id     = $review_music_dto->get_review_user_id();
		$page        = $review_music_dto->get_page();
		$limit       = $review_music_dto->get_limit();
		$offset      = ($page - 1) * $limit;

		if ($is_count)
		{
			$arr_columns = array(
				\DB::expr('count(r.id) as cnt'),
			);
		}
		else
		{
			$arr_columns = array(
					'r.id',
					'r.about',
					'r.artist_id',
					'r.artist_name',
					\DB::expr('a.kana as artist_kana'),
					array('r.artist_id', 'about_id'),
					array('r.artist_name', 'about_name'),
					'a.image_url',
					'a.image_small',
					'a.image_medium',
					'a.image_large',
					'a.image_extralarge',
					'r.star',
					'r.review',
					'r.user_id',
					'r.cool_count',
					'r.comment_count',
					'r.created_at',
					'r.updated_at',
					'u.user_name',
			);
		}

		/**
		 * r : trn_review_music_artist
		 * u : trn_user
		 * a : mst_artist
		 */
		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('trn_user', 'u'));
		$query->on('r.user_id', '=', 'u.id');
		$query->on('u.is_deleted', '=', \DB::expr(0));
		$query->on('u.is_leaved', '=', \DB::expr(0));
		$query->join(array('mst_artist', 'a'));
		$query->on('r.artist_id', '=', 'a.id');
		if ( ! empty($review_id))
		{
			$query->where('r.id', $review_id);
		}
		if ( ! empty($about_id))
		{
			$query->where('r.artist_id', $about_id);
		}
		if ( ! empty($search_word))
		{
			$query->and_where_open();
			$query->or_where('r.review'     , 'like', '%'. $search_word. '%');
			$query->or_where('a.name', 'like', $search_word. '%');
			$query->or_where('r.artist_name', 'like', $search_word. '%');
			$query->or_where('a.name', 'like', 'the '. $search_word. '%');
			$query->or_where('r.artist_name', 'like', 'the '. $search_word. '%');
			$query->or_where('u.user_name'  , 'like', '%'. $search_word. '%');
			$query->and_where_close();
		}
		if ( ! empty($user_id))
		{
			$query->where('r.user_id', '=', $user_id);
		}
		$query->where('r.is_deleted', '=', '0');
		$query->order_by('r.id', 'DESC');
		if ( ! empty($limit))
		{
			$query->offset($offset);
			$query->limit($limit);
		}
		return $query->execute()->as_array();
	}


	public function get_review_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$arr_columns = array(
				'r.id',
				'r.about',
				'r.artist_id',
				'r.artist_name',
				\DB::expr('a.kana as artist_kana'),
				array('r.artist_id', 'about_id'),
				array('r.artist_name', 'about_name'),
				'a.image_url',
				'a.image_small',
				'a.image_medium',
				'a.image_large',
				'a.image_extralarge',
				'r.star',
				'r.review',
				'r.user_id',
				'r.cool_count',
				'r.comment_count',
				'r.created_at',
				'r.updated_at',
				'u.user_name',
		);

		/**
		 * r : trn_review_music_artist
		 * u : trn_user
		 * a : mst_artist
		 */
		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('trn_user', 'u'));
		$query->on('r.user_id', '=', 'u.id');
		$query->on('u.is_deleted', '=', \DB::expr(0));
		$query->on('u.is_leaved', '=', \DB::expr(0));
		$query->join(array('mst_artist', 'a'));
		$query->on('r.artist_id', '=', 'a.id');
		$query->on('a.is_deleted', '=', \DB::expr(0));
		$query->where('r.id', '=', $review_music_dto->get_review_id());
		$query->where('r.is_deleted', '=', '0');

		return $query->execute()->as_array();
	}


	/**
	 * アーティストレビュートップリストを取得
	 */
	public function get_top_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$count       = $review_music_dto->get_count();
		$offset      = 0;

		$arr_columns = array(
				'r.id',
				'r.about',
				'r.artist_id',
				'r.artist_name',
				\DB::expr('a.kana as artist_kana'),
				array('r.artist_id', 'about_id'),
				array('r.artist_name', 'about_name'),
				'a.image_small',
				'a.image_medium',
				'a.image_large',
				'a.image_extralarge',
				'r.star',
				'r.review',
				'r.user_id',
				'r.cool_count',
				'r.comment_count',
				'r.created_at',
				'r.updated_at',
				'u.user_name',
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('trn_user', 'u'));
		$query->on('r.user_id', '=', 'u.id');
		$query->on('u.is_deleted', '=', \DB::expr(0));
		$query->on('u.is_leaved', '=', \DB::expr(0));
		$query->join(array('mst_artist', 'a'));
		$query->on('r.artist_id', '=', 'a.id');
		$query->where('r.review', '!=', '');
		$query->where('r.is_deleted', '=', '0');
		$query->order_by('r.id', 'DESC');
		if ( ! empty($count))
		{
			$query->offset($offset);
			$query->limit($count);
		}

		return $query->execute()->as_array();
	}


	public function is_empty_same_review()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$review_dto = ReviewMusicDto::get_instance();
		$login_dto = LoginDto::get_instance();
		$result = $this->search(
				array(
						'artist_id' => $review_dto->get_artist_id(),
						'user_id' => $login_dto->get_user_id(),
				),
				array(
						'id'
				)
		);
		if (empty($result))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	# 物理削除
	public function delete_review_music($id)
	{
		$result = $this->delete(array(), array('id' => $id), false);
	}

	# 一覧取得
	public function get_list($arr_where, $arr_columns, $arr_order=null, $dto=null, $limit, $page=1)
	{
		return $this->search($arr_where, $arr_columns, $arr_order, $dto, $limit, $page);
	}

	# 一件取得（あらかじめ一件とわかっている時のみ）
	public function get_one($arr_where, $arr_columns, $arr_order=null)
	{
		return $this->search_one($arr_where, $arr_columns, $arr_order);
	}

}