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


final class PhedorabotAPIRequest {

    private $data;
    private $debug = false;
    private $uri;
    private $apiKey;
    private $apiSecret;
    private $headers;
    private $responseHeaders = array();
    private $accessToken;
    private $tokenType;
    private $requestMethod = self::REQUEST_METHOD_POST;
    private $encode = false;
    private $authenticate = false;

    const JWT_EXPIRES         = 240;
    const REQUEST_METHOD_POST = 'POST';
    const REQUEST_METHOD_GET  = 'GET';

    public function withEncode() {
        $this->encode = true;
        return $this;
    }

    public function withDebug(){
      $this->debug = true;
      return $this;
    }

    public function withAuthentication() {
        $this->authenticate = true;
        return $this;
    }

    public function addHeader($key, $value) {
        if (!$this->headers) {
            $this->headers = array();
        }
        $this->headers[] = array($key, $value);
    }

    public function setData(array $data) {
        $this->data = $data;
        return $this;
    }

    public function getData() {
        return $this->data;
    }

    public function setURI($uri) {
        $this->uri = $uri;
        return $this;
    }

    public function getURI() {
        return $this->uri;
    }

    public function setRequestMethod($method) {
        $expected_methods = array(
            self::REQUEST_METHOD_GET => 1,
            self::REQUEST_METHOD_POST => 1,
        );

        if (!array_key_exists(
        strtoupper(strtolower($method)), $expected_methods)) {
            throw new PhedorabotAPIError('Invalid request method');
        }
        $this->requestMethod = $method;
        return $this;
    }

    public function setAccessToken($access_token) {
        $this->accessToken = $access_token;
        return $this;
    }

    public function setAccessTokenType($token_type) {
        $this->tokenType = $token_type;
        return $this;
    }

    public function setAPIKey($api_key) {
        $this->apiKey = $api_key;
        return $this;
    }

    public function setAPISecret($api_secret) {
        $this->apiSecret = $api_secret;
        return $this;
    }

    final public function makeAPICall() {
        $data = $this->data;
        $ch = curl_init();
        if ($data) {
          if ($this->encode) {
            if (!$this->apiKey || !$this->apiSecret) {
              throw new PhedorabotAPIError(
              'Data request for transmission needs to be encoded as a JWT '.
              'Token payload. This requires that you provide your Api key '.
              'pair for this operation');
            }

            $data = $this->encodeData($data);
            $data = array('jwt' => $data);
          }
        }

        if ($this->authenticate) {
            if (!$this->apiKey || !$this->apiSecret) {
              throw new PhedorabotAPIError(
              'Requesting for access token authentication requires your api '.
              'key and secret');
            }

            $this->addHeader('Accept', 'application/json');
            $this->addHeader('Accept-Language', 'en_US');

            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt(
            $ch, CURLOPT_USERPWD, "{$this->apiKey}:{$this->apiSecret}");

        } else {

            $this->addHeader('Content-Type', 'application/json');
            $this->addHeader(
            'Authorization'
            , "{$this->tokenType} {$this->accessToken}");
        }

        $raw_header = array();
        if($this->debug){
          echo "Making a request to {$this->uri}\n\n";
        }

        if ($this->headers) {
            foreach ($this->headers as $header) {
                list($key, $value) = $header;
                $raw_header[] = "{$key}:{$value}";
            }
        }

        $options = array(
            CURLOPT_HEADER         => true,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_HTTPHEADER     => $raw_header,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE        => true,
            CURLOPT_TIMEOUT        => 20,
        );

        if ($this->requestMethod == self::REQUEST_METHOD_POST) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($data,'&');
        } else {
            $this->uri . '?' . http_build_query($data, '&');
        }

        $uri_parts = parse_url($this->uri);

        curl_setopt($ch, CURLOPT_URL, $this->uri);
        if($uri_parts['scheme'] === 'https'){
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
          curl_setopt($ch, CURLOPT_CAINFO, dirname(getcwd()).'/data/cacert.pem');
        }


        $options[CURLOPT_CUSTOMREQUEST] = $this->requestMethod;
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        $handler = null;
        try{

          $data = $this->parseResponse($response);
          $handler = new PhedorabotAPIResponse(
          $data, $this->responseHeaders, null);

        }catch(PhedorabotAPIError $ex){
          $handler = new PhedorabotAPIResponse(
          array()
          , $this->responseHeaders
          , $ex->getMessage());
        }

        return $handler;
    }

    final protected function encodeData(array $data) {
        if (!$data) {
            throw new PhedorabotAPIError(
            'Building a JWT Payload requires that data map');
        }

        if(!$this->apiSecret){
          throw new PhedorabotAPIError(
          'Encoding payload as JWT Web token requires your api key');
        }

        $custom_headers = array(
          'aud'   => 'PhedorabotAPI'
          , 'iss' => (string)$this->apiKey
          , 'iat' => time()
          , 'exp' => time() + self::JWT_EXPIRES
        );

        $jwt_string = JWT::encode(
        $data, $custom_headers, $this->apiSecret);

        return $jwt_string;
    }

    final protected function decodeData($jwt_string) {

        try {

            list($header, $data) = JWT::decode(
            $jwt_string
            , $this->apiSecret);

            // check the issuer it must be the originating api key
            if(!$header){
              throw new Exception(
              'Received an invalid JWT header section for request');
            }

            if (empty($header['iss']) || ($header['iss'] !== 'PhedorabotAPI')) {
                throw new Exception(
                'Received JWT Web token package has invalid issuer');
            }
            // check the audience it must be the same as the origin
            if (empty($header['aud']) || ($header['aud'] !== $this->apiKey)) {
                throw new Exception(
                'Received JWT Web token package has invalid audience');
            }
            $this->responseHeaders = $header;
        } catch (Exception $ex) {
            throw new PhedorabotAPIError($ex->getMessage());
        }

        return $data;
    }

    private function parseResponse($response_string) {

        if ($this->debug) {
            //echo "Got : {$response_string} \n";
        }

        list($status_code, $body, $headers, $raw) =
        $this->parseRawHTTPResponse($response_string);

        if (!$status_code) {
            throw new PhedorabotAPIError(
            "Got a bad status code {$status_code}");
        }

        $json_data = null;
        try {
            $json_data = json_decode(trim($body), true);

        } catch (Exception $ex) {
            throw new PhedorabotAPIError($ex->getMessage());
        }

        if (!$json_data && strlen($response_string)) {
            throw new PhedorabotAPIError(
            "Expected JSON response failed to parse from ".
            "response {$response_string}");
        }

        // search for jwt data if we have one validate and parse it
        if(array_key_exists('jwt', $json_data)){
          if (!empty($json_data['jwt'])) {
              $json_data = $this->decodeData($json_data['jwt']);
          }else{
            throw new PhedorabotAPIError(
            'Payload is expecting a JWT encoded response but received '.
            'an empty body');
          }
        }

        // check for error
        if(array_key_exists('error', $json_data)){
          throw new PhedorabotAPIError(
          "API Error key:{$json_data['error']}, ".
          "description:{$json_data['error_description']}");
        }

        if(!array_key_exists('result', $json_data)){
          return $json_data;
        }else{
          return $json_data['result'];
        }

    }

    private function parseRawHTTPResponse($raw_response) {
        $rex_base = "@^(?P<head>.*?)\r?\n\r?\n(?P<body>.*)$@s";
        $rex_head = "@^HTTP/\S+ (?P<code>\d+) (?P<status>.*?)" .
                "(?:\r?\n(?P<headers>.*))?$@s";

        // We need to parse one or more header blocks in case we got any
        // "HTTP/1.X 100 Continue" nonsense back as part of the response. This
        // happens with HTTPS requests, at the least.
        if($this->debug){
          // echo "Received raw response : {$raw_response}\n\n";
        }
        $response = $raw_response;
        while (true) {
            $matches = null;
            if (!preg_match($rex_base, $response, $matches)) {
                return $this->buildMalformedResult($raw_response);
            }

            $head = $matches['head'];
            $body = $matches['body'];

            if (!preg_match($rex_head, $head, $matches)) {
                return $this->buildMalformedResult($raw_response);
            }

            $response_code = (int) $matches['code'];
            $response_status = strtolower($matches['status']);
            if ($response_code == 100) {
                // This is HTTP/1.X 100 Continue, so this whole chunk is moot.
                $response = $body;
            } else if (($response_code == 200) &&
                    ($response_status == 'connection established')) {
                // When tunneling through an HTTPS proxy, we get an initial
                // header block like "HTTP/1.X 200 Connection established",
                // then newlines, then the normal response. Drop this chunk.
                $response = $body;
            } else {
                $headers = $this->parseHeaders($matches['headers']);
                break;
            }
        }

        return array($response_code, $body, $headers, $raw_response);
    }

    /**
     * Parse an HTTP header block.
     *
     * @param string Raw HTTP headers.
     * @return list List of HTTP header tuples.
     * @task internal
     */
    private function parseHeaders($head_raw) {
        $rex_header = '@^(?P<name>.*?):\s*(?P<value>.*)$@';

        $headers = array();

        if (!$head_raw) {
            return $headers;
        }

        $headers_raw = preg_split("/\r?\n/", $head_raw);
        foreach ($headers_raw as $header) {
            $m = null;
            if (preg_match($rex_header, $header, $m)) {
                $headers[] = array($m['name'], $m['value']);
            } else {
                $headers[] = array($header, null);
            }
        }

        return $headers;
    }

    /**
     * Build a result tuple indicating a parse error resulting from a malformed
     * HTTP response.
     *
     * @return tuple Valid resolution tuple.
     * @task internal
     */
    private function buildMalformedResult($raw_response) {
        $body = null;
        $headers = array();
        return array(false, $body, $headers, $raw_response);
    }
}
