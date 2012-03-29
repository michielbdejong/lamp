<?php 
require_once('router.php');
Router::route($_SERVER['REQUEST_URI']);
