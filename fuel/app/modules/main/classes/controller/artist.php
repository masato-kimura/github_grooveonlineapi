<?php
namespace main;

use main\domain\service\ArtistService;
use main\model\dto\ArtistDto;
use main\domain\service\TracklistService;
use main\model\dto\TracklistDto;

final class Controller_Artist extends \Controller_Rest
{
	/**
	 * artist_idからアーティスト詳細情報を取得
	 * @param artist_id required
	 */
	public function post_detail()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ArtistService::get_json_request();

			# リクエストバリデーション
			ArtistService::validation_for_detail();

			# DTOにリクエストをセット
			ArtistService::set_dto_for_detail();

			# アーティスト情報を取得
			ArtistService::get_artist_detail();

			# お気に入りアーティスト情報を取得
			ArtistService::get_favorite_artist();

			# トラックリストを取得
			TracklistService::get_list();
			TracklistService::get_list_count();

			$artist_dto    = ArtistDto::get_instance();
			$tracklist_dto = TracklistDto::get_instance();

			$arr_result = $artist_dto->get_arr_list();

			if ( ! empty($arr_result))
			{
				$arr_result['favorite_status'] = $artist_dto->get_favorite_status();
				$arr_result['tracklist']       = $tracklist_dto->get_arr_list();
				$arr_result['tracklist_count'] = $tracklist_dto->get_count();
			}

			# APIレスポンス
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => __FUNCTION__,
				'result'   => $arr_result,
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
	 * アーティスト情報をitunes ,lastfmから取得し登録まで行う
	 * @return boolean
	 */
	public function post_search()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ArtistService::get_json_request();

			# リクエストバリデーション
			ArtistService::validation_for_search();

			# DTOにリクエストをセット
			ArtistService::set_dto_for_search();

			# アーティスト情報を取得＆登録
			ArtistService::search_and_regist();

			$artist_dto = ArtistDto::get_instance();

			# APIレスポンス
			$arr_response  = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'search_and_regist',
				'result'   => array(
					'arr_list' => $artist_dto->get_arr_list(),
				)
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


	public function post_getsearch()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ArtistService::get_json_request();

			# リクエストバリデーション
			ArtistService::validation_for_getsearch();

			# DTOにリクエストをセット
			ArtistService::set_dto_for_getsearch();

			# 検索されたアーティスト一覧を取得
			ArtistService::getsearch();

			$artist_dto = ArtistDto::get_instance();

			# APIレスポンス
			$arr_response  = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'getsearch',
				'result'   => array(
				'arr_list' => $artist_dto->get_arr_list(),
				)
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


	public function post_setfavorite()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ArtistService::get_json_request();

			# バリデーションチェック
			ArtistService::validation_for_setfavorite();

			# DTOにリクエストをセット
			ArtistService::set_dto_for_setfavorite();

			# データベースインサートを実行
			ArtistService::set_favorite_artist();

			$artist_dto = ArtistDto::get_instance();

			$arr_response = array(
					'success'  => true,
					'code'     => '1001',
					'response' => 'set favorite done!',
					'result'   => array(
							'favorite_artist_id' => $artist_dto->get_artist_id(),
					),
			);

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);
			return true;

		}
		catch (\Exception $e)
		{
			$arr_response = array('result' => null, 'success' => false, 'code' => $e->getCode(), 'response' => $e->getMessage());
			\Log::error($e->getFile(). '['. $e->getLine().']');
			\Log::error($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);
			$this->response($arr_response);
			return false;
		}
	}

}