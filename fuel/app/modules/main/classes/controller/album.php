<?php
namespace main;

use main\domain\service\AlbumService;
use main\model\dto\AlbumDto;
use main\domain\service\TrackService;
use main\domain\service\ArtistService;

final class Controller_Album extends \Controller_Rest
{
	/**
	 * アーティストIDからアルバム情報を取得
	 * @param artist_id, artist_name
	 * @return boolean
	 */
	public function post_list()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			AlbumService::get_json_request();

			# バリデーションチェック
			AlbumService::validation_for_list();

			# DTOにリクエストをセット
			AlbumService::set_dto_for_list();

			# アーティスト情報を取得
			ArtistService::get_artist_list();

			# アルバム情報を取得
			AlbumService::get_album_list();

			$album_dto = AlbumDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'get album list',
				'result'   => array(
					'arr_list' => $album_dto->get_arr_list(),
				),
			);

			$this->response($arr_response);

			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			$arr_response = array('result' => null, 'success' => false, 'code' => $e->getCode(), 'response' => $e->getMessage());
			$this->response($arr_response);
			\Log::error($e->getFile(). '['. $e->getLine(). ']');
			\Log::error($arr_response);
			\Log::error('[end]'. PHP_EOL. PHP_EOL);

			return false;
		}
	}


	/**
	 * 検索ワードからアルバム情報を取得
	 * @param word
	 * @return boolean
	 */
	public function post_search()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			AlbumService::get_json_request();

			# バリデーションチェック
			AlbumService::validation_for_search();

			# DTOにリクエストをセット
			AlbumService::set_dto_for_search();

			# アルバム情報を取得
			AlbumService::search_album_list();

			$album_dto = AlbumDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'get album list',
				'result'   => array(
					'arr_list' => $album_dto->get_arr_list(),
				),
			);

			$this->response($arr_response);

			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			$arr_response = array('result' => null, 'success' => false, 'code' => $e->getCode(), 'response' => $e->getMessage());
			$this->response($arr_response);
			\Log::error($e->getFile(). '['. $e->getLine(). ']');
			\Log::error($arr_response);
			\Log::error('[end]'. PHP_EOL. PHP_EOL);

			return false;
		}
	}


	/**
	 * アルバムIDからアルバム情報を取得する
	 * @param integer $album_id
	 * @return boolean
	 */
	public function post_info()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			AlbumService::get_json_request();

			# バリデーションチェック
			AlbumService::validation_for_info();

			# DTOにリクエストをセット
			AlbumService::set_dto_for_info();

			# アルバム情報を取得
			AlbumService::get_album_info_by_id();

			# アルバムトラック情報を取得
			TrackService::get_album_track_list_from_grooveonline();

			# フォーマット
			AlbumService::format_for_info();

			$album_dto = AlbumDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'get album info',
				'result'   => $album_dto->get_arr_list(),
			);

			$this->response($arr_response);

			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			$arr_response = array('result' => null, 'success' => false, 'code' => $e->getCode(), 'response' => $e->getMessage());
			$this->response($arr_response);
			\Log::error($e->getFile(). '['. $e->getLine(). ']');
			\Log::error($arr_response);
			\Log::error('[end]'. PHP_EOL. PHP_EOL);

			return false;
		}
	}
}