<?php
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmpif'] = array();
$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmpif", 265);
$bool = TRUE;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpif", 265, '$bool', $bool);
if($bool){
	check(TRUE, TRUE);
}
else{
	check(TRUE, FALSE);
}

$__condtmpvar7 = Null;
if(TRUE){
	$__condtmpvar7 = "This " . "works";
}
else{
	$__condtmpvar7 = "Doesn't" . "work";
}
check($__condtmpvar7, "This works");

$__condtmpvar8 = Null;
if(FALSE){
	$__condtmpvar8 = "Doesn't" . "work";
}
else{
	$__condtmpvar8 = "This " . "works";
}
check($__condtmpvar8, "This works");

$__condtmpvar9 = Null;
if(TRUE){
	$value = "True after all";
$__condtmpvar9 = Lexical::bind_lexing("_Users_aarti_pharen_test_tmpif", 265, '$value', $value);
}
else{
	$value = "Shouldn't be here";
$__condtmpvar9 = Lexical::bind_lexing("_Users_aarti_pharen_test_tmpif", 265, '$value', $value);
}
$result = $__condtmpvar9;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpif", 265, '$result', $result);
check($result, "True after all");

$__condtmpvar10 = Null;
if(TRUE){
	"Working";
	$__condtmpvar10 = "Done!";
}
else{
	$__condtmpvar10 = "Not working";
}
check($__condtmpvar10, "Done!");
