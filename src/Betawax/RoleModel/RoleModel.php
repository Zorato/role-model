<?php namespace Betawax\RoleModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Validator;

class RoleModel extends Model {
	
	/**
	 * Validation rules.
	 *
	 * @var array
	 */
	public static $rules = array();
	
	/**
	 * Validation errors.
	 *
	 * @var Illuminate\Support\MessageBag
	 */
	protected $errors;
	
	/**
	 * Validator instance.
	 *
	 * @var Illuminate\Validation\Validator
	 */
	protected $validator;
	
	/**
	 * Force save.
	 *
	 * @var bool
	 */
	protected $force = false;
	
	/**
	 * Share the Validator instance.
	 *
	 * @param  array  $attributes
	 * @param  Illuminate\Validation\Validator  $validator
	 * @return void
	 */
	public function __construct(array $attributes = array(), Validator $validator = null)
	{
		parent::__construct($attributes);
		
		$this->validator = $validator ? $validator : \App::make('validator');
	}
	
	/**
	 * Register event bindings.
	 *
	 * @return void
	 */
	public static function boot()
	{
		parent::boot();
		
		static::saving(function($model)
		{
			if ( ! $model->isForced()) return $model->validate();
		});
	}
	
	/**
	 * Validate the model's attributes.
	 *
	 * @param  array  $rules
	 * @return bool
	 */
	public function validate(array $rules = array())
	{
		$rules = self::processRules($rules ? $rules : static::$rules);
		$validator = $this->validator->make($this->attributes, $rules);
		
		if ($validator->fails())
		{
			$this->errors = $validator->errors();
			return false;
		}
		
		$this->errors = null;
		return true;
	}
	
	/**
	 * Process validation rules.
	 *
	 * @param  array  $rules
	 * @return array  $rules
	 */
	protected function processRules(array $rules)
	{
		$id = $this->getKey();
		array_walk($rules, function(&$item) use ($id)
		{
			// Replace placeholders
			$item = stripos($item, ':id:') !== false ? str_ireplace(':id:', $id, $item) : $item;
		});
		
		return $rules;
	}
	
	/**
	 * Get validation errors.
	 *
	 * @return Illuminate\Support\MessageBag
	 */
	public function errors()
	{
		return $this->errors;
	}
	
	/**
	 * Check if the model has validation errors.
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
		return ! is_null($this->errors);
	}
	
	/**
	 * Save the model to the database.
	 *
	 * @param  array  $options
	 * @return bool
	 */
	public function save(array $options = array())
	{
		$this->force = false;
		return parent::save($options);
	}
	
	/**
	 * Force save the model to the database.
	 *
	 * @param  array  $options
	 * @return bool
	 */
	public function forceSave(array $options = array())
	{
		$this->force = true;
		return parent::save($options);
	}
	
	/**
	 * Returns true if not validation needed before save
	 * 
	 * @return bool
	 */
	public function isForced()
	{
		return $this->force;	
	}
	
}
