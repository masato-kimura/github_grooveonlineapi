<?php
namespace main\domain\service;

use Fuel\Core\Validation;
use main\model\dto\AlbumDto;
use main\model\dto\TrackDto;
use main\model\dto\ArtistDto;
use main\model\dao\AlbumDao;
use main\model\dao\SearchAlbumDao;

class AlbumService extends Service
{
	private static $arr_album_list_from_lastfm = array();
	private static $arr_album_list_from_itunes = array();
	private static $arr_album_list_mixed_api   = array();
	private static $arr_album_detail           = array();
	private static $arr_search                 = array();
	private static $search_only                = false;
	private static $api_expired_days           = 3; // days apiにアクセスしない期間


	public static function validation_for_list()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($validate);

		# 個別バリデート設定
		$validate->add('artist_id', 'アーティストID')
			->add_rule('required')
			->add_rule('valid_string', array('numeric'))
			->add_rule('max_length', '19');
		$validate->add('page', 'ページ')
			->add_rule('valid_string', array('numeric'))
			->add_rule('numeric_min', '1')
			->add_rule('numeric_max', '100000000');
		$validate->add('limit', '１ページあたりのアルバム表示数')
			->add_rule('valid_string', array('numeric'))
			->add_rule('numeric_min', '1')
			->add_rule('numeric_max', '200');

		# バリデート実行
		static::_validate_run($validate);

		return true;
	}


	public static function validation_for_search()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($validate);

		# 個別バリデート設定
		$validate->add('artist_id', 'アーティストID')
			->add_rule('required')
			->add_rule('valid_string', array('numeric'))
			->add_rule('max_length', '19');
		$validate->add('album_name', 'アルバム検索名')
			->add_rule('required')
			->add_rule('max_length', '100');

		# バリデート実行
		static::_validate_run($validate);

		return true;
	}


	public static function validation_for_info()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($validate);

		# 個別バリデート設定
		$validate->add('album_id', 'アルバムID')
			->add_rule('required')
			->add_rule('valid_string', array('numeric'))
			->add_rule('max_length', '19');

		# バリデート実行
		static::_validate_run($validate);

		return true;
	}


	public static function set_dto_for_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$album_dto  = AlbumDto::get_instance();

		$artist_dto->set_artist_id(trim(static::$_obj_request->artist_id));
		$album_dto->set_page(trim(static::$_obj_request->page));
		$album_dto->set_limit(trim(static::$_obj_request->limit));

		return true;
	}


	public static function set_dto_for_search()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$album_dto  = AlbumDto::get_instance();

		$artist_dto->set_artist_id(trim(static::$_obj_request->artist_id));
		$album_dto->set_album_name(trim(static::$_obj_request->album_name));

		return true;
	}


	public static function set_dto_for_info()
	{
		\Log::debug('[start]'. __METHOD__);

		$album_dto  = AlbumDto::get_instance();

		$album_dto->set_album_id(trim(static::$_obj_request->album_id));

		return true;
	}

	public static function set_dto_for_review_write()
	{
		\Log::debug('[start]'. __METHOD__);

		$album_dto = AlbumDto::get_instance();

		$album_dto->set_album_id(trim(static::$_obj_request->album_id));
		$album_dto->set_album_name(trim(static::$_obj_request->album_name));

		return true;
	}


	/**
	 * 外部apiから取得したアーティスト情報を登録（ignore）
	 */
	private static function _set_api_album_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$album_dao = new AlbumDao();
		$result = $album_dao->set_insert_update_values(static::$arr_album_list_mixed_api, true);

		return $result;
	}


	/**
	 * 検索ワードテーブルへのアクセス
	 * @return boolean
	 */
	public static function search_words()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();

		static::$search_only = false;
		static::$arr_search['artist_id']   = $artist_dto->get_artist_id();
		static::$arr_search['artist_name'] = preg_replace('/[&=]/', '', trim($artist_dto->get_artist_name()));

		$search_album_dao = new SearchAlbumDao();

		# トランザクション開始
		$search_album_dao->start_transaction();

		# 検索アーティスト情報を取得
		$arr_where = array(
			'word' => static::$arr_search['artist_id'],
		);

		# 行ロック
		$arr_result = $search_album_dao->set_lock($arr_where);

		# 検索結果が0なら後ほどインサート
		if (empty($arr_result))
		{
			\Log::info("未登録の検索アーティストIDです(". static::$arr_search['artist_name']. ":". static::$arr_search['artist_id']. ")");

			# コミット
			$search_album_dao->commit_transaction();

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
			static::$arr_search['artist_id'] = preg_replace('/[&=]/', '', trim($arr_result['exchange_word']));
		}

		$search_album_dao->update_values($arr_values, $arr_result['id']);

		# コミット
		$search_album_dao->commit_transaction();

		return true;
	}


	/**
	 * 検索ワードテーブルへのアクセス
	 * @return boolean
	 */
	public static function regist_words()
	{
		\Log::debug('[start]'. __METHOD__);

		$search_album_dao = new SearchAlbumDao();
		$artist_dto = ArtistDto::get_instance();

		# ワード検索結果が0ならインサート
		if (static::$search_only === false)
		{
			$arr_values = array(
					'word'          => $artist_dto->get_artist_id(),
					'count'         => 1,
					'took_at'       => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
					'exchange_word' => static::$arr_search['artist_id'],
			);
			$search_album_dao->set_values($arr_values);
		}

		return true;
	}


	/**
	 * アルバム情報を検索しつつインサートも行う
	 * @param string $is_only_grooveonline
	 * @throws \Exception
	 * @return boolean
	 */
	public static function get_album_list()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$artist_dto = ArtistDto::get_instance();

			static::$arr_search['artist_id']   = $artist_dto->get_artist_id();
			static::$arr_search['artist_name'] = $artist_dto->get_artist_name();

			/* 検索ワード期限を確認＆登録 */
			static::search_words();

			if (static::$search_only === false)
			{
				/* itunesからアルバムリストを取得*/
				static::_get_album_list_from_itunes();

				/* lastfmからアルバムリストを取得 */
				static::_get_album_list_from_lastfm();

				/* apiサービスから取得したデータをまとめる*/
				static::_merge_album_list_from_api_service();

				/* トランザクション開始 */
				static::start_transaction();

				/* DBにアルバム登録する */
				static::_set_api_album_list();

				/* DBに検索アーティストIDを登録する */
				static::regist_words();

				/* コミット */
				static::commit_transaction();
			}

			// グルーヴオンラインテーブルから取得
			static::_get_album_list_from_grooveonline_at_last();

			return true;

		}
		catch (\Exception $e)
		{
			static::rollback_transaction();
			throw new \Exception($e);
		}
	}


	/**
	 * アルバムIDからアルバム情報を取得しメンバ変数にセット
	 * @return boolean
	 */
	public static function get_album_info_by_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$album_dto = AlbumDto::get_instance();

		$album_dao = new AlbumDao();
		$arr_where = array('id' => $album_dto->get_album_id());
		static::$arr_album_detail = current($album_dao->get_album($arr_where));

		$artist_dto->set_artist_id(static::$arr_album_detail->artist_id);
		$album_dto->set_artist_id(static::$arr_album_detail->artist_id);
		$album_dto->set_mbid_itunes(static::$arr_album_detail->mbid_itunes);
		$album_dto->set_mbid_lastfm(static::$arr_album_detail->mbid_lastfm);
		$album_dto->set_release_itunes(static::$arr_album_detail->release_itunes);
		$album_dto->set_copyright_itunes(static::$arr_album_detail->copyright_itunes);
		$album_dto->set_genre_itunes(static::$arr_album_detail->genre_itunes);

		return true;
	}


	public static function get_album_by_id_to_dto($album_id)
	{
		\Log::debug('[start]'. __METHOD__);

		$album_dto = AlbumDto::get_instance();
		$album_dao = new AlbumDao();
		$arr_where = array('id' => $album_id);
		$arr_result = $album_dao->get_album($arr_where);

		foreach ($arr_result as $i => $arr_columns)
		{
			foreach ($arr_columns as $key => $val)
			{
				$method = 'set_'. $key;
				if (method_exists($album_dto, $method))
				{
					$album_dto->$method($val);
				}
			}
		}

		return true;
	}


	public static function search_album_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$album_dao = new AlbumDao();
		$arr_result = $album_dao->search_album_name();

		$album_dto = AlbumDto::get_instance();
		$album_dto->set_arr_list($arr_result);

		return true;
	}


	public static function format_for_info()
	{
		\Log::debug('[start]'. __METHOD__);

		$album_dto = AlbumDto::get_instance();
		$track_dto = TrackDto::get_instance();

		$arr_album_track_list = $track_dto->get_arr_list();
		static::$arr_album_detail->album_id    = static::$arr_album_detail->id;
		static::$arr_album_detail->album_name  = static::$arr_album_detail->name;
		static::$arr_album_detail->arr_list    = $arr_album_track_list;

		$album_dto->set_arr_list(static::$arr_album_detail);

		return true;
	}


	private static function _get_album_list_from_itunes()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$artist_dto = ArtistDto::get_instance();
			$album_dto  = AlbumDto::get_instance();

			$arr_params = array(
				'country' => 'JP',
				'media'   => 'music',
				'entity'  => 'album',
				'term'    => preg_replace('/[&=\?]/', '', static::$arr_search['artist_name']),
				'attribute' => 'artistTerm',
				'limit'   => 200,
			);

			$url = \Config::get('itunes.url_rest'). http_build_query($arr_params);
			\Log::info($url);
			$ch  = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, \Config::get('itunes.api_exec_timeout'));
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, \Config::get('itunes.connect_api_timeout'));
			$json_response = curl_exec($ch);
			curl_close($ch);

			$obj_response = json_decode($json_response);

			if (empty($obj_response))
			{
				throw new \Exception('itunes response failed');
			}

			if (property_exists($obj_response, 'results'))
			{
				$album_response = $obj_response->results;
				$cnt = count($album_response);
				foreach($album_response as $i => $item)
				{
					$mbid = $item->collectionId;
					$name = $item->collectionName;
					$english = '';
					if (Util::is_english($name))
					{
						$english = $name;
					}
					$same_names       = ' '. Util::same_name_replace($name);
					$image            = $item->artworkUrl100;
					$image_small      = $item->artworkUrl60;
					$image_medium     = $item->artworkUrl100;
					$image_large      = preg_replace('/100x100/', '150x150', $image);
					$image_extralarge = preg_replace('/100x100/', '200x200', $image);
					$copyright_itune  = property_exists($item, 'copyright')? $item->copyright: '';
					$release_itunes   = property_exists($item, 'releaseDate')? $item->releaseDate: '';
					$genre_itunes     = property_exists($item, 'primaryGenreName')? $item->primaryGenreName: '';

					static::$arr_album_list_from_itunes[mb_strtolower($name)] = array(
							'id'               => '',
							'artist_id'        => $artist_dto->get_artist_id(),
							'name'             => $name,
							'kana'             => '',
							'english'          => $english,
							'same_names'       => $same_names,
							'mbid_itunes'      => $mbid,
							'mbid_lastfm'      => '',
							'url_itunes'       => $item->collectionViewUrl,
							'url_lastfm'       => '',
							'image_url'    => $image,
							'image_small'      => $image_small,
							'image_medium'     => $image_medium,
							'image_large'      => $image_large,
							'image_extralarge' => $image_extralarge,
							'copyright_itunes' => $copyright_itune,
							'release_itunes'   => $release_itunes,
							'genre_itunes'     => $genre_itunes,
							'sort'             => $cnt,
							'api_type'         => \Config::get('itunes.api_type'),
					);
					$cnt--;
				} //foreach
			}

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


	private static function _get_album_list_from_lastfm()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$artist_dto = ArtistDto::get_instance();
			$album_dto  = AlbumDto::get_instance();

			$arr_params = array(
				'method'  => 'artist.getTopAlbums',
				'api_key' => \Config::get('lastfm.api_key'),
				'artist'  => static::$arr_search['artist_name'],
				'mbid'    => $artist_dto->get_mbid_lastfm(),
				'format'  => 'json',
				'limit'  => 200
			);
			$url = \Config::get('lastfm.url_rest'). http_build_query($arr_params);
			\Log::info($url);
			$ch  = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, \Config::get('lastfm.api_exec_timeout'));
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, \Config::get('lastfm.connect_api_timeout'));
			$json_response = curl_exec($ch);
			curl_close($ch);
			$obj_response = json_decode($json_response);
			if (empty($obj_response))
			{
				\Log::info('Lastfmからのレスポンスが取得できません');
				return true;
			}

			if ( ! property_exists($obj_response, 'topalbums'))
			{
				\Log::info('Lastfmからのレスポンスtopalubmsが取得できません');
				return true;
			}

			if ( ! property_exists($obj_response->topalbums, 'album'))
			{
				\Log::info('Lastfmからのレスポンスtopalubms->albumが取得できません');
				return true;
			}

			if (property_exists($obj_response, 'error'))
			{
				\Log::info('error: '. $obj_response->message. '['. $obj_response->error. ']');
				return true;
			}

			$arr_response_album = array();
			if (count($obj_response->topalbums->album) === 1)
			{
				$arr_response_album[] = $obj_response->topalbums->album;
			}
			else
			{
				$arr_response_album = $obj_response->topalbums->album;
			}

			$cnt = count($arr_response_album);
			foreach($arr_response_album as $i => $item)
			{
				if (is_array($item))
				{
					$item = current($item);
				}

				if ( ! property_exists($item, 'name'))
				{
					continue;
				}
				if ( ! property_exists($item, 'mbid'))
				{
					continue;
				}

				$name = $item->name;
				$mbid = $item->mbid;
				$english = '';
				if (Util::is_english($name))
				{
					$english = $name;
				}
				$same_names = ' '. Util::same_name_replace($name);
				$image = current($item->image[1]);
				$image_small      = preg_replace('/[0-9]+(s*\/.+\.)(png|jpg)$/i', '34$1$2', $image);
				$image_medium     = preg_replace('/[0-9]+(s*\/.+\.)(png|jpg)$/i', '64$1$2', $image);
				$image_large      = preg_replace('/[0-9]+(s*\/.+\.)(png|jpg)$/i', '126$1$2', $image);
				$image_extralarge = preg_replace('/[0-9]+(s*\/.+\.)(png|jpg)$/i', '252$1$2', $image);
				static::$arr_album_list_from_lastfm[mb_strtolower($name)] = array(
					'id'               => '',
					'artist_id'        => $artist_dto->get_artist_id(),
					'name'             => $name,
					'kana'             => '',
					'english'          => $english,
					'same_names'       => $same_names,
					'mbid_itunes'      => '',
					'mbid_lastfm'      => $mbid,
					'url_itunes'       => '',
					'url_lastfm'       => $item->url,
					'image_url'        => $image,
					'image_small'      => $image_small,
					'image_medium'     => $image_medium,
					'image_large'      => $image_large,
					'image_extralarge' => $image_extralarge,
					'sort'             => $cnt,
					'api_type'         => \Config::get('lastfm.api_type'),
				);
				$cnt--;
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


	/**
	 * グルーヴオンラインテーブルから取得
	 * @return boolean
	 */
	private static function _get_album_list_from_grooveonline_at_last()
	{
		\Log::debug('[start]'. __METHOD__);

		$album_dto = AlbumDto::get_instance();
		$album_dto->set_sort(array(
				'api_type' => 'ASC',
				'sort' => 'DESC',
		));

		$album_dao = new AlbumDao();
		$arr_result = $album_dao->get_album_list_by_artist_id();

		/* dtoにセット */
		$album_dto->set_arr_list($arr_result);

		return true;
	}


	/**
	 * lastfm apiとitunes apiから取得したデータをまとめる
	 *
	 * @return boolean
	 */
	private static function _merge_album_list_from_api_service()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();

		foreach (static::$arr_album_list_from_itunes as $key => $val)
		{
			static::$arr_album_list_mixed_api[$key] = $val;
			if (isset(static::$arr_album_list_from_lastfm[$key]))
			{
				static::$arr_album_list_mixed_api[$key]['mbid_lastfm'] = static::$arr_album_list_from_lastfm[$key]['mbid_lastfm'];
				static::$arr_album_list_mixed_api[$key]['url_lastfm']  = static::$arr_album_list_from_lastfm[$key]['url_lastfm'];

				$itunes_image_url = $val['image_url'];
				if (empty($itunes_image_url))
				{
					static::$arr_album_list_mixed_api[$key]['image_url']    = static::$arr_album_list_from_lastfm['image_url'];
					static::$arr_album_list_mixed_api[$key]['image_small']      = static::$arr_album_list_from_lastfm['image_small'];
					static::$arr_album_list_mixed_api[$key]['image_medium']     = static::$arr_album_list_from_lastfm['image_medium'];
					static::$arr_album_list_mixed_api[$key]['image_large']      = static::$arr_album_list_from_lastfm['image_large'];
					static::$arr_album_list_mixed_api[$key]['image_extralarge'] = static::$arr_album_list_from_lastfm['image_extralarge'];
				}
				unset(static::$arr_album_list_from_lastfm[$key]);
			}
		}
		unset($key, $val);
		$cnt = count(static::$arr_album_list_mixed_api);
		if (empty($cnt))
		{
			$cnt = count(static::$arr_album_list_from_lastfm) + 1;
		}

		foreach (static::$arr_album_list_from_lastfm as $key => $val)
		{
			$cnt = $cnt - 1;
			if ($cnt <= 1)
			{
				$cnt = 1;
			}
			static::$arr_album_list_mixed_api[$key] = $val;
			static::$arr_album_list_mixed_api[$key]['sort'] = $cnt;
			static::$arr_album_list_mixed_api[$key]['release_itunes']   = '';
			static::$arr_album_list_mixed_api[$key]['copyright_itunes'] = '';
			static::$arr_album_list_mixed_api[$key]['genre_itunes']     = '';
		}

		return true;
	}
}