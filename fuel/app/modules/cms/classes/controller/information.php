<?php
namespace cms;

use main\model\dao\GlobalInformationDao;
use Fuel\Core\Pagination;
final class Controller_Information extends \Controller_Template
{
	public $template = 'template';

	public function before()
	{
		parent::before();
	}

	public function action_index()
	{
		\Log::debug('[start]'. __METHOD__);

		// 一覧取得
		$global_information_dao = new GlobalInformationDao();
		$pagination_config = array(
				'pagination_url' => \Config::get('host.api_url'). '/cms/information/index/',
				'total_items'    => $global_information_dao->search_one(array(), array(array(\DB::expr('count(id)'), 'cnt')))->cnt,
				'per_page'       => 10,
				'uri_segment'    => 'page',
		);
		$obj_pagination = Pagination::forge('pagination', $pagination_config);

		$this->template->content = \View::forge('information/index');
		$this->template->content->pagination = $obj_pagination;
		$this->template->content->arr_list   = $global_information_dao->search(array(), array(), array('id' => 'DESC'), null, 10, $obj_pagination->calculated_page);

		return \Response::forge($this->template);
	}

	public function action_create()
	{
		\Log::debug('[start]'. __METHOD__);

		if ( ! empty(\Input::param('comment')) and ! empty(\Input::param('date')))
		{
			$test = \Input::param('comment');

			$global_information_dao = new GlobalInformationDao();
			$global_information_dao->save(array(
					'date'    => \Input::param('date'),
					'comment' => \Input::param('comment'),
			));

			\Response::redirect(\Config::get('host.api_url'). '/cms/information/index/');
		}

		$this->template->content = \View::forge('information/create');

		return \Response::forge($this->template);
	}

	public function action_update($id)
	{
		\Log::debug('[start]'. __METHOD__);

		if (empty($id))
		{
			\Response::redirect(\Config::get('host.api_url'). '/cms/information/index/');
		}

		$global_information_dao = new GlobalInformationDao();

		if ( ! empty(\Input::param('comment')) and ! empty(\Input::param('date')))
		{
			$arr_where = array(
					'id' => $id,
			);
			$arr_values = array(
					'date' => \Input::post('date'),
					'comment' => \Input::post('comment'),
			);
			$global_information_dao->update($arr_values, $arr_where);

			\Response::redirect(\Config::get('host.api_url'). '/cms/information/index/');
		}
		$this->template->content = \View::forge('information/update');
		$this->template->content->arr_detail = $global_information_dao->search_one(array('id' => $id));

		\Response::forge($this->template);
	}

	public function action_delete($id)
	{
		\Log::debug('[start]'. __METHOD__);

		if (empty($id))
		{
			\Response::redirect(\Config::get('host.api_url'). '/cms/information/index/');
		}

		$global_information_dao = new GlobalInformationDao();
		$global_information_dao->delete(array(), array('id' => $id), false);

		\Response::redirect(\Config::get('host.api_url'). '/cms/information/index/');
	}
}