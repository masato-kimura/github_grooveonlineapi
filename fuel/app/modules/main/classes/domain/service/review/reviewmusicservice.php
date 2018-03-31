<?php
namespace main\domain\service\review;

use main\model\dao\ReviewMusicArtistDao;
use main\model\dao\ReviewMusicAlbumDao;
use main\model\dao\ReviewMusicTrackDao;
use main\model\dao\ReviewMusicDao;
use main\model\dao\UserCommentDao;
use main\model\dao\CommentDao;
use main\model\dao\CommentStatusDao;
use main\model\dao\UserInformationDao;
use main\model\dto\ArtistDto;
use main\model\dto\AlbumDto;
use main\model\dto\TrackDto;
use main\model\dto\CoolDto;
use main\model\dao\CoolDao;
use main\model\dto\ReviewMusicDto;
use main\model\dto\LoginDto;
use main\model\dto\UserCommentDto;
use main\model\dto\UserDto;
use main\model\dto\CommentDto;
use Fuel\Core\Validation;
use main\domain\service\Service;


/**
 * @author masato
 *
 */
class ReviewMusicService extends Service
{
	public static function validation_for_write()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('required');
		$v->add_rule('max_length', '32'); // md5
		$v->add_rule('check_login_hash');

		$v = $obj_validate->add('artist_id', 'アーティストID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('artist_name', 'アーティスト名');
		//$v->add_rule('required');
		$v->add_rule('max_length', '100');

		$v = $obj_validate->add('about', 'about');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(artist)|(album)|(track)/');

		$v = $obj_validate->add('is_delete', 'is_delete');
		$v->add_rule('valid_string', array('numeric'));
		$is_delete = \Input::post('is_delete');

		$v = $obj_validate->add('review_id');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');
		$review_id = \Input::post('review_id');
		if ( ! empty($is_delete))
		{
			$v->add_rule('required');
		}

		$v = $obj_validate->add('album_id', 'アルバムID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');
		if (\Input::post('about') === 'album' and empty($review_id))
		{
			$v->add_rule('required');
		}

		$v = $obj_validate->add('album_name', 'アルバム名');
		$v->add_rule('max_length', '100');

		$v = $obj_validate->add('track_id', 'トラックID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');
		if (\Input::post('about') === 'track' and empty($review_id))
		{
			$v->add_rule('required');
		}

		$v = $obj_validate->add('track_name', 'トラック名');
		$v->add_rule('max_length', '100');

		$v = $obj_validate->add('review', 'コメントレビュー');
		$v->add_rule('max_length', '2000');
		$review = \Input::post('review');
		$star   = \Input::post('star');
		if (empty($is_delete) and ! isset($star))
		{
			$v->add_rule('required');
		}

		$v = $obj_validate->add('star', 'スターレビュー');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('numeric_min', '0');
		$v->add_rule('numeric_max', '5');
		if (empty($is_delete) and ! isset($review))
		{
			$v->add_rule('required');
		}

		$v = $obj_validate->add('link', 'リンク');
		$v->add_rule('valid_url');

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_one()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php
		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('required');
		$v->add_rule('max_length', '32');
		$v->add_rule('check_login_hash');

		$v = $obj_validate->add('about', 'about');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(artist)|(album)|(track)/');

		$v = $obj_validate->add('artist_id', 'アーティストID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');
		if (\Input::post('about') === 'artist')
		{
			$v->add_rule('required');
		}

		$v = $obj_validate->add('album_id', 'アルバムID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');
		if (\Input::post('about') === 'album')
		{
			$v->add_rule('required');
		}

		$v = $obj_validate->add('track_id', 'トラックID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');
		if (\Input::post('about') === 'track')
		{
			$v->add_rule('required');
		}

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_top(array $arr_params)
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセット
		static::_set_request_to_post(static::$_obj_request);

		# セグメントパラメータを$_POSTにセット
		$_POST['about'] = $arr_params['about'];
		$_POST['count'] = $arr_params['count'];

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('about', 'about');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(artist)|(album)|(track)|(all)/');

		$v = $obj_validate->add('count', 'count');
		$v->add_rule('valid_string', array('numeric'));

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_getlist()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセット
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('about', 'about');
		$v->add_rule('match_pattern', '/(artist)|(album)|(track)|(all)/');

		$v = $obj_validate->add('artist_id', 'artist_id');
		$v->add_rule('valid_string', array('numeric'));

		$v = $obj_validate->add('user_id', 'user_id');
		$v->add_rule('valid_string', array('numeric'));

		$v = $obj_validate->add('offset', 'offset');
		$v->add_rule('valid_string', array('numeric'));

		$v = $obj_validate->add('limit', 'limit');
		$v->add_rule('valid_string', array('numeric'));

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function validation_for_all(array $arr_params)
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		# セグメントパラメータを$_POSTにセット
		$_POST['count_flg'] = $arr_params['count_flg'];

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('disp_user_id', '表示ユーザID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('review_id', 'レビューID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('about', 'about');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(artist)|(album)|(track)|(artist_all)|(all)/');

		$v = $obj_validate->add('about_id', 'about_id');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');
		if (\Input::post('about') === 'artist_all')
		{
			$v->add_rule('required');
		}

		$v = $obj_validate->add('search_word', '検索ワード');
		$v->add_rule('max_length', '100');

		$v = $obj_validate->add('page', 'ページ');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('numeric_min', 1);
		$v->add_rule('numeric_max', '100000000');

		$v = $obj_validate->add('limit', 'リミット');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('numeric_min', 1);
		$v->add_rule('numeric_max', '100');

		$v = $obj_validate->add('comment_offset', 'コメントオフセット');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('numeric_min', 0);
		$v->add_rule('numeric_max', '100000000');

		$v = $obj_validate->add('comment_limit', 'コメントリミット');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('numeric_min', 1);
		$v->add_rule('numeric_max', '100');


		# バリデート実行
		static::_validate_run($obj_validate);
	}


	public static function validation_for_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		# ログインハッシュ
		if (isset(static::$_obj_request->user_id))
		{
			$v = $obj_validate->add('login_hash', 'login_hash');
			$v->add_rule('max_length', '32');
			$v->add_rule('check_login_hash', static::$_obj_request->user_id); // AddValidation
		}

		$v = $obj_validate->add('review_id', 'レビューID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('about', 'about');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(artist)|(album)|(track)/');

		$v = $obj_validate->add('comment_offset', 'コメントオフセット');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('numeric_min', 0);
		$v->add_rule('numeric_max', '100000000');

		$v = $obj_validate->add('comment_limit', 'コメントリミット');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('numeric_min', 1);
		$v->add_rule('numeric_max', '100');

		# バリデート実行
		$arr_params = array();
		foreach (static::$_obj_request as $i => $val)
		{
			$arr_params[$i] = $val;
		}
		static::_validate_run($obj_validate, $arr_params);
	}


	public static function validation_for_sendcool()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		$v = $obj_validate->add('about', 'about');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(artist)|(album)|(track)/');

		$v = $obj_validate->add('review_id', 'レビューID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');
		$v->add_rule('is_not_self_review'); // AddValidation

		$v = $obj_validate->add('cool_user_id', 'クールユーザID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('login_hash', 'login_hash');
		$_POST['user_id'] = static::$_obj_request->cool_user_id;
		$v->add_rule('max_length', '32');
		$v->add_rule('check_login_hash'); // AddValidation

		$v = $obj_validate->add('ip', 'IPアドレス');
		$v->add_rule('valid_ip');

		# バリデート実行
		static::_validate_run($obj_validate);
	}


	public static function validation_for_setusercomment()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		$v = $obj_validate->add('about', 'about');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(artist)|(album)|(track)/');

		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('max_length', '32');
		$v->add_rule('check_login_hash'); // AddValidation

		$v = $obj_validate->add('user_comment', 'コメント');
		$v->add_rule('required');
		$v->add_rule('max_length', 2000);

		$v = $obj_validate->add('priority', '優先度');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', 19);

		# バリデート実行
		static::_validate_run($obj_validate);
	}


	public static function validation_for_removeusercomment()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		$v = $obj_validate->add('id', 'id');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('id'));

		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('max_length', '32');
		$v->add_rule('check_login_hash'); // AddValidation

		# バリデート実行
		static::_validate_run($obj_validate);
	}


	public static function validation_for_sendcomment()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		$v = $obj_validate->add('about', 'about');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(artist)|(album)|(track)/');

		$v = $obj_validate->add('review_id', 'review_id');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));

		$v = $obj_validate->add('review_user_id', 'review_user_id');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));

		$v = $obj_validate->add('comment', 'コメント内容');
		$v->add_rule('required');
		$v->add_rule('max_length', 500);

		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('max_length', '32');
		$v->add_rule('check_login_hash'); // AddValidation

		# バリデート実行
		$arr_params = array(
			'about'          => static::$_obj_request->about,
			'review_id'      => static::$_obj_request->review_id,
			'review_user_id' => static::$_obj_request->review_user_id,
			'comment'        => static::$_obj_request->comment,
			'user_id'        => static::$_obj_request->user_id,
			'login_hash'     => static::$_obj_request->login_hash,
		);
		static::_validate_run($obj_validate, $arr_params);
	}


	public static function validation_for_delete_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);
		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		$v = $obj_validate->add('comment_id', 'コメントID');
		$v->add_rule('required');
		$v->add_rule('valid_string', 'numeric');

		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('login_hash', 'login_hash');
		$v->add_rule('max_length', '32');
		$v->add_rule('check_login_hash'); // AddValidation

		# バリデート実行
		$arr_params = array(
			'comment_id' => static::$_obj_request->comment_id,
			'user_id'    => static::$_obj_request->user_id,
			'login_hash' => static::$_obj_request->login_hash,
		);
		static::_validate_run($obj_validate, $arr_params);
	}


	public static function validation_for_getcool()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$v = $obj_validate->add('review_id', 'レビューID');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('about', 'about');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(artist)|(album)|(track)/');

		$v = $obj_validate->add('user_id', 'ユーザID');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', '19');

		$v = $obj_validate->add('ip', 'ipアドレス');
		$v->add_rule('required');
		$v->add_rule('valid_ip');

		# バリデート実行
		static::_validate_run($obj_validate);
	}


	public static function validation_for_getcomment()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# 個別バリデート設定
		$obj_validate->add_callable('AddValidation'); // fuel/app/classes/addvalidation.php

		$v = $obj_validate->add('about', 'about');
		$v->add_rule('required');
		$v->add_rule('match_pattern', '/(artist)|(album)|(track)/');

		$v = $obj_validate->add('review_id', 'review_id');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));

		$v = $obj_validate->add('offset', 'offset');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', 19);

		$v = $obj_validate->add('limit', 'limit');
		$v->add_rule('required');
		$v->add_rule('valid_string', array('numeric'));
		$v->add_rule('max_length', 19);

		# バリデート実行
		$arr_params = array(
			'api_key'   => static::$_obj_request->api_key,
			'about'     => static::$_obj_request->about,
			'review_id' => static::$_obj_request->review_id,
			'offset'    => static::$_obj_request->offset,
			'limit'     => static::$_obj_request->limit,
		);

		static::_validate_run($obj_validate, $arr_params);
	}



	public static function set_dto_for_write()
	{
		\Log::debug('[start]'. __METHOD__);

		$login_dto  = LoginDto::get_instance();
		$artist_dto = ArtistDto::get_instance();
		$album_dto  = AlbumDto::get_instance();
		$track_dto  = TrackDto::get_instance();
		$review_dto = ReviewMusicDto::get_instance();

		foreach (static::$_obj_request as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}
			if ($key === 'user_id')
			{
				$review_dto->set_review_user_id(trim($val));
				$login_dto->set_user_id(trim($val));
			}
			if ($key === 'login_hash')
			{
				$login_dto->set_login_hash(trim($val));
			}
			if ($key === 'artist_id')
			{
				$review_dto->set_artist_id(trim($val));
				$artist_dto->set_artist_id(trim($val));
			}
			if ($key === 'artist_name')
			{
				$review_dto->set_artist_name(trim($val));
				$artist_dto->set_artist_name(trim($val));
			}
			if ($key === 'about')
			{
				$review_dto->set_about(trim($val));
			}
			if ($key === 'is_delete')
			{
				$review_dto->set_is_delete(trim($val));
			}
			if ($key === 'review_id')
			{
				$review_dto->set_review_id(trim($val));
			}
			if ($key === 'album_id')
			{
				$review_dto->set_album_id(trim($val));
				$album_dto->set_album_id(trim($val));
			}
			if ($key === 'album_name')
			{
				$review_dto->set_album_name(trim($val));
				$album_dto->set_album_name(trim($val));
			}
			if ($key === 'track_id')
			{
				$review_dto->set_track_id(trim($val));
				$track_dto->set_track_id(trim($val));
			}
			if ($key === 'track_name')
			{
				$review_dto->set_track_name(trim($val));
				$track_dto->set_track_name(trim($val));
			}
			if ($key === 'review')
			{
				$review_dto->set_review(trim($val));
			}
			if ($key === 'star')
			{
				$review_dto->set_star(trim($val));
			}
			if ($key === 'link')
			{
				$review_dto->set_link(trim($val));
			}
		}
	}


	public static function set_dto_for_one()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto   = UserDto::get_instance();
		$login_dto  = LoginDto::get_instance();
		$artist_dto = ArtistDto::get_instance();
		$album_dto  = AlbumDto::get_instance();
		$track_dto  = TrackDto::get_instance();
		$review_dto = ReviewMusicDto::get_instance();


		foreach (static::$_obj_request as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}
			if ($key === 'user_id')
			{
				$user_dto->set_user_id(trim($val));
				$login_dto->set_user_id(trim($val));
			}
			if ($key === 'login_hash')
			{
				$login_dto->set_login_hash(trim($val));
			}
			if ($key === 'about')
			{
				$review_dto->set_about(trim($val));
			}
			if ($key === 'artist_id')
			{
				$review_dto->set_artist_id(trim($val));
				$artist_dto->set_artist_id(trim($val));
			}
			if ($key === 'album_id')
			{
				$review_dto->set_album_id(trim($val));
				$album_dto->set_album_id(trim($val));
			}
			if ($key === 'track_id')
			{
				$review_dto->set_track_id(trim($val));
				$track_dto->set_track_id(trim($val));
			}
		}
	}


	public static function set_dto_for_top()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_dto = ReviewMusicDto::get_instance();

		$review_dto->set_about(trim(\Input::post('about')));
		$review_dto->set_count(trim(\Input::post('count')));
	}


	public static function set_dto_for_getlist()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_dto = ReviewMusicDto::get_instance();

		foreach (static::$_obj_request as $key => $val)
		{
			if ( ! isset($val))
			{
				continue;
			}

			if ($key === 'artist_id')
			{
				$review_dto->set_artist_id(trim($val));
			}
			if ($key === 'user_id')
			{
				$review_dto->set_review_user_id(trim($val));
			}
			if ($key === 'about')
			{
				$review_dto->set_about(trim($val));
			}
			if ($key === 'offset')
			{
				$review_dto->set_offset(trim($val));
			}
			if ($key === 'limit')
			{
				$review_dto->set_limit(trim($val));
			}
		}
	}


	public static function set_dto_for_all()
	{
		\Log::debug('[start]'. __METHOD__);

		$user_dto         = UserDto::get_instance();
		$login_dto        = LoginDto::get_instance();
		$review_music_dto = ReviewMusicDto::get_instance();
		$artist_dto       = ArtistDto::get_instance();
		$comment_dto      = CommentDto::get_instance();

		$comment_dto->set_offset(0);
		$comment_dto->set_limit(10);

		foreach (static::$_obj_request as $key => $val)
		{
			if ( ! isset($val))
			{
				continue;
			}

			if ($key === 'user_id')
			{
				$login_dto->set_user_id(trim($val));
			}
			if ($key === 'disp_user_id')
			{
				$user_dto->set_disp_user_id(trim($val));
				$review_music_dto->set_review_user_id(trim($val));
			}
			if ($key === 'review_id')
			{
				$review_music_dto->set_review_id(trim($val));
				$comment_dto->set_review_id(trim($val));
			}
			if ($key === 'about')
			{
				$review_music_dto->set_about(trim($val));
				$comment_dto->set_about(trim($val));
			}
			if ($key === 'about_id')   {
				$review_music_dto->set_about_id(trim($val));
			}
			if ($key === 'search_word')
			{
				$review_music_dto->set_search_word(trim($val));
			}
			if ($key === 'page')
			{
				$review_music_dto->set_page(trim($val));
			}
			if ($key === 'limit')
			{
				$review_music_dto->set_limit(trim($val));
			}
			if ($key === 'comment_offset')
			{
				$comment_dto->set_offset(trim($val));
			}
			if ($key === 'comment_limit')
			{
				$comment_dto->set_limit(trim($val));
			}
		} // endforeach

		return true;
	}


	public static function set_dto_for_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$login_dto        = LoginDto::get_instance();
		$review_music_dto = ReviewMusicDto::get_instance();
		$comment_dto      = CommentDto::get_instance();

		$comment_dto->set_offset(0);
		$comment_dto->set_limit(10);

		foreach (static::$_obj_request as $key => $val)
		{
			if ( ! isset($val)) continue;

			if ($key === 'user_id')
			{
				$login_dto->set_user_id(trim($val));
				$review_music_dto->set_review_user_id(trim($val));
				$comment_dto->set_review_user_id(trim($val));
			}
			if ($key === 'review_id')
			{
				$review_music_dto->set_review_id(trim($val));
				$comment_dto->set_review_id(trim($val));
			}
			if ($key === 'about')
			{
				$review_music_dto->set_about(trim($val));
				$comment_dto->set_about(trim($val));
			}
			if ($key === 'comment_offset')
			{
				$comment_dto->set_offset(trim($val));
			}
			if ($key === 'comment_limit')
			{
				$comment_dto->set_limit(trim($val));
			}
		} // endforeach

		return true;
	}


	public static function set_dto_for_sendcool()
	{
		\Log::debug('[start]'. __METHOD__);

		$cool_dto   = CoolDto::get_instance();
		$review_dto = ReviewMusicDto::get_instance();
		$login_dto  = LoginDto::get_instance();

		foreach (static::$_obj_request as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}
			if ($key === 'review_id')
			{
				$review_dto->set_review_id(trim($val));
				$cool_dto->set_review_id(trim($val));
			}
			if ($key === 'about')
			{
				$review_dto->set_about(trim($val));
				$cool_dto->set_about(trim($val));
			}
			if ($key === 'cool_user_id')
			{
				$cool_dto->set_cool_user_id(trim($val));
				$login_dto->set_user_id(trim($val));
			}
			if ($key === 'login_hash')
			{
				$login_dto->set_login_hash(trim($val));
			}
			if ($key === 'ip')
			{
				$cool_dto->set_ip(trim($val));
			}
		} // endforeach

		return true;
	}


	public static function set_dto_for_setusercomment()
	{
		\Log::debug('[start]'. __METHOD__);

		$login_dto  = LoginDto::get_instance();
		$user_comment_dto = UserCommentDto::get_instance();

		foreach (static::$_obj_request as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}
			if ($key === 'about')
			{
				$user_comment_dto->set_about(trim($val));
			}
			if ($key === 'user_id')
			{
				$user_comment_dto->set_user_id(trim($val));
				$login_dto->set_user_id(trim($val));
			}
			if ($key === 'login_hash')
			{
				$login_dto->set_login_hash(trim($val));
			}
			if ($key === 'user_comment')
			{
				$user_comment_dto->set_user_comment(trim($val));
			}
			if ($key === 'priority')
			{
				$user_comment_dto->set_priority(trim($val));
			}
		} // endforeach

		return true;
	}


	public static function set_dto_for_removeusercomment()
	{
		\Log::debug('[start]'. __METHOD__);

		$login_dto  = LoginDto::get_instance();
		$user_comment_dto = UserCommentDto::get_instance();

		foreach (static::$_obj_request as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}
			if ($key === 'user_id')
			{
				$user_comment_dto->set_user_id(trim($val));
				$login_dto->set_user_id(trim($val));
			}
			if ($key === 'login_hash')
			{
				$login_dto->set_login_hash(trim($val));
			}
			if ($key === 'id')
			{
				$user_comment_dto->set_user_comment_id(trim($val));
			}
		} // endforeach

		return true;
	}


	public static function set_dto_for_sendcomment()
	{
		\Log::debug('[start]'. __METHOD__);

		$login_dto   = LoginDto::get_instance();
		$comment_dto = CommentDto::get_instance();

		foreach (static::$_obj_request as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}
			if ($key === 'about')
			{
				$comment_dto->set_about(trim($val));
			}
			if ($key === 'review_id')
			{
				$comment_dto->set_review_id(trim($val));
			}
			if ($key === 'review_user_id')
			{
				$comment_dto->set_review_user_id(trim($val));
			}
			if ($key === 'comment')
			{
				$comment_dto->set_comment(trim($val));
			}
			if ($key === 'comment_offset')
			{
				$comment_dto->set_offset(trim($val));
			}
			if ($key === 'comment_limit')
			{
				$comment_dto->set_limit(trim($val));
			}
			if ($key === 'user_id')
			{
				$login_dto->set_user_id(trim($val));
				$comment_dto->set_comment_user_id(trim($val));
			}
			if ($key === 'login_hash')
			{
				$login_dto->set_login_hash(trim($val));
			}
		} // endforeach

		return true;
	}


	public static function set_dto_for_delete_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		$login_dto   = LoginDto::get_instance();
		$comment_dto = CommentDto::get_instance();

		foreach (static::$_obj_request as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}
			if ($key === 'comment_id')
			{
				$comment_dto->set_comment_id(trim($val));
			}
			if ($key === 'user_id')
			{
				$login_dto->set_user_id(trim($val));
				$comment_dto->set_comment_user_id(trim($val));
			}
			if ($key === 'login_hash')
			{
				$login_dto->set_login_hash(trim($val));
			}
		} // endforeach

		return true;
	}





	public static function set_dto_for_getcool()
	{
		\Log::debug('[start]'. __METHOD__);

		$cool_dto   = CoolDto::get_instance();
		$review_dto = ReviewMusicDto::get_instance();
		$login_dto  = LoginDto::get_instance();

		$cool_dto->set_offset(0);
		$cool_dto->set_limit(30);

		foreach (static::$_obj_request as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}
			if ($key === 'review_id')
			{
				$review_dto->set_review_id(trim($val));
				$cool_dto->set_review_id(trim($val));
			}
			if ($key === 'about')
			{
				$review_dto->set_about(trim($val));
				$cool_dto->set_about(trim($val));
			}
			if ($key === 'user_id')
			{
				$login_dto->set_user_id(trim($val));
				$cool_dto->set_cool_user_id(trim($val));
			}
			if ($key === 'ip')
			{
				$cool_dto->set_ip(trim($val));
			}
			if ($key === 'offset')
			{
				$cool_dto->set_offset(trim($val));
			}
			if ($key === 'limit')
			{
				$cool_dto->set_limit(trim($val));
			}
		} // endforeach

		return true;
	}


	public static function set_dto_for_getcomment()
	{
		\Log::debug('[start]'. __METHOD__);

		$login_dto   = LoginDto::get_instance();
		$comment_dto = CommentDto::get_instance();
		foreach (static::$_obj_request as $key => $val)
		{
			if (empty($val))
			{
				continue;
			}
			if ($key === 'about')
			{
				$comment_dto->set_about(trim($val));
			}
			if ($key === 'review_id')
			{
				$comment_dto->set_review_id(trim($val));
			}
			if ($key === 'offset')
			{
				$comment_dto->set_offset(trim($val));
			}
			if ($key === 'limit')
			{
				$comment_dto->set_limit(trim($val));
			}
		} // endforeach

		return true;
	}


	public static function set_unread_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		$login_dto   = LoginDto::get_instance();
		$comment_dto = CommentDto::get_instance();
		$review_dto  = ReviewMusicDto::get_instance();


		$arr_list = $review_dto->get_arr_list();
		if ( ! empty($arr_list))
		{
			$review_user_id = current($arr_list)['user_id'];

			$user_id = $login_dto->get_user_id();

			if ($review_user_id != $user_id)
			{
				return true;
			}
		}

		$commentstatus_dao = new CommentStatusDao();

		$arr_where = array(
			'review_id' => $review_dto->get_review_id(),
			'about'     => $review_dto->get_about(),
			'is_read'   => false,
		);
		$arr_values = array(
			'is_read' => true,
		);

		return $commentstatus_dao->update($arr_values, $arr_where);
	}


	public static function get_all_review()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();

		$arr_artist_review = ArtistReview::get_all_user_review();
		$arr_album_review  = AlbumReview::get_all_user_review();
		$arr_track_review  = TrackReview::get_all_user_review();
		$arr_review = array();
		foreach ($arr_artist_review as $i => $val)
		{
			$arr_review[strtotime($val['created_at'])] = $val;
		}
		foreach ($arr_album_review as $i => $val)
		{
			$arr_review[strtotime($val['created_at'])] = $val;
		}
		foreach ($arr_track_review as $i => $val)
		{
			$arr_review[strtotime($val['created_at'])] = $val;
		}

		krsort($arr_review);
		$arr_top_review = array();
		$offset = ($review_music_dto->get_page() - 1) * $review_music_dto->get_limit();
		$i = 0;
		foreach ($arr_review as $val)
		{
			if ($i < $offset)
			{
				continue;
			}
			if ($i < $review_music_dto->get_limit())
			{
				$arr_top_review[] = $val;
				//\Log::info($val);
				$i++;
			}
			else
			{
				break;
			}
		}

		$review_music_dto->set_arr_list($arr_top_review);
		return true;
	}


	public static function get_all_review_by_view_count()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$review_music_dao = new ReviewMusicDao();

		$count = $review_music_dao->get_review_list_count();
		$review_music_dto->set_count($count);

		return true;
	}


	public static function get_all_review_by_view()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$review_music_dao = new ReviewMusicDao();

		$arr_list = $review_music_dao->get_review_list();
		$arr_result = static::_format_review_music_list($arr_list);
		$review_music_dto->set_arr_list($arr_result);

		return true;
	}


	public static function get_review_detail()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$arr_result = array();

		switch ($review_music_dto->get_about())
		{
			case 'artist':
				$arr_result = ArtistReview::get_review_detail();
				break;
			case 'album':
				$arr_result = AlbumReview::get_review_detail();
				break;
			case 'track':
				$arr_result = TrackReview::get_review_detail();
				break;
			default :
				throw new \Exception('not exist about');
		}

		$review_music_dto->set_arr_list($arr_result);

		return true;
	}



	private static function _format_review_music_list($arr_list)
	{
		\Log::info('[start]'. __METHOD__);

		$arr_result = array();
		$arr_key    = array();
		$arr_comment_read   = array();
		$arr_comment_was_read = array();

		foreach ($arr_list as $i => $val)
		{

			$key = $val['id']. $val['about'];

			// レビューに対するコメント数を集計
			if ( ! empty($val['comment_id']))
			{
				if (isset($arr_comment_read[$key]['count']))
				{
					$arr_comment_read[$key]['count']++;
				}
				else
				{
					$arr_comment_read[$key]['count'] = 1;
				}
			}

			// コメント既読数を集計
			if ( ! empty($val['is_read']))
			{
				if (isset($arr_comment_was_read[$key]['count']))
				{
					$arr_comment_was_read[$key]['count']++;
				}
				else
				{
					$arr_comment_was_read[$key]['count'] = 1;
				}
			}

			// キーがセットされていたらループを抜ける。（続く再生成は必要ないため）
			if (isset($arr_key[$key])) continue;
			$arr_key[$key] = true;

			// コメントIDと既読フラグを抜いた配列を再生成
			unset($val['comment_id']);
			unset($val['is_read']);
			$arr_result[$key] = $val;

		} // endforeach

		unset($key);

		$arr_result_list = array();
		foreach ($arr_result as $key => $val)
		{
			if (isset($arr_comment_read[$key]['count']))
			{
				$val['comment_count'] = $arr_comment_read[$key]['count'];
			}
			else
			{
				$val['comment_count'] = 0;
			}

			if (isset($arr_comment_was_read[$key]['count']))
			{
				$val['was_read_comment_count'] = $arr_comment_was_read[$key]['count'];
			}
			else
			{
				$val['was_read_comment_count'] = 0;
			}

			$arr_result_list[] = $val;
		}

		return $arr_result_list;
	}


	/**
	 * user_idと(artist_id or album_id or track_id)でレビューを特定する
	 * @return boolean
	 */
	public static function get_one()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$login_dto  = LoginDto::get_instance();
		$artist_dto = ArtistDto::get_instance();
		$album_dto  = AlbumDto::get_instance();
		$track_dto  = TrackDto::get_instance();

		$user_id    = $login_dto->get_user_id();
		$about      = $review_music_dto->get_about();
		$artist_id  = $artist_dto->get_artist_id();
		$album_id   = $album_dto->get_album_id();
		$track_id   = $track_dto->get_track_id();

		$arr_where = array();
		$arr_where['about']   = $about;
		$arr_where['user_id'] = $user_id;

		switch ($review_music_dto->get_about())
		{
			case 'artist':
				$review_music_dao = new ReviewMusicArtistDao();
				if ( ! empty($artist_id))
				{
					$arr_where['artist_id'] = $artist_id;
				}
				break;
			case 'album':
				$review_music_dao = new ReviewMusicAlbumDao();
				if ( ! empty($album_id))
				{
					$arr_where['album_id'] = $album_id;
				}
				break;
			case 'track':
				$review_music_dao = new ReviewMusicTrackDao();
				if ( ! empty($track_id))
				{
					$arr_where['track_id'] = $track_id;
				}
				break;
			default:
				throw new \Exception('not about for get one');
		}

		$arr_result = $review_music_dao->get_one($arr_where, array());
		$review_music_dto->set_arr_list($arr_result);

		return true;
	}


	public static function get_user_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		$login_dto = LoginDto::get_instance();
		$user_comment_dto = UserCommentDto::get_instance();

		$user_comment_dao = new UserCommentDao();
		$arr_result = $user_comment_dao->get_user_comment($login_dto->get_user_id());
		$user_comment_dto->set_arr_comment($arr_result);

		return true;
	}


	/**
	 * トップレビューリストを取得
	 *
	 * @return boolean
	 */
	public static function get_top_review()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$arr_result = array();
		switch ($review_music_dto->get_about())
		{
			case 'artist':
				$arr_result = ArtistReview::get_top_list();
				break;
			case 'album':
				$arr_result = AlbumReview::get_top_list();
				break;
			case 'track':
				$arr_result = TrackReview::get_top_list();
				break;
			default:
				$review_music_dao = new ReviewMusicDao();
				$arr_result = $review_music_dao->get_top_list();
		}

		$review_music_dto->set_arr_list($arr_result);

		return true;
	}


	/**
	 * トップレビューリストを取得
	 *
	 * @return boolean
	 */
	public static function get_list()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$review_music_dao = new ReviewMusicDao();

		// list
		$arr_list = $review_music_dao->get_review_list();
		$arr_list = static::_format_review_music_list($arr_list);
		$review_music_dto->set_arr_list($arr_list);

		// count
		$cnt = $review_music_dao->get_review_list_count();
		$review_music_dto->set_count($cnt);

		return true;
	}


	/**
	 * レビューを登録および更新する
	 *
	 * @throws \Exception
	 * @return boolean
	 */
	public static function insert_review_music()
	{
		\Log::debug('[start]'. __METHOD__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$artist_dto       = ArtistDto::get_instance();
		$album_dto        = AlbumDto::get_instance();
		$track_dto        = TrackDto::get_instance();
		$review_id        = $review_music_dto->get_review_id();
		$is_delete        = $review_music_dto->get_is_delete();

		$arr_values = array(
				'user_id'     => $review_music_dto->get_review_user_id(),
				'artist_id'   => $review_music_dto->get_artist_id(),
				'artist_name' => $review_music_dto->get_artist_name(),
				'album_id'    => $review_music_dto->get_album_id(),
				'album_name'  => $review_music_dto->get_album_name(),
				'track_id'    => $review_music_dto->get_track_id(),
				'track_name'  => $review_music_dto->get_track_name(),
				'link'        => $review_music_dto->get_link(),
				'review'      => $review_music_dto->get_review(),
				'about'       => $review_music_dto->get_about(),
				'star'        => $review_music_dto->get_star(),
		);

		if (empty($arr_values['artist_id']))
		{
			$arr_values['artist_id'] = $artist_dto->get_artist_id();
			$review_music_dto->set_artist_id($artist_dto->get_artist_id());
		}
		if (empty($arr_values['artist_name']))
		{
			$arr_values['artist_name'] = $artist_dto->get_artist_name();
			$review_music_dto->set_artist_name($artist_dto->get_artist_name());
		}
		if (empty($arr_values['album_id']))
		{
			$arr_values['album_id'] = $album_dto->get_album_id();
			$review_music_dto->set_album_id($album_dto->get_album_id());
		}
		if (empty($arr_values['album_name']))
		{
			$arr_values['album_name'] = $album_dto->get_album_name();
			$review_music_dto->set_album_name($album_dto->get_album_name());
		}

		if (empty($arr_values['track_id']))
		{
			$arr_values['track_id'] = $track_dto->get_track_id();
			$review_music_dto->set_track_id($track_dto->get_track_id());
		}
		if (empty($arr_values['track_name']))
		{
			$arr_values['track_name'] = $track_dto->get_track_name();
			$review_music_dto->set_track_name($track_dto->get_track_name());
		}

		# factory
		switch ($review_music_dto->get_about())
		{
			case 'artist':
				$review_music_dao = new ReviewMusicArtistDao();
				break;
			case 'album':
				$review_music_dao = new ReviewMusicAlbumDao();
				unset($arr_values['track_id']);
				unset($arr_values['track_name']);
				break;
			case 'track':
				$review_music_dao = new ReviewMusicTrackDao();
				break;
			default :
				throw new \Exception('not exist review_about');
		}

		$arr_values_modified = array();
		foreach ($arr_values as $key => $val)
		{
			$arr_values_modified['star'] = 0;

			if ( ! is_null($val))
			{
				$arr_values_modified[$key] = $val;
			}
		}

		if (empty($review_id) and $review_music_dao->is_empty_same_review())
		{
			if ( ! empty($is_delete))
			{
				throw new \Exception('error is_delete ? ');
			}

			# insert
			$arr_result = $review_music_dao->insert_review_music($arr_values_modified);
			$review_music_dto->set_review_id($arr_result[0]);
			if (empty($arr_result))
			{
				throw new \Exception('no return db_request', 8001);
			}
		}
		else
		{
			# update
			if ((empty($is_delete)) or !(empty($arr_values['star']) and empty($arr_values['review'])))
			{
				if ( ! empty($review_id))
				{
					$arr_result = $review_music_dao->update_review_music_by_id($arr_values_modified);
				}
				else
				{
					$arr_result = $review_music_dao->update_review_music_by_same_title($arr_values_modified);
				}
			}

			# delete
			else
			{
				$arr_result = $review_music_dao->delete_review_music($review_id);
			}

			$review_music_dto->set_review_id($review_id);
		}

		return true;
	}


	public static function get_artist_from_table() // @todo これ一個に絞るのはムズいな
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$review_music_dto = ReviewMusicDto::get_instance();
		$artist_name = static::_format_name($review_music_dto->get_artist_name());

		# ビンゴ！
		$arr_return = static::get_just_artist_name($artist_name);
		if ( ! empty($arr_return))
		{
			return $arr_return;
		}

		# ビンゴができなかったら似た名前を探す（テキスト検索のため処理重）
		$arr_return = static::get_same_artist_name($artist_name);

		return true;
	}


	/**
	 * review_idを指定して３０件のクールユーザを取得
	 * @return boolean
	 */
	public static function get_cool_users()
	{
		\Log::debug('[start]'. __METHOD__);

		$cool_dto = CoolDto::get_instance();
		$cool_dao = new CoolDao();

		$arr_where = array(
			'review_id' => $cool_dto->get_review_id(),
			'about'     => $cool_dto->get_about(),
		);

		$arr_result = $cool_dao->get_cool_users($arr_where, $cool_dto->get_offset(), $cool_dto->get_limit());

		$cool_dto->set_arr_list($arr_result);

		return true;
	}


	public static function get_cool_user_count()
	{
		\Log::debug('[start]'. __METHOD__);

		$cool_dto = CoolDto::get_instance();
		$cool_dao = new CoolDao();

		$arr_where = array(
			'review_id' => $cool_dto->get_review_id(),
			'about'     => $cool_dto->get_about(),
		);

		$arr_result = $cool_dao->get_cool_user_count($arr_where);

		$cool_dto->set_all_count($arr_result['cnt']);

		return true;
	}


	public static function is_cool_done()
	{
		\Log::debug('[start]'. __METHOD__);

		$cool_dto  = CoolDto::get_instance();
		$cool_dao  = new CoolDao();

		$about     = $cool_dto->get_about();
		$review_id = $cool_dto->get_review_id();
		$user_id   = $cool_dto->get_cool_user_id();
		$ip        = $cool_dto->get_ip();

		$arr_result=$cool_dao->specify_cool_user($about, $review_id, $user_id, $ip);
		if ( ! empty($arr_result))
		{
			$cool_dto->set_is_done(true);
		}

		return true;
	}


	public static function set_cool()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$cool_dto = CoolDto::get_instance();
			$cool_dao = new CoolDao();

			# 既存がないか
			$arr_result = $cool_dao->get_my_cool();
			if ( ! empty($arr_result))
			{
				# 存在時は反映なしで終了ー
				$cool_dto->set_reflection(false);
				return true;
			}

			$cool_dao->start_transaction();

			$review_dao = null;
			switch ($cool_dto->get_about())
			{
				case 'artist':
					$review_dao = new ReviewMusicArtistDao();
					break;
				case 'album':
					$review_dao = new ReviewMusicAlbumDao();
					break;
				case 'track':
					$review_dao = new ReviewMusicTrackDao();
					break;
			}

			// review_user_idの取得
			$arr_where = array('id' => $cool_dto->get_review_id());
			$arr_columns = array('user_id');
			$arr_result = $review_dao->search_one($arr_where, $arr_columns);
			$cool_dto->set_review_user_id($arr_result->user_id);

			// coolテーブルへ登録
			list($id, $set_count) = $cool_dao->set_cool();
			if ($set_count > 0)
			{
				$cool_dto->set_reflection(true);

				// レビューテーブルを更新
				$review_dao->set_cool();
			}
			else
			{
				$cool_dto->set_reflection(false);
				$arr_send_values = array(
						'about'     => $cool_dto->get_about(),
						'review_id' => $cool_dto->get_review_id(),
						'user_id'   => $cool_dto->get_cool_user_id(),
						'ip'        => $cool_dto->get_ip(),
				);
				¥Log::error('coolユーザが重複しようとしてます');
				¥Log::error($arr_send_values);
			}

			$cool_dao->commit_transaction();

			# 全件数取得
			$count = $review_dao->get_cool_count();
			$cool_dto->set_cool_count($count);

			return true;
		}
		catch (\Exception $e)
		{
			if ($cool_dao->in_transaction())
			{
				$cool_dao->rollback_transaction();
			}
			throw new \Exception($e);

			return false;
		}
	}


	public static function set_usercomment()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$user_comment_dto = UserCommentDto::get_instance();
			$user_comment_dao = new UserCommentDao();

			$priority = $user_comment_dto->get_priority();
			if (empty($priority))
			{
				$user_comment_dto->set_priority(99999);
			}

			$user_comment_dao->start_transaction();

			# インサート
			$result = $user_comment_dao->set_user_comment();
			$user_comment_dto->set_user_comment_id($result);

			$user_comment_dao->commit_transaction();


			return true;
		}
		catch (\Exception $e)
		{
			if ($user_comment_dao->in_transaction())
			{
				$user_comment_dao->rollback_transaction();
			}
			throw new \Exception($e);

			return false;
		}
	}


	public static function remove_usercomment()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$user_comment_dto = UserCommentDto::get_instance();
			$user_comment_dao = new UserCommentDao();

			$user_comment_dao->start_transaction();

			# 物理削除
			$result = $user_comment_dao->remove_user_comment();

			$user_comment_dao->commit_transaction();


			return true;
		}
		catch (\Exception $e)
		{
			if ($user_comment_dao->in_transaction())
			{
				$user_comment_dao->rollback_transaction();
			}
			throw new \Exception($e);

			return false;
		}
	}


	public static function set_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$comment_dto = CommentDto::get_instance();
			$comment_dao = new CommentDao();
			$comment_information_dao = new CommentStatusDao();

			# インサート
			$comment_dao->start_transaction();

			$comment_id  = $comment_dao->set_comment();
			$comment_dto->set_comment_id($comment_id);
			$result_info = $comment_information_dao->set_information();

			$comment_dao->commit_transaction();

			return true;
		}
		catch (\Exception $e)
		{
			if ($comment_dao->in_transaction())
			{
				$comment_dao->rollback_transaction();
			}
			throw new \Exception($e);

			return false;
		}
	}


	public static function delete_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$comment_dto = CommentDto::get_instance();
			$comment_dao = new CommentDao();
			$comment_status_dao = new CommentStatusDao();

			# delete
			$comment_dao->start_transaction();

			$comment_dao->delete_comment();
			$comment_status_dao->delete_information();

			$comment_dao->commit_transaction();

			return true;
		}
		catch (\Exception $e)
		{
			if ($comment_dao->in_transaction())
			{
				$comment_dao->rollback_transaction();
			}
			throw new \Exception($e);

			return false;
		}
	}


	public static function get_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$comment_dto = CommentDto::get_instance();
			$comment_dao = new CommentDao();

			// 一覧を取得
			$review_id = $comment_dto->get_review_id();
			$about     = $comment_dto->get_about();
			$offset = $comment_dto->get_offset();
			$limit  = $comment_dto->get_limit();
			$arr_list  = $comment_dao->get_comment_list($review_id, $about, $offset, $limit);
			$arr_list  = array_reverse($arr_list);
			$comment_dto->set_arr_list($arr_list);

			return true;
		}
		catch (\Exception $e)
		{
			if ($comment_dao->in_transaction())
			{
				$comment_dao->rollback_transaction();
			}
			throw new \Exception($e);

			return false;
		}
	}


	public static function get_review_id_from_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		$comment_dao = new CommentDao();
		$comment_dto = CommentDto::get_instance();
		$arr_where = array(
			'id' => $comment_dto->get_comment_id(),
		);
		$arr_columns = array(
			'review_id',
			'about',
		);
		$result = $comment_dao->search_one($arr_where, $arr_columns);

		$comment_dto->set_review_id($result->review_id);
		$comment_dto->set_about($result->about);

		return true;
	}


	public static function get_comment_count()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$comment_dto = CommentDto::get_instance();
			$comment_dao = new CommentDao();

			// 件数を取得
			$review_id = $comment_dto->get_review_id();
			$about     = $comment_dto->get_about();
			$arr_count = $comment_dao->get_comment_count($review_id, $about);
			$comment_dto->set_count($arr_count['cnt']);

			return true;
		}
		catch (\Exception $e)
		{
			if ($comment_dao->in_transaction())
			{
				$comment_dao->rollback_transaction();
			}
			throw new \Exception($e);

			return false;
		}
	}


	public static function unset_comment_information()
	{
		\Log::debug('[start]'. __METHOD__);

		$login_dto = LoginDto::get_instance();
		$user_id = $login_dto->get_user_id();
		if (empty($user_id))
		{
			return true;
		}

		// 消し込み
		$information_dao = new UserInformationDao();
		$information_dao->update(array('is_read' => true), array('user_id' => $user_id));

		return true;
	}


	public static function get_review_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$comment_dto = CommentDto::get_instance();

			$review_id = $comment_dto->get_review_id();
			$about     = $comment_dto->get_about();
			$offset    = $comment_dto->get_offset();
			$limit     = $comment_dto->get_limit();

			if (empty($review_id))
			{
				\Log::info('review_idが未特定');
				return true;
			}

			$comment_dao = new CommentDao();
			$arr_count = $comment_dao->get_comment_count($review_id, $about);
			$arr_list  = $comment_dao->get_comment_list($review_id, $about, $offset, $limit);
			$arr_list  = array_reverse($arr_list);
			$comment_dto->set_arr_list($arr_list);
			$comment_dto->set_count($arr_count['cnt']);
		}
		catch (\Exception $e)
		{
			throw new \Exception($e);
		}
	}


	public static function get_review_detail_comment()
	{
		\Log::debug('[start]'. __METHOD__);

		try
		{
			$comment_dto = CommentDto::get_instance();

			$review_id = $comment_dto->get_review_id();
			$about     = $comment_dto->get_about();
			$offset    = $comment_dto->get_offset();
			$limit     = $comment_dto->get_limit();

			$comment_dao = new CommentDao();
			$arr_count = $comment_dao->get_comment_count($review_id, $about);
			$arr_list  = $comment_dao->get_comment_list($review_id, $about, $offset, $limit);
			$arr_list  = array_reverse($arr_list);
			$comment_dto->set_arr_list($arr_list);
			$comment_dto->set_count($arr_count['cnt']);

			return true;
		}
		catch (\Exception $e)
		{
			throw new \Exception($e);
		}
	}



	private static function get_just_artist_name($name)
	{
		\Log::debug('[start]'. __METHOD__);

		$artist_dao = new ArtistDao();
		return $artist_dao->get_just_artist_list($name);
	}


	private static function get_same_artist_name($name)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$artist_dao = new ArtistDao();
		$arr_result = $artist_dao->get_same_aritist_list($name);
		$arr_return = array();
		foreach ($arr_result as $i => $arr_val)
		{
			$arr_same_names = implode($artist_dao->get_delimiter(), $arr_val['same_names']);
			if (in_array($name, $arr_same_names))
			{
				$arr_return[] = $arr_result;
			}
		}

		return $arr_result;
	}


	private static function _format_name($name)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		$name = mb_strtoupper($name);
		$name = mb_convert_kana($name, 'Kas');
		$name = preg_replace('/[\s]+/', ' ', $name);

		return trim($name);
	}
}