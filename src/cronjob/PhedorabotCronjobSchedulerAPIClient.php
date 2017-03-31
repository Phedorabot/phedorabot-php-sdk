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

final class PhedorabotCronjobSchedulerAPIClient extends PhedorabotAPIClient{

  public function __construct(){
    parent::__construct();
  }

  /** Subscription ID
  *
  * The cronjob service subscription as seen from your subscription page on
  * Phedorabotbot
  */
  public function setSubscriptionID($id){

    if($id && strlen($id)){
      $this->setParameter('subscription_id',(string)$id);
    }
    return $this;
  }

  /** Job ID
  *
  * Set a cronjob task job id already created on Phedorabotbot
  */
  public function setJobID($id){

    if($id && strlen($id)){
      $this->setParameter('job_id',(string)$id);
    }
    return $this;
  }

  /**
  * Task Name
  * The cronjob task name for easy identification
  */
  public function setTaskName($task_name){
    if($task_name && strlen($task_name)){
      $this->setParameter('task_name', $task_name);
    }
    return $this;
  }

  /**
  * Callback URI
  * Set a fully qualified uri on your server to which Phedorabotbot will send
  * Instant Execution Notification of your cronjob tasks. This should have
  * http protocols
  */
  public function setCallbackURI($callback_uri){
    if($callback_uri && strlen($callback_uri)){
      $this->setParameter('callback_uri', $callback_uri);
    }
    return $this;
  }

  /**
  * A description of this cronjob task, not more than 160 characters
  */
  public function setTaskDescription($task_description){
    if($task_description && strlen($task_description)){
      $this->setParameter('task_description', $task_description);
    }
    return $this;
  }

  /**
  * The cronjob macros that will be used for building the timepoint graph for
  * executing this cronjob going forward
  */
  public function setCronMaros($macros){
    if($macros && strlen($macros)){
      $this->setParameter('cron_script', $macros);
    }
    return $this;
  }

  /**
  * Add custom properties to this cronjob task, this will always be sent
  * back to your callback uri each time Phedorabotbot sends an instant
  * execution notification
  */
  public function addCustomProperty($key, $value){
    $properties = $this->getParameter('cron_properties', array());

    if($key && strlen($key) && $value && strlen($value)){
      $properties[$key] = $value;
      $this->setParameter('cron_properties', $properties);
    }
    return $this;
  }

  /**
  * Add custom headers to this cronjob task, this will always be sent
  * back to your callback uri as part of the headers each time Phedorabot
  * sends an instant execution notification
  */
  public function addCustomHeader($key, $value){
    $headers = $this->getParameter('cron_headers', array());

    if($key && strlen($key) && $value && strlen($value)){
      $headers[$key] = $value;
      $this->setParameter('cron_headers', $headers);
    }
    return $this;
  }

}
