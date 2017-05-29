select 
table_schema as "schema",table_name as "table",
column_name as field,
column_name as label,
ordinal_position as "order",
data_type as type,character_maximum_length,
column_key,extra,
1 as display,
1 as editable
from information_schema."columns"
where table_name = 'scenario1'
order by ordinal_position
