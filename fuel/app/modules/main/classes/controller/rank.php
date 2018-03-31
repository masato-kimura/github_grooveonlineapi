<?php
namespace Main;

use main\domain\service\RankService;
use main\model\dto\RankDto;
final class Controller_Rank extends \Controller_Rest
{
	/**
	 * リクエストパラメータのアルバムIDをキーに
	 * トラック情報を取得
	 * アルバムID onlyでいいんじゃない？
	 * @param $about (track|album)
	 */
	public function post_week($about='track')
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);


			# JSONリクエストを取得
			RankService::get_json_request();

			# バリデーションチェック
			RankService::validation_for_week($about);

			# DTOにリクエストをセット
			RankService::set_dto_for_week($about);

			# ランキングを取得
			RankService::get_by_weekly_rank_table();

			$rank_dto = RankDto::get_instance();

			# APIレスポンス
			$arr_response = array(
				'success' => true,
				'code'=> '1001',
				'response' => 'get top rank week',
				'result' => array(
					'arr_list' => $rank_dto->get_arr_list(),
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