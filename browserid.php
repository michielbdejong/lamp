<?php

class Browserid {
  public static function verifyAssertion($assertion, $audience) {
    $url = 'https://browserid.org/verify';
    $params = 'assertion='.$assertion.'&audience=' . $audience;

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch,CURLOPT_POST,2);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $params);
    $result = curl_exec($ch);
    curl_close($ch);
    echo $result;

    return false;
  }
}
