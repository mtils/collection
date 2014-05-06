<?php namespace Collection\Map;

use Collection\FullProxy;

class ValueProxy extends FullProxy{

    protected $position = NULL;
    protected $key = NULL;
    protected $value = NULL;

    public function getKey(){
        return $this->key;
    }

    public function _setKey($key){
        $this->key = $key;
        return $this;
    }

    public function getValue(){
        return $this->value;
    }

    public function _setValue($value){
        $this->value = $value;
    }

    public function getPosition(){
        return $this->position;
    }

    public function _setPosition($pos){
        $this->position = $pos;
        return $this;
    }

    public function __toString(){
        return (string)$this->value;
    }

    public function __get($name){
        if(in_array($name, array('key','value','position'))){
            return $this->{$name};
        }
        return parent::__get($name);
    }

}