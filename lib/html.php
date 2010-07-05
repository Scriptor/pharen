<?php
require_once('/Applications/MAMP/htdocs/pharen/lang.php');
Lexical::$scopes['html'] = array();
function html_form($method, $action, $code){
	Lexical::$scopes["html"][1] = array();

	$code = array_slice(func_get_args(), 2);
	return sprintf("<form action='%s' method='%s'>%s</form>", $method, $action, implode("<br/>", $code));
}
function html_label($id){
	Lexical::$scopes["html"][2] = array();
	return sprintf("<label for='%s'>%s</label>", $id, $id);
}
function html_textbox($id){
	Lexical::$scopes["html"][3] = array();
	return sprintf("%s <br/> <input type='text' id='%s' name='%s'/>", html_label($id), $id, $id);
}
function html_textarea($id){
	Lexical::$scopes["html"][4] = array();
	return sprintf("%s <br/> <textarea id='%s' name='%s'></textarea>", $id);
}
function html_submit($id){
	Lexical::$scopes["html"][5] = array();
	return sprintf("%s <br/> <input type='submit' id='%s' name='%s' value='%s'/>", $id, $id, $id);
}
