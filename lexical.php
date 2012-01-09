<?php
class Lexical{
    static $scopes = array();

    public static function init_closure($ns, $id){
        if(isset(self::$scopes[$ns][$id])){
            self::$scopes[$ns][$id][] = array();
        }else{
            self::$scopes[$ns][$id] = array(array());
        }
        return $id;
    }

    public static function get_closure_id($ns, $id){
        if($id === Null){
            return Null;
        }else{
            return count(self::$scopes[$ns][$id])-1;
        }
    }

    public static function bind_lexing($ns, $id, $var, &$val){
        $closure_id = self::get_closure_id($ns, $id);
        self::$scopes[$ns][$id][$closure_id][$var] =& $val;
    }

    public static function get_lexical_binding($ns, $id, $var, $closure_id){
        return self::$scopes[$ns][$id][$closure_id][$var];
    }
}
