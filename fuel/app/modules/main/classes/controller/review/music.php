<?php
namespace main;

use main\domain\service\review\ReviewMusicService;
use Fuel\Core\Controller_Rest;
use main\model\dto\ReviewMusicDto;
use main\model\dto\CoolDto;
use main\model\dto\UserCommentDto;
use main\domain\service\ArtistService;
use main\model\dto\ArtistDto;
use main\model\dto\CommentDto;
/**
 * @throws \Exception
 *  1001 正常
 *  8001 システムエラー
 *  8002 DBエラー
 *  9001 認証エラー
 *  9002 リクエスト内容未存在
 *  9003 必須項目エラー
 * @author masato
 *
 */
final class Controller_Review_Music extends Controller_Rest
{
	/**
	 * レビュー書き込み、更新、削除
	 * @param login_hash required
	 * @return boolean
	 */
	public function post_write()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			# リクエストバリデーション
			ReviewMusicService::validation_for_write();

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_write();

			# 投稿を登録
			ReviewMusicService::insert_review_music();

			$review_music_dto = ReviewMusicDto::get_instance();

			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'your music review write complate !',
				'result'   => array(
					'review_id'  => $review_music_dto->get_review_id(),
					'updated_at' => \Date::forge()->format('%Y-%m-%d %H:%M:%S'),
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


	/**
	 * ユーザが投稿したレビューを一件取得する
	 * user_id と （アーティスト、アルバム、トラック）のIDでレビューを特定
	 * review_idでは特定しない
	 *
	 * @param login_hash required
	 * @return boolean
	 */
	public function post_one()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			# リクエストバリデーション
			ReviewMusicService::validation_for_one();

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_one();

			# レビュー一覧を取得
			ReviewMusicService::get_one();

			# ユーザコメントを取得（アーティストレビュー取得時のみ）
			ReviewMusicService::get_user_comment();

			# お気に入りアーティスト情報を取得
			ArtistService::get_favorite_artist();

			$review_music_dto = ReviewMusicDto::get_instance();
			$user_comment_dto = UserCommentDto::get_instance();
			$artist_dto       = ArtistDto::get_instance();

			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'get music review one',
				'result'   =>
					array(
						'arr_review'       => $review_music_dto->get_arr_list(),
						'arr_user_comment' => $user_comment_dto->get_arr_comment(),
						'favorite_status'  => $artist_dto->get_favorite_status(),
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


	/**
	 * ユーザ関係なくすべてのレビューを取得する
	 * @return boolean
	 */
	public function post_top($about='all', $count='5')
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			# リクエストバリデーション
			$arr_params = array(
				'about' => $about,
				'count' => $count,
			);
			ReviewMusicService::validation_for_top($arr_params);

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_top();

			# トップレビューを取得
			ReviewMusicService::get_top_review();

			$review_music_dto = ReviewMusicDto::get_instance();
			$arr_response = array(
					'success'  => true,
					'code'     => '1001',
					'response' => 'get top review',
					'result'   => array(
						'arr_list' => $review_music_dto->get_arr_list(),
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


	/**
	 * レビューを取得する
	 * @return boolean
	 */
	public function post_getlist()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			ReviewMusicService::validation_for_getlist();

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_getlist();

			# リストを取得
			ReviewMusicService::get_list();

			$review_music_dto = ReviewMusicDto::get_instance();
			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'get review list',
				'result'   => array(
					'arr_list' => $review_music_dto->get_arr_list(),
					'count'    => $review_music_dto->get_count(),
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



	/**
	 * ユーザ関係なくすべてのレビューを取得する
	 * @return boolean
	 */
	public function post_all($count_flg=null)
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			# リクエストバリデーション
			$arr_params = array('count_flg' => $count_flg);
			ReviewMusicService::validation_for_all($arr_params);

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_all();

			# レビュー一覧を取得
			if (empty($count_flg))
			{
				// カウントを取得
				ReviewMusicService::get_all_review_by_view_count();

				// レビューを取得
				ReviewMusicService::get_all_review_by_view();

				// コメントインフォメーションを削除
				ReviewMusicService::unset_comment_information();
			}
			else
			{
				// カウントを取得
				ReviewMusicService::get_all_review_by_view_count();
			}

			$review_music_dto = ReviewMusicDto::get_instance();
			$comment_dto      = CommentDto::get_instance();

			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'get music all',
				'result'   => array(
					'arr_list'         => $review_music_dto->get_arr_list(),
					'arr_comment_list' => $comment_dto->get_arr_list(),
					'comment_count'    => $comment_dto->get_count(),
					'count'            => $review_music_dto->get_count(),
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


	/**
	 * ユーザ関係なく指定のレビューを取得する
	 * （ユーザID付与時はコメントの既読フラグを更新する）
	 * @return boolean
	 */
	public function post_detail()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			# リクエストバリデーション
			ReviewMusicService::validation_for_detail();

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_detail();

			// レビューを取得
			ReviewMusicService::get_review_detail();

			// レビューへのコメントを取得
			ReviewMusicService::get_review_detail_comment();

			# 未読コメントを既読へ
			ReviewMusicService::set_unread_comment();

			$review_music_dto = ReviewMusicDto::get_instance();
			$comment_dto      = CommentDto::get_instance();

			$arr_response = array(
					'success'  => true,
					'code'     => '1001',
					'response' => 'get review detail',
					'result'   => array(
						'arr_detail'       => current($review_music_dto->get_arr_list()),
						'arr_comment_list' => $comment_dto->get_arr_list(),
						'comment_count'    => $comment_dto->get_count(),
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



	/**
	 * クール！(cool!)を受け付ける
	 *
	 * @return boolean
	 */
	public function post_sendcool()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			# リクエストバリデーション
			ReviewMusicService::validation_for_sendcool();

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_sendcool();

			# クールを登録
			ReviewMusicService::set_cool();

			$cool_dto = CoolDto::get_instance();

			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'get music all',
				'result'   => array(
					'reflection' => $cool_dto->get_reflection(),
					'cool_count' => $cool_dto->get_cool_count(),
				),
			);

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			$arr_response = array(
				'success'  => false,
				'code'     => $e->getCode(),
				'response' => $e->getMessage(),
				'result'   => null,
			);
			\Log::error($e->getFile(). '['. $e->getLine().']');
			\Log::error($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);
			$this->response($arr_response);

			return false;
		}
	}


	/**
	 * クール！(cool!)したユーザ情報を取得
	 *
	 * @return boolean
	 */
	public function post_getcool()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			# リクエストバリデーション
			ReviewMusicService::validation_for_getcool();

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_getcool();

			# クールを取得
			ReviewMusicService::get_cool_users();

			# 全件数を取得
			ReviewMusicService::get_cool_user_count();

			# 自分が投稿したクール済確認
			ReviewMusicService::is_cool_done();

			$cool_dto = CoolDto::get_instance();

			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'get cool user complate',
				'result'   => array(
					'arr_list' => $cool_dto->get_arr_list(),
					'all_count' => $cool_dto->get_all_count(),
					'is_done'  => $cool_dto->get_is_done(),
				),
			);

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			$arr_response = array(
					'success'  => false,
					'code'     => $e->getCode(),
					'response' => $e->getMessage(),
					'result'   => null,
			);
			\Log::error($e->getFile(). '['. $e->getLine().']');
			\Log::error($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);
			$this->response($arr_response);

			return false;
		}
	}


	/**
	 * ユーザコメントボックスを受け付ける
	 *
	 * @return boolean
	 */
	public function post_setusercomment()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			# リクエストバリデーション
			ReviewMusicService::validation_for_setusercomment();

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_setusercomment();

			# ユーザコメントを登録
			ReviewMusicService::set_usercomment();

			$user_comment_dto = UserCommentDto::get_instance();

			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'set user comment done',
				'result'   => array(
					'is_done' => true,
					'id'      => $user_comment_dto->get_user_comment_id(),
				),
			);

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			$arr_response = array(
				'success'  => false,
				'code'     => $e->getCode(),
				'response' => $e->getMessage(),
				'result'   => null,
			);
			\Log::error($e->getFile(). '['. $e->getLine().']');
			\Log::error($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);
			$this->response($arr_response);

			return false;
		}
	}


	/**
	 * ユーザコメントボックスの削除を受け付ける
	 *
	 * @return boolean
	 */
	public function post_removeusercomment()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			# リクエストバリデーション
			ReviewMusicService::validation_for_removeusercomment();

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_removeusercomment();

			# ユーザコメントを削除
			ReviewMusicService::remove_usercomment();

			$user_comment_dto = UserCommentDto::get_instance();

			$arr_response = array(
				'success'  => true,
				'code'     => '1001',
				'response' => 'remove user comment done',
				'result'   => array(
					'is_done' => true,
				),
			);

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			$arr_response = array(
					'success'  => false,
					'code'     => $e->getCode(),
					'response' => $e->getMessage(),
					'result'   => null,
			);
			\Log::error($e->getFile(). '['. $e->getLine().']');
			\Log::error($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);
			$this->response($arr_response);

			return false;
		}
	}


	/**
	 * レビューコメント受け付ける
	 *
	 * @return boolean
	 */
	public function post_sendcomment()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			# リクエストバリデーション
			ReviewMusicService::validation_for_sendcomment();

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_sendcomment();

			# ユーザコメントを登録
			ReviewMusicService::set_comment();

			# コメント件数を取得
			ReviewMusicService::get_comment_count();

			# コメント一覧を取得
			ReviewMusicService::get_comment();

			$comment_dto = CommentDto::get_instance();

			$arr_response = array(
					'success'  => true,
					'code'     => '1001',
					'response' => 'set user comment done',
					'result'   => array(
							'is_done'    => true,
							'comment_id' => $comment_dto->get_comment_id(),
							'count'      => $comment_dto->get_count(),
							'arr_list'   => $comment_dto->get_arr_list(),
					),
			);

			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			$arr_response = array(
					'success'  => false,
					'code'     => $e->getCode(),
					'response' => $e->getMessage(),
					'result'   => null,
			);
			\Log::error($e->getFile(). '['. $e->getLine().']');
			\Log::error($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);
			$this->response($arr_response);

			return false;
		}
	}


	/**
	 * レビューコメントの削除を受け付ける
	 *
	 * @return boolean
	 */
	public function post_deletecomment()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			# リクエストバリデーション
			ReviewMusicService::validation_for_delete_comment();

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_delete_comment();

			# ユーザコメントを削除
			ReviewMusicService::delete_comment();

			# コメント数を取得
			ReviewMusicService::get_review_id_from_comment();
			ReviewMusicService::get_comment_count();

			$comment_dto = CommentDto::get_instance();

			$arr_response = array(
					'success'  => true,
					'code'     => '1001',
					'response' => 'delete comment done',
					'result'   => array(
							'is_done'    => true,
							'comment_id' => $comment_dto->get_comment_id(),
							'count'      => $comment_dto->get_count(),
					),
			);
			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			$arr_response = array(
					'success'  => false,
					'code'     => $e->getCode(),
					'response' => $e->getMessage(),
					'result'   => null,
			);
			\Log::error($e->getFile(). '['. $e->getLine().']');
			\Log::error($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);
			$this->response($arr_response);

			return false;
		}
	}


	/**
	 * コメントを取得する
	 *
	 * @return boolean
	 */
	public function post_getcomment()
	{
		try
		{
			\Log::debug('--------------------------------------');
			\Log::debug('[start]'. __METHOD__);

			# JSONリクエストを取得
			ReviewMusicService::get_json_request();

			# リクエストバリデーション
			ReviewMusicService::validation_for_getcomment();

			# DTOにリクエストをセット
			ReviewMusicService::set_dto_for_getcomment();

			# コメント一覧を取得
			ReviewMusicService::get_comment();

			# コメント件数を取得
			ReviewMusicService::get_comment_count();

			$comment_dto = CommentDto::get_instance();

			$arr_response = array(
					'success'  => true,
					'code'     => '1001',
					'response' => 'get comment done',
					'result'   => array(
						'review_id' => $comment_dto->get_review_id(),
						'count'     => $comment_dto->get_count(),
						'arr_list'  => $comment_dto->get_arr_list(),
					),
			);
			$this->response($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);

			return true;
		}
		catch (\Exception $e)
		{
			$arr_response = array(
					'success'  => false,
					'code'     => $e->getCode(),
					'response' => $e->getMessage(),
					'result'   => null,
			);
			\Log::error($e->getFile(). '['. $e->getLine().']');
			\Log::error($arr_response);
			\Log::debug('[end]'. PHP_EOL. PHP_EOL);
			$this->response($arr_response);

			return false;
		}
	}

}