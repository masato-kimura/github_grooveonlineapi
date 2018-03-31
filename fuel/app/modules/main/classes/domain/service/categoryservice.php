<?php
namespace main\domain\service;

class CategoryService extends Service
{
	public static function validation_for_get()
	{
		\Log::debug('[start]'. __METHOD__);

		# バリデートで使用するため obj_requestの値を$_POSTにセットする
		static::_set_request_to_post(static::$_obj_request);

		# オブジェクト化
		$obj_validate = Validation::forge();

		# API共通バリデート設定
		static::_validate_base($obj_validate);

		# バリデート実行
		static::_validate_run($obj_validate);

		return true;
	}


	public static function set_dto_for_get()
	{
		\Log::debug('[start]'. __METHOD__);

		return true;
	}


	public static function get_all_names()
	{
		\Log::debug('[start]'. __METHOD__);

		$obj_cateogy = new \main\model\dao\CategoryDao();
		return $obj_cateogy->get_all_names();
	}
}