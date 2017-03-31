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


final class PhedorabotClient {

    private $apiKey = null;
    private $apiSecret = null;
    private $callType;
    private $accessToken;
    private $debug = false;

    private function __construct(
    $api_key = null, $api_secret = null, $access_token=null) {
        //Intentionally made private;
        $this->apiKey      = $api_key;
        $this->apiSecret   = $api_secret;
        $this->accessToken = $access_token;
    }

    /**
     * Call this function if you want to start a live transaction
     * with the fivekobo platform
     */
    public static function initializeRequest(
    $api_key = null, $api_secret = null, $access_token=null) {
      return new PhedorabotClient($api_key, $api_secret, $access_token);
    }

    final public function getAPIKey() {
        return $this->apiKey;
    }

    final public function getAPISecret() {
        return $this->apiSecret;
    }


    final public function getAccessToken(){
      return $this->accessToken;
    }

    final public function withDebug(){
      $this->debug = true;
      return $this;
    }

    final public function getDebug(){
      return $this->debug;
    }

    final public function send(PhedorabotAPIClient $api){
      // This method will either return a PhedorabotAPIResponse object or throw
      // a PhedorabotAPIError exception on error
      $api->setAPI($this);
      return $this->doRequest($api);
    }

    final private function doRequest(PhedorabotAPIClient $api){
      $uri = $api->getRequestURI();
      if(!$uri || !strlen($uri)){
        throw new PhedorabotAPIError(
        'Invalid request uri for client '.get_class($api));
      }

      $data = $api->getParameters();
      if(!$this->getAPIKey() || !strlen($this->getAPIKey())){
        throw new PhedorabotAPIError(
        'API Key is required to send '.get_class($api).' client request');
      }

      if(!$this->getAPISecret() || !strlen($this->getAPISecret())){
        throw new PhedorabotAPIError(
        'API secret key is required to send '.get_class($api).
        ' client request');
      }

      if($this->accessToken !== null){
        $api->setAccessToken($this->accessToken);
      }else{
        $api->requireAccessToken();
      }

      $uri = PhedorabotAPIClient::REQUEST_ENDPOINT.ltrim($uri, '/');
      $uri = rtrim($uri, '/').'/';

      $request = new PhedorabotAPIRequest();
      $request->setAPIKey($this->getAPIKey());
      $request->setAPISecret($this->getAPISecret());
      $request->setAccessToken($api->getAccessToken());
      $request->setAccessTokenType($api->getAccessTokenType());
      $request->setURI($uri);
      $request->setData($data);
      $request->withEncode();

      if($this->getDebug()){
        $request->withDebug();
      }
      return $request->makeAPICall();
    }
}
