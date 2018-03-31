<?php
namespace main\model\dao;

use main\model\dto\ReviewMusicDto;
use main\model\dto\RankMusicDto;
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
class ReviewMusicTrackDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_review_music_track';
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
		$review_music_dto = ReviewMusicDto::get_instance();
		$result = $this->update($arr_values, array('id' => $review_music_dto->get_review_id()));

		return $result;
	}

	public function update_review_music_by_same_title(array $arr_values)
	{
		$review_music_dto = ReviewMusicDto::get_instance();
		$login_dto = LoginDto::get_instance();
		$arr_where = array(
				'artist_name' => $review_music_dto->get_artist_name(),
				'album_name' => $review_music_dto->get_album_name(),
				'track_name' => $review_music_dto->get_track_name(),
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


	/**
	 * レビュー一覧
	 */
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
					'r.track_id',
					'r.track_name',
					array('r.track_id', 'about_id'),
					array('r.track_name', 'about_name'),
					'r.star',
					'r.cool_count',
					'r.comment_count',
					'r.review',
					't.image_url',
					't.image_small',
					't.image_medium',
					't.image_large',
					't.image_extralarge',
					array('a.image_medium', 'artist_image_medium'),
					'r.user_id',
					'r.created_at',
					'r.updated_at',
					'u.user_name',
			);
		}

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('mst_track', 't'));
		$query->on('t.id', '=', 'r.track_id');
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
			$query->where('r.track_id', $about_id);
		}
		if ( ! empty($search_word))
		{
			$query->and_where_open();
			$query->or_where('r.review'     , 'like', '%'. $search_word. '%');
			$query->or_where('r.artist_name', 'like', $search_word. '%');
			$query->or_where('r.artist_name', 'like', 'the '. $search_word. '%');
			$query->or_where('u.user_name'  , 'like', '%'. $search_word. '%');
			$query->or_where('r.album_name' , 'like', $search_word. '%');
			$query->or_where('r.track_name' , 'like', $search_word. '%');
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
		$review_id   = $review_music_dto->get_review_id();
		$arr_columns = array(
				'r.id',
				'r.about',
				'r.artist_id',
				'r.artist_name',
				'r.album_id',
				'r.album_name',
				'r.track_id',
				'r.track_name',
				array('r.track_id', 'about_id'),
				array('r.track_name', 'about_name'),
				'r.star',
				'r.cool_count',
				'r.comment_count',
				'r.review',
				't.image_url',
				't.image_small',
				't.image_medium',
				't.image_large',
				't.image_extralarge',
				array('a.image_medium', 'artist_image_medium'),
				'r.user_id',
				'r.created_at',
				'r.updated_at',
				'u.user_name',
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('mst_track', 't'));
		$query->on('t.id', '=', 'r.track_id');
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
	 * トップレビューリスト
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
				'r.track_id',
				'r.track_name',
				array('r.track_id', 'about_id'),
				array('r.track_name', 'about_name'),
				'r.star',
				'r.cool_count',
				'r.comment_count',
				'r.review',
				't.image_url',
				't.image_small',
				't.image_medium',
				't.image_large',
				't.image_extralarge',
				'r.user_id',
				'r.created_at',
				'r.updated_at',
				'u.user_name',
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('mst_track', 't'));
		$query->on('t.id', '=', 'r.track_id');
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


	public function get_rank()
	{
		$rank_music_dto = RankMusicDto::get_instance();

		$page = $rank_music_dto->get_page();
		if (empty($page))
		{
			$page = 1;
		}
		$limit = $rank_music_dto->get_limit();
		if (empty($limit))
		{
			$limit = 10;
		}
		$offset = ($page - 1) * $limit;

		$arr_columns = array(
			'r.id',
			'r.artist_id',
			'r.artist_name',
			'r.track_id',
			'r.track_name',
			'r.star',
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));

		$query->where('created_at', '>=', $rank_music_dto->get_rank_from_date());
		$query->where('created_at', '<=', $rank_music_dto->get_rank_to_date());
		$query->where('is_deleted', '0');

		$query->order_by('star', 'DESC');

		if ( ! empty($limit))
		{
			$query->offset($offset);
			$query->limit($limit);
		}

		return $query->execute()->as_array();
	}


	public function get_top_star($from, $to)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_columns = array(
				't.id',
				't.artist_id',
				array('a.name', 'artist_name'),
				't.album_id',
				't.name',
				't.kana',
				't.english',
				't.mbid_itunes',
				't.mbid_lastfm',
				't.url_itunes',
				't.url_lastfm',
				't.image_url',
				't.image_small',
				't.image_medium',
				't.image_large',
				't.image_extralarge',
				't.number',
				't.content',
				't.release_itunes',
				't.release_lastfm',
				't.genre_itunes',
				't.duration',
				't.preview_itunes',
				array(\DB::expr('sum(case r.review when NULL then r.star when "" then r.star else r.star * 2 end)'), 'star'),
		);

		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'r'));
		$query->join(array('mst_track', 't'));
		$query->on('r.track_id', '=', 't.id');
		$query->join(array('mst_artist', 'a'));
		$query->on('r.artist_id', '=', 'a.id');
		$query->group_by(array('t.id'));
		$query->where('r.created_at', '>=', $from);
		$query->where('r.created_at', '<=', $to);
		$query->where('r.is_deleted', '=', '0');
		$query->order_by('star', 'DESC');
		$query->limit(10000); // 保険
		$result = $query->execute()->as_array();

		return $result;
	}


	public function is_empty_same_review()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$review_dto = ReviewMusicDto::get_instance();
		$login_dto = LoginDto::get_instance();
		$result = $this->search(
				array(
						'artist_name' => $review_dto->get_artist_name(),
						'album_name' => $review_dto->get_album_name(),
						'track_name' => $review_dto->get_track_name(),
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

	public function get_list($arr_where, $arr_columns, $arr_order=array())
	{
		return $this->search($arr_where, $arr_columns, $arr_order);
	}

	public function get_one($arr_where, $arr_columns, $arr_order=array())
	{
		return $this->search_one($arr_where, $arr_columns, $arr_order);
	}

}