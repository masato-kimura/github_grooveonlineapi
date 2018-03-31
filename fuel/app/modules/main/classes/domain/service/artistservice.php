<?php
namespace main\domain\service;

use main\model\dto\ArtistDto;
use main\model\dao\ArtistDao;
use main\model\dao\SearchArtistDao;
use Fuel\Core\Validation;
use main\model\dao\FavoriteArtistDao;
use main\model\dto\UserDto;
use main\model\dto\TracklistDto;

class ArtistService extends Service
{
	private static $arr_artist_list_by_gol = array();
	private static $arr_artist_list_by_lastfm = array();
	private static $arr_artist_list_by_itunes = array();
	private static $arr_artist_list_by_api_services = array();
	private static $search_word = '';
	private static $search_only = false;
	private static $api_expired_days = 3; // days apiにアクセスしない期間


	public static function validation_for_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('artist_id', 'アーティストID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# login_hash
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash', static::$_obj_request->user_id);

		# バリデート実行
		$arr_value_params = array(
			'api_key'    => static::$_obj_request->api_key,
			'artist_id'  => static::$_obj_request->artist_id,
			'user_id'    => static::$_obj_request->user_id,
			'login_hash' => static::$_obj_request->login_hash,
		);
		static::_validate_run($obj_validate, $arr_value_params);

		return true;
	}


	public static function validation_for_search()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('artist_name', 'アーティスト検索名');
		$v->add_rule('required');
		$v->add_rule('max_length', '100');

		$v = $obj_validate->add('page', 'ページ');
		$v->add_rule('numeric_min', '1');
		$v->add_rule('numeric_max', '100000000');

		$v = $obj_validate->add('limit', 'limit');
		$v->add_rule('numeric_min', '1');
		$v->add_rule('numeric_max', '200');

		$v = $obj_validate->add('available_play', '視聴可能フラグ');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('numeric_min', 0);
		$v->add_rule('numeric_max', 1);

		# バリデート実行
		$arr_value_params = array(
			'api_key'     => static::$_obj_request->api_key,
			'artist_name' => static::$_obj_request->artist_name,
			'page'        => static::$_obj_request->page,
			'limit'       => static::$_obj_request->limit,
			'available_play' => static::$_obj_request->available_play,
		);
		static::_validate_run($obj_validate, $arr_value_params);

		return true;
	}


	public static function validation_for_getsearch()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		$v = $obj_validate->add('offset', 'offset');
		$v->add_rule('required');
		$v->add_rule('numeric_min', '0');
		$v->add_rule('numeric_max', '100000000');

		$v = $obj_validate->add('limit', 'limit');
		$v->add_rule('required');
		$v->add_rule('numeric_min', '1');
		$v->add_rule('numeric_max', '100');

		$v = $obj_validate->add('type', 'type');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(new)|(top)/i');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_regist()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('artist_name', 'アーティスト検索名');
		$v->add_rule('required');
		$v->add_rule('max_length', '100');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_updatebylastfm()
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

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_setfavorite()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);
		$_POST['user_id'] = \Input::param('client_user_id');

		$obj_validate = Validation::forge();

		/* API共通バリデート設定 */
		static::_validate_base($obj_validate);

		/* 個別バリデート設定 */
		# client_user_id
		$v = $obj_validate->add('client_user_id', 'クライアントユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# client_artist_id
		$v = $obj_validate->add('favorite_artist_id', 'お気に入りアーティストID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# status
		$v = $obj_validate->add('status', 'お気に入り登録ステータス');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('nmumeric'));
		$v->add_rule('numeric_min', 0);
		$v->add_rule('numeric_max', 1);

		# login_hash
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('required');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}



	public static function set_dto_for_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$user_dto   = UserDto::get_instance();
		$tracklist_dto = TracklistDto::get_instance();

		foreach (static::$_obj_request as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}
			if ($key === 'artist_id')
			{
				$artist_dto->set_artist_id(trim($val));
			}
			if ($key === 'user_id')
			{
				$user_dto->set_user_id(trim($val));
			}
			if ($key === 'tracklist_offset')
			{
				$tracklist_dto->set_offset(trim($val));
			}
			if ($key === 'tracklist_limit')
			{
				$tracklist_dto->set_limit(trim($val));
			}
		}

		return true;
	}


	public static function set_dto_for_search()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();

		foreach (static::$_obj_request as $key => $val)
		{
			if ( ! isset($val))
			{
				continue;
			}
			if ($key === 'artist_name')
			{
				$artist_dto->set_artist_name(trim($val));
			}
			if ($key === 'page')
			{
				$artist_dto->set_page(trim($val));
			}
			if ($key === 'limit')
			{
				$artist_dto->set_limit(trim($val));
			}
			if ($key === 'available_play')
			{
				$artist_dto->set_available_play($val);
			}
		}

		return true;
	}



	public static function set_dto_for_getsearch()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();

		foreach (static::$_obj_request as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}
			if ($key === 'type')
			{
				$artist_dto->set_type(trim($val));
			}
			if ($key === 'offset')
			{
				$artist_dto->set_offset(trim($val));
			}
			if ($key === 'limit')
			{
				$artist_dto->set_limit(trim($val));
			}
		}

		return true;
	}



	public static function set_dto_for_setfavorite()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto   = UserDto::get_instance();
		$artist_dto = ArtistDto::get_instance();
		$user_dto->set_user_id(trim(static::$_obj_request->client_user_id));
		$artist_dto->set_artist_id(trim(static::$_obj_request->favorite_artist_id));
		$artist_dto->set_favorite_status(static::$_obj_request->status);

		return true;
	}



	public static function insert_artist_info()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$artist_dao = new ArtistDao();

		$artist_id = $artist_dto->get_artist_id();

		# 更新
		if ( ! empty($artist_id))
		{
			return true;
		}

		# 登録
		$english = $artist_dto->get_english();
		if (empty($english))
		{
			if (Util::is_english($artist_dto->get_artist_name()))
			{
				$english = $artist_dto->get_artist_name();
			}
		}

		$arr_set_values = array(
			'name'             => $artist_dto->get_artist_name(),
			'kana'             => $artist_dto->get_kana(),
			'english'          => $english,
			'same_names'       => $artist_dto->get_same_names(),
			'mbid_itunes'      => $artist_dto->get_mbid_itunes(),
			'mbid_lastfm'      => $artist_dto->get_mbid_lastfm(),
			'url_itunes'       => $artist_dto->get_url_itunes(),
			'url_lastfm'       => $artist_dto->get_url_lastfm(),
			'image_url'        => $artist_dto->get_image_url(),
			'image_small'      => $artist_dto->get_image_small(),
			'image_medium'     => $artist_dto->get_image_medium(),
			'image_large'      => $artist_dto->get_image_large(),
			'image_extralarge' => $artist_dto->get_image_extralarge(),
		);

		list($id, $count) = $artist_dao->set_values($arr_set_values);
		$artist_dto->set_artist_id($id);

		return true;
	}


	/**
	 * アーティスト情報を一件検索しdtoにセット
	 *
	 * パラメータのアーティスト名がテーブルに存在する場合はエラーとする
	 *
	 * @return boolean
	 */
	public static function is_exist_artist()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto  = ArtistDto::get_instance();
		$artist_id   = $artist_dto->get_artist_id();
		$artist_name = $artist_dto->get_artist_name();

		# DBから既存アーティスト情報を取得(name, lastfm_url)
		$artist_dao = new ArtistDao();
		$arr_artist_info = $artist_dao->get_just_artist_list($artist_name);

		if (empty($artist_id) and (count($arr_artist_info) > 0))
		{
			throw new \Exception('アーティスト名が存在します', 8901);
		}
		return true;
	}


	public static function get_artist_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$artist_dao = new ArtistDao();

		$artist_id   = $artist_dto->get_artist_id();

		# artist_idが存在し検索
		if (empty($artist_id))
		{
			return true;
		}

		$arr_result = $artist_dao->get_artist_by_id($artist_id);
		if (empty($arr_result))
		{
			$arr_response = array();
		}
		else
		{
			$obj_current_result = current($arr_result);
			$arr_response = array();
			$arr_response['id']                = $obj_current_result->id;
			$arr_response['name']              = $obj_current_result->name;
			$arr_response['kana']              = $obj_current_result->kana;
			$arr_response['english']           = $obj_current_result->english;
			$arr_response['mbid_itunes']       = $obj_current_result->mbid_itunes;
			$arr_response['mbid_lastfm']       = $obj_current_result->mbid_lastfm;
			$arr_response['url_itunes']        = $obj_current_result->url_itunes;
			$arr_response['url_lastfm']        = $obj_current_result->url_lastfm;
			$arr_response['image_url']         = $obj_current_result->image_url;
			$arr_response['image_small']       = $obj_current_result->image_small;
			$arr_response['image_medium']      = $obj_current_result->image_medium;
			$arr_response['image_large']       = $obj_current_result->image_large;
			$arr_response['image_extralarge']  = $obj_current_result->image_extralarge;
			$arr_response['sort']              = $obj_current_result->sort;
		}

		$artist_dto->set_arr_list($arr_response);

		return true;
	}


	public static function get_artist_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();

		$artist_id   = $artist_dto->get_artist_id();
		$artist_name = $artist_dto->get_artist_name();
		$page        = $artist_dto->get_page();
		$page        = empty($page) ? 0 : $page;
		$limit       = $artist_dto->get_limit();
		$limit       = empty($artist_dto) ? 1 : $page;
		$sort        = $artist_dto->get_sort();

		if (empty($sort))
		{
			$sort = 'name';
		}

		$artist_dao = new ArtistDao();

		# artist_idが存在し検索
		if (isset($artist_id))
		{
			$arr_result = $artist_dao->get_artist_by_id($artist_id);
			if ( ! empty($arr_result))
			{
				$obj_current_result = current($arr_result);
				$artist_dto->set_artist_name($obj_current_result->name);
				$artist_dto->set_kana($obj_current_result->kana);
				$artist_dto->set_english($obj_current_result->english);
				$artist_dto->set_same_names($obj_current_result->same_names);
				$artist_dto->set_mbid_itunes($obj_current_result->mbid_itunes);
				$artist_dto->set_mbid_lastfm($obj_current_result->mbid_lastfm);
				$artist_dto->set_image_url($obj_current_result->image_url);
				$artist_dto->set_image_small($obj_current_result->image_small);
				$artist_dto->set_image_medium($obj_current_result->image_medium);
				$artist_dto->set_image_large($obj_current_result->image_large);
				$artist_dto->set_image_extralarge($obj_current_result->image_extralarge);
				$artist_dto->set_sort($obj_current_result->sort);
				$artist_dto->set_arr_list($arr_result);

				return true;
			}
		}

		# artist_nameが存在し検索
		if (isset($artist_name))
		{
			$arr_where = array(
				'name' => $artist_name,
			);

			$arr_result = $artist_dao->get_same_artist_list($artist_name);
			if ( ! empty($arr_result))
			{
				$artist_dto->set_arr_list($arr_result);
				return true;
			}
		}

		# 単純検索
		if ( ! isset($artist_id) and !isset($artist_name))
		{
			$arr_result = $artist_dao->get_artist_list(array(), array(), array($sort => 'ASC'), 0, 100);
			$artist_dto->set_arr_list($arr_result);
		}

		return true;
	}

	/**
	 * アーティスト情報を検索しつつインサートも行う
	 * @return boolean
	 */
	public static function search_and_regist()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$artist_dto = ArtistDto::get_instance();
			static::$search_word = $artist_dto->get_artist_name();

			//* 検索ワード期限を確認＆登録 */
			static::search_words();

			\Log::info('search_only::'. static::$search_only);

			if (static::$search_only === false)
			{

				//* itunesから検索 */
				static::search_by_itunes();

				//* itunesからの検索結果が1件のみで検索ワードと結果が変わった場合 */
				static::_exchange_search_word_by_itunes();

				//* lastfmから検索 */
				static::search_by_lastfm();

				//* apiサービスをまとめる *
				static::_merge_from_api_service();

				// トランザクション開始
				static::start_transaction();

				// DBにアーティスト登録する
				static::_insert_services_result();

				// DBに検索アーティスト名を登録する
				static::regist_words();

				// コミット
				static::commit_transaction();
			}

			// グルーヴオンラインテーブルから取得
			static::search_by_gol_use_index();

			return true;
		}
		catch (\Exception $e)
		{
			static::rollback_transaction();
			throw new \Exception($e);
		}
	}


	public static function getsearch()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$artist_dto = ArtistDto::get_instance();

			$search_artist_dao = new SearchArtistDao();
			$arr_result = $search_artist_dao->get_history_new($artist_dto->get_offset(), $artist_dto->get_limit());
			$arr_index = array();
			foreach ($arr_result as $i => $val)
			{
				if (preg_match('//i', $val['artist_name'])) continue;
				if (empty($val['artist_image'])) continue;
				$index = trim($val['artist_name']);
				$arr_index[$index] = $val;
				if (count($arr_index) > $artist_dto->get_limit())
				{
					break;
				}
			}

			$artist_dto->set_arr_list($arr_index);

			return true;
		}
		catch (\Exception $e)
		{
			static::rollback_transaction();
			throw new \Exception($e);
		}
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
		static::$search_word = preg_replace('/[&=]/', '', trim($artist_dto->get_artist_name()));

		$search_artist_dao = new SearchArtistDao();

		# トランザクション開始
		$search_artist_dao->start_transaction();

		# 検索アーティスト情報を取得
		$arr_where = array(
			'word' => $artist_dto->get_artist_name(),
		);

		# 行ロック
		$arr_result = $search_artist_dao->set_lock($arr_where);

		# 検索結果が0なら後ほどインサート
		if (empty($arr_result))
		{
			\Log::info("未登録の検索アーティストです({$artist_dto->get_artist_name()})");

			# コミット
			$search_artist_dao->commit_transaction();

			return true;
		}

		# 存在し期間外ならば更新日、カウントをアップデート
		$expired_plus = 60 * 60 * 24 * static::$api_expired_days;
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
			static::$search_word = preg_replace('/[&=]/', '', trim($arr_result['exchange_word']));
		}

		$search_artist_dao->update_values($arr_values, $arr_result['id']);

		# コミット
		$search_artist_dao->commit_transaction();

		return true;
	}


	/**
	 * 検索ワードテーブルへのアクセス
	 * @return boolean
	 */
	public static function regist_words()
	{
		\Log::debug('[start]'. __METHOD__);

		$search_artist_dao = new SearchArtistDao();
		$artist_dto = ArtistDto::get_instance();

		# ワード検索結果が0ならインサート
		if (static::$search_only === false)
		{
			$arr_values = array(
					'word'          => $artist_dto->get_artist_name(),
					'count'         => 1,
					'took_at'       => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
					'exchange_word' => static::$search_word,
			);
			$search_artist_dao->set_values($arr_values);
		}

		return true;
	}


	public static function search_by_gol_use_index()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto = ArtistDto::get_instance();
		$artist_id  = $artist_dto->get_artist_id();
		$page       = $artist_dto->get_page();
		$page       = empty($page) ? 1 : $page;
		$limit      = $artist_dto->get_limit();
		$limit      = empty($limit) ? 20 : $page;
		$artist_dao = new ArtistDao();

		# artist_nameが存在し検索
		$artist_name = static::$search_word;
		if (empty($artist_name))
		{
			return false;
		}

		// 初期化
		static::$arr_artist_list_by_gol = array();
		$arr_order = array(
				'sort' => 'DESC',
		);

		\Log::info(static::$search_word. '::'. $artist_dto->get_artist_name() );
		if (static::$search_word != $artist_dto->get_artist_name())
		{
			$arr_artist_name = array();
			$arr_search_name = array();
			$arr_artist_name[$artist_dto->get_artist_name()] = $artist_dto->get_artist_name();
			$arr_obj_artist_result = $artist_dao->get_arr_same_artist_list($arr_artist_name, $arr_order, false);

			$arr_search_name[static::$search_word] = static::$search_word;
			$arr_obj_search_result = $artist_dao->get_arr_same_artist_list($arr_search_name, $arr_order, false);
			foreach ($arr_obj_artist_result as $i => $item)
			{
				static::$arr_artist_list_by_gol[$item->name] = array(
						'id'               => $item->id,
						'name'             => $item->name,
						'kana'             => $item->kana,
						'english'          => $item->english,
						'mbid_itunes'      => $item->mbid_itunes,
						'mbid_lastfm'      => $item->mbid_lastfm,
						'url_itunes'       => $item->url_itunes,
						'url_lastfm'       => $item->url_lastfm,
						'image_url'        => $item->image_url,
						'image_small'      => $item->image_small,
						'image_medium'     => $item->image_medium,
						'image_large'      => $item->image_large,
						'image_extralarge' => $item->image_extralarge,
						'sort'             => $item->sort,
				);
			} // endforeach

			foreach ($arr_obj_search_result as $i => $item)
			{
				static::$arr_artist_list_by_gol[$item->name] = array(
						'id'               => $item->id,
						'name'             => $item->name,
						'kana'             => $item->kana,
						'english'          => $item->english,
						'mbid_itunes'      => $item->mbid_itunes,
						'mbid_lastfm'      => $item->mbid_lastfm,
						'url_itunes'       => $item->url_itunes,
						'url_lastfm'       => $item->url_lastfm,
						'image_url'        => $item->image_url,
						'image_small'      => $item->image_small,
						'image_medium'     => $item->image_medium,
						'image_large'      => $item->image_large,
						'image_extralarge' => $item->image_extralarge,
						'sort'             => $item->sort,
				);
			} // endforeach
		}
		else
		{
			$arr_name = array();
			$arr_name[static::$search_word] = static::$search_word;
			$arr_obj_result = $artist_dao->get_arr_same_artist_list($arr_name, $arr_order, false);

			if (empty($arr_obj_result))
			{
				return true;
			}

			foreach ($arr_obj_result as $i => $item)
			{
				static::$arr_artist_list_by_gol[$item->name] = array(
						'id'               => $item->id,
						'name'             => $item->name,
						'kana'             => $item->kana,
						'english'          => $item->english,
						'mbid_itunes'      => $item->mbid_itunes,
						'mbid_lastfm'      => $item->mbid_lastfm,
						'url_itunes'       => $item->url_itunes,
						'url_lastfm'       => $item->url_lastfm,
						'image_url'        => $item->image_url,
						'image_small'      => $item->image_small,
						'image_medium'     => $item->image_medium,
						'image_large'      => $item->image_large,
						'image_extralarge' => $item->image_extralarge,
						'sort'             => $item->sort,
				);
			}
		}

		$artist_dto->set_arr_list(static::$arr_artist_list_by_gol);

		return true;
	}


	public static function search_by_gol($is_full_search=false)
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dto  = ArtistDto::get_instance();

		$artist_id   = $artist_dto->get_artist_id();
		$artist_name = static::$search_word;
		$page        = $artist_dto->get_page();
		$page        = empty($page) ? 1 : $page;
		$limit       = $artist_dto->get_limit();
		$limit       = empty($limit) ? 20 : $page;

		$artist_dao = new ArtistDao();

		# artist_nameが存在し検索
		if (empty($artist_name))
		{
			return false;
		}

		$arr_obj_result = $artist_dao->get_same_artist_list($artist_name);

		if ( ! empty($arr_obj_result))
		{
			foreach ($arr_obj_result as $i => $item)
			{
				$key = mb_strtoupper($item->name). $item->mbid_api;
				static::$arr_artist_list_by_gol[$key] = array(
						'id'               => $item->id,
						'name'             => static::$search_word,
						'kana'             => static::$search_word,
						'english'          => static::$search_word,
						'search'           => static::$search_word,
						'same_names'       => static::$search_word,
						'mbid_itunes'      => $item->mbid_itunes,
						'mbid_lastfm'      => $item->mbid_lastfm,
						'url_itunes'       => $item->url_api,
						'url_lastfm'       => $item->url_itunes,
						'image_url'    => $item->image_url,
						'image_small'      => $item->image_small,
						'image_medium'     => $item->image_medium,
						'image_large'      => $item->image_large,
						'image_extralarge' => $item->image_extralarge,
				);
			}

			if (count(static::$arr_artist_list_by_gol) >= $artist_dto->get_limit())
			{
				$artist_dto->set_arr_list(static::$arr_artist_list_by_gol);
				return true;
			}
		}

		$artist_dto->set_arr_list(static::$arr_artist_list_by_gol);

		return true;
	}


	public static function search_by_itunes()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$artist_dto  = ArtistDto::get_instance();

			$arr_params = array(
				'country'  => 'JP',
				'entity'   => 'musicArtist',
				'media'    => 'music',
				'term'     => trim(static::$search_word),
				'attribute' => 'artistTerm',
				'limit'   => 20,
			);
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
			if (empty($obj_response))
			{
				throw new \Exception('itunes response failed');
			}

			if (property_exists($obj_response, 'results'))
			{
				$artist_response = $obj_response->results;

				foreach($artist_response as $i => $item)
				{
					$mbid = $item->artistId;

					// 不確定なデータを排除
					if (empty($mbid))
					{
						continue;
					}

					// 改行コードとタブを覗く制御文字が含まれないか
					if ( ! preg_match('/\A[\r\n\t[:^cntrl:]]*\z/u', $item->artistName) === 1)
					{
						\Log::error('制御文字が含まれているので排除'. $item->artistName);
						continue;
					}
					if ( preg_match('//i', $item->artistName, $match))
					{
						\Log::error('制御文字が含まれているので排除'. $item->artistName);
						continue;
					}

					// 同名のアーティストは先に取得したもの以外スルーする
					if (isset(static::$arr_artist_list_by_itunes[mb_strtolower($item->artistName)]))
					{
						\Log::info('同名のアーティストがすでに存在します');
						continue;
					}

					$english = null;
					if (Util::is_english($item->artistName))
					{
						$english = $item->artistName;
					}

					$same_names = '';
					if (static::$search_word != $item->artistName)
					{
						$same_names = static::$search_word;
					}
					$same_names .= ' '. Util::same_name_replace($item->artistName);
					$key = mb_strtolower($item->artistName);
					static::$arr_artist_list_by_itunes[$key] = array(
						'id'               => '',
						'name'             => $item->artistName,
						'kana'             => '',
						'english'          => $english,
						'search'           => static::$search_word,
						'same_names'       => $same_names,
						'mbid_itunes'      => $mbid,
						'mbid_lastfm'      => '',
						'url_itunes'       => $item->artistLinkUrl,
						'url_lastfm'       => '',
						'image_url'        => '',
						'image_small'      => '',
						'image_medium'     => '',
						'image_large'      => '',
						'image_extralarge' => '',
					);
				} //foreach

				// from itunes api (全角対応)
				foreach (static::$arr_artist_list_by_itunes as $key => $val)
				{
					// 全角は排除
					$hankaku_to_zenkaku_key = mb_convert_kana($key, "Ak"); // 半角英数字を全角、全角カナを半角カナ
					if (isset(static::$arr_artist_list_by_itunes[$hankaku_to_zenkaku_key]))
					{
						\Log::info('なんかしらんけど全角排除');
						//unset(static::$arr_artist_list_by_itunes[$hankaku_to_zenkaku_key]);
						continue;
					}
				}
			}

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine(). ']');
			\Log::error('itunesネットワークエラー');
			return false;
		}
	}


	public static function search_by_lastfm()
	{
		try
		{
			\Log::debug('[start]'. __METHOD__);

			$artist_dto  = ArtistDto::get_instance();
			$arr_search_words = array();
			$arr_search_words[static::$search_word] = static::$search_word;
			if (strcasecmp(static::$search_word, $artist_dto->get_artist_name()) != 0)
			{
				$arr_search_words[$artist_dto->get_artist_name()] = $artist_dto->get_artist_name();
			}

			$arr_artist_response = array();
			foreach ($arr_search_words as $search_word)
			{
				$arr_params = array(
						'method'  => 'artist.search',
						'api_key' => \Config::get('lastfm.api_key'),
						'artist'  => $search_word,
						'page'    => 1,
						'limit'   => 20,
						'format'  => 'json',
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
					throw new \Exception('last.fm failed! empty obj_response');
				}

				if ( ! property_exists($obj_response, 'results'))
				{
					throw new \Exception('last.fm failed! empty response results');
				}

				if (property_exists($obj_response->results->artistmatches, 'artist'))
				{
					$arr_artist_response[] = $obj_response->results->artistmatches->artist;
				}
			} // foreach

			foreach ($arr_artist_response as $artist_response)
			{
				$artist_response_new = array();
				if (count($artist_response) === 1)
				{
					$artist_response_new[] = $artist_response;
					$artist_response = $artist_response_new;
				}

				foreach($artist_response as $i => $item)
				{
					$mbid = $item->mbid;
					if (empty($mbid))
					{
						//continue; // 不確定なデータを排除
					}

					// 改行コードとタブを覗く制御文字が含まれないか
					if ( ! preg_match('/\A[\r\n\t[:^cntrl:]]*\z/u', $item->name) === 1)
					{
						\Log::error('制御文字が含まれているので排除'. $item->name);
						continue;
					}
					if ( preg_match('//i', $item->name, $match))
					{
						\Log::error('制御文字が含まれているので排除'. $item->name);
						continue;
					}

					$image = current($item->image[2]);
					$english = '';
					$same_names = '';
					if (Util::is_english($item->name))
					{
						$english = $item->name;
					}
					$same_names = '';
					if ($artist_dto->get_artist_name() != $item->name)
					{
						$same_names = $artist_dto->get_artist_name();
					}
					$same_names .= ' '. Util::same_name_replace($item->name);
					$image_small      = preg_replace('/last\.fm\/serve\/[0-9]+s*\//i', 'last.fm/serve/34/', $image);
					$image_medium     = preg_replace('/last\.fm\/serve\/[0-9]+s*\//i', 'last.fm/serve/64/', $image);
					$image_large      = preg_replace('/last\.fm\/serve\/[0-9]+s*\//i', 'last.fm/serve/126/', $image);
					$image_extralarge = preg_replace('/last\.fm\/serve\/[0-9]+s*\//i', 'last.fm/serve/252/', $image);

					$key = mb_strtolower($item->name, mb_internal_encoding());
					static::$arr_artist_list_by_lastfm[$key] = array(
							'id'               => '',
							'name'             => $item->name,
							'kana'             => '',
							'english'          => $english,
							'search'           => static::$search_word,
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
					);
				} // endforeach

				// from lastfm api (全角対応)
				foreach (static::$arr_artist_list_by_lastfm as $key => $val)
				{
					$hankaku_to_zenkaku_key = mb_convert_kana($key, "Ak"); // 半角英数字を全角、全角カナを半角カナ

					if (isset(static::$arr_artist_list_by_lastfm[$hankaku_to_zenkaku_key]))
					{
						$zen_val = static::$arr_artist_list_by_lastfm[$hankaku_to_zenkaku_key];
						// 画像がなければ消す
						if (empty($zen_val['image_url']))
						{
							unset(static::$arr_artist_list_by_lastfm[$hankaku_to_zenkaku_key]);
							continue;
						}
						// 全角に画像があり、半角側に画像がなければ
						if (empty($val['image_url']))
						{
							$name = static::$arr_artist_list_by_lastfm[$key]['name'];
							\Log::debug("全角文字を半角に変換しました". $name);
							static::$arr_artist_list_by_lastfm[$key] = static::$arr_artist_list_by_lastfm[$hankaku_to_zenkaku_key];
							static::$arr_artist_list_by_lastfm[$key]['name'] = $name;
						}
					} // if

					// スペースが２つ以上ある
					if (preg_match('/[\s]{2,}/', $key, $match))
					{
						// スペースをシングルにした状態で未存在なら有効にする
						$single_space = preg_replace('/[\s]{2,}/', ' ', $key);
						if (empty(static::$arr_artist_list_by_lastfm[$single_space]))
						{
							continue;
						}

						// 画像がなければ消す
						if (empty($val['image_url']))
						{
							\Log::debug('半角スペースが複数存在し画像がないので削除しました'. $key);
							unset(static::$arr_artist_list_by_lastfm[$key]);
							continue;
						}
					}
				} // endforeach
			}

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine(). ']');
			\Log::error('Lastfmネットワークエラー');
			return false;
		}
	}


	public static function set_favorite_artist()
	{
		\Log::debug('[start]'. __METHOD__);

		$favorite_dao = new FavoriteArtistDao();
		$artist_dto   = ArtistDto::get_instance();
		$user_dto     = UserDto::get_instance();

		$favorite_artist_id = $artist_dto->get_artist_id();
		$client_user_id = $user_dto->get_user_id();
		switch ($artist_dto->get_favorite_status())
		{
			case '0':
				$favorite_dao->unset_favorite_artist($favorite_artist_id, $client_user_id);
				break;
			case '1':
				$favorite_dao->set_favorite_artist($favorite_artist_id, $client_user_id);
				break;
		}

		return true;
	}


	public static function get_favorite_artist()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$artist_dto = ArtistDto::get_instance();
		$favorite_dao = new FavoriteArtistDao();

		$user_id   = $user_dto->get_user_id();
		$artist_id = $artist_dto->get_artist_id();

		$arr_where = array(
			'client_user_id'     => $user_id,
			'favorite_artist_id' => $artist_id,
		);

		$arr_result = $favorite_dao->get_favorite_artists($arr_where);

		if (empty($arr_result))
		{
			$artist_dto->set_favorite_status(false);
		}
		else
		{
			$artist_dto->set_favorite_status(true);
			$user_dto->set_favorite_artists($arr_result);
		}

		return true;
	}


	public static function get_favorite_artist_by_user_id()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto = UserDto::get_instance();
		$user_id   = $user_dto->get_user_id();
		$artist_dto = ArtistDto::get_instance();

		$favorite_dao = new FavoriteArtistDao();

		$arr_where = array(
				'client_user_id' => $user_id,
		);

		$offset = $artist_dto->get_offset();
		$limit  = $artist_dto->get_limit();

		$arr_result = $favorite_dao->get_favorite_artists_by_user_id($arr_where, $offset, $limit);
		$user_dto->set_favorite_artists($arr_result);

		return true;
	}






	/**
	 * lastfm apiとitunes apiから取得したデータをまとめる
	 *
	 * @return boolean
	 */
	private static function _merge_from_api_service()
	{
		\Log::debug('[start]'. __METHOD__);

		foreach (static::$arr_artist_list_by_itunes as $key => $val)
		{
			static::$arr_artist_list_by_api_services[$key] = $val;
			if (isset(static::$arr_artist_list_by_lastfm[$key]))
			{
				//static::$arr_artist_list_by_api_services[$key]['name']        = static::$arr_artist_list_by_lastfm[$key]['name'];
				static::$arr_artist_list_by_api_services[$key]['mbid_lastfm'] = static::$arr_artist_list_by_lastfm[$key]['mbid_lastfm'];
				static::$arr_artist_list_by_api_services[$key]['url_lastfm']  = static::$arr_artist_list_by_lastfm[$key]['url_lastfm'];
				$image_url = static::$arr_artist_list_by_lastfm[$key]['image_url'];
				if ( ! empty($image_url))
				{
					static::$arr_artist_list_by_api_services[$key]['image_url']    = static::$arr_artist_list_by_lastfm[$key]['image_url'];
					static::$arr_artist_list_by_api_services[$key]['image_small']      = static::$arr_artist_list_by_lastfm[$key]['image_small'];
					static::$arr_artist_list_by_api_services[$key]['image_medium']     = static::$arr_artist_list_by_lastfm[$key]['image_medium'];
					static::$arr_artist_list_by_api_services[$key]['image_large']      = static::$arr_artist_list_by_lastfm[$key]['image_large'];
					static::$arr_artist_list_by_api_services[$key]['image_extralarge'] = static::$arr_artist_list_by_lastfm[$key]['image_extralarge'];
				}
				unset(static::$arr_artist_list_by_lastfm[$key]);
			}
		}
		unset($key, $val);

		foreach (static::$arr_artist_list_by_lastfm as $key => $val)
		{
			static::$arr_artist_list_by_api_services[$key] = $val;
		}

		return true;
	}


	/**
	 * 外部apiから取得したアーティスト情報を登録（ignore）
	 */
	private static function _insert_services_result()
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dao = new ArtistDao();
		$result = $artist_dao->set_insert_update_values(static::$arr_artist_list_by_api_services, static::$search_word, true);

		return $result;
	}


	private static function _exchange_search_word_by_itunes()
	{
		\Log::debug('[start]'. __METHOD__);

		if (empty(static::$arr_artist_list_by_itunes))
		{
			return true;
		}

		$arr_list = static::$arr_artist_list_by_itunes;
		if (empty(current($arr_list)['name']))
		{
			return true;
		}

		if (current($arr_list)['name'] !== static::$search_word )
		{
			static::$search_word = current($arr_list)['name'];
		}

		return true;
	}
}