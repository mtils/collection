<?php namespace Collection;

use \ArrayAccess;
use \Countable;
use \BadMethodCallException;
use \IteratorAggregate;
use \DomainException;
use Collection\Map\Extractor;
use Collection\Iterator\CastableIterator;

class Map implements ArrayAccess, Countable, IteratorAggregate{

    protected $src = array();

    protected $extractor = NULL;

    public function __construct($src, $extractor){
        $this->setExtractor($extractor);
        $this->setSrc($src);
    }

    public function getSrc(){
        return $this->src;
    }

    public function setSrc($src){
        if(!is_array($src) && !($src instanceof Traversable)){
            throw new DomainException("src has to traversable");
        }
        $this->src = $src;
        return $this;
    }

    public function getExtractor(){
        return $this->extractor;
    }

    public function setExtractor($extractor){
        if(is_array($extractor)){
            $extractor = new Extractor($extractor[0],
                                       $extractor[1]);
        }
        if(!is_callable($extractor)){
            throw new BadMethodCallException('Extractor has to be callable');
        }
        $this->extractor = $extractor;
        return $this;
    }

    public function offsetExists($offset){
        $i = 0;
        foreach($this->src as $item){
            list($key,$value) = $this->extractor->__invoke($item, $i);
            if($key == $offset){
                return TRUE;
            }
            $i++;
        }
        return FALSE;
    }

    public function offsetGet($offset){
        $i = 0;
        foreach($this->src as $originalKey=>$item){
            list($key,$value) = $this->extractor->__invoke($originalKey,$item, $i);
            if($key == $offset){
                return $value;
            }
            $i++;
        }
    }

    public function offsetSet($offset, $value){
        if(!method_exists($this->extractor,'setItemValue')){
            throw new DomainException("The extractor has no setItemValue method");
        }
        $i = 0;
        foreach($this->src as $originalKey=>$item){
            list($key,$oldValue) = $this->extractor->__invoke($originalKey,$item, $i);
            if($key == $offset){
                $this->extractor->setItemValue($item,$value);
            }
            $i++;
        }
    }

    public function offsetUnset($offset){
        if(!method_exists($this->extractor,'setItemKey')){
            throw new DomainException("The extractor has no unsetItemKey method");
        }
        $i=0;
        foreach($this->src as $originalKey=>$item){
            list($key,$oldValue) = $this->extractor->__invoke($originalKey,$item,$i);
            if($key == $offset){
                $this->extractor->unsetItemKey($item,$key);
            }
            $i++;
        }
    }

    public function count(){
        if(is_array($this->src) || ($this->src instanceof Countable)){
            return count($this->src);
        }
        elseif($this->src instanceof Traversable){
            $i = 0;
            foreach($this->src as $unused){
                $i++;
            }
            return $i;
        }
        return 0;
    }
    
    public function getIterator(){
        return new CastableIterator($this);
    }
}