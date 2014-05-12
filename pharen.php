<?php
if(version_compare(phpversion(), "5.4") < 0){
    error_reporting(E_ALL);
}else{
    error_reporting(E_ALL ^ E_STRICT);
}
define("COMPILER_SYSTEM", dirname(__FILE__));
define("EXTENSION", ".phn");

require_once(COMPILER_SYSTEM."/lang.php");
require_once(COMPILER_SYSTEM."/lib/sequence.php");
use Pharen\Lexical as Lexical;

// Some utility functions for use in Pharen

function is_assoc($xs){
    return array_keys($xs) !== range(0, count($xs)-1);
}

function split_body_last($xs){
    $body = array_slice($xs, 0, -1);
    $last = $xs[count($xs)-1];
    return array($body, $last);
}
            
class Annotation {
    public $typename;
    public $var;
    public $value_type;

    public function __construct($typename, $var, $value_type){
        $this->typename = $typename;
        $this->var = $var;
        $this->value_type = $value_type;
    }
}

class TypeSig {
    const SAME_VALTYPE = 3;
    const SAME_TYPE = 2;
    const ANY_MATCH = 1;

    public $any = True;
    public $annotations = array();

    public function add_type($ann){
        if($ann !== "Any"){
            $this->any = False;
        }
        array_unshift($this->annotations, $ann);
    }

    public function match(TypeSig $other){
        $thislen = count($this->annotations);
        $otherlen = count($other->annotations);

        if($thislen !== $otherlen) return 0;
        $score = 0;

        for($x=0; $x<$thislen; $x++){
            $thistype = $this->annotations[$x];
            $othertype = $other->annotations[$x];

            if($thistype === "Any" || $othertype === "Any"){
                $score += self::ANY_MATCH;
            }else if($thistype->value_type === $othertype->value_type){
                $score += self::SAME_VALTYPE;
            }else if($thistype->typename === $othertype->typename){
                $score += self::SAME_TYPE;
            }else{
                return 0;
            }
        }
        return $score;
    }
}

class Token implements IPharenComparable, IPharenHashable{
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

    public function __toString(){
        return $this->value;
    }
    
    public function eq($other){
        if(is_object($other)){
            if($other instanceof Token){
                return $this->value === $other->value;
            }
        }else{
            return $this->value === $other;
        }
    }

    public function hash(){
        return $this->value;
    }
}

class OpenParenToken extends Token{
    public $closer = "CloseParenToken";
}

class CloseParenToken extends Token{
}

class OpenBracketToken extends Token{
    public $closer = "CloseBracketToken";
}

class CloseBracketToken extends Token{
}

class OpenBraceToken extends Token{
    public $closer = "CloseBraceToken";
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

class AnnotationToken extends Token{
}

class FuncValToken extends Token{
}

class KeywordToken extends Token{
}

class Lexer{
    static $keyword_rewrites = array(
        'or' => 'pharen-or',
        'and' => 'pharen-and',
        'list' => 'pharen-list'
    );

    public $code;
    private $char;
    private $tok;
    private $state = "new-expression";
    private $toks = array();
    private $escaping = false;
    private $i=0;
	
    public function __toString(){
        return "<".__CLASS__.">";
    }

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

    public function finished(){
        $first_tok = $this->toks[0];
        if(!($first_tok instanceof OpenParenToken || $first_tok instanceof OpenBraceToken || $first_tok instanceof OpenBracketToken))
            return True;

        $open_class = get_class($first_tok);
        $close_class = $first_tok->closer;
        $open_instances = 0;
        $close_instances = 0;
        foreach($this->toks as $tok){
            if(get_class($tok) === $open_class){
                $open_instances++;
            }else if(get_class($tok) === $close_class){
                $close_instances++;
            }
        }
        return $close_instances >= $open_instances;
    }

    public function reset(){
        $this->i = 0;
        $this->code = "";
        $this->toks = array();
        $this->state = "new-expression";
        $this->esaping = False;
        $this->char = "";
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
                $this->tok->append($this->char);
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
            if($this->state == "new-expression"){
                $last_tok = $this->toks[count($this->toks)-1];
                if(isset(self::$keyword_rewrites[$last_tok->value])){
                    $last_tok->value = self::$keyword_rewrites[$last_tok->value];
                }
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
            }else if($this->char == '&' && trim($this->code[$this->i+1]) != ""){
                $this->tok = new SplatToken;
                $this->state = "append";
            }else if($this->char == '#'){
                $this->tok = new FuncValToken;
                $this->state = "append";
            }else if($this->char == '^'){
                $this->tok = new AnnotationToken;
                $this->state = "append";
            }else if($this->char == ':' && $this->code[$this->i+1] != ':'){
                $this->tok = new KeywordToken;
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

    public function __construct($name, $force_not_partial, $args, $scope){
        if(strstr($name, '$')){
            if(($scoped_node = $scope->find($name, True, Null)) !== False){
                if(isset($scoped_node->value)){
                    $this->name = $scoped_node->value;
                }
            }
        }else{
            $this->name = $name;
        }
        $this->args_given = $args;
        $this->force_not_partial = $force_not_partial;

        if(FuncDefNode::is_pharen_func($this->name)){
            $this->func = FuncDefNode::get_pharen_func($this->name);
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
            if(!($param instanceof ListNode
                || $param instanceof AnnotationNode
                || $param instanceof SplatNode)){
                $num++;
            }else{
                break;
            }
        }
        if($this->func instanceof LambdaNode){
            $num--;
        }
        return $num;
    }

    public function get_tmp_func($parent){
        $old_tmp = Node::$tmp;
        Node::$tmp = '';
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

        if(last($this->func->params instanceof SplatNode)){
            $body->add_child(new LeafNode($body, array(), "call_user_func_array"));
            $splatting = True;
        }else{
            $splatting = False;
        }
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

        $scopeid_node = new VariableNode($params, array(), "__closure_id");
        $params->add_child($scopeid_node);

        $ret = array($function->compile_statement().$parent->format_line(""), $name);
        Node::$tmp = $old_tmp;
        return $ret;
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
    public $postfix;
    public $replacements;

    public function __construct($owner, $postfix=False, $replacements=array()){
        $this->owner = $owner;
        $this->id = self::$scope_id++;
        $this->postfix = $postfix;
        $this->replacements = $replacements;
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
        return "$var_name = $value";
    }

    public function init_namespace_scope(){
        if(!RootNode::$ns){
            return $this->owner->format_line("Lexical::\$scopes['".Node::$ns."'] = array();");
        }else{
            return "";
        }
    }

    public function init_lexical_scope(){
        if(count($this->lexically_needed) === 0){
            return "";
        }else{
            return $this->owner->format_line_indent('$__scope_id = Lexical::init_closure("'.Node::$ns.'", '.$this->id.');');
        }
    }

    public function get_lexing($var_name, $force=False, $prefix=""){
        if($force){
            $this->lexically_needed[$var_name] = True;
        }
        if(!isset($this->lexically_needed[$var_name])){
            return "";
        }
        return $this->owner->format_line_indent($prefix.'Lexical::bind_lexing("'.Node::$ns."\", {$this->id}, '$var_name', $var_name);");
    }

    public function get_lexical_bindings(){
        $code = "";
        foreach($this->lexical_bindings as $var_name=>$id){
            $code .= $this->owner->format_line_indent($this->get_lexical_binding($var_name, $id).';');
        }
        return $code;
    }

    public function find($var_name, $return_value=False, $from_virtual=Null, $is_tok=False){
        $bindings = $is_tok ? $this->tok_bindings : $this->bindings;

        if($var_name[0] != '$'){
            $var_name = '$'.$var_name;
        }

        if(!array_key_exists($var_name, $bindings)){
            if($this->owner->parent !== Null){
                // Not virtual if variable was created from a non-virtual scope inside a virtual scope
                // eg: Anonymous function inside let
                $virtual = $this->virtual && ($from_virtual === Null || $from_virtual === True);
                return $this->owner->parent->get_scope()->find($var_name, $return_value, $virtual, $is_tok);
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
    const REPL_INPUT = "repl_input";
    static $in_func = 0;
    static $tmpfunc;
    static $lambda_tmp;
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
        $code = Node::$prev_tmp.Node::$tmp.$code.Node::$post_tmp;
        if(!LambdaNode::$in_lambda_compile){
            $code = Node::$lambda_tmp.$code;
            Node::$lambda_tmp = '';
        }
        if(!MacroNode::$ghosting && !MacroNode::$rescope_vars){
            Node::$prev_tmp = '';
            Node::$tmp = '';
        }
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
                $list[] = $val = $tok->convert_to_list($return_as_array, $get_values);
            }else{
                if($get_values && ($tok instanceof StringToken || $tok instanceof NumberToken)){
                    $tok_value = $tok->value;
                    if($tok instanceof NumberToken){
                        if(ctype_digit($tok_value)){
                            $tok_value = intval($tok_value);
                        }else{
                            $tok_value = floatval($tok_value);
                        }
                    }
                    $list[] = $tok_value;
                }else{
                    $list[] = $tok;
                }
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
                $output[] = $a = $arg->compile();
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
            $func_val = $func_name_node->compile();
            if($func_name_node instanceof FuncDefNode){
                $scope = $this->get_scope();
                $name_node = new LeafNode($this, array(), $func_name_node->get_name());
                $scope->bind($func_name, $name_node);
            }
            Node::$tmp .= $this->format_line($func_name." = ".$func_val.";");
        }else{
            if($func_name_node instanceof VariableNode){
                $this->has_variable_func = True;
                $ann = $func_name_node->get_annotation();
                if($ann && $ann->value_type){
                    $func_name = $ann->value_type;
                }else{
                    $func_name = $func_name_node->compile();
                }
            }else{
                $func_name = $func_name_node->compile();
            }

            if(isset(ExpandableFuncNode::$funcs[$func_name])){
                $func_node = ExpandableFuncNode::$funcs[$func_name];
            }
        }
        return $func_name;
    }

    public function create_partial($func){
        list($tmp_func, $tmp_name) = $func->get_tmp_func($this->parent);
        Node::$tmp .= $tmp_func;
        $scope = $this->get_scope();
        if(count($scope->lexically_needed) === 0 || $scope->owner instanceof MacroNode){
            $scope_id_str = 'Null';
        }else{
            $scope_id_str = '$__scope_id';
        }
        return 'new \PharenLambda(\''.RootNode::$ns.'\\\\'.$tmp_name.'\', Lexical::get_closure_id("'.Node::$ns.'", '.$scope_id_str.'))';
    }

    public function compile($is_statement=False, $is_return=False, $prefix=""){
        $scope = $this->get_scope();
        $func_name = $this->get_func_name();

        $func = new FuncInfo($func_name, $this->force_not_partial, array_slice($this->children, 1), $this->get_scope());

        if(MicroNode::is_micro($func_name)){
            $micro = MicroNode::get_micro($func_name);
            return $micro->get_body(array_slice($this->children, 1), $this->indent);
        }else if(!($this->parent instanceof MethodCallNode)
                && MacroNode::is_macro($func_name) && !MacroNode::$ghosting){
            $this->in_macro = True;
            $unevaluated_args = array_slice($this->children, 1);
            $arg_values = array();
            foreach($unevaluated_args as $key=>$arg){
                $unevaluated_args[$key] = $arg->convert_to_list();
                $arg_values[] = $arg->convert_to_list(False, True);
            }
            MacroNode::evaluate($func_name, $unevaluated_args);

            $macro_result = call_user_func_array($func_name, $arg_values);
            if($macro_result instanceof QuoteWrapper){
                $tokens = $macro_result->get_tokens(Null, $this->get_scope());
                $parser = new Parser($tokens);
                $macro_result = $parser->parse($this->parent);
                $count = count($this->parent->children);
                $expanded = $this->parent->children[$count-1];
                array_pop($this->parent->children);
                if($expanded instanceof SpecialForm or $expanded instanceof BindingNode){
                    // This prevents it from adding unnecessary semicolons
                    $this->returns_special_form = True;
                }else if(get_class($expanded) == 'Node'){
                    $old_tmp = Node::$tmp;
                    $old_prev_tmp = Node::$prev_tmp;
                    $old_lambda_tmp = Node::$lambda_tmp;
                    $old_tmpfunc = Node::$tmpfunc;
                    if(MacroNode::is_macro($expanded->get_func_name())){
                        $this->returns_special_form = True;
                    }
                    Node::add_tmp('');
                    Node::add_tmpfunc('');
                    Node::$tmp = $old_tmp;
                    Node::$prev_tmp = $old_prev_tmp;
                    Node::$lambda_tmp = $old_lambda_tmp;
                    Node::$tmpfunc = $old_tmpfunc;
                }
                
                if($is_statement){
                    $code = $expanded->compile_statement($prefix);
                    if(!$this->returns_special_form){
                        # Special forms shouldn't have semicolons anyway
                        $code = trim($code, ";\n");
                    }
                    return $code;
                }else if($is_return){
                    return $expanded->compile_return();
                }else{
                    return $expanded->compile();
                }
            }else if(is_string($macro_result)){
                return '"'.$macro_result.'"';
            }else if(is_bool($macro_result) || $macro_result===Null){
                return $macro_result ? "True" : "False";
            }else{
                return $macro_result;
            }
        }else if(!$this->has_splice && $func->is_partial()){
            return $this->create_partial($func);
        }else if(isset(ExpandableFuncNode::$funcs[$func_name])){
            $func_node = ExpandableFuncNode::$funcs[$func_name];
            $args = array_slice($this->children, 1);
            $typesig = new TypeSig;
            foreach($args as $arg){
                if($arg instanceof VariableNode){
                    if($ann = $arg->get_annotation()){
                        $typesig->add_type($ann);
                    }else{
                       $typesig->add_type("Any");
                    }
                }else{
                    $typesig->add_type("Any");
                }
            }

            $typesig_found = False;
            if(!$typesig->any){
                $typesig_map = ExpandableFuncNode::$typesig_map[$func_name];
                $highest_score = 0;
                foreach($typesig_map as $pair){
                    $func_typesig = $pair[0];
                    $score = $typesig->match($func_typesig);
                    if ($score > $highest_score) {
                        $typesig_found = True;
                        $func_node = $pair[1];
                    }
                }
            }

            $is_tail = $func_node->is_tail_recursive;
            if($is_tail && ($typesig_found || $func_name[0] === '^')){
                $func_name = $func_node->true_name;
            }else if(!$is_tail && (!$func_node->typed_only || $typesig_found)){
                return $func_node->inline(array_slice($this->children, 1));
            }
        }

        $args = $this->compile_args(array_slice($this->children, 1));
        $args_string = implode(", ", $args);
        return "$func_name($args_string)";
    }

    public function add_semicolon($code){
        if($this->returns_special_form){
            $semicolon="";
        }else{
            $semicolon=";";
        }
        return $code.$semicolon;
    }

    public function compile_statement($prefix=""){
        $code = $this->add_semicolon($this->compile(True, False, $prefix));
        if($this->in_macro){
            return $this->format_statement($code);
        }else{
            return $this->format_statement($code, $prefix);
        }
    }

    public function compile_return($prefix=""){
        $code = $this->compile(False, True);
        if(!$this->in_macro){
            $code = "return $code;";
        }
        return $this->format_statement($code, $prefix);
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
        $func = new FuncInfo($func_name, $this->force_not_partial, array_slice($this->children, 1), $this->get_scope());
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
    public static $raw_ns;
    public static $ns;
    public static $ns_string;
    public static $uses = array();
    public static $pharen_uses = array();
    public static $last_scope = Null;

    public function __construct($scope=Null){
        // No parent to be passed to the constructor. It's Root all the way down.
        $this->parent = Null;
        $this->children = array();
        $this->indent = "";
        $this->scope = $scope ? $scope : new Scope($this);
    }

    public function format_line_indent($code, $prefix=""){
        return $this->format_line($code, $prefix);
    }

    public function compile(){
        $code = "";
        $hashbang = "";
        if(Flags::is_true('executable')){
            $hashbang = $this->format_line("#! /usr/bin/env php");
        }

        $php_tag = $this->format_line("<?php");
        if(!Flags::is_true('no-import-lang')){
            $code .= $this->format_line("require_once('".COMPILER_SYSTEM."/"."lang.php"."');");
        }else{
            if(!Flags::is_true('import-lexi-relative')){
                $prefix = "'".COMPILER_SYSTEM."'";
            } else {
                $prefix = "dirname(__FILE__)";
            }
            $code .= $this->format_line("require_once(".$prefix.".'"."/"."lexical.php"."');");
        }
        $code .= $this->format_line("use Pharen\Lexical as Lexical;");

        $code .= $this->scope->init_namespace_scope();

        $body = "";
        foreach($this->children as $child){
            $body .= Node::add_tmpfunc($child->compile_statement());
        }

        $code .= $this->scope->init_lexical_scope().$body;

        if(self::$ns_string){
            $code = self::$ns_string . $code;
        }
        return $hashbang.$php_tag.$code;
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
    public $annotation;

    public static function phpfy_name($name){
        $char_mappings = array(
            '-'=>'_',
            '?'=>'__question',
            '!'=>'__exclam',
            '*'=>'__star',
            '.'=>'\\'
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
        }else if(isset(Lexer::$keyword_rewrites[$val])){
            $val = Lexer::$keyword_rewrites[$val];
        }else{
            return $val;
        }
    }

    public function convert_to_list($return_as_array=False, $get_value=False){
        if ($get_value) {
            if($this->tok instanceof NumberToken){
                return $this->tok->value;
            }
        }
        return $this->tok;
    }

    public function search($value){
        return $this->value === $value;
    }

    public function get_last_func_call(){
        return $this;
    }

    public function compile(){
        return strlen($this->value) > 1 && !is_numeric($this->value) && !in_array($this->value, self::$reserved) ?
            self::phpfy_name($this->value)
            : $this->value;
    }
}

class KeywordCallNode extends Node{
    public function compile(){
        return $this->compile_statement();
    }
    
    public function compile_statement(){
        $keyword = $this->children[1]->compile();
        $args = array_slice($this->children, 2);
        $compiled_args = "";
        foreach($args as $arg){
            $compiled_args []= $arg->compile();
        }
        $args_str = implode(' ', $compiled_args);
        return $this->format_statement($keyword . " " . $args_str . ";");
    }
}

class NamespaceNode extends KeywordCallNode{
    static $repling = False;

    public function compile_statement(){
        if(MacroNode::$ghosting) {
            return "";
        }
        array_unshift($this->children, Null);
        $this->children[1]->value = "namespace";
        if(empty(RootNode::$raw_ns) || self::$repling){
            RootNode::$ns_string = parent::compile_statement();
            $output = "";
        }else{
            $output = parent::compile_statement();
        }
        RootNode::$raw_ns = $this->children[2]->value;
        RootNode::$ns = $this->children[2]->compile();
        return $output;
    }

    public function compile(){
        $this->compile_statement();
        return "NULL";
    }
}

class UseNode extends KeywordCallNode{

    public function compile(){
        Node::$tmp .= $this->compile_statement();
        return "NULL";
    }

    public function compile_statement(){
        array_unshift($this->children, Null);
        $use = array($this->children[2]->compile());
        $pharen_use = array($this->children[2]->value);
        if(isset($this->children[4])){
            $use []=$this->children[4]->compile();
            $pharen_use []=$this->children[4]->value;
        }
        if(!isset(RootNode::$uses[RootNode::$ns])){
            RootNode::$uses[RootNode::$ns] = array();
            RootNode::$pharen_uses[RootNode::$ns] = array();
        }

        if(!isset(RootNode::$uses[RootNode::$ns][$use[0]])){
            RootNode::$uses[RootNode::$ns][$use[0]] = $use;
            RootNode::$pharen_uses[RootNode::$ns] []= $pharen_use;
        }

        // Require needs to be added after the compilation is done
        $file = str_replace("\\", "/", $use[0]).".php";
        if(stream_resolve_include_path($file)){
            Node::$tmp .= $this->format_line("include_once '" . $file . "';");
        }
        $code =  parent::compile_statement();
        return $code;
    }
}

class FuncValNode extends LeafNode{

    public function compile(){
        $name = parent::compile();
        if(RootNode::$ns && !function_exists($name) && !strpos($name, "\\")){
            $ns = RootNode::$ns;
        }else{
            $ns = "";
        }
        return "'"."$ns\\".$name."'";
    }
}

class VariableNode extends LeafNode{
    static $postfix_counter = 0;
    
    public function compile($in_binding=False){
        $scope = $this->get_scope();
        $varname = '$'.parent::compile();

        if(isset($scope->replacements[$varname])){
            $replacement = $scope->replacements[$varname];
            return $replacement;
        }

        if($in_binding or $varname[1] == '$'){
            return $varname;
        }

        if($scope->find_immediate($varname) !== False){
            return $varname;
        }else if(($id = $scope->find($varname, False, Null)) !== False){
            $scope->bind_lexical($varname, $id);
            return $varname;
        }else{
            return $varname;
        }
    }
    
    public function get_annotation(){
        $varname = '$'.parent::compile();
        $var = $this->get_scope()->find($varname, true, true);
        if($var && isset($var->annotation)){
            return $var->annotation;
        }else{
            return null;
        }
    }

    public function compile_nolookup(){
        return '$'.parent::compile();
    }
}

class SplatNode extends VariableNode{
}

class AnnotationNode extends LeafNode{

    public function compile(){
        $code = $this->value;
        $lexer = new Lexer($code);
        $toks = $lexer->lex();
        $tok = $toks[0];
        $value_type = "";
        switch(get_class($tok)){
        case 'NameToken':
            $type = parent::compile();
            break;
        case 'FuncValToken':
            $type = "callable";
            $value_type = $tok->value;
            break;
        }
        return array($type, $value_type);
    }
}

class StringNode extends LeafNode{

    public function __toString(){
        return $this->value;
    }

    public function compile(){
        return '"'.$this->value.'"';
    }
}

class KeywordNode extends StringNode{
    public function compile() {
        return parent::compile();
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

    public function compile_body($lines=false, $prefix="", $return=False, $omit_last_line=False){
        // Compile the body expressions of the special form according to
        // the start index of the first body expression.
        $body = "";

        // If there is a prefix then it should be indented as if it were an expression.
        $body_index = $lines === false ? $this->body_index : 0;
        $lines = $lines === false ? $this->children : $lines;
        $last_line = "";

        if ($this->body_index < count($lines)) {
            $last = array_pop($lines);
            if(!$omit_last_line){
                if($return){
                    $last_line = $last->compile_return();
                }else{
                    $last_line = $last->compile_statement($prefix);
                }
            }
        }

        foreach(array_slice($lines, $body_index) as $child){
            $body .= $child->compile_statement();
        }
        $body .= $last_line;
        return $body;
    }

    public function get_body_nodes(){
        # Where body is all but the last child of a special form node
        # Weird hack used since before body->children and this->children
        # pointed to the same array
        $old_children = $this->children;
        $body = clone $this;
        $body->children =& $old_children;
        $last_child = array_pop($body->children);
        foreach($body->children as $c){
            $c->parent = $body;
        }
        $last_body_nodes = $last_child->get_body_nodes(True);
        if(count($last_body_nodes) > 0){
            $last_body_node = $last_body_nodes[0]; // [0] because get_body_nodes returns [$body]
            $body->children[] = $last_body_node;
            $last_body_node->parent = $body;
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

class InterfaceNode extends SpecialForm{
    public $name;

    public function compile(){
        Node::$tmp .= $this->compile_statement();
        return '"'.$this->name.'"';
    }

    public function compile_statement(){
        $this->name = $name = $this->children[1]->compile();
        $signature_nodes = array_slice($this->children, 2);
        $signatures = "";
        foreach($signature_nodes as $node){
            $signatures .= $node->compile_statement();
        }
        return $this->format_line("interface $name{").$signatures.$this->format_line("}");
    }
}

class SignatureNode extends Node{
    public function compile(){
        return $this->compile_statement();
    }

    public function compile_statement(){
        $access = $this->children[1]->compile();
        $name = $this->children[2]->compile();
        $args = $this->children[3]->compile();
        return $this->format_statement($access." function ".$name.$args.";");
    }
}

class FuncDefNode extends SpecialForm{
    static $functions;

    protected $body_index = 3;
    public $scope;

    public $params = array();
    public $param_vals = array();
    public $is_partial;
    public $name;
    public $is_tail_recursive;

    static function is_pharen_func($func_name){
        if(!empty(RootNode::$ns) && strpos($func_name, "\\")){
            $last_slash = strrpos($func_name, "\\");
            $ns = substr($func_name, 0, $last_slash);
            $name = substr($func_name, $last_slash+1);
            foreach(RootNode::$uses[RootNode::$ns] as $use){
                if(count($use) == 2 && $use[1] == $ns && isset(self::$functions[$use[0]."\\".$name])){
                    $usename = $use[1]."\\".$name;
                    self::$functions[$usename] = self::$functions[$use[0]."\\".$name];
                    $func_name = $usename;
                    break;
                }
            }
        }
        return isset(self::$functions[$func_name]);
    }

    static function get_pharen_func($func_name){
        return self::$functions[$func_name];
    }

    public function compile(){
        Node::$in_func++;
        $this->compile_statement();
        Node::$in_func--;
        return '\''.RootNode::$ns."\\\\".$this->name.'\'';
    }

    public function add_to_functions_list($name){
        if(RootNode::$ns){
            self::$functions[RootNode::$ns."\\".$this->name] = $this;
        }
        self::$functions[$this->name] = $this;
    }

    public function get_name(){
        return $this->children[1]->compile();
    }

    public function compile_statement($prefix=""){
        $this->scope = $this->scope === Null ? new Scope($this) : $this->scope;

        if(!$this->name){
            $this->name = $this->get_name();
        }
        $this->add_to_functions_list($this->name);
        $this->params = $this->children[2]->children;

        $params = $this->get_param_names($this->params);
        $this->bind_params($params);
        list($body_nodes, $last_node) = $this->split_body_last();

        $body = "";
        $original_params = $params;
        $splats = $this->compile_splat_code($params);
        $params_string = $this->build_params_string($params);

        Node::$in_func++;
        if(Node::$in_func > 1){
            $this->decrease_indent();
        }
        if($this->is_tail_recursive($last_node)){
            $this->is_tail_recursive = true;

            // Final $last_expr->get_last_expr() -> The last function call, which provides the new args for the tail recurse
            // $while_body_nodes -> The body of the function, anything that's not the last "statementy" expression and isn't a return
            // $while_last_node -> What's returned when tail recursion stops

            list($body_nodes, $last_expr) = $this->split_body_tail();
            // Indent because the nodes below are nested inside the while loop
            $this->increase_indent();
            $body .= $this->format_line("while(1){");

            list($while_body_nodes, $while_last_node) = split_body_last($body_nodes);
            $body .= count($while_body_nodes) > 0 ? $this->compile_body($while_body_nodes) : "";
            $while_last_node->increase_indent();
            $body .= $while_last_node->compile_return();

            if($last_expr instanceof SpecialForm or $last_expr instanceof BindingNode){
                $body .= $this->compile_body($last_expr->get_body_nodes());
            }

            # Ugly hack to force it to find the last function call node
            while(get_class($last_expr->get_last_expr()) !== 'Node'){
                $last_expr = $last_expr->get_last_expr();
            }
            
            $new_param_values = array_slice($last_expr->get_last_expr()->children, 1);
            $params_len = count($new_param_values);
            $recur = "";
            $tmp = "";
            for($x=0; $x<$params_len; $x++){
                $val_node = $new_param_values[$x];
                $recur .= $this->format_line_indent("\$__tailrecursetmp$x = " . $val_node->compile().";");
                $tmp .= Node::$tmp;
                Node::$tmp = "";
            }
            $body .= Node::add_tmp($tmp.$recur);
            $x=0;
            if ($this instanceof LambdaNode) {
                array_pop($params);
            }
            foreach($params as $param){
                if(is_array($param)){
                    $param_name = $param[0];
                }else if($param instanceof Annotation){
                    $param_name = $param->var;
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
            Node::$prev_tmp = "";
            $body .= count($body_nodes) > 0 ? parent::compile_body($body_nodes) : "";
            $last = $this->compile_return_line($last_node);
            $body .= $last;
        }
        $body = $this->scope->get_lexical_bindings().$body;
        $lexings = $this->get_param_lexings($original_params);

        $code = $this->wrap_fn_headers($body, $params_string, $prefix, $splats, $lexings);

        return $this->handle_tmpfunc($code);
    }

    public function handle_tmpfunc($code){
        if(Node::$in_func > 1){
            Node::$in_func--;
            Node::$tmpfunc .= $code;
            return $this->format_line("");
        }else{
            Node::$in_func--;
            return Node::add_tmpfunc($code);
        }
    }

    public function wrap_fn_headers($body, $params_string, $prefix, $splats, $lexings){
        return $this->format_line("function ".$this->name.$params_string."{", $prefix).
            $splats.
            $lexings.
            $body.
            $this->format_line("}").$this->format_line("");
    }

    public function compile_return_line($node){
        return $node->compile_return();
    }

    public function is_tail_recursive($last_node){
        if ($last_node instanceof QuoteWrapper ||
            $last_node instanceof FuncDefNode) return False;
        $last_func_call = $last_node->get_last_func_call();
        $last_func_call_val = $last_func_call->compile();
        if ($last_func_call_val === "recur") return True;
        return count($this->children) > 3 &&
            !($this instanceof MacroNode) &&
            !($last_func_call instanceof EmptyNode)
            && $this->children[1]->compile() == $last_func_call_val;
    }

    public function compile_last($node){
        return $node->compile_return($this->indent."\t");
    }

    public function compile_splat_code(&$params){
        $params_count = count($params);
        $code = "";
        if($params_count > 0 && $this->params[$params_count-1] instanceof SplatNode){
            $param = array_pop($params);
            $this->scope->bind($param, new LeafNode($this, Null, $param));

            $code = $this->format_line("").$this->format_line_indent($param." = seq(array_slice(func_get_args(), ".($params_count-1)."));");
        }
        return $code;
    }

    public function get_param_lexings($varnames){
        $lexings = $this->scope->init_lexical_scope();
        foreach($varnames as $varname){
            if(is_array($varname)){
                $varname = $varname[0];
            }else if($varname instanceof Annotation){
                $varname = $varname->var;
            }
            $lexings .= $this->scope->get_lexing($varname);
        }
        return $lexings;
    }

    public function get_param_names($param_nodes){
        $params = array();
        $last_ann = Null;
        foreach($param_nodes as $node){
            if($node instanceof VariableNode || $node instanceof UnquoteWrapper){
                if($last_ann !== Null){
                    $type_data = $last_ann->compile();
                    $typename = $type_data[0];
                    $value_type = $type_data[1];
                    $var = $node->compile();
                    $params[] = new Annotation($typename, $var, $value_type);
                    $last_ann = Null;
                }else{
                    $params[] = $node->compile(True);
                }
            }else if($node instanceof AnnotationNode){
                $last_ann = $node;
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
            $this->param_vals[] = $param[0];
        }else{
            $val = new LeafNode($this, Null, $param);
            if($param instanceof Annotation){
                $val->annotation = $param;
                $this->scope->bind($param->var, $val);
                $this->param_vals[] = $param->var;
            } else {
                $this->scope->bind($param, $val);
                $this->param_vals[] = $param;
            }
        }
    }

    public function build_params_string($params){
        return '('.ltrim(array_reduce($params, array($this, "add_param")), ", ").')';
    }

    public function add_param($params, $param){
        if(is_array($param)){
            if($param[1] instanceof ListNode){
                $default = $param[1]->compile(True);
            }else{
                $default = $param[1]->compile();
            }
            $params .= ", ".$param[0].'='.$default;
        }else if($param instanceof Annotation){
            $params .= ",{$param->typename} {$param->var}";
        }else{
            $params .= ", $param";
        }
        return $params;
    }

    public function split_body_tail(){
        list($body_nodes, $last) = parent::split_body_last();
        $body_nodes = array_merge($body_nodes, $last->get_body_nodes());
        return array($body_nodes, $last->get_last_expr());
    }
}

class ExpandableFuncNode extends FuncDefNode{
    static $funcs = array();
    static $inline_counter = 0;
    static $tmp_var_counter = 0;
    static $replacement_counter = 0;
    static $name_counters = array();
    static $typesig_map = array();
    static $functypes = array();

    public $inlining = False;
    public $tmp_var;
    public $replacement_tmps;
    public $typesig;
    public $typed_only = False;
    public $parent_name;
    public $true_name;

    public static function get_next_tmp_var(){
        return '$__inline_result'.self::$tmp_var_counter++;
    }

    public static function get_next_replacement_var(){
        return '$__inline_arg'.self::$replacement_counter++;
    }

    public function compile_statement($prefix="", $replacements=array()){
        if(!$this->scope){
            $this->scope = new Scope($this,
                                     "__inline".self::$inline_counter++,
                                     new ArrayObject);
        }else{
            $this->scope->replacements->exchangeArray($replacements);
        }

        $this->typesig = new TypeSig;

        $code = parent::compile_statement($prefix);

        self::$funcs[$this->parent_name] = $this;

        if(!isset(self::$typesig_map[$this->parent_name])){
            self::$typesig_map[$this->parent_name] = array();
        }
        self::$typesig_map[$this->parent_name][] = array($this->typesig, $this);
        return $code;
    }

    public function inline($args){
        $this->inlining = true;
        # Todo: Modify VariableNode to change every instance of param
        # name to the argument
        $replacements = array();
        foreach($this->param_vals as $i=>$param) {
            $arg = $args[$i];
            if($arg instanceof LeafNode){
                $replacements[$param] = $arg->compile();
            } else {
                $tmp_var = self::get_next_replacement_var();
                $this->replacement_tmps .= $this->format_line($tmp_var
                    .' = '.$arg->compile().';');
                $replacements[$param] = $tmp_var;
            }
        }

        $simple = False;
        if(!$this->simple_inlinable()){
            $this->tmp_var = self::get_next_tmp_var();
        }else{
            $simple = True;
            $this->tmp_var = "";
        }

        $code = $this->compile_statement("", $replacements);

        if($simple){
            return $code;
        }else{
            return $this->tmp_var;
        }
    }

    public function get_mainfunc_node(){
        $parent_name = parent::get_name();
        return FuncDefNode::$functions[$parent_name];
    }

    public function generate_functype($name) {
        if(isset(self::$functypes[$name])){
            return;
        }
        self::$functypes[$name] = True;
        $code = "class_alias('PharenLambda', '$name');";
        Node::$tmpfunc .= $this->format_statement($code);
    }

    public function get_name(){
        $parent_name = parent::get_name();
        if($this->children[1] instanceof AnnotationNode){
            $this->generate_functype($parent_name);
            $this->parent_name = '^'.$parent_name;
        }else{
            $this->parent_name = $parent_name;
        }

        if($this->inlining){
            return $parent_name;
        }
        if(!isset(self::$name_counters[$parent_name])){
            self::$name_counters[$parent_name] = 1;
            if(isset(FuncDefNode::$functions[$parent_name])){
                $this->typed_only = True;
                $this->true_name = $parent_name."1";
            }else{
                $this->true_name = $parent_name;
            }
            return $this->true_name;
        }else{
            return $this->true_name = $parent_name.++self::$name_counters[$parent_name];
        }
    }

    public function handle_tmpfunc($code){
        if(!$this->inlining){
            return parent::handle_tmpfunc($code);
        }

        if(Node::$in_func > 1){
            Node::$in_func--;
        }
        return $code;
    }

    public function simple_inlinable(){
        if (count($this->children) > 4) return False;
        $fourth_node = $this->children[3];
        if($fourth_node instanceof SpecialForm) return False;
        if($fourth_node instanceof Node && count($fourth_node->children) >= 3) {
            if(MacroNode::is_macro($fourth_node->get_func_name())){
                return False;
            }
        }

        return True;
    }

    public function wrap_fn_headers($body, $params_string, $prefix, $splats, $lexings){
        if(!$this->inlining){
            return parent::wrap_fn_headers($body, $params_string, $prefix, $splats, $lexings);
        }

        Node::$tmp .= $lexings;
        Node::$tmp .= $splats;
        Node::$tmp .= $this->replacement_tmps;

        if($this->simple_inlinable()){
            return trim($body, " \t\n;");
        }else{
            Node::$tmp .= $body;
            return "";
        }
    }

    public function compile_return_line($node){
        if(!$this->inlining){
            return parent::compile_return_line($node);
        }

        if($this->tmp_var){
            $prefix = $this->tmp_var.' = ';
        } else {
            $prefix = "";
        }
        return $node->compile_statement($prefix);
    }

    public function bind_param($param){
        if($param instanceof Annotation){
            $this->typesig->add_type($param);
        } else {
            $this->typesig->add_type("Any");
        }
        parent::bind_param($param);
    }

    public function bind_params($params){
        if(!$this->inlining){
            return parent::bind_params($params);
        }
        return Null;
    }
}

class AnnotatedFuncNode extends ExpandableFuncNode{
    public function compile_statement($prefix="", $replacements=array()){
        if (!isset($this->children[3])) {
            $parent_node = $this->get_mainfunc_node();
            $this->children = array_merge($this->children, array_slice($parent_node->children, 3));
            $len = count($this->children);
            for($x=3; $x<$len; $x++){
                $this->children[$x]->parent = $this;
            }
        }
        $code = parent::compile_statement($prefix, $replacements);
        return $code;
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
    public $macro_ns;
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
            }else if($tok instanceof PharenHashMap || $tok instanceof QuoteWrapper){
                $scope->bind($param_node->compile(True), $tok);
            }else{
                $scope->bind($param_node->compile(True), $tok->value);
            }
        }
        $old_ns = RootNode::$ns;
        RootNode::$ns = $macronode->macro_ns;
        $old_tmpfunc = Node::$tmpfunc;
        $old_tmp = Node::$tmp;
        $code = $macronode->parent_compile();
        RootNode::$ns = $old_ns;
        if($macronode->evaluated || function_exists($name)){
            Node::add_tmpfunc('');
            Node::$tmpfunc = $old_tmpfunc;
        }else{
            $code = "use Pharen\Lexical as Lexical;\n"
                .Node::add_tmpfunc($code);
            eval($code);
            $macronode->evaluated = True;
        }
        Node::$tmp = $old_tmp;
    }

    static function get_values_from_list($list){
        $values = array();
        foreach($list->cached_array as $el){
            if($el instanceof PharenEmptyList){
                continue;
            }elseif(!($el instanceof PharenList)){
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
        $this->macro_ns = RootNode::$ns;
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
    public $lexer;
    private $literal_id;

    function __construct(Node $node, $literal_id){
        $this->node = $node;
        $this->node->scope = new Scope($this->node);
        $this->parent = $node->parent;
        $this->children =& $node->children;
        $this->literal_id = $literal_id;
    }

    public function str(){
        return 'MacroNode::$literals['.$this->literal_id.']';
    }

    public function compile_return(){
        $tmpfunc = Node::$tmpfunc;
        // Only compile to put any variables in scope
        MacroNode::upghost();
        MacroNode::$literals[$this->literal_id]->node->compile_return();
        MacroNode::downghost();
        Node::$tmpfunc = $tmpfunc;
        Node::add_tmp('');
        return $this->format_line("return ".$this->str().";");
    }

    public function convert_to_list($return_as_array=False, $get_value=False){
        $old_tokens = $this->node->tokens;
        $this->node->tokens = $this->str_tokens(True);
        $list = $this->node->convert_to_list($return_as_array, $get_value);
        $this->node->tokens = $old_tokens;
        return $list;
    }

    public function str_tokens($trim_parens=False){
        $pharen_code = "(:: MacroNode (:literals {$this->literal_id}))";
        $lexer = new Lexer($pharen_code);
        $toks = $lexer->lex();
        if($trim_parens){
            return array_slice($toks, 1, -1);
        }else{
            return $toks;
        }
    }

    public function get_tokens($tokens=Null, $caller_scope=Null){
        if($tokens === Null) {
            $tokens = $this->node->get_tokens();
            $scope = $this->node->parent->get_scope();
        }else{
            $scope = $caller_scope;
        }
        $new_tokens = array();
        $this->lexer = new Lexer("");
        foreach($tokens as $key=>$tok){
            if($tok->unquoted){
                $val = $scope->find(LeafNode::phpfy_name(ltrim($tok->value, '-')), True, Null, True);
                if($val === False){
                    // $val is bound to a node defined outside the macro so we need to
                    // explicitly get its token
                    $val_node = $scope->find(LeafNode::phpfy_name(ltrim($tok->value, '-')), True, Null, False);
                    if($val_node instanceof Node){
                        $val = $val_node->convert_to_list();
                    }else if($val_node instanceof PharenList || $val_node instanceof PharenHashMap){
                        $val = $val_node;
                    }else{
                        $val = $this->lex_val($val_node);
                    }
                }
                if($val instanceof PharenList || $val instanceof PharenHashMap){
                    $flattened = $this->flatten($val);
                    $flattened = $this->get_tokens($flattened, $caller_scope);
                    $new_tokens = array_merge($new_tokens, $flattened);
                }else if($val instanceof QuoteWrapper){
                    $new_tokens = array_merge($new_tokens, $val->str_tokens());
                }else{
                    if($tok->value[0]=='-'){
                        $val = new UnstringToken(is_string($val) ? $val : $val->value);
                    }
                    $new_tokens[] = $val;
                }
            }else if($tok->unquote_spliced){
                $els = $scope->find($tok->value, True, Null, True);
                if($els === False){
                    $phpfied = LeafNode::phpfy_name($tok->value);
                    $els = $scope->find($phpfied, True, Null, False);
                }
                if($els instanceof Node){
                    $closure_id = Lexical::get_closure_id(Node::$ns, $scope->id);
                    $els = Lexical::get_lexical_binding(Node::$ns, $scope->id, '$'.$phpfied, $closure_id);
                }
                foreach($els as $el){
                    if($el instanceof PharenList && isset($el->delimiter_tokens)){
                        $flattened = $this->flatten($el);
                        $new_tokens = array_merge($new_tokens, $flattened);
                    }else{
                        $new_tokens[] = $this->lex_val($el);
                    }
                }
            }else{
                $new_tokens[] = $tok;
            }
        }
        return $new_tokens;
    }

    public function lex_val($val){
        if($val instanceof Token){
            return $val;
        }else{
            $this->lexer->reset();
            if(is_string($val)){
                $val = '"'.$val.'"';
            }
            $this->lexer->code = (string)$val;
            $toks = $this->lexer->lex();
            return $toks[0];
        }
    }

    public function flatten($list){
        $delims = $list->delimiter_tokens;
        $tokens = array();
        $tokens []= new $delims[0];

        if ($list instanceof PharenHashMap) {
            $listified = array();
            foreach ($list as $key=>$val) {
                $listified[] = $key;
                $listified[] = $val;
            }
            $list = $listified;
        }

        foreach($list as $el){
            if($el instanceof PharenList){
                $tokens = array_merge($tokens, $this->flatten($el));
            }else{
                $tokens[] = $this->lex_val($el);
            }
        }
        $tokens []= new $delims[1];
        return $tokens;
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
            $scope = $this->get_scope();
            return $scope->find($varname, True, Null, True);
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
    public $name = Null;

    static function get_next_name(){
        return Node::$ns."__lambdafunc".self::$counter++;
    }

    public function get_name(){
        if(!$this->name){
            return $this->name = self::get_next_name();
        }else{
            return $this->name;
        }
    }

    public function compile(){
        self::$in_lambda_compile = True;
        $name = $this->get_name();
        $name_node = new LeafNode($this, array(), $name);

        array_splice($this->children, 1, 0, array($name_node));
        $scopeid_node = new VariableNode($this, array(), "__closure_id");
        $this->children[2]->children[] = $scopeid_node;

        $code = parent::compile_statement();

        Node::$lambda_tmp .= $code.$this->format_line("");
        $scope = $this->parent->get_scope();
        if(count($scope->lexically_needed) === 0 || $scope->owner instanceof MacroNode){
            $scope_id_str = 'Null';
        }else{
            $scope_id_str = '$__scope_id';
        }

        array_splice($this->children, 1, 1);
        array_pop($this->children[1]->children);
        self::$in_lambda_compile = False;
        $ns = str_replace('\\', '\\\\', RootNode::$ns);
        return 'new \PharenLambda("'.$ns.'\\\\'.$name.'", Lexical::get_closure_id("'.Node::$ns.'", '.$scope_id_str.'))';
    }

    public function compile_statement(){
        return $this->format_statement($this->compile().";");
    }

    public function compile_return(){
        // Indent because FuncDefNode decreases an indent
        return $this->format_line_indent("return ".$this->compile().";");
    }

    public function compile_splat_code($params){
        $params_count = count($this->params);
        $code = "";
        if($params_count > 1 && $this->params[$params_count-2] instanceof SplatNode){
            $param = $params[count($params)-2];
            array_splice($params, count($params)-2, 1);
            $code = $this->format_line('$__splatargs = func_get_args();');
            $code .= $this->format_line($param." = seq(array_slice(\$__splatargs, ".($params_count-2).", count(\$__splatargs) - 1));");
            $code .= $this->format_line('$__closure_id = last($__splatargs);');
        }
        return $code;
    }
}

class DoNode extends SpecialForm{
    public $body_index = 1;
    public static $tmp_num;

    static function get_tmp_name(){
        return "\$__dotmpvar".self::$tmp_num++;
    }
    
    public function __construct($parent){
        parent::__construct($parent);
        $this->indent = $this->parent->indent;
    }

    public function compile(){
        $tmp_var = self::get_tmp_name();
        Node::$tmp .= $this->compile_body(False, $tmp_var." = ");
        return $tmp_var;
    }

    public function compile_return($prefix=""){
        return $this->compile_body(False, $prefix, True);
    }

    public function compile_statement($prefix=""){
        return $this->compile_body(False, $prefix, False);
    }

    public function compile_without_last($prefix=""){
        return $this->compile_body(False, $prefix, False, True);
    }

    public function get_body_nodes($recur=False){
        if(!$recur){
            return parent::get_body_nodes();
        }else{
            return array();
        }
    }
}

class ClassNode extends SpecialForm{
    public $body_index = 2;
    public $class_name;

    public function generate($header, $body){
        return $this->format_line("class ".$header."{").$body.$this->format_line("}");
    }

    public function compile(){
        Node::$tmp .= $this->compile_statement();
        return '"'.$this->class_name.'"';
    }

    public function compile_statement(){
        $class_name = $this->children[1]->compile();
        $this->class_name = $class_name;
        $body = isset($this->children[2]) ? $this->compile_body() : "";
        return $this->generate($class_name, $body);
    }
}

class ClassExtendsNode extends ClassNode{
    public $body_index = 3;

    public function compile_statement(){
        if(MacroNode::$ghosting)
            return "";

        $class_name = $this->children[1]->compile();
        $this->class_name = $class_name;
        $parent_class = $this->children[2]->compile();
        $body = isset($this->children[3]) ? $this->compile_body() : "";
        return $this->generate("$class_name extends $parent_class", $body);
    }
}

class DefRecordNode extends ClassNode{
    static $type_attrs = array();
    static $existing_records = array();

    public $body_index = 2;

    public function process_attrs(){
        $attrs = array_slice($this->children, $this->body_index);
        $attr_body = "";
        $constructor_body = "";
        $attrnames = array();
        self::$type_attrs[$this->class_name] = array();
        $this->increase_indent();
        foreach($attrs as $index=>$attr){
            $attrname = trim($attr->compile(), '"');
            if($attrname[0] === '$'){
                $no_dollar = substr($attrname, 1);
            }else{
                $no_dollar = $attrname;
                $attrname = '$'.$attrname;
            }
            $attrnames[] = $attrname;
            self::$type_attrs[$this->class_name][$attrname] = $index;
            $constructor_body .= $this->format_line_indent('$this->'.$no_dollar.' = '.$attrname.';');
            $attr_body .= $this->format_line("public ".$attrname.";");
        }
        $this->decrease_indent();
        $constructor_header = "function __construct("
            .implode($attrnames, ", ")."){";
        return $this->format_line($attr_body)
            .$this->format_line_indent($constructor_header)
            .$constructor_body
            .$this->format_line_indent("}");
    }

    public function compile_statement(){
        if(MacroNode::$ghosting)
            return "";

        $name = $this->children[1]->compile();
        $this->class_name = $name;
        self::$existing_records[$this->children[1]->value] = $this;
        $body = $this->process_attrs();
        return $this->format_statement($this->generate($name, $body));
    }

    public function compile(){
        Node::$tmp .= $this->compile_statement();
        return '$'.$this->class_name;
    }

    public function compile_as_string(){
        Node::$tmp .= $this->compile_statement();
        return '"'.$this->class_name.'"';
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
    public $cond_prev_tmp;
    public $cond_tmp;
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

        $if_condition = $if_pair->children[0]->compile();
        $if_then_code_children = array_slice($if_pair->children, 1);
        $code .= $this->compile_if($if_condition, $if_then_code_children, $prefix, $return);
        foreach($elseif_pairs as $elseif_pair){
            $condition = $elseif_pair->children[0]->compile();
            $then_code_children = array_slice($elseif_pair->children, 1);
            #$code = Node::$prev_tmp.Node::$tmp.$code;
            if($condition == "TRUE" or strtoupper($condition) == "ELSE"){
                $code .= $this->compile_else($condition, $then_code_children, $prefix, $return);
            }else{
                $code .= $this->compile_elseif($condition, $then_code_children, $prefix, $return);
            }
        }
        $this->unstore_tmp();
        return Node::add_tmp($this->cond_prev_tmp.$this->cond_tmp.$code);
    }

    public function compile_return(){
        return $this->compile_statement(False, True);
    }

    public function capture_tmp(){
        $this->cond_prev_tmp .= Node::$prev_tmp;
        $this->cond_tmp .= Node::$tmp;
        Node::$prev_tmp = "";
        Node::$tmp = "";
    }

    public function unstore_tmp(){
        Node::$prev_tmp = $this->cond_prev_tmp;
        Node::$tmp = $this->cond_tmp;
        $this->cond_prev_tmp = "";
        $this->cond_tmp = "";
    }

    public function compile_header($condition, $stmt_type){
        if($stmt_type == 'else'){
            return $this->format_line("else{");
        }else{
            $this->capture_tmp();
            return $this->format_statement("$stmt_type($condition){");
        }
    }

    public function compile_if($condition, $then_code_children, $prefix, $return=False, $stmt_type="if"){
        $header = $this->compile_header($condition, $stmt_type);
        $body = $this->compile_body($then_code_children, $prefix, $return);

        return $header
            .$body
        .$this->format_line("}");
    }

    public function compile_elseif($condition, $then_code_children, $prefix, $return=False){
        return $this->compile_if($condition, $then_code_children, $prefix, $return, "else if");
    }

    public function compile_else($condition, $then_code_children, $prefix, $return=False){
        return $this->compile_if($condition, $then_code_children, $prefix, $return, "else");
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
        $tmp_from_cond = Node::add_tmp("");

        $code = $prefix ? $this->format_line($prefix."Null;") : "";
        $code .=  $this->format_line("if($cond){")
                      .$this->children[2]->$compile_func($prefix)
                  .$this->format_line("}");

        if(isset($this->children[3])){
            $code .= $this->format_line("else{")
                .$this->children[3]->$compile_func($prefix)
            .$this->format_line("}");
        }
        return $tmp_from_cond.$code;
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

class TryNode extends SpecialForm {
    static $tmp_num = 0;

    static function get_tmp_name(){
        return "\$__trytmpvar".self::$tmp_num++;
    }

    public function compile(){
        $tmp_var = self::get_tmp_name();
        Node::$prev_tmp.= $this->format_line("").$this->compile_statement($tmp_var." = ");
        return $tmp_var;
    }

    public function compile_statement($prefix="", $return=False){
        $compile_func = $return ? "compile_return" : "compile_statement";

        $code = !($prefix) ? "" :
            $this->format_line("").$this->format_line("$prefix Null;");

        $code .= $this->format_line("try {");
        $code .= $this->children[1]->$compile_func($prefix);
        $code .= $this->format_line("}");

        foreach(array_slice($this->children, 2) as $children) {
            //$children->indent = $this->parent instanceof RootNode ? "" : $this->parent->indent;
            $children->indent = ($this->indent === Null) ? "" : $this->indent;
            $code .= $children->$compile_func($prefix);
        }

        return $code;
    }

    public function compile_return(){
      return $this->compile_statement(False, True);
    }

    public function get_last_func_call(){
        return $this->children[sizeof($this->children) - 1]->get_last_func_call();
    }

    public function get_last_expr(){
        return $this->children[sizeof($this->children) - 1];
    }
}

class CatchNode extends TryNode {
    public $type_name;

    public function compile_statement($prefix="", $return=False){
        $compile_func = $return ? "compile_return" : "compile_statement";

        $type_name = $this->children[1]->compile();
        $var_name  = $this->children[2]->compile();

        $this->type_name = $type_name;

        $this->children[3]->indent = $this->indent . "\t";

        $code  = $this->format_line("catch ($type_name $var_name) {");
        $code .= $this->children[3]->$compile_func($prefix);
        $code .= $this->format_line("}");

        return $code;
    }
}

class ListAccessNode extends Node{
    static $tmp_var = 0;

    public function __construct($parent=Null){
        parent::__construct($parent);
        $this->tokens = array(new ListAccessToken);
    }

    public function compile($prefix=""){
        $list_name_node = $this->children[0];
        $type = Null;
        if($list_name_node instanceof LeafNode){
            $varname = $list_name_node->compile();
            $type = $list_name_node->get_annotation();
        }else{
            $varname = '$__listAcessTmpVar'.self::$tmp_var++;
            Node::$tmp .= $varname.' = '.$this->children[0]->compile().";\n";
        }
        $indexes = "";

        $start_child_index = 1;
        foreach(array_slice($this->children, 1) as $index){
            $indexes .= '['.$index->compile().']';
        }
        return $varname.$indexes;
    }

    public function compile_statement($prefix=""){
        return $this->format_statement($this->compile().";", $prefix);
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
    static $delimiter_tokens = array("OpenBraceToken", "CloseBraceToken");

    public function compile(){
        // Use an offset when using the (dict... notation for dictionaries
        $offset = (count($this->children) > 0 and $this->children[0] instanceof LeafNode and $this->children[0]->value === "dict") ? 1 : 0;
        $pairs = array_slice($this->children, $offset);

        // Code uses the paren-less syntax for dictionaries, so break it up into pairs
        $pairs = array_chunk($pairs, 2);

        $mappings = array();
        $code = "";
        foreach($pairs as $pair){
            $key = $pair[0]->compile();
            $value = $pair[1]->compile();
            $mappings[] = "$key => $value";
        }
        return "hashify(array(".implode(", ", $mappings)."))";
    }

    public function compile_statement(){
        return $this->compile().";\n";
    }

    public function convert_to_list($return_as_array=False, $get_values=False){
        $list = array();
        foreach($this->tokens as $key=>$tok){
            if($tok instanceof Node){
                $list[] = $val = $tok->convert_to_list($return_as_array, $get_values);
            }else{
                if($get_values && ($tok instanceof StringToken || $tok instanceof NumberToken)){
                    $tok_value = $tok->value;
                    if($tok instanceof NumberToken){
                        if(ctype_digit($tok_value)){
                            $tok_value = intval($tok_value);
                        }else{
                            $tok_value = floatval($tok_value);
                        }
                    }
                    $list[] = $tok_value;
                }else{
                    $list[] = $tok;
                }
            }
        }

        $pairs = array_chunk($list, 2);
        $dict = array();
        foreach($pairs as $pair){
            $dict[$pair[0]] = $pair[1];
        }

        if($return_as_array){
            return $dict;
        }else{
            $pharen_map = new PharenHashMap($dict);
            $pharen_map->delimiter_tokens = $this->get_delims();
            return $pharen_map;
        }
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

    public function compile($no_vector=False){
        if(($x = $this->is_range()) !== False){
            $step = $x > 1 ? $this->get_range_step() : 1;
            $first = intval($this->children[0]->compile());
            $end = intval(end($this->children)->compile());

            if($step == 1){
                $code = "range($first, $end)";
            }else{
                $code = "range($first, $end, $step)";
            }
            if($no_vector){
                return $code;
            }else{
                return "\\PharenVector::create_from_array($code)";
            }
        }else{
            $code = "array".parent::compile();
            if($no_vector){
                return $code;
            }else{
                return "\\PharenVector::create_from_array($code)";
            }
        }
    }

    public function is_range(){
        for($x=0; $x<count($this->children); $x++){
            $el = $this->children[$x];
            // Restore tmp since the test compile below may mess with it
            $tmp = Node::$tmp;
            if($el instanceof Node && $el->value == '..'){
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
    static $tmp_num = 0;

    static function get_tmp_name(){
        return "\$__deftmpvar".self::$tmp_num++;
    }

    public function compile_statement($prefix=""){
        $this->scope = $this->parent->get_scope();
        $varname = $this->children[1]->compile();

        $this->scope->bind($varname, $this->children[2]);
        $code = $this->format_statement($this->scope->get_binding($varname));
        $code .= $this->scope->get_lexing($varname, True, $prefix);

        return $code;
    }

    public function compile(){
        $tmp_name = self::get_tmp_name();
        $code = $this->compile_statement($tmp_name." = ");
        Node::$tmp .= ($code);
        return $tmp_name;
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
    static $tmp_num;

    public $only_return_body = False;

    public function __construct($parent){
        parent::__construct($parent);
    }

    static function get_tmp_name(){
        return "\$__lettmpvar".self::$tmp_num++;
    }

    public function compile_statement($prefix="", $return=False, $expr=False){
        if(MacroNode::$ghosting){
            return "";
        }
        $parent_scope = $this->parent->get_scope();
        $parent_postfix = $parent_scope->postfix;
        $parent_replacements = $parent_scope->replacements;
        $scope = $this->scope = new Scope($this, $parent_postfix, $parent_replacements);
        $scope->virtual = True;

        $pairs = $this->children[1]->children;
        if(isset($pairs[0]) && !($pairs[0] instanceof ListNode)){
            $pairs = array_chunk($pairs, 2);
        }
        $varnames = array();
        $code = "";
        $bindings = array();
        foreach($pairs as $pair_node){
            $varname = $pair_node[0]->compile();
            $varnames[] = $varname;

            $scope->bind($varname, $pair_node[1]);
            $bindings[$varname] = $this->format_statement($scope->get_binding($varname));
        }

        $body = "";
        $last_line = "";

        if($this->only_return_body){
            $stashed_children = $this->children;
            $last_node = array_pop($this->children);
            $last_node_body = $last_node->get_body_nodes();
            $this->children = array_merge($this->children, $last_node_body);
        }

        if($expr){
            $tmp_var = self::get_tmp_name();
            $prefix = "$tmp_var = ";
        }

        if($return === True || $prefix !== ""){
            $ret_stashed_children = $this->children;
            $last_node = array_pop($this->children);
            if($prefix){
                $last_line = $last_node->compile_statement($prefix);
            }else{
                $last_line = $last_node->compile_return();
            }
        }

        foreach(array_slice($this->children, 2) as $line){
            $body .= $line->compile_statement();
        }

        $code .= $this->scope->init_lexical_scope();
        foreach($varnames as $varname){
            $code .= $bindings[$varname];
            $code .= $scope->get_lexing($varname);
        }
        $code = $this->scope->get_lexical_bindings().$code.$body.$last_line;

        if($return === True || $prefix !== ""){
            $this->children = $ret_stashed_children;
        }
        // Restore children because only_return_body is TODO: MUTATING GAAH
        if($this->only_return_body){
            $this->children = $stashed_children;
        }
        if($expr){
            Node::$tmp .= $code;
            return $tmp_var;
        }else{
            return $code;
        }
    }

    public function compile_return($prefix=""){
        return $this->compile_statement($prefix, True);
    }

    public function compile($prefix=""){
        return $this->compile_statement($prefix, False, True);
    }

    public function get_last_expr(){
        $count = count($this->children);
        return $this->children[$count-1]->get_last_expr();
    }

    public function get_body_nodes($recur=False){
        if(!$recur){
            $body = clone $this;
            $body->only_return_body = True;
            return array($body);
        }else{
            return array();
        }
    }

    public function compile_without_last($prefix=""){
        $this->only_return_body = True;
        return $this->compile_statement();
    }

    public function get_last_func_call(){
        return $this->get_last_expr()->get_last_func_call();
    }
}


class PlambdaDefNode extends FuncDefNode {

    public function compile(){
        return $this->compile_statement();
    }

    public function compile_statement($prefix=""){
      
        $this->scope = $this->scope == Null ? new Scope($this) : $this->scope;
        $this->params = $this->children[1];

        $params = $this->get_param_names($this->params);
        $this->bind_params($params);
        list($body_nodes, $last_node) = $this->split_body_last();

        $body = $this->compile_splat_code($params);
        $params_string = $this->build_params_string($params);

        Node::$in_func++;
        if(Node::$in_func > 1){
            $this->decrease_indent();
        }
  
        $body .= count($body_nodes) > 0 ? parent::compile_body($body_nodes) : "";
        $last = $last_node->compile_return();
        $body .= $last;

        $body = $this->scope->get_lexical_bindings().$body;
        $lexings = $this->get_param_lexings($params);

        $code = $this->format_line("function ".$params_string."{", $prefix).
            $lexings.
            $body.
            $this->format_line("}").$this->format_line("");

            Node::$in_func--;
            return Node::add_tmpfunc($code);
    }


}

class Parser{
    static $INFIX_OPERATORS; 
    static $reader_macros;

    static $value;
    static $values;
    static $func_call_name;
    static $ann_or_name;
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
        self::$INFIX_OPERATORS = array("+", "-", "*", ".", "/", "%", "=", "=&", "<", ">", "<=", ">=", "===", "==", "!=", "!==", "instanceof", "|", "&");

        self::$reader_macros = array(
            "'" => "quote",
            "," => "unquote"
        );

        self::$value = array(
            "NameToken" => "VariableNode",
            "StringToken" => "StringNode",
            "NumberToken" => "LeafNode",
            "FuncValToken" => "FuncValNode",
            "KeywordToken" => "KeywordNode",
            "AnnotationToken" => "AnnotationNode",
            "SplatToken" => "SplatNode",
            "UnquoteToken" => "UnquoteNode",
            "UnstringToken" => "LeafNode"
        );

        self::$values = array(self::$value);

        self::$func_call_name = array(
            "NameToken" => "LeafNode",
            "ExplicitVarToken" => "VariableNode"
        );

        self::$ann_or_name = array(
            "AnnotationToken" => "AnnotationNode",
            "NameToken" => "LeafNode"
        );

        self::$func_call = array("Node", self::$func_call_name, array(self::$value));
        self::$infix_call = array("InfixNode", "LeafNode", array(self::$value));
        self::$empty_node = array("EmptyNode");

        self::$literal_form = array("LiteralNode", self::$values);
        self::$cond_pair = array("LiteralNode", self::$value, self::$value);
        self::$list_form = array("ListNode", self::$values);
        self::$list_access_form = array("ListAccessNode", self::$value, self::$values);

        self::$special_forms = array(
            "fn" => array("FuncDefNode", "LeafNode", "LeafNode", "LiteralNode", self::$values),
            "lambda" => array("LambdaNode", "LeafNode", "LiteralNode", self::$values),
            "fun" => array("ExpandableFuncNode", "LeafNode", self::$ann_or_name, "LiteralNode", self::$values),
            "ann" => array("AnnotatedFuncNode", "LeafNode", "LeafNode", "LiteralNode"),
            "do" => array("DoNode", "LeafNode", self::$values),
            "cond" => array("CondNode", "LeafNode", array(self::$cond_pair)),
            "if" => array("LispyIfNode", "LeafNode", self::$value, self::$value, self::$value),
            "try" => array("TryNode", "LeafNode", self::$value, self::$values),
            "catch" => array("CatchNode", "LeafNode", "LeafNode", "VariableNode", self::$value),
            "$" => array("SuperGlobalNode", "LeafNode", "LeafNode", self::$value),
            "def" => array("DefNode", "LeafNode", "VariableNode", self::$value),
            "local" => array("LocalNode", "LeafNode", "VariableNode", self::$value),
            "let" => array("BindingNode", "LeafNode", "LiteralNode", self::$values),
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
            "class-extends" => array("ClassExtendsNode", "LeafNode", "LeafNode", "LeafNode", "LeafNode", self::$values),
            "access" => array("AccessModifierNode", "LeafNode", "LeafNode", self::$values),
            "interface" => array("InterfaceNode", "LeafNode", "LeafNode", self::$values),
            "defrecord" => array("DefRecordNode", "LeafNode", "LeafNode", array("VariableNode")),
            "signature*" => array("SignatureNode", "LeafNode", "LeafNode", "LeafNode", "LiteralNode"),
            "keyword-call" => array("KeywordCallNode", "LeafNode", "LeafNode",  array("LeafNode")),
            "ns" => array("NamespaceNode", "LeafNode", array("LeafNode")),
            "use" => array("UseNode", "LeafNode", array("LeafNode")),
            "plambda" => array("PlambdaDefNode",  "LeafNode", "LiteralNode", self::$values),            
        );
        
        $this->tokens = $tokens;

    }
    
    public function parse($root=Null, $scope=Null){
        $curnode = $root ? $root : new RootNode($scope);
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
                    if($lookahead instanceof OpenParenToken or $lookahead instanceof OpenBraceToken or $lookahead instanceof OpenBracketToken){
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

        if(($tok instanceof NameToken && ctype_alnum(str_replace('-', '', str_replace('_', '', $tok->value)))) and (strstr($tok->value, ".") || strToUpper($tok->value) == $tok->value)){
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
        $cur = end($state);
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
    public static $shortcuts = array(
        'r' => 'repl',
        'e' => 'executable',
        'l' => 'no-import-lang'
    );

    public static function is_true($flag) {
        return isset(self::$flags[$flag]) && self::$flags[$flag];
    }
}

function set_flag($flag, $setting=True){
    if(isset(Flags::$shortcuts[$flag])){
        $flag = Flags::$shortcuts[$flag];
    }
    Flags::$flags[$flag] = $setting;
}

function unset_flag($flag){
    Flags::$flags[$flag] = False;
}

function compile_file($fname, $output_dir=Null){
    RootNode::$ns = "";
    RootNode::$raw_ns = "";
    RootNode::$ns_string = "";
    $file = basename($fname, EXTENSION);
    $output_dir = $output_dir === Null ? dirname($fname) : $output_dir;
    $ns = str_replace('-', '_', $file);

    $dir = str_replace("\\", "_", $output_dir);
    $dir = str_replace("/", "_", $dir);
    $dir = str_replace(":", "_", $dir);
    $dir = str_replace("-", "_", $dir);
    $dir = str_replace(".", "_", $dir);
    $first_underscore = strpos($dir, "_");
    $dir = substr($dir, $first_underscore);
    Node::$ns = $dir.$ns;

    $code = file_get_contents($fname);
    $phpcode = compile($code);
 
    $output = $output_dir.DIRECTORY_SEPARATOR.$file.".php";
    file_put_contents($output, $phpcode);
    return $phpcode;
}
 
function compile($code, $root=Null, $ns=Null, $scope=Null){
    if($ns !== Null){
        Node::$ns = $ns;
    }
    $lexer = new Lexer($code);
    $tokens = $lexer->lex();

    if(!$lexer->finished()){
        return False;
    }
    $parser = new Parser($tokens);
    $node_tree = $parser->parse($root, $scope);
    $phpcode = $node_tree->compile();
    return $phpcode;
}

function compile_lang(){
    if(Flags::$lang_compiled)
        return;
    $old_lang_setting = isset(Flags::$flags['no-import-lang']) ? Flags::$flags['no-import-lang'] : False;
    $old_lexi_setting = isset(Flags::$flags['import-lexi-relative']) ? Flags::$flags['import-lexi-relative'] : False;
    $old_executable_setting = isset(Flags::$flags['executable']) ? Flags::$flags['executable'] : False;
    set_flag("no-import-lang");
    set_flag("import-lexi-relative");
    set_flag("executable", False);
    if(!$old_lang_setting){
        $lang_code = compile_file(COMPILER_SYSTEM . DIRECTORY_SEPARATOR . "lang.phn");
    }
    set_flag("import-lexi-relative", $old_lexi_setting);
    set_flag("no-import-lang", $old_lang_setting);
    set_flag("executable", $old_executable_setting);
    Flags::$lang_compiled = True;
}
