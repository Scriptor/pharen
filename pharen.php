<?php
class Token{
    private $value;

    public function __construct($value=null){
        $this->value = $value;
    }
}

class OpenParenToken{
}

class CloseParenToken{
}

class StringToken{
}

class NameToken{
}

class Lexer{
	private $code;
    private $char;
    private $tok;
	private $state = "";
    private $toks = array();
    private $escaping = false;
    private $i=0;
	
	public function __construct($code){
		$this->code = $code;
	}

    public function lex(){
        for($this->i=0;$this->i<strlen($this->code);$this->i++){

    }

    public function get_char(){
        $this->char = $code[$i++];
    }

    public function lex_char(){
        if($this->char == "("){
            $this->tok = new OpenParenToken;
        }else if($this->char == ")"){
            $this->tok = new CloseParenToken;
        }else if(
    }

}
