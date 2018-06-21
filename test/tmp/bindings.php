<?php
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmpbindings'] = array();
$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmpbindings", 261);
$name = "Arthur Dent";
Lexical::bind_lexing("_Users_aarti_pharen_test_tmpbindings", 261, '$name', $name);
check($name, "Arthur Dent");
$job = "Jedi Masta";
check($job, "Jedi Masta");
$question = NULL;
$answer = (6 * 9);
check($question, NULL);
check($answer, 54);
