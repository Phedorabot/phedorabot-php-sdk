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


final class PhedorabotAPIResponse{

  private $headers;
  private $rawData = array();
  private $error;
  private $body;
  private $offset = 0;
  private $limit = 100;
  private $more = false;

  public function __construct(array $data, array $headers, $error=null){
    $this->headers = $headers;
    $this->rawData = $this->parseData($data);
    $this->error = $error;
  }

  private function parseData(array $data){

    if(array_key_exists('starting_at', $data)){
      $this->offset = (int)$data['starting_at'];
    }
    if(array_key_exists('has_more', $data)){
      $this->more = $data['has_more'];
    }

    if(array_key_exists('data', $data)){
      $this->body = $data['data'];
    }
    return $data;
  }

  public function isSuccessful(){
    if($this->error !== null){
      return false;
    }
    return true;
  }

  public function isError(){
    return $this->isFailure();
  }

  public function isFailure(){
    if($this->error !== null){
      return true;
    }
    return false;
  }

  public function getError(){
    return $this->error;
  }

  public function getBody(){
    return $this->body;
  }

  public function getOffset(){
    return $this->offset;
  }

  public function getLimit(){
    return $this->limit;
  }

  public function getRawData(){
    return $this->rawData;
  }

  public function hasMoreData(){
    return $this->more;
  }
}
