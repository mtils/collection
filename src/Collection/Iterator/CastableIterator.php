<?php namespace Collection\Iterator;

use \Iterator;
use \IteratorAggregate;
use \Countable;
use \DomainException;
use \ArrayIterator as PhpArrayIterator;
use Collection\Map;
use Collection\Map\Extractor;
use Collection\CollectionCreator;
use Collection\OrderedList;
use Collection\Dictionary;



class CastableIterator implements Iterator, Countable, CollectionCreator{

    /**
    * @brief The src Iterator CastableIterator acts as an proxy, so it can be
    *        used with every Iterable var
    * @var Iterator
    */
    private $srcIterator = NULL;
    protected $caster = NULL;
    protected $position = 0;
    protected $src;
    protected $currentKey = NULL;
    protected $currentValue = NULL;

    public function __construct($src, $caster=NULL){
        if( ($src instanceof Map) && is_null($caster)){
            $caster = $src->getExtractor();
            $src = $src->getSrc();
        }
        if($caster === NULL){
            throw new DomainException("Couldnt find caster for ".\gettype($src));
        }
        if(is_array($caster)){
            $caster = new Extractor($caster);
        }
        if(!is_callable($caster)){
            throw new DomainException("Caster has to be callable");
        }
        $this->caster = $caster;
        $this->srcIterator = $this->createSrcIterator($src);
        $this->src = $src;
    }

    protected function createSrcIterator($src){
        if(is_array($src)){
            return new PhpArrayIterator($src);
        }
        elseif($src instanceof Iterator){
            return $src;
        }
        elseif($src instanceof IteratorAggregate){
            return $src->getIterator();
        }
        throw new DomainException("Couldnt find srcIterator for " . \gettype($src));
    }

    protected function updateKeyAndValue(){
        if($this->srcIterator->valid()){
            list($key,$val) = $this->caster->__invoke($this->srcIterator->key(),
                                                      $this->srcIterator->current(),
                                                      $this->position);
            $this->currentKey = $key;
            $this->currentValue = $val;
            return;
        }
        $this->currentKey = NULL;
        $this->currentValue = NULL;
    }

    public function current(){
        return $this->currentValue;
    }

    public function item(){
        return $this->srcIterator->current();
    }

    public function key(){
        return $this->currentKey;
    }

    public function next(){
        $this->srcIterator->next();
        $this->position++;        
        $this->updateKeyAndValue();
    }

    public function rewind(){
        $this->srcIterator->rewind();
        $this->position = 0;        
        $this->updateKeyAndValue();
    }

    public function valid(){
        return $this->srcIterator->valid();
    }

    public function count(){
        if(is_array($this->src) || ($this->src instanceof Countable)){
            return count($this->src);
        }
        elseif($this->srcIterator instanceof Countable){
            return count($this->srcIterator);
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

    public function toArray(){
        $array = array();
        foreach($this as $key=>$value){
            $array[$key] = $value;
        }
        return $array;
    }

    public function toList(){
        return new OrderedList($this->toArray());
    }

    public function toDictionary(){
        return new Dictionary($this->toArray());
    }

    public function toMap($extractor=NULL){
        if($this->src instanceof Map && is_null($extractor)){
            return $this->src;
        }
        return new Map($this->src,$extractor);
    }
}