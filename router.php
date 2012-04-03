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
    if(count($parts)<3) {
	    self::showWelcomePage();
	  } else {
	    switch($parts[1]) {
	    case '.well-known': Webfinger::showWellKnown($uri); break;
	    case 'webfinger': Webfinger::showLrdd($get); break;
	    case 'oauth': Auth::showOAuth($method, $uri, $data, $get); break;
	    case 'storage':  Storage::showStorage($method, $uri, $data, $token); break;
	    default: self::showNotFound($uri);
	    }
	  }
  }
}
