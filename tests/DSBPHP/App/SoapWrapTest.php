<?php
use Mockery as m;

class SoapWrapTest extends PHPUnit_Framework_TestCase {


	private $SoapWrap;


	public function tearDown()
	{

		m::close();
	}


	public function setUp()
	{

		$SoapClient = $this	->getMockBuilder('\SoapClient')
							->disableOriginalConstructor()
							->getMock();

		$this->SoapWrap = new \DSBPHP\App\SoapWrap($SoapClient);
	}


	/**
	* @expectedException PHPUnit_Framework_Error
	*/
	public function testConstructThrowsExceptionIfArgumentIsNotASoapClient()
	{

		$SoapWrap = new \DSBPHP\App\SoapWrap('NotValidArgument');
	}


	public function testSoapDelayDefaultTo100000MicroSeconds()
	{

		$delay = $this->SoapWrap->getSoapDelay();
		$this->assertEquals(100000, $delay);
	}


	/**
	* @expectedException InvalidArgumentException
	*/
	public function testThrowsExceptionWhenSoapDelayMustBeLowerThan1()
	{

		$this->SoapWrap->setSoapDelay(0);
	}


	/**
	* @expectedException InvalidArgumentException
	*/
	public function testThrowsExceptionIfSoapDelayNotSetToInt()
	{

		$this->SoapWrap->setSoapDelay('string');	
	}


	/**
	* @expectedException InvalidArgumentException
	*/
	public function testGetStationQueueByUicThrowsExceptionIfFirstArgNotInteger()
	{

		$this->SoapWrap->getStationQueueByUIC('string');
	}


	public function testGetStationsByUicReturnsArray()
	{

		$train = $this->SoapWrap->getStationQueueByUIC(1);
		$this->assertInternalType('array', $train);
	}


	/**
	* @expectedException PHPUnit_Framework_Error
	*/
	public function testGetStationsFirstArgumentMustBeInstaceOfFilterInterface()
	{		

		$this->SoapWrap->getStations(new DateTime());
	}


	public function testGetStationsReturnsArray()
	{

		$stations = $this->SoapWrap->getStations();
		$this->assertInternalType('array', $stations);
	}


	public function testGetQueuesByStationsFirstArgIsArray()
	{

		$stations = [];
		$trainQueues = $this->SoapWrap->getQueuesByStations($stations);
	}


	public function testGetQueuesByStationsSecondArgIsInstaceOfFilterInterface() 
	{

		$stations = [];
		$stub = $this	->getMockBuilder('\DSBPHP\Filters\PropertyFilter')
						->disableOriginalConstructor()
						->getMock();

		$this->SoapWrap->getQueuesByStations($stations, $stub);
	}


	public function testGetQueuesByStationsReturnsArray()
	{

		$stations = [];
		$trainQueues = $this->SoapWrap->getQueuesByStations($stations);
		$this->assertInternalType('array', $trainQueues);
	}


	public function testGetTrainsByStationsTakesArrayOfStations()
	{

		$stations = [];
		$this->SoapWrap->getTrainsByStations($stations);
	}


	public function testGetTrainsByStationsTakesSecondArgAsInstaceOfFilterInterface()
	{

		$stations = [];
		$stub = $this	->getMockBuilder('\DSBPHP\Filters\PropertyFilter')
						->disableOriginalConstructor()
						->getMock();

		$this->SoapWrap->getTrainsByStations($stations, $stub);
	}


	/**
	*	@expectedException OutOfBoundsException
	*/
	public function testGetTrainByStationsThrowExceptionIfInvalidArrayFormat()
	{

		$this->SoapWrap->getTrainsByStations(['zbyz']);
	}


	public function testGetTrainsByStationsReturnsArray()
	{

		$result = $this->SoapWrap->getStations();
		$this->assertInternalType('array', $result);
	}

}