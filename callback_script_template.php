<?php

/** Copyright 2017 Phedorabot
*
* Licensed under the Apache License, Version 2.0 (the "License"); you may
* not use this file except in compliance with the License. You may obtain
* a copy of the License at
*
*     http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
* WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
* License for the specific language governing permissions and limitations
* under the License.
*/


/**
* This script describes how to implement a callback if your using the Phedorabot
* PHP SDK for your task execution. Note that this callback must listen for
* POST requests only because Phedorabot servers will send the Instant Task
* Execution Payload as a POST request
*/

if($_SERVER['REQUEST_METHOD'] === 'POST'){

  // load the Phedorabot PHP SDk library to make it available to this script
  // for processing the request. Load the phedorabot.php script located within
  // the phedorabot-php-sdk folder

  require_once = '/path/phedorabot-php-sdk/phedorabot.php';

  // Wrap the entire process in a try/catch block to deal with exceptions
  try{

    // Initialize the webhook engine
    $engine = new PhedorabotWebHookEngine();

    // set the raw payload sent from Phedorabot servers, this is located in a
    // post variable called 'payload'

    $engine->setRawData($_POST['payload']);
    // set the raw post headers to build the headers coming from Phedorabot
    // if you had sent in custom headers, you will also find it in the headers
    // as well

    $engine->setRawHeaders($_SERVER);

    // Next we need to check if this is a valid Phedorabot task execution
    // request, this is a very important step because it verifies the
    // task payload and also picks up the api key that was used for creating
    // the payload message digest as seen in the header. We will be needing
    // the private key corresponding to the api public key for verifing the
    // integrity of the payload, all this steps is to jsut ensure that the
    // task execution payload was not tempered with in transit to your server

    if($engine->isValidTaskExecution()){
      // If we get this far then we have a valid task execution payload from
      // Phedorabot, at this point you can get your Phedorabot public api key
      // that is associated with this task payload
      $api_key = $engine->getApiKey();

      // use the api key to select from your database the corresponding api
      // private key, set it below then pass it to the engine for the final
      // task execution payload data integrity verification
      $api_secret_key = '';
      $engine->setApiSecret($api_secret_key);
      // verify the integrity of the task execution payload

      if($engine->verifyTaskExecutionPayload()){
        // If again we get this far then you can rest assure know that you have
        // a valid payload coming from Phedorabot server
        // get the payload
        $payload = $engine->getPayload();
        // get all headers sent in
        $headers = $engine->getHeaders();

        // at this point you can execute the task you want to execute you have
        // a window of 30 seconds to do that before Phedorabot server
        // stops waiting for response from this connection
        // TODO: implement your custom task execute here
        // after you are done you can pass the execution status to the engine
        // to be registered on your Phedorabot task execution log
        // by calling $engine->addResult('key', 'value');
      }
    }

  }catch(Exception $ex){

    $engine->setErrorDescription($e->getMessage());
    $engine->setError('callback_script_error');

  }finally{
    // This ensure that Phedorabot gets updated on the status of the callback
    // execution so that you get to see in the logs how your callback scripts
    // is executing

    $engine->sendResponse();
  }
  // end the script
  exit;
}
