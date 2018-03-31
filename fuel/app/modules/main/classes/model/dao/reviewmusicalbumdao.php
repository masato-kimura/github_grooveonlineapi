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
class ReviewMusicAlbumDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_review_music_album';
	}

	/**
	 *
	 * @return boolean|Ambigous <boolean, unknown>
	 */
	public function insert_review_music(array $arr_values)
	{
		$result = $this->save($arr_values);

		return $result;
	}

	public function update_review_music_by_id(array $arr_values)
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$result = $this->update($arr_values, array('id' => $review_music_dto->get_review_id()));

		return $result;
	}

	public function update_review_music_by_same_title(array $arr_values)
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$login_dto = LoginDto::get_instance();
		$arr_where = array(
			'artist_name' => $review_music_dto->get_artist_name(),
			'album_name' => $review_music_dto->get_album_name(),
			'user_id' => $login_dto->get_user_id(),
		);
		$result = $this->update($arr_values, $arr_where);
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
					'r.album_id',
					'r.album_name',
					array('r.album_id', 'about_id'),
					array('r.album_name', 'about_name'),
					'r.star',
					'r.cool_count',
					'r.comment_count',
					'r.review',
					'al.image_url',
					'al.image_small',
					'al.image_medium',
					'al.image_large',
					'al.image_extralarge',
					array('a.image_medium', 'artist_image_medium'),
					'r.user_id',
					'r.created_at',
					'r.updated_at',
					'u.user_name',
			);
		}

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('mst_album', 'al'));
		$query->on('al.id', '=', 'r.album_id');
		$query->join(array('trn_user', 'u'));
		$query->on('r.user_id', '=', 'u.id');
		$query->on('u.is_deleted', '=', \DB::expr(0));
		$query->on('u.is_leaved', '=', \DB::expr(0));
		$query->join(array('mst_artist', 'a'));
		$query->on('a.id', '=', 'r.artist_id');
		if ( ! empty($review_id))
		{
			$query->where('r.id', $review_id);
		}
		if ( ! empty($about_id))
		{
			$query->where('r.album_id', $about_id);
		}
		if ( ! empty($search_word))
		{
			$query->and_where_open();
			$query->or_where('r.review'     , 'like', '%'. $search_word. '%');
			$query->or_where('r.artist_name', 'like', $search_word. '%');
			$query->or_where('r.artist_name', 'like', 'the '. $search_word. '%');
			$query->or_where('u.user_name'  , 'like', '%'. $search_word. '%');
			$query->or_where('r.album_name', 'like', $search_word. '%');
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
			'r.album_id',
			'r.album_name',
			array('r.album_id', 'about_id'),
			array('r.album_name', 'about_name'),
			'r.star',
			'r.cool_count',
			'r.comment_count',
			'r.review',
			'al.image_url',
			'al.image_small',
			'al.image_medium',
			'al.image_large',
			'al.image_extralarge',
			array('a.image_medium', 'artist_image_medium'),
			'r.user_id',
			'r.created_at',
			'r.updated_at',
			'u.user_name',
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('mst_album', 'al'));
		$query->on('al.id', '=', 'r.album_id');
		$query->join(array('trn_user', 'u'));
		$query->on('r.user_id', '=', 'u.id');
		$query->on('u.is_deleted', '=', \DB::expr(0));
		$query->on('u.is_leaved', '=', \DB::expr(0));
		$query->join(array('mst_artist', 'a'));
		$query->on('a.id', '=', 'r.artist_id');
		$query->on('a.is_deleted', '=', \DB::expr(0));
		$query->where('r.id', '=', $review_music_dto->get_review_id());
		$query->where('r.is_deleted', '=', '0');

		return $query->execute()->as_array();
	}


	/**
	 * トップレビューを取得
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
				'r.album_id',
				'r.album_name',
				array('r.album_id', 'about_id'),
				array('r.album_name', 'about_name'),
				'r.star',
				'r.cool_count',
				'r.comment_count',
				'r.review',
				'al.image_url',
				'al.image_small',
				'al.image_medium',
				'al.image_large',
				'al.image_extralarge',
				'r.user_id',
				'r.created_at',
				'r.updated_at',
				'u.user_name',
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('mst_album', 'al'));
		$query->on('al.id', '=', 'r.album_id');
		$query->join(array('trn_user', 'u'));
		$query->on('r.user_id', '=', 'u.id');
		$query->on('u.is_deleted', '=', \DB::expr(0));
		$query->on('u.is_leaved', '=', \DB::expr(0));
		$query->where('r.review', '!=', '');
		$query->where('r.is_deleted', '=', '0');
		$query->order_by('r.id', 'DESC');
		$query->offset($offset);
		$query->limit($count);

		return $query->execute()->as_array();
	}


	# 物理削除
	public function delete_review_music($id)
	{
		$result = $this->delete(array(), array('id' => $id), false);
	}

	/**
	 * スターレビュートップランキング
	 * @return unknown
	 */
	public function get_top_star($offset=0, $limit=100)
	{
		\Log::debug('[start]'. __METHOD__);

		/*
		 * r.reviewmusicalbumdao
		 * l.mst_album
		 * a.mst_artist
		 */
		$arr_columns = array(
				'l.id',
				'l.artist_id',
				array('a.name', 'artist_name'),
				'r.album_id',
				'l.name',
				'l.kana',
				'l.english',
				'l.mbid_itunes',
				'l.mbid_lastfm',
				'l.url_itunes',
				'l.url_lastfm',
				'l.image_url',
				'l.image_small',
				'l.image_medium',
				'l.image_large',
				'l.image_extralarge',
				'l.release_itunes',
				'l.copyright_itunes',
				'l.genre_itunes',
				array(\DB::expr('sum(case r.review when NULL then r.star when "" then r.star else r.star * 2 end)'), 'star'),
				//'r.review',
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('mst_album', 'l'));
		$query->on('r.album_id', '=', 'l.id');
		$query->join(array('mst_artist', 'a'));
		$query->on('r.artist_id', '=', 'a.id');
		$query->group_by(array('l.id'));
		$query->where('r.created_at', '>=', \Date::forge(time() - (60 * 60 * 24 * 7 * 10))->format('%Y-%m-%d 00:00:00')); // @todo * 10いらない
		$query->where('r.is_deleted', '=', '0');
		$query->order_by('star', 'DESC');
		$query->offset($offset);
		$query->limit($limit);
		$result = $query->execute()->as_array();
		return $result;
	}


	public function is_empty_same_review()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_dto = ReviewMusicDto::get_instance();
		$login_dto = LoginDto::get_instance();
		$result = $this->search(
			array(
				'artist_name' => $review_dto->get_artist_name(),
				'album_name' => $review_dto->get_album_name(),
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

	public function get_list($arr_where, $arr_columns, $arr_order=null)
	{
		return $this->search($arr_where, $arr_columns, $arr_order);
	}

	public function get_one($arr_where, $arr_columns, $arr_order=null)
	{
		return $this->search_one($arr_where, $arr_columns, $arr_order);
	}

}