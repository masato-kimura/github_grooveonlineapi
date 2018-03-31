<?php
namespace main\model\dao;

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
class CoolDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;
	protected $_table_name_for_count;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'trn_cool';
	}

	public function get_count()
	{
		\Log::debug('[start]'. __METHOD__);

		$cool_dto = CoolDto::get_instance();

		$query = \DB::select(array(\DB::expr('count(id)'), 'cnt'));
		$query->from($this->_table_name);
		$query->where('about', $cool_dto->get_about());
		$query->where('review_id', $cool_dto->get_review_id());
		$arr_result = $query->execute()->current();
		$cnt = $arr_result['cnt'];

		return $cnt;
	}

	public function get_my_cool()
	{
		\Log::debug('[start]'. __METHOD__);

		$cool_dto = CoolDto::get_instance();
		$cool_user_id = $cool_dto->get_cool_user_id();

		$query = \DB::select('id');
		$query->from($this->_table_name);
		$query->where('about', $cool_dto->get_about());
		$query->where('review_id', $cool_dto->get_review_id());
		// ユーザIDが未存在ならIPアドレスで特定
		if ( ! empty($cool_user_id))
		{
			$query->where('user_id', $cool_dto->get_cool_user_id());
		}
		else
		{
			$query->where('ip', $cool_dto->get_ip());
		}
		$arr_result = $query->execute()->as_array();

		return $arr_result;
	}

	/**
	 *
	 * @param array $arr_where
	 *    review_id,
	 *    about,
	 *    user_id*
	 * @param number $offset
	 * @param number $limit
	 * @return unknown
	 */
	public function get_cool_users(array $arr_where, $offset=0, $limit=20)
	{
		\Log::debug('[start]'. __METHOD__);

		$cool_dto = CoolDto::get_instance();

		$query = \DB::select_array(array('c.user_id', 'u.user_name'));
		$query->from(array($this->_table_name, 'c'));
		$query->join(array('trn_user', 'u'));
		$query->on('c.user_id', '=', 'u.id');
		$query->where('c.review_id', $arr_where['review_id']);
		$query->where('c.about', $arr_where['about']);
		$query->where('c.user_id', '>=', 1);
		if ( isset($arr_where['user_id']))
		{
			$query->where('c.user_id', $arr_where['user_id']);
		}
		$query->where('c.is_deleted', 0);
		$query->where('u.is_deleted', 0);
		$query->where('u.is_leaved', 0);
		$query->offset($offset);
		$query->limit($limit);
		$query->order_by('c.id', 'DESC');
		$arr_result = $query->execute()->as_array();

		return $arr_result;
	}


	/**
	 *
	 * @param array $arr_where
	 *    review_id,
	 *    about,
	 *    user_id*
	 * @param number $offset
	 * @param number $limit
	 * @return unknown
	 */
	public function get_cool_user_count(array $arr_where)
	{
		\Log::debug('[start]'. __METHOD__);

		$cool_dto = CoolDto::get_instance();

		$query = \DB::select(array(\DB::expr('count(c.user_id)'), 'cnt'));
		$query->from(array($this->_table_name, 'c'));
		$query->join(array('trn_user', 'u'));
		$query->on('c.user_id', '=', 'u.id');
		$query->where('c.review_id', $arr_where['review_id']);
		$query->where('c.about', $arr_where['about']);
		$query->where('c.user_id', '>=', 1);
		if ( isset($arr_where['user_id']))
		{
			$query->where('c.user_id', $arr_where['user_id']);
		}
		$query->where('c.is_deleted', 0);
		$query->where('u.is_deleted', 0);
		$query->where('u.is_leaved', 0);
		$arr_result = $query->execute()->current();

		return $arr_result;
	}


	public function get_cool_top_week($about='all', $from, $to, $offset=0, $limit=100)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_column = array(
				'c.review_id',
				'c.about',
				'r.artist_id',
				'r.artist_name',
				'r.review',
				'r.star',
				'r.cool_count',
				'r.comment_count',
				array(\DB::expr("sum(case c.user_id when 0 then 0.01 else 1 end)"), 'aggregate'),
		);

		switch ($about)
		{
			case 'artist':
				$query = \DB::select_array($arr_column);
				$query->from(array($this->_table_name, 'c'));
				$query->join(array('trn_review_music_artist', 'r'));
				$query->on('c.review_id', '=', 'r.id');
				$query->where('c.about', '=', 'artist');
				$query->where('r.is_deleted', '=', 0);
				break;
			case 'album':
				$arr_column[] = 'r.album_id';
				$arr_column[] = 'r.album_name';
				$arr_column[] = 'm.id';
				$arr_column[] = 'm.image_url';
				$arr_column[] = 'm.image_small';
				$arr_column[] = 'm.image_medium';
				$arr_column[] = 'm.image_large';
				$arr_column[] = 'm.image_extralarge';
				$arr_column[] = 'm.mbid_itunes';
				$arr_column[] = 'm.mbid_lastfm';
				$arr_column[] = 'm.genre_itunes';

				$query = \DB::select_array($arr_column);
				$query->from(array($this->_table_name, 'c'));
				$query->join(array('trn_review_music_album', 'r'));
				$query->on('c.review_id', '=', 'r.id');
				$query->join(array('mst_album', 'm'));
				$query->on('r.album_id', '=', 'm.id');
				$query->where('c.about', '=', 'album');
				$query->where('r.is_deleted', '=', 0);
				break;
			case 'track':
				$arr_column[] = 'r.album_id';
				$arr_column[] = 'r.album_name';
				$arr_column[] = 'r.track_id';
				$arr_column[] = 'r.track_name';
				$arr_column[] = 'm.id';
				$arr_column[] = 'm.image_url';
				$arr_column[] = 'm.image_small';
				$arr_column[] = 'm.image_medium';
				$arr_column[] = 'm.image_large';
				$arr_column[] = 'm.image_extralarge';
				$arr_column[] = 'm.mbid_itunes';
				$arr_column[] = 'm.mbid_lastfm';
				$arr_column[] = 'm.genre_itunes';
				$arr_column[] = 'm.preview_itunes';

				$query = \DB::select_array($arr_column);
				$query->from(array($this->_table_name, 'c'));
				$query->join(array('trn_review_music_track', 'r'));
				$query->on('c.review_id', '=', 'r.id');
				$query->join(array('mst_track', 'm'));
				$query->on('r.track_id', '=', 'm.id');
				$query->where('c.about', '=', 'track');
				$query->where('r.is_deleted', '=', 0);
				break;
			default:
				$arr_column[] = 'r.about_id';

				$query = \DB::select_array($arr_column);
				$query->from(array($this->_table_name, 'c'));
				$query->join(array('view_review_music', 'r'));
				$query->on('c.review_id', '=', 'r.id');
				$query->on('c.about', '=', 'r.about');

		}
		$query->where('c.created_at', '>=', $from);
		$query->where('c.created_at', '<=', $to);
		$query->group_by('c.review_id','c.about');
		$query->order_by('aggregate', 'DESC');

		return $query->execute()->as_array();
	}


	public function specify_cool_user($about, $review_id, $user_id, $ip)
	{
		\Log::debug('[start]'. __METHOD__);

		$query = \DB::select('c.id', 'c.user_id');
		$query->from(array($this->_table_name, 'c'));
		if ( ! empty($user_id))
		{
			$query->join(array('trn_user', 'u'));
			$query->on('c.user_id', '=', 'u.id');
			$query->where('c.user_id', $user_id);
			$query->where('u.is_deleted', 0);
			$query->where('u.is_leaved', 0);
		}
		$query->where('c.review_id', $review_id);
		$query->where('c.about', $about);
		$query->where('c.ip', $ip);
		$query->where('c.is_deleted', 0);
		$arr_result = $query->execute()->as_array();

		return $arr_result;
	}


	public function get_cools($user_id, $offset=0, $limit=30)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_columns = array(
			array('c.review_user_id', 'user_id'),
			'u.user_name',
			array(\DB::expr('count(c.review_user_id)'), 'cool_count'),
		);
		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'c'));
		$query->join(array('trn_user', 'u'));
		$query->on('c.review_user_id', '=', 'u.id');
		$query->where('c.user_id', '=', $user_id);
		$query->where('c.is_deleted', '=', '0');
		$query->where('u.is_deleted', '=', '0');
		$query->where('u.is_leaved', '=', '0');
		$query->group_by('c.review_user_id');
		$query->group_by('u.user_name');
		$query->group_by('c.review_user_id');
		$query->order_by('c.id', 'DESC');
		$query->offset($offset);
		$query->limit($limit);
		$result = $query->execute()->as_array();

		return $result;
	}


	public function get_thanks($user_id, $offset=0, $limit=30)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_columns = array(
				array('c.user_id', 'user_id'),
				'u.user_name',
				array(\DB::expr('count(c.review_user_id)'), 'cool_count'),
		);
		$query = \DB::select_array($arr_columns);
		$query->from(array($this->_table_name, 'c'));
		$query->join(array('trn_user', 'u'));
		$query->on('c.user_id', '=', 'u.id');
		$query->where('c.review_user_id', '=', $user_id);
		$query->where('c.user_id', '>', 0);
		$query->where('c.is_deleted', '=', '0');
		$query->where('u.is_deleted', '=', '0');
		$query->where('u.is_leaved', '=', '0');
		$query->group_by('c.user_id');
		$query->group_by('u.user_name');
		$query->group_by('c.review_user_id');
		//$query->order_by('c.id', 'DESC');
		$query->offset($offset);
		$query->limit($limit);
		$result = $query->execute()->as_array();

		return $result;
	}


	public function set_cool()
	{
		\Log::debug('[start]'. __METHOD__);

		/**
		 * about, review_id, user_id, ip でユニークカラム
		 */
		$cool_dto = CoolDto::get_instance();
		$cool_user_id = $cool_dto->get_cool_user_id();
		$arr_params = array(
			'about'     => $cool_dto->get_about(),
			'review_id' => $cool_dto->get_review_id(),
			'review_user_id' => $cool_dto->get_review_user_id(),
			'user_id'   => $cool_dto->get_cool_user_id(),
			'ip'        => $cool_dto->get_ip(),
		);
		$query = $this->save($arr_params, true);

		return $query;
	}

}