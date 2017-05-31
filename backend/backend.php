<?php	 
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once('db_utils.php');

$action = (isset($_GET['action'])) ? stripslashes($_GET['action']) : fail ("no action");
$table = (isset($_GET['table'])) ? stripslashes($_GET['table']) : fail ("No table");
$profile = (isset($_GET['profile'])) ? stripslashes($_GET['profile']) : fail("No profile");

require_once("profiles/$profile/config.php");
isset($config) or fail("Invalid config");
require_once('EditableGrid.php');
require_once('pdoDB.php');

$db = new DBClass($config);
isset($db) or fail("backend","No DB");


// create a new EditableGrid object
if($action == 'add'){
	$return = $db->add($table);
	//var_dump($return);
	// why doesn't this return json
	if($return){
		$json = json_encode($return);
		//file_put_contents('update.log', $json ."\n");
		echo $json;
	}
  else{
		echo json_encode(array('error' => $db->errorInfo()));
	}
	//echo $return ? $data . $return : "error";  
}

if ($action == 'update'){
	$return = $db->update($table);
	echo $return ? "ok" : json_encode(array('error' => $db->errorInfo()));
}

if ($action == 'delete'){
	$return = $db->delete($table);
	echo $return ? "ok" : json_encode(array('error' => $db->errorInfo()));
}

if ($action == 'duplicate'){
	$return = $db->duplicate($table);
	echo $return ? "ok" : json_encode(array('error' => $db->errorInfo()));
	
}
if ($action == 'load') {
	$grid = new EditableGrid();
	$sql = "SELECT * FROM $table";
	$result = $db->query($sql);
	$result or fail('load','select failed'); //TODO: Show this properly
	add_columns_from_meta($result,$grid,$table);
	//var_dump($meta);fail;
	// send data to the browser
	$grid->renderJSON($result);
}

/* 
*  Add columns. The first argument of addColumn is the name of the field in the databse. 
*  The second argument is the label that will be displayed in the header
*/
/*
$grid->addColumn('id', 'ID', 'integer', NULL, false); 
$grid->addColumn('name', 'Name', 'string');  
$grid->addColumn('firstname', 'Firstname', 'string');  
$grid->addColumn('age', 'Age', 'integer');  
$grid->addColumn('height', 'Height', 'float');  
*/

/* The column id_country and id_continent will show a list of all available countries and continents. So, we select all rows from the tables */
/*
$grid->addColumn('id_continent', 'Continent', 'string' , $db->fetch_pairs('SELECT id, name FROM continent'),true);  
$grid->addColumn('id_country', 'Country', 'string', $db->fetch_pairs('SELECT id, name FROM country'),true );  
$grid->addColumn('email', 'Email', 'email');											   
$grid->addColumn('freelance', 'Freelance', 'boolean');  
$grid->addColumn('lastvisit', 'Lastvisit', 'date');  
$grid->addColumn('website', 'Website', 'string');  
$grid->addColumn('action', 'Action', 'html', NULL, false, 'id');  
*/
