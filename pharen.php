<?php
error_reporting(E_ALL | E_NOTICE);
define("COMPILER_SYSTEM", dirname(__FILE__));
define("EXTENSION", ".phn");

require_once(COMPILER_SYSTEM.DIRECTORY_SEPARATOR.'lexical.php');
#require_once("lang.php");
require_once(COMPILER_SYSTEM.DIRECTORY_SEPARATOR."lib/"."sequence.php");

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

class UnstringToken extends Token{
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

class FuncValToken extends Token{
}

class Lexer{
    static $keyword_rewrites = array(
        'or' => 'pharen-or',
        'and' => 'pharen-and'
    );

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

    public function in_sexpr_opening(){
        $c = count($this->toks);
        if($c < 1)
            return False;

        $prev_tok = $this->toks[$c-1];
        return $prev_tok instanceof OpenParenToken || $prev_tok instanceof ReaderMacroToken;
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
            }else if($this->char == "\\" && !$this->escaping && $this->code[$this->i+1] == "\\"){
                $this->escaping = True;
                $this->tok->append($this->char);
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
                if(isset(self::$keyword_rewrites[$this->tok->value])){
                    $this->tok->value = self::$keyword_rewrites[$this->tok->value];
                }
                $this->state = "new-expression";
            }else if($this->char == ")"){
                $this->tok = new CloseParenToken;
                $this->state = "new-expression";
            }else if($this->char == "]"){
                $this->tok = new CloseBracketToken;
                $this->state = "new-expression";
            }else if($this->char == '}'){
                $this->tok = new CloseBraceToken;
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
            }else if($this->in_sexpr_opening() && $this->char == ':' && $this->code[$this->i+1] != ':'){
                $this->tok = new ListAccessToken;
            }else if($this->in_sexpr_opening() && $this->char == '$' && trim($this->code[$this->i+1]) != ""){
                $this->tok = new ExplicitVarToken;
                $this->state = "append";
            }else if($this->char == '&'){
                $this->tok = new SplatToken;
                $this->state = "append";
            }else if($this->char == '#'){
                $this->tok = new FuncValToken;
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
    private $force_not_partial;
    private $args_given;

    static function get_next_name(){
        return Node::$ns."__partial".self::$tmp_counter++;
    }

    public function __construct($name, $force_not_partial, $args){
        $this->name = $name;
        $this->args_given = $args;
        $this->force_not_partial = $force_not_partial;

        if(FuncDefNode::is_pharen_func($name)){
            $this->func = FuncDefNode::get_pharen_func($name);
        }else if(in_array($name, Parser::$INFIX_OPERATORS)){
            $this->func = new InfixFunc;
        }
    }

    public function is_partial(){
        return !$this->force_not_partial && $this->func && count($this->args_given) < $this->get_num_args_needed();
    }

    public function get_num_args_needed(){
        $num = 0;
        foreach($this->func->params as $param){
            if(!($param instanceof ListNode)){
                $num++;
            }else{
                break;
            }
        }
        return $num;
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
        return array($function->compile().$parent->format_line(""), $name);
    }

}

class Scope{
    public static $scope_id = 0;
    public static $scopes = array();

    public $owner;
    public $bindings = array();
    public $tok_bindings = array();
    public $lexical_bindings = array();
    public $lexically_needed = array();
    public $lex_vars = False;
    public $virtual=False;
    public $id;

    public function __construct($owner){
        $this->owner = $owner;
        $this->id = self::$scope_id++;
        self::$scopes[$this->id] = $this;
    }

    public function bind($var_name, $value_node){
        // Keep value as just the node to allow for possible implementation of lazy evaluation in the future
        $this->bindings[$var_name] = $value_node;
    }

    public function bind_lexical($var_name, $id){
        $this->lexical_bindings[$var_name] = $id;
    }

    public function bind_tok($var_name, $token){
        $this->tok_bindings[$var_name] = $token;
    }

    public function get_indent(){
        return $this->owner->parent instanceof RootNode && $this->owner instanceof BindingNode ? "" : $this->owner->indent."\t";
    }

    public function rescope($var_name){
            Node::$post_tmp .= $this->owner->format_line_indent("Scope::\$scopes['{$this->id}']->bindings['$var_name'] = $var_name;");
    }

    public function get_binding($var_name){
        $value = $this->bindings[$var_name]->compile();

        if(MacroNode::$rescope_vars){
            $this->rescope($var_name);
        }
        return "$var_name = $value;";
    }

    public function get_lexical_binding($var_name, $id){
        $value = "Lexical::get_lexical_binding('".Node::$ns."', $id, '$var_name', isset(\$__closure_id)?\$__closure_id:0);";
        return "$var_name =& $value";
    }

    public function init_namespace_scope(){
        return $this->owner->format_line("Lexical::\$scopes['".Node::$ns."'] = array();");
    }

    public function init_lexical_scope(){
        if(count($this->lexically_needed) === 0){
            return "";
        }else{
            return $this->owner->format_line_indent('$__scope_id = Lexical::init_closure("'.Node::$ns.'", '.$this->id.');');
        }
    }

    public function get_lexing($var_name, $force=False){
        if($force){
            $this->lexically_needed[$var_name] = True;
        }
        if(!isset($this->lexically_needed[$var_name])){
            return "";
        }
        return $this->owner->format_line_indent('Lexical::bind_lexing("'.Node::$ns."\", {$this->id}, '$var_name', $var_name);");
    }

    public function get_lexical_bindings(){
        $code = "";
        foreach($this->lexical_bindings as $var_name=>$id){
            $code .= $this->owner->format_line_indent($this->get_lexical_binding($var_name, $id).';');
        }
        return $code;
    }

    public function find($var_name, $return_value=False, $from_virtual=False, $is_tok=False){
        $bindings = $is_tok ? $this->tok_bindings : $this->bindings;

        if($var_name[0] != '$'){
            $var_name = '$'.$var_name;
        }

        if(!array_key_exists($var_name, $bindings)){
            if($this->owner->parent !== Null){
                return $this->owner->parent->get_scope()->find($var_name, $return_value, $this->virtual, $is_tok);
            }else{
                return False;
            }
        }

        if(!$from_virtual){
            // When the scope below this is not a virtual scope such as a let binding
            $this->lexically_needed[$var_name] = True;
        }

        if($return_value){
            return $bindings[$var_name];
        }
        return $this->id;
    }


    public function find_immediate($var_name){
        if($var_name[0] != '$'){
            $var_name = '$'.$var_name;
        }
        $result = array_key_exists($var_name, $this->bindings) ? $this->bindings[$var_name] : False;
        if($result === False && $this->virtual && $this->owner->parent !== Null){
            $parent_scope = $this->owner->parent->get_scope();
            return $parent_scope->find_immediate($var_name);
        }else{
            return $result;
        }
    }
}

class Node implements Iterator, ArrayAccess, Countable{
    static $in_func = 0;
    static $tmpfunc;
    static $prev_tmp;
    static $tmp;
    static $post_tmp;
    static $ns;
    static $tmp_funcname_var=0;
    static $delimiter_tokens = array("OpenParenToken", "CloseParenToken");

    public $parent;
    public $children;
    public $tokens;
    public $return_flag = False;
    public $has_variable_func = False;
    public $in_macro;
    public $force_not_partial;
    public $returns_special_form;
    public $indent = Null;

    public $quoted;
    public $unquoted;
    public $has_splice;

    public $scope = Null;
    protected $value = "";

    static function add_tmp($code){
        if(LambdaNode::$in_lambda_compile){
            return $code;
        }
        $code = Node::$prev_tmp.Node::$tmp.$code.Node::$post_tmp;
        Node::$prev_tmp = '';
        Node::$tmp = '';
        Node::$post_tmp = '';
        return $code;
    }

    static function add_tmpfunc($code){
        $code = Node::$tmpfunc.$code;
        Node::$tmpfunc = '';
        return $code;
    }

    static function get_tmp_funcname_var(){
        return "\$__tmpfuncname".self::$tmp_funcname_var++;
    }

    static function next_scope_id(){
        return self::$next_scope_id++;
    }

    public function __construct($parent=null){
        $this->parent = $parent;
        $this->children = array();
        $this->tokens = array();
        if($this->parent instanceof SpecialForm){
            $this->indent = $this->parent->indent."\t";
        }else if($parent !== Null){
            $this->indent = $this->parent->indent;
        }
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
        return $this->current() !== False;
    }

    public function offsetExists($offset){
        return isset($this->children[$offset]);
    }

    public function offsetGet($offset){
        if(isset($this->children[$offset])){
            $node = $this->children[$offset];
            if($node instanceof LeafNode){
                return $node->typify();
            }else{
                $node->is_macro = True;
                return $node;
            }
        }else{
            return Null;
        }
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

    public function increase_indent(){
        $this->indent .= "\t";
        if(is_array($this->children)){
            foreach($this->children as $c){
                $c->increase_indent();
            }
        }
    }

    public function decrease_indent(){
        $this->indent = substr($this->indent, 1);
        if(is_array($this->children)){
            foreach($this->children as $c){
                $c->decrease_indent();
            }
        }
    }

    public function format_line($code, $prefix=""){
        if($this->indent === Null){
            $this->indent = $this->parent instanceof RootNode ? "" : $this->parent->indent."\t";
        }
        return $this->indent.$prefix.$code."\n";
    }

    public function format_line_indent($code, $prefix=""){
        return "\t".$this->format_line($code, $prefix);
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

    public function get_exprs(){
        return $this->children;
    }

    protected function split_children(){
        return array($this->children[0], array_slice($this->children, 1));
    }

    public function add_child($child, $tok=Null){
        $this->children[] = $child;
        if($tok === Null){
            # If $tok is null it'll mean that $child is not a leafnode
            # and has tokens of its own
            $this->tokens[] = $child;
        }else{
            $this->tokens[] = $tok;
        }
    }

    public function add_children($children){
        foreach($children as $c){
            $this->children[] = $c;
            $c->parent = $this;
        }
    }

    public function get_delims(){
        $node_cls_vars = get_class_vars(get_class($this));
        $delims = $node_cls_vars['delimiter_tokens'];
        return array(new $delims[0], new $delims[1]);
    }

    public function get_tokens($delims=Null){
        $delims = $delims === Null ? $this->get_delims() : $delims;
        $tokens = array($delims[0]);
        foreach($this->tokens as $tok){
            if(!($tok instanceof Token)){
                $tokens = array_merge($tokens, $tok->get_tokens());
            }else{
                $tokens[] = $tok;
            }
        }
        $tokens[] = $delims[1];
        return $tokens;
    }

    public function convert_to_list($return_as_array=False, $get_values=False){
        $list = array();
        foreach($this->tokens as $key=>$tok){
            if($tok instanceof Node){
                $list[] = $tok->convert_to_list($return_as_array, $get_values);
            }else{
                if($get_values)
                    $list[] = $tok->value;
                else
                    $list[] = $tok;
            }
        }
        if($return_as_array){
            return $list;
        }else{
            $delims = $this->get_delims();
            $list = PharenList::create_from_array($list);
            $list->delimiter_tokens = $delims;
            return $list;
        }
    }

    public function compile_args($args=Null){
        $output = array();
        for($x=0; $x<count($args); $x++){
            $arg = $args[$x];
            if($arg instanceof SpliceWrapper){
                $this->has_splice = True;
                array_splice($args, $x+1, 0, $arg->get_exprs());
            }else if(is_string($arg)){
                $output[] = $arg;
            }else{
                $output[] = $arg->compile();
            }
        }
        return $output;
    }

    public function search($value){
        foreach($this->children as $child){
            if($child->search($value)){
                return True;
            }
        }
        return False;
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
    
    public function get_func_name(){
        // Returns the compiled code for the function name and the arguments
        // in a function call.
        list($func_name_node, $args) = $this->split_children();
        if(!($func_name_node instanceof LeafNode) && !($func_name_node instanceof UnquoteWrapper)){
            $this->has_variable_func = True;
            $func_name = self::get_tmp_funcname_var();
            Node::$tmp .= $this->format_line($func_name." = ".$func_name_node->compile().";");
        }else{
            if($func_name_node instanceof VariableNode){
                $this->has_variable_func = True;
            }
            $func_name = $func_name_node->compile();
        }
        return $func_name;
    }

    public function create_partial($func){
        list($tmp_func, $tmp_name) = $func->get_tmp_func($this->parent);
        Node::$tmp .= $tmp_func;
        return '"'.$tmp_name.'"';
    }

    public function compile($is_statement=False){
        $scope = $this->get_scope();
        $func_name = $this->get_func_name();

        $func = new FuncInfo($func_name, $this->force_not_partial, array_slice($this->children, 1));
        if(MicroNode::is_micro($func_name)){
            $micro = MicroNode::get_micro($func_name);
            return $micro->get_body(array_slice($this->children, 1), $this->indent);
        }else if(!($this->parent instanceof MethodCallNode)
                && MacroNode::is_macro($func_name) && !MacroNode::$ghosting){
            $unevaluated_args = array_slice($this->children, 1);
            $arg_values = array();
            foreach($unevaluated_args as $key=>$arg){
                $unevaluated_args[$key] = $arg->convert_to_list();
                $arg_values[] = $arg->convert_to_list(False, True);
            }
            MacroNode::evaluate($func_name, $unevaluated_args);

            $macro_result = call_user_func_array($func_name, $arg_values);
            if($macro_result instanceof QuoteWrapper){
                $tokens = $macro_result->get_tokens();
                $parser = new Parser($tokens);
                $macro_result = $parser->parse($this->parent);
                $count = count($this->parent->children);
                $expanded = $this->parent->children[$count-1];
                array_pop($this->parent->children);
                if($expanded instanceof SpecialForm){
                    // This prevents it from adding unnecessary semicolons
                    $this->returns_special_form = True;
                }
                
                if($is_statement){
                    return trim($expanded->compile_statement(), ";\n");
                }else{
                    return $expanded->compile();
                }
            }else if(is_string($macro_result)){
                return '"'.$macro_result.'"';
            }else{
                return $macro_result;
            }
        }else if(!$this->has_splice && $func->is_partial()){
            return $this->create_partial($func);
        }

        $args = $this->compile_args(array_slice($this->children, 1));
        $args_string = implode(", ", $args);
        if($this->has_variable_func){
            $args[] = $func_name."[1]";
            $closure_args_string = implode(", ", $args);
            return "(is_string($func_name)?$func_name($args_string):{$func_name}[0]($closure_args_string))";
        }else{
            return "$func_name($args_string)";
        }
    }

    public function compile_statement($prefix=""){
        $code = $this->compile(True);
        if($this->returns_special_form){
            $semicolon="";
        }else{
            $semicolon=";";
        }
        return $this->format_statement($code.$semicolon, $prefix);
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
        $func_name = $this->get_func_name();
        $args = $this->compile_args(array_slice($this->children, 1));
        $func = new FuncInfo($func_name, $this->force_not_partial, array_slice($this->children, 1));
        if(!$this->has_splice && $func->is_partial()){
            return $this->create_partial($func);
        }
        $code = implode(' '.$func_name.' ', $args);
        return "(".$code.")";
    }

    public function compile_statement($prefix=""){
        $code = $this->compile();
        // Remove parentheses added by regular compile() since they're not
        // needed for statements. Makes pretty.
        $code = substr($code, 1, -1).";";
        return $this->format_statement($code, $prefix);
    }

    public function compile_return($prefix=""){
        return $this->format_statement("return ".$this->compile().";", $prefix);
    }
}

class RootNode extends Node{
    public function __construct(){
        // No parent to be passed to the constructor. It's Root all the way down.
        $this->parent = Null;
        $this->children = array();
        $this->scope = new Scope($this);
        $this->indent = "";
    }

    public function format_line_indent($code, $prefix=""){
        return $this->format_line($code, $prefix);
    }

    public function compile(){
        $code = "";
        if(isset(Flags::$flags['executable']) && Flags::$flags['executable']){
            $code .= $this->format_line("#! /usr/bin/env php");
        }

        $code .= $this->format_line("<?php");
        if(!isset(Flags::$flags['no-import-lang']) or Flags::$flags['no-import-lang'] == False){
            $code .= $this->format_line("require_once('".COMPILER_SYSTEM.DIRECTORY_SEPARATOR."lang.php"."');");
        }else if(Flags::$flags['no-import-lang'] == True){
            if(!isset(Flags::$flags['import-lexi-relative']) or Flags::$flags['import-lexi-relative'] == False){
                $prefix = "'".COMPILER_SYSTEM."'";
            } else {
                $prefix = "dirname(__FILE__)";
            }
            $code .= $this->format_line("require_once(".$prefix.".'".DIRECTORY_SEPARATOR."lexical.php"."');");
        }

        $code .= $this->scope->init_namespace_scope();
        $body = "";
        foreach($this->children as $child){
            $body .= $child->compile_statement();
        }
        $code .= $this->scope->init_lexical_scope().$body;
        return $code;
    }

}

class CommentNode extends Node{
    public function __construct($parent, $value){
        $this->parent = $parent;
        $this->value = $value;
    }

    public function compile(){
        return $this->format_line('# '.$this->value);
    }

    public function compile_statement(){
        return $this->compile();
    }
}

class LeafNode extends Node{
    static $reserved = array(
        '!=',
        '!=='
    );

    public $value;
    public $tok;

    public static function phpfy_name($name){
        $char_mappings = array(
            '-'=>'_',
            '?'=>'__question',
            '!'=>'__exclam'
        );
        foreach($char_mappings as $char=>$replacement){
            $name = str_replace($char, $replacement, $name);
        }
        return $name;
    }

    public function __construct($parent, $children, $value, $tok=Null){
        parent::__construct($parent);
        $this->children = Null;
        $this->value = $value;
        $this->tok = $tok;
    }

    public function typify(){
        $val = $this->compile();
        if(is_numeric($val)){
            return strpos($val, '.') === False ? intval($val) : floatval($val);
        }else if(strstr($val, '"')){
            return str_replace('"', '', $val);
        }else if(isset($keyword_rewrites[$val])){
            $val = $keyword_rewrites[$val];
        }else{
            return $val;
        }
    }

    public function convert_to_list($return_as_array=False, $get_value=False){
        return $get_value ? $this->tok->value : $this->tok;
    }

    public function search($value){
        return $this->value === $value;
    }

    public function get_last_func_call(){
        return $this;
    }

    public function compile(){
        return strlen($this->value) > 1 && !is_numeric($this->value[1]) && !in_array($this->value, self::$reserved) ?
            self::phpfy_name($this->value)
            : $this->value;
    }
}

class FuncValNode extends LeafNode{

    public function compile(){
        return '"'.parent::compile().'"';
    }
}

class VariableNode extends LeafNode{
    
    public function compile($in_binding=False){
        $scope = $this->get_scope();
        $varname = '$'.parent::compile();

        if($in_binding or $varname[1] == '$'){
            return $varname;
        }

        if($scope->find_immediate($varname) !== False){
            return $varname;
        }else if(($id = $scope->find($varname, False, False)) !== False){
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

    public function __toString(){
        return $this->value;
    }

    public function compile(){
        return '"'.$this->value.'"';
    }
}

class InstantiationNode extends Node{

    public function compile(){
        $class_name = $this->children[1]->compile();
        $arg_nodes = array_slice($this->children, 2);
        $args = $this->compile_args($arg_nodes);

        $args_string = implode(", ", $args);
        return "new $class_name($args_string)";
    }
}

class MethodCallNode extends Node{

    public function compile(){
        $obj_varname = $this->children[1]->compile();
        $node_chain = array_slice($this->children, 2);
        $chain = array();
        foreach($node_chain as $node){
            if($node instanceof VariableNode){
                $chain []= substr($node->compile(), 1); // $ sign not needed for field access
            }else{
                if(get_class($node) == "Node"){
                    $node->force_not_partial = True;
                }
                $chain []= $node->compile();
            }
        }
        return $obj_varname."->".implode("->", $chain);
    }
}

class StaticCallNode extends Node{

    public function compile(){
        $class_name = $this->children[1]->compile();
        $expr = $this->children[2]->compile();
        return $class_name.'::'.$expr;
    }
}

class SpecialForm extends Node{
    protected $body_index;

    public function compile_statement(){
        return $this->compile_statement();
    }

    public function compile_body($lines=false, $prefix="", $return=False){
        // Compile the body expressions of the special form according to
        // the start index of the first body expression.
        $body = "";

        // If there is a prefix then it should be indented as if it were an expression.
        $body_index = $lines === false ? $this->body_index : 0;
        $lines = $lines === false ? $this->children : $lines;
        $last = array_pop($lines);
        if($return){
            $last_line = $last->compile_return();
        }else{
            $last_line = $last->compile_statement($prefix);
        }

        foreach(array_slice($lines, $body_index) as $child){
            $body .= $child->compile_statement();
        }
        $body .= $last_line;
        return $body;
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
        return $this->children[count($this->children)-1];
    }

    public function get_last_func_call(){
        return $this->get_last_expr()->get_last_func_call();
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
    public $scope;

    public $params = array();
    public $is_partial;

    static function is_pharen_func($func_name){
        return isset(self::$functions[$func_name]);
    }

    static function get_pharen_func($func_name){
        return self::$functions[$func_name];
    }

    public function compile(){
        return $this->compile_statement();
    }

    public function compile_statement($prefix=""){
        $this->scope = $this->scope == Null ? new Scope($this) : $this->scope;

        $this->name = $this->children[1]->compile();
        self::$functions[$this->name] = $this;
        $this->params = $this->children[2]->children;

        $params = $this->get_param_names($this->params);
        $params_string = $this->build_params_string($params);
        $this->bind_params($params);
        list($body_nodes, $last_node) = $this->split_body_last();

        $body = "";
        $params_count = count($this->params);
        if($params_count > 0 && $this->params[$params_count-1] instanceof SplatNode){
            $param = $params[$params_count-1];
            $body .= $this->format_line("").$this->format_line_indent($param." = array_slice(func_get_args(), ".($params_count-1).");");
        }

        Node::$in_func++;
        if(Node::$in_func > 1){
            $this->decrease_indent();
        }
        if($this->is_tail_recursive($last_node)){
            list($body_nodes, $last_expr) = $this->split_body_tail();
            // Indent because the nodes below are nested inside the while loop
            $this->increase_indent();
            $body .= $this->format_line("while(1){");

            list($while_body_nodes, $while_last_node) = split_body_last($body_nodes);
            $body .= count($while_body_nodes) > 0 ? $this->compile_body($while_body_nodes) : "";
            $while_last_node->increase_indent();
            $body .= $while_last_node->compile_return();

            foreach($last_expr->get_body_nodes() as $n){
                $body .= $n->compile_statement();
            }

            $new_param_values = array_slice($last_expr->get_last_expr()->children, 1);
            $params_len = count($new_param_values);
            for($x=0; $x<$params_len; $x++){
                $val_node = $new_param_values[$x];
                $body .= $this->format_line_indent("\$__tailrecursetmp$x = " . $val_node->compile().";");
            }
            $x=0;
            foreach($params as $param){
                if(is_array($param)){
                    $param_name = $param[0];
                }else{
                    $param_name = $param;
                }
                $val_node = $new_param_values[$x];
                $body .= $this->format_line_indent($param_name. " = \$__tailrecursetmp$x;");
                $x++;
            }
            $body .= $this->format_line("}");
            $this->decrease_indent();
        }else{
            $body .= count($body_nodes) > 0 ? parent::compile_body($body_nodes) : "";
            $last = $last_node->compile_return();
            $body .= $last;
        }
        $body = $this->scope->get_lexical_bindings().$body;
        $lexings = $this->get_param_lexings($params);

        $code = $this->format_line("function ".$this->name.$params_string."{", $prefix).
            $lexings.
            $body.
            $this->format_line("}").$this->format_line("");

        if(Node::$in_func > 1){
            Node::$in_func--;
            Node::$tmpfunc .= $code;
            return $this->format_line("");
        }else{
            Node::$in_func--;
            return Node::add_tmpfunc($code);
        }
    }

    public function is_tail_recursive($last_node){
        $last_func_call = $last_node->get_last_func_call();
        return count($this->children) > 3 &&
            !($this instanceof MacroNode) &&
            !($last_func_call instanceof EmptyNode)
            && $this->children[1]->compile() == $last_node->get_last_func_call()->compile();
    }

    public function compile_last($node){
        return $node->compile_return($this->indent."\t");
    }

    public function get_param_lexings($varnames){
        $lexings = $this->scope->init_lexical_scope();
        foreach($varnames as $varname){
            if(is_array($varname)){
                $varname = $varname[0];
            }
            $lexings .= $this->scope->get_lexing($varname);
        }
        return $lexings;
    }

    public function get_param_names($param_nodes){
        $params = array();
        foreach($param_nodes as $node){
            if($node instanceof VariableNode || $node instanceof UnquoteWrapper){
                $params[] = $node->compile(True);
            }else if($node instanceof ListNode){
                $params[] = array($node->children[0]->compile(True), $node->children[1]);
            }
        }
        return $params;
    }

    public function bind_params($params){
        array_walk($params, array($this, "bind_param"));
    }

    public function bind_param($param){
        if(is_array($param)){
            $this->scope->bind($param[0], $param[1]);
        }else{
            $this->scope->bind($param, new EmptyNode($this));
        }
    }

    public function build_params_string($params){
        return '('.ltrim(array_reduce($params, array($this, "add_param")), ", ").')';
    }

    public function add_param($params, $param){
        if(is_array($param)){
            $params .= ", ".$param[0].'='.$param[1]->compile();
        }else{
            $params .= ", $param";
        }
        return $params;
    }

    public function split_body_tail(){
        list($body_nodes, $last) = parent::split_body_last();
        $body_nodes = array_merge($body_nodes, $last->get_body_nodes());
        $last = $last->get_last_expr();
        return array($body_nodes, $last);
    }
}

class MacroNode extends FuncDefNode{
    static $macros = array();
    static $literals = array();
    static $next_literal_id = 0;
    static $current_params;
    static $ghost_num = 0;
    static $ghosting = False;
    static $rescope_vars = False;

    public $args;
    public $evaluated = False;

    static function is_macro($name){
        return isset(self::$macros[$name]);
    }

    static function upghost(){
        self::$ghost_num++;
        self::$ghosting = True;
    }

    static function downghost(){
        self::$ghost_num--;
        if(self::$ghost_num === 0){
            self::$ghosting = False;
        }
    }

    static function evaluate($name, $args){
        $macronode = self::$macros[$name];
        $macronode->args = $args;
        $scope = $macronode->get_scope();
        foreach($macronode->children[2]->children as $param_node){
            if($param_node instanceof SplatNode){
                $scope->bind_tok($param_node->compile(True), $args);
                $values = array();
                foreach($args as $tok){
                    if($tok instanceof PharenCachedList){
                        $values[] = self::get_values_from_list($tok);
                    }else{
                        $values[] = $tok->value;
                    }
                }
                $scope->bind($param_node->compile(True), PharenList::create_from_array($values));
                break;
            }
            $tok = array_shift($args);
            $scope->bind_tok($param_node->compile(True), $tok);
            if($tok instanceof PharenCachedList){
                $scope->bind($param_node->compile(True), self::get_values_from_list($tok));
            }else{
                $scope->bind($param_node->compile(True), $tok->value);
            }
        }
        $code = $macronode->parent_compile();
        if($macronode->evaluated){
            Node::add_tmpfunc('');
            return;
        }else{
            eval(Node::add_tmpfunc($code));
            $macronode->evaluated = True;
        }
    }

    static function get_values_from_list($list){
        $values = array();
        foreach($list->cached_array as $el){
            if(!($el instanceof PharenCachedList)){
                $values[] = $el->value;
            }else{
                $values[] = self::get_values_from_list($el);
            }
        }
        return PharenList::create_from_array($values);
    }

    public function parent_compile(){
        MacroNode::$rescope_vars = True;
        $code = parent::compile_statement();
        MacroNode::$rescope_vars = False;
        return $code;
    }

    public function compile_statement(){
        self::upghost();
        $this->parent_compile();
        self::$current_params = array();
        self::downghost();
        Node::add_tmp('');

        $name = $this->children[1]->compile();
        $this->scope = new Scope($this);
        self::$macros[$name] = $this;
        return "";
    }

    public function bind_params($params){
        self::$current_params = $params;
        return parent::build_params_string($params);
    }
}

class QuoteWrapper{

    public $node;
    public $parent;
    public $children;
    private $literal_id;

    function __construct(Node $node, $literal_id){
        $this->node = $node;
        $this->node->scope = new Scope($this->node);
        $this->parent = $node->parent;
        $this->children =& $node->children;
        $this->literal_id = $literal_id;
    }

    public function compile_return(){
        $tmpfunc = Node::$tmpfunc;
        // Only compile to put any variables in scope
        MacroNode::upghost();
        MacroNode::$literals[$this->literal_id]->node->compile_return();
        MacroNode::downghost();
        Node::$tmpfunc = $tmpfunc;
        Node::add_tmp('');
        return 'return MacroNode::$literals['.$this->literal_id.'];'."\n";
    }

    public function get_tokens(){
        $tokens = $this->node->get_tokens();
        $scope = $this->node->parent->get_scope();
        foreach($tokens as $key=>$tok){
            if($tok->unquoted){
                $val = $scope->find(LeafNode::phpfy_name(ltrim($tok->value, '-')), True, False, True);
                if($val === False){
                    // $val is bound to a node defined outside the macro so we need to
                    // explicitly get its token
                    $val_node = $scope->find(LeafNode::phpfy_name(ltrim($tok->value, '-')), True, False);
                    if($val_node instanceof Node){
                        $val = $val_node->convert_to_list();
                    }else{
                        if(is_string($val_node)){
                            $val = new StringToken($val_node);
                        }else if(is_integer($val_node) or is_double($val_node)){
                            $val = new NumberToken($val_node);
                        }
                    }
                }
                if($val instanceof PharenCachedList){
                    $flattened = $val->flatten($val->delimiter_tokens);
                    $new_tokens = array_merge($new_tokens, $flattened);
                }else{
                    if($tok->value[0]=='-'){
                        $val = new UnstringToken(is_string($val) ? $val : $val->value);
                    }
                    $new_tokens[] = $val;
                }
            }else if($tok->unquote_spliced){
                $els = $scope->find($tok->value, True, False, True);
                foreach($els as $el){
                    if($el instanceof PharenCachedList){
                        $flattened = $el->flatten($el->delimiter_tokens);
                        $new_tokens = array_merge($new_tokens, $flattened);
                    }else{
                        $new_tokens[] = $el;
                    }
                }
            }else{
                $new_tokens[] = $tok;
            }
        }
        return $new_tokens;
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

    public function get_delims(){
        $delims = $this->node->get_delims();
        $delims[0]->unquoted = True;
        $delims[1]->unquoted = True;
        return $delims;
    }

    public function get_tokens(){
        return $this->node->get_tokens($this->get_delims());
    }

    public function compile(){
        $unstring = False;
        if(isset($this->node->value) and count($this->value) > 0 and $this->value[0] == '-'){
            $unstring = True;
            $this->value = substr($this->value, 1);
        }
        if($this->node instanceof VariableNode){
            $code = $this->node->compile(True);
        }else{
            $code = $this->node->compile();
        }
        if($this->node instanceof LeafNode){
            $lex = !in_array($code, MacroNode::$current_params);
            $val_node = $this->get_scope()->find($code, True, $lex);
            if(MacroNode::$ghosting){
                $val = Null;
            }else{
                if(is_object($val_node)){
                    $val = $val_node->compile();
                }else{
                    if(is_string($val_node)){
                        $val = '"'.$val_node.'"';
                    }else{
                        $val = $val_node;
                    }
                }
            }
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
        return $this->format_line("return ".$this->compile().";");
    }

    public function compile_statement($prefix){
        return $this->format_line($prefix.$this->compile().";");
    }
}

class SpliceWrapper extends UnquoteWrapper{
    public $as_collection = False;
    public $exprs;
    public $children;

    public function __construct($wrapped, $as_collection=False){
        if($as_collection){
            $this->as_collection = True;
            $this->exprs = $wrapped;
            $this->children &= $this->exprs;
        }else{
            $this->node = $wrapped;
        }
    }

    public function get_exprs(){
        if(MacroNode::$ghosting){
            return array();
        }
        if($this->as_collection){
            return $this->exprs;
        }else{
            $varname = str_replace('@', '', $this->node->compile(True));
            return $this->get_scope()->find($varname, True);
        }
    }

    private function compile_exprs($exprs, $prefix="", $f="compile_statement", $return=False){
        $code = "";
        $last = array_pop($exprs);
        foreach($exprs as $expr){
            $code .= $expr->$f();
            if($f == 'compile'){
                $code .= ", ";
            }
        }
        $code .= $return ? $last->compile_return() : $last->$f($prefix);
        return $code;
    }

    public function compile($prefix=""){
        if(MacroNode::$ghosting){
            return "";
        }
        return $this->compile_exprs($this->get_exprs(), $prefix, __FUNCTION__);
    }

    public function compile_statement($prefix=""){
        if(MacroNode::$ghosting){
            return "";
        }
        return $this->compile_exprs($this->get_exprs(), $prefix, __FUNCTION__);
    }

    public function compile_return(){
        if(MacroNode::$ghosting){
            return "";
        }
        return $this->compile_exprs($this->get_exprs(), "", "compile_statement", True);
    }

    public function get_last_func_call(){
        $exprs = $this->get_exprs();
        return ($count = count($exprs)) > 0 ? $exprs[count($exprs)-1] : new EmptyNode($this->node->parent);
    }
}

class LambdaNode extends FuncDefNode{
    static $in_lambda_compile = False;
    static $counter=0;

    public $scope;

    static function get_next_name(){
        return Node::$ns."__lambdafunc".self::$counter++;
    }

    public function compile(){
        self::$in_lambda_compile = True;
        $name = self::get_next_name();
        $name_node = new LeafNode($this, array(), $name);

        array_splice($this->children, 1, 0, array($name_node));
        $scopeid_node = new VariableNode($this, array(), "__closure_id");
        $this->children[2]->children[] = $scopeid_node;

        $code = parent::compile_statement();

        Node::$tmp .= $code.$this->format_line("");
        $scope = $this->parent->get_scope();
        if(count($scope->lexically_needed) === 0 || $scope->owner instanceof MacroNode){
            $scope_id_str = 'Null';
        }else{
            $scope_id_str = '$__scope_id';
        }

        array_splice($this->children, 1, 1);
        array_pop($this->children[1]->children);
        self::$in_lambda_compile = False;
        return 'array("'.$name.'", Lexical::get_closure_id("'.Node::$ns.'", '.$scope_id_str.'))';
    }

    public function compile_statement(){
        return $this->format_statement($this->compile());
    }

    public function compile_return(){
        // Indent because FuncDefNode decreases an indent
        return $this->format_line_indent("return ".$this->compile().";");
    }
}

class DoNode extends SpecialForm{
    public $body_index = 1;

    public function __construct($parent){
        parent::__construct($parent);
        $this->indent = $this->parent->indent;
    }

    public function compile(){
        return $this->compile_body();
    }

    public function compile_return($prefix=""){
        return $this->compile_body(False, $prefix, True);
    }

    public function compile_statement($prefix=""){
        return $this->compile_body(False, $prefix, False);
    }
}

class ClassNode extends SpecialForm{
    public $body_index = 2;

    public function generate($header, $body){
        return $this->format_line("class ".$header."{").$body.$this->format_line("}");
    }

    public function compile_statement(){
        $class_name = $this->children[1]->compile();
        $body = $this->compile_body();
        return $this->generate($class_name, $body);
    }
}

class ClassExtendsNode extends ClassNode{
    public $body_index = 3;

    public function compile_statement(){
        if(MacroNode::$ghosting)
            return "";

        $class_name = $this->children[1]->compile();
        $parent_class = $this->children[2]->children[0]->value;
        $body = $this->compile_body();
        return $this->generate($class_name." extends ".$parent_class, $body);
    }
}

class AccessModifierNode extends SpecialForm{
    public $body_index = 2;

    public function __construct($parent){
        parent::__construct($parent);
        $this->indent = $this->parent->indent;
    }

    public function compile_statement(){
        $access_modifier = $this->children[1]->compile();
        $code = $this->compile_body(false, $access_modifier." ");
        return $code;
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

    public function get_last_expr(){
        return $this->children[count($this->children)-1]->children[1];
    }

    public function compile(){
        $tmp_var = self::get_tmp_name();
        Node::$prev_tmp.= $this->format_line("").$this->compile_statement($tmp_var." = ");
        return $tmp_var;
    }

    public function compile_statement($prefix="", $return=False){
        $pairs = array_slice($this->children, 1);
        $if_pair = array_shift($pairs);
        $elseif_pairs = $pairs;

        $code = $prefix === "" ? "" : $this->format_line("").$this->format_line("$prefix Null;");   // Start with newline because current line already has tabs in it.

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

        $code = $prefix ? $this->format_line($prefix."Null;") : "";
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

class ListAccessNode extends Node{
    static $tmp_var = 0;

    public function __construct($parent=Null){
        parent::__construct($parent);
        $this->tokens = array(new ListAccessToken);
    }

    public function compile(){
        $list_name_node = $this->children[0];
        if($list_name_node instanceof LeafNode){
            $varname = $list_name_node->compile();
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
        $offset = (count($this->children) > 0 and $this->children[0] instanceof LeafNode and $this->children[0]->value === "dict") ? 1 : 0;
        $pairs = array_slice($this->children, $offset);

        // Code uses the paren-less syntax for dictionaries, so break it up into pairs
        if(count($pairs) > 0 && $pairs[0][0] === Null){
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

    static $delimiter_tokens = array("OpenBracketToken", "CloseBracketToken");

    public function compile(){
        if(($x = $this->is_range()) !== False){
            $step = $x > 1 ? $this->get_range_step() : 1;
            $first = intval($this->children[0]->compile());
            $end = intval(last($this->children)->compile());

            if($step == 1){
                return "range($first, $end)";
            }else{
                return "range($first, $end, $step)";
            }
        }
        return "array".parent::compile();
    }

    public function is_range(){
        for($x=0; $x<count($this->children); $x++){
            $el = $this->children[$x];
            // Restore tmp since the test compile below may mess with it
            $tmp = Node::$tmp;
            if($el instanceof Node && $el->compile() == '..'){
                Node::$tmp = $tmp;
                return $x;
            }
            Node::$tmp = $tmp;
        }
        return False;
    }

    public function get_range_step(){
        $el1 = $this->children[0]->compile();
        $el2 = $this->children[1]->compile();
        return intval($el2) - intval($el1);
    }

    public function compile_return(){
        return $this->format_statement("return ".$this->compile().";");
    }
}

class DefNode extends Node{

    public function compile_statement($prefix=""){
        $this->scope = $this->parent->get_scope();
        $varname = $this->children[1]->compile();

        $this->scope->bind($varname, $this->children[2]);
        $code = $this->format_statement($this->scope->get_binding($varname), $prefix);
        $code .= $this->scope->get_lexing($varname, True);

        return $code;
    }

    public function compile_return(){
        return $this->compile_statement("return ");
    }

}

class LocalNode extends Node{

    public function compile(){
        $varname = $this->children[1]->compile();
        $value = $this->children[2]->compile();

        return $varname." = ".$value;
    }
}

class BindingNode extends Node{

    public function __construct($parent){
        parent::__construct($parent);
    }

    public function compile_statement($return=False){
        $scope = $this->scope = new Scope($this);
        $scope->virtual = True;

        $pairs = $this->children[1]->children;
        if(!($pairs[0] instanceof ListNode)){
            $pairs = array_chunk($pairs, 2);
        }
        $varnames = array();
        $code = "";
        foreach($pairs as $pair_node){
            $varname = $pair_node[0]->compile();
            $varnames[] = $varname;

            $scope->bind($varname, $pair_node[1]);
            $code .= $this->format_statement($scope->get_binding($varname));
        }

        $body = "";
        $last_line = "";

        if($return === True){
            $last_line = array_pop($this->children)->compile_return();
        }
        foreach(array_slice($this->children, 2) as $line){
            $body .= $line->compile_statement();
        }

        $lexings = $this->scope->init_lexical_scope();
        foreach($varnames as $varname){
            $lexings .= $scope->get_lexing($varname);
        }
        return $this->scope->get_lexical_bindings().$code.$lexings.$body.$last_line;
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
        self::$INFIX_OPERATORS = array("+", "-", "*", ".", "/", "=", "=&", "<", ">", "<=", ">=", "===", "==", "!=", "!==", "instanceof");

        self::$reader_macros = array(
            "'" => "quote",
            "," => "unquote"
        );

        self::$value = array(
            "NameToken" => "VariableNode",
            "StringToken" => "StringNode",
            "NumberToken" => "LeafNode",
            "FuncValToken" => "FuncValNode",
            "SplatToken" => "SplatNode",
            "UnquoteToken" => "UnquoteNode",
            "UnstringToken" => "LeafNode"
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
            "do" => array("DoNode", "LeafNode", self::$values),
            "cond" => array("CondNode", "LeafNode", array(self::$cond_pair)),
            "if" => array("LispyIfNode", "LeafNode", self::$value, self::$value, self::$value),
            "$" => array("SuperGlobalNode", "LeafNode", "LeafNode", self::$value),
            "def" => array("DefNode", "LeafNode", "VariableNode", self::$value),
            "local" => array("LocalNode", "LeafNode", "VariableNode", self::$value),
            "let" => array("BindingNode", self::$list_form, array(self::$value)),
            "dict" => array("DictNode", "LeafNode", array(self::$value)),
            "dict-literal" => array("DictNode", array(self::$value)),
            "micro" => array("MicroNode", "LeafNode", "LeafNode", "LiteralNode", self::$values),
            "defmacro" => array("MacroNode", "LeafNode", "LeafNode", "LiteralNode", self::$values),
            "quote" => array("LiteralNode", "LeafNode", self::$values),
            "unquote" => array("UnquoteNode", "LeafNode", self::$values),
            "each_pair" => array("EachPairNode", "LeafNode", "VariableNode", "LiteralNode", self::$value),
            "->" => array("MethodCallNode", "LeafNode", self::$values),
            "::" => array("StaticCallNode", "LeafNode", "LeafNode", self::$values),
            "new" => array("InstantiationNode", "LeafNode", "LeafNode", self::$values),
            "class" => array("ClassNode", "LeafNode", "LeafNode", self::$values),
            "class-extends" => array("ClassExtendsNode", "LeafNode", "LeafNode", self::$list_form, self::$values),
            "access" => array("AccessModifierNode", "LeafNode", "LeafNode", self::$values)
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
            
            if($tok instanceof OpenParenToken or $tok instanceof OpenBracketToken or $tok instanceof OpenBraceToken){
                $expected_state = $this->get_expected($state);
                $added_tok = Null;
                if($this->is_literal($expected_state)){
                    if(!is_array($state[count($state)-1][0])){
                        array_shift($state[count($state)-1]);
                    }
                    array_push($state, self::$literal_form);
                }else if($tok instanceof OpenBracketToken){
                    array_push($state, self::$list_form);
                }else if($lookahead instanceof ListAccessToken){
                    array_push($state, self::$list_access_form);
                    $added_tok = $lookahead;
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
                    if($lookahead instanceof OpenParenToken){
                        # Remove the func name part of the func_call state
                        #   because the next expression is a func call that
                        #   acts as a func name
                        array_splice($state[count($state)-1], 1, 1);
                    }
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
                //$curnode->add_child(new CommentNode($curnode, $tok->value));
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
                $curnode->add_child($node, $tok);
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
            $tok_class = get_class($tok);
            if(isset($expected[$tok_class])){
                $class = $expected[$tok_class];
            }else{
                $class = "LeafNode";
            }
            array_shift($cur_state);
        }else{
            $class = $expected;
            array_shift($cur_state);
        }

        $node = new $class($parent, null, $tok->value, $tok);
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
    public static $lang_compiled = False;
    public static $flags = array();
}

function set_flag($flag, $setting=True){
    Flags::$flags[$flag] = $setting;
}

function unset_flag($flag){
    Flags::$flags[$flag] = False;
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
    return $phpcode;
}

function compile_lang(){
    if(Flags::$lang_compiled)
        return;
    $old_lang_setting = isset(Flags::$flags['no-import-lang']) ? Flags::$flags['no-import-lang'] : False;
    $old_lexi_setting = isset(Flags::$flags['import-lexi-relative']) ? Flags::$flags['import-lexi-relative'] : False;
    set_flag("no-import-lang");
    set_flag("import-lexi-relative");
    if(!$old_lang_setting){
        $lang_code = compile_file(COMPILER_SYSTEM . DIRECTORY_SEPARATOR . "lang.phn");
    }
    set_flag("import-lexi-relative", $old_lexi_setting);
    set_flag("no-import-lang", $old_lang_setting);
    Flags::$lang_compiled = True;
}
