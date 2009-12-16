<?php
class phLexer{
	const PAREN_OPEN = "PAREN_OPEN";
	const PAREN_CLOSE = "PAREN_CLOSE";
    const PARAMS_START = "PARAMS_START";
    const PARAMS_CLOSE = "PARAMS_CLOSE";
	const INT_VALUE = "PAREN_OPEN";
	const FUNC_CALL = "PAREN_OPEN";
	
	private $code;
	private $state;
	
	public function __construct($code){
		$this->code = $code;
	}
}