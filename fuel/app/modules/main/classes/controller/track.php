<?php
namespace Main;

use main\domain\service\TrackService;
use main\model\dto\TrackDto;
use main\domain\service\AlbumService;
use main\model\dto\AlbumDto;

final class Controller_Track extends \Controller_Rest
{
	/**
	 * リクエストパラメータのアルバムIDをキーに
	 * トラック情報を取得
	 * アルバムID onlyでいいんじゃない？
	 */
	public function post_albumtracklist()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			TrackService::get_json_request();

			# バリデーションチェック
			TrackService::validation_for_albumtracklist();

			# DTOにリクエストをセット
			TrackService::set_dto_for_albumtracklist();

			# アルバム情報を取得
			AlbumService::get_album_info_by_id();

			# トラック情報を取得
			TrackService::get_track_list();

			$album_dto = AlbumDto::get_instance();
			$track_dto = TrackDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success' => true,
				'code'=> '1001',
				'response' => 'get album track list',
				'result' => array(
					'arr_list' => $track_dto->get_arr_list(),
					'release_itunes' => $album_dto->get_release_itunes(),
					'copyright_itunes' => $album_dto->get_copyright_itunes(),
					'genre_itunes' => $album_dto->get_genre_itunes(),
				),
			);

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine(). ']');

			$arr_response = array(
				'result'   => null,
				'success'  => false,
				'code'     => $e->getCode(),
				'response' => $e->getMessage(),
			);

			$this->response($arr_response);
			\Log::error('[end]'. PHP_EOL. PHP_EOL);

			return false;
		}
	}


	/**
	 * track_idからトラック詳細情報を取得
	 * @param track_id
	 */
	public function post_info()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			TrackService::get_json_request();

			# バリデーションチェック
			TrackService::validation_for_info();

			# DTOにリクエストをセット
			TrackService::set_dto_for_info();

			# トラック情報を取得
			TrackService::get_info();

			$track_dto = TrackDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success' => true,
				'code'=> '1001',
				'response' => 'get track list',
				'result' => $track_dto->get_arr_list(),
			);

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine(). ']');

			$arr_response = array(
				'result'   => null,
				'success'  => false,
				'code'     => $e->getCode(),
				'response' => $e->getMessage(),
			);

			$this->response($arr_response);
			\Log::error('[end]'. PHP_EOL. PHP_EOL);

			return false;
		}
	}


	/**
	 * トラック情報を取得
	 * 曲名から
	 * @param artist_id, track_name, limit, page
	 *
	 */
	public function post_search()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			TrackService::get_json_request();

			# バリデーションチェック
			TrackService::validation_for_search();

			# DTOにリクエストをセット
			TrackService::set_dto_for_search();

			# トラック情報を取得
			TrackService::search_track_list();

			$track_dto = TrackDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success' => true,
				'code'=> '1001',
				'response' => 'get track list',
				'result' => array(
					'arr_list' => $track_dto->get_arr_list(),
				)

			);

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			\Log::error($e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine(). ']');

			$arr_response = array(
				'result'   => null,
				'success'  => false,
				'code'     => $e->getCode(),
				'response' => $e->getMessage(),
			);

			$this->response($arr_response);
			\Log::error('[end]'. PHP_EOL. PHP_EOL);

			return false;
		}
	}
}