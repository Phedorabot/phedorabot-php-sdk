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

final class PhedorabotRecurrentSchedulerAPIClient extends PhedorabotAPIClient{

  public function __construct(){
    parent::__construct();
  }

  /** Subscription ID
  *
  * The recurrent service subscription as seen from your subscription page on
  * Phedorabot
  */
  public function setSubscriptionID($id){

    if($id && strlen($id)){
      $this->setParameter('subscription_id',(string)$id);
    }
    return $this;
  }

  /** Job ID
  *
  * Set a recurrent task job id already created on Phedorabot
  */
  public function setJobID($id){

    if($id && strlen($id)){
      $this->setParameter('job_id',(string)$id);
    }
    return $this;
  }

  /**
  * Task Name
  * The recurrent task name for easy identification
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
  * Instant Execution Notification of your recurrent tasks. This should have
  * http protocols
  */
  public function setCallbackURI($callback_uri){
    if($callback_uri && strlen($callback_uri)){
      $this->setParameter('callback_uri', $callback_uri);
    }
    return $this;
  }

  /**
  * A description of this recurrent task, not more than 160 characters
  */
  public function setTaskDescription($task_description){
    if($task_description && strlen($task_description)){
      $this->setParameter('task_description', $task_description);
    }
    return $this;
  }

  /**
  * Add custom properties to this recurrent task, this will always be sent
  * back to your callback uri each time Phedorabot sends an instant
  * execution notification
  */
  public function addCustomProperty($key, $value){
    $properties = $this->getParameter('recurrent_properties', array());

    if($key && strlen($key) && $value && strlen($value)){
      $properties[$key] = $value;
      $this->setParameter('recurrent_properties', $properties);
    }
    return $this;
  }

  /**
  * Add custom headers to this recurrent task, this will always be sent
  * back to your callback uri as part of the headers each time Phedorabot
  * sends an instant execution notification
  */
  public function addCustomHeader($key, $value){
    $headers = $this->getParameter('recurrent_headers', array());

    if($key && strlen($key) && $value && strlen($value)){
      $headers[$key] = $value;
      $this->setParameter('recurrent_headers', $headers);
    }
    return $this;
  }


  /** Start Date
   *
   * This is the contextual date that should be used for calculating when
   * this task will execute for the first time, acceptable dates is of the
   * format 'Year-Month-Day Hour:Minutes:seconds'
   * for example '2017-06-14 10:30:00' is a valid date, which means for
   * example if you set time_unit to be 'month' and period_length to be '1'
   * then this task will be executed on the 14th of July 2017 at 10:30 am
   *
   */
  public function setStartDate($start_date){
    if($start_date){
      $this->setParameter('start_date', $start_date);
    }
    return $this;
  }

  /** If you do not want to set a start date or a day of the month you can call
   * this method, if will tell Phedorabot to use the current date and time at which
   * you request was recieved by the api to calculate when the task will be
   * executed
   */
  public function shouldStartImmediately(){
    return $this->setParameter('start_immediately', true);
  }

  /** Day of month
   *
   * Day of the month is a number between 1 to 31, this number means
   * that you want Phedorabot to use the day of the month as the contextual date
   * for calculating when the task should start executing.
   * For example, if today is February 15 and you set this day to be 14,
   * then the contextual date will be set to 14th of March.
   *
   * If you set the day to be 18, then the contextual date will be set to
   * February 18th; if you set the day to be 15, then the contextual date will
   * be set to today, using this contextual date we can then compute when the
   * task should start executing
   *
   * If you want the contextual date to be at the end of the month regardless
   * of the month, then set the day to 31
   */
  public function setDayOfMonth($day){
    if(!is_int($day)){
      throw new PhedorabotAPIError(
      'Day of month must be an integer between 1 and 31');
    }

    if($day && intVal($day)){
      $this->setParameter('day_of_month',(int)$day);
    }

    return $this;
  }

  /** Time unit
   *
   * Time unit allows you to indicate if you want your tasks to
   * subscribe to this subscription for a period of time before been billed
   * for the subscription. The trial time unit is one of
   * (day, week, month and year).
   *
   * If for example you want your customer to try out your subscription
   * service for three months before billing them for the subscription then
   * you should set the 'trial time unit' to 'month' and the
   * 'trial period length' to 3
   */
  public function setTimeUnit($time_unit){

    $expected_time_units = array('hour', 'day', 'week', 'month', 'year');
    $valid = array_fill_keys($expected_time_units, 1);
    if(!array_key_exists($time_unit, $valid)){
      throw new PhedorabotAPIError(
      'Invalid time unit '.$time_unit.
      ' valid time unit is one of (hour, day, week, month or year)');
    }

    return $this->setParameter('time_unit',$time_unit);
  }

  /** Period Length
   *
   * Trial period length is an integer that indicates how long a subcription
   * service trial should last. for example if you want customers to try out
   * your subscription service for a month then set the 'trial period length'
   * to 1 and the 'trial time unit' to month
   */
  public function setPeriodLength($period_length){
    if(!is_int($period_length)){
      throw new PhedorabotAPIError('Period length must be an integer');
    }
    return $this->setParameter('period_length',$period_length);
  }

  /** Exclude Weekdays
  * Given a contextual date from which to calculate when a task should execute
  * for the first time, we can exclude all weekdays by removing all Sartuday
  * and Sundays between the dates, this will mean that the task execution
  * date will be pushed forward
  *
  */
  public function withExcludeWeekends(){
    $this->setParameter('exclude_weekends', true);
    return $this;
  }
}
