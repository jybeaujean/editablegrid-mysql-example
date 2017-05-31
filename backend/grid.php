<?php
# Library file related to "grids" and databases.
# grid is an (numbered) array of (associative) arrays
# This is a universal "table" format
# We can pull database resultsets into a grid, and print a grid to HTML, with a single line
# TODO: This should be an object

#	sql_to_grid
#	Generate a grid, or execute, a SQL query
#	Returns FALSE for failure
#	Returns 0 or number of rows for executions
#	Returns "" or resultset grid for selects
function sql_to_grid($sql,$p=array())
{
	$limit = $p[limit];

	$_SESSION[last_error] = "";
	if (!preg_match('/\w/',$sql))
		bug('no query');
	
	$start = get_time();
	$result = mysql_query($sql);
	if ($result===FALSE) {
		$_SESSION[last_error] = "FAILED TO RUN QUERY [$sql], error was: ".mysql_error();
		log_query($sql,$start,-1,$_SESSION[last_error]);
		warning("sql_to_grid:$_SESSION[last_error]");
		return FALSE;
	}
	elseif ($result === TRUE) {
		// TODO: Check inserted/affected rows difference
		$affected_rows = mysql_affected_rows();
		$_SESSION[last_error] = "[$affected_rows] rows affected";
		log_query($sql,$start,$affected_rows,$_SESSION[last_error]);
		return $affected_rows;
	}
	else
	{
		$i = 0;
		$grid = array();
		while ($row = mysql_fetch_assoc($result))
		{
			$i++; 
			$grid[]= $row;
			if ($limit and $i > $limit)
			{
				$_SESSION[last_error] = "limit $limit reached";
				log_query($sql,$start,$limit,$_SESSION[last_error]);
				return $grid;	
			}
		} // end of each row
		if ($i > 0)
		{
			$_SESSION[last_error] = "Found $i rows";
			log_query($sql,$start,$i,$_SESSION[last_error]);
			return $grid;
		}
		else
		{
			$_SESSION[last_error] = "No results found for [$sql]";
			log_query($sql,$start,0,$_SESSION[last_error]);
			return "";
		}
	}
}

# Read a file containing multiple sql commands (seperated by ; and execute them
# Return an array of results for each
function file_sql_to_grids($file)
{
	$content = file_get_contents($file) or fail("Failed to open file");
	$results = array();
	$sqls = explode(';',$content);
	foreach ($sqls as $sql)
	{
		if (preg_match("/\w/",$sql))
			$results[]= sql_to_grid($sql);
	}
}

# Execute SQL command and return array (single field must be queried)
function sql_to_array($sql,$limit="")
{
	$grid = sql_to_grid($sql);
	if ($grid)	{
		$cols = grid_to_cols($grid);
		return $cols[0];
	}
	else
		return $grid;
}

# Retrieve single value
# Possible return values:
# Error issuing: FALSE
# Nothing found: NULL
# 0,'',NULL etc - returned as is
function sql_to_value($sql)
{
	
	$_SESSION[last_error] = "";
	
	$start = get_time();
	$result = mysql_query($sql);
	if ($result===FALSE)	{
		$_SESSION[last_error] = "FAILED TO RUN QUERY [$sql], error was: ".mysql_error();
		log_query($sql,$start,-1,$_SESSION[last_error]);
		warning("sql_to_value:$_SESSION[last_error]");
		return FALSE;
	}
	// Note: php seems to return NULL as empty string
	elseif (@mysql_num_rows($result)) {
		$row = mysql_fetch_row($result);
		$value = $row[0];
		return $value;
	}
	else
		return NULL;
}

# Insert grid into a database table
function grid_to_db_insert($grid,$table)
{
	// TODO:
	$count = 0;
	foreach ($grid as $rec)
	{
		$keys = implode_with_quote(array_keys($rec),"`");
		$values = implode_with_quote(array_values($rec),"'");
		$sql = "insert into `$table` ($keys) VALUES ($values)";
		$result = sql_to_grid($sql);
		if ($result != 1)
			warning("Bad result [$result]");
		$count = $count+$result;
	}
	return $count;
}

#	db_error($msg,$warn)
#	Merges (optional) $msg with $_SESSION[last_error], and fails
#	Fails by default. If $warn set, just warns.
#	Example1 - Fails with last_error
#		$result = sql_to_grid($sql) or fail($_SESSION[last_error]);
#	Example2 - Fails with identifier:last_error
#		$result = sql_to_grid($sql) or db_error("Identifier");

#  Note the above do not differentiate between SQL failure, and no results.
#	Example3
#		$result = sql_to_grid($sql);
#		$result === FALSE and db_error();
#		if (!$result) {
#			db_error("Identifier","warn");
#			continue;
#		}
#
#	Example4
#		$result = sql_to_grid($sql);
#		if ($result === 0)	continue;
#		elseif (!$result)		db_error();
#		if ($debug)				new dbug(array(nl2br($sql),render_grid($result),$_SESSION[last_error]));
#	
#	All
#	if ($debug)
#		new dbug(array(nl2br($sql),render_grid($result),$_SESSION[last_error]));
#
function db_error($msg="",$warn=0)
{
	
	$msg = "$msg:$_SESSION[last_error]";
	if ($warn)
		warning($msg);
	else
		fail($msg);	
}

# Write query information to $_SESSION[queries] for debugging
function log_query($sql,$start,$records) {

	$sql = preg_replace("/\r\n/","<BR>",$sql);
	$query = array(
			'script' => $_SERVER[PHP_SELF],
			'records' => $records,
			'time' => (get_time() - $start)*1000,
			'message' => $_SESSION[last_error],
			'sql' => $sql
		);

	isset($_SESSION['queries']) or $_SESSION['queries'] = array();
	array_push($_SESSION['queries'],$query);
	//dump('queries',$_SESSION[queries]);
	// Write line to file
	
	//Console::log($queries);
	//Console::log($query);
}

# Used by log_query
function get_time() {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$start = $time;
		return $start;
	}

# Retrieve database table structure
// TODO: Check this structure is a proper grid
function describe_table ($table)
{
	$sql = "describe `$table`";
	$resultset = mysql_query($sql) or fail("Failed to run query [$sql], error was ".mysql_error());
	while ($row = mysql_fetch_assoc($resultset))
	{
		$grid[] = $row;
	}
	return $grid;
}

# $html = grid_to_html($grid,$p);
#
# Parameters:
# style: itunes (default), plain, mcp, any other class in css
# no_auto_header
function grid_to_html($grid,$p=array())
{
	//list ($html,$tsv) = render_grid($result,$p);
	list($html,$tsv) = _render_grid($grid,$p);
	return $html;
}
# Also returns TSV version of table
function render_grid_smart($result,$p=array())
{
	list ($html,$tsv) = _render_grid($result,$p);
	return array($html,$tsv);
}

# Internal function for grid_to_html
function _render_grid($grid,$p=array())
{
	
	$id = "";
	if ($p[id]) $id = " id='$p[id]'";
	
	if (!$p['class'])	$p['class'] = 'itunes';
	
	if ($p['style'] == "plain") {
		$html = "<table$id border=1>";
	}
	elseif ($p['style'] == "layout") {
		message("Layout");
		$html = "<table$id border=0>";
		$p[leave_me_alone] = 1;
	}
	elseif ($p['datatable']) {
		$html = "<table$id class='display datatable $p[class]' cellpadding='0' cellspacing='0' border='0' width='100%'>\n";
	}
	else	{
		$html = "<table$id class='$p[class]'>\n";
	}
	
	// Parameters
	// $p[no_auto_header] = 0;
	// $p[metrics_in_first_column] =0;
	// new dbug($p);

	if (!is_array($grid))
	{
		new dbug($grid);
		$_SESSION[last_error] .= "Not a grid";
		return "";
	}

	// By default header is automatically generated from the first row
	// no_auto_header turns this off
	if (!$p[no_auto_header])
	{
		$header = array_keys($grid[0]);
		$header=array("Header"=>1)+$header;
		// new dbug($header);
		array_unshift($grid,$header);
	}
	
	// We iterate through the grid
	// 
	$tsv = array();
	$i=0;	
	foreach ($grid as $row)
	{
		$tr = "tr";
		$header_row = false;
		if ($i==0 and !$p[no_auto_header]) {
			$header_row = true;
			$html .= "<thead>\n";
		}
		$html .= "<$tr>\n";

		$rec = array();
		foreach (array_keys($row) as $key)
		{
			$td = "td";
			if ($header_row) $td = "th";
			$td_params = "";
			$value = $row[$key];
			
			// Special behaviours can be passed by having special column names in the row elements
			if ($key === "Heading")
			{
				// message("colname: [$key]");
				// $html .= "<TD class='$p['style']_heading' colspan='$colspan'>$value</td></tr>\n";
				$html .= "<TR><TD class='header' colspan='1000'>$value</td></tr>\n";
				
				array_push($tsv,array($value));
				continue 2; // next row
			}
			elseif ($key === "Spacer")
			{
				$html .= "<TR><TD colspan='1000'>&nbsp;</td></tr>\n";
				array_push($tsv,array(""));
				continue 2; // next row
			}
			elseif ($key === "Header")
			{
				continue; // next cell
			}
			//if ($key == "TR")
			//{
			//	$tr = "TR $value"; // Used to specify TR level settings
			//	continue; // next cell
			//}
			
			// Used to specify TD level settings
			// TD parameters can be passed by creating a cell as an array, with the data pushed into "TD". The other parameters become TD parameters.
			elseif (is_array($value))
			{
				$href = $value;
				$value = $href[TD];
				foreach ($href as $k=>$v)
				{
					if ($k== 'TD')	continue;
					$td_params .= " $k='$v'";
				}
				
			}
			// Heading: creates a heading row
			// Spacer: Creates a spacer row
			// TR: TD: Should create tr and td level settings for the rest of the row..not tested yet
			$td_start = "$td $td_params";
			
			$metric_name = $key;
			if ($p[metrics_in_first_column])	$metric_name = $first_value;
				// $td .= " class='bold'";
			
			// Header row:
			if ($header_row === true or ($first_col ===true and $p[metrics_in_first_column]))
			{
				$align ="";
				if ($value == "Current" or $value == "%Chg")
					$align = " align='right' ";
				$html .= "<$td_start $align>".htmlspecialchars($value)."</$td>\n";
			}
			elseif(!strlen($value))
			{
				$html .= "<$td_start>&nbsp; </$td>\n";
			}
			// Numbers
			elseif (is_numeric($value))
			{
				// Percentages
				$ratio = 0;
				if (preg_match("/ctr|percent|rate|conv-diff|[%]/i",$metric_name) or
						  preg_match("/\%Change/i",$key))
				{
					$value = $value*100;
					$ratio = 1;
				}
				// Force integer
				if (preg_match("/mav8/i",$key))
					$value = number_format($value);
				// Format the decimals
				elseif(strpos($value,".") == true)
					$value = number_format($value,2);
				else
					$value = number_format($value);
				
				if ($ratio)
					$value = "$value%";
				
				// Currencies
				if (preg_match("/spend|cost|budget|cpc|fee|surplus|rollover|cpa|[$]/i",$metric_name))
					$value = "\$$value";
				
				$html .= "<$td_start align='right'>$value</$td>\n";
			} // End of numbers 
			else
			{
				// This is so bad..can't get a single expression to work
				// To preserve leading spaces in the value
				$hvalue = $value;
				if (!$p[leave_me_alone])
					$hvalue = htmlentities($value);
				$hvalue = preg_replace('/^(\s\s\s\s\s)(\s)(\S)/','$1&nbsp;&nbsp;$3',$hvalue);
				$hvalue = preg_replace('/^(\s\s\s\s)(\s)(\S)/','$1&nbsp;&nbsp;$3',$hvalue);
				$hvalue = preg_replace('/^(\s\s\s)(\s)(\S)/','$1&nbsp;&nbsp;$3',$hvalue);
				$hvalue = preg_replace('/^(\s\s)(\s)(\S)/','$1&nbsp;&nbsp;$3',$hvalue);
				$hvalue = preg_replace('/^(\s)(\s)(\S)/','$1&nbsp;&nbsp;$3',$hvalue);
				$hvalue = preg_replace('/^\s(\S)/','&nbsp;&nbsp;$1',$hvalue);
				// $hvalue = str_replace(" ","&nbsp;&nbsp;",$hvalue);
				$html .= "<$td_start align='left'>$hvalue</TD>\n";
			}
			array_push($rec,$value);
		}
		
		// Finished processing row
		array_push($tsv,$rec);
		$html .= "</tr>\n\n";
		if ($header_row)
			$html .= "</thead>\n<tbody>\n";
		$i++;
	} // end of each row
	
	// End of grid table
	$html .= "</tbody>\n</table>\n";
	return array($html,$tsv);
}

# Issue query and return HTML rendering the resultset
function sql_to_html($sql,$sql_params=array(),$html_params=array())
{
	$grid = sql_to_grid($sql,$sql_params);
	return grid_to_html($grid,$html_params);
}

# Load TSV and print as HTML Table
function tsv_to_html($filename,$html_params)
{
	$grid = tsv_to_grid($filename);
	return grid_to_html($grid,$html_params);
}

#
# TSV FUNCTIONS
#
# load a tab seperated text file as grid
//function to create tsv
function grid_to_tsv($grid,$filename,$mode="w+")
{
	$content = '';
	$write_first_line = true;
	if ($mode == 'append') {
		if (file_exists($filename))	$write_first_line = false;
		$mode = "a+";
	}
		

	//if (file_exists($filename) && !is_writeable($filename)) {
	$fp = fopen($filename,$mode) or fail("Failed to open $filename for writing");
	
	for ($i=0;$i<count($grid);$i++)
	{
		$rec = $grid[$i];
		foreach ($rec as $key=>&$val)
			$val = str_replace("\t", " ", $val);
		
		if ($i==0)
		{
			$keys = array_keys($rec);
			if ($write_first_line) {
				$line = implode("\t", $keys)."\n";
				fwrite($fp, $line);
			}
		}
		$r=array();
		foreach ($keys as $key)
		{
			isset($rec[$key]) or fail("Couldn't find key $key in line $i");
			$r[$key] = $rec[$key];
		}
		$values = array_values($r);
		$line = implode("\t", $values)."\n";
		fwrite($fp, $line);
	}
	fclose($fp);
	return true;
}

// load a tab seperated text file as array
function tsv_to_grid($filename)
{
	if (!file_exists($filename)) {
		fail("File $filename does not exist");
	}

	$content = file($filename);
		
	$grid = array();
	for ($i = 0; $i < count($content); $i++)
	 {
		if (trim($content[$i]) == '')
			next;
		
		$array = explode("\t", trim($content[$i]));
		if($i==0)
			$keys = $array;
		else
		{
			if (count($keys) != count($array))
				//warning("Line $i has incorrect number of keys: ".count($array)." should be ".count($keys));
					  
			for($j=0;$j<count($keys);$j++)
				$rec[$keys[$j]] = $array[$j];
			$grid[]= $rec;
		}
	 }
	 return $grid;
}



#
#	GRID COLUMN-BASED FUNCTIONS
# 

# Converts grid to an array, where each row is keyed by column name,
# and contains all the column data values
function grid_to_cols($grid,$type="numeric")
{
	if (!isset($grid) or $grid=="")	{
		warning("Grid undefined");
		return array();
	}
	
	// Convert data to columns
	$keys = array_keys($grid[0]);
	$key_count = count($keys);
	$d = array();
	$grid_count = count($grid);
	if ($grid_count)
	{
		foreach ($grid as $row)
		{
			for ($i=0;$i<$key_count;$i++)
			{
				$key = $keys[$i];
				$value = $row[$keys[$i]];
				//if ($value == '')
				//	$value = 0;
				if ($type == 'numeric')
					$d[$i][] = $value;
				else
					$d[$key][] = $value;
			}
		}
	}
	return $d;
}

# Returns grid column named $col
function grid_column($grid,$col)	{
	$cols = grid_to_cols($grid,"hash");
	return $cols[$col];
}

# Returns first column of grid
function grid_first_column($grid)	{
	$cols = grid_to_cols($grid);
	return $cols[0];
}

# Deletes column $col
function del_column(&$grid,$col)	{
	foreach ($grid as &$row)
	{
		unset ($row[$col]);
	}
	return $grid;
}

# Adds a total row to the grid
function add_total($grid)	{
	// new dbug($grid);
	$keys = array_keys($grid[0]);
	$key_count = count($keys);

	$total = array();
	foreach ($grid as $row)
	{
		for ($i=0;$i<$key_count;$i++)
		{
			$key = $keys[$i];
			$value = $row[$keys[$i]];
			$total[$key] += $value;
		}
	}
	$total[$keys[0]] = "Total";
	foreach ($keys as $col)
	{
		if (in_array($col,array("Mth","Week Starting","Day")))
			$total[$col] = "";
		$total[$col] = array("class"=>"bold","TD"=>$total[$col]);
	}
	$grid[]= $total;
	return $grid;
}

# Adds an initial column to grid, with row number
function add_number(&$data)
{
	$i=1;
	foreach ($data as &$row)
	{
		array_unshift($row,$i);
		$i++;
	}
}

# Lookup a value in a grid structure
# Return the record if found, false if not.
function table_lookup($grid,$field,$value)
{
	foreach ($grid as $rec)
	{
		if ($rec[$field] === $value)
			return $rec;
	}
	return FALSE;
}


#
#	ARRAY FUNCTIONS
#
# Converts a normal array into a grid
function array_to_grid($array,$header="value")	{
	$count = count ($array);
	$grid = array();
	for ($i = 0; $i < $count ; $i++)
	{
		$value = $array[$i];
		$grid[] = array('#'=>$i,$header => $value);
	}
	//new dbug($grid);
	return $grid;
}

# Print an array as HTML
function array_to_html($array,$p=array())	{
	return grid_to_html(array_to_grid($array),$p);
}

# Find average value of an array of numbers
function array_average($list)
{
	foreach ($list as $number)
	{
		$total += $number;
	}
	$count = count($list);
	$average = @$total/$count;
	return $average;
}

#	$hash = array_to_hash($array);
#	Reverses the array.
#	i.e. values are keys, index position is value
function array_to_hash($array)
{
	$hash = array();
	for($i=0;$i<count($array);$i++)
	{
		$value = $array[$i];
		if (isset($value))
			$hash[$value] = $i;
	}
	return $hash;
}

?>
