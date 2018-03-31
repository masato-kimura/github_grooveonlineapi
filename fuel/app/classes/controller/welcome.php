<?php
use Fuel\Core\DB;
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.6
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * The Welcome Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 *
 * @package  app
 * @extends  Controller
 */
class Controller_Welcome extends Controller
{

	/**
	 * The basic welcome message
	 *
	 * @access  public
	 * @return  Response
	 */
	public function action_index()
	{
/*
		$sql = '
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE ALGORITHM=UNDEFINED DEFINER=`LAA0191122`@`mysql021.phy.lolipop.lan` SQL SECURITY DEFINER VIEW `view_review_music` AS (select `ra`.`id` AS `id`,`ra`.`user_id` AS `user_id`,`ra`.`about` AS `about`,`ra`.`artist_id` AS `artist_id`,`ra`.`artist_name` AS `artist_name`,`ra`.`artist_id` AS `about_id`,`ra`.`artist_name` AS `about_name`,`ra`.`review` AS `review`,`ra`.`star` AS `star`,`ra`.`cool_count` AS `cool_count`,`ra`.`comment_count` AS `comment_count`,`ra`.`created_at` AS `created_at`,`ra`.`updated_at` AS `updated_at`,`a`.`image_small` AS `image_small`,`a`.`image_medium` AS `image_medium`,`a`.`image_large` AS `image_large`,`a`.`image_extralarge` AS `image_extralarge` from (`trn_review_music_artist` `ra` join `mst_artist` `a` on((`ra`.`artist_id` = `a`.`id`))) where (`ra`.`is_deleted` = 0)) union all (select `rl`.`id` AS `id`,`rl`.`user_id` AS `user_id`,`rl`.`about` AS `about`,`rl`.`artist_id` AS `artist_id`,`rl`.`artist_name` AS `artist_name`,`rl`.`album_id` AS `album_id`,`rl`.`album_name` AS `album_name`,`rl`.`review` AS `review`,`rl`.`star` AS `star`,`rl`.`cool_count` AS `cool_count`,`rl`.`comment_count` AS `comment_count`,`rl`.`created_at` AS `created_at`,`rl`.`updated_at` AS `updated_at`,`alb`.`image_small` AS `image_small`,`alb`.`image_medium` AS `image_medium`,`alb`.`image_large` AS `image_large`,`alb`.`image_extralarge` AS `image_extralarge` from (`trn_review_music_album` `rl` join `mst_album` `alb` on((`rl`.`album_id` = `alb`.`id`))) where (`rl`.`is_deleted` = 0)) union all (select `rt`.`id` AS `id`,`rt`.`user_id` AS `user_id`,`rt`.`about` AS `about`,`rt`.`artist_id` AS `artist_id`,`rt`.`artist_name` AS `artist_name`,`rt`.`track_id` AS `track_id`,`rt`.`track_name` AS `track_name`,`rt`.`review` AS `review`,`rt`.`star` AS `star`,`rt`.`cool_count` AS `cool_count`,`rt`.`comment_count` AS `comment_count`,`rt`.`created_at` AS `created_at`,`rt`.`updated_at` AS `updated_at`,`t`.`image_small` AS `image_small`,`t`.`image_medium` AS `image_medium`,`t`.`image_large` AS `image_large`,`t`.`image_extralarge` AS `image_extralarge` from (`trn_review_music_track` `rt` join `mst_track` `t` on((`rt`.`track_id` = `t`.`id`))) where (`rt`.`is_deleted` = 0));

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE ALGORITHM=UNDEFINED DEFINER=`LAA0191122`@`mysql021.phy.lolipop.lan` SQL SECURITY DEFINER VIEW `view_review_music_id` AS (select `trn_review_music_artist`.`id` AS `id`,`trn_review_music_artist`.`user_id` AS `user_id`,`trn_review_music_artist`.`review` AS `review` from `trn_review_music_artist` where (`trn_review_music_artist`.`is_deleted` = 0)) union all (select `trn_review_music_album`.`id` AS `id`,`trn_review_music_album`.`user_id` AS `user_id`,`trn_review_music_album`.`review` AS `review` from `trn_review_music_album` where (`trn_review_music_album`.`is_deleted` = 0)) union all (select `trn_review_music_track`.`id` AS `id`,`trn_review_music_track`.`user_id` AS `user_id`,`trn_review_music_track`.`review` AS `review` from `trn_review_music_track` where (`trn_review_music_track`.`is_deleted` = 0));

';

var_dump($sql);exit;
		$query = DB::query($sql);

		//var_dump($query->execute());
*/

		return Response::forge(View::forge('welcome/index'));
	}

	/**
	 * A typical "Hello, Bob!" type example.  This uses a ViewModel to
	 * show how to use them.
	 *
	 * @access  public
	 * @return  Response
	 */
	public function action_hello()
	{
		return Response::forge(ViewModel::forge('welcome/hello'));
	}

	/**
	 * The 404 action for the application.
	 *
	 * @access  public
	 * @return  Response
	 */
	public function action_404()
	{
		return Response::forge(ViewModel::forge('welcome/404'), 404);
	}
}
