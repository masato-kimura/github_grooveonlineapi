<?php
namespace main\domain\service;

use main\model\dto\TracklistDto;
use main\model\dto\ArtistDto;
use main\model\dto\LoginDto;
use main\model\dto\UserDto;
use main\model\dao\TracklistDao;
use main\model\dao\TracklistDetailDao;

class TracklistService extends Service
{
	public static function validation_for_set()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = \Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		// title
		$v = $obj_validate->add('title', 'タイトル');
		$v->add_rule('required');
		$v->add_rule('max_length', 30);

		// user_name
		$v = $obj_validate->add('user_name', 'お名前');
		$v->add_rule('max_length', 20);

		// artist_id
		$v = $obj_validate->add('artist_id', 'アーティストID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# user_id
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# edit_mode
		$v = $obj_validate->add('edit_mode', 'エディットモード');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', 1);

		# tracklist_id
		$v = $obj_validate->add('tracklist_id', 'トラックリストID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', 19);

		# login_hash
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_delete()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = \Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		// tracklist_id
		$v = $obj_validate->add('tracklist_id', 'トラックリストID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# user_id
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

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


	public static function validation_for_getlist()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = \Validation::forge();

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


	public static function validation_for_getlist_title()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = \Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		// artist_id
		$v = $obj_validate->add('artist_id', 'アーティストID');
		$v->add_rule('max_length', 30);
		$v->add_rule('valid_string', array('numeric'));

		// user_id
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('max_length', 30);
		$v->add_rule('valid_string', array('numeric'));

		// offset
		$v = $obj_validate->add('offset', 'オフセット');
		$v->add_rule('max_length', 30);
		$v->add_rule('valid_string', array('numeric'));

		// limit
		$v = $obj_validate->add('limit', 'リミット');
		$v->add_rule('max_length', 30);
		$v->add_rule('valid_string', array('numeric'));

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_get_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = \Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		// title
		$v = $obj_validate->add('tracklist_id', 'トラックリストID');
		$v->add_rule('required');
		$v->add_rule('max_length', 30);

		// artist_id
		$v = $obj_validate->add('artist_id', 'アーティストID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', 30);

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function set_dto_for_set()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$artist_dto    = ArtistDto::get_instance();
		$user_dto      = UserDto::get_instance();
		$tracklist_dto->set_title(static::$_obj_request->title);
		$tracklist_dto->set_user_name(static::$_obj_request->user_name);
		$tracklist_dto->set_arr_list(static::$_obj_request->arr_list);
		$edit_mode = isset(static::$_obj_request->edit_mode)? static::$_obj_request->edit_mode: false;
		$tracklist_dto->set_edit_mode($edit_mode);
		$tracklist_id = isset(static::$_obj_request->tracklist_id)? static::$_obj_request->tracklist_id: null;
		$tracklist_dto->set_tracklist_id($tracklist_id);
		$user_dto->set_user_id(static::$_obj_request->user_id);
		$arr_artist = array();
		foreach (static::$_obj_request->arr_list as $i => $val)
		{
			$arr_artist[$val->artist_id] = true;
		}
		if (count($arr_artist) === 1)
		{
			$artist_dto->set_artist_id($val->artist_id);
		}

		return true;
	}


	public static function set_dto_for_delete()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$user_dto      = UserDto::get_instance();

		$tracklist_dto->set_tracklist_id(static::$_obj_request->tracklist_id);
		$user_dto->set_user_id(static::$_obj_request->user_id);

		return true;
	}


	public static function set_dto_for_getlist()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$artist_dto    = ArtistDto::get_instance();

		$tracklist_dto->set_offset(static::$_obj_request->offset);
		$tracklist_dto->set_limit(static::$_obj_request->limit);
		$tracklist_dto->set_user_id(static::$_obj_request->user_id);
		$artist_dto->set_artist_id(static::$_obj_request->artist_id);

		return true;
	}


	public static function set_dto_for_get_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$artist_dto    = ArtistDto::get_instance();
		$tracklist_dto->set_tracklist_id(static::$_obj_request->tracklist_id);
		$artist_dto->set_artist_id(static::$_obj_request->artist_id);
		return true;
	}

	public static function set_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$login_dto     = LoginDto::get_instance();
		$user_dto      = UserDto::get_instance();
		$artist_dto    = ArtistDto::get_instance();

		$tracklist_dao = new TracklistDao();
		$tracklist_dao->start_transaction();

		$edit_mode = $tracklist_dto->get_edit_mode();
		if ($edit_mode == false)
		{
			list($id, $count) = $tracklist_dao->set_list_title();
			$tracklist_dto->set_tracklist_id($id);
			$tracklist_detail_dao = new TracklistDetailDao();
			// インサート
			$tracklist_detail_dao->set_list();
		}
		else
		{
			$tracklist_detail_dao = new TracklistDetailDao();
			// 論理削除
			$tracklist_detail_dao->delete_list();
			// インサート
			$tracklist_detail_dao->set_list();
		}
		$tracklist_dao->commit_transaction();
		return true;
	}

	public static function delete_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$user_dto      = UserDto::get_instance();

		$tracklist_dao = new TracklistDao();
		$count = $tracklist_dao->delete_list_title();
		if (empty($count))
		{
			throw new \Exception('投稿を削除することができませんでした。');
		}
		$tracklist_detail_dao = new TracklistDetailDao();
		$tracklist_detail_dao->delete_list();

		return true;
	}

	public static function get_track_list_titles()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();

		$arr_list = array();
		$tracklist_dto->set_arr_list($arr_list);

		return true;
	}

	public static function get_list_count()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		if ($tracklist_dto->get_limit() == 0)
		{
			return true;
		}

		$user_dto = UserDto::get_instance();

		$tracklist_dao = new TracklistDao();
		$arr_result = $tracklist_dao->get_list_title_count();
		$tracklist_dto->set_count($arr_result->cnt);

		return true;
	}

	public static function get_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();

		if ($tracklist_dto->get_limit() == 0)
		{
			return true;
		}

		$tracklist_dao = new TracklistDao();
		$arr_result = $tracklist_dao->get_list_title();
		$arr_result_formated = array();
		$tmp_track_id = '';
		foreach ($arr_result as $i => $val)
		{
			if ($tmp_track_id != $val->id)
			{
				$tmp_track_id = $val->id;
				$arr_tracks = array();
				$std = new \stdClass();
				$std->track_name = $val->track_name;
				$std->track_artist_name = $val->track_artist_name;
				$arr_tracks[] = $std;
			} else {
				if (count($arr_tracks) < 4)
				{
					$std = new \stdClass();
					$std->track_name = $val->track_name;
					$std->track_artist_name = $val->track_artist_name;
					$arr_tracks[] = $std;
				}
			}
			$val->arr_tracks = $arr_tracks;
			$arr_result_formated[$val->id] = $val;
		}
		$arr_result = array();
		foreach ($arr_result_formated as $i => $val)
		{
			$arr_result[] = $val;
		}

		$tracklist_dto->set_arr_list($arr_result);

		return true;
	}

	public static function get_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$tracklist_dto = TracklistDto::get_instance();
		$user_dto = UserDto::get_instance();
		$artist_dto = ArtistDto::get_instance();

		$tracklist_dao = new TracklistDao();
		$arr_result = $tracklist_dao->get_detail();
		if (empty($arr_result))
		{
			return true;
		}

		reset($arr_result);
		$user_id = current($arr_result)->user_id;
		$user_dto->set_user_id($user_id);
		$artist_dto->set_artist_id(current($arr_result)->artist_id);
		$artist_dto->set_artist_name(current($arr_result)->artist_name);
		$artist_dto->set_mbid_itunes(current($arr_result)->artist_mbid_itunes);
		$artist_dto->set_mbid_lastfm(current($arr_result)->artist_mbid_lastfm);
		$artist_dto->set_url_itunes(current($arr_result)->artist_url_itunes);
		$artist_dto->set_url_lastfm(current($arr_result)->artist_url_lastfm);
		if (empty($user_id))
		{
			$tracklist_dto->set_user_name(current($arr_result)->user_name);
		}
		else
		{
			$tracklist_dto->set_user_name(current($arr_result)->user_login_name);
		}
		$tracklist_dto->set_title(current($arr_result)->title);
		$tracklist_dto->set_created_at(current($arr_result)->created_at);
		$tracklist_dto->set_updated_at(current($arr_result)->updated_at);
		$tracklist_dto->set_arr_list($arr_result);

		return true;
	}



}