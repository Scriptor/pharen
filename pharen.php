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
    return end($xs);
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

    public function __construct(Node $parent=null){
        $this->parent = $parent;
        $this->children = array();
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

// Empty parentheses are still going to be part of the tree, in case they
// are needed later
class EmptyNode extends Node{

    public function compile(){
        return null;
    }
}

class LiteralNode extends Node{

    public function compile(){
        $els = $this->compile_args($this->children);
        return "(".implode(", ", $els).")";
    }

    public function compile_as_code(){
        $code = "";
        foreach($this->children as $c){
            $code .= $c->compile_statement();
        }
        return $code;
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
        $this->parent = $this;
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

    public function compile_statement(){
        return $this->compile().";\n";
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

    public function compile_body($lines=false){
        // Compile the body expressions of the special form according to
        // the start index of the first body expression.
        $body = "";
        $lines = $lines === false ? $this->children : $lines;
        foreach(array_slice($lines, $this->body_index) as $child){
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

    public function compile_body(){
        $body = "";
        $lines = array_slice($this->children, 0, count($this->children) - 1);
        $last = $this->children[count($this->children)-1];

        $body = parent::compile_body($lines);
        if($last instanceof IfNode){
            $body .= "\t".$last->compile_statement($this->indent."\t");
        }else{
            $body .= "\treturn ".$last->compile_statement();
        }
        return $body;
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
    protected $body_index = 1;

    public function compile($indent=0){
        $body = $this->compile_body();

        return $this->type."{\n".
                $body.
            $this->indent."}";
    }
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

class DictNode extends Node{

    public function compile(){
        $pairs = array_slice($this->children, 1);
        $mappings = array();
        $code = "";

        foreach($pairs as $pair){
            $key = $pair->children[0]->compile();
            $value = $pair->children[1]->compile();
            $mappings[] = "$key => $value";
        }
        return "array(".implode(", ", $mappings).")";
    }
}

class Parser{
    static $INFIX_OPERATORS; 

    static $value;
    static $values;
    static $func_call;
    static $infix_call;
    static $empty_node;

    static $literal_form;
    static $special_forms;

    private $tokens;

    public function __construct($tokens){
        self::$INFIX_OPERATORS = array("+", "-", "*", ".", "/", "and", "or", "<", ">", "==", '=');

        self::$value = array(
            "NameToken" => "VariableNode",
            "StringToken" => "StringNode",
            "NumberToken" => "LeafNode"
        );
        self::$values = array(self::$value);
        self::$func_call = array("Node", "LeafNode", array(self::$value));
        self::$infix_call = array("InfixNode", "LeafNode", array(self::$value));
        self::$empty_node = array("EmptyNode");

        self::$literal_form = array("LiteralNode", self::$values);
        self::$special_forms = array(
            "fn" => array("FuncDefNode", "LeafNode", "LeafNode", "LiteralNode", self::$values),
            "if" => array("IfNode", "LiteralNode", self::$values),
            "elseif" => array("ElseIfNode", "LiteralNode", self::$values),
            "else" => array("ElseNode", self::$values),
            "at" => array("AtArrayNode", "LeafNode", "VariableNode", self::$value),
            "$" => array("SuperGlobalNode", "LeafNode", "LeafNode", self::$value),
            "dict" => array("DictNode", array(self::$literal_form))
        );
        
        $this->tokens = $tokens;

    }
    
    public function parse(){
        $curnode = new RootNode;
        $rootnode = $curnode;
        $state = array();
        $len = count($this->tokens);

        for($i=0;$i<$len;$i++){
            $tok = $this->tokens[$i];
            if($i+1 < $len){
                $lookahead = $this->tokens[$i+1];
            }
            $node;

            if($tok instanceof OpenParenToken){
                $expected_state = $this->get_expected($state);
                if($this->is_literal($expected_state)){
                    array_shift($state[count($state)-1]);
                    array_push($state, self::$literal_form);
                }else if($this->is_special($lookahead)){
                    array_push($state, self::$special_forms[$lookahead->value]);
                }else if($this->is_infix($lookahead)){
                    array_push($state, self::$infix_call);
                }else if($lookahead instanceof CloseParenToken){
                    array_push($state, self::$empty_node);
                }else{
                    array_push($state, self::$func_call);
                }
                list($node, $state) = $this->parse_tok($tok, $state, $curnode);
                $curnode->add_child($node);
                $curnode = $node;
            }else if($tok instanceof CloseParenToken){
                $curnode = $curnode->parent;
                array_pop($state);
                if(count($state) === 0){
                    array_push($state, self::$func_call);
                }
            }else{
                list($node, $state) = $this->parse_tok($tok, $state, $curnode);
                $curnode->add_child($node);
            }
        }
        return $rootnode;
    }

    public function parse_tok($tok, $state, $parent){
        $cur_state = &$state[count($state)-1];
        $expected = $cur_state[0];
        if(is_array($expected) && !is_assoc($expected)){
            array_push($cur_state, $expected);
            $expected = $this->reduce_state($expected);
        }

        if(is_array($expected) && is_assoc($expected)){
            $class = $expected[get_class($tok)];
        }else{
            $class = $expected;
            array_shift($cur_state);
        }
        $node = new $class($parent, null, $tok->value);
        return array($node, $state);
    }

    public function reduce_state($expected){
        if(is_array($expected) && !is_assoc($expected)){
            return $this->reduce_state($expected[0]);
        }
        return $expected;
    }

    public function is_special($tok){
        return isset(self::$special_forms[$tok->value]);
    }

    public function is_infix($tok){
        return in_array($tok->value, self::$INFIX_OPERATORS);
    }

    public function is_literal($expected){
        $result = ($expected == "LiteralNode") || (isset($expected[0]) && $expected[0] == "LiteralNode");
        return $result;
    }

    public function get_expected($state){
        $cur = last($state);
        $expected = count($cur) > 0 ? $cur[0] : null;
        if(is_array($expected) && !is_assoc($expected)){
            $expected = $expected[0];
        }
        return $expected;
    }
}

function compile_file($fname){
    $code = file_get_contents($fname);
    $phpcode = compile($code);

    $output = basename($fname, EXTENSION).".php";
    file_put_contents($output, $phpcode);
    return $phpcode;
}

function compile($code){
    $lexer = new Lexer($code);
    $tokens = $lexer->lex();

    $parser = new Parser($tokens);
    $node_tree = $parser->parse();
    $phpcode = $node_tree->compile();
    return $phpcode;
}

$fname = "lib.phn";
$output = "lib.php";
if(isset($argv) && isset($argv[1])){
    $fname = $argv[1];
    if(isset($argv[2])){
        $output = $argv[2];
    }else{
        $output = basename($argv[1], EXTENSION).".php";
    }
}

$phpcode = compile_file($fname);
echo "<pre>".$phpcode."</pre>";
