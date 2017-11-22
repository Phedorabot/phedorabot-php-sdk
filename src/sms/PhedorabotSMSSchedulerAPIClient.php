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

final class PhedorabotSMSSchedulerAPIClient extends PhedorabotAPIClient{

  public function __construct(){
    parent::__construct();
  }

  /** Subscription ID
  *
  * The sms messaging service subscription as seen from your subscription page
  * on Phedorabot
  */
  public function setSubscriptionID($id){

    if($id && strlen($id)){
      $this->setParameter('subscription_id',(string)$id);
    }
    return $this;
  }

  /** Job ID
  *
  * Set an sms messaging job id already created on Phedorabot
  */
  public function setJobID($id){

    if($id && strlen($id)){
      $this->setParameter('job_id',(string)$id);
    }
    return $this;
  }

  /**
  * Task Name
  * The sms messaging task name for easy identification
  */
  public function setTaskName($task_name){
    if($task_name && strlen($task_name)){
      $this->setParameter('task_name', $task_name);
    }
    return $this;
  }

  /**
  * Callback URI
  * Set a fully qualified uri on your server to which Phedorabot will send
  * Instant Execution Notification of your sms message delivery. This
  * should have http protocols
  */
  public function setCallbackURI($callback_uri){
    if($callback_uri && strlen($callback_uri)){
      $this->setParameter('callback_uri', $callback_uri);
    }
    return $this;
  }

  /**
  * A description of this sms message task, not more than 160 characters
  */
  public function setTaskDescription($task_description){
    if($task_description && strlen($task_description)){
      $this->setParameter('task_description', $task_description);
    }
    return $this;
  }

  /**
  * Add a custom proper maps if you already have a dictionary of properties
  * whose keys consists of strings and whose values consists of strings you can
  * set the properties at once using this method
  */
  public function addCustomPropertyMap(array $properties){
    foreach($properties as $prop_key => $prop_value){
      $this->addCustomProperty($prop_key, $prop_value);
    }
    return $this;
  }

  /**
  * Add custom properties to this sms messaging, this will always be sent
  * back to your callback uri each time Phedorabot sends an instant
  * execution notification
  */
  public function addCustomProperty($key, $value){
    $properties = $this->getParameter('sms_properties', array());

    if($key && strlen($key) && $value && strlen($value)){
      $properties[$key] = $value;
      $this->setParameter('sms_properties', $properties);
    }
    return $this;
  }

  /**
  * Add custom headers to this one time task, this will always be sent
  * back to your callback uri as part of the headers each time Phedorabot
  * sends an instant execution notification
  */
  public function addCustomHeader($key, $value){
    $headers = $this->getParameter('sms_headers', array());

    if($key && strlen($key) && $value && strlen($value)){
      $headers[$key] = $value;
      $this->setParameter('sms_headers', $headers);
    }
    return $this;
  }

  /** Add New SMS Recipient
  *
  * Adding a new SMS recipient requires proving the following
  *
  * - standard_number: (required) the mobile number of the recipient with the
  *                    country code attached to it example +234807676565.
  *
  * - message : (required) The message that should be sent to this recipient
  *             messages are normally not more than 160 characters, however
  *             the more characters you have the more pages will be generated
  *             for delivery the sms to the client and the more sms credit that
  *             will be used.
  *
  * - time_unit : (optional) This is provided when you want the sms to be
  *               delivered at a future time. Its usually one of
  *               (minute, hour, day, week). Example if you want an sms to be
  *               delivered to your client say three days from today, you need
  *               to set the time_unit to 'day' and the period_length to '3'
  *
  * - period_length : (optional) This is provided when you want the sms to be
  *                   delivered at a future time. Its usually a positive number.
  *                   Example of you want an sms delivered to be delivered to
  *                   your client in 2 weeks time, you would set the time_unit
  *                   to 'week' and the period_length to  '2'
  *
  */
  public function addRecipient(
  $standard_number, $message, $time_unit=null, $period_length=null){

    $recipients = $this->getParameter('recipients', array());
    if($standard_number
    && strlen($standard_number)
    && $message && strlen($message)){
      $recipients[] = array(
        'number' => $standard_number,
        'body'   => $message,
        'time_unit' => $time_unit,
        'period_length' => $period_length,
      );
      $this->setParameter('recipients', $recipients);
    }

    return $this;
  }

  /** Sender ID
  *
  * Specify the sender id, this can either be a service name or a company name
  * however, it should not be more than 11 characters in lenght
  */
  public function setSenderID($sender_id){
    if($sender_id && strlen($sender_id)){
      $this->setParameter('sender_id', $sender_id);
    }

    return $this;
  }

}
