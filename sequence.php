<?php
interface IPharenSeq extends Countable, ArrayAccess{
    public function first();
    public function rest();
    public function cons($item);
}

class PharenList implements IPharenSeq, Iterator{
    public $first;
    public $rest;
    public $length;
    public $iterator_key = 0;
    public $iterator_el;

    public static function create_from_array(&$xs){
        $cache = SplFixedArray::fromArray($xs);
        $reversed = array_reverse($xs, True);
        $el1 = new PharenCachedList(Null, Null, 0, $cache, count($xs));
        foreach($reversed as $index=>$x){
            $el2 = $el1->cached_cons($x, $cache, $index);
            $el1 = $el2;
        }
        return $el1;
    }

    public function __construct($first, $rest=null, $length=1){
        $this->first = $first;
        $this->rest = $rest;
        $this->length = $first !== Null ? $length : 0;
        $this->iterator_el = $this;
    }

    public function count(){
        return $this->length;
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
        for($x=$offset; $x > 0 && $list !== Null; $x--){
            $list = $list->rest;
        }
        return $list !== Null ? $list->first : Null;
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
        return $this->iterator_el !== Null;
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

    public function cached_cons($value, $cached_array, $index){
        return new PharenCachedList($value, $this, $this->length+1, $cached_array, $index);
    }
}

class PharenCachedList extends PharenList{
    public $cached_array;
    public $index;

    public function __construct($value, $rest, $length, $cached_array, $index){
        $this->cached_array = $cached_array;
        $this->index = $index;
        parent::__construct($value, $rest, $length);
    }

    public function offsetExists($offset){
        return isset($this->cached_array[$this->index + $offset]);
    }

    public function offsetGet($offset){
        return $this->cached_array[$this->index + $offset];
    }
}
