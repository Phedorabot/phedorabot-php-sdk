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

$test_data = '{"job_id":"352667059500430128","service_name":"One Time Trigger Service","subscription_id":"ott_110801827011331707","execution_id":"166887952632453844","task_name":"RAJI TARIQ","execution_epoch":"1514446200","execution_date":"December 28th, 2017 at 7:30 am","next_epoch":"1545982200","next_execution_date":"December 28th, 2018 at 7:30 am","task_duration":"1 Year","user_guid":"6lmhcmazsltepsxwtyrt","user_birthday_epoch":"1514446200","family_guids":"xfl3neemltidwc3xirvo,f5dvlspdldlfsmlnfqop\r\n"}';

$test_headers = '{
  "HTTP_X_PHEDORABOT_NOTIFICATION_DIGEST":"965d207a58a302562a548fa28fcdc0611343d8b0f8664c2ccf918f2f80497fa4",
  "HTTP_X_PHEDORABOT_SENT_THIS":"1",
  "HTTP_X_PHEDORABOT_API_KEY":"YlNiZQp0QWlkVnBJQ0g4"
}';


$engine = new PhedorabotWebHookEngine();
$engine->setRawHeaders(json_decode($test_headers, true));
$engine->setRawData($test_data);
$engine->setApiSecret('SFUraUVjU2RUeDRoemNXTnJaaXVEZGJNRW9TRFN5');
if($engine->isValidTaskExecution()){
  // we can verify the task execuetion
  echo "Valid task execution processing payload...\n";
  $engine->verifyTaskExecutionPayload();
}

// send the response
echo "Sending feedback ...\n";
$engine->sendResponse();
echo "\nDone\n";
