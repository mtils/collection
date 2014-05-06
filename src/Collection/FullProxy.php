<?php namespace Collection;

use \ArrayAccess;

class FullProxy implements ArrayAccess{

    protected $src;

    public function __construct($src=NULL){
        $this->setSrc($src);
    }

    public function getSrc(){
        return $this->src;
    }

    public function setSrc($src){
        $this->src = $src;
        return $this;
    }

    public function __get($name){
        if($name == 'src'){
           return $this->src;
        }
        return $this->src->{$name};
    }

    public function __set($name, $value){
        $this->src->{$name} = $value;
    }

    public function __isset($name){
        return isset($this->src->{$name});
    }

    public function __unset($name){
        unset($this->src->{$name});
    }

    public function __call($method, $args){
        return call_user_func_array(
            array($this->src, $method),
            $args
        );
    }

    public static function __callStatic($method, $args){
        return call_user_func_array(
            array(get_class($this->src), $method),
            $args
        );
    }

    public function __invoke(){
        return call_user_func_array(
            array($this->src, '__invoke'),
            func_get_args()
        );
    }

    public function __toString(){
        return (string)$this->src;
    }

    public function offsetExists($offset){
        return isset($this->src[$offset]);
    }

    public function offsetGet($offset){
        return $this->src[$offset];
    }

    public function offsetSet($offset, $value){
        $this->src[$offset] = $value;
    }

    public function offsetUnset($offset){
        unset($this->src[$offset]);
    }

}