<?php

class ProptertyFilterTest extends PHPUnit_Framework_TestCase {

	protected $PropertyFilter;

	public function setUp()
	{

		$this->PropertyFilter = new \DSBPHP\Filters\PropertyFilter([]);
	}


	public function testConstructFirstArgMustBeArray()
	{

		$properties = [];
		$PropertyFilter = new \DSBPHP\Filters\PropertyFilter($properties);
	}


	/**
	* @expectedException PHPUnit_Framework_Error
	*/
	public function testConstructErrorIfFirstArgNotArray()
	{

		$properties = 'string';
		$PropertiesFilter = new \DSBPHP\Filters\PropertyFilter($properties);
	}


	public function testRunFirstArgIsArray()
	{

		$this->PropertyFilter->run([]);
	}


	public function testRunReturnsArray()
	{

		$arg = [ 'name' => ['Fredericia']];
		$result = $this->PropertyFilter->run($arg);
	}

	public function testRunFiltersResult()
	{

		$to_filter = [];
		$names = ['Jane', 'An', 'Peter', 'Jim', 'Lars', 'Paula'];
		$age = [10,20,30,15,25,5];

		for($i = 0; $i < 6; ++$i )
		{
			$Entry = new StdClass();
			$Entry->name = $names[$i];
			$Entry->age = $age[$i];
			$to_filter[] = $Entry;
		}

		$PropertyFilter = new \DSBPHP\Filters\PropertyFilter(['name' => ['Paula', 'Jim']]);
		$result_count = count( $PropertyFilter->run($to_filter) );

		$this->assertSame(2, $result_count);
	}
}