<?php 
require_once('config.php');
require_once('router.php');
if(!Config::$serverHost) {
  Config::$serverHost = $_SERVER['SERVER_NAME'];
}
if(!Config::$serverProtocol) {
  Config::$serverProtocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
}
if(!Config::$usersHost) {
  Config::$usersHost = $_SERVER['SERVER_NAME'];
}
Router::route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_GET, file_get_contents('php://input'));
