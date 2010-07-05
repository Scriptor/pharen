<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['sql'] = array();
function sql_connect($user, $pass, $db){
	Lexical::$scopes["sql"][1] = array();
	mysql_connect("localhost", $user, $pass);
	return mysql_select_db($db);
}
function sql_vals($pairs){
	Lexical::$scopes["sql"][2] = array();
	return implode(", ", array_values($pairs));
}
function sql_cols($pairs){
	Lexical::$scopes["sql"][3] = array();
	return implode(", ", array_keys($paris));
}
function sql_fetch_by_id($table, $id){
	Lexical::$scopes["sql"][4] = array();
	$query = sprintf("SELECT * FROM %s WHERE id=%s;", mysql_real_escape_string($table), mysql_real_escape_string($id));
	return mysql_fetch_assoc(mysql_query($query));
}
function sql_insert($table, $pairs){
	Lexical::$scopes["sql"][5] = array();
	return mysql_query(sprintf("INSERT INTO %s (%s) VALUES (%s);", mysql_real_escape_string($table), sql_cols($pairs), sql_vals($pairs)));
}
