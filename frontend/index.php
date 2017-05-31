<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="content-language" content="en" />
	<title>EditableGrid Demo - Database Link</title>
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
	<link rel="stylesheet" href="css/responsive.css" type="text/css" media="screen">
	<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
	<link rel="stylesheet" href="css/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css" media="screen">
	<link rel="stylesheet" href="css/sds.css" type="text/css" media="screen">

	<script src="js/editablegrid.js"></script>
	<script src="js/editablegrid_renderers.js"></script>
	<script src="js/editablegrid_editors.js"></script>
	<script src="js/editablegrid_validators.js"></script>
	
	<script src="js/editablegrid_utils.js"></script>   
	<script src="js/jquery-1.11.1.min.js" ></script>
	<!-- EditableGrid test if jQuery UI is present. If present, a datepicker is automatically used for date type -->
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
	<script src="js/backend.js" ></script>

 </head>
<body>
	<div id="wrap">
	<h1>Database EditableGrid Demo</h1> 

<?php 
	//,'demo','continent'
	$tables = array('scenario1','scenario2','demo');
	foreach($tables as $table){
		echo <<<HERE
	
	<h1>$table</h1>
	<div class='grid_message' id="${table}_message"></div>
	<div class="grid_toolbar">
		<input type="text" class="grid_filter" name="${table}_filter" placeholder="Filter :type any text here"  />
		<a id="${table}_addbutton" class="grid_addbutton button green"><i class="fa fa-plus"></i> Add new row</a>
	</div>
	
	<!-- Grid contents -->
	<div class='grid' id="$table">
	</div>

	<!-- Paginator control -->
	<div class='paginator' id="${table}_paginator">
	</div>

	<script type="text/javascript">
		var ${table}_grid = new DatabaseGrid('$table','postgres');
	</script>
	<HR>

HERE;
}
?>

	<h2>Sample</h2>
	<div class="foo" id="minimal"></div>

	<script type="text/javascript">
		window.onload = function() {
			sample = new EditableGrid("minimal"); 
			sample.tableLoaded = function() { this.renderGrid("minimal", "excel testgrid"); };
			sample.loadXML("grid.xml");
		} 
	</script>
</body>
</html>
