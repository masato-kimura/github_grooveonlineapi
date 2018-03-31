<?php
namespace main\model\dao;

use Fuel\Core\Database_Connection;
class MySqlDao extends \Model
{
	protected $_table_name = null;

	public function __construct($_table_name=null)
	{
		$this->_table_name = $_table_name;
	}

	public function start_transaction()
	{
		$db = Database_Connection::instance();
		if ( ! $db->in_transaction())
		{
			return $db->start_transaction();
		}
	}

	public function commit_transaction()
	{
		$db = Database_Connection::instance();
		if ($db->in_transaction())
		{
			return $db->commit_transaction();
		}
	}

	public function rollback_transaction()
	{
		$db = Database_Connection::instance();
		if ($db->in_transaction())
		{
			return $db->rollback_transaction();
		}
	}

	public function in_transaction()
	{
		$db = Database_Connection::instance();
		return $db->in_transaction();
	}

	protected function search_limit(array $arr_where=array(), $arr_columns=array(), $arr_order=array(), $offset=0, $limit=null, $dto=null)
	{
		\Log::debug('[start]'. __CLASS__. '::'. __FUNCTION__);

		if (empty($arr_columns))
		{
			$query = \DB::select()->from($this->_table_name);
		}
		else
		{
			$query = \DB::select_array($arr_columns)->from($this->_table_name);
		}
		if ( ! empty($arr_where))
		{
			foreach ($arr_where as $key => $val)
			{
				if (empty($val))
				{
					continue;
				}
				if (is_array($val))
				{
					$val = array_filter($val, 'strlen');
					$query->where($key, 'in', $val);
				}
				else
				{
					$query->where($key, $val);
				}
			}
			$query->where('is_deleted', 0);
		}
		unset($key, $val);

		if ( ! empty($arr_order))
		{
			foreach ($arr_order as $key => $val)
			{
				$query->order_by($key, $val);
			}
		}

		if ($offset > 0)
		{
			$query->offset($offset);
		}

		if ( ! empty($limit))
		{
			$query->limit($limit);
		}

		if (empty($dto))
		{
			return $query->as_object()->execute()->as_array();
		}
		else
		{
			$return_dto = $query->as_object(get_class($dto))->execute()->as_array();

			if (empty($return_dto))
			{
				return array();
			}

			$arr_method = get_class_methods($dto);
			foreach ($arr_method as $method)
			{
				if (preg_match('/^set_(.+)/', $method, $match))
				{
					$get_method = 'get_'. $match[1];
					$str = $return_dto[0]->$get_method();
					$dto->$method($str);
				}
			}

			return $return_dto;
		}
	}


	public function search(array $arr_where=array(), $arr_columns=array(), $arr_order=array(), $dto=null, $limit=null, $page=null)
	{
		\Log::debug('[start]'. __METHOD__);

		if (empty($arr_columns))
		{
			$query = \DB::select()->from($this->_table_name);
		}
		else
		{
			$query = \DB::select_array($arr_columns)->from($this->_table_name);
		}
		if ( ! empty($arr_where))
		{
			foreach ($arr_where as $key => $val)
			{
				if (preg_match('/\s*([\!><=]+)$/', $key, $match))
				{
					$key = preg_replace('/\s*[\!><=]+$/', '', $key);
					$diff = $match[1];
					if ($diff == '!') $diff = '!=';
					$query->where(trim($key), $diff, $val);
				}
				else
				{
					$query->where(trim($key), '=', $val);
				}
			}
			$query->where('is_deleted', 0);
		}
		unset($key, $val);

		if ( ! empty($arr_order))
		{
			foreach ($arr_order as $key => $val)
			{
				$query->order_by($key, $val);
			}
		}

		if ( ! empty($limit))
		{
			$query->limit($limit);
		}

		if ( ! empty($page))
		{
			$offset = (int)($page - 1) * (int)$limit;
			$query->offset($offset);
		}

		if (empty($dto))
		{
			return $query->as_object()->execute()->as_array();
		}
		else
		{
			return $query->as_object(get_class($dto))->execute()->as_array();
		}
	}


	public function search_offset(array $arr_where=array(), $arr_columns=array(), $arr_order=array(), $offset=0, $limit=10)
	{
		\Log::debug('[start]'. __METHOD__);

		if (empty($arr_columns))
		{
			$query = \DB::select()->from($this->_table_name);
		}
		else
		{
			$query = \DB::select_array($arr_columns)->from($this->_table_name);
		}
		if ( ! empty($arr_where))
		{
			foreach ($arr_where as $key => $val)
			{
				if (preg_match('/\s*([\!><=]+)$/', $key, $match))
				{
					$key = preg_replace('/\s*[\!><=]+$/', '', $key);
					$diff = $match[1];
					if ($diff == '!') $diff = '!=';
					$query->where(trim($key), $diff, $val);
				}
				else
				{
					$query->where(trim($key), '=', $val);
				}
			}
			$query->where('is_deleted', 0);
		}
		unset($key, $val);

		if ( ! empty($arr_order))
		{
			foreach ($arr_order as $key => $val)
			{
				$query->order_by($key, $val);
			}
		}

		if ( ! empty($limit))
		{
			$query->limit($limit);
		}

		$query->offset($offset);

		return $query->as_object()->execute()->as_array();
	}



	/**
	 *
	 * @param array $arr_where
	 * @param unknown $arr_columns
	 * @param unknown $arr_order
	 * @param string $dto このDTOオブジェクトにセットされる
	 * @return multitype:|unknown
	 */
	public function search_one(array $arr_where=array(), $arr_columns=array(), $arr_order=array(), $dto=null)
	{
		\Log::debug('[start]'. __METHOD__);

		$arr_result = $this->search($arr_where, $arr_columns, $arr_order);
		if (empty($arr_result)) return array();

		$obj_result = $arr_result[0];

		if (isset($dto))
		{
			$this->_set_orginal_dto($obj_result, $dto);
		}
		return $obj_result;
	}


	private function _set_orginal_dto($obj_result, $dto)
	{
		\Log::debug('[start]'. __METHOD__);

		if ( ! is_object($obj_result))
		{
			return false;
		}

		foreach ($obj_result as $key => $val)
		{
			$method_name = 'set_'. $key;
			if (method_exists($dto, $method_name))
			{
				$dto->$method_name(trim($val));
			}
		}

		return true;
	}


	/**
	 *
	 * @param unknown $arr_params
	 * @return array('primary_key', 'count')
	 */
	public function save($arr_params, $is_ignore=false)
	{
		\Log::debug('[start]'. __METHOD__);

		if (empty($arr_params))
		{
			return false;
		}
		$arr_params['is_deleted'] = 0;
		$arr_params['created_at'] = \Date::forge()->format('%Y-%m-%d %H:%M:%S');
		$arr_params['updated_at'] = $arr_params['created_at'];
		if ($is_ignore === true)
		{
			$query = \DB::insert($this->_table_name);
			$query->set($arr_params);
			$sql = $query->compile();
			$sql = preg_replace('/^insert into/i', 'insert ignore into', $sql);
			$query = \DB::query($sql);
		}
		else
		{
			$query = \DB::insert($this->_table_name);
			$query->set($arr_params);
		}

		\Log::info($query->compile());

		return $query->execute();
	}

	public function save_multi($arr_values, $is_ignore=false)
	{
		if (empty($arr_values))
		{
			return false;
		}

		$datetime = \Date::forge()->format('%Y-%m-%d %H:%M:%S');

		$arr_columns = array_keys(current($arr_values));
		$arr_columns[] = 'is_deleted';
		$arr_columns[] = 'created_at';
		$arr_columns[] = 'updated_at';
		foreach ($arr_columns as $i => $val)
		{
			if ($val === 'id')
			{
				unset($arr_columns[$i]);
			}
		}
		unset($i, $val);

		$query = \DB::insert($this->_table_name);
		$query->columns($arr_columns);

		foreach ($arr_values as $i => $val)
		{
			unset($val['id']);
			$val['is_deleted'] = 0;
			$val['created_at'] = $datetime;
			$val['updated_at'] = $datetime;
			$query->values($val);
		}

		$sql = $query->compile();

		if ($is_ignore === true)
		{
			$sql = preg_replace('/^insert into/i', 'insert ignore into', $sql);
		}

		$query = \DB::query($sql);

		return $query->execute();
	}

	public function update($arr_values, $arr_where=array(), $use_logical_delete=true)
	{
		# 更新する値が存在しなければfalse
		if (empty($arr_values)) return false;

		if ( ! isset($arr_values['is_deleted']))
		{
			$arr_values['is_deleted'] = 0;
		}

		$arr_values['updated_at'] = \Date::forge()->format('%Y-%m-%d %H:%M:%S');
		$query = \DB::update($this->_table_name);
		# 更新対象値
		foreach ($arr_values as $key => $val)
		{
			$query->value($key, $val);
		}
		unset($key);
		unset($val);
		# 条件
		foreach ($arr_where as $key => $val)
		{
			$query->where($key, $val);
		}
		if ($use_logical_delete)
		{
			$query->where('is_deleted', '=', '0');
		}

		\Log::info($query->compile());

		$result = $query->execute();

		return $result;
	}


	/**
	 * $arr_valuesは現在使用しないが、削除理由など記録するときなどに使用するかも
	 * @param unknown $arr_values
	 * @param unknown $arr_where
	 * @return boolean
	 */
	public function delete($arr_values, $arr_where=array(), $use_logical_delete=true)
	{
		if ($use_logical_delete)
		{
			$query = \DB::update($this->_table_name);
			$arr_delete_values = array();
			$arr_delete_values['updated_at'] = \Date::forge()->format('%Y-%m-%d %H:%M:%S');
			$arr_delete_values['is_deleted'] = 1;
			$query->where('is_deleted', '=', 0);
			foreach ($arr_delete_values as $key => $val)
			{
				$query->value($key, $val);
			}
			unset($key);
			unset($val);

			foreach ($arr_where as $key => $val)
			{
				$query->where($key, $val);
			}

			return true;
		}
		else
		{
			$query = \DB::delete($this->_table_name);
			foreach ($arr_where as $key => $val)
			{
				$query->where($key, $val);
			}
		}

		\Log::info($query->compile());

		return $query->execute();
	}
}