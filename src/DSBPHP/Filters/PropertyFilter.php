<?php 
namespace DSBPHP\Filters;


class PropertyFilter extends BaseFilter
{
	/**
	*	Filters an array of value objects against their public properties.
	*
	*	Takes an array of value objects as argument. Keys is property names. Values is value to filter against.
	*	Does not take multi keys, only values.
	*	Example. $StationNameFilter = new \DSBPHP\Filters\PropertyFilter( ['Name' => ['Fredericia', 'Odense']] )
	*	@param array $filter
	*	@throws \InvalidArgumentException		If the $filters is not an array
	*/
	public function __construct( Array $filters )
	{

		$this->filters = $filters;
	}	
}