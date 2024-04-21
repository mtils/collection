<?php namespace Collection;

use OutOfBoundsException;
use Collection\Iterator\ArrayIterator;
use ReturnTypeWillChange;

trait CallableSetTrait
{

    /**
     * @var callable[]
     */
    protected array $_callables = [];

    public function add(callable $callable) : static
    {
        if (!$this->contains($callable)) {
            $this->_callables[] = $callable;
        }
        return $this;
    }

    /**
     * @param callable[] $callables
     * @return $this
     */
    public function extend(iterable $callables) : static
    {
        $callables = func_num_args() > 1 ? func_get_args() : (array) $callables;
        foreach ($callables as $callable) {
            $this->add($callable);
        }
        return $this;
    }

    public function remove(callable $callable) : static
    {
        return $this->pop($this->indexOf($callable));
    }

    public function indexOf(callable $callable) : int
    {

        foreach ($this->_callables as $i=>$knownCallable) {
            if ($this->isSameCallable($callable, $knownCallable)) {
                return $i;
            }
        }

        throw new OutOfBoundsException('Callable not found');

    }

    public function contains(callable $callable) : bool
    {
        try {
            return is_int($this->indexOf($callable));
        } catch (OutOfBoundsException $e) {
            return false;
        }
    }

    public function count() : int
    {
        return count($this->_callables);
    }

    public function getIterator() : \Iterator
    {
        return new ArrayIterator($this->_callables);
    }

    #[ReturnTypeWillChange]
    public function offsetExists(mixed $offset) : bool
    {
        return isset($this->_callables[$offset]);
    }

    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset) : callable
    {
        return $this->_callables[$offset];
    }

    #[ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value) : void
    {
        $this->_callables[$offset] = $value;
    }

    #[ReturnTypeWillChange]
    public function offsetUnset(mixed $offset) : void
    {
        $this->pop($offset);
    }

    public function pop(?int $index=null) : static
    {

        if($index === null){
            array_pop($this->_callables);
            return $this;
        }

        unset($this->_callables[$index]);
        $this->_callables = array_values($this->_callables);

        return $this;
    }

    protected function isSameCallable(callable $callable1, callable $callable2) : bool
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

        /** @noinspection PhpParamsInspection */
        $callable1Id = spl_object_hash($callable1);
        /** @noinspection PhpParamsInspection */
        $callable2Id = spl_object_hash($callable2);

        return ($callable1Id == $callable2Id);
    }

}