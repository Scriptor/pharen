<?php
namespace pharen\html;
require_once('/Users/historium/pharen/lang.php');
use Pharen\Lexical as Lexical;
Lexical::$scopes['html'] = array();
function html_form($method, $action, $code){

	$code = seq(array_slice(func_get_args(), 2));
	return sprintf("<form action='%s' method='%s'>%s</form>", $action, $method, implode("<br/>", $code));
}

function html_label($id){
	return sprintf("<label for='%s'>%s</label>", $id, $id);
}

function html_textbox($id){
	return sprintf("%s <br/> <input type='text' id='%s' name='%s'/>", html_label($id), $id, $id);
}

function html_textarea($id){
	return sprintf("%s <br/> <textarea id='%s' name='%s'></textarea>", html_label($id), $id, $id);
}

function html_submit($id){
	return sprintf("%s <br/> <input type='submit' id='%s' name='%s' value='%s'/>", html_label($id), $id, $id, $id);
}

function html_link($url, $text){
	return sprintf("<a href='%s'>%s</a>", $url, $text);
}

