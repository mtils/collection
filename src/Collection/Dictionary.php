<?php

namespace Collection;

use ArrayAccess;
use Collection\Iterator\ArrayIterator;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;
use ReflectionClass;
use ReturnTypeWillChange;

class Dictionary implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * Data Holder
     * @var array|ArrayAccess
     */
    protected array|ArrayAccess $_array = [];

    /**
     *
     * @param array|ArrayAccess|null $array
     */
    public function __construct(array|ArrayAccess|null $array=null)
    {
        $this->_array = $array === null ? [] : $array;
        if (!$array) {
            $this->_array = [];
        }
    }

    /**
     * @see Countable::count()
     */
    public function count() : int
    {
        if (is_array($this->_array) || $this->_array instanceof Countable) {
            return count($this->_array);
        }
        return 0;
    }

    /**
     * @see ArrayAccess::offsetExists()
     */
    #[ReturnTypeWillChange]
    public function offsetExists(mixed $offset) : bool
    {
        return array_key_exists($offset, $this->_array);
    }

    /**
     * @see ArrayAccess::offsetUnset()
     */
    #[ReturnTypeWillChange]
    public function offsetUnset(mixed $offset) : void
    {
        if($this->offsetExists($offset)){
            unset($this->_array[$offset]);
            return;
        }
        throw new OutOfBoundsException("Offset $offset does not exist");
    }

    /**
     * @see ArrayAccess::offsetGet()
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset) : mixed
    {
        if($this->offsetExists($offset)){
            return $this->_array[$offset];
        }
        throw new OutOfBoundsException("Offset $offset does not exist");
    }

    /**
     * @see ArrayAccess::offsetSet()
     */
    #[ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value) : void
    {
        $this->_array[$offset] = $value;
    }

    /**
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator() : \Iterator
    {
        return new ArrayIterator($this->_array);
    }

    /**
     * Clears the Dictionary
     *
     * @return Dictionary
     */
    public function clear() : static
    {
        $this->_array = [];
        return $this;
    }

    /**
     * Copies this Dictionary or it extended classes
     *
     * @return Dictionary
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function copy() : static
    {
        $reflection = new ReflectionClass($this);
        /** @noinspection PhpUnhandledExceptionInspection */
        return $reflection->newInstance($this->_array);
    }

    /**
     * @see self::copy()
     */
    public function __clone() : void
    {
        $this->copy();
    }

    /**
     * @brief Returns a new Dictionary object without the key "key"
     *
     * @param mixed $key
     * @see self::copy()
     * @return Dictionary
     */
    public function without(mixed $key) : static
    {
        $copy = $this->copy();
        unset($copy[$key]);
        return $copy;
    }

    /**
     * Safely get an offset. Will check isset() before.
     * If the offset does not exist it will return $default
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default=null) : mixed
    {
        try{
            return $this->offsetGet($key);
        } catch(OutOfBoundsException){
        }
        return $default;
    }

    /**
     * Sets a value with fluid syntax
     *
     * @param mixed $key
     * @param mixed $value
     * @return Dictionary
     */
    public function set(string $key, mixed $value) : static
    {
        $this->offsetSet($key, $value);
        return $this;
    }

    /**
     * Sets a value by reference with fluid syntax
     *
     * @param mixed $key
     * @param mixed $value
     * @return Dictionary
     */
    public function setRef(string $key, mixed &$value) : static
    {
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
    public function hasKey(string $key) : bool
    {
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
     * @param string|int|float $key
     * @return boolean
     */
    public function has(mixed $key) : bool
    {
        if($this->offsetExists($key) && $this->offsetGet($key)) {
            return true;
        }
        return false;
    }

    /**
     * Returns a OrderedList of key/value pairs (array($key, $value)
     *
     * @return OrderedList
     */
    public function items() : OrderedList
    {

        $list = new OrderedList();

        foreach($this->_array as $key=>$value){
            $list->append(array($key,$value));
        }
        return $list;
    }

    /**
     * Returns a OrderedList of keys (scalars) of the hash
     *
     * @return OrderedList
     */
    public function keys() : OrderedList
    {
        return new OrderedList(array_keys($this->_array));
    }

    /**
     * Remove key $key of hash if exists. If not exists, return
     * default. If no default is set a OutOfBoundsException
     * is thrown.
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function pop(mixed $key, mixed $default=NULL) : mixed
    {
        if(is_null($default)){
            $value = $this->offsetGet($key);
            $this->offsetUnset($key);
            return $value;
        }
        if($this->offsetExists($key)){
            return $this->offsetGet($key);
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
     * @return mixed
     */
    public function returnOrSet(mixed $key, mixed $default=NULL): mixed
    {
        if($this->offsetExists($key)){
            return $this->offsetGet($key);
        }
        $this->offsetSet($key, $default);
        return $default;
    }

    /**
     * Updates the values of this hash with the values of others.
     *
     * @param iterable $other
     * @return Dictionary
     */
    public function update(iterable $other) : static
    {
        if(is_array($other)){
            $this->_array = $other;
            return $this;
        }
        $this->_array = [];
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
    public function values() : OrderedList
    {
        return new OrderedList(array_values($this->_array));
    }

    /**
     * Constructs a Hash with the passed keys
     *
     * @param iterable $keys
     * @return Dictionary
     */
    public static function fromKeys(iterable $keys) : Dictionary
    {
        $hash = new Dictionary();
        foreach($keys as $key){
            $hash[$key] = NULL;
        }
        return $hash;
    }

}
