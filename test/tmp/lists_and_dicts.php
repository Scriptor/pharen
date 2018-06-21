<?php
require_once('/Users/aarti/pharen/lang.php');
use Pharen\Lexical as Lexical;
use \Seq as Seq;
use \FastSeq as FastSeq;
Lexical::$scopes['_Users_aarti_pharen_test_tmplists_and_dicts'] = array();
$__scope_id = Lexical::init_closure("_Users_aarti_pharen_test_tmplists_and_dicts", 263);
check(count(\PharenVector::create_from_array(array(1, 2, 3))), 3);
check(\PharenVector::create_from_array(range(1, 5)), \PharenVector::create_from_array(array(1, 2, 3, 4, 5)));
check(\PharenVector::create_from_array(range(1, 6, 2)), \PharenVector::create_from_array(array(1, 3, 5)));
$__listAcessTmpVar0 = \PharenVector::create_from_array(array("pharen", "php"));
check($__listAcessTmpVar0[0], "pharen");
$__tmpfuncname5 = \PharenVector::create_from_array(array("pharen", "php"));
check($__tmpfuncname5(0), "pharen");
$lst = \PharenVector::create_from_array(array("scheme", "CL", "clojure"));
Lexical::bind_lexing("_Users_aarti_pharen_test_tmplists_and_dicts", 263, '$lst', $lst);
check($lst[2], "clojure");
check($lst(2), "clojure");
check(count(hashify(array("functional" => "Haskell", "imperative" => "C", "wtf" => "Pharen"))), 3);
$__listAcessTmpVar1 = hashify(array("functional" => "Haskell", "imperative" => "C", "wtf" => "Pharen"));
check($__listAcessTmpVar1["wtf"], "Pharen");
$dct = hashify(array("functional" => "Haskell", "imperative" => "C", "wtf" => "Pharen"));
Lexical::bind_lexing("_Users_aarti_pharen_test_tmplists_and_dicts", 263, '$dct', $dct);
check($dct["functional"], "Haskell");
$new_dct = assoc("logic", "Prolog", $dct);
Lexical::bind_lexing("_Users_aarti_pharen_test_tmplists_and_dicts", 263, '$new_dct', $new_dct);
check($new_dct["logic"], "Prolog");
$__tmpfuncname6 = hashify(array("foo" => "bar"));
check($__tmpfuncname6("foo"), "bar");
check($dct("imperative"), "C");
