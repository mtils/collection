<?php namespace Collection;

use \Iterator as PHPIterator;
use \Countable;

class Iterator implements PHPIterator, Countable{

    protected $src;

    protected $currentPosition = 0;

    protected $currentValue = NULL;
    protected $currentKey = NULL;

    public function getSrc(){
        return $this->src;
    }

    public function setSrc($src){
        $this->src = $src;
    }

    public function current(){
        return $this->currentValue;
    }

    public function key(){
        return $this->currentKey;
    }

    public function next(){
        ++$this->currentPosition;
    }

    public function rewind(){
        $this->currentPosition = 0;
    }

    public function valid(){
        return ($this->currentKey !== NULL);
    }

    public function count(){
        return count($this->src);
    }
}