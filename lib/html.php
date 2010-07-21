#! /usr/bin/env php
<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['html'] = array();
	$__scope_id = Lexical::init_closure("html", 50);
function html_form($method, $action, $code){
	$__scope_id = Lexical::init_closure("html", 51);

	$code = array_slice(func_get_args(), 2);
	return sprintf("<form action='%s' method='%s'>%s</form>", $method, $action, implode("<br/>", $code));
}

function html_label($id){
	$__scope_id = Lexical::init_closure("html", 52);
	return sprintf("<label for='%s'>%s</label>", $id, $id);
}

function html_textbox($id){
	$__scope_id = Lexical::init_closure("html", 53);
	return sprintf("%s <br/> <input type='text' id='%s' name='%s'/>", html_label($id), $id, $id);
}

function html_textarea($id){
	$__scope_id = Lexical::init_closure("html", 54);
	return sprintf("%s <br/> <textarea id='%s' name='%s'></textarea>", $id);
}

function html_submit($id){
	$__scope_id = Lexical::init_closure("html", 55);
	return sprintf("%s <br/> <input type='submit' id='%s' name='%s' value='%s'/>", $id, $id, $id);
}

