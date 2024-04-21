<?php namespace Collection\Iterator;

use ArrayIterator as PhpArrayIterator;
use Collection\CollectionCreator;
use Collection\Dictionary;
use Collection\Map;
use Collection\Map\Extractor;
use Collection\OrderedList;
use Countable;
use DomainException;
use Iterator;
use IteratorAggregate;

use function gettype;
use function is_iterable;


class CastableIterator implements Iterator, Countable, CollectionCreator
{

    /**
    * The src Iterator CastableIterator acts as a proxy, so it can be
    * used with every Iterable var
    * @var Iterator|null
    */
    private Iterator|null $srcIterator = null;

    /**
     * @var ?callable
     */
    protected mixed $caster = null;

    protected int $position = 0;

    protected iterable $src;

    protected mixed $currentKey = null;

    protected mixed $currentValue = null;

    public function __construct($src, callable|array|null $caster=NULL)
    {
        if( ($src instanceof Map) && is_null($caster)){
            $caster = $src->getExtractor();
            $src = $src->getSrc();
        }
        if ($caster === null) {
            throw new DomainException("Couldn't find caster for " .gettype($src));
        }
        if (is_array($caster)) {
            $caster = new Extractor($caster);
        }
        if (!is_callable($caster)) {
            throw new DomainException("Caster has to be callable");
        }
        $this->caster = $caster;
        $this->srcIterator = $this->createSrcIterator($src);
        $this->src = $src;
    }

    protected function createSrcIterator($src) : Iterator
    {
        if(is_array($src)){
            return new PhpArrayIterator($src);
        }
        if($src instanceof Iterator){
            return $src;
        }
        if($src instanceof IteratorAggregate){
            /** @noinspection PhpUnhandledExceptionInspection */
            return $src->getIterator();
        }
        throw new DomainException("Couldn't find srcIterator for " . gettype($src));
    }

    protected function updateKeyAndValue() : void
    {
        if($this->srcIterator->valid()){
            list($key,$val) = $this->caster->__invoke($this->srcIterator->key(),
                                                      $this->srcIterator->current(),
                                                      $this->position);
            $this->currentKey = $key;
            $this->currentValue = $val;
            return;
        }
        $this->currentKey = null;
        $this->currentValue = null;
    }

    public function current() : mixed
    {
        return $this->currentValue;
    }

    public function item() : mixed
    {
        return $this->srcIterator->current();
    }

    public function key() : mixed
    {
        return $this->currentKey;
    }

    public function next() : void
    {
        $this->srcIterator->next();
        $this->position++;
        $this->updateKeyAndValue();
    }

    public function rewind() : void
    {
        $this->srcIterator->rewind();
        $this->position = 0;
        $this->updateKeyAndValue();
    }

    public function valid() : bool
    {
        return $this->srcIterator->valid();
    }

    public function count() : int
    {
        if(is_array($this->src) || ($this->src instanceof Countable)){
            return count($this->src);
        }
        if($this->srcIterator instanceof Countable){
            return count($this->srcIterator);
        }
        if(is_iterable($this->src)){
            $i = 0;
            foreach($this->src as $_){
                $i++;
            }
            return $i;
        }
        return 0;
    }

    public function toArray() : array
    {
        $array = [];
        foreach($this as $key=>$value){
            $array[$key] = $value;
        }
        return $array;
    }

    public function toList() : OrderedList
    {
        return new OrderedList($this->toArray());
    }

    public function toDictionary() : Dictionary
    {
        return new Dictionary($this->toArray());
    }

    public function toMap(array|callable|null $extractor=null) : Map
    {
        if($this->src instanceof Map && is_null($extractor)){
            return $this->src;
        }
        return new Map($this->src, $extractor);
    }
}