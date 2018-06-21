<?php
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmpcond'] = array();
$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmpcond", 264);
$num = 3;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpcond", 264, '$num', $num);
if(($num == 1)){
	fail();
}
else if(($num == 2)){
	fail();
}
else if(($num == 3)){
	check(TRUE, TRUE);
}
else{
	fail();
}
$s = "Hello, world!";
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpcond", 264, '$s', $s);


$__condtmpvar5 =  Null;
if(($s == "!dlrow, olleH")){
	$__condtmpvar5 = "That's backwards!";
}
else if(($s == "Hello, world!")){
	"Creative" . " much?";
	$__condtmpvar5 = "Who is world anyway?";
}
check($__condtmpvar5, "Who is world anyway?");


$__condtmpvar6 =  Null;
if((1 == 1)){
	$__condtmpvar6 = "chicken";
}
else if((1 == 2)){
	$__condtmpvar6 = "math broke again!";
}
$foo = $__condtmpvar6;
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpcond", 264, '$foo', $foo);
check($foo, "chicken");
