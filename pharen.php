<?php
error_reporting(E_ALL | E_STRICT | E_NOTICE);
define("EXTENSION", ".phn");

// Some utility functions for use in Pharen
function first($xs){
    return $xs[0];
}

function rest($xs){
    return array_slice($xs, 1);
}

function last($xs){
    return $xs[sizeof($xs)-1];
}

function at($xs, $i){
    return $xs[$i];
}

function is_assoc($xs){
    return array_keys($xs) !== range(0, count($xs)-1);
}

function print_tree($node){
    echo "\t";
    foreach($node->children as $child){
        if($child instanceof LeafNode){
            echo $child->value;
        }else{
            echo get_class($child);
        }
    }
}
            

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

    public $parent;
    public $children;

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
    public $value;

    public function __construct(Node $parent, $children, $value){
        // $children is kept as an argument, even though LeafNode should
        // not have any child nodes, for compatibility with parameter
        // structure of Node
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

class SpecialForm extends Node{
    protected $body_index;
    protected $indent;

    public function compile_statement($indent=""){
        $this->indent = $indent;
        return $this->compile()."\n";
    }

    public function compile_body(){
        // Compile the body expressions of the special form according to
        // the start index of the first body expression.
        $body = "";
        foreach(array_slice($this->children, $this->body_index) as $child){
            $body .= $this->indent."\t".$child->compile_statement($this->indent."\t");
        }
        return $body;
    }
}

class FuncDefNode extends SpecialForm{
    protected $body_index = 3;
    
    public function compile(){
        $name = $this->children[1]->compile();
        $args = $this->children[2]->compile();
        $body = $this->compile_body();

        $code = "function ".$name.$args."{\n".
                    $body.
                $this->indent."}";
        return $code;
    }
}

class IfNode extends SpecialForm{
    protected $body_index = 2;
    protected $type = "if";

    public function compile($indent=0){
        $cond = $this->children[1]->compile();
        $body = $this->compile_body();

        return $this->type.$cond."{\n".
                    $body.
                $this->indent."}";
    }
}

class ElseIfNode extends IfNode{
    protected $type = "else if";
}

class ElseNode extends IfNode{
    protected $type = "else";
}

class AtArrayNode extends Node{

    public function compile(){
        $varname = $this->children[1]->compile();
        $index = $this->children[2]->compile();
        return $varname."[$index]";
    }
}

class SuperGlobalNode extends Node{

    public function compile(){
        $varname = strToUpper($this->children[1]->compile());
        $key = $this->children[2]->compile();
        return '$_'.$varname.'['.$key.']';
    }
}

class Parser{
    static $INFIX_OPERATORS = array("+", "-", "*", ".", "/", "and", "or", "==", '=');

    static $NODE_TOK_MAP = array(
        "NameToken" => "VariableNode",
        "StringToken" => "StringNode",
        "NumberToken" => "LeafNode"
    );
    
    static $NODES;
    static $SPECIAL_FORMS;
    static $LITERAL_FORM;
    static $INFIX_FORM;
    static $NORMAL_FORM;

    private $state_stack;
    private $curnode;
    private $tokens;
    private $tok;
    private $i=0;
    private $root;

    public function __construct($tokens){
        $this->tokens = $tokens;
        $this->curnode = new RootNode;
        $this->root = $this->curnode;
        $this->state_stack = array();

        self::$NODES = array(self::$NODE_TOK_MAP);

        self::$LITERAL_FORM = array("LiteralNode", self::$NODES);
        self::$INFIX_FORM = array("InfixNode", "LeafNode", self::$NODES);
        self::$NORMAL_FORM = array("Node", "LeafNode", self::$NODES);

        self::$SPECIAL_FORMS = array(
            "fn" => array("FuncDefNode", "LeafNode", "LeafNode", "LiteralNode", self::$NODES),
            "if" => array("IfNode", "LiteralNode", self::$NODES),
            "elseif" => array("ElseIfNode", "LiteralNode", self::$NODES),
            "else" => array("ElseNode", "LiteralNode", self::$NODES),
            "at" => array("AtArrayNode", "LeafNode", "VariableNode", "LeafNode"),
            "$" => array("SuperGlobalNode", "LeafNode", "LeafNode", self::$NODE_TOK_MAP)
        );
    }

    public function parse(){
        $len = sizeof($this->tokens);
        for($this->i=0; $this->i<$len; $this->i++){
            $this->tok = $this->tokens[$this->i];
            $this->parse_tok();
        }
        return $this->root;
    }

    public function parse_tok(){
        if($this->tok instanceof OpenParenToken){
            if($this->get_next_state_node() == "LiteralNode"){
                array_shift($this->state_stack[sizeof($this->state_stack)-1]);
                array_push($this->state_stack, self::$LITERAL_FORM);
            }else if($this->is_special()){
                array_push($this->state_stack, self::$SPECIAL_FORMS[$this->lookahead_value()]);
            }else if($this->is_infix()){
                array_push($this->state_stack, self::$INFIX_FORM);
            }else{
                array_push($this->state_stack, self::$NORMAL_FORM);
            }
            $newnode = $this->add_node();
            $this->curnode = $newnode;
        }else if($this->tok instanceof CloseParenToken){
            array_pop($this->state_stack);
            $this->curnode = $this->curnode->parent;
        }else{
            $this->add_node();
        }
    }

    public function add_node(){
        $next = $this->get_next_state_node();
        $class = "";
        if(is_array($next)){
            if(is_assoc($next)){
                $class = $next[get_class($this->tok)];
            }else if(is_array($next[0]) && is_assoc($next[0])){
                $class = $next[0][get_class($this->tok)];
            }else{
                $class = $next[0];
            }
        }else{
            $class = array_shift($this->state_stack[sizeof($this->state_stack)-1]);
        }
        $newnode = new $class($this->curnode, null, $this->tok->value);
        $this->curnode->add_child($newnode);
        return $newnode;
    }

    public function get_next_state_node(){
        if(sizeof($this->state_stack) === 0){
            return null;
        }
        $state = last($this->state_stack);
        if(sizeof($state) === 0){
            return null;
        }
        return $state[0];
    }

    public function lookahead(){
        return $this->tokens[$this->i+1];
    }

    public function lookahead_value(){
        return $this->lookahead()->value;
    }

    public function is_special(){
        return isset(self::$SPECIAL_FORMS[$this->lookahead_value()]);
    }

    public function is_infix(){
        return in_array($this->lookahead_value(), self::$INFIX_OPERATORS);
    }
}


$fname = "simple.phn";
$output = "simple.php";
if(isset($argv) && isset($argv[1])){
    $fname = $argv[1];
    if(isset($argv[2])){
        $output = $argv[2];
    }else{
        $output = basename($argv[1], EXTENSION).".php";
    }
}

$code = file_get_contents($fname);
$code = trim($code);
$lexer = new Lexer($code);
$tokens = $lexer->lex();

$parser = new Parser($tokens);
$node_tree = $parser->parse();
$phpcode = $node_tree->compile();
echo "<pre>".$phpcode."</pre>";
file_put_contents($output, "<?php\n".$phpcode."\n?>");
