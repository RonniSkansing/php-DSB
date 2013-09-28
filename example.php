<?php
// PSR-0 Autoload
require 'vendor/autoload.php';

// Create the SoapClient Object.
$SoapClient = new SoapClient(\DSBPHP\App\SoapWrap::WDSL_URL);

// Create SoapWrap Object.
$DSB = new \DSBPHP\App\SoapWrap($SoapClient);

// Create a PropertyFilter.
$StationPropertyFilter = new \DSBPHP\Filters\PropertyFilter(['Name' => ['Fredericia','Odense']]);

// Get all stations
#$stations = $DSB->getStations(); // gets all stations
#var_dump($stations);

// Get all stations and filter by PropertyFilter
$stations = $DSB->getStations($StationPropertyFilter);
#var_dump($stations);

// Get All queues that belongs to the stations in the $station array.
$queues = $DSB->getQueuesByStations($stations);
var_dump($queues);

// Get all trains belonging to $staions array, not queues.
#$trains = $DSB->getTrainsByStations($stations);
#var_dump($trains);

// Create a PropertyFilter for Train value object.
#$TrainPropertyFilter = new \DSBPHP\Filters\PropertyFilter(['TrainNumber' => ['2670']]);

// Get all trains and return according to filter.
#$train = $DSB->getTrainsByStations($stations, $TrainPropertyFilter);