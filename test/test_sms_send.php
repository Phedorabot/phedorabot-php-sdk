<?php

/**
* This script tests sending SMS messages
*/

require '../phedorabot.php';
require 'test_config.php';

$request = PhedorabotClient::initializeRequest(
Config::API_KEY, Config::API_SECRET);

// we are debuging
$request->withDebug();
// Create an sms scheduler client
$client = new PhedorabotSMSSchedulerAPIClient();
// set the request path
$client->setRequestURI('/sms/message/send');
$client->setTaskName('Sample SMS Delivery');
$client->setTaskDescription('Sample SMS Delivery');
// set the sender id, this should be the name that should appear as the
// sender id, it should not be more than 11 characters
$client->setSenderID('Flipkarts');
$client->addCustomHeader('key1', 'value1');
$client->addCustomHeader('key2', 'value2');

$client->addCustomProperty('key1', 'value1');
$client->addCustomProperty('key2', 'value2');

// set the cronjob service subscription id
$client->setSubscriptionID('sms_235509178964741374');

// set the callback uri
$client->setCallbackURI('http://www.amastore.com/sms/callback/');

// Add the recipients of the sms from the lists
$lists = array(
  '+2349076834238' => 'Hello World...',
  '+2348065782721' => 'Hi just cheeking up on you...',
);

foreach($lists as $number => $message){
  // not that we are setting time_unit and period_length to null to indicate
  // that we want the messages delivered immediately
  $client->addRecipient($number, $message, null, null);
}

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
