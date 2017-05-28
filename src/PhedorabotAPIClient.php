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

abstract class PhedorabotAPIClient {

    protected $accessToken;
    protected $accessTokenType;
    protected $expiresIn;
    protected $parameters = array();
    protected $api;
    protected $uri;

    const REQUEST_ENDPOINT = 'https://www.phedorabot.com/api/v1/';
    
    public function __construct() {
      $this->parameters = array();
    }

    public function setRequestURI($uri){
      $this->uri = $uri;
      return $this;
    }

    public function getRequestURI(){
      if($this->uri === null){
        throw new PhedorabotAPIError(
        'Request uri is not defined for this operation');
      }
      return $this->uri;
    }

    final protected function setParameter($name, $value){
      $this->parameters[$name] = $value;
      return $this;
    }

    final protected function getParameter($name, $default=null){
      if(!array_key_exists($name, $this->parameters)){
        return $default;
      }else{
        return $this->parameters[$name];
      }
    }

    final public function getParameters(){
      return $this->parameters;
    }

    final public function setAccessToken($access_token){
      $this->accessToken = $access_token;
      $this->accessTokenType = 'Bearer';
      return $this;
    }

    final public function getAccessToken(){
      return $this->accessToken;
    }

    final public function getAccessTokenType(){
      return $this->accessTokenType;
    }

    final public function requireAccessToken() {

        if (!$this->accessToken || !$this->accessTokenType) {
            $token_handler = $this->requestAccessToken();
            if (!$token_handler) {
                throw new PhedorabotAPIError(
                'Failed to retrieve an access token for this operation');
            }

            if($token_handler->isFailure()){
              throw new PhedorabotAPIError($token_handler->getError());
            }

            $token = $token_handler->getRawData();

            if(array_key_exists('error', $token)){
              throw new PhedorabotAPIError(
              "{$token['error']}:{$token['error_description']}");
            }

            $this->accessToken     = $token['access_token'];
            $this->accessTokenType = $token['access_token_type'];
            $this->expiresIn       = $token['expires_in'];
        }
        // check if the access token has expired
        $expired = (int) $this->expiresIn - time();
        if ($expired < 0) {
            // request a fresh token
            $token_handler = $this->requestAccessToken();
            if (!$token_handler) {
                throw new PhedorabotAPIError(
                'Failed to retrieve an access token for this transaction');
            }

            if($token_handler->isFailure()){
              throw new PhedorabotAPIError($token_handler->getError());
            }
            $token = $token_handler->getRawData();

            $this->accessToken     = $token['access_token'];
            $this->accessTokenType = $token['access_token_type'];
            $this->expiresIn       = $token['expires_in'];
        }
    }

    final public function setAPI($api) {
        $this->api = $api;
        return $this;
    }

    final public function getAPI() {
        return $this->api;
    }

    final protected function requestAccessToken() {
        if (!$this->api) {
            throw new PhedorabotAPIError(
            'API object is needed to request for access token');
        }

        $params = array(
            'grant_type' => 'client_credentials',
        );

        $api = $this->getAPI();
        $uri = self::REQUEST_ENDPOINT.'authentication/client/accesstoken/';

        $request = new PhedorabotAPIRequest();
        $request->setAPIKey($api->getAPIKey());
        $request->setAPISecret($api->getAPISecret());
        $request->setData($params);
        $request->setURI($uri);
        $request->withAuthentication();
        return $request->makeAPICall();
    }

    public function __toString() {
      $class_name = get_class($this);
      $parameters = isset($this->parameters)?$this->parameters:array();
        foreach($this as $key => $value){
            if($key !== 'parameters'){
             $parameters[$key] = $value;
            }
        }
        return "\n\nClass-> {$class_name}: Data ->\n\n".
        json_encode($parameters);
    }

    public function setPagingLimit($limit=100){
      $this->setParam('limit',(int)$limit);
      return $this;
    }

    public function setStartingAt($starting_at=0){
      $this->setParam('starting_at',(int)$starting_at);
      return $this;
    }
}
