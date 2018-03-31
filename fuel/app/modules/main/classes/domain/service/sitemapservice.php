<?php
namespace main\domain\service;

use main\model\dao\ReviewMusicDao;
use main\model\dao\UserDao;
use main\model\dao\ArtistDao;

/**
 * プライマリーのidは基本使用しない
 * @author masato
 *
 */
class SitemapService extends Service
{
	private static $_arr_site_map;
	private static $_max_count = 50000;

	public static function set_dto_for_make()
	{
		\Log::debug('[start]'. __METHOD__);


		return true;
	}


	public static function validation_for_get()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = \Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function get_reviews()
	{
		\Log::debug('[start]'. __METHOD__);

		if (count(static::$_arr_site_map) > static::$_max_count)
		{
			return true;
		}

		$review_dao = new ReviewMusicDao();
		$arr_columns = array(
			'r.id', 'r.updated_at', 'r.about',
		);
		$arr_where = array(
			'u.is_leaved' => '0',
		);
		$arr_result = $review_dao->get_review_list(false, $arr_where, $arr_columns);
		$cnt = count(static::$_arr_site_map);
		foreach ($arr_result as $i => $val)
		{
			static::$_arr_site_map[]['url'] = array(
				'loc'        => \Config::get('host.front_url'). '/review/music/detail/'. $val['about']. '/'. $val['id']. '/',
				'lastmod'    => $val['updated_at'],
				'changefreq' => 'daily',
				'priority'   => '0.8',
			);
			$cnt++;
			if ($cnt > static::$_max_count)
			{
				break;
			}
		}

		return true;
	}


	public static function get_users()
	{
		\Log::debug('[start]'. __METHOD__);

		if (count(static::$_arr_site_map) > static::$_max_count)
		{
			return true;
		}

		$user_dao = new UserDao();
		$arr_columns = array(
			'id', 'updated_at',
		);
		$arr_where = array(
			'is_leaved' => '0',
		);
		$arr_order = array(
			'id' => 'DESC',
		);
		$arr_result = $user_dao->search($arr_where, $arr_columns, $arr_order);
		$cnt = count(static::$_arr_site_map);
		foreach ($arr_result as $i => $val)
		{
			static::$_arr_site_map[]['url'] = array(
				'loc'        => \Config::get('host.front_url'). '/user/you/'. $val->id. '/',
				'lastmod'    => $val->updated_at,
				'changefreq' => 'daily',
				'priority'   => '0.7',
			);
			$cnt++;
			if ($cnt > static::$_max_count)
			{
				break;
			}
		}

		return true;
	}


	public static function get_artists()
	{
		\Log::debug('[start]'. __METHOD__);

		if (count(static::$_arr_site_map) > static::$_max_count)
		{
			return true;
		}

		$artist_dao = new ArtistDao();

		$arr_columns = array(
			'a.id',
			'a.updated_at',
		);
		$query = \DB::select_array($arr_columns);
		$query->from(array('mst_artist', 'a'));
		$query->where(\DB::expr( 'NOT EXISTS (select * from mst_artist as b where b.mbid_itunes = "" and mbid_lastfm = "" and b.id = a.id)' ));
		$query->where('a.is_deleted', '=', '0');
		$arr_result = $query->as_object()->execute()->as_array();
		$cnt = count(static::$_arr_site_map);
		foreach ($arr_result as $i => $val)
		{
			static::$_arr_site_map[]['url'] = array(
					'loc'        => \Config::get('host.front_url'). '/artist/detail/'. $val->id. '/',
					'lastmod'    => $val->updated_at,
					'changefreq' => 'daily',
					'priority'   => '0.6',
			);
			$cnt++;
			if ($cnt > static::$_max_count)
			{
				break;
			}
		}

		return true;
	}


	public static function get_albums()
	{
		\Log::debug('[start]'. __METHOD__);

		if (count(static::$_arr_site_map) > static::$_max_count)
		{
			return true;
		}

		$artist_dao = new ArtistDao();

		$arr_columns = array(
			'album.id',
			'album.updated_at',
		);
		$query = \DB::select_array($arr_columns);
		$query->from(array('mst_artist', 'a'));
		$query->join(array('mst_album', 'album'));
		$query->on('a.id', '=', 'album.artist_id');
		$query->where(\DB::expr( 'NOT EXISTS (select * from mst_artist as b where b.mbid_itunes = "" and mbid_lastfm = "" and b.id = a.id)' ));
		$query->where('a.is_deleted', '=', '0');
		$query->where('album.is_deleted', '=', '0');
		$arr_result = $query->as_object()->execute()->as_array();
		$cnt = count(static::$_arr_site_map);
		foreach ($arr_result as $i => $val)
		{
			static::$_arr_site_map[]['url'] = array(
					'loc'        => \Config::get('host.front_url'). '/album/detail/'. $val->id. '/',
					'lastmod'    => $val->updated_at,
					'changefreq' => 'daily',
					'priority'   => '0.5',
			);
			$cnt++;
			if ($cnt > static::$_max_count)
			{
				break;
			}
		}

		return true;
	}


	public static function get_tracks()
	{
		\Log::debug('[start]'. __METHOD__);

		if (count(static::$_arr_site_map) > static::$_max_count)
		{
			return true;
		}

		$artist_dao = new ArtistDao();

		$arr_columns = array(
			'track.id',
			'track.updated_at',
		);
		$query = \DB::select_array($arr_columns);
		$query->from(array('mst_artist', 'a'));
		$query->join(array('mst_track', 'track'));
		$query->on('a.id', '=', 'track.artist_id');
		$query->where(\DB::expr( 'NOT EXISTS (select * from mst_artist as b where b.mbid_itunes = "" and mbid_lastfm = "" and b.id = a.id)' ));
		$query->where('a.is_deleted', '=', '0');
		$query->where('track.is_deleted', '=', '0');
		$arr_result = $query->as_object()->execute()->as_array();
		$cnt = count(static::$_arr_site_map);
		foreach ($arr_result as $i => $val)
		{
			static::$_arr_site_map[]['url'] = array(
					'loc'        => \Config::get('host.front_url'). '/track/detail/'. $val->id. '/',
					'lastmod'    => $val->updated_at,
					'changefreq' => 'daily',
					'priority'   => '0.4',
			);
			$cnt++;
			if ($cnt > static::$_max_count)
			{
				break;
			}
		}

		return true;
	}


	public static function set_cache()
	{
		\Log::debug('[start]'. __METHOD__);

		\Cache::set('site_map', static::$_arr_site_map);

		return true;
	}
}