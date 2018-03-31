<?php
namespace main\model\dao;

use main\model\dto\CategoryDto;


/**
 * @throws \Exception
 *  1001 正常
 *  9001
 * @author masato
 *
 */
class CategoryDao extends \main\model\dao\MySqlDao
{
	protected $_table_name;

	/**
	 *
	 */
	public function __construct()
	{
		$this->_table_name = 'mst_category';
	}

	public function get_all_names()
	{
		$arr_where = array('is_enabled' => '1');
		$arr_columns = array('id', 'name');
		$arr_order = array('sort' => 'asc');
		$arr_result = $this->search($arr_where, $arr_columns, $arr_order);
		return $arr_result;
	}

	public function get_category_by_id()
	{
		$category_dto = CategoryDto::get_instance();

		$arr_where =array(
			'id' => $category_dto->get_id(),
			'is_deleted' => 0,
			'is_enabled' => 1,
		);

		return $this->search_one($arr_where, array(), array(), $category_dto);
	}
}