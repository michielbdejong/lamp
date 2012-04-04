<?php

class Db {
  private static $link;
  private static function createDb() {
    mysql_query('CREATE DATABASE '.Config::$mysqlDb, self::$link)
        or die('could not create db "'.Config::$mysqlDb.'" '.mysql_error());
    mysql_select_db(Config::$mysqlDb)
        or die('could not select db "'.Config::$mysqlDb.'" '.mysql_error());
    self::doQuery('CREATE TABLE address ('
      .'primary_address VARCHAR(255), '
      .'secondary_address VARCHAR(255), '
      .'PRIMARY KEY(primary_address)'
      .')');
    self::doQuery('CREATE TABLE grants ('
      .'user_address VARCHAR(255), '
      .'token VARCHAR(255), '
      .'client_id VARCHAR(255), '
      .'scope VARCHAR(255), '
      .'PRIMARY KEY(user_address, client_id),'
      .'INDEX(token)'//to speed up queries with: WHERE token='whatever'
      .')');
    self::doQuery('CREATE TABLE items ('
      .'user_address VARCHAR(255), '
      .'category VARCHAR(255), '
      .'zone INT, '
      .'key_name VARCHAR(255), '
      .'value BLOB, '
      .'PRIMARY KEY(user_address, category, zone, key_name)'
      .')');
  }
  private static function getLink() {
    if(!self::$link) {
      self::$link = mysql_connect(Config::$mysqlHost, Config::$mysqlUsr, Config::$mysqlPwd)
        or die('could not connect '.mysql_error());
    }
    mysql_select_db(Config::$mysqlDb) or self::createDb();
  }
  private static function doQuery($query) {
    error_log($query);
    self::getLink();
    $result = mysql_query($query, self::$link) or die('Query failed: '. mysql_error());
    return $result;
  }
  public static function getStrings($columns, $table, $whereClauses) {
    $cols = array();
    $clauses = array();
    foreach($columns as $c) {
      array_push($cols, mysql_escape_string($c));
    }
    foreach($whereClauses as $whereField => $whereValue) {
      array_push($clauses, mysql_escape_string($whereField).'="'.mysql_escape_string($whereValue).'"');
    }
    $result = self::doQuery('SELECT '.implode(',', $cols)
      .' FROM '.mysql_escape_string($table)
      .' WHERE ('.implode(') AND (', $clauses).')');
    if(mysql_num_rows($result) != 1) {
      return null;
    }
    $line = mysql_fetch_array($result, MYSQL_NUM);
    return $line;
  }
  public static function setStrings($columns, $table, $whereClauses) {
    $pairsUpdate = array();
    foreach($columns as $k => $v) {
      $pairsUpdate[mysql_escape_string($k)] = mysql_escape_string($v);
    }
    $pairs = $pairsUpdate;
    foreach($whereClauses as $k => $v) {
      $pairs[mysql_escape_string($k)] = mysql_escape_string($v);
    }
    $str = 'INSERT INTO '.mysql_escape_string($table)
      .' ('.implode(', ', array_keys($pairs))
      .') VALUES ("'.implode('", "', $pairs).'")'
      .' ON DUPLICATE KEY UPDATE ';
    foreach($pairsUpdate as $k => $v) {
      $str .= $k.' = "'.$v.'"';
    }
    return self::doQuery($str);
  }
  public static function deleteRow($table, $whereClauses) {
    $clauses = array();
    foreach($whereClauses as $whereField => $whereValue) {
      array_push($clauses, mysql_escape_string($whereField).'="'.mysql_escape_string($whereValue).'"');
    }
    return self::doQuery('DELETE FROM '.mysql_escape_string($table).' WHERE ('
      .implode(') AND (', $clauses).')');
  }
}
