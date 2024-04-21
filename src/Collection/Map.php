<?php namespace Collection;

use \ArrayAccess;
use \Countable;
use \BadMethodCallException;
use \IteratorAggregate;
use \DomainException;
use Collection\Map\Extractor;
use Collection\Iterator\CastableIterator;

use ReturnTypeWillChange;

use function is_iterable;
use function iterator_to_array;

class Map implements ArrayAccess, Countable, IteratorAggregate
{

    protected iterable $src = [];

    /**
     * @var ?callable
     */
    protected mixed $extractor = null;

    public function __construct($src, callable|array|null $extractor)
    {
        $this->setExtractor($extractor);
        $this->setSrc($src);
    }

    public function getSrc() : iterable
    {
        return $this->src;
    }

    public function setSrc(iterable $src) : static
    {
        $this->src = $src;
        return $this;
    }

    public function getExtractor() : ?callable
    {
        return $this->extractor;
    }

    public function setExtractor(callable|array $extractor) : static
    {
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

    #[ReturnTypeWillChange]
    public function offsetExists(mixed $offset) : bool
    {
        $i = 0;
        foreach($this->src as $item){
            list($key,$value) = $this->extractor->__invoke($i, $item, $i);
            if($key == $offset){
                return true;
            }
            $i++;
        }
        return false;
    }

    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset) : mixed
    {
        $i = 0;
        foreach($this->src as $originalKey=>$item){
            list($key,$value) = $this->extractor->__invoke($originalKey,$item, $i);
            if($key == $offset){
                return $value;
            }
            $i++;
        }
        return null;
    }

    #[ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value) : void
    {
        if(!method_exists($this->extractor,'setItemValue')){
            throw new DomainException("The extractor has no setItemValue method");
        }
        $i = 0;
        foreach($this->src as $originalKey=>$item){
            list($key,$oldValue) = $this->extractor->__invoke($originalKey,$item, $i);
            if($key === $offset){
                $this->extractor->setItemValue($item,$value);
            }
            $i++;
        }
    }

    #[ReturnTypeWillChange]
    public function offsetUnset(mixed $offset) : void
    {
        if(!method_exists($this->extractor,'unsetItemKey')){
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

    public function count() : int
    {
        if (is_array($this->src) || ($this->src instanceof Countable)){
            return count($this->src);
        }
        if (is_iterable($this->src)) {
            $i = 0;
            foreach($this->src as $_){
                $i++;
            }
            return $i;
        }
        return 0;
    }

    public function getIterator() : \Iterator
    {
        return new CastableIterator($this);
    }
}