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

final class PhedorabotWebHookEngine {

  private $response;
  private $headers;
  private $payload;
  private $apiKey;
  private $apiSecret;
  private $rawData;
  private $error = null;
  private $errorDescription;
  private $result = array();

  const NOTIFICATION_DIGEST_KEY = 'phedorabot_notification_digest';
  const API_KEY = 'phedorabot_api_key';

  public function __construct(){
    $this->response = array();
    $this->headers = array();
    $this->rawHeaders = array();
    $this->payload = array();
    $this->apiKey = null;
    $this->apiSecret = null;
    $this->digestKey = null;

  }

  public function setRawHeaders(array $raw_headers){
    $this->rawHeaders = $raw_headers;
    return $this;
  }

  public function setApiKey($api_key){
    $this->apiKey = $api_key;
    return $this;
  }

  public function getApiKey(){
    return $this->apiKey;
  }

  public function setApiSecret($api_secret){
    $this->apiSecret = $api_secret;
    return $this;
  }

  public function getApiSecret(){
    return $apiSecret;
  }

  public function setError($error){
    $this->error = $error;
    return $this;
  }

  public function setErrorDescription($description){
    $this->errorDescription = $description;
    return $this;
  }

  public function getPayload(){
    return $this->payload;
  }

  public function getHeaders(){
    return $this->headers;
  }

  public function setRawData($raw_data){
    if(is_array($raw_data)){
      $raw_data = json_encode($raw_data);
    }

    $this->rawData = $raw_data;
    return $this;
  }

  public function addResult($key, $value){
    $this->result[$key] = $value;
    return $this;
  }

  private function willValidateTaskExecution(){
    $this->buildHeaders();
    return $this;
  }

  public function isValidTaskExecution(){
    $status = true;

    try{

      $this->willValidateTaskExecution();
      // Should be able to decode the raw JSON data to an array or we
      // throw an exception
      if(!$this->rawData || !strlen($this->rawData)){
        throw new PhedorabotWebHookEngineException(
        'Task Web hook listener received an invalid task execution payload');
      }

      $payload = json_decode($this->rawData, true);
      if(!$payload || !is_array($payload)){
        throw new PhedorabotWebHookEngineException(
        'Task Web hook listener is unable to decode the task execution '.
        'message payload');
      }

      // Next if this payload is valid at this point we should have the
      // api key set so that it can be used to query for the
      // corresponding api secret on your local database storage of
      // whereever you decided upfront to store your api key pair.
      // Note this key pair is needed to actually verify the integrity of
      // this payload, failing to do this will not

      $this->payload = $this->trimPayload($payload);
      $this->addResult('status', 'message delivered');

    }catch(PhedorabotWebHookEngineException $ex){
      $this->error = $ex->getCode();
      $this->errorDescription = $ex->getMessage();
      $status = false;
    }catch(Exception $e){
      $this->error = $e->getCode();
      $this->errorDescription = $ex->getMessage();
      $status = false;
    }

    return $status;
  }

  private function trimPayload(array $props){
    $maps = array();
    foreach($props as $k => $v){
      $maps[trim($k)] = trim($v);
    }

    return $maps;
  }

  public function verifyTaskExecutionPayload(){

    if(!$this->payload){
      throw new PhedorabotWebHookEngineException(
      'Task execution payload is not defined');
    }

    $hmac_string = $this->computeHMAC($this->payload);
    $known_hmac = $this->headers[self::NOTIFICATION_DIGEST_KEY];
    $is_valid = true;
    $len = strlen($known_hmac);
    $this->checksum = 'verified';

    for($ii=0; $ii < $len; $ii++){
      $delta = ord($known_hmac[$ii]) ^ ord($hmac_string[$ii]);
      if($delta){
        $is_valid = false;
        $this->checksum = 'invalid';
      }
    }

    $this->addResult('digestkey', $known_hmac);
    $this->addResult('checksum', $this->checksum);
    $this->addResult('computed_hmac', $hmac_string);

    return $is_valid;
  }

  private function flattenPayload(array $payload=array(), $parent_key=null){
    $blocks = array();

    if(is_array($payload)){
      foreach($payload as $key=>$value){
        $key = $this->computeValidKey($key, $parent_key);
        if(is_array($value)){
          $intermediates =$this->flattenPayload($value, $key);
          foreach($intermediates as $in_key=>$in_value){
            $blocks[$in_key] = $in_value;
          }
        }else{
          $blocks[$key] = $value;
        }
      }
    }
    return $blocks;
  }

  private function computeHMAC(array $payload=array()){
    // First flattern the payload we need to compute the hmac
    // so that if by any means any entries has been modified during
    // the payload transit, the hmac string in the header will ultimately
    // differ from the currently computed version thereby triggering
    // a WebHookValidationException

    if(!$this->apiSecret){
      throw new Exception(
      'corresponding api secret key is not defined for verifying the '.
      'integrity of this payload');
    }

    $flattened = $this->flattenPayload($payload, null);
    ksort($flattened);
    $data = array();
    foreach($flattened as $key=>$value){
      $key = trim($key);
      $value = trim($value);
      $data[] = "{$key}={$value}";
    }

    $data_string = implode('',$data);
    return hash_hmac('sha256', $data_string, $this->apiSecret);
  }

  private function computeValidKey($current_key, $parent_key){
    if($parent_key !== null){
      return "{$parent_key}_{$current_key}";
    }else{
      return $current_key;
    }
  }

  private function buildCustomHeaders(){
    $headers = array();
    foreach($this->rawHeaders as $key => $value){
      $key = strtolower($key);
      if(substr($key, 0, 5) === 'php_x'){
        $headers[substr($key, 6)] = $value;
      }
    }
    return $headers;
  }

  private function buildHeaders(){
    $headers = array();
    foreach($this->rawHeaders as $key => $value){
      $key = strtolower($key);
      if(substr($key, 0, 6) == 'http_x'){
        $headers[substr($key, 7)] = $value;
      }
    }

    if(!$headers){
      $headers = $this->buildCustomHeaders();
    }

    if(array_key_exists(self::API_KEY, $headers)){
      $this->apiKey = (string)$headers[self::API_KEY];
    }

    $this->headers = $headers;
    return $this;
  }

  private function buildResponse(){

    $response = array();
    if($this->error !== null && $this->errorDescription !== null){
      $response['error'] = $this->error;
      $response['error_description'] = $this->errorDescription;
    }

    $response = $response + $this->result;
    return $response;
  }

  public function sendResponse(){
    $response = $this->buildResponse();
    echo json_encode($response);
  }

  public function getResponse(){
    return $this->buildResponse();
  }
}
