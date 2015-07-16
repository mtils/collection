<?php namespace Collection;

use OutOfBoundsException;
use Collection\Iterator\ArrayIterator;

trait CallableSetTrait
{

    protected $_callables = [];

    public function add(callable $callable)
    {
        if (!$this->contains($callable)) {
            $this->_callables[] = $callable;
        }
        return $this;
    }

    public function extend($callables)
    {
        $callables = func_num_args() > 1 ? func_get_args() : (array) $callables;
        foreach ($callables as $callable) {
            $this->add($callable);
        }
        return $this;
    }

    public function remove(callable $callable)
    {
        return $this->pop($this->indexOf($callable));
    }

    public function indexOf(callable $callable)
    {

        foreach ($this->_callables as $i=>$knownCallable) {
            if ($this->isSameCallable($callable, $knownCallable)) {
                return $i;
            }
        }

        throw new OutOfBoundsException('Callable not found');

    }

    public function contains(callable $callable)
    {
        try {
            return is_int($this->indexOf($callable));
        } catch (OutOfBoundsException $e) {
            return false;
        }
    }

    public function count()
    {
        return count($this->_callables);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->_callables);
    }

    public function offsetExists($offset)
    {
        return isset($this->_callables[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->_callables[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->_callables[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->pop($offset);
    }

    public function pop($index=null)
    {

        if($index === null){
            array_pop($this->_callables);
            return $this;
        }

        unset($this->_callables[$index]);
        $this->_callables = array_values($this->_callables);

        return $this;
    }

    protected function isSameCallable(callable $callable1, callable $callable2)
    {

        $callable1Type = gettype($callable1);
        $callable2Type = gettype($callable2);

        if ($callable1Type != $callable2Type) {
            return false;
        }

        if ($callable1Type == 'array' && $callable1 === $callable2) {
            return true;
        }

        if ($callable1Type == 'string' && $callable1 == $callable2) {
            return true;
        }

        if ($callable1Type != 'object') {
            return false;
        }

        $callable1Id = spl_object_hash($callable1);
        $callable2Id = spl_object_hash($callable2);

        return ($callable1Id == $callable2Id);
    }

}