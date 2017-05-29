<?php
 
/*
 * 
 * http://editablegrid.net
 *
 * Copyright (c) 2011 Webismymind SPRL
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://editablegrid.net/license
 */
      
require_once('config.php');         

// Database connection                                   
$mysqli = mysqli_init();
$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
$mysqli->real_connect($config['db_host'],$config['db_user'],$config['db_password'],$config['db_name']); 

// Get all parameter provided by the javascript
$name = $mysqli->real_escape_string(strip_tags($_POST['name']));
$firstname = $mysqli->real_escape_string(strip_tags($_POST['firstname']));
$tablename = $mysqli->real_escape_string(strip_tags($_POST['tablename']));

$return=false;
if ( $stmt = $mysqli->prepare("INSERT INTO ".$tablename."  (name, firstname) VALUES (  ?, ?)")) {

	$stmt->bind_param("ss", $name, $firstname);
    $return = $stmt->execute();
	$stmt->close();
}             
$mysqli->close();        

echo $return ? "ok" : "error";

      

