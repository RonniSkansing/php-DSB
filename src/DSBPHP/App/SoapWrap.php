<?php 
namespace DSBPHP\App;


class SoapWrap {


	const WDSL_URL = 'http://traindata.dsb.dk/stationdeparture/Service.asmx?WSDL';

	/**
	*	The WDSL/SOAP Client
	*	@access private
	*	@var \SoapClient $Client
	*/
	private $Client; // SOAP client.
	/**
	*	Microseconds between each call when querying queues and trains.
	*	@access private
	*	@var int $soap_delay 
	*/
	private $soap_delay = 100000; 


	/**
	*	Sets up \SoapClient connection.
	* 	@throws \SoapFault upon SOAP/WDSL error.
	*/
	public function __construct( \SoapClient $SoapClient )
	{

		try 
		{

			$this->Client = $SoapClient;

		}
		catch( \SoapFault $SoapFault )
		{

			throw $SoapFault;
		}
	}


	/**
	*	Throws Exception if argument is not valid station data.
	*	@throws \InvalidArgumentException	If $stations is not a array
	*	@throws \OutOfBoundsException		If a $stations element does not have a property named UIC
	*/
	private function validateStationsAreValid( array $stations)
	{

		if( is_array($stations) !== true )
			throw new \InvalidArgumentException('Expected array. Given ' . gettype($stations) );

		foreach($stations as $Station)			
			if( isset($Station->UIC) === false OR is_numeric($Station->UIC) === false)
				throw new \OutOfBoundsException('Expected integer UIC property. Given ' . var_export($Station, true));
	}


	/**
	*	Gets \SoapClient connected to dsb-labs soapservice.
	*	@return \SoapClient
	*/
	public function getSoapClient()
	{

		return $this->Client;
	}


	/**
	*	Get the current delay between delayed SOAP calls 
	*	@return int soap delay in microseconds. 
	*/
	public function getSoapDelay()
	{

		return $this->soap_delay;
	}


	/**
	*	Set the current delay between delayed SOAP calls 
	*	@param int $microseconds
	*	@return Current SoapDelay in microseconds. 
	*/
	public function setSoapDelay($microseconds)
	{

		if(is_int($microseconds) !== false OR $microseconds <= 0)
			throw new \InvalidArgumentException('Delay must be a integer larger than 0');

		$this->soap_delay = $microseconds;
	}


	/** 
	* 	Gets all stations and filters if a filter is passed.
	*
	*	As DSB SOAP service only allows to get all stations 
	*	a filter can be used narrow down the results. 
	*	@param \DSBPHP\Filters\BaseFilter $StationFilte
	*	@return Array with station value objects.
	*/
	public function getStations( \DSBPHP\Filters\FilterInterface $StationFilter = null )
	{
		// DSB soap service inforces only method for getting all stations...

		$Result = $this->Client->GetStations();

		if ( isset( $Result->GetStationsResult->Station ) === false)
			return [];

		if($StationFilter !== null) 
			return $StationFilter->run($Result->GetStationsResult->Station);

		// return all trains
		return $Result->GetStationsResult->Station;
	}


	/** 
	*	Gets all trainqueue for specific station uid.
	*	Returns queue as an array of value objects.
	*	@param int $uid 	Uid for station to get the trainqueue
	*	@return array
	*	@throws Mixed 		\InvalidArgumentException / \SoapFault		
	*/
	public function getStationQueueByUIC( $uic )
	{

		try 
		{

			if( is_numeric($uic) === false )
				throw new \InvalidArgumentException('UID must be a positive integer');

			$trainQueue = $this->Client->getStationQueue( ['request' => ['UICNumber' => $uic ]]);

			if( is_object($trainQueue) === false)
				return [];

			return $trainQueue;
		} 
		catch(SoapFault $SoapFault) 
		{ 

			throw $SoapFault; 
		}
	}


	/** 
	*	Gets all trainqueues at supplied array of stations.
	*	Returns the same array as inputted but the station value objects has an added
	*	parameter named queue.
	*	NOTICE: This calls the soap service for each station, use $this->soap delay
	*			to avoid calling the SOAP service to fast.
	*	@param array $stations 	Array of station value objects.
	*	@param \DSBPHP\Filters\BaseFilter $QueueFilter 	
	*	@return Array same array of stations value objects, added with a queue property
	*/
	public function getQueuesByStations( 	Array 	$stations, 
											\DSBPHP\Filters\FilterInterface $QueueFilter = null)  
	{
		// throws exceptions upon failing.
		$this->validateStationsAreValid($stations);	

		$train_queues = [];
		foreach($stations as $key => $Station)			
		{

			try 
			{

				$StationsQueue = $this->getStationQueueByUIC( $Station->UIC );
			}
			catch( Exception $e )
			{

				throw $e;
			}

			// avoid too many calls too fast...	
			usleep($this->soap_delay); 

			// in lack of any train queue information
			if( isset($StationsQueue->GetStationQueueResult->Trains->Queue ) === FALSE)
			{
				$stations[$key]->queue = [];
				continue;
			}
				
			$queue = $StationsQueue->GetStationQueueResult->Trains->Queue;

			if($QueueFilter !== null)
				$stations[$key]->queue = $QueueFilter->run( $queue );
			else
				$stations[$key]->queue = $queue;
		}

		return $stations;
	}


	/** 
	*	Gets all Trains in queues at supplied array of stations.
	*
	*	Returns an array of the trains and not the queues.
	*	The DSB SOAP service only allows for getting train queue for a
	*	specific station.
	*	Useful when trying to locate if a specific train is on route.
	*	NOTICE: This calls the soap service for each station, use $soap delay
	*			to avoid calling the SOAP service to fast.
	*	@param array $stations 	Array of station value objects.
	*	@param \DSBPHP\Filters\BaseFilter $TrainFilter 	
	*	@return Array of train value objects.
	*/
	public function getTrainsByStations( 	array	$stations, 
										 	\DSBPHP\Filters\FilterInterface $TrainFilter = null)
	{
		// throws exceptions upon failing.
		$this->validateStationsAreValid($stations);

		$trains = [];

		foreach($stations as $Station)			
		{

			// Make a request for current station queue.
			try 
			{

				$StationsQueue = $this->getStationQueueByUIC( $Station->UIC );
			}
			catch( Exception $e )
			{

				throw $e;
			}

			// avoid too many calls too fast...	
			usleep($this->soap_delay); 

			// in lack of any train queue information
			if( isset($StationsQueue->GetStationQueueResult->Trains->Queue ) === FALSE)
				continue;

			$queue = $StationsQueue->GetStationQueueResult->Trains->Queue;

			if($TrainFilter !== null)
				$trains = array_merge( $trains, $TrainFilter->run( $queue ) );
			else
				$trains = array_merge( $trains, $queue );
		}

		return $trains;
	}
}