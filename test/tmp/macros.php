<?php
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmpmacros'] = array();
check((5 * 5), 25);
check((15 . " is too big!"), "15 is too big!");
check(1, 1);
function generated_test_fn(){
	$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmpmacros", 299);
	$stuff = "foo";
	Lexical::bind_lexing("_Users_aarti_pharen_test_tmpmacros", 299, '$stuff', $stuff);
	return ($stuff . "bar");
}


check(generated_test_fn(), "foobar");

$__condtmpvar11 = Null;
if((2 == 2)){
	"line" . 1;
	$__condtmpvar11 = "foobar";
}
else{
	$__condtmpvar11 = FALSE;
}
check($__condtmpvar11, "foobar");
function var_test(){
	$var_test = "foo";
	return $var_test;
}


check(var_test(), "foo");
