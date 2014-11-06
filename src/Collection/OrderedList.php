<?php

namespace Collection;

use \ReflectionClass;
use \Countable;
use \IteratorAggregate;
use \ArrayAccess;
use \LogicException;
use \OutOfRangeException;
use Collection\Iterator\ArrayIterator;


class OrderedList implements Countable, IteratorAggregate, ArrayAccess{

    protected $_array = array();

    public function __construct($src=NULL){
        if($src){
            $this->setSrc($src);
        }
    }

    public function append($value){
        $this->_array[] = $value;
        return $this;
    }

    public function push($value){
        return $this->append($value);
    }

    public function extend($values){
        foreach($values as $value){
            $this->append($value);
        }
        return $this;
    }

    public function insert($index, $value){

        $newArray = array();
        $pastInsertPosition=FALSE;
        $count = $this->count();

        if($index == $count){
            $this->append($value);
            return $this;
        }

        if($index > $count){
            throw new OutOfRangeException("Index $index not found");
        }

        for($i=0; $i<$count; $i++){
            if($i == $index){
                $newArray[$index] = $value;
                $newArray[$i+1] = $this->_array[$i];
                $pastInsertPosition = TRUE;
            }
            else{
                if(!$pastInsertPosition){
                    $newArray[$i] = $this->_array[$i];
                }
                else{
                    $newArray[$i+1] = $this->_array[$i];
                }
            }
        }
        if($pastInsertPosition){
            $this->_array = $newArray;
        }
        return $this;
    }

    public function remove($value){
        return $this->pop($this->indexOf($value));
    }

    public function pop($index=NULL){

        if(is_null($index)){
            array_pop($this->_array);
            return $this;
        }

        $count = $this->count();
        $found = FALSE;
        for($i=0; $i<$count; $i++){
            if($i < $index){
                $newArray[$i] = $this->_array[$i];
            }
            if($i == $index){
                $found = TRUE;
            }
            if($i > $index){
                $newArray[$i-1] = $this->_array[$i];
            }
        }
        if($found){
            $this->_array = $newArray;
            return $this;
        }
    }

    public function indexOf($value){
        $count = $this->count();
        $found = FALSE;
        for($i=0; $i<$count; $i++){
            if($value === $this->_array[$i]){
                return $i;
            }
        }
        throw new LogicException("Value $value not found");
    }

    public function contains($value){
        try{
            return is_int($this->indexOf($value));
        }
        catch(LogicException $e){
            return FALSE;
        }
    }

    public function count(){
        if(func_num_args() > 0){
            return $this->countValue(func_get_arg(0));
        }
        return count($this->_array);
    }

    public function countValue($value){
        $count = 0;
        foreach($this->_array as $arrayVal){
            if($arrayVal === $value){
                $count ++;
            }
        }
        return $count;
    }

    public function sort(){
        sort($this->_array);
        return $this;
    }

    public function reverse(){
        $this->_array = array_reverse($this->_array);
        return $this;
    }

    public function getIterator(){
        return new ArrayIterator($this->_array);
    }
    
    public function offsetExists($offset){
        return isset($this->_array[$offset]);
    }
    
    public function offsetGet($offset){
        return $this->_array[$offset];
    }
    
    public function offsetSet($offset, $value){
        $this->_array[$offset] = $value;
    }

    public function offsetUnset($offset){
        $this->pop($offset);
    }

    public function src(){
        return $this->_array;
    }

    public function setSrc($src){
        if(is_array($src)){
            $this->_array = array_values($src);
            return $this;
        }
    }

    public function first(){
        if(isset($this->_array[0])){
            return $this->_array[0];
        }
    }

    public function last(){
        $lastIndex = (count($this->_array)-1);
        if(isset($this->_array[$lastIndex])){
            return $this->_array[$lastIndex];
        }
    }

    /**
     * Copies the list or its extended class
     * 
     * @return OrderedList
     */
    public function copy(){
        $reflection = new ReflectionClass($this);
        return $reflection->newInstance($this->_array);
    }

    /**
     * @see OrderedList::copy()
     * @return OrderedList
     */
    public function __clone(){
        return $this->copy();
    }
}