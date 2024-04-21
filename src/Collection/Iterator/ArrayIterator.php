<?php namespace Collection\Iterator;

use ArrayIterator as PhpArrayIterator;
use Collection\Dictionary;
use Collection\OrderedList;
use Collection\CollectionCreator;
use Collection\Map;

class ArrayIterator extends PhpArrayIterator implements IteratorInterface, CollectionCreator
{

    public function first() : mixed
    {
        $this->seek(0);
        return $this->current();
    }

    public function last() : mixed
    {
        $this->seek($this->count()-1);
        return $this->current();
    }

    public function toArray() : array
    {
        return $this->getArrayCopy();
    }

    public function toList() : OrderedList
    {
        return new OrderedList($this->getArrayCopy());
    }

    public function toDictionary() : Dictionary
    {
        return new Dictionary($this->getArrayCopy());
    }

    public function toMap(array|callable|null $extractor=NULL) : Map
    {
        return new Map($this->getArrayCopy(), $extractor);
    }
}