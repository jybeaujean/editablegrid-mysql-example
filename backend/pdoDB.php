<?php

class DBClass{
//private $pdh;
//private $db_type;
//private $db_name;
//private $db_host;
//private $db_password;
//private $db_user;
//private $db_table;
private $schema;

function __construct($config) {
	$params = array('db_type','db_host','db_name','db_user','db_password');
	//TODO: Add these back into the object
	//foreach isset check or fail
	$this->db_type = $config['db_type'];
	$this->dbh = new PDO(sprintf('%s:host=%s;dbname=%s', $config['db_type'],$config['db_host'],$config['db_name']), $config['db_user'], $config['db_password']);
	
	if(isset($config['db_schema']) and $config['db_type'] == 'pgsql') {
		$schema = $config['db_schema'];
		$this->dbh->exec("SET search_path TO $schema");
		$this->schema = $schema;
	}
	else {
		$this->schema = $config['db_name'];
	}
}

public function get_meta($table, $forced=FALSE){
	$dir = sprintf("%s/profiles/%s", dirname(__FILE__), $this->db_type == 'mysql'?$this->db_type:'postgres');
	$file = sprintf("%s/%s.spec.tsv", $dir, $table);
	debug('get_meta','here');
	if(!file_exists($file) || $forced){
		$file = sprintf("%s/%s.tsv", $dir, $table);
		if(!file_exists($file)|| $forced) {
			### create it here
			$query=file_get_contents("$dir/meta.sql");
			if(!$query){
				fail('get_meta',"$dir/meta.sql not found");
			}
			$query = sprintf($query, $table, $this->schema);
			debug('get_meta','debug',$query);
			$result = $this->dbh->query($query);
			//$rows= array();
			$rows = $result->fetchAll(PDO::FETCH_ASSOC);
			debug('get_meta',"result of $rows rows",print_r($rows,TRUE));
			$data = array();
			$i = 0;
			file_put_contents($file, "");
			foreach($rows as $k => $v){
				$this->print_meta_row($v,$i++, $file);
			}
		}
	}
	## load and parse it, cache it??
	return $this->parse_meta_file($file);
}

public function print_meta_row($row, $count,$file){
	$h=array();
	$cols= array();
	foreach($row as $k => $v){
		$h[] =$k;
		// modify grid_type
		if($k === 'type'){
			$v =  get_col_type($v, $cols[2]);
		}
		$cols[]  = $v;
	}
	if($count <1)	{
		file_put_contents($file, join("\t", $h) . "\n",FILE_APPEND);
	}
	file_put_contents($file, join("\t", $cols) . "\n",FILE_APPEND);
}

public function parse_meta_file($file, $sep="\t"){
	$csv = array();
	$handle = fopen($file, "r");
	$c=0;
	while ($data = fgetcsv($handle, 99999, $sep)){
		if($data !== FALSE)
			$csv[$c++] = $data;
	}
	//return $rows ;
	debug('parse_meta_file', $file, $csv);
	$header = array_shift($csv);
	//var_dump($header);
	array_walk($csv, function(&$a) use ($header) {
		//var_dump($a);
		$a = array_combine($header, $a);
	});
	// now, use name as key
	$rows= array();
	foreach($csv as $k => $v){
		$rows[$v['field']] = $v;
	}
	return $rows;
}

public function quote_identifier($field) {
	$type = $this->db_type; 
	if ($type == 'mysql') {
		return "`".str_replace("`","``",$field)."`";
	}
	elseif($type == 'pgsql') {
		return '"'.$field.'"';
	}
	else {
		fail("Unrecognised type $type");
	}
}

public function zzclean_arg($arg,$type) {
	if ($type == 'value') {
		return $this->dbh->quote(strip_tags($_POST[$arg]));
	}
	elseif ($type == 'field') {
		return $this->quote_identifier(strip_tags($_POST[$arg]));
	}
	else {
		fail("Invalid args");
	}
}

public function query($query, $args=array()){
	return $this->dbh->query($query, PDO::FETCH_ASSOC);
}

public function fetch_pairs($query){
	if (!($res = $this->dbh->query($query, PDO::FETCH_ASSOC))) return FALSE;
	$rows = array();
	while ($row = $res->fetch()) {
		$first = true;
		$key = $value = null;
		foreach ($row as $val) {
			if ($first) { $key = $val; $first = false; }
			else { $value = $val; break; } 
		}
		$rows[$key] = $value;
	}
	return $rows;
}

public function get_column_meta($table){
	$rs = $this->dbh->query(sprintf("select * from %s limit 1", $table));
	
	$meta = array();
	for($i=0; $i< $rs->columnCount(); $i++){
		$meta[] = $rs->getColumnMeta($i);
	}
	// for compatible with all other 
	//$rs  = $this->dbh->query("desc $table", PDO::FETCH_ASSOC);
	//return $rs->fetchAll();
	return $meta;
	
}

public function get_table_columns($table){
	$meta = $this->get_column_meta($table);
	$cols = array();
	foreach($meta as $i => $v){
		//$k = $v["Field"];
		//$t= $v["Type"];
		$k = $v["name"];
		$cols[$k] = $v; 
	}
	return $cols;
}

public function get($id, $table){
	$rs = $this->dbh->query(sprintf("select * from %s where id=%s", $table, $id), PDO::FETCH_ASSOC);
	return $rs->fetch(PDO::FETCH_ASSOC);
}

public function add(){
	global $_POST;
	$tablename = strip_tags($_POST['tablename']);
	//$tablename = 'demo';
	return $this->insert($tablename, $_POST);
}

public function insert($tablename, $values, $cols=FALSE, $excl_cols=array()) {
	
	if($cols === FALSE)	{
			$cols = $this->get_meta($tablename);
	}
	//$cols = $this->get_table_columns($tablename);
	//debug('insert cols',print_r($cols,TRUE),'ok');
	$fields = array();
	$new_values = array();
	foreach($cols as $k => $v){
		// just simple ignore 'id', if it is primray key
		//if(array_key_exists($k,$excl_cols))
		if($cols[$k]['column_key'] == 'PRIMARY KEY') {
			continue;
		}
		$fields[] = $this->quote_identifier($k);
		if(array_key_exists($k, $values)){
			$new_values[] = $this->dbh->quote(strip_tags($values[$k]));
		}else{
			$new_values[] = 'default';
		}
	}
	$query = sprintf("INSERT INTO %s  (%s) VALUES (%s)", $tablename, join(',', $fields), join(',',$new_values)); 

	//file_put_contents('update.log', $query ."\n");
	debug('insert',$query, 'ok');
	if($this->dbh->query($query)){
		$id = $this->dbh->lastInsertId();
		return $this->get($id, $table);
	}
	return FALSE;
}




public function update(){
	global $_POST;
	$table = $this->quote_identifier(strip_tags($_POST['tablename']));
	$field = $this->quote_identifier(strip_tags($_POST['colname']));
	$id = $this->dbh->quote(strip_tags($_POST['id']));
	$value = $this->dbh->quote(strip_tags($_POST['newvalue']));
	$coltype = strip_tags($_POST['coltype']);
	if($coltype == 'date'){
		if ($value === "")
			$value = NULL;
		else{
			$date_info = date_parse_from_format('d/m/Y', $value);
			$value = "{$date_info['year']}-{$date_info['month']}-{$date_info['day']}";
			$value = $this->dbh->quote($value);
		}
	}
	$query = sprintf("UPDATE %s SET %s=%s WHERE id = %s", $table, $field, $value, $id );
	$result = $this->dbh->query($query);
	debug('update',$query,$result);
	return $result;
}

public function delete(){
	global $_POST;
	$table = strip_tags($_POST['tablename']);
	$id = $this->dbh->quote(strip_tags($_POST['id']));
	$query = sprintf("DELETE FROM %s  WHERE id = %s", $table, $id );
	$result = $this->dbh->query($query);
	debug('delete',$query,$result);
	return $result;
}


public function duplicate(){
	global $_POST;
	$tablename = strip_tags($_POST['table']);
	$cols = $this->get_meta($tablename);
	//$fields = join(',',array_diff(array_keys($cols), ['id']));
	$id = $this->dbh->quote(strip_tags($_POST['id']));
	
	
	$query = sprintf("SELECT * FROM %s WHERE id=%s", $tablename,  $id );
	$result = $this->dbh->query($query, PDO::FETCH_ASSOC);
	if($result === FALSE)
		return $result;
	
	$row = $result->fetch();
	debug('duplicate',$query,print_r($row, TRUE));
	return $this->insert($tablename, $row, $cols, array('id' => 1));
}

//what is this?
public function duplicatex(){
	global $_POST;
	$table = strip_tags($_POST['table']);
	$cols = $this->get_table_columns($table);
	$fields = join(',',array_diff(array_keys($cols), ['id']));
	$id = $this->dbh->quote(strip_tags($_POST['id']));
	
	
	$query = sprintf("INSERT INTO %s (%s) SELECT %s FROM %s WHERE id=%s", $table, $fields, $fields, $table, $id );
	$result = $this->dbh->query($query);
	debug('duplicatex',$query,$result);
	return $result;
}


public function zzlist_tables(){
	try {   
			$tableList = array();
			$result = $this->dbh->query("SHOW TABLES");
			while ($row = $result->fetch(PDO::FETCH_NUM)) {
				$tableList[] = $row[0];
			}
	}
	catch (PDOException $e) {
		echo $e->getMessage();
	}
	return $tableList;
}

public function errorInfo(){
	$err = $this->dbh->errorInfo();
	debug('errorInfo',$err[0],$err[1],$err[2]);
	return $err[2];
}

}
