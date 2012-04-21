<?php

class Webfinger {
  public static function showJrd($get) {
    $userName = '';
    if($get['resource']) {
      $bits = explode('@', $_GET['resource']);
      if(count($bits)==2 && $bits[1] == Config::$usersHost) {
        debug($userName);
        $userName = $bits[0];
      } else {
        debug($userName.' rejected for users host '.Config::$usersHost);
      }
    }
    if(substr($userName, 0, 5) == 'acct:') {
      $userName = substr($userName, 5);
    }
    $baseAddress = Config::$serverProtocol.'://'.Config::$serverHost;
    if($userName) {
      header('Access-Control-Allow-Origin: *');
      header('Content-Type: application/json; charset=UTF-8');
      echo '{ "links": ['."\n"
        .'  {'."\n"
        .'    "rel":"http://www.w3.org/community/unhosted/wiki/rww-2012.04#simple",'."\n"
        .'    "href":"'.$baseAddress.'/storage/'.$userName.'",'."\n"
        .'    "properties":{'."\n"
        .'      "http://www.w3.org/community/unhosted/wiki/pds-2012.04#oauth2-ig":"'.$baseAddress.'/auth/'.$userName.'",'."\n"
        .'    }'."\n"
        .'  }'."\n"
        .']}'."\n";
    } else {
      header('HTTP/1.0 412 Precondition failed');
      header('Access-Control-Allow-Origin: *');
    }
  }
}
