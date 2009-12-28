<?php
error_reporting(E_ALL);
class Token{
    public $value;

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

            $toks_size = sizeof($this->toks);
            if($toks_size == 0 or $this->tok !== $this->toks[$toks_size-1]){
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

class Node{
    static $INFIX_OPERATORS = array("+", "-", "*", "/", "and", "or", "==", '=');

    public $parent;
    protected $children;

    public function __construct(Node $parent=null, $children=array()){
        $this->parent = $parent;
        $this->children = $children;
    }

    protected function split_children(){
        return array($this->children[0], array_slice($this->children, 1));
    }

    public function add_child(Node $child){
        $this->children[] = $child;
    }

    public function compile_args($args){
        while(list($key) = each($args)){
            $args[$key] = $args[$key]->compile();
        }
        return $args;
    }

    public function compile(){
        list($func_name_node, $args) = $this->split_children();
        $args = $this->compile_args($args);
        $func_name = $func_name_node->compile();
        
        if($func_name_node->infix){
            return $this->compile_infix($func_name, $args);
        }else{
            return $this->compile_normal($func_name, $args);
        }
    }

    public function compile_infix($func_name, $args){
        return "(".implode(' '.$func_name.' ', $args).")";
    }

    public function compile_normal($func_name, $args){
        $args_string = implode(", ", $args);
        return "$func_name($args_string)";
    }

    public function compile_statement(){
        $code = $this->compile();
        return $this->compile().";\n";
    }
}

class RootNode extends Node{
    public function __construct(){
    }

    public function compile(){
        $code = "";
        foreach($this->children as $child){
            $code .= $child->compile_statement();
        }
        return $code;
    }
}

class LeafNode extends Node{
    private $value;
    public $infix;

    public function __construct(Node $parent, $value){
        $this->parent = $parent;
        $this->value = $value;
        if(in_array($value, Node::$INFIX_OPERATORS)){
            $this->infix = true;
        }
    }

    public function compile(){
        return $this->value;
    }
}

class StringNode extends LeafNode{

    public function compile(){
        return '"'.parent::compile().'"';
    }
}

class Parser{
    static $NODE_TOK_MAP = array(
        "NameToken" => "LeafNode",
        "StringToken" => "StringNode",
        "NumberToken" => "LeafNode"
    );

    private $tokens;
    private $state;
    private $curnode;
    private $rootnode;

    public function __construct($tokens){
        $this->tokens = $tokens;
        $this->curnode = new RootNode;
        $this->rootnode = $this->curnode;
    }

    public function parse(){
        foreach($this->tokens as $tok){
            $this->parse_token($tok);
        }

        // Since all parentheses have been closed, curnode should now point to root node
        return $this->rootnode;
    }

    public function parse_token($tok){
        if($tok instanceof OpenParenToken){
            $newnode = new Node($this->curnode);
            $this->curnode->add_child($newnode);
            $this->curnode = $newnode;
        }else if($tok instanceof CloseParenToken){
            if($this->curnode->parent !== null){
                $this->curnode = $this->curnode->parent;
            }
        }else{
            $newnode = new self::$NODE_TOK_MAP[get_class($tok)]($this->curnode, $tok->value);
            $this->curnode->add_child($newnode);
        }
    }
}

$code = file_get_contents("simple.phn");
$code = trim($code);
$lexer = new Lexer($code);
$tokens = $lexer->lex();

$parser = new Parser($tokens);
$node_tree = $parser->parse();
echo nl2br($node_tree->compile());
