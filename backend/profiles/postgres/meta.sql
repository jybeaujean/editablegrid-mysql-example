select  tab_columns.table_schema as "schema",tab_columns.table_name as "table", tab_columns.column_name as field, tab_columns.column_name as label, tab_columns.ordinal_position as "order", tab_columns.data_type as type, tab_columns.character_maximum_length, tab_constraints.constraint_type as column_key, tab_columns.column_default as extra,  1 as display, 1 as editable from information_schema.columns AS tab_columns 
LEFT OUTER JOIN 
information_schema.constraint_column_usage AS col_constraints 
ON tab_columns.table_name = col_constraints.table_name AND 
tab_columns.column_name = col_constraints.column_name 
LEFT OUTER JOIN 
information_schema.table_constraints AS tab_constraints 
ON tab_constraints.constraint_name = col_constraints.constraint_name
where tab_columns.table_name = '%s' and tab_columns.table_schema = '%s' order by ordinal_position;