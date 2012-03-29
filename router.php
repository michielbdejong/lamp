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
  public static function route($uri) {
	  $parts = explode('/', $uri);
    if(count($parts)<3) {
	    self::showWelcomePage();
	  } else {
	    switch($parts[1]) {
	    case '.well-known': Webfinger::showWellKnown($uri); break;
	    case 'webfinger': Webfinger::showLrdd($uri); break;
	    case 'oauth': Auth::showOAuth($uri); break;
	    case 'storage':  Storage::showStorage($uri); break;
	    default: self::showNotFound($uri);
	    }
	  }
  }
}
