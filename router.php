<?php
require_once 'webfinger.php';
require_once 'auth.php';
require_once 'storage.php';

class Router {
  static function showWelcomePage() {
    echo 'Welcome!';
  }
  static function showNotFound($uri) {
     echo htmlentities($uri).' not found';
  }
  public static function route($method, $uri, $data, $get, $token) {
	  $parts = explode('/', $uri);
    debug($parts);
    if(count($parts)<3) {
	    self::showWelcomePage();
	  } else {
      debug($parts[1]);
      debug($get);
      debug($method);
      debug($uri);
      debug($token);
	    switch($parts[1]) {
	    case '.well-known': Webfinger::showJrd($get); break;
	    case 'oauth': Auth::showOAuth($method, $uri, $data, $get); break;
	    case 'storage':  Storage::showStorage($method, $uri, $data, $token); break;
	    default: self::showNotFound($uri);
	    }
	  }
  }
}
