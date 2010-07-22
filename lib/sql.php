<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['sql'] = array();
	$__scope_id = Lexical::init_closure("sql", 39);
function sql_connect($user, $pass, $db){
	$__scope_id = Lexical::init_closure("sql", 40);
	mysql_connect("localhost", $user, $pass);
	return mysql_select_db($db);
}

function sql_vals($pairs){
	$__scope_id = Lexical::init_closure("sql", 41);
	return implode(", ", array_values($pairs));
}

function sql_cols($pairs){
	$__scope_id = Lexical::init_closure("sql", 42);
	return implode(", ", array_keys($paris));
}

function sql_fetch_by_id($table, $id){
	$__scope_id = Lexical::init_closure("sql", 43);
	$query = sprintf("SELECT * FROM %s WHERE id=%s;", mysql_real_escape_string($table), mysql_real_escape_string($id));
	return mysql_fetch_assoc(mysql_query($query));
}

function sql_insert($table, $pairs){
	$__scope_id = Lexical::init_closure("sql", 44);
	return mysql_query(sprintf("INSERT INTO %s (%s) VALUES (%s);", mysql_real_escape_string($table), sql_cols($pairs), sql_vals($pairs)));
}

