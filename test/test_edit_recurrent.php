<?php

/**
* This scripts tests creating cronjobs
*/

require '../phedorabot.php';
require 'test_config.php';

$request = PhedorabotClient::initializeRequest(
Config::API_KEY, Config::API_SECRET);

// we are debuging
$request->withDebug();
// Create a cronjob scheduler client
$client = new PhedorabotRecurrentSchedulerAPIClient();
// set the request path
$client->setRequestURI('/recurrent/task/edit');
$client->setJobID('145406991714980500');
// $client->setTaskName('new cronjob task name');
// $client->setTaskDescription('some that runs at a given time');
// set the cronjob macros we want to valid and verify
$client->setTimeUnit('month');
$client->setPeriodLength(3);

// send the request
try{

  $response = $request->send($client);
  // do we have an error
  if($response->isError()){
    echo $response->getError();
  }else{
    // we have a response
    echo json_encode($response->getRawData());
  }

}catch(PhedorabotAPIError $ex){
  echo $ex->getMessage();
}
echo "\n\nDone\n\n";
