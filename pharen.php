<?php
class Token{
    private $value;

    public function __construct($value=null){
        $this->value = $value;
    }

    public function append($ch){
        $this->value .= $ch;
    }
}

class OpenParenToken extends Token{
}

class CloseParenToken extends Token{
}

class NumberToken extends Token{
}

class StringToken extends Token{
}

class NameToken extends Token{
}

class Lexer{
	private $code;
    private $char;
    private $tok;
	private $state = "new-expression";
    private $toks = array();
    private $escaping = false;
    private $i=0;
	
	public function __construct($code){
		$this->code = $code;
	}

    public function lex(){
        for($this->i=0;$this->i<strlen($this->code);$this->i++){
            $this->get_char();
            $this->lex_char();
            if($this->tok !== $this->toks[sizeof($this->toks)-1]){
                $this->toks[] = $this->tok;
            }
        }
        return $this->toks;
    }

    public function get_char(){
        $this->char = $this->code[$this->i];
    }

    public function lex_char(){
        if($this->state == "string"){
            if($this->char == '"' && !$this->escaping){
                $this->state = "new-expression";
            }else if($this->char == "\\" && !$this->escaping){
                $this->escaping = True;
            }else if($this->escaping){
                $this->escaping = False;
            }else{
                $this->tok->append($this->char);
            }
        }else if($this->state == "append"){
            if(trim($this->char) === ""){
                $this->state = "new-expression";
            }else if($this->char == ")"){
                $this->tok = new CloseParenToken;
                $this->state = "new-expression";
            }else{
                $this->tok->append($this->char);
            }
        }else if($this->state == "new-expression"){
            // For function calls, a function name can itself be a sexpr that returns a function name
            if($this->char == "("){
                $this->tok = new OpenParenToken;
            }else if($this->char == ")"){
                // Allow for empty set of parens
                $this->tok = new CloseParenToken;
            }else if($this->char == '"'){
                $this->tok = new StringToken;
                $this->state = "string";
            }else if(is_numeric($this->char)){
                $this->tok = new NumberToken($this->char);
                $this->state = "append";
            }else if(trim($this->char) !== ""){
                $this->tok = new NameToken($this->char);
                $this->state = "append";
            }
        }            
    }
}

class Parser{
    private $tokens;
    private $state;
    private $curnode;
    private $parent;

    public function __construct($tokens){
        $this->tokens = $tokens;
    }

    public function parse(){
        foreach($this->tokens as $tok){
            $this->parse_token($tok);
        }
    }

    public function parse_token($tok){
    }
}

$a = new NameToken;
$b = new NameToken;

$code = file_get_contents("simple.phn");
$code = trim($code);
$lexer = new Lexer($code);
print_r($lexer->lex());
