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

final class PhedorabotWebHookEngineException extends Exception{
  protected $what;
  protected $reason;

  public function __construct($message='', $code=0){
    parent::__construct($message, $code);

    $this->what = '';
    $this->reson = '';

  }
  final public function setWhat($what){
    $this->what = $what;
    return $this;
  }

  final public function getWhat(){
    return $this->what;
  }

  final public function setReason($reason){
    $this->reason = $reason;
    return $this;
  }

  final public function getReason(){
    return $this->reason;
  }
}
