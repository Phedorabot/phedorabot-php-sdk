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

require '../phedorabot.php';
// sample cronjob payload from phedorabot
$test_data = '
{"cronjob_id":"295039090705787663",
  "service_name":"Recurrent Event Service",
  "subscription_id":"rcu_353431617358994734",
  "execution_id":"274295575708856625",
  "task_name":"Recurrent event for 2017-03-21 8:18:22",
  "execution_epoch":"1492759102",
  "execution_date":"April 21st, 2017 at 7:18 am",
  "next_epoch":"1495351102",
  "next_execution_date":"May 21st, 2017 at 7:18 am",
  "cronjob_script":null,
}';

$test_headers = '{
  "HTTP_X_PHEDORABOT_NOTIFICATION_DIGEST":"9504aa75bde1a51ddfdcdbb432f35e834c0f279a",
  "HTTP_X_PHEDORABOT_SENT_THIS":"1",
  "HTTP_X_PHEDORABOT_API_KEY":"eXZ2U1dtdWRKczFqYmNj"
}';


$engine = new PhedorabotWebHookEngine();
$engine->setRawHeaders(json_decode($test_headers, true));
$engine->setRawData($test_data);
$engine->setApiSecret('aU42Rm5DZ1JnQTAwMkVLZ0dOenBtY01kNTQrZlJq');
if($engine->isValidTaskExecution()){
  // we can verify the task execuetion
  echo "Valid task execution processing payload...\n";
  $engine->verifyTaskExecutionPayload();
}

// send the response
echo "Sending feedback ...\n";
$engine->sendResponse();
echo "\nDone\n";
