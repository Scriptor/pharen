<?php
namespace pharen\sql;
require_once('/Users/historium/pharen/lang.php');
use Pharen\Lexical as Lexical;
Lexical::$scopes['sql'] = array();
function sql_connect($user, $pass, $db){
	mysql_connect("localhost", $user, $pass);
	return mysql_select_db($db);
}

function sql_quote($v){
	$val = mysql_real_escape_string($v);
	if(is_string($val)){
		return ("'" . $val . "'");
	}
	else{
		return $val;
	}
}

function sql_vals($pairs){
	return implode(", ", map("pharen\sql\\sql_quote", array_values($pairs)));
}

function sql_cols($pairs){
	return implode(", ", array_keys($pairs));
}

function sql_fetch_by_id($table, $id){
	$__scope_id = Lexical::init_closure("sql", 217);
	$query = sprintf("SELECT * FROM %s WHERE id=%s;", mysql_real_escape_string($table), mysql_real_escape_string($id));
	Lexical::bind_lexing("sql", 217, '$query', $query);
	return mysql_fetch_assoc(mysql_query($query));
}

function sql_insert($table, $pairs){
	mysql_query(sprintf("INSERT INTO %s (%s) VALUES (%s);", mysql_real_escape_string($table), sql_cols($pairs), sql_vals($pairs)));
	return mysql_insert_id();
}

