<?php namespace Collection\Iterator;

use \ArrayIterator as PhpArrayIterator;
use Collection\Dictionary;
use Collection\OrderedList;
use Collection\CollectionCreator;
use Collection\Map;

class ArrayIterator extends PhpArrayIterator implements IteratorInterface, CollectionCreator{

    public function first(){
        $this->seek(0);
        return $this->current();
    }

    public function last(){
        $this->seek($this->count()-1);
        return $this->current();
    }

    public function toArray(){
        return $this->getArrayCopy();
    }

    public function toList(){
        return new OrderedList($this->getArrayCopy());
    }

    public function toDictionary(){
        return new Dictionary($this->getArrayCopy());
    }

    public function toMap($extractor=NULL){
        return new Map($this->getArrayCopy(), $extractor);
    }
}