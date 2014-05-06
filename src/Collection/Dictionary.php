<?php

namespace Collection;

use \Countable;
use \IteratorAggregate;
use \ArrayAccess;
use \OutOfBoundsException;
use Collection\Iterator\ArrayIterator;

class Dictionary implements Countable, IteratorAggregate, ArrayAccess{
    /**
     * Data Holder
     * @var array
     */
    protected $_array = array();

    /**
     * 
     * @param Traversable $array
     */
    public function __construct($array=NULL){
        $this->_array = $array;
    }

    /**
     * (non-PHPdoc)
     * @see Countable::count()
     */
    public function count(){
        return count($this->_array);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset){
        return array_key_exists($offset, $this->_array);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset){
        if($this->offsetExists($offset)){
            unset($this->_array[$offset]);
            return;
        }
        throw new OutOfBoundsException("Offset $offset does not exist");
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset){
        if($this->offsetExists($offset)){
            return $this->_array[$offset];
        }
        throw new OutOfBoundsException("Offset $offset does not exist");
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value){
        $this->_array[$offset] = $value;
    }

    /**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator(){
        return new ArrayIterator($this->_array);
    }

    /**
     * Clears the Dictionary
     * 
     * @return Dictionary
     */
    public function clear(){
        $this->_array = array();
        return $this;
    }
    
    /**
     * Copies this Dictionary or it extended classes
     * 
     * @return Dictionary
     */
    public function copy(){
        $reflection = new ReflectionClass($this);
        return $reflection->newInstance($this->_array);
    }

    /**
     * @see self::copy()
     * @return Dictionary
     */
    public function __clone(){
        return $this->copy();
    }

    /**
     * Safely get an offset. Will check isset() before.
     * If the offset does not exists it will return $default
     * 
     * @param string $key
     * @param multiple $default
     * @return multitype:|NULL
     */
    public function get($key, $default=NULL){
        try{
            return $this->offsetGet($key);
        }
        catch(OutOfBoundsException $e){
        }
        return $default;
    }

    /**
     * Sets a value with fluid syntax
     * 
     * @param mixed $key
     * @param multitype $value
     * @return Dictionary
     */
    public function set($key, $value){
        $this->offsetSet($key, $value);
        return $this;
    }

    /**
     * Sets a value by reference with fluid syntax
     * 
     * @param mixed $key
     * @param multitype $value
     * @return Dictionary
     */
    public function setRef($key, &$value){
        $this->_array[$key] = &$value;
//         $this->offsetSet($key, $value);
        return $this;
    }

    /**
     * Checks if a key exists, no matter if is_null()
     * 
     * @param string $key
     * @return bool
     * @see Dictionary::offsetExists($offset)
     */
    public function hasKey($key){
        return $this->offsetExists($key);
    }

    /**
     * Checks if a key can be assumed to be true.
     * This is a shortCut for:
     * 
     * if( isset($array['foo']) && $array['foo'] )
     * 
     * This would be written like this:
     * 
     * if($array->has('foo'))
     * 
     * @param multitype:String|Int|Float $key
     * @return boolean
     */
    public function has($key){
        if($this->offsetExists($key) && (bool)$this->offsetGet($key)){
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Returns a OrderedList of key/value pairs (array($key, $value)
     * 
     * @return MDF_Array_List
     */
    public function items(){

        $list = new OrderedList();

        foreach($this->_array as $key=>$value){
            $list->append(array($key,$value));
        }
        return $list;
    }

    /**
     * Returns an iterator over $key=>$value
     * 
     * @return ArrayIterator
     */
    public function iterItems(){
        return $this->items()->getIterator();
    }

    /**
     * Returns an iterator over $this->keys()
     * 
     * @return ArrayIterator
     */
    public function iterKeys(){
        return new ArrayIterator(array_keys($this->_array));
    }

    /**
     * Returns an iterator over $this->values()
     * 
     * @return ArrayIterator
     */
    public function iterValues(){
        return new ArrayIterator(array_values($this->_array));
    }

    /**
     * Returns a OrderedList of keys (scalars) of the hash
     * 
     * @return OrderedList
     */
    public function keys(){
        return new OrderedList(array_keys($this->_array));
    }

    /**
     * Remove key $key of hash if exists. If not exists, return
     * default. If no default is set a OutOfBoundsException
     * is thrown.
     * 
     * @param mixed $key
     * @param mixed $default
     */
    public function pop($key, $default=NULL){
        if(is_null($default)){
            $value = $this->offsetGet($key);
            $this->offsetUnset($key);
            return $value;
        }
        if($this->offsetExists($offset)){
            $value = $this->offsetGet($offset);
            return $value;
        }
        return $default;
    }

    /**
     * Set the value if it does not exist in the Dictionary.
     * If it exists, return the value.
     * If it does not exist, set it with the value of
     * $default and return $default
     * 
     * @param mixed $key
     * @param mixed $default
     */
    public function setDefault($key, $default=NULL){
        if($this->offsetExists($key)){
            return $this->offsetGet($key);
        }
        $this->offsetSet($key, $default);
        return $default;
    }

    /**
     * Updates the values of this hash with the values of other.
     * 
     * @param Traversable: $other
     * @return Dictionary
     */
    public function update($other){
        if(is_array($other)){
            $this->_array = $other;
            return $this;
        }
        $this->_array = array();
        foreach($other as $key=>$value){
            $this->_array[$key] = $value;
        }
        return $this;
    }

    /**
     * Return a OrderedList of all values.
     * 
     * @return OrderedList
     */
    public function values(){
        return new OrderedList(array_values($this->_array));
    }

    /**
     * Constructs a Hash with the passed keys
     * 
     * @param Traversable $keys
     * @return Dictionary
     */
    public static function fromKeys($keys){
        $hash = new Dictionary();
        foreach($keys as $key){
            $hash[$key] = NULL;
        }
        return $hash;
    }

}