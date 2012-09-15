<?php
interface IPharenSeq{
    public function first();
    public function rest();
    public function cons($item);
}

interface IPharenLazy{
    public function force();
    public function realized();
}

interface IPharenComparable{
    public function eq($other);
}

class PharenList implements IPharenSeq, IPharenComparable, Countable, ArrayAccess, Iterator{
    public $first;
    public $rest;
    public $length = Null;
    public $iterator_key = 0;
    public $iterator_el;
    public $arr;
    public $delimiter_tokens = array("OpenParenToken", "CloseParenToken");

    public static function create_from_array($xs, $cls="PharenCachedList"){
        if(empty($xs)){
            return new PharenEmptyList;
        }
        $cache = SplFixedArray::fromArray($xs);
        $reversed = array_reverse($xs, True);
        $last_el = array_shift($reversed);
        $len = count($xs);
        $el1 = new $cls($last_el, new PharenEmptyList, 1, $cache, $len-1);
        foreach($reversed as $i=>$x){
            $index = $len-($i+2);
            $el2 = $el1->cached_cons($x, $cache, $index, $cls);
            $el1 = $el2;
        }
        return $el1;
    }

    public static function seqify(&$xs){
        if(is_array($xs)){
            if(count($xs) === 0){
                return new PharenEmptyList;
            }else{
                return self::create_from_array($xs);
            }
        }else if(is_string($xs)){
            $splitted = str_split($xs);
            return self::create_from_array($splitted);
        }
    }

    public function __construct($first, $rest=null, $length=1){
        $this->first = $first;
        $this->rest = $rest;
        $this->length = $length;
        $this->iterator_el = $this;
    }

    public function __toString(){
        $vals = array();
        foreach($this as $val){
            if(is_object($val)){
                $vals []= $val->__toString();
            }else if(is_array($val)){
                $vals []= "[".implode(", ", $val)."]";
            }else{
                if(is_string($val)){
                    $val = '"'.$val.'"';
                }
                $vals []= $val;
            }
        }
        return "[" . implode(", ", $vals) . "]";
    }

    public function seq(){
        return $this;
    }

    public function eq($other){
        if($other instanceof IPharenSeq || is_array($other)){
            foreach($this as $index=>$thisval){
                if(!isset($other[$index]) || !eq($thisval, $other[$index])){
                    return False;
                }
            }
            if(isset($index) && isset($other[$index+1])){
                return False;
            }
            return True;
        }else{
            return $this === $other;
        }
    }

    public function arr(){
        if($this->arr)
            return $this->arr;

        $arr = array();
        foreach($this as $val){
            $arr[] = $val;
        }
        $this->arr = $arr;
        return $arr;
    }

    public function count(){
        if($this->length){
            return $this->length;
        }else{
            $this->length = 1 + $this->rest()->count();
            return $this->length;
        }
    }

    public function offsetExists($offset){
        $list = $this;
        for($x=$offset; $x > 0 && $list !== Null; $x--){
            $list = $list->rest;
        }
        return $list !== Null;
    }

    public function offsetGet($offset){
        $list = $this;
        for($x=$offset; $x > 0; $x--){
            if($list instanceof PharenEmptyList){
                throw new OutOfRangeException;
            }
            $list = $list->rest;
        }
        return $list->first;
    }

    public function offsetSet($offset, $value){
    }

    public function offsetUnset($offset){
    }

    public function current(){
        return $this->iterator_el->first;
    }

    public function key(){
        return $this->iterator_key;
    }

    public function next(){
        $this->iterator_key++;
        $this->iterator_el = $this->iterator_el->rest;
    }

    public function rewind(){
        $this->iterator_key = 0;
        $this->iterator_el = $this;
    }

    public function valid(){
        return $this->iterator_el->length !== 0;
    }

    public function first(){
        return $this->first;
    }

    public function rest(){
        return $this->rest;
    }

    public function cons($value){
        return new PharenList($value, $this, $this->length+1);
    }

    public function cached_cons($value, $cached_array, $index, $cls="PharenCachedList"){
        return new $cls($value, $this, $this->length+1, $cached_array, $index);
    }
}

class PharenCachedList extends PharenList{
    public $cached_array;
    public $index;

    public function __construct($value, $rest, $length, $cached_array, $index){
        parent::__construct($value, $rest, $length);
        $this->cached_array = $cached_array;
        $this->index = $index;
        $this->length = count($this->cached_array) - $index;
    }

    public function count(){
        return $this->length;
    }

    public function arr(){
        if($this->arr)
            return $this->arr;
        $this->arr = array_slice($this->cached_array->toArray(), $this->index);
        return $this->arr;
    }

    public function offsetExists($offset){
        return isset($this->cached_array[$this->index + $offset]);
    }

    public function offsetGet($offset){
        return $this->cached_array[$this->index + $offset];
    }

    public function offsetSet($offset, $value){
        $this->cached_array[$this->index+$offset] = $value;
    }

    public function flatten($delimeters=Null){
        if(is_null($delimeters)){
            $tokens = array();
        }else{
            $tokens = array($delimeters[0]);
        }
        foreach($this->cached_array as $el){
            if($el instanceof PharenCachedList){
                $tokens = array_merge($tokens, $el->flatten($el->delimiter_tokens));
            }else{
                $tokens[] = $el;
            }
        }
        if(!is_null($delimeters)){
            $tokens[] = $delimeters[1];
        }
        return $tokens;
    }
}

class PharenEmptyList extends PharenList{

    public function __construct(){
        $this->first = Null;
        $this->length = 0;
        $this->rest = $this;
    }
}

class PharenLazyList implements IPharenSeq, IPharenLazy, ArrayAccess, Iterator{
    public $first = Null;
    public $rest = Null;
    public $length = Null;
    public $lambda;
    public $lambda_result = Null;

    public function __construct($lambda){
        $this->lambda = $lambda;
    }

    public function __toString(){
        return "<".__CLASS__.">";
    }

    public function seq(){
        $this->force();
        return $this->lambda_result;
    }

    public function current(){
        $this->iterator_el->force();
        return $this->iterator_el->first;
    }

    public function key(){
        return $this->iterator_key;
    }

    public function next(){
        $this->iterator_key++;
        $this->iterator_el->force();
        $this->iterator_el = $this->iterator_el->rest;
    }

    public function rewind(){
        $this->iterator_key = 0;
        $this->iterator_el = $this;
    }

    public function valid(){
        $this->iterator_el->force();
        return !($this->iterator_el->lambda_result instanceof PharenEmptyList);
    }

    public function first(){
        $this->iterator_el->force();
        return $this->first;
    }

    public function rest(){
        $this->iterator_el->force();
        return $this->rest;
    }

    public function offsetExists($offset){
        $list = $this->seq();
        for($x=$offset; $x > 0 && $list !== Null; $x--){
            $list = $list->rest->seq();
        }
        return !($list instanceof PharenEmptyList);
    }

    public function offsetGet($offset){
        $list = $this->seq();
        for($x=$offset; $x > 0; $x--){
            if($list instanceof PharenEmptyList){
                throw new OutOfRangeException;
            }
            $list = $list->rest->seq();
        }
        return $list->first;
    }

    public function offsetSet($offset, $value){
    }

    public function offsetUnset($offset){
    }

    public function force(){
        if(!$this->lambda_result){
            $lambda = $this->lambda;
            $result = $lambda();

            if(empty($result)){
                $result = new PharenEmptyList;
            }
            $this->lambda_result = $result;
            $this->first = $result->first();
            $this->rest = $result->rest();
        }
    }

    public function realized(){
        return $this->lambda_result !== Null;
    }

    public function count(){
        if($this->length){
            return $this->length;
        }else{
            $this->length = 1 + $this->rest()->count();
            return $this->length;
        }
    }

    public function cons($value){
        return new PharenList($value, $this, Null);
    }
}

class PharenDelay implements IPharenLazy{
    public $lambda;
    public $value = Null;
    public $realized = False;

    public function __toString(){
        return "<".__CLASS__.">";
    }

    public function __construct($lambda){
        $this->lambda = $lambda;
    }

    public function force(){
        if(!$this->realized){
            $fn = $this->lambda;
            if(is_array($fn)){
                $this->value = $fn[0]($fn[1]);
            }else{
                $this->value = $fn();
            }
            $this->realized = True;
            return $this->value;
        }else{
            return $this->value;
        }
    }

    public function realized(){
        return $this->realized;
    }
}

class PharenHashMap implements Countable, ArrayAccess, Iterator{
    public $hashmap;
    public $count;
    public $delimiter_tokens = array("OpenBraceToken", "CloseBraceToken");

    public function __construct($hashmap, $count=Null){
        $this->hashmap = $hashmap;
        if($count){
            $this->count = $count;
        }else{
            $this->count = count($hashmap);
        }
    }

    public function __toString(){
        $pairs = array();
        foreach($this as $k=>$v){
            if(is_string($k)){
                $k = '"'.$k.'"';
            }
            if(is_string ($v)){
                $v = '"'.$v.'"';
            }
            $pairs []= "$k $v";
        }
        return "{".implode(", ", $pairs)."}";
    }

    public function __invoke($key, $default=Null){
        if(isset($this->hashmap[$key])){
            return $this->hashmap[$key];
        }else{
            return $default;
        }
    }

    public function assoc($key, $val){
        $new_hashmap = $this->hashmap;
        $new_hashmap[$key] = $val;
        return new PharenHashMap($new_hashmap, $this->count+1);
    }

    public function offsetGet($key){
        return $this->hashmap[$key];
    }

    public function offsetSet($key, $val){
        $this->hashmap[$key] = $val;
    }

    public function offsetUnset($key){
    }

    public function offsetExists($key){
        return isset($this->hashmap[$key]);
    }

    public function count(){
        return $this->count;
    }

    public function current(){
        return current($this->hashmap);
    }

    public function key(){
        return key($this->hashmap);
    }

    public function next(){
        return next($this->hashmap);
    }

    public function rewind(){
        return reset($this->hashmap);
    }

    public function valid(){
        return isset($this->hashmap[key($this->hashmap)]);
    }
}

class PharenVector extends PharenCachedList{
    public $delimiter_tokens = array("OpenBracketToken", "CloseBracketToken");

    public static function create_from_array($array, $cls="PharenVector"){
        return PharenList::create_from_array($array, __CLASS__);
    }

    public function __invoke($n){
        return $this->offsetGet($n);
    }
}

class PharenLambda{
    public $closure_id;
    public $func;

    public function __construct($func, $closure_id){
        $this->func = $func;
        $this->closure_id = $closure_id;
    }

    public function __invoke(){
        $args = func_get_args();
        array_push($args, $this->closure_id);
        return call_user_func_array($this->func, $args);
    }

    public function __toString(){
        return "<Lambda: {$this->func}:{$this->closure_id}>";
    }
}
