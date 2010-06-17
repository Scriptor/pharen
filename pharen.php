<?php
error_reporting(E_ALL | E_NOTICE);
define("COMPILER_SYSTEM", dirname(__FILE__));
define("EXTENSION", ".phn");

require_once('lexical.php');

// Some utility functions for use in Pharen

function last($xs){
    return end($xs);
}

function at($xs, $i){
    return $xs[$i];
}

function is_assoc($xs){
    return array_keys($xs) !== range(0, count($xs)-1);
}

function split_body_last($xs){
    $body = array_slice($xs, 0, -1);
    $last = $xs[count($xs)-1];
    return array($body, $last);
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
    public $quoted;
    public $unquoted;
    public $unquote_spliced;

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

class OpenBraceToken extends Token{
}

class CloseBraceToken extends Token{
}

class NumberToken extends Token{
}

class StringToken extends Token{
}

class ListAccessToken extends Token{
}

class NameToken extends Token{
}

class SplatToken extends Token{
}

class ExplicitVarToken extends Token{
}

class ReaderMacroToken extends Token{
}

class CommentToken extends Token{
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
        $this->code = trim($code);
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
            }else if($this->char == "\\" && !$this->escaping && $this->code[$this->i+1] == '"'){
                $this->escaping = True;
            }else{
                if($this->escaping){
                    $this->escaping = False;
                }
                $this->tok->append($this->char);
            }
        }else if($this->char == ";"){
            $this->state = "comment";
            $this->tok = new CommentToken;
        }else if($this->state == "comment"){
            if($this->char == "\n" or $this->char == "\r"){
                $this->state = "new-expression";
            }else{
                $this->tok->append($this->char);
            }
        }else if($this->state == "append"){
            if(trim($this->char) === "" or $this->char === ","){
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
            }else if($this->char == "{"){
                $this->tok = new OpenBraceToken;
            }else if($this->char == "}"){
                $this->tok = new CloseBraceToken;
            }else if($this->char == '"'){
                $this->tok = new StringToken;
                $this->state = "string";
            }else if($this->code[$this->i-1] == "(" && $this->char == ':'){
                $this->tok = new ListAccessToken;
            }else if($this->code[$this->i-1] == "(" && $this->char == '$' && trim($this->code[$this->i+1]) != ""){
                $this->tok = new ExplicitVarToken;
                $this->state = "append";
            }else if($this->char == '&'){
                $this->tok = new SplatToken();
                $this->state = "append";
            }else if($this->char == '~' or $this->char == "'" or $this->char == '@'){
                $this->tok = new ReaderMacroToken($this->char);
            }else if(is_numeric($this->char)){
                $this->tok = new NumberToken($this->char);
                $this->state = "append";
            }else if(trim($this->char) !== "" and $this->char !== ","){
                $this->tok = new NameToken($this->char);
                $this->state = "append";
            }
        }
    }
}

class InfixFunc{
    public $params = array('x', 'y');
}

class FuncInfo{
    static $tmp_counter=0;

    private $name;
    private $func;
    private $args_given;

    static function get_next_name(){
        return Node::$ns."__partial".self::$tmp_counter++;
    }

    public function __construct($name, $args){
        $this->name = $name;
        $this->args_given = $args;

        if(FuncDefNode::is_pharen_func($name)){
            $this->func = FuncDefNode::get_pharen_func($name);
        }else if(in_array($name, Parser::$INFIX_OPERATORS)){
            $this->func = new InfixFunc;
        }
    }

    public function is_partial(){
        return $this->func && count($this->args_given) < count($this->func->params);
    }

    public function get_tmp_func($parent){
        $name = self::get_next_name();
        $params_diff = count($this->func->params) - count($this->args_given);

        $function = new FuncDefNode($parent);
        $function->is_partial = True;
        $function->indent = $parent->indent;
        $fn = $function->add_child(new LeafNode($function, array(), 'fn'));
        $function->add_child(new LeafNode($function, array(), $name));

        $params = new LiteralNode($function);
        $function->add_child($params);

        $body = Null;
        if($this->func instanceof InfixFunc){
            $body = new InfixNode($function);
        }else{
            $body = new Node($function);
        }
        $function->add_child($body);

        $body->add_child(new LeafNode($body, array(), $this->name));
        foreach($this->args_given as $arg){
            $arg->parent = $body;
            $body->add_child($arg);
        }
        
        for($x=0;$x<$params_diff;$x++){
            $var = new VariableNode($params, array(), "arg$x");
            $params->add_child($var);
            $var = new VariableNode($body, array(), "arg$x");
            $body->add_child($var);
        }
        return array($function->compile()."\n", $name);
    }

}

class Scope{
    public static $scope_id = 0;

    public $owner;
    public $bindings = array();
    public $lexical_bindings = array();
    public $lexically_needed = array();
    public $id;

    public function __construct($owner){
        $this->owner = $owner;
        $this->id = self::$scope_id++;
    }

    public function bind($var_name, $value_node){
        // Keep value as just the node to allow for possible implementation of lazy evaluation in the future
        $this->bindings[$var_name] = $value_node;
    }

    public function bind_lexical($var_name, $id){
        $this->lexical_bindings[$var_name] = $id;
    }

    public function get_indent(){
        return $this->owner->parent instanceof RootNode && $this->owner instanceof BindingNode ? "" : $this->owner->indent."\t";
    }

    public function get_binding($var_name){
        $value = $this->bindings[$var_name]->compile();
        return $this->owner->indent."$var_name = $value;";
    }

    public function get_lexical_binding($var_name, $id){
        $value = 'Lexical::$scopes["'.Node::$ns.'"]['.$id.'][\''.$var_name.'\']';
        return "$var_name =& $value";
    }

    public function init_namespace_scope(){
        return "Lexical::\$scopes['".Node::$ns."'] = array();\n";
    }

    public function init_lexical_scope(){
        return $this->get_indent().'Lexical::$scopes["'.Node::$ns.'"]['.$this->id.'] = array();'."\n";
    }

    public function get_lexing($var_name){
        if(!isset($this->lexically_needed[$var_name])){
            return "";
        }
        $value = $this->bindings[$var_name]->compile();
        return $this->get_indent().'Lexical::$scopes["'.Node::$ns.'"]['.$this->id.'][\''.$var_name.'\'] =& '.$var_name.";";
    }

    public function get_lexical_bindings(){
        $code = "";
        foreach($this->lexical_bindings as $var_name=>$id){
            $code .= $this->owner->indent."\t".$this->get_lexical_binding($var_name, $id).";\n";
        }
        return $code;
    }

    public function find($var_name, $return_value=False){
        if($var_name[0] != '$'){
            $var_name = '$'.$var_name;
        }
        if(!array_key_exists($var_name, $this->bindings)){
            if($this->owner->parent !== Null){
                return $this->owner->parent->get_scope()->find($var_name, $return_value);
            }else{
                return False;
            }
        }
        $this->lexically_needed[$var_name] = True;
        if($return_value){
            return $this->bindings[$var_name];
        }
        return $this->id;
    }

    public function find_immediate($var_name){
        if($var_name[0] != '$'){
            $var_name = '$'.$var_name;
        }
        return array_key_exists($var_name, $this->bindings) ? $this->bindings[$var_name] : False;
    }
}

class Node implements Iterator, ArrayAccess, Countable{
    static $delay_tmp = False;
    static $prev_tmp;
    static $tmp;
    static $ns;

    public $parent;
    public $children;
    public $return_flag = False;
    public $indent;
    public $quoted;
    public $unquoted;
    public $tmped;

    protected $scope = Null;
    protected $value = "";

    static function add_tmp($code){
        if(!Node::$delay_tmp){
            $code = Node::$prev_tmp.Node::$tmp.$code;
            Node::$prev_tmp = '';
            Node::$tmp = '';
        }
        return $code;
    }

    public function __construct($parent=null){
        $this->parent = $parent;
        $this->children = array();
    }

    public function rewind(){
        reset($this->children);
    }

    public function current(){
        return current($this->children);
    }

    public function key(){
        return key($this->children);
    }

    public function next(){
        return next($this->children);
    }

    public function valid(){
        return $this->current !== False;
    }

    public function offsetExists($offset){
        return isset($this->children[$offset]);
    }

    public function offsetGet($offset){
        return isset($this->children[$offset]) ? $this->children[$offset] : Null;
    }

    public function offsetSet($offset, $value){
        $this->children[$offset] = $value;
    }

    public function offsetUnset($offset){
        unset($this->children[$offset]);
    }

    public function count(){
        return count($this->children);
    }

    public function format_line($code, $prefix=""){
        if($this->tmped){
            $this->indent = $this->parent->indent;
        }else if(!$this->indent){
            $this->indent = $this->parent instanceof RootNode ? "" : $this->parent->indent."\t";
        }
        return $this->indent.$prefix.$code."\n";
    }

    public function format_statement($code, $prefix=""){
        return Node::add_tmp($this->format_line($code, $prefix));
    }

    public function get_scope(){
        if($this->scope === Null){
            return $this->parent->get_scope();
        }
        return $this->scope;
    }

    public function set_scope(Scope $scope){
        $this->scope = $scope;
    }

    protected function split_children(){
        return array($this->children[0], array_slice($this->children, 1));
    }

    public function add_child($child){
        $this->children[] = $child;
    }

    public function add_children($children){
        foreach($children as $c){
            $this->children[] = $c;
            $c->parent = $this;
        }
    }

    public function compile_args($args=Null){
        $output = array();
        for($x=0; $x<count($args); $x++){
            $arg = $args[$x];
            if($arg instanceof SpliceWrapper){
                array_splice($args, $x+1, 0, $arg->compile());
            }else{
                $output[] = $arg->compile();
            }
        }
        return $output;
    }

    public function get_last_func_call(){
        return $this->children[0];
    }

    public function get_body_nodes(){
        return array();
    }

    public function get_last_expr(){
        return $this;
    }
    
    public function get_compiled_func_args(){
        // Returns the compiled code for the function name and the arguments
        // in a function call.
        list($func_name_node, $args) = $this->split_children();
        $func_name = $func_name_node->compile();
        $args = $this->compile_args($args);
        return array($func_name, $args);
    }

    public function create_partial($func){
        list($tmp_func, $tmp_name) = $func->get_tmp_func($this->parent);
        Node::$tmp .= $tmp_func;
        return '"'.$tmp_name.'"';
    }

    public function compile(){
        $scope = $this->get_scope();
        list($func_name, $args) = $this->get_compiled_func_args();

        $func = new FuncInfo($func_name, array_slice($this->children, 1));
        if(MicroNode::is_micro($func_name)){
            $micro = MicroNode::get_micro($func_name);
            return $micro->get_body(array_slice($this->children, 1), $this->indent);
        }else if(MacroNode::is_macro($func_name)){
            $unevaluated_args = array_slice($this->children,1 );
            MacroNode::evaluate($func_name, $unevaluated_args);
            foreach($unevaluated_args as $key=>$val){
                if($val instanceof LeafNode)
                    $unevaluated_args[$key] = $val->compile();
            }
            $expanded = call_user_func_array($func_name, $unevaluated_args);
            if($expanded instanceof QuoteWrapper){
                return $expanded->compile();
            }else if(is_string($expanded)){
                return '"'.$expanded.'"';
            }else{
                return $expanded;
            }
        }else if($func->is_partial()){
            return $this->create_partial($func);
        }

        $args_string = implode(", ", $args);

        return "$func_name($args_string)";
    }

    public function compile_statement($prefix=""){
        return $this->format_statement($this->compile().";", $prefix);
    }

    public function compile_return($prefix=""){
        return $this->format_statement("return ".$this->compile().";", $prefix);
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

    public function compile_return(){
        return "";
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
        $func = new FuncInfo($func_name, $args);
        if($func->is_partial()){
            return $this->create_partial($func);
        }
        $code = implode(' '.$func_name.' ', $args);
        return "(".$code.")";
    }

    public function compile_statement(){
        $code = $this->compile();
        // Remove parentheses added by regular compile() since they're not
        // needed for statements. Makes pretty.
        $code = substr($code, 1, strlen($code)-2).";";
        return $this->format_statement($code);
    }
}

class RootNode extends Node{
    public function __construct(){
        // No parent to be passed to the constructor. It's Root all the way down.
        $this->parent = Null;
        $this->children = array();
        $this->scope = new Scope($this);
    }

    public function compile(){
        $code = "";
        if(!isset(Flags::$flags['no-import-lang']) or Flags::$flags['no-import-lang'] == False){
            $code .= "require_once('".COMPILER_SYSTEM."/lang.php"."');\n";
        }else if(Flags::$flags['no-import-lang'] == True){
            $code .= "require_once('".COMPILER_SYSTEM."/lexical.php"."');\n";
        }
        $code .= $this->scope->init_namespace_scope();
        foreach($this->children as $child){
            $code .= $child->compile_statement();
        }
        return $code;
    }

}

class CommentNode extends Node{
    public function __construct($parent, $value){
        $this->parent = $parent;
        $this->value = $value;
    }

    public function compile(){
        return '# '.$this->value."\n";
    }

    public function compile_statement(){
        return $this->compile();
    }
}

class LeafNode extends Node{
    public $value;

    public function __construct($parent, $children, $value){
        // $children is kept as an argument, even though LeafNode should
        // not have any child nodes, for compatibility with parameter
        // structure of Node
        $this->parent = $parent;
        $this->value = $value;
    }

    public function get_last_func_call(){
        return $this;
    }

    public function compile(){
        return strlen($this->value) > 1 ? str_replace('-', '_', $this->value) : $this->value;
    }
}

class VariableNode extends LeafNode{
    
    public function compile($in_binding=False){
        $scope = $this->get_scope();
        $varname = '$'.parent::compile();
        if($in_binding){
            return $varname;
        }

        if($varname[1] == '$'){
            return $varname;
        }

        if($scope->find_immediate($varname) !== False){
            return $varname;
        }else if(($id = $scope->find($varname)) !== False){
            $scope->bind_lexical($varname, $id);
            return $varname;
        }else{
            return $varname;
        }
    }

    public function compile_nolookup(){
        return '$'.parent::compile();
    }
}

class SplatNode extends VariableNode{
}

class StringNode extends LeafNode{

    public function compile(){
        return '"'.$this->value.'"';
    }
}

class SpecialForm extends Node{
    protected $body_index;

    public function compile_statement($indent=""){
        $this->indent = $indent;
        return $this->compile($indent)."\n";
    }

    public function compile_body($lines=false, $prefix="", $return=False){
        // Compile the body expressions of the special form according to
        // the start index of the first body expression.
        $body = "";

        // If there is a prefix then it should be indented as if it were an expression.
        $body_index = $lines === false ? $this->body_index : 0;
        $lines = $lines === false ? $this->children : $lines;
        $bt = debug_backtrace();
        $last = array_pop($lines);
        if($return){
            $last_line = $last->compile_return();
        }else{
            $bt = debug_backtrace();
            $last_line = $last->compile_statement($prefix);
        }

        foreach(array_slice($lines, $body_index) as $child){
            $body .= $child->compile_statement();
        }
        $body .= $last_line;
        return $body;
    }

    public function split_body_last(){
        $len = count($this->children);
        $body = array_slice($this->children, $this->body_index, $len - ($this->body_index + 1));
        $last = $this->children[$len - 1];
        return array($body, $last);
    }
}

class FuncDefNode extends SpecialForm{
    static $functions;

    protected $body_index = 3;
    protected $scope;

    public $params = array();
    public $is_partial;
    
    static function is_pharen_func($func_name){
        return isset(self::$functions[$func_name]);
    }

    static function get_pharen_func($func_name){
        return self::$functions[$func_name];
    }

    public function compile(){
        $this->indent = $this->parent instanceof RootNode ? "" : $this->parent->indent."\t";
        $this->scope = $this->scope == Null ? new Scope($this) : $this->scope;

        $this->name = $this->children[1]->compile();
        self::$functions[$this->name] = $this;
        $this->params = $this->children[2]->children;

        $varnames = $this->get_param_names($this->params);
        $this->bind_params($varnames);
        $params = $this->children[2]->compile();

        list($body_nodes, $last_node) = $this->split_body_last();

        $body = "";
        $params_count = count($this->params);
        if($params_count > 0 && $this->params[$params_count-1] instanceof SplatNode){
            $param = $varnames[$params_count-1];
            $body .= "\n".$this->indent."\t".$param." = array_slice(func_get_args(), ".($params_count-1).");\n";
        }

        Node::$delay_tmp = True;
        if($this->is_tail_recursive($last_node)){
            list($body_nodes, $last_expr) = $this->split_body_tail();
            $this->indent .= "\t";
            $body .= $this->format_line("while(1){");

            list($while_body_nodes, $while_last_node) = split_body_last($body_nodes);
            $body .= count($while_body_nodes) > 0 ? $this->compile_body($while_body_nodes) : "";
            $body .= $while_last_node->compile_return();

            $new_param_values = array_slice($last_expr->children, 1);
            $params_len = count($new_param_values);
            for($x=0; $x<$params_len; $x++){
                $val_node = $new_param_values[$x];
                $body .= $this->indent."\t"."\$__tailrecursetmp$x = " . $val_node->compile().";\n";
            }
            for($x=0; $x<$params_len; $x++){
                $var_node = $this->params[$x];
                $val_node = $new_param_values[$x];
                $body .= $this->indent."\t".$var_node->compile() . " = \$__tailrecursetmp$x;\n";
            }
            $body .= $this->indent."}\n";
            $this->indent = substr($this->indent, 1);
        }else{
            $body .= count($body_nodes) > 0 ? parent::compile_body($body_nodes) : "";
            $last = $last_node->compile_return();
            $body .= $last;
        }
        $body = $this->scope->get_lexical_bindings().$body;
        $lexings = $this->get_param_lexings($varnames);

        $code = $this->indent."function ".$this->name.$params."{\n".
            $lexings.
            $body.
            $this->indent."}\n";

        if(!$this->is_partial) Node::$delay_tmp = False;
        $code = Node::add_tmp($code);
        return $code;
    }

    public function is_tail_recursive($last_node){
        return count($this->children) > 3 && $this->children[1]->compile() == $last_node->get_last_func_call()->compile();
    }

    public function compile_last($node){
        return $node->compile_return($this->indent."\t");
    }

    public function get_param_lexings($varnames){
        $lexings = $this->scope->init_lexical_scope();
        foreach($varnames as $varname){
            $lexings .= $this->scope->get_lexing($varname);
        }
        return $lexings;
    }

    public function get_param_names($param_nodes){
        $varnames = array();
        foreach($param_nodes as $node){
            $varnames[] = $node->compile();
        }
        return $varnames;
    }

    public function bind_params($params){
        $scope = $this->get_scope();
        foreach($params as $param){
            $scope->bind($param, new EmptyNode($this));
        }
    }

    public function split_body_tail(){
        list($body_nodes, $last) = parent::split_body_last($this->children);
        $body_nodes = array_merge($body_nodes, $last->get_body_nodes());
        $last = $last->get_last_expr();
        return array($body_nodes, $last);
    }
}

class MacroNode extends FuncDefNode{
    static $macros = array();
    static $literals = array();
    static $next_literal_id = 0;

    public $args;
    public $evaluated = False;

    static function is_macro($name){
        return isset(self::$macros[$name]);
    }

    static function evaluate($name, $args){
        $macronode = self::$macros[$name];
        $macronode->args = $args;
        $scope = $macronode->get_scope();
        foreach($macronode->children[2]->children as $param_node){
            if($param_node instanceof SplatNode){
                $scope->bind($param_node->compile(), new SpliceWrapper($args, True));
                break;
            }
            $scope->bind($param_node->compile(), array_shift($args));
        }
        $code = $macronode->parent_compile();
        if($macronode->evaluated){
            return;
        }else{
            eval($code);
            $macronode->evaluated = True;
        }
    }

    public function parent_compile(){
        return parent::compile();
    }

    public function compile(){
        $name = $this->children[1]->compile();
        $this->scope = new Scope($this);
        self::$macros[$name] = $this;
        return "";
    }

    public function bind_params($params){
    }
}

class QuoteWrapper{
    public $node;
    public $parent;
    public $children;
    private $literal_id;

    function __construct(Node $node, $literal_id){
        $this->node = $node;
        $this->parent = $node->parent;
        $this->children =& $node->children;
        $this->literal_id = $literal_id;
    }

    public function compile_return(){
        return 'return MacroNode::$literals['.$this->literal_id.'];'."\n";
    }

    public function __get($name){
        return $this->node->$name;
    }

    public function __set($name, $val){
        $this->node->$name = $val;
    }

    public function __call($name, $arguments){
        return call_user_func_array(array($this->node, $name), $arguments);
    }
}

class UnquoteWrapper{
    protected $node;

    function __construct(Node $node){
        $this->node = $node;
    }

    public function __call($name, $args){
        return call_user_func_array(array($this->node, $name), $args);
    }

    public function __get($name){
        return $this->node->$name;
    }

    public function __set($name, $val){
        $this->node->$name = $val;
    }

    public function compile(){
        $unstring = False;
        if(isset($this->node->value) and count($this->value) > 0 and $this->value[0] == '-'){
            $unstring = True;
            $this->value = substr($this->value, 1);
        }
        $code = $this->node->compile();
        if($this->node instanceof LeafNode){
            $val = $this->get_scope()->find($code, True)->compile();
            if($unstring){
                $val = trim($val, '"');
                $this->value = '-'.$this->value;
            }
            return $val;
        }else{
            return $code;
        }
    }

    public function compile_return(){
        return "return ".$this->compile().";\n";
    }
}

class SpliceWrapper extends UnquoteWrapper{
    public $as_collection = False;
    public $exprs;

    public function __construct($wrapped, $as_collection=False){
        if($as_collection){
            $this->as_collection = True;
            $this->exprs = $wrapped;
        }else{
            $this->node = $wrapped;
        }
    }
    
    private function get_exprs(){
        if($this->as_collection){
            return $this->exprs;
        }else{
            $varname = str_replace('@', '', $this->node->compile());
            return $this->get_scope()->find($varname, True)->get_exprs();
        }
    }

    private function compile_exprs($exprs){
        $code = "";
        foreach($exprs as $expr){
            $code .= $expr->compile_statement();
        }
        return $code;
    }

    public function compile(){
        return $this->compile_exprs($this->get_exprs());
    }

    public function compile_return(){
        $exprs = $this->get_exprs();
        return $this->compile_exprs(array_slice($exprs, 0, -1));
    }
}

class LambdaNode extends FuncDefNode{
    static $counter=0;

    protected $scope;

    static function get_next_name(){
        return Node::$ns."__lambdafunc".self::$counter++;
    }

    public function compile(){
        $name = self::get_next_name();
        $name_node = new LeafNode($this, array(), $name);
        array_splice($this->children, 1, 0, array($name_node));

        $code = parent::compile();
        Node::$tmp .= $code."\n";
        return '"'.$name.'"';
    }

    public function compile_statement(){
        return $this->compile().";\n";
    }
}

class CondNode extends SpecialForm{
    static $tmp_num = 0;

    static function get_tmp_name(){
        return "\$__condtmpvar".self::$tmp_num++;
    }

    public function get_last_func_call(){
        $len = count($this->children);
        return $this->children[$len-1]->children[1]->get_last_func_call();
    }

    public function get_body_nodes(){
        $body = clone $this;
        array_pop($body->children);
        foreach($body->children as $c){
            $c->parent = $body;
        }
        return array($body);
    }

    public function get_last_expr(){
        return $this->children[count($this->children)-1]->children[1];
    }

    public function compile(){
        $tmp_var = self::get_tmp_name();
        $this->tmped = True;
        Node::$prev_tmp.= "\n".$this->compile_statement($tmp_var." = ");
        return $tmp_var;
    }

    public function compile_statement($prefix="", $return=False){
        $this->indent = $this->parent instanceof RootNode ? "" : $this->parent->indent."\t";
        $pairs = array_slice($this->children, 1);
        $if_pair = array_shift($pairs);
        $elseif_pairs = $pairs;

        $code = $prefix === "" ? "" : "\n".$this->format_line("$prefix Null;");   // Start with newline because current line already has tabs in it.

        $code .= $this->compile_if($if_pair, $prefix, $return);
        foreach($elseif_pairs as $elseif_pair){
            $code .= $this->compile_elseif($elseif_pair, $prefix, $return);
        }
        return $code;
    }

    public function compile_return(){
        return $this->compile_statement(False, True);
    }

    public function compile_if($pair, $prefix, $return=False, $stmt_type="if"){
        $condition = $pair->children[0]->compile();
        $body = $this->compile_body(array_slice($pair->children, 1), $prefix, $return);

        return $this->format_line("$stmt_type(".$condition."){")
            .$body
        .$this->format_line("}");
    }

    public function compile_elseif($pair, $prefix, $return=False){
        return $this->compile_if($pair, $prefix, $return, "else if");
    }
}

class LispyIfNode extends CondNode{
    static $tmp_num = 0;

    static function get_tmp_name(){
        // Won't be called below PHP 5.3 because of lack of late static binding
        return '$__iftmpvar'.self::$tmp_num++;
    }

    public function compile_statement($prefix=False, $return=False){
        $compile_func = $return ? "compile_return" : "compile_statement";

        $cond = $this->children[1]->compile();

        $code = $prefix ? $prefix."Null;\n" : "";
        $code .=  $this->format_line("if($cond){")
                      .$this->children[2]->$compile_func($prefix)
                  .$this->format_line("}");

        if(isset($this->children[3])){
            $code .= $this->format_line("else{")
                .$this->children[3]->$compile_func($prefix)
            .$this->format_line("}");
        }
        return $code;
    }

    public function compile_return(){
        return $this->compile_statement(False, True);
    }

    public function get_last_func_call(){
        return $this->children[3]->get_last_func_call();
    }

    public function get_last_expr(){
        return $this->children[3];
    }
}

class IfNode extends SpecialForm{
    protected $body_index = 2;
    protected $type = "if";

    public function compile($indent=""){
        $this->indent = $indent;
        $cond = $this->children[1]->compile();
        $body = $this->compile_body();

        return $this->indent.$this->type."(".$cond."){\n".
                    $body.
                $this->indent."}";
    }

    public function compile_statement($indent=""){
        return $this->compile($indent)."\n";
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

        return $this->indent.$this->type."{\n".
                $body.
            $this->indent."}";
    }
}

class WhileNode extends IfNode{
    protected $type = "while";
}


// Deprecated way to access an array
class AtArrayNode extends Node{

    public function compile(){
        $varname = $this->children[1]->compile();
        $index = $this->children[2]->compile();
        return $varname."[$index]";
    }
}

class ListAccessNode extends Node{
    static $tmp_var = 0;

    public function compile(){
        if($this->children[0] instanceof LeafNode){
            $varname = $this->children[0]->compile();
        }else{
            $varname = '$__listAcessTmpVar'.self::$tmp_var++;
            Node::$tmp .= $varname.' = '.$this->children[0]->compile().";\n";
        }
        $indexes = "";
        foreach(array_slice($this->children, 1) as $index){
            $indexes .= '['.$index->compile().']';
        }
        return $varname.$indexes;
    }

    public function compile_statement(){
        return Node::add_tmp($this->compile().";\n");
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
        // Use an offset when using the (dict... notation for dictionaries
        $offset = ($this->children[0] instanceof LeafNode and $this->children[0]->value === "dict") ? 1 : 0;
        $pairs = array_slice($this->children, $offset);
        if($pairs[0][0] === Null){
            $pairs = array_chunk($pairs, 2);
        }
        $mappings = array();
        $code = "";

        foreach($pairs as $pair){
            $key = $pair[0]->compile();
            $value = $pair[1]->compile();
            $mappings[] = "$key => $value";
        }
        return "array(".implode(", ", $mappings).")";
    }

    public function compile_statement(){
        return $this->compile().";\n";
    }
}


class MicroNode extends SpecialForm{
    static $micros = array();

    protected $body_index = 3;
    protected $name;
    public $body;

    static function is_micro($name){
        return isset(self::$micros[$name]);
    }

    static function get_micro($name){
        return self::$micros[$name];
    }

    public function get_params(){
        $params = array();
        foreach($this->children[2]->children as $c){
            $params[] = $c->compile(True);
        }
        return $params;
    }

    public function compile(){
        $this->name= $this->children[1]->compile();
        $this->params = $this->get_params();
        
        self::$micros[$this->name] = $this;
        return '"'.$this->name.'"';
    }

    public function compile_statement(){
        $this->compile();
        return "";
    }

    public function get_body($arg_nodes, $indent=""){
        $this->indent = $indent;
        $tmp_args = $arg_nodes;
        foreach($this->params as $param){
            $this->get_scope()->bind($param, array_shift($arg_nodes));
        }
        $this->body = $this->compile_body();
        $params = $this->params;
        foreach($arg_nodes as $arg_node){
            $param = array_shift($params);
            $this->body = str_replace($param, $arg_node->compile(), $this->body);
        }
        return $this->body;
    }
            
    public function compile_body(){
        $body = parent::compile_body();
        return trim(substr($body, 0, strlen($body) - 2));
    }
}

class ListNode extends LiteralNode{

    public function compile(){
        if(($x = $this->is_range()) !== False){
            $step = $x > 1 ? $this->get_range_step() : 1;
            $first = intval($this->children[0]->compile());
            $end = intval(last($this->children)->compile());

            $vals = array();
            for($x=$first; $x<=$end; $x+=$step){
                $vals[] = $x;
            }
            return "array(".implode(', ', $vals).")";

        }
        return "array".parent::compile();
    }

    public function is_range(){
        for($x=0; $x<count($this->children); $x++){
            $el = $this->children[$x];
            if($el->compile() == '..'){
                return $x;
            }
        }
        return False;
    }

    public function get_range_step(){
        $el1 = $this->children[0]->compile();
        $el2 = $this->children[1]->compile();
        return intval($el2) - intval($el1);
    }
}

class EachPairNode extends SpecialForm{
    protected $body_index = 3;
    
    public function compile_statement(){
        $this->indent = $this->parent instanceof RootNode ? "" : $this->parent->indent."\t";
        Node::$delay_tmp = True;

        $dict_name = $this->children[1]->compile();
        $key_name = $this->children[2]->children[0]->compile(True);
        $val_name = $this->children[2]->children[1]->compile(True);

        $scope = $this->scope = new Scope($this);
        $scope->init_lexical_scope();

        $scope->bind($key_name, new EmptyNode($this));
        $scope->bind($val_name, new EmptyNode($this));

        $lexings = "";
        $lexings .= $this->indent."\t".$scope->get_lexing($key_name);
        $lexings .= "\n".$this->indent."\t".$scope->get_lexing($val_name)."\n";

        $body = $this->compile_body();
        Node::$delay_tmp = False;
        
        $code =  $this->indent."foreach($dict_name as $key_name => $val_name){\n"
            .$lexings
            .$body
        .$this->indent."}\n";
        return Node::add_tmp($code);
    }
}

class DefNode extends Node{

    public function compile_statement(){
        $this->scope = $this->parent->get_scope();
        $varname = $this->children[1]->compile();

        $this->scope->bind($varname, $this->children[2]);
        $code = $this->scope->get_binding($varname);
        $code .= $this->scope->get_lexing($varname);

        return $this->format_statement($code);
    }
}

class BindingNode extends Node{
        

    public function compile_statement($return=False){
        $this->indent = $this->parent instanceof RootNode ? "" : $this->parent->indent."\t";
        $scope = $this->scope = new Scope($this);
        $pairs = $this->children[1]->children;
        if(!($pairs[0] instanceof ListNode)){
            $pairs = array_chunk($pairs, 2);
        }
        $varnames = array();
        $code = "";
        $lexings = "";
        foreach($pairs as $pair_node){
            $varname = $pair_node[0]->compile();
            $varnames[] = $varname;

            $scope->bind($varname, $pair_node[1]);
            $code .= $this->format_statement($scope->get_binding($varname));
        }

        $this->indent = substr($this->indent, 1);
        $body = "";
        $last_line = "";
        if($return === True){
            $last_line = array_pop($this->children)->compile_return();
        }
        foreach(array_slice($this->children, 2) as $line){
            $l = $this->indent."\t".$line->compile_statement();
            if($this->parent instanceof RootNode){
                $l = ltrim($l);
            }
            $body .= $l;
        }

        foreach($varnames as $varname){
            $lexings .= $scope->get_lexing($varname);
        }
        return $this->scope->get_lexical_bindings($this->indent."\t").$code."\n".$lexings."\n".$body.$last_line;
    }

    public function compile_return(){
        return $this->compile_statement(True);
    }
}

class Parser{
    static $INFIX_OPERATORS; 
    static $reader_macros;

    static $value;
    static $values;
    static $func_call_name;
    static $func_call;
    static $infix_call;
    static $empty_node;

    static $literal_form;
    static $cond_pair;
    static $list_form;
    static $list_access_form;
    static $special_forms;

    private $tokens;

    public function __construct($tokens){
        self::$INFIX_OPERATORS = array("+", "-", "*", ".", "/", "and", "or", "=", "=&", "<", ">", "<=", ">=", "===", "==", "!=", "!==");

        self::$reader_macros = array(
            "'" => "quote",
            "," => "unquote"
        );

        self::$value = array(
            "NameToken" => "VariableNode",
            "StringToken" => "StringNode",
            "NumberToken" => "LeafNode",
            "SplatToken" => "SplatNode",
            "UnquoteToken" => "UnquoteNode"
        );

        self::$values = array(self::$value);

        self::$func_call_name = array(
            "NameToken" => "LeafNode",
            "ExplicitVarToken" => "VariableNode"
        );
        self::$func_call = array("Node", self::$func_call_name, array(self::$value));
        self::$infix_call = array("InfixNode", "LeafNode", array(self::$value));
        self::$empty_node = array("EmptyNode");

        self::$literal_form = array("LiteralNode", self::$values);
        self::$cond_pair = array("LiteralNode", self::$value, self::$value);
        self::$list_form = array("ListNode", self::$values);
        self::$list_access_form = array("ListAccessNode", self::$value, self::$value, self::$value);

        self::$special_forms = array(
            "fn" => array("FuncDefNode", "LeafNode", "LeafNode", "LiteralNode", self::$values),
            "lambda" => array("LambdaNode", "LeafNode", "LiteralNode", self::$values),
            "cond" => array("CondNode", "LeafNode", array(self::$cond_pair)),
            "if" => array("LispyIfNode", "LeafNode", self::$value, self::$value, self::$value),
            "php_if" => array("IfNode", "LiteralNode", self::$values),
            "php_elseif" => array("ElseIfNode", "LiteralNode", self::$values),
            "php_else" => array("ElseNode", self::$values),
            "at" => array("AtArrayNode", "LeafNode", "VariableNode", self::$value),
            "$" => array("SuperGlobalNode", "LeafNode", "LeafNode", self::$value),
            "def" => array("DefNode", "LeafNode", "VariableNode", self::$value),
            "let" => array("BindingNode", self::$list_form, array(self::$value)),
            "dict" => array("DictNode", "LeafNode", array(self::$value)),
            "dict-literal" => array("DictNode", array(self::$value)),
            "micro" => array("MicroNode", "LeafNode", "LeafNode", "LiteralNode", self::$values),
            "defmacro" => array("MacroNode", "LeafNode", "LeafNode", "LiteralNode", self::$values),
            "quote" => array("LiteralNode", "LeafNode", self::$values),
            "unquote" => array("UnquoteNode", "LeafNode", self::$values),
            "each_pair" => array("EachPairNode", "LeafNode", "VariableNode", "LiteralNode", self::$value)
        );
        
        $this->tokens = $tokens;

    }
    
    public function parse($root=Null){
        $curnode = $root ? $root : new RootNode;
        $rootnode = $curnode;
        $state = array();

        $count=0;
        for($i=0;$i<count($this->tokens);$i++){
            $tok = $this->tokens[$i];
            if($i+1 < count($this->tokens)){
                $lookahead = $this->tokens[$i+1];
            }
            $node;
            if($tok instanceof OpenParenToken or $tok instanceof OpenBracketToken or $tok instanceof OpenBraceToken){
                $expected_state = $this->get_expected($state);
                if($this->is_literal($expected_state)){
                    if(!is_array($state[count($state)-1][0])){
                        array_shift($state[count($state)-1]);
                    }
                    array_push($state, self::$literal_form);
                }else if($tok instanceof OpenBracketToken){
                    array_push($state, self::$list_form);
                }else if($lookahead instanceof ListAccessToken){
                    array_push($state, self::$list_access_form);
                    array_splice($this->tokens, $i+1, 1);
                }else if($tok instanceof OpenBraceToken){
                    array_push($state, self::$special_forms["dict-literal"]);
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
                if($tok->quoted){
                    $node = new QuoteWrapper($node, MacroNode::$next_literal_id);
                    MacroNode::$literals[MacroNode::$next_literal_id++] = $node;
                }else if($tok->unquoted){
                    $node = new UnquoteWrapper($node);
                }
                $curnode->add_child($node);
                $curnode = $node;
            }else if($tok instanceof CommentToken){
                // $curnode->add_child(new CommentNode($curnode, $tok->value));
            }else if($tok instanceof ReaderMacroToken){
                if($tok->value == "'")
                    $lookahead->quoted = True;
                else if($tok->value == "~")
                    $lookahead->unquoted = True;
                else if($tok->value == '@' && $this->tokens[$i-1]->value == '~'){
                    $lookahead->unquote_spliced = True;
                }
            }else if($tok instanceof CloseParenToken or $tok instanceof CloseBracketToken or $tok instanceof CloseBraceToken){
                $curnode = $curnode->parent;
                array_pop($state);
                if(count($state) === 0){
                    array_push($state, self::$func_call);
                }
            }else{
                list($node, $state) = $this->parse_tok($tok, $state, $curnode);
                if($tok->unquoted){
                    $node =  new UnquoteWrapper($node);
                }else if($tok->unquote_spliced){
                    $node = new SpliceWrapper($node);
                }
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

        if($tok instanceof NameToken and strToUpper($tok->value) == $tok->value){
            // Check if the token is all upper case, which means it's a constant
            $class = "LeafNode";
            array_shift($cur_state);
        }else if($tok instanceof TreatedToken){
            $class = "TreatedNode";
            array_shift($cur_state);
        }else if(is_array($expected) && is_assoc($expected)){
            $class = $expected[get_class($tok)];
            array_shift($cur_state);
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
        return !is_array($tok->value) and isset(self::$special_forms[$tok->value]);
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

class Flags{
    static $flags = array();
}

function compile_file($fname, $output_dir=Null){
    $ns = basename($fname, EXTENSION);
    Node::$ns = $ns;

    $code = file_get_contents($fname);
    $phpcode = compile($code);
 
    $output_dir = $output_dir === Null ? dirname($fname) : $output_dir;
    $output = $output_dir.DIRECTORY_SEPARATOR.$ns.".php";
    file_put_contents($output, $phpcode);
    return $phpcode;
}
 
function compile($code, $root=Null){
    $lexer = new Lexer($code);
    $tokens = $lexer->lex();
 
    $parser = new Parser($tokens);
    $node_tree = $parser->parse($root);
    $phpcode = $node_tree->compile();
    return "<?php\n".$phpcode."?>";
}

if(__FILE__ === realpath($_SERVER['SCRIPT_NAME'])){
    $input_files = array();
    if(isset($argv) && isset($argv[1])){
        array_shift($argv);
        foreach($argv as $arg){
            if(strpos($arg, "--") === 0){
                $flag = substr($arg, 2);
                Flags::$flags[$flag] = True;
            }else{
                $input_files[] = $arg;
            }
        }
    }

    $php_code = "";
    $old_setting = isset(Flags::$flags['no-import-lang']) ? Flags::$flags['no-import-lang'] : False;
    Flags::$flags['no-import-lang'] = True;
    //$lang_code = compile_file(COMPILER_SYSTEM . "/lang.phn");
    Flags::$flags['no-import-lang'] = $old_setting;
    if(isset($_SERVER['REQUEST_METHOD'])){
        $php_code = $lang_code;
    }else{
        $php_code = "";
    }
    //require(SYSTEM . "/lang.php");
    foreach($input_files as $file){
        $php_code .= compile_file($file);
    }
    if(isset($_SERVER['REQUEST_METHOD'])){
        echo "<pre>$php_code</pre>";
    }
}
