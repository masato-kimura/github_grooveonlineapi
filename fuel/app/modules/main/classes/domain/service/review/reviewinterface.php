<?php
namespace main\domain\service\review;

interface ReviewInterface
{
	public static function get_all_user_review();

	public static function get_review_detail();

	public static function get_top_list();
}