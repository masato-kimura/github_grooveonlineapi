<?php
namespace Main;

use main\domain\service\TracklistService;
use main\model\dto\TracklistDto;
use main\model\dto\UserDto;
use main\model\dto\ArtistDto;
use Fuel\Core\Fuel;
final class Controller_Tracklist extends \Controller_Rest
{
	/**
	 *
	 */
	public function post_set()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			TracklistService::get_json_request();

			# バリデーションチェック
			TracklistService::validation_for_set();

			# DTOにリクエストをセット
			TracklistService::set_dto_for_set();

			# DBセット
			TracklistService::set_list();

			$tracklist_dto = TracklistDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success' => true,
				'code'=> '1001',
				'response' => 'set track list',
				'result' => array(
					'tracklist_id' => $tracklist_dto->get_tracklist_id(),
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
	 *
	 */
	public function post_delete()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			TracklistService::get_json_request();

			# バリデーションチェック
			TracklistService::validation_for_delete();

			# DTOにリクエストをセット
			TracklistService::set_dto_for_delete();

			# DBセット
			TracklistService::delete_list();

			$tracklist_dto = TracklistDto::get_instance();

			# APIレスポンス
			$arr_response = array(
			'success' => true,
			'code'=> '1001',
			'response' => 'delete track list',
			'result' => array(
				'tracklist_id' => $tracklist_dto->get_tracklist_id(),
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


	public function post_getlist()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			TracklistService::get_json_request();

			# バリデーションチェック
			TracklistService::validation_for_getlist_title();

			# DTOにリクエストをセット
			TracklistService::set_dto_for_getlist();

			# DBから取得
			TracklistService::get_list();
			TracklistService::get_list_count();

			$tracklist_dto = TracklistDto::get_instance();
			$user_dto = UserDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'set tracklist',
				'result'   => array(
					'count'    => $tracklist_dto->get_count(),
					'arr_list' => $tracklist_dto->get_arr_list(),
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


	public function post_getdetail()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			TracklistService::get_json_request();

			# バリデーションチェック
			TracklistService::validation_for_get_detail();

			# DTOにリクエストをセット
			TracklistService::set_dto_for_get_detail();

			# DBから取得
			TracklistService::get_detail();

			$tracklist_dto = TracklistDto::get_instance();
			$user_dto = UserDto::get_instance();
			$artist_dto = ArtistDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'set track list',
				'result'   => array(
					'title'              => $tracklist_dto->get_title(),
					'user_id'            => $user_dto->get_user_id(),
					'user_name'          => $tracklist_dto->get_user_name(),
					'artist_id'          => $artist_dto->get_artist_id(),
					'artist_name'        => $artist_dto->get_artist_name(),
					'artist_mbid_itunes' => $artist_dto->get_mbid_itunes(),
					'artist_mbid_lastfm' => $artist_dto->get_mbid_lastfm(),
					'artist_url_itunes'  => $artist_dto->get_url_itunes(),
					'artist_url_lastfm'  => $artist_dto->get_url_lastfm(),
					'created_at'         => $tracklist_dto->get_created_at(),
					'updated_at'         => $tracklist_dto->get_updated_at(),
					'arr_list'           => $tracklist_dto->get_arr_list(),
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

}