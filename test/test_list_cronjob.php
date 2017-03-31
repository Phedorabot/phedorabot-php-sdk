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
$client = new PhedorabotCronjobSchedulerAPIClient();
// set the request path
$client->setRequestURI('/cron/task/list');
$client->setSubscriptionID('crj_324145629863172456');
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
