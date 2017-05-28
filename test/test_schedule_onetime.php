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
$client = new PhedorabotOneTimeSchedulerAPIClient();
// set the request path
$client->setRequestURI('/onetime/task/schedule');
// set subscription id
$client->setSubscriptionID('ott_368304984053430447');

// We want to schedule a task that will executed at a future date
$client->setRequestURI('/onetime/task/schedule');

// set the task name
$client->setTaskName('Email Reminder');
// set the task description, not more than 160 characters
$client->setTaskDescription('Email reminder for our customer base');

// We will be scheduling a task that will execute three weeks from today
$start_date = date('Y-m-d G:i:s', time());

// set the time unit in this case 'week', you could also use the following
// (month, day, year, hour) these are valid time unit
$client->setTimeUnit('hour');

// set the period length in this case 3
$client->setPeriodLength(3);

// Set the date that should be used for computing when the task will execute
// this date will be used for calculating when the task will execute, like for
// example if today is 2017-03-12 10:19:49, that is today is March 12th, 2017 at 10:19 am, then
// the task will execute three weeks from that date ,2017-04-02 10:19:49 that is on
// April 2nd, 2017 at 10:19 am

$client->setStartDate($start_date);

// You can add custom headers to this one time task, it will be sent back to
// you when your receive instant execution notification

$client->addCustomHeader('key1', 'value1');
$client->addCustomHeader('key2', 'value2');

// You can add custom key-value properties too, this will be sent back to your
// server when instant execution notification is sent

$client->addCustomProperty('key1', 'value1');
$client->addCustomProperty('key2', 'value2');

// Lastly set the callback uri at which instant execution notification will be
// sent to. This should be a uri on your server

// set the callback uri
$client->setCallbackURI('http://www.mywebsite.com/cron/callback/');
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
