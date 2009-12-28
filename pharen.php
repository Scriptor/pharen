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

class OpenBracketToken extends Token{
}

class CloseBracketToken extends Token{
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
            }else if($this->char == "]"){
                $this->tok = new CloseBracketToken;
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
            }else if($this->char == "["){
                $this->tok = new OpenBracketToken;
            }else if($this->char == "]"){
                $this->tok = new CloseBracketToken;
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

    public function get_compiled_func_args(){
        // Returns the compiled code for the function name and the arguments
        // in a function call.
        list($func_name_node, $args) = $this->split_children();
        $func_name = $func_name_node->compile();
        $args = $this->compile_args($args);
        return array($func_name, $args);
    }

    public function compile(){
        list($func_name, $args) = $this->get_compiled_func_args();
        $args_string = implode(", ", $args);
        return "$func_name($args_string)";
    }

    public function compile_statement(){
        $code = $this->compile();
        return $code.";\n";
    }
}

class LiteralNode extends Node{

    public function compile(){
        $els = $this->compile_args($this->children);
        return "(".implode(", ", $els).")";
    }
}

class InfixNode extends Node{

    public function compile(){
        list($func_name, $args) = $this->get_compiled_func_args();
        $code = implode(' '.$func_name.' ', $args);
        return "(".$code.")";
    }

    public function compile_statement(){
        $code = $this->compile();
        // Remove parentheses added by regular compile() since they're not
        // needed for statements. Makes pretty.
        return substr($code, 1, strlen($code)-2).";\n";
    }
}

class RootNode extends Node{
    public function __construct(){
        // No parent to be passed to the constructor. It's Root all the way down.
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

    public function __construct(Node $parent, $value){
        $this->parent = $parent;
        $this->value = $value;
    }

    public function compile(){
        return $this->value;
    }
}

class VariableNode extends LeafNode{
    
    public function compile(){
        return '$'.parent::compile();
    }
}

class StringNode extends LeafNode{

    public function compile(){
        return '"'.parent::compile().'"';
    }
}

class FuncDefNode extends Node{
    
    public function compile(){
        $name = $this->children[1]->compile();
        $args = $this->children[2]->compile_list();
        $body = $this->children[3]->compile();

        
    }
}

class SpecialForm extends Node{

    public function compile_statement(){
        return $this->compile();
    }
}

class IfNode extends SpecialForm{

    public function compile(){
        $cond = $this->children[1]->compile();
        $body = "";
        foreach(array_slice($this->children, 2) as $child){
            $body .= "\t".$child->compile_statement();
        }

        return "if".$cond."{\n".
                    $body.
                "}";
    }
}

class Parser{
    static $NODE_TOK_MAP = array(
        "NameToken" => "VariableNode",
        "StringToken" => "StringNode",
        "NumberToken" => "LeafNode"
    );

    static $SPECIAL_FORMS = array(
        "function" => "FuncDefNode",
        "if" => "IfNode"
    );

    private $tokens;
    private $state;
    private $curnode;
    private $rootnode;
    private $i=0;

    public function __construct($tokens){
        $this->tokens = $tokens;
        $this->curnode = new RootNode;
        $this->rootnode = $this->curnode;
    }

    public function parse(){
        $len = sizeof($this->tokens);
        for($this->i=0; $this->i<$len; $this->i++){
            $this->parse_token($this->tokens[$this->i]);
        }
        return $this->rootnode;
    }

    public function parse_token($tok){
        if($tok instanceof OpenParenToken){
            $this->state = "function-call";
            if($this->isinfix()){
                $newnode = new InfixNode($this->curnode);
            }else if(($class = $this->is_special()) !== False){
                $newnode = new $class($this->curnode);
            }else{
                $newnode = new Node($this->curnode);
            }
            $this->curnode->add_child($newnode);
            $this->curnode = $newnode;
        }else if($tok instanceof OpenBracketToken){
            $newnode = new LiteralNode($this->curnode);
            $this->curnode->add_child($newnode);
            $this->curnode = $newnode;
        }else if($tok instanceof CloseParenToken or $tok instanceof CloseBracketToken){
            if($this->curnode->parent !== null){
                $this->curnode = $this->curnode->parent;
            }
        }else{
            if($this->state == "function-call"){
                $newnode = new LeafNode($this->curnode, $tok->value);
                $this->state = "";
            }else{
                $class = get_class($tok);
                $newnode = new self::$NODE_TOK_MAP[$class]($this->curnode, $tok->value);
            }
            $this->curnode->add_child($newnode);
        }
    }

    public function lookahead(){
        return $this->tokens[$this->i+1];
    }

    public function isinfix(){
        $nextval = $this->lookahead()->value;
        return in_array($nextval, Node::$INFIX_OPERATORS);
    }

    public function is_special(){
        // Also returns the class name corresponding to the special form
        // if one exists, otherwise returns False.
        $nextval = $this->lookahead()->value;
        if(isset(self::$SPECIAL_FORMS[$nextval])){
            return self::$SPECIAL_FORMS[$nextval];
        }else{
            return False;
        }
    }        
}

$code = file_get_contents("simple.phn");
$code = trim($code);
$lexer = new Lexer($code);
$tokens = $lexer->lex();

$parser = new Parser($tokens);
$node_tree = $parser->parse();
$phpcode = $node_tree->compile();
echo "<pre>".$phpcode."</pre>";
