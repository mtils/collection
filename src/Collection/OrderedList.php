<?php

namespace Collection;

use ArrayAccess;
use Collection\Iterator\ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use OutOfRangeException;
use ReflectionClass;
use ReturnTypeWillChange;

use function array_values;


class OrderedList implements Countable, IteratorAggregate, ArrayAccess
{

    protected array $_array = [];

    public function __construct($src=NULL){
        if($src){
            $this->setSrc($src);
        }
    }

    public function append($value): static
    {
        $this->_array[] = $value;
        return $this;
    }

    public function push($value) : static
    {
        return $this->append($value);
    }

    public function extend(iterable $values) : static
    {
        foreach($values as $value){
            $this->append($value);
        }
        return $this;
    }

    public function insert(int $index, mixed $value) : static
    {

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
                $pastInsertPosition = true;
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

    public function remove(mixed $value) : static
    {
        return $this->pop($this->indexOf($value));
    }

    public function pop(?int $index=null) : static
    {

        if(is_null($index)){
            array_pop($this->_array);
            return $this;
        }
        if (isset($this->_array[$index])) {
            unset($this->_array[$index]);
            $this->_array = array_values($this->_array);
        }
        return $this;
    }

    public function indexOf(mixed $value): int
    {
        $count = $this->count();
        for($i=0; $i<$count; $i++){
            if($value === $this->_array[$i]){
                return $i;
            }
        }
        throw new LogicException("Value $value not found");
    }

    public function contains($value) : bool
    {
        try{
            return is_int($this->indexOf($value));
        }
        catch(LogicException $e){
            return false;
        }
    }

    public function count() : int
    {
        if(func_num_args() > 0){
            return $this->countValue(func_get_arg(0));
        }
        return count($this->_array);
    }

    public function countValue(mixed $value) : int
    {
        $count = 0;
        foreach($this->_array as $arrayVal){
            if($arrayVal === $value){
                $count ++;
            }
        }
        return $count;
    }

    public function sort() : static
    {
        sort($this->_array);
        return $this;
    }

    public function reverse() : static
    {
        $this->_array = array_reverse($this->_array);
        return $this;
    }

    /** @noinspection PhpMissingReturnTypeInspection */
    #[ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->_array);
    }

    #[ReturnTypeWillChange]
    public function offsetExists($offset) : bool
    {
        return isset($this->_array[$offset]);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset) : mixed
    {
        return $this->_array[$offset];
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value) : void
    {
        $this->_array[$offset] = $value;
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset) : void
    {
        $this->pop($offset);
    }

    public function src() : array
    {
        return $this->_array;
    }

    public function setSrc($src) : static
    {
        if(is_array($src)){
            $this->_array = array_values($src);
        }
        return $this;
    }

    public function first() : mixed
    {
        if(isset($this->_array[0])){
            return $this->_array[0];
        }
        return null;
    }

    public function last() : mixed
    {
        $lastIndex = (count($this->_array)-1);
        if(isset($this->_array[$lastIndex])){
            return $this->_array[$lastIndex];
        }
        return null;
    }

    /**
     * Copies the list or its extended class
     *
     * @return OrderedList
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function copy() : static
    {
        $reflection = new ReflectionClass($this);
        /** @noinspection PhpUnhandledExceptionInspection */
        return $reflection->newInstance($this->_array);
    }

    /**
     * @see OrderedList::copy()
     * @return OrderedList
     */
    public function __clone() : void
    {
        // This method made no sense. It is just here to minimize changed
        $this->copy();
    }
}