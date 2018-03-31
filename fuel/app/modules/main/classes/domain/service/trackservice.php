<?php
namespace main\domain\service;

use main\model\dto\TrackDto;
use main\model\dao\TrackDao;
use Fuel\Core\Validation;
use main\model\dto\ReviewMusicDto;
use main\model\dto\ArtistDto;
use main\model\dao\ArtistDao;
use main\model\dto\AlbumDto;
use main\model\dao\AlbumDao;
use main\model\dao\SearchTrackDao;
use main\model\dao\SearchTrackWordDao;

class TrackService extends Service
{
	private static $arr_album_track_list_from_grooveonline = array();
	private static $arr_album_track_list_from_lastfm = array();
	private static $arr_album_track_list_from_itunes = array();
	private static $arr_album_track_list_mixed_api = array();
	private static $arr_album_track_list_all = array();
	private static $arr_track_list_from_grooveonline = array();
	private static $arr_track_list_from_lastfm = array();
	private static $arr_track_list_from_itunes = array();
	private static $arr_track_list_mixed_api = array();
	private static $arr_track_list_all = array();

	private static $arr_search = array();
	private static $search_only = false;
	private static $api_expired_days = 3; // days apiにアクセスしない期間(アルバムトラックリスト)
	private static $api_expired_days_word = 3; // days apiにアクセスしない期間(検索ワード)


	public static function validation_for_albumtracklist()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('album_id', 'アルバムID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_info()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('track_id', 'トラックID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_search()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('artist_id', 'アーティストID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('track_name', 'トラック検索名');
		$v->add_rule('required');
		$v->add_rule('max_length', '100');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function set_dto_for_albumtracklist()
	{
		\Log::debug('[start]'. __METHOD__);

		$album_dto = AlbumDto::get_instance();
		$album_dto->set_album_id(trim(static::$_obj_request->album_id));

		return true;
	}


	public static function set_dto_for_info()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dto = TrackDto::get_instance();
		$track_dto->set_track_id(trim(static::$_obj_request->track_id));

		return true;
	}


	public static function set_dto_for_search()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$track_dto = TrackDto::get_instance();

		$artist_dto->set_artist_id(trim(static::$_obj_request->artist_id));
		$track_dto->set_track_name(trim(static::$_obj_request->track_name));

		return true;
	}


	/**
	 * バリデーション
	 * @throws \Exception
	 * @return boolean
	 */
	public static function validation_for_regist()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dto = TrackDto::get_instance();

		//---------------------------
		// 必須項目エラーチェック
		//---------------------------
		$track_name = $track_dto->get_track_name();
		if (empty($track_name))
		{
			throw new \Exception('require error[track_name]', 7002);
		}

		//---------------------------
		// 型チェック
		//---------------------------
		$track_id = $track_dto->get_track_id();
		if ( ! empty($track_id))
		{
			if ( ! is_numeric($track_id) or (strlen($track_id) > 20))
			{
				throw new \Exception('type error[track_id]', 7003);
			}
		}

		if (strlen($track_name) > 100)
		{
			throw new \Exception('type error[track_name]', 7003);
		}

		$page = $track_dto->get_page();
		if ( ! empty($page) and ! is_numeric($page))
		{
			throw new \Exception('type error[page]', 7003);
		}

		$limit = $track_dto->get_limit();
		if ( ! empty($limit) and ! is_numeric($limit))
		{
			throw new \Exception('type error[limit]', 7003);
		}

		return true;
	}

	/**
	 * バリデーション
	 * @throws \Exception
	 * @return boolean
	 */
	public static function validation_for_rank()
	{
		\Log::debug('[start]'. __METHOD__);

		//---------------------------
		// 必須項目エラーチェック
		//---------------------------

		//---------------------------
		// 型チェック
		//---------------------------

		return true;
	}



	/**
	 * バリデーション
	 * @throws \Exception
	 * @return boolean
	 */
	public static function validation_for_track_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dto = TrackDto::get_instance();

		$artist_id = $track_dto->get_artist_id();
		//---------------------------
		// 必須項目エラーチェック
		//---------------------------
		if (empty($artist_id))
		{
			throw new \Exception('require error[artist_id]', 7002);
		}


		//---------------------------
		// 型チェック
		//---------------------------

		if ( ! empty($artist_id))
		{
			if ( ! is_numeric($artist_id) or (strlen($artist_id) > 20))
			{
				throw new \Exception('type error[artist_id]', 7003);
			}
		}

		$track_name = $track_dto->get_track_name();
		if ( ! empty($track_name))
		{
			if (strlen($track_name) > 100)
			{
				throw new \Exception('type error[track_name]', 7003);
			}
		}

		static::_validation_for_paginate($track_dto);

		return true;
	}

	/**
	 * バリデーション
	 * @throws \Exception
	 * @return boolean
	 */
	public static function validation_for_track_one()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$track_dto = TrackDto::get_instance();

		$artist_id = $track_dto->get_artist_id();
		//---------------------------
		// 必須項目エラーチェック
		//---------------------------

		//---------------------------
		// 型チェック
		//---------------------------

		if ( ! empty($artist_id))
		{
			if ( ! is_numeric($artist_id) or (strlen($artist_id) > 20))
			{
				throw new \Exception('type error[artist_id]', 7003);
			}
		}

		$track_name = $track_dto->get_track_name();
		if ( ! empty($track_name))
		{
			if (strlen($track_name) > 500)
			{
				throw new \Exception('type error[track_name]', 7003);
			}
		}

		static::_validation_for_paginate($track_dto);

		return true;
	}


	/**
	 * バリデーション
	 * @throws \Exception
	 * @return boolean
	 */
	public static function validation_albumtracklist()
	{
		\Log::debug('[start]'. __METHOD__);

		$album_dto = AlbumDto::get_instance();

		//---------------------------
		// 必須項目エラーチェック
		//---------------------------
		$album_id = $album_dto->get_album_id();
		if (empty($album_id))
		{
			throw new \Exception('require error[artist_id]', 7002);
		}

		//---------------------------
		// 型チェック
		//---------------------------
		if ( ! empty($album_id))
		{
			if ( ! is_numeric($album_id))
			{
				throw new \Exception('type error[album_id]', 7003);
			}
		}

		return true;
	}


	private static function _validation_for_paginate($dto)
	{
		\Log::debug('[start]'. __METHOD__);

		$page = $dto->get_page();
		if (isset($page) and ( ! is_numeric($page)))
		{
			throw new \Exception('type error[page]', 7003);
		}
		$limit = $dto->get_limit();
		if (isset($limit) and ( ! is_numeric($limit)))
		{
			throw new \Exception('type error[limit]', 7003);
		}
	}


	public static function set_dto_from_request($obj_request)
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$album_dto  = AlbumDto::get_instance();
		$track_dto  = TrackDto::get_instance();
		foreach ($obj_request as $key => $val)
		{
			if (empty($val)) continue;
			if ($key === 'artist_id')
			{
				$artist_dto->set_artist_id(trim($val));
				$track_dto->set_artist_id(trim($val));
			}
			if ($key === 'artist_name')
			{
				$artist_dto->set_artist_name(trim($val));
				$track_dto->set_artist_name(trim($val));
			}
			if ($key === 'album_id')
			{
				$album_dto->set_album_id(trim($val));
				$track_dto->set_album_id(trim($val));
			}
			if ($key === 'album_name')
			{
				$album_dto->set_album_name(trim($val));
				$track_dto->set_album_name(trim($val));
			}
			if ($key === 'album_mbid_itunes')
			{
				$album_dto->set_mbid_itunes(trim($val));
			}
			if ($key === 'album_mbid_lastfm')
			{
				$album_dto->set_mbid_lastfm(trim($val));
			}
			if ($key === 'track_id')         $track_dto->set_track_id(trim($val));
			if ($key === 'track_name')       $track_dto->set_track_name(trim($val));
			if ($key === 'mbid_itunes')      $track_dto->set_mbid_itunes(trim($val));
			if ($key === 'mbid_lastfm')      $track_dto->set_mbid_lastfm(trim($val));
			if ($key === 'english')          $track_dto->set_english(trim($val));
			if ($key === 'kana')             $track_dto->set_kana(trim($val));
			if ($key === 'image_url')        $track_dto->set_image_url(trim($val));
			if ($key === 'image_small')      $track_dto->set_image_small($val);
			if ($key === 'image_medium')     $track_dto->set_image_medium($val);
			if ($key === 'image_large')      $track_dto->set_image_large($val);
			if ($key === 'image_extralarge') $track_dto->set_image_extralarge($val);
			if ($key === 'url_itunes')       $track_dto->set_url_itunes(trim($val));
			if ($key === 'url_lastfm')       $track_dto->set_url_lastfm(trim($val));
			if ($key === 'mbid_itunes')      $track_dto->set_mbid_itunes(trim($val));
			if ($key === 'mbid_lastfm')      $track_dto->set_mbid_lastfm(trim($val));
			if ($key === 'limit')            $track_dto->set_limit(trim($val));
			if ($key === 'page')             $track_dto->set_page(trim($val));
			if ($key === 'tracks')           $track_dto->set_tracks($val);
			if ($key === 'content')          $track_dto->set_content(trim($val));
			if ($key === 'track_album_mbid_itunes')
			{
				$track_dto->set_album_mbid_itunes(trim($val));
				$album_dto->set_mbid_itunes(trim($val));
			}
			if ($key === 'track_album_mbid_lastfm')
			{
				$track_dto->set_album_mbid_lastfm(trim($val));
				$album_dto->set_mbid_lastfm(trim($val));
			}
			if ($key === 'track_album_url_itunes')  $track_dto->set_album_url_itunes(trim($val));
			if ($key === 'track_album_url_lastfm')  $track_dto->set_album_url_lastfm(trim($val));
			if ($key === 'track_album_artist') $track_dto->set_artist_name(trim($val));
		}

		$artist_dao = new ArtistDao();
		$artist_id = $artist_dto->get_artist_id();
		if ( ! empty($artist_id))
		{
			$arr_artist = $artist_dao->get_artist_by_id($artist_id);
			$artist_dto->set_artist_name(current($arr_artist)->name);
			$artist_dto->set_mbid_itunes(current($arr_artist)->mbid_itunes);
			$artist_dto->set_mbid_lastfm(current($arr_artist)->mbid_lastfm);
		}
		return true;
	}


	public static function set_api_album_track_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_keys_mixed_api    = array_keys(static::$arr_album_track_list_mixed_api);
		$arr_keys_grooveonline = array_keys(static::$arr_album_track_list_from_grooveonline);
		$arr_diff = array_diff($arr_keys_mixed_api, $arr_keys_grooveonline);
		if (empty($arr_diff))
		{
			return true;
		}

		$album_dto = AlbumDto::get_instance();
		$artist_dto = ArtistDto::get_instance();

		$arr_values = array();
		foreach ($arr_diff as $val)
		{
			static::$arr_album_track_list_mixed_api[$val]['artist_id'] = $artist_dto->get_artist_id();
			static::$arr_album_track_list_mixed_api[$val]['album_id']  = $album_dto->get_album_id();
			$arr_values[] = static::$arr_album_track_list_mixed_api[$val];
		}
		$track_dao = new TrackDao();
		$track_dao->set_multi_values($arr_values, true);

		return true;
	}


	/**
	 *
	 */
	public static function set_albumtrack_from_api(array $arr_from_itunes, array $arr_from_grooveonline)
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$artist_dao = new ArtistDao();
		$album_dao  = new AlbumDao();
		$track_dao  = new TrackDao();

		$track_dao->start_transaction();

		// トラック情報を登録
		$arr_diff_tracks = static::_diff_track($arr_from_grooveonline, $arr_from_itunes);

		static::_set_track($arr_diff_tracks);

		$track_dao->commit_transaction();
	}


	/**
	 * insert or update
	 */
	public static function set_track_from_itunes()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dao = new TrackDao();
		$track_dao->set_insert_update_values(static::$arr_album_track_list_from_itunes);

		return true;
	}


	/**
	 * insert or update
	 */
	public static function set_track_from_lastfm()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dao = new TrackDao();
		$track_dao->set_insert_update_values(static::$arr_album_track_list_from_lastfm);

		return true;
	}


	public static function set_track_info()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dto = TrackDto::get_instance();
		$album_dto = AlbumDto::get_instance();
		$review_music_dto = ReviewMusicDto::get_instance();
		$arr_obj_tracks = $track_dto->get_tracks();

		if ($review_music_dto->get_is_delete() == 1)
		{
			return true;
		}

		$track_dao = new TrackDao();

		$arr_tracks = array();

		switch ($review_music_dto->get_about())
		{
			case 'album':

				foreach ($arr_obj_tracks as $i => $val)
				{
					if ( ! property_exists($val, 'track_name'))
					{
						continue;
					}

					$track_name = $val->track_name;

					# 同一アーティスト、アルバムで同名の曲がmst_trackに存在する場合は登録しない
					if ( ! $track_dao->is_empty_same_artist_same_album_by_title($track_name))
					{
						continue;
					}

					$track_id = $val->track_id;
					if (empty($track_id) or $track_id === 'null')
					{
						$track_name = $val->track_name;
						$english = '';
						if (Util::is_english($track_name))
						{
							$english = $track_name;
						}
						list($id, $count) = $track_dao->set_values(array(
							'artist_id'   => $track_dto->get_artist_id(),
							'album_id'    => $album_dto->get_album_id(),
							'name'        => $track_name,
							'english'     => $english,
							'name_api' => $track_name,
							'mbid_api' => $val->track_mbid,
							'url_api'  => $val->track_url,
 						));
						$arr_tracks[] = $id;
					}
				}

			break;

			case 'track':
				$track_name = $review_music_dto->get_track_name();

				# 同一アーティスト、アルバムで同名の曲がmst_track存在しない場合は登録する
				$result = $track_dao->is_empty_same_artist_same_album_by_track_album($track_name);
				if ($result === true)
				{
					$track_id = $track_dto->get_track_id();
					if (empty($track_id) or $track_id === 'null')
					{
						$track_name = $track_dto->get_track_name();
						$english = '';
						if (Util::is_english($track_name))
						{
							$english = $track_name;
						}

						list($id, $count) = $track_dao->set_values(array(
							'artist_id'          => $track_dto->get_artist_id(),
							'album_id'           => $track_dto->get_album_id(),
							'name'               => $track_name,
							'english'            => $english,
							'mbid_itunes'        => $track_dto->get_mbid_itunes(),
							'mbid_lastfm'        => $track_dto->get_mbid_lastfm(),
							'url_itunes'         => $track_dto->get_url_itunes(),
							'url_lastfm'         => $track_dto->get_url_lastfm(),
							'image_url'          => $track_dto->get_image_url(),
							'image_small'        => $track_dto->get_image_small(),
							'image_medium'       => $track_dto->get_image_medium(),
							'image_large'        => $track_dto->get_image_large(),
							'image_extralarge'   => $track_dto->get_image_extralarge(),
							'content'            => $track_dto->get_content(),
							'track_album_name'   => $track_dto->get_album_name(),
							'track_album_url'    => $track_dto->get_album_url(),
							'track_album_artist' => $track_dto->get_artist_name(),
						));

						// insert
						$track_dto->set_track_id($id);
					}
				}
				else
				{
					$id               = $result['id'];
					$mbid_itunes      = $result['mbid_itunes'];
					$mbid_lastfm      = $result['mbid_lastfm'];
					$image_url = $result['image_url'];
					$track_dto->set_track_id($id);

					$arr_where = array(
						'id' => $id,
					);
					if ( ! empty($mbid_itunes))
					{
						$arr_where['mbid_itunes'] = $mbid_itunes;
					}
					if ( ! empty($mbid_lastfm))
					{
						$arr_where['mbid_lastfm'] = $mbid_lastfm;
					}

					if (empty($image_url))
					{
						// update
						$track_dao->update_values(array(
							'image_url'          => $track_dto->get_image_url(),
							'image_small'        => $track_dto->get_image_small(),
							'image_medium'       => $track_dto->get_image_medium(),
							'image_large'        => $track_dto->get_image_large(),
							'image_extralarge'   => $track_dto->get_image_extralarge(),
							'content'            => $track_dto->get_content(),
							'track_album_name'   => $track_dto->get_album_name(),
							'track_album_url_itunes'    => $track_dto->get_album_url_itunes(),
							'track_album_url_lastfm'    => $track_dto->get_album_url_lastfm(),
							'track_album_artist' => $track_dto->get_artist_name(),
						), $arr_where);
					}
					else
					{
						// 画像情報が入ってたらスルー
						continue;
					}
				}

				break;
		}

		return true;
	}


	private static function _set_track(array $arr_tracks)
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$arr_request = array();
		foreach ($arr_tracks as $val)
		{
			$arr_request[] = array(
				'artist_id'        => $artist_dto->get_artist_id(),
				'album_id'         => $val['album_id'],
				'name'             => $val['name'],
				'kana'             => empty($val['kana'])? '': $val['kana'],
				'english'          => empty($val['english'])? '': $val['english'],
				'same_names'       => empty($val['same_names'])? '': $val['same_names'],
				'mbid_itunes'      => $val['mbid_itunes'],
				'mbid_lastfm'      => $val['mbid_lastfm'],
				'url_itunes'       => $val['url_itunes'],
				'url_lastfm'       => $val['url_lastfm'],
				'image_url'    => $val['image_url'],
				'image_small'      => $val['image_small'],
				'image_medium'     => $val['image_medium'],
				'image_large'      => $val['image_large'],
				'image_extralarge' => $val['image_extralarge'],
				'content'          => '',
				'number'           => $val['number'],
			);
		}
		$track_dao = new TrackDao();
		return $track_dao->set_multi_values($arr_request, true);
	}


	private static function _set_track_album(array $arr_item)
	{
		\Log::debug('[start]'. __METHOD__);

		$album_dao  = new AlbumDao();

		$english = null;
		if (Util::is_english($arr_item['track_album_name']))
		{
			$english = $arr_item['track_album_name'];
		}
		$same_names       = ' '. Util::same_name_replace($arr_item['track_album_name']);
		$image            = $arr_item['image_medium'];
		$image_small      = $arr_item['image_small'];
		$image_medium     = $arr_item['image_medium'];
		$image_large      = preg_replace('/100x100-75/', '150x150-75', $image);
		$image_extralarge = preg_replace('/100x100-75/', '200x200-75', $image);

		// アルバムをインサート
		$arr_album_request = array();
		$arr_album_request[] = array(
			'id'               => null,
			'artist_id'        => $arr_item['artist_id'],
			'name'             => $arr_item['track_album_name'],
			'kana'             => null,
			'english'          => $english,
			'same_names'       => $same_names,
			'mbid_itunes'      => $arr_item['mbid_itunes'],
			'mbid_lastfm'      => null,
			'url_itunes'       => $arr_item['url_itunes'],
			'url_lastfm'       => null,
			'image_url'        => $image,
			'image_small'      => $image_small,
			'image_medium'     => $image_medium,
			'image_large'      => $image_large,
			'image_extralarge' => $image_extralarge,
		);

		list($album_id, $count) = $album_dao->set_multi_values($arr_album_request, true);

		return $album_id;
	}



	public static function get_album_track_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dto = TrackDto::get_instance();
		$album_dto = AlbumDto::get_instance();
		AlbumService::get_album_by_id_to_dto($album_dto->get_album_id());

		/* grooveonlineからアルバムトラックリストを取得 */
		static::get_album_track_list_from_grooveonline();

		/* itunesからアルバムトラックリストを取得 */
		static::get_album_track_list_from_itunes();

		// 差分をインサート
		static::set_albumtrack_from_api(static::$arr_album_track_list_from_itunes, static::$arr_album_track_list_from_grooveonline);

		/* lastfmからアルバムトラックリストを取得 */
		static::get_album_track_list_from_lastfm();

		// 差分をインサート
		static::set_albumtrack_from_api(static::$arr_album_track_list_from_lastfm, static::$arr_album_track_list_from_grooveonline);

		/* 再度grooveonlineからアルバムトラックリストを取得 */
		static::get_album_track_list_from_grooveonline();

		$track_dto->set_arr_list(static::$arr_album_track_list_from_grooveonline);

		return true;
	}


	/**
	 * トラックIDから詳細情報を取得
	 * @return boolean
	 */
	public static function get_info()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dto = TrackDto::get_instance();
		$track_dao = new TrackDao();
		$track_dto->set_arr_list($track_dao->get_track_detail());

		return true;
	}

	/**
	 * トラック情報を検索しつつインサートも行う
	 * @throws \Exception
	 * @return boolean
	 */
	public static function get_track_list()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$track_dto = TrackDto::get_instance();
			$album_dto = AlbumDto::get_instance();

			static::$arr_search['album_id']    = $album_dto->get_album_id();

			//* 検索ワード期限を確認＆登録 *//
			static::search_words();

			if (static::$search_only === false)
			{
				$mbid_itunes = $album_dto->get_mbid_itunes();
				$mbid_lastfm = $album_dto->get_mbid_lastfm();

				if ( ! empty($mbid_itunes))
				{
					/* itunesからアルバムトラックリストを取得 */
					static::get_album_track_list_from_itunes();

					/* トランザクション開始 */
					static::start_transaction();

					/* itunesからの取得した未登録のトラックを登録 */
					static::set_track_from_itunes();

					/* DBに検索アルバムIDを登録する */
					static::regist_words();

					/* コミット */
					static::commit_transaction();
				}
				else if ( ! empty($mbid_lastfm))
				{
					/* lastfmからアルバムトラックリストを取得 */
					static::get_album_track_list_from_lastfm();

					/* トランザクション開始 */
					static::start_transaction();

					/* lastfmからの取得した未登録のトラックを登録 */
					static::set_track_from_lastfm();

					/* DBに検索アルバムIDを登録する */
					static::regist_words();

					/* コミット */
					static::commit_transaction();
				}
			}

			/* grooveonlineからアルバムトラックリストを取得 */
			static::get_album_track_list_from_grooveonline();

			return true;
		}
		catch (\Exception $e)
		{
			static::rollback_transaction();
			throw new \Exception($e);
		}
	}


	/**
	 * 検索ワードからトラックリストを取得
	 *
	 * @throws \Exception
	 * @return boolean
	 */
	public static function search_track_list()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$artist_dto = ArtistDto::get_instance();
			$track_dto  = TrackDto::get_instance();

			static::$arr_search['track_name']    = $track_dto->get_track_name();

			//* 検索ワード期限を確認＆登録 *//
			static::search_words_by_word();

			if (static::$search_only === false)
			{
				//* アーティスト名を取得
				static::get_artist_by_id();

				//* itunesからアルバムトラックリストを取得
				static::search_word_track_list_from_itunes();

				//* トランザクション開始
				static::start_transaction();

				//* itunesからの取得した未登録のトラックを登録
				static::set_track_from_itunes();

				//* DBに検索ワードを登録する
				static::regist_words_by_word();

				//* コミット
				static::commit_transaction();
			}

			/* grooveonlineからアルバムトラックリストを取得 */
			static::search_word_track_list_from_grooveonline();

			return true;
		}
		catch (\Exception $e)
		{
			static::rollback_transaction();
			throw new \Exception($e);
		}
	}


	public static function get_artist_by_id()
	{
		\Log::info('[start]'. __METHOD__);

		$artist_dao = new ArtistDao();
		$artist_dto = ArtistDto::get_instance();

		$arr_result = $artist_dao->get_artist_by_id($artist_dto->get_artist_id());
		$artist_name = current($arr_result)->name;
		$mbid_itunes = current($arr_result)->mbid_itunes;
		$artist_dto->set_artist_name($artist_name);
		$artist_dto->set_mbid_itunes($mbid_itunes);

		return true;
	}


	public static function get_album_track_list_from_grooveonline()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dao = new TrackDao();
		$track_dto = TrackDto::get_instance();

		$arr_result = $track_dao->get_track_list_by_album_id();

		foreach ($arr_result as $val)
		{
			static::$arr_album_track_list_from_grooveonline[] = $val;
		}

		$track_dto->set_arr_list(static::$arr_album_track_list_from_grooveonline);

		return true;
	}


	public static function get_track_list_from_grooveonline()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dao = new TrackDao();

		foreach ($track_dao->get_track_list() as $val)
		{
			static::$arr_track_list_from_grooveonline[mb_strtoupper($val['name'])] = $val;
		}

		return true;
	}


	/**
	 * 検索ワードテーブルへのアクセス
	 * @return boolean
	 */
	public static function search_words()
	{
		\Log::debug('[start]'. __METHOD__);

		$album_dto = AlbumDto::get_instance();

		static::$search_only = false;
		static::$arr_search['album_id']   = $album_dto->get_album_id();
		static::$arr_search['album_name'] = preg_replace('/[&=]/', '', trim($album_dto->get_album_name()));

		$search_track_dao = new SearchTrackDao();

		# トランザクション開始
		$search_track_dao->start_transaction();

		# 検索アーティスト情報を取得
		$arr_where = array(
		'word' => static::$arr_search['album_id'],
		);
		# 行ロック
		$arr_result = $search_track_dao->set_lock($arr_where);

		# 検索結果が0なら後ほどインサート
		if (empty($arr_result))
		{
			\Log::info("未登録の検索アルバムIDです(".static::$arr_search['album_name']. ":". static::$arr_search['album_id']. ")");

			# コミット
			$search_track_dao->commit_transaction();

			return true;
		}

		# 存在し期間外ならば更新日、カウントをアップデート
		$expired_plus = 60 * 60 * 24 * static::$api_expired_days;
		if (\Date::forge()->get_timestamp() > strtotime($arr_result['took_at']) + $expired_plus)
		{
			$arr_values = array(
					'count' => \DB::expr('count + 1'),
					'took_at' => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
			);
		}
		# 存在し期間内ならばカウントのみアップデート
		else
		{
			$arr_values = array(
					'count' => \DB::expr('count + 1')
			);
			# 存在し期間内ならば参照オンリーメンバ変数をセット
			static::$search_only = true;
			# 検索ワードをメンバ変数にセット
			static::$arr_search['album_id'] = preg_replace('/[&=]/', '', trim($arr_result['exchange_word']));
		}
		$search_track_dao->update_values($arr_values, $arr_result['id']);

		# コミット
		$search_track_dao->commit_transaction();

		return true;
	}


	/**
	 * 検索ワードテーブルへのアクセス(ワード検索時)
	 * @return boolean
	 */
	public static function search_words_by_word()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$track_dto  = TrackDto::get_instance();

		static::$search_only = false;
		static::$arr_search['track_name'] = preg_replace('/[&=]/', '', trim($track_dto->get_track_name()));

		$search_track_word_dao = new SearchTrackWordDao();

		# トランザクション開始
		$search_track_word_dao->start_transaction();

		# 検索アーティスト情報を取得
		$arr_where = array(
			'word' => static::$arr_search['track_name'],
			'artist_id' => $artist_dto->get_artist_id(),
		);
		# 行ロック
		$arr_result = $search_track_word_dao->set_lock($arr_where);

		# 検索結果が0なら後ほどインサート
		if (empty($arr_result))
		{
			\Log::info("未登録の検索トラック名です(".static::$arr_search['track_name']. ")");

			# コミット
			$search_track_word_dao->commit_transaction();

			return true;
		}

		# 存在し期間外ならば更新日、カウントをアップデート
		$expired_plus = 60 * 60 * 24 * static::$api_expired_days_word;
		if (\Date::forge()->get_timestamp() > strtotime($arr_result['took_at']) + $expired_plus)
		{
			$arr_values = array(
				'count'   => \DB::expr('count + 1'),
				'took_at' => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
			);
		}
		# 存在し期間内ならばカウントのみアップデート
		else
		{
			$arr_values = array(
				'count' => \DB::expr('count + 1')
			);

			# 存在し期間内ならば参照オンリーメンバ変数をセット
			static::$search_only = true;

			# 検索ワードをメンバ変数にセット
			static::$arr_search['track_name'] = preg_replace('/[&=]/', '', trim($arr_result['exchange_word']));
		}
		$search_track_word_dao->update_values($arr_values, $arr_result['id']);

		# コミット
		$search_track_word_dao->commit_transaction();

		return true;
	}


	/**
	 * 検索ワードテーブルへのアクセス
	 * @return boolean
	 */
	public static function regist_words()
	{
		\Log::debug('[start]'. __METHOD__);

		$search_track_dao = new SearchTrackDao();
		$album_dto = AlbumDto::get_instance();

		# ワード検索結果が0ならインサート
		if (static::$search_only === false)
		{
			$arr_values = array(
					'word'          => $album_dto->get_album_id(),
					'count'         => 1,
					'took_at'       => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
					'exchange_word' => static::$arr_search['album_id'],
			);
			$search_track_dao->set_values($arr_values);
		}

		return true;
	}


	/**
	 * 検索ワードテーブルへのアクセス(検索時)
	 * @return boolean
	 */
	public static function regist_words_by_word()
	{
		\Log::debug('[start]'. __METHOD__);

		$search_track_word_dao = new SearchTrackWordDao();
		$artist_dto = ArtistDto::get_instance();
		$track_dto  = TrackDto::get_instance();

		# ワード検索結果が0ならインサート
		if (static::$search_only === false)
		{
			$arr_values = array(
					'word'          => $track_dto->get_track_name(),
					'artist_id'     => $artist_dto->get_artist_id(),
					'count'         => 1,
					'took_at'       => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
					'exchange_word' => static::$arr_search['track_name'],
			);
			$search_track_word_dao->set_values($arr_values);
		}

		return true;
	}


	/**
	 * LastFmAPIからアーティスト名をキーにアルバム情報を取得
	 * アルバムトラックス取得
	 * @return boolean
	 */
	public static function get_album_track_list_from_lastfm()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$artist_dto = ArtistDto::get_instance();
			$album_dto  = AlbumDto::get_instance();
			$track_dto  = TrackDto::get_instance();

			$arr_params = array(
				'method'  => 'album.getInfo',
				'api_key' => \Config::get('lastfm.api_key'),
				'artist'  => $artist_dto->get_artist_name(),
				'album'   => $album_dto->get_album_name(),
				'mbid'    => $album_dto->get_mbid_lastfm(),
				'format'  => 'json',
				'limit'   => $track_dto->get_limit(),
			);

			$url = \Config::get('lastfm.url_rest'). http_build_query($arr_params);
			\Log::info($url);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, \Config::get('lastfm.api_exec_timeout'));
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, \Config::get('lastfm.api_connect_timeout'));
			$json_response = curl_exec($ch);
			curl_close($ch);

			$obj_response = json_decode($json_response);

			if (empty($obj_response))
			{
				throw new \Exception('lastfm response failed');
			}

			if ( ! property_exists($obj_response, 'album'))
			{
				throw new \Exception('lastfm response failed album');
			}

			if ( ! property_exists($obj_response->album, 'tracks'))
			{
				throw new \Exception('lastfm response failed tracks');
			}

			if ( ! property_exists($obj_response->album->tracks, 'track'))
			{
				throw new \Exception('lastfm response failed track');
			}

			if (count($obj_response->album->tracks->track) === 1)
			{
				$arr_obj_album_track[] = $obj_response->album->tracks->track;
			}
			else
			{
				$arr_obj_album_track = $obj_response->album->tracks->track;
			}

			foreach($arr_obj_album_track as $i => $item)
			{
				$name = $item->name;
				if (empty($item->mbid))
				{
					$mbid = null;
				}
				$mbid = $item->mbid;

				$english = null;
				if (Util::is_english($name))
				{
					$english = $name;
				}
				$same_names = ' '. Util::same_name_replace($name);
				static::$arr_album_track_list_from_lastfm[] = array(
						'id'          => '',
						'artist_id'        => $artist_dto->get_artist_id(),
						'album_id'         => $album_dto->get_album_id(),
						'name'             => $name,
						'kana'             => '',
						'english'          => $english,
						'same_names'       => $same_names,
						'mbid_itunes'      => '',
						'mbid_lastfm'      => $mbid,
						'url_itunes'       => '',
						'url_lastfm'       => $item->url,
						'image_url'        => $album_dto->get_image_url(),
						'image_small'      => $album_dto->get_image_small(),
						'image_medium'     => $album_dto->get_image_medium(),
						'image_large'      => $album_dto->get_image_large(),
						'image_extralarge' => $album_dto->get_image_extralarge(),
						'release_itunes'   => '',
						'release_lastfm'   => $obj_response->album->releasedate,
						'genre_itunes'     => '',
						'duration'         => $item->duration,
						'preview_itunes'   => '',
						'number'           => end($item)->rank,
				);
			} //foreach

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage(). '['. $e->getCode().']');
			\Log::error($e->getFile(). '['. $e->getLine(). ']');
			\Log::error('Lastfmネットワークエラー');
			return false;
		}
	}


	/**
	 * itunesからアーティストIDをキーにアルバム情報を取得
	 * アルバムトラックス取得
	 * @return boolean
	 */
	public static function get_album_track_list_from_itunes()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$artist_dto = ArtistDto::get_instance();
			$album_dto  = AlbumDto::get_instance();
			$track_dto  = TrackDto::get_instance();

			$arr_params = array(
				'country' => 'JP',
				'media'   => 'music',
				'entity'  => 'song',
				'id'      => $album_dto->get_mbid_itunes(),
				'format'  => 'json',
			);
			//https://itunes.apple.com/search/?country=JP&media=music&entity=song&id=79217&format=json&term=she thin lizzy&attribute=songTerm
			$url = \Config::get('itunes.url_lookup'). http_build_query($arr_params);
\Log::info($url);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, \Config::get('itunes.api_exec_timeout'));
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, \Config::get('itunes.api_connect_timeout'));
			$json_response = curl_exec($ch);
			curl_close($ch);

			$obj_response = json_decode($json_response);

			if (empty($obj_response))
			{
				throw new \Exception('itunes response failed');
			}

			if ( ! property_exists($obj_response, 'results'))
			{
				throw new \Exception('itunes response failed');
			}

			foreach($obj_response->results as $i => $item)
			{
				if ( ! property_exists($item, 'trackName'))
				{
					continue;
				}

				$name = $item->trackName;
				$english = null;
				if (Util::is_english($name))
				{
					$english = $name;
				}

				$same_names = ' '. Util::same_name_replace($name);
				$image            = $item->artworkUrl100;
				$image_small      = $item->artworkUrl60;
				$image_medium     = $item->artworkUrl100;
				$image_large      = preg_replace('/100x100-75/', '150x150-75', $image);
				$image_extralarge = preg_replace('/100x100-75/', '200x200-75', $image);
				$duration = '';
				if (property_exists($item, 'trackTimeMillis'))
				{
					$duration = round($item->trackTimeMillis/1000);
				}
				$preview_itunes = '';
				if (property_exists($item, 'previewUrl'))
				{
					$preview_itunes = $item->previewUrl;
				}

				static::$arr_album_track_list_from_itunes[] = array(
						'id'               => '',
						'artist_id'        => $artist_dto->get_artist_id(),
						'album_id'         => $album_dto->get_album_id(),
						'name'             => $name,
						'kana'             => '',
						'english'          => $english,
						'same_names'       => $same_names,
						'mbid_itunes'      => $item->trackId,
						'mbid_lastfm'      => '',
						'url_itunes'       => $item->collectionViewUrl,
						'url_lastfm'       => '',
						'image_url'        => $item->artworkUrl100,
						'image_small'      => $image_small,
						'image_medium'     => $image_medium,
						'image_large'      => $image_large,
						'image_extralarge' => $image_extralarge,
						'release_itunes'   => $item->releaseDate,
						'genre_itunes'     => $item->primaryGenreName,
						'duration'         => $duration,
						'preview_itunes'   => $preview_itunes,
						'number'           => $item->trackNumber,
				);
			} //foreach

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage(). '['. $e->getCode().']');
			\Log::error($e->getFile(). '['. $e->getLine(). ']');
			\Log::error('itunesネットワークエラー');
			throw new \Exception($e);
		}
	}


	/**
	 * itunesから検索ワードをキーにトラックを取得
	 *
	 * @return boolean
	 */
	public static function search_word_track_list_from_itunes()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$artist_dto = ArtistDto::get_instance();
			$album_dto  = AlbumDto::get_instance();
			$track_dto  = TrackDto::get_instance();

			$arr_params = array(
				'country' => 'JP',
				'media'   => 'music',
				'entity'  => 'song',
				'format'  => 'json',
				'term'      => $artist_dto->get_artist_name(). ' '. $track_dto->get_track_name(),
			);
			// https://itunes.apple.com/search/?country=JP&media=music&entity=song&format=json&term=Thin+Lizzy+moon
			$url = \Config::get('itunes.url_rest'). http_build_query($arr_params);
			\Log::info($url);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, \Config::get('itunes.api_exec_timeout'));
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, \Config::get('itunes.api_connect_timeout'));
			$json_response = curl_exec($ch);
			curl_close($ch);


			$obj_response = json_decode($json_response);
\Log::info($obj_response);

			if (empty($obj_response))
			{
				throw new \Exception('itunes response failed');
			}

			if ( ! property_exists($obj_response, 'results'))
			{
				throw new \Exception('itunes response failed');
			}

			foreach($obj_response->results as $i => $item)
			{
				if ( ! property_exists($item, 'trackName'))
				{
					continue;
				}

				if ($item->artistId != $artist_dto->get_mbid_itunes())
				{
					\Log::info('continue');
					continue;
				}

				$name = $item->trackName;
				$english = null;
				if (Util::is_english($name))
				{
					$english = $name;
				}

				$same_names = ' '. Util::same_name_replace($name);
				$image            = $item->artworkUrl100;
				$image_small      = $item->artworkUrl60;
				$image_medium     = $item->artworkUrl100;
				$image_large      = preg_replace('/100x100-75/', '150x150-75', $image);
				$image_extralarge = preg_replace('/100x100-75/', '200x200-75', $image);

				$album_dao = new AlbumDao();
				$arr_album = $album_dao->get_album(
						array(
							'mbid_itunes' => $item->collectionId,
						)
				);
				if (empty($arr_album))
				{
					$album_id = '';
				}
				else
				{
					$album_id = current($arr_album)->id;
				}

				static::$arr_album_track_list_from_itunes[] = array(
						'id'               => '',
						'artist_id'        => $artist_dto->get_artist_id(),
						'album_id'         => $album_id,
						'name'             => $name,
						'kana'             => '',
						'english'          => $english,
						'same_names'       => $same_names,
						'mbid_itunes'      => $item->trackId,
						'mbid_lastfm'      => '',
						'url_itunes'       => $item->collectionViewUrl,
						'url_lastfm'       => '',
						'image_url'        => $item->artworkUrl100,
						'image_small'      => $image_small,
						'image_medium'     => $image_medium,
						'image_large'      => $image_large,
						'image_extralarge' => $image_extralarge,
						'release_itunes'   => $item->releaseDate,
						'genre_itunes'     => $item->primaryGenreName,
						'duration'         => round($item->trackTimeMillis/1000),
						'preview_itunes'   => isset($item->previewUrl)? $item->previewUrl: '',
						'number'           => $item->trackNumber,
				);
			} //foreach

		//	\Log::info(static::$arr_album_track_list_from_itunes);

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage(). '['. $e->getCode().']');
			\Log::error($e->getFile(). '['. $e->getLine(). ']');
			\Log::error('itunesネットワークエラー');
			throw new \Exception($e);
		}
	}


	/**
	 * LastFmAPIからアーティスト名をキーにアルバム情報を取得
	 * アルバムトラックス取得
	 * @return boolean
	 */
	public static function search_word_track_list_from_lastfm()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$artist_dto = ArtistDto::get_instance();
			$album_dto  = AlbumDto::get_instance();
			$track_dto  = TrackDto::get_instance();

			$arr_params = array(
					'method'  => 'track.search',
					'api_key' => \Config::get('lastfm.api_key'),
					'artist'  => $artist_dto->get_artist_name(),
					'track'   => $track_dto->get_track_name(),
					'format'  => 'json',
					'limit'   => $track_dto->get_limit(),
			);

			$url = \Config::get('lastfm.url_rest'). http_build_query($arr_params);
			\Log::info($url);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, \Config::get('lastfm.api_exec_timeout'));
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, \Config::get('lastfm.api_connect_timeout'));
			$json_response = curl_exec($ch);
			curl_close($ch);

			$obj_response = json_decode($json_response);

			if (empty($obj_response))
			{
				throw new \Exception('lastfm response failed');
			}

			if ( ! property_exists($obj_response, 'results'))
			{
				throw new \Exception('lastfm response failed results');
			}

			if ( ! property_exists($obj_response->results, 'trackmatches'))
			{
				throw new \Exception('lastfm response failed trackmatches');
			}

			if ( ! property_exists($obj_response->results->trackmatches, 'track'))
			{
				throw new \Exception('lastfm response failed track');
			}

			if (count($obj_response->results->trackmatches->track) === 1)
			{
				$arr_obj_track[] = $obj_response->results->trackmatches->track;
			}
			else
			{
				$arr_obj_track = $obj_response->results->trackmatches->track;
			}

			foreach($arr_obj_track as $i => $item)
			{
				$name = $item->name;
				$mbid = $item->mbid;
				if (empty($mbid))
				{
					//continue;
				}

				$english = null;
				if (Util::is_english($name))
				{
					$english = $name;
				}
				$same_names = ' '. Util::same_name_replace($name);
				static::$arr_album_track_list_from_lastfm[] = array(
						'id'          => '',
						'artist_id'        => $artist_dto->get_artist_id(),
						'album_id'         => $album_dto->get_album_id(),
						'name'             => $name,
						'kana'             => '',
						'english'          => $english,
						'same_names'       => $same_names,
						'mbid_itunes'      => '',
						'mbid_lastfm'      => $mbid,
						'url_itunes'       => '',
						'url_lastfm'       => $item->url,
						'image_url'        => $album_dto->get_image_url(),
						'image_small'      => $album_dto->get_image_small(),
						'image_medium'     => $album_dto->get_image_medium(),
						'image_large'      => $album_dto->get_image_large(),
						'image_extralarge' => $album_dto->get_image_extralarge(),
						'release_itunes'   => '',
						'release_lastfm'   => '',
						'genre_itunes'     => '',
						'duration'         => '',
						'preview_itunes'   => '',
						'number'           => '',
				);
			} //foreach

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage(). '['. $e->getCode().']');
			\Log::error($e->getFile(). '['. $e->getLine(). ']');
			\Log::error('Lastfmネットワークエラー');
			return false;
		}
	}


	/**
	 * 検索ワードでトラックリストを取得
	 */
	public static function search_word_track_list_from_grooveonline()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dao = new TrackDao();
		$track_dto = TrackDto::get_instance();

		$arr_result = $track_dao->search_track_list();

		foreach ($arr_result as $val)
		{
			static::$arr_album_track_list_from_grooveonline[] = $val;
		}

		$track_dto->set_arr_list(static::$arr_album_track_list_from_grooveonline);

		return true;
	}


	/**
	 * itunesから曲名をキーにアルバム情報を取得
	 * トラックを取得
	 * @return boolean
	 */
	public static function get_track_from_itunes()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$artist_dto = ArtistDto::get_instance();
			$album_dto  = AlbumDto::get_instance();
			$track_dto  = TrackDto::get_instance();

			$term = $artist_dto->get_artist_name(). ' '. $track_dto->get_track_name();

			$arr_params = array(
				'country' => 'JP',
				'media'   => 'music',
				'entity'  => 'song',
				'term'    => $term,
				'format'  => 'json',
			);
			$url = \Config::get('itunes.url_rest'). http_build_query($arr_params);
\Log::debug($url);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, \Config::get('itunes.api_exec_timeout'));
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, \Config::get('itunes.api_connect_timeout'));
			$json_response = curl_exec($ch);
			curl_close($ch);
			$obj_response = json_decode($json_response);

			if (empty($obj_response))
			{
				throw new \Exception('itunes response failed');
			}

			if ( ! property_exists($obj_response, 'results'))
			{
				throw new \Exception('itunes response failed');
			}

			foreach($obj_response->results as $i => $item)
			{
				if ( ! property_exists($item, 'trackName'))
				{
					continue;
				}

				$name = $item->trackName;
				$mbid = $item->trackId;

				$english = null;
				if (Util::is_english($name))
				{
					$english = $name;
				}

				$same_names = ' '. Util::same_name_replace($name);
				$image            = $item->artworkUrl100;
				$image_small      = $item->artworkUrl60;
				$image_medium     = $item->artworkUrl100;
				$image_large      = preg_replace('/100x100-75/', '150x150-75', $image);
				$image_extralarge = preg_replace('/100x100-75/', '200x200-75', $image);

				static::$arr_track_list_from_itunes[$item->trackNumber] = array(
						'id'                => '',
						'name'              => $name,
						'kana'              => '',
						'english'           => $english,
						'same_names'        => $same_names,
						'mbid_itunes'       => $mbid,
						'mbid_lastfm'       => '',
						'url_itunes'        => $item->collectionViewUrl,
						'url_lastfm'        => '',
						'image_url'         => $item->artworkUrl100,
						'image_small'       => $image_small,
						'image_medium'      => $image_medium,
						'image_large'       => $image_large,
						'image_extralarge'  => $image_extralarge,
						'track_artist_name' => $item->artistName,
						'track_artist_id'   => $item->artistId,
						'track_album_name'  => $item->collectionName,
						'track_album_id'    => $item->collectionId,
						'number'            => $item->trackNumber,
				);
			} //foreach

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage(). '['. $e->getCode().']');
			\Log::error($e->getFile(). '['. $e->getLine(). ']');
			\Log::error('itunesネットワークエラー');
			throw new \Exception($e);
		}
	}

	/**
	 * アルバムIDからトラックを取得
	 */
	public static function get_track_by_album_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dao = new TrackDao();
		return $track_dao->get_track_list();
	}

	/**
	 * トラックIDからトラックを取得
	 */
	public static function get_track_by_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$track_dao  = new TrackDao();
		$arr_result = current($track_dao->get_track_detail());
		$track_dto  = TrackDto::get_instance();
		foreach ($arr_result as $column => $val)
		{
			if ($column === 'name')
			{
				$column = 'track_name';
			}
			$method = 'set_'. $column;
			if (method_exists($track_dto, $method))
			{
				$track_dto->$method($val);
			}
		}
		return true;
	}


	/**
	 * LastFmAPIからトラック情報をキーにトラック詳細情報を取得
	 * トラック取得
	 * @return boolean
	 */
	public static function get_one_by_lastfm() // @todo
	{
		try
		{
			\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

			$track_dto = TrackDto::get_instance();

			$arr_params = array(
				'method'  => 'track.getInfo',
				'api_key' => \Config::get('lastfm.api_key'),
				'format'  => 'json',
				'artist'  => $track_dto->get_artist_name(),
				'track'   => $track_dto->get_track_name(),
				'mbid'    => $track_dto->get_track_mbid(),
				'limit'   => $track_dto->get_limit(),
			);

			$url = \Config::get('lastfm.url_rest'). http_build_query($arr_params);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, \Config::get('lastfm.api_exec_timeout'));
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, \Config::get('lastfm.api_connect_timeout'));
			$json_response = curl_exec($ch);
			curl_close($ch);

			$obj_response = json_decode($json_response);
			if ( ! property_exists($obj_response, 'track'))
			{
				throw new \Exception('response not exist track');
			}

			$obj_response_track = $obj_response->track;
			if (property_exists($obj_response_track, 'name'))
			{
				$track_name = $obj_response->track->name;
				$english = '';
				if (Util::is_english($track_name))
				{
					$english = $track_name;
				}

				$album_name = '';
				$album_mbid = '';
				$album_image = '';
				$album_url = '';
				$album_artist = '';
				if (property_exists($obj_response_track, 'album'))
				{
					$album_name = $obj_response_track->album->title;
					$album_mbid = $obj_response_track->album->mbid;
					$album_image = current($obj_response_track->album->image[2]);
					$album_url = $obj_response_track->album->url;
					$album_artist = $obj_response_track->album->artist;
				}
				$content = '';
				if (property_exists($obj_response_track, 'wiki'))
				{
					$content = $obj_response_track->wiki->content;
				}

				static::$arr_lastfm_result[$track_name] = array(
						'id' => null,
						'name' => $track_name,
						'kana' => null,
						'english' => $english,
						'same_names' => null,
						'name_api' => $track_name,
						'mbid_api' => $obj_response_track->mbid,
						'mbid_itunes' => null,
						'mbid_lastfm' => $obj_response_track->mbid,
						'url_api' => $obj_response_track->url,
						'url_itunes' => null,
						'url_lastfm' => $obj_response_track->url,
						'image_url' => $album_image,
						'content' => $content,
						'track_album_name' => $album_name,
						'track_album_mbid' => $album_mbid,
						'track_album_url' => $album_url,
						'track_album_artist' => $album_artist,
				);
			}

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage(). '['. $e->getCode().']');
			\Log::error($e->getFile(). '['. $e->getLine(). ']');
			\Log::error('Lastfmネットワークエラー');
			throw new \Exception($e);
		}
	}


	public static function merge_album_track_list_all()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);
		static::$arr_album_track_list_all = array_values(array_merge(static::$arr_album_track_list_from_grooveonline, static::$arr_album_track_list_mixed_api));

		return true;
	}

	public static function merge_track_list_all()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);
		static::$arr_track_list_all = array_values(array_merge(static::$arr_track_list_from_grooveonline, static::$arr_album_track_list_mixed_api));

		return true;
	}

	public static function merge_album_track_list_from_api_service()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		static::$arr_album_track_list_mixed_api = array_merge(static::$arr_album_track_list_from_lastfm, static::$arr_album_track_list_from_itunes);

		return true;
	}

	public static function merge_track_list_from_api_service()
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		static::$arr_track_list_mixed_api = array_merge(static::$arr_track_list_from_lastfm, static::$arr_track_list_from_itunes);

		return true;
	}


	private static function _diff_track(array $arr_track_gol, array $arr_track_api)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$arr_key_gol  = array_keys($arr_track_gol);
		$arr_key_api  = array_keys($arr_track_api);
		$arr_key_diff = array_diff($arr_key_api, $arr_key_gol); // apiで取得した配列の中にgolに含まれていないものを抽出
		$arr_return = array();
		foreach ($arr_key_diff as $val)
		{
			$arr_return[] = $arr_track_api[$val];
		}

		return $arr_return;
	}

	private static function _convert_artist_name_lookup_by_itunes($id)
	{
		try
		{
			\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

			$arr_params = array(
				//'country' => 'JP',
				'media'   => 'music',
				'format'  => 'json',
				'id'      => $id,
			);
			$url = \Config::get('itunes.url_lookup'). http_build_query($arr_params);
			\Log::debug($url);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, \Config::get('itunes.api_exec_timeout'));
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, \Config::get('itunes.api_connect_timeout'));
			$json_response = curl_exec($ch);
			curl_close($ch);
			$obj_response = json_decode($json_response);

			if (empty($obj_response))
			{
				throw new \Exception('itunes response failed');
			}

			if ( ! property_exists($obj_response, 'results'))
			{
				throw new \Exception('itunes response failed');
			}

			$obj_result = $obj_response->results;
			if (empty($obj_result))
			{
				return '';
			}

			return $obj_response->results[0]->artistName;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage(). '['. $e->getCode().']');
			\Log::error($e->getFile(). '['. $e->getLine(). ']');
			\Log::error('itunesネットワークエラー');
			throw new \Exception($e);
		}
	}
}