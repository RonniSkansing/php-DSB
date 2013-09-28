<?php
namespace DSBPHP\Filters;


class BaseFilter implements FilterInterface
{
	protected $filters;

	public function __construct()
	{
		// throws BadArgumentException if not array.
		$this->validateFilterAsArray();
	}


	/**
	*	Throws \InvalidArgumentException if $this->filters is not an array
	*/
	private function validateFilterAsArray()
	{
		
		if(is_array($this->filters) === false)
			throw new \InvalidArgumentException();
	}


	/**
	*	Filters the array of value objects.
	*
	* 	Takes an array of value objects. Unsets an object each time it does not match with
	* 	set filters. 
	*	@param array $object_arr Array of value objects.
	*/
	public function run($object_arr)
	{
		foreach($object_arr as $key => $Object) 
			foreach($this->filters as $f_key => $values)
			{

				if( isset($Object->$f_key) == false )
					throw new \UnexpectedValueException(	'Object from array argument does not have a ' 
														. $f_key . ' property');

				if( in_array( $Object->$f_key , $values ))
					break;
				else
					unset($object_arr[$key]);
			}

		return $object_arr;
	}
}