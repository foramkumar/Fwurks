<?php

abstract class ActiveRecord
{
	/**
	 * Database Instance
	 * 
	 * @var Database
	 * @access protected
	 */
	protected static $db;
	
	/**
	 * Session Instance
	 * 
	 * @var Session
	 * @access protected
	 */
	protected static $sess;
	
	/**
	 * The name of the table active record is working with.
	 * Initialized in the constructor as the class name lower cased, undure_scored and pluralized
	 * 
	 * @var string
	 * @access public
	 */
	public $table_name 				= null;
	
	/**
	 * Primary key name
	 * Default is 'id'
	 * 
	 * @var string
	 * @access public
	 */
	public $primary_key 			= 'id';
	
	/**
	 * Error stack mainly filled after the validation process
	 * 
	 * @var array
	 * @access public
	 */
	public $errors					= array();
	
	/**
	 * Are the records are mirrored or no, if null it will be set to the value in the Application Config
	 * 
	 * @var bool|null
	 * @access protected
	 */
	protected $has_mirror 			= null;
	
	/**
	 * Is there an internationalization table, if null it will be set to the value in the Application Config
	 * 
	 * @var bool|null
	 * @access protected
	 */
	protected $has_i18n 			= null;

	/**
	 * Suffix used for the internationalization table name
	 * 
	 * @var string
	 * @access protected
	 */
	protected $i18n_suffix 			= 'i18n';
	
	/**
	 * Internationalization table name, generated by the table name and the suffix
	 * 
	 * @var string
	 * @access protected
	 */
	protected $i18n_table;
	
	/**
	 * Current locale
	 * 
	 * @var string
	 * @access protected
	 * 
	 * @example 'ln_LN'
	 */
	protected $i18n_locale;
	
	/**
	 * The code of the current locale
	 * 
	 * @var string
	 * @access protected
	 * 
	 * @example 'ln'
	 */
	protected $i18n_locale_code;
	
	/**
	 * Default locale
	 * 
	 * @var string
	 * @access protected
	 * 
	 * @example 'ln_LN'
	 */
	protected $i18n_default_locale;
	
	/**
	 * The code of the current locale
	 * 
	 * @var string
	 * @access protected
	 * 
	 * @example 'ln'
	 */
	protected $i18n_default_locale_code;
	
	/**
	 * All the locales
	 * 
	 * @var array
	 * @access protected
	 * 
	 * @example array('ln' => 'ln_LN')
	 */
	protected $i18n_locales;
	
	/**
	 * Name of the internationalization table foreign key linking to the main table
	 * 
	 * @var string
	 * @access protected
	 */
	protected $i18n_fk_field 		= 'i18n_foreign_key';
	
	/**
	 * Name of the internationalization table locale column
	 * 
	 * @var string
	 * @access protected
	 */
	protected $i18n_locale_field 	= 'i18n_locale';
	
	/**
	 * All table columns
	 * 
	 * @var array
	 * @access protected
	 */
	protected $columns 				= array();
	
	/**
	 * All table columns information.
	 * For easy of use the keys are the same as the column's names.
	 * 
	 * @var array
	 * @access protected
	 */
	protected $columns_info 		= array();
	
	
	public $select_what 			= array();
	
	
	public $select_type				= 'add'; // only
	
	/**
	 * All i18n table columns
	 * 
	 * @var array
	 * @access private
	 */
	private $i18n_columns 			= array();
	
	/**
	 * All i18n table columns information.
	 * For easy of use the keys are the same as the column's names.
	 * 
	 * @var array
	 * @access private
	 */
	private $i18n_columns_info 		= array();
	

	/**
	 * Relation: one from other table to many from this table
	 * 
	 * @var array
	 * @access protected
	 */
	protected $belongs_to 			= array();
	
	/**
	 * Relation: one from this table to one from other table
	 * 
	 * @var array
	 * @access protected
	 */
	protected $has_one 				= array();
	
	/**
	 * Relation: one from this table to many from other table
	 * 
	 * @var array
	 * @access protected
	 */
	protected $has_many 			= array();
	
	/**
	 * Relation: many from this table to many from other table
	 * 
	 * @var array
	 * @access protected
	 */
	protected $has_many_through		= array();
	
	/**
	 * Result class name
	 * 
	 * @var string
	 * @access protected
	 */
	protected $result_class 		= 'ActiveRecordResult';
	
	/**
	 * Parameter to be sent to the result class
	 * 
	 * @var array
	 * @access protected
	 * 
	 * @example array('var_name' => 'var_value')
	 */
	protected $result_params_add 	= array();
	
	/**
	 * Fields not to be validated or skip the whole validation process if false.
	 * 
	 * @var array|false
	 * @access protected
	 * 
	 * @example array('field');
	 */
	protected $dont_validate_fields	= array();
	
	
	/**
	 * Fields not to be htmlspecialchar-ed or skip the whole escaping if false.
	 * 
	 * @var array|false
	 * @access protected
	 * 
	 * @example array('field');
	 */
	protected $dont_escape_fields	= array();
	
	/**
	 * Determines if this record is a new one thus choosing insert or update
	 * 
	 * @var bool
	 * @access private
	 */
	private $new_record 			= true;
	
	/**
	 * Stores all the data
	 * 
	 * @var ActiveRecordResult
	 */
	protected $storage;
	
	/**
	 * Clone of the storage before updating attributes
	 * 
	 * @var ActiveRecordResult
	 */
	protected $old_storage;
	
	
	/**
	 * Constructor, calls init() in the end.
	 */
	public function __construct($id = null)
	{
		self::$db = Registry()->db;
		self::$sess = Registry()->session;
		$this->i18n_locale = Registry()->i18n_locale;
		$this->i18n_locale_code = Registry()->locale;
		$this->table_name = $this->table_name ? $this->table_name : Inflector()->tableize(get_class($this));
		
		$this->has_i18n = $this->has_i18n !== null ? $this->has_i18n : Application_Config::$has_i18n;
		if($this->has_i18n)
		{
			$this->i18n_table = $this->table_name.'_'.$this->i18n_suffix;
			$this->has_mirror = $this->has_mirror !== null ? $this->has_mirror : Application_Config::$has_mirror;
			if($this->has_mirror){ foreach (Registry()->locales as $code => $l){ $this->i18n_locales[$code] = $l['i18n']; } }
		}
		
		$this->i18n_default_locale_code = Application_Config::$defaultLocale;
		$this->i18n_default_locale = $this->i18n_locales[$this->i18n_default_locale_code];
		
		$this->load_columns();
		
		if($id){ $this->load($id); }
		
		settype($this->storage, 'object');
		
		$this->init();
	}
	
	/**
	 * Event called after the constructor
	 * 
	 * @access protected
	 */
	protected function init(){}
	

	// ------------------------------------
   	// TO BE COMMENTED SOOOOOOOOOOOOOOOOOON
	// vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
	
	final public function __get($var_name)
	{
		if( $this->storage && array_key_exists($var_name, get_object_vars($this->storage)) )
		{
			return $this->storage->$var_name;
		}
	}
	
	final public function __set($var_name, $value)
	{
		/*
		$attributes = array_merge(($objvars = get_object_vars($this->storage)) ? $objvars : array(), array_merge($this->columns_info, $this->i18n_columns_info));
		if( array_key_exists($var_name, $attributes) )
		{
		*/
			return $this->storage->$var_name = $value;
		/*
		}
		else
		{	
			return $this->$var_name = $value;
		}
		*/
	}
	
	final public function __call($method, $params)
	{
		if( $this->storage->{$this->primary_key} && in_array($method, array_merge(array('holder', 'belonging', 'belongings', 'through', 'clear_cache', 'turn_cache'), get_class_methods($this->storage))) )
		{
			return call_user_func_array(array($this->storage, $method), $params);
		}
		else if(preg_match('/(find_by|find_all_by|find_or_create_by)_(\w+)/', $method, $array))
		{
			return $this->find_by($array[1], $array[2], $params);
		}
	}
	
	final private function find_by($method, $fields, $params)
	{
		$method = substr($method, 0, strlen($method)-3);
		
		$fields = explode("|", str_replace(array('_and_', '_or_'), array('|AND|', '|OR|'), $fields));
		
		$number_params = ceil(count($fields) / 2);
		
		if(is_array($params[$number_params])){ $options = $params[$number_params]; }
		else
		{
			$params[$number_params] 	&& $options['order'] = $params[$number_params];
			$params[$number_params+1] 	&& $options['limit'] = $params[$number_params+1];
			$params[$number_params+2] 	&& $options['joins'] = $params[$number_params+2];
		}
		
		$order = $options['order'] ? $options['order'] : null;
		$limit = $options['limit'] ? $options['limit'] : null;
		$joins = $options['joins'] ? $options['joins'] : null;
		
		$conditions = '';
		
		for($i=0, $len = count($fields); $i<$len; $i++)
		{
			$field = $fields[$i];
			$conditions .= ($joins && in_array($field, $this->columns) ? $this->table_name.'.' : '').str_replace('__', '.', $field).' ';
			if(!($i&1))
			{
				$value = array_shift($params);
				$conditions .= is_array($value) ? 'IN ('.implode(',', $value).') ' : '= '.self::$db->qstr($value).' ';
				$create_fields[$field] = $value;
			}
		}
		
		$conditions = $options['conditions'] ? "(".$options['conditions'].") AND (".$conditions.")" : $conditions;
		
		return $this->$method($conditions, $order, $limit, $joins, $create_fields);
	}
	
	final public function find_or_create($conditions = null, $order = null, $limit = null, $joins = null, array $create_fields = array())
	{
		if(is_object($found = $this->find($conditions, $order, $limit, $joins)))
		{
			return $found;
		}
		else if($create_fields)
		{
			foreach($create_fields as $field => $value) {
				$this->storage->$field = $value;
			}
			$this->save();
			return $this->find($conditions, $order, $limit, $joins);
		}
	}
	
	final public function find_all($conditions = null, $order = null, $limit = null, $joins = null)
	{
		if(substr($conditions, 0, 6) == "SELECT" || substr($conditions, 0, 7) == "(SELECT")
		{
			$sql = $conditions;
		}
		else
		{
			if($this->has_i18n)
			{
				$joins_what = ", {$this->i18n_table}.*";
				$joins_tables = " LEFT JOIN {$this->i18n_table} ON {$this->table_name}.{$this->primary_key} = {$this->i18n_table}.{$this->i18n_fk_field} AND {$this->i18n_table}.{$this->i18n_locale_field} = '{$this->i18n_locale}' ";
			}
			
			if(is_array($joins) && is_array($joins[0]))
			{
				foreach($joins as $j)
				{
					$join_model = $j['table'] ? $this : new $j['model']();
					
					if(!$j['on']){ $j['on'] = " {$this->table_name}.".Inflector()->singularize($join_model->table_name)."_{$join_model->primary_key} "; }
					
					$joins_paramas = $join_model->build_joins($j);
					
					$joins_what 	.= $joins_paramas['what'] ? ','.$joins_paramas['what'].' ' : ''; 
					$joins_tables 	.= $joins_paramas['tables'].' '; 
				}
			}
			
			
			foreach($this->select_what as &$what)
			{
				$what = str_replace('$table', $this->table_name, $what);
			}
			
			switch($this->select_type)
			{
				case 'add':
					$what = "{$this->table_name}.* ".($this->select_what ? ', '.implode(', ', $this->select_what) : '');
				break;
				
				case 'only':
					$what = implode(', ', $this->select_what);
				break;
				
				default: 		break;
			}
			
						
			$sql  = "SELECT $what $joins_what FROM {$this->table_name} $joins_tables ";
			if ($conditions)	{ $sql .= " WHERE $conditions "; }
			if ($order)			{ $sql .= " ORDER BY $order "; }
			if ($limit)			{ $sql .= " LIMIT $limit "; }
		}
		
		$result_params[] = array_merge($this->result_params_add, array
		(
			'belongs_to' 		=> $this->belongs_to,
			'has_one' 			=> $this->has_one,
			'has_many' 			=> $this->has_many,
			'has_many_through' 	=> $this->has_many_through,
			
			'model_name' 		=> strtolower(get_class($this)),
			'primary_key' 		=> $this->primary_key,
		));
		
		$result = self::$db->fetch( self::$db->query($sql), false, $this->result_class, $result_params );
		
		$this->after_find($result);
		
		return $result;
	}
	
	final public function find($id, $order = null, $limit = null, $joins = null)
	{
		$conditions = $this->do_find_id($id);
		$function = strstr($conditions, ' IN (') ? 'find_all' : 'find_first';
		
		$order = $order ? $order : null;
		$limit = $limit ? $limit : null;
		$joins = $joins ? $joins : null;
		
		return (object)$this->$function($conditions, $order, $limit, $joins);
	}
	
	final public function find_first($conditions = null, $order = null, $limit = 1, $joins = null)
	{
		return current($this->find_all($conditions, $order, $limit, $joins));
	}
	
	final public function load($id)
	{
		$this->before_load();
		
		if($this->storage = $this->find($id))
		{
			if($this->has_mirror)
			{
				foreach ($this->i18n_columns as $c)
				{
					if(!in_array($c, array($this->i18n_fk_field, $this->i18n_locale_field)))
					{
						$this->storage->i18n_locales_storage->{$c}[$this->i18n_locale_code] = $this->storage->$c;
						unset($this->storage->$c);
					}
				}
				
				$current_locale = $this->i18n_locale;
				
				foreach($this->i18n_locales as $lcode => $l)
				{
					if($l == $current_locale){ continue; }
					
					$this->i18n_locale = $l;
					$t_storage = $this->find($id);
					
					foreach ($this->i18n_columns as $c)
					{
						if(!in_array($c, array($this->i18n_fk_field, $this->i18n_locale_field)))
						{
							$this->storage->i18n_locales_storage->{$c}[$lcode] = $t_storage->$c;
							unset($this->storage->$c);
						}
					}
					unset($t_storage);
				}
				
				$this->i18n_locale = $current_locale;
			}
			
			$this->new_record = false;
			$this->after_load();
			return true;
		}
		return false;
	}
	
	final public function reload()
	{
		if($this->storage->id){ return $this->load($this->storage->id); }
		return false;
	}
	
	final public function update($id, array $attributes)
	{
		$conditions = $this->do_find_id($id);
		
		foreach (array_merge($this->columns, $this->i18n_columns) as $c)
		{
			isset($attributes[$c]) && $field_values[$c] = $attributes[$c];
		}
		
		if(!$field_values){ return 0; } 
		
		$i18n_add = '';
		if($this->has_i18n)
		{
			$i18n_add = " LEFT JOIN {$this->i18n_table} ON {$this->primary_key} = {$this->i18n_fk_field} AND {$this->i18n_locale_field} = '{$this->i18n_locale}' ";
		}
		
		return self::$db->update($this->table_name.$i18n_add, $field_values, $conditions);
	}
	
	final public function add(array $attributes)
	{
		foreach ($this->columns as $c)
		{
			isset($attributes[$c]) && $field_values[$c] = $attributes[$c];
		}
		
		$last_id = self::$db->insert($this->table_name, $field_values);
		
		if($this->has_i18n)
		{
			$locales = $this->has_mirror ? $this->i18n_locales : array($this->i18n_locale);
			
			foreach($locales as $lcode => $l)
			{
				$i18n_field_values = array();
				
				if($this->has_mirror || $l == $this->i18n_locale)
				{
					foreach ($this->i18n_columns as $c)
					{
						$i18n_field_values[$c] = $this->has_mirror ? $attributes['i18n_locales_storage'][$c][$lcode] : $attributes[$c];
					}	
				}
				
				$i18n_field_values[$this->i18n_fk_field] = $last_id;
				$i18n_field_values[$this->i18n_locale_field] = $l;
				
				self::$db->insert($this->i18n_table, ($i18n_field_values));
			}
		}
		
		return $last_id;
	}
	
	final public function delete($id = null, $find_before_delete = false)
	{
		$id || $id = $this->storage->id;
		
		if($conditions = $this->do_find_id($id))
		{
			$find_before_delete && $this->storage = $this->find($id);
			
			$this->before_delete($id);
			$return = $this->_delete($conditions);
			$this->after_delete($id);
			
			return $return;
		}
	}
	
	final protected function _delete($conditions)
	{
		if($this->has_i18n)
		{
			$table_i18n = ", {$this->i18n_table}";
			$conditions_i18n = "AND {$this->i18n_fk_field} = ".$this->primary_key;
		}
		return self::$db->query("DELETE {$this->table_name} $table_i18n FROM {$this->table_name} $table_i18n WHERE $conditions $conditions_i18n");
	}
	
	final public function save($attributes = null, $validate = true)
	{
		if($attributes){ $this->update_storage($attributes); };
		if(!$validate || $this->valid()){ return $this->add_or_update_record(); }
	}
	
	final private function add_or_update_record()
	{
        $this->before_save();
        if($this->new_record)
        {
            $this->before_create();
            $result = $this->add_record();
            $this->after_create();
        }
        else
        {
            $this->before_update();
            $result = $this->update_record();
            $this->after_update();
        }
        $this->after_save();
        
        return $result;
    }
    
	final private function add_record()
	{
		$field_values = array($this->primary_key => null);
		foreach ($this->columns as $c)
		{
			isset($this->storage->$c) && $field_values[$c] = $this->storage->$c;
		}
		
		$last_id = self::$db->insert($this->table_name, $field_values);
		
		if($this->has_i18n)
		{
			$locales = $this->has_mirror ? $this->i18n_locales : array($this->i18n_locale);
			
			foreach($locales as $lcode => $l)
			{
				$i18n_field_values = array();
				
				if($this->has_mirror || $l == $this->i18n_locale)
				{
					foreach ($this->i18n_columns as $c)
					{
						$i18n_field_values[$c] = $this->has_mirror ? $this->storage->i18n_locales_storage->{$c}[$lcode] : $this->storage->$c;
					}	
				}
				
				$i18n_field_values[$this->i18n_fk_field] = $last_id;
				$i18n_field_values[$this->i18n_locale_field] = $l;
				
				self::$db->insert($this->i18n_table, ($i18n_field_values));
			}
		}
		
		$this->load($last_id);
		
		return $last_id;
	}
	
	final private function update_record()
	{
		foreach (array_merge($this->columns, $this->i18n_columns) as $c)
		{
			if ( !in_array($c, array($this->primary_key, $this->i18n_fk_field, $this->i18n_locale_field)) )
			{
				if($this->has_mirror)
				{
					foreach($this->i18n_locales as $lcode => $l)
					{
						$field_values[$l][$c] = in_array($c, $this->i18n_columns) ? $this->storage->i18n_locales_storage->{$c}[$lcode] : $this->storage->$c;
						if($field_values[$l][$c] === null){ unset($field_values[$l][$c]); }
					}
				}
				else
				{
					$field_values[$this->i18n_locale][$c] = $this->storage->$c;
					if(!$field_values[$this->i18n_locale][$c]){ unset($field_values[$c]); }
				}
			}
		}
		
		$locale = $this->has_mirror ? $this->i18n_locales : array($this->i18n_locale);
		foreach($locale as $l)
		{
			$i18n_add = '';
			if($this->has_i18n)
			{
				$i18n_add = " LEFT JOIN {$this->i18n_table} ON {$this->primary_key} = {$this->i18n_fk_field} AND {$this->i18n_locale_field} = '$l' ";
			}
			$return = self::$db->update($this->table_name.$i18n_add, $field_values[$l], $this->primary_key.'='. self::$db->qstr($this->{$this->primary_key}));
		}
		return $this->{$this->primary_key};
	}
	
	final private function update_storage($attributes)
	{
		$this->old_storage = clone $this->storage;
		$this->before_update_storage($attributes);
		
		$cols = array_merge($this->columns_info, $this->i18n_columns_info);
		
		foreach (array_merge($this->columns, $this->i18n_columns) as $c)
		{
			if( !in_array($c, array($this->primary_key, $this->i18n_fk_field, $this->i18n_locale_field)) )
			{
				
				if($this->has_mirror && in_array($c, $this->i18n_columns))
				{
					$this->storage->i18n_locales_storage->$c = $attributes['i18n_locales_storage'][$c];
				}
				else if(isset($attributes[$c]) || ($cols[$c]['real_type'] == 'tinyint' && $cols[$c]['max_length'] == 1))
				{
					if($cols[$c]['real_type'] == 'tinyint' && $cols[$c]['max_length'] == 1){ $attributes[$c] = $attributes[$c] ? 1 : 0; }
					$this->storage->$c = $attributes[$c];
					
					if($attributes[$c.'_confirm'])$this->storage->{$c.'_confirm'} = $attributes[$c.'_confirm'];
				}
			}
			
			if($this->has_i18n)
			{
				$this->storage->{$this->i18n_fk_field} = $this->storage->{$this->primary_key};
				$this->storage->{$this->i18n_locale_field} = $this->i18n_locale;
			}
		}
		
		
		$this->after_update_storage($attributes);
	}
	
	
	final private function do_find_id($id)
	{
		if(is_array($id) && count($id))
		{
			foreach ($id as &$i){ $i = self::$db->qstr($i); }
			$pk_values = implode(",", $id);
			
			$conditions = "{$this->primary_key} IN ($pk_values)";
		}
		else if(strstr($id, '='))
		{
			$conditions = $id;
		}
		else
		{
			$conditions = $this->primary_key.'='.self::$db->qstr($id);
		}
		
		return $conditions;
	}
	
	// validation
	final private function valid()
	{
		$this->errors = array();
		
		$this->before_validation();
		$this->new_record ? $this->before_validation_on_create() : $this->before_validation_on_update();
		$this->validate();
		$this->validate_fields();
		$this->after_validation();
		$this->new_record ? $this->after_validation_on_create() : $this->after_validation_on_update();
		
		return !$this->errors;
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param object $attributes
	 * @return array errors
	 */
	final public function validate(&$attributes = null)
	{
		$storage = $attributes ? $attributes : $this->storage;
		
		if($this->dont_validate_fields === false){ return; }
		
		$fields = $this->has_i18n ? array_merge($this->columns_info, $this->i18n_columns_info) : $this->columns_info;
		
		foreach( $fields AS $field_name => $field )
    	{
	    	if ( in_array($field_name, array_merge(array($this->primary_key, $this->i18n_fk_field, $this->i18n_locale_field), $this->dont_validate_fields)) ){ continue; }
    		
    		if($this->has_i18n && $this->has_mirror && in_array($field_name, $this->i18n_columns))
    		{
    			foreach($this->i18n_locales as $lcode => $l)
    			{
    				$field_value = $storage->i18n_locales_storage->{$field_name}[$lcode];
	    			$field_value_confirm = $storage->i18n_locales_storage->{$field_name.'_confirm'}[$lcode];
	    			
	    			$this->validate_actions($field_name, $field, $field_value, $field_value_confirm, $lcode);
		    		
		    		if(in_array($field['real_type'], array('varchar', 'char')) && (!in_array($field_name, $this->dont_escape_fields) || $this->dont_escape_fields === false))
		    		{
		    			 $storage->i18n_locales_storage->{$field_name}[$lcode] = htmlspecialchars(html_entity_decode($storage->i18n_locales_storage->{$field_name}[$lcode]));
		    		}
    			}
    		}
    		else
    		{
    			$field_value = $storage->$field_name;
	    		$field_value_confirm = $storage->{$field_name.'_confirm'};
	    		
	    		$this->validate_actions($field_name, $field, $field_value, $field_value_confirm);
	    		
	    		if(in_array($field['real_type'], array('varchar', 'char')) && (!in_array($field_name, $this->dont_escape_fields) || $this->dont_escape_fields === false))
	    		{
	    			 $storage->$field_name = htmlspecialchars(html_entity_decode($storage->$field_name));
	    		}
    		}
    	}
    	
    	if($attributes){ return $this->errors; }
	}
	
	final private function validate_actions($field_name, $field, $field_value, $field_value_confirm, $lang = null)
	{
		if(!$field['null'] && !strlen(trim(preg_replace('/(&nbsp;|<br[^>]*>)/ixm', '', $field_value))))
		{
			$this->add_error('cant_be_empty', $field_name, $lang);
		}
		else if($field['max_length'] > 0 && strlen($field_value) > $field['max_length'])
		{
			$this->add_error('too_long', $field_name, $lang);
		}
		else if($field_value_confirm && $field_value !== $field_value_confirm)
		{
			$this->add_error('not_match', $field_name, $lang);
			unset($field_value_confirm);
		}
		else if($field['unique']
			&& ($obj = $this->{'find_by_'.$field_name}($field_value))
			&& ($obj instanceof ActiveRecordResult || $obj instanceof ActiveRecord)
			&& $obj->{$this->primary_key} != $this->storage->{$this->primary_key}
		){
			$this->add_error('already_exists', $field_name, $lang);
		}
	}
	
	
	final private function validate_fields()
	{
		$attributes = array_merge(($objvars = get_object_vars($this->storage)) ? $objvars : array(), array_merge($this->columns_info, $this->i18n_columns_info));
		
		$reflection = new ReflectionClass(get_class($this));
		foreach($reflection->getMethods(ReflectionMethod::IS_PROTECTED) as $method)
		{
			$method = $method->getName();
			if( preg_match('/^validate_(.+)/', $method, $matches)
            	&& array_key_exists($matches[1], $attributes)
            	&& is_string($error_type = $this->$method($this->{$matches[1]}))
            ){
            	$this->add_error($error_type, $matches[1]);
            }
        }
    }

    
   	// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
   	// TO BE COMMENTED SOOOOOOOOOOOOOOOOOON
    // ------------------------------------
    
    /**
	 * ------------------------------------
     * ============== Events ==============
	 * ------------------------------------
     */
    
    /**
     * Event called before loading the data in the storage
     */
    protected function before_load(){}
    
    
    /**
     * Event called after loading the data in the storage
     */
    protected function after_load(){}
	
    
    /**
     * Event called after finding records
     * 
     * @param array $result found reccords
     */
    protected function after_find($result){}
    
    
    /**
     * Event called before updating the storage (in the beginnig of save)
     * 
     * @param array $attributes new storage data
     */
    protected function before_update_storage($attributes){}
	
    
   /**
     * Event called after updating the storage (in the beginnig of save)
     * 
     * @param array $attributes new storage data
     */
    protected function after_update_storage($attributes){}
	
	
    /**
     * Event called before the validation process
     */
	protected function before_validation(){}
	
	
	/**
     * Event called before the validation process but only when it is new record
     * and will be inserted after
     */
	protected function before_validation_on_create() {}
	
	
	/**
     * Event called before the validation process but only when it is old record
     * and will be updated after
     */
	protected function before_validation_on_update() {}
	
	
	// validate()
	// validate_fields()
	
	
	/**
     * Event called after the validation process
     */
	protected function after_validation(){}
	
	
	/**
     * Event called after the validation process but only when it is new record
     * and will be inserted after
     */
	protected function after_validation_on_create(){}
	
	
	/**
     * Event called after the validation process but only when it is old record
     * and will be updated after
     */
	protected function after_validation_on_update(){}
	
	
	/**
     * Event called before saving record
     */
	protected function before_save(){}
	
	
	/**
     * Event called after saving record
     */
	protected function after_save(){}
	
	
	/**
     * Event called before saving new record
     */
	protected function before_create(){}
	
	
	/**
     * Event called after saving new record
     */
	protected function after_create(){}
	
	
	/**
     * Event called before saving already existing record
     */
	protected function before_update(){}
	
	
	/**
     * Event called before saving already existing record
     */
	protected function after_update(){}
		
	
	/**
     * Event called before record
     */
	protected function before_delete($id){}
		
	
	/**
     * Event called after record
     */
	protected function after_delete($id){}
	
	
	/**
	 * ------------------------------------
	 * ============ Additional ============
	 * ------------------------------------
	 */
	
	
	/**
	 * Loads Columns info
	 */
	final private function load_columns()
	{
		
		$this->columns_info = self::$db->table_info($this->table_name);
		if($this->has_i18n)
		{
			$this->i18n_columns_info = self::$db->table_info($this->i18n_table);
			$this->i18n_columns = array_keys($this->i18n_columns_info);
		}
		
		$this->columns = array_keys($this->columns_info);
	}
	
	
	/**
	 * Adds error to the error stack
	 */
	final protected function add_error($type, $field, $lang = null, array $params = array())
	{
		$field_name = $lang ? "i18n_locales_storage[$field][$lang]" : $field;
		
		if(!$error = Registry()->globals['DATABASE_ERRORS'][$type])
		{
			$error = Registry()->globals['DATABASE_ERRORS']['unknown_error'].' ('.$type.')'; 
		}
		if($t_field = Registry()->globals['DATABASE_FIELDS'][$field]){ $field = $t_field; }
		
		$this->errors[$field_name] = array('field' => $field, 'message' => $error, 'language' => Registry()->locales[$lang]['language']);
	}
	 
	
	/**
	 * Sets i18n locale
	 *
	 * @param String $locale: 2 letters locale code
	 */
	final public function set_locale($locale)
	{
		if(array_key_exists($this->i18n_locales))
		{
			$this->i18n_locale = $this->i18n_locales[$locale];
			$this->i18n_locale_code = $locale;
		}
	}
	
	
	/**
	 * Columns info
	 * 
	 * @param null|1|2 type of columns to return. 
	 * 		null will return both table columns
	 * 		1 will return table columns
	 * 		2 will return 118n
	 * @return array columns info
	 */
	final public function get_columns_info($type = null)
	{
		switch($type)
		{
			case 1: 	return $this->columns_info;											break;
			case 2: 	return $this->i18n_columns_info;									break;
			default:	return array_merge($this->columns_info, $this->i18n_columns_info);	break;
		}
	}
	
	
	/**
	 * Return current record data
	 * 
	 * @access public
	 * @return ActiveRecordClass
	 */
	final public function get_storage()
	{
		return $this->storage;
	}
	
	
	/**
	 * Builds table joins
	 * 
	 * @access private
	 */
	final private function build_joins(array $params)
	{
		$table = $params['table'] ? $params['table'] : $this->table_name;
		$table_singular = $params['table'] ? $params['table'] : Inflector()->singularize($table);
		
		
		$params['prefix'] && $table = $params['prefix'].'_'.$table;
		
		$params['table_alias'] && $alias = $params['table_alias'];
		$params['table_alias'] || $alias = $params['prefix'] ? $params['prefix'].'_'.$table : $table;
		
		$params['force_index'] && $force_index = "FORCE INDEX({$params['force_index']})";
		
		$what = array();
		$params['what'] || $params['what'] = array();
		if(!$params['table'])
		{
			foreach ($this->columns as $c)
			{
				if($c != $this->primary_key){ $what[] = "$alias.{$c} AS {$table_singular}_{$c}"; }
			}
		}
		
		if(!strstr($params['on'], '=')){ $params['on'] = " {$params['on']} = {$alias}.{$this->primary_key} "; }
		$tables = "{$params['type']} JOIN {$table} as $alias $force_index ON {$params['on']} ";
		
		if($this->has_i18n)
		{
			foreach ($this->i18n_columns as $c)
			{
				if($c != $this->i18n_fk_field && $c != $this->i18n_locale_field){ $what[] = "{$table}_i18n.{$c} AS {$table_singular}_{$c}"; }
			}
			
			$tables .= "LEFT JOIN {$this->i18n_table} as {$alias}_i18n ON {$alias}.{$this->primary_key} = {$this->i18n_table}.{$this->i18n_fk_field} AND {$this->i18n_table}.{$this->i18n_locale_field} = '{$this->i18n_locale}'";
		}
		$what = array_merge($what, $params['what']);
		
		return array('what' => implode(', ', $what), 'tables' => $tables, 'no_model' => isset($params['table']));
	}
}

?>
