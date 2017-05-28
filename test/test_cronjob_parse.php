<?php

/**
* This scripts tests the cronjob parse api
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
$client->setRequestURI('/cron/script/parse');
// set the cronjob macros we want to valid and verify
$client->setCronMaros('30 11,16 * * *');

// set the cronjob service subscription id
$client->setSubscriptionID('crj_137142717460555707');

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
