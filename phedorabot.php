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

if (!function_exists('curl_init')) {
    throw new Exception('curl_init function is required');
}

if (!function_exists('json_decode')) {
    throw new Exception('The json extension is required by this library');
}

function initialize_phedorabot_library(){
  $root = dirname(__FILE__);
  $files = array();

  $path = $root.'/src';

  $list = @scandir($path);
  if ($list === false) {
    throw new Exception("Unable to list contents of directory `{$path}`");
  }

  foreach ($list as $k => $v) {
    if ($v == '.' || $v == '..') {
      unset($list[$k]);
    }
  }

  foreach($list as $k => $file_or_directory){
    if(is_dir($path.'/'.$file_or_directory)){
      $sublists = @scandir($path.'/'.$file_or_directory);
      if($sublists !== false){
        foreach($sublists as $n => $m){
          if($m == '.' || $m == '..'){
            unset($sublists[$n]);
          }
        }
        foreach($sublists as $n => $subfile){
          $files[$subfile] = $path.'/'.$file_or_directory.'/'.$subfile;
        }
      }
    }else{
      $files[$file_or_directory] = $path.'/'.$file_or_directory;
    }
  }

  foreach($files as $file_name => $file_path){
    try{
      require_once $file_path;
    }catch(Exception $ex){
      echo $ex->getMessage();
    }
  }
}

initialize_phedorabot_library();
