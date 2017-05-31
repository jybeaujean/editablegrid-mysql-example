/**
 *  highlightRow and highlight are used to show a visual feedback. If the row has been successfully modified, it will be highlighted in green. Otherwise, in red
 */
 
var backend = '../backend/backend.php';

function highlightRow(rowId, bgColor, after) {
	var rowSelector = $("#" + rowId);
	rowSelector.css("background-color", bgColor);
	rowSelector.fadeTo("normal", 0.5, function() {
		rowSelector.fadeTo("fast", 1, function() {
			rowSelector.css("background-color", '');
		});
	});
}

function highlight(div_id, style) {
	highlightRow(div_id, style == "error" ? "#e5afaf" : style == "warning" ? "#ffcc00" : "#8dc70a");
}

function log(op,type,message, table=null) {
	debug = 1;
	line = op + ":" + type + ':' + message;
	//if (debug && type === 'ok') {
	console.log(line);
	if(type == 'error') {
		if(!table)
			table = editableGrid.name;
		show_message(table, line);
	}
}

function show_message(table,msg){
	var self = this;
	//console.log('message:', msg);
	var msgId = table+'_message';
	var x = $('#'+msgId);
	x.html(msg);
	x.fadeTo("normal", 0.5, function() {
			x.fadeTo(5000, 1, function() {
			x.html('');
			});
	});
};


/**
	 updateCellValue calls the PHP script that will update the database. 
 */


function updateCellValue(editableGrid, rowIndex, columnIndex, oldValue, newValue, row, onResponse) {
	$.ajax({
		url: editableGrid.getActionUrl('update'),
		type: 'POST',
		dataType: "html",
		data: {
			tablename: editableGrid.name,
			id: editableGrid.getRowId(rowIndex),
			newvalue: editableGrid.getColumnType(columnIndex) == "boolean" ? (newValue ? 1 : 0) : newValue,
			colname: editableGrid.getColumnName(columnIndex),
			coltype: editableGrid.getColumnType(columnIndex)
		},
		success: function(response) {
			// reset old value if failed then highlight row
			var success = onResponse ? onResponse(response) : ((response.indexOf("error")<0) || !isNaN(parseInt(response))); // by default, a sucessfull reponse can be "ok" or a database id 
			if (!success) editableGrid.setValueAt(rowIndex, columnIndex, oldValue);
			highlight(row.id, success ? "ok" : "error");
      log('update','ok','updated' + row.id + 'set value x to y');
      //show_message(editableGrid.name, response);
		},
		error: function(XMLHttpRequest, textStatus, exception) {
			//alert("Ajax failure\n" + errortext);
				log('update','error',exception);
				//show_message(editableGrid.name, exception)
				//console.log('error:', exception);
		},
		async: true
	});
}

function DatabaseGrid(table,config) {
// 	isset(table) or die;
//	isset(config) or die;
	var that = this;
	this.editableGrid = new EditableGrid(table, {
		enableSort: true,
		pageSize: 10,
		// Once the table is displayed, we update the paginator state
		tableRendered: function() {
			updatePaginator(this, table + '_paginator');
		},
		tableLoaded: function() {
			that.initializeGrid(this, table);
		},
		modelChanged: function(rowIndex, columnIndex, oldValue, newValue, row) {
			updateCellValue(this, rowIndex, columnIndex, oldValue, newValue, row);

		},
        dbconfig: config,
        getActionUrl: function(action){
    
            var self = this;
            var url= backend + '?action='+action+'&profile='+self.dbconfig+'&table='+self.name;
            return url;
        },
        
	});
	this.fetchGrid(table,config);
}

DatabaseGrid.prototype.fetchGrid = function(table,config) {
	// call a PHP script to get the data
	url = backend + "?action=load&profile="+config+ "&table=" + table;
	this.editableGrid.loadJSON(url);
};

DatabaseGrid.prototype.initializeGrid = function(grid, table) {
	var self = this;
    //self.config = config;

	// render for the action column
	grid.setCellRenderer("action", new CellRenderer({
		render: function(cell, id) {
			cell.innerHTML += "<a href='#' class='delete-row' ><i  class='fa fa-trash-o red' ></i></a>";
			cell.innerHTML += "  <a href='#' class='copy-row' ><i  class='fa fa-copy' ></i></a>";
		}
	}));

	function get_table_id(ele) {
		var id = ele.closest('tr').prop('id');
		var x = id.split('_');
		id = x.pop();
		var table = x.join('_');
		return {
			id: id,
			table: table
		};
	}

	$('body').on('click', '.delete-row', function(e) {
		var xdata = get_table_id($(this));

		//console.log('row id: ',  xdata);
		if (xdata.table == self.editableGrid.name)
			self.deleteRow(xdata.id);
	});
	$('body').on('click', '.copy-row', function(e) {
		var xdata = get_table_id($(this));

		//console.log('duplicateRow id: ',  xdata);
		if (xdata.table == self.editableGrid.name)
			self.duplicateRow(xdata.id);
	});

	//console.log('table is: ' + table);
	//	grid.renderGrid('demo',"testgrid");
	// add
	$(".grid_addbutton").click(function(e) {
		if ($(this).prop('id').indexOf(self.editableGrid.name) >= 0) {
			//console.log('adding row');
			self.addRow();
		};
	});

	//filter
	$(".grid_filter").keyup(function(e) {
		if ($(e.target).prop('name').indexOf(self.editableGrid.name) >= 0) {
			console.log('grid_filter:', e.target);
			self.editableGrid.filter($(this).val());
		}
		// To filter on some columns, you can set an array of column index 
		//datagrid.editableGrid.filter( $(this).val(), [0,3,5]);
	});
	grid.renderGrid(table,'excel testgrid');
};

DatabaseGrid.prototype.deleteRow = function(id) {
	var self = this;

	if (confirm('Are you sure you want to delete the row id ' + id)) {
		$.ajax({

			url: self.editableGrid.getActionUrl('delete'),
		type: 'POST',
			type: 'POST',
			dataType: "html",
			data: {
				tablename: self.editableGrid.name,
				id: id,
			},
			success: function(response) {
				if (response == "ok"){
					self.editableGrid.removeRow(id);
					log('deleteRow','ok', "RowId:"+id +" has been removed!", self.editableGrid.name);
				}
					
			},
			error: function(XMLHttpRequest, textStatus, exception) {
				//alert("Ajax failure\n" + errortext);
        //show_message(self.editableGrid.name,errortext);
				log('deleteRow','error', errortext, self.editableGrid.name);
			},
			async: true
		});
	}
};

DatabaseGrid.prototype.duplicateRow = function(id) {
	var self = this;
	$.ajax({

		url: self.editableGrid.getActionUrl('duplicate'),
		type: 'POST',
		dataType: "html",
		data: {
			table: self.editableGrid.name,
			id: id,
		},
		success: function(response) {
			if (response && response.indexOf('error') <0) {
				//alert("Row duplicated : reload model");
        show_message(self.editableGrid.name,"Row duplicated : reload model");
				//console.log("Row duplicated");
				self.fetchGrid(self.editableGrid.name);
				log('duplicateRow','ok', "RowId:"+id +" has been duplicated!", self.editableGrid.name);
			}else{
				log('duplicateRow','error', response, self.editableGrid.name);
			}

		},
		error: function(XMLHttpRequest, textStatus, exception) {
			//alert("Ajax failure\n" + errortext);
      //show_message(self.editableGrid.name,errortext);
			log('duplicateRow','error', errortext, self.editableGrid.name);
		},
		async: true
	});
};



DatabaseGrid.prototype.addRow = function(id) {
	var self = this;
	$.ajax({

		url: self.editableGrid.getActionUrl('add'),
		type: 'POST',
		dataType: "html",
		data: {
			tablename: self.editableGrid.name,
		},
		success: function(response) {
			if (response.indexOf("error") < 0) {
				var row = jQuery.parseJSON(response);
				// hide form
				//showAddForm();   
				//form.find("input[type=text]").val("");
				var id = row.id;
				//alert("Row added : reload model:" + id);
                show_message(self.editableGrid.name,"Row added : reload model:" + id);
				//self.fetchGrid(self.editableGrid.name);
				var rowIndex = self.editableGrid.pageSize < 0 ? 0 : self.editableGrid.pageSize;
				var rowNum = self.editableGrid.getRowCount();
				rowIndex = Math.min(rowIndex, rowNum);
				//var row = self.editableGrid.getRowValues(rowIndex);
				//row.id = id;
				//console.log('rowCount:', rowIndex, ' id:', id);
				self.editableGrid.insert(rowIndex - 1, id, row, null, true);
				log('addRow','ok', "Row:" + id + " has been added", self.editableGrid.name);
			} else{
					//alert("error");
					//show_message(self.editableGrid.name,response);
					log('addRow','error', response, self.editableGrid.name);
      }
				
		},
		error: function(XMLHttpRequest, textStatus, exception) {
			//alert("Ajax failure\n" + errortext);
            //show_message(self.editableGrid.name,errortext);
						log('addRow','error', exception, self.editableGrid.name);
		},
		async: true
	});
};

function updatePaginator(grid, divId) {
	divId = divId || "paginator";
	var paginator = $("#" + divId).empty();
	var nbPages = grid.getPageCount();

	// get interval
	var interval = grid.getSlidingPageInterval(20);
	if (interval == null) return;

	// get pages in interval (with links except for the current page)
	var pages = grid.getPagesInInterval(interval, function(pageIndex, isCurrent) {
		if (isCurrent) return "<span id='currentpageindex'>" + (pageIndex + 1) + "</span>";
		return $("<a>").css("cursor", "pointer").html(pageIndex + 1).click(function(event) {
			grid.setPageIndex(parseInt($(this).html()) - 1);
		});
	});

	// "first" link
	var link = $("<a class='nobg'>").html("<i class='fa fa-fast-backward'></i>");
	if (!grid.canGoBack()) link.css({
			opacity: 0.4,
			filter: "alpha(opacity=40)"
		});
	else link.css("cursor", "pointer").click(function(event) {
			grid.firstPage();
		});
	paginator.append(link);

	// "prev" link
	link = $("<a class='nobg'>").html("<i class='fa fa-backward'></i>");
	if (!grid.canGoBack()) link.css({
			opacity: 0.4,
			filter: "alpha(opacity=40)"
		});
	else link.css("cursor", "pointer").click(function(event) {
			grid.prevPage();
		});
	paginator.append(link);

	// pages
	for (p = 0; p < pages.length; p++) paginator.append(pages[p]).append(" ");

	// "next" link
	link = $("<a class='nobg'>").html("<i class='fa fa-forward'>");
	if (!grid.canGoForward()) link.css({
			opacity: 0.4,
			filter: "alpha(opacity=40)"
		});
	else link.css("cursor", "pointer").click(function(event) {
			grid.nextPage();
		});
	paginator.append(link);

	// "last" link
	link = $("<a class='nobg'>").html("<i class='fa fa-fast-forward'>");
	if (!grid.canGoForward()) link.css({
			opacity: 0.4,
			filter: "alpha(opacity=40)"
		});
	else link.css("cursor", "pointer").click(function(event) {
			grid.lastPage();
		});
	paginator.append(link);
};


function showAddForm() {
	if ($("#addform").is(':visible'))
		$("#addform").hide();
	else
		$("#addform").show();
}