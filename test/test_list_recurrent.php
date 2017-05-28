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
// Create a onetime scheduler client
$client = new PhedorabotRecurrentSchedulerAPIClient();
// set the request path
$client->setRequestURI('/recurrent/task/list');
$client->setSubscriptionID('rcu_277870587038832398');
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
