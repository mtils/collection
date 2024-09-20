<?php namespace Collection\Map;

use \DomainException;

use Collection\ColumnList;

class ProxyExtractor extends Extractor{

    /**
     * @brief The source Extractor (callable)
     * @var ?Extractor
     */
    protected ?Extractor $srcExtractor = NULL;

    /**
     * @var ?callable
     */
    protected $callableExtractor;

    protected ?ColumnList $columns = null;

    public function __construct(string|callable|null $keyAccessor=null, ?string $valueAccessor=null)
    {
        parent::__construct();
        if ($keyAccessor instanceof Extractor) {
            $this->srcExtractor = $keyAccessor;
            return;
        }
        if(!is_callable($keyAccessor)){
            $this->srcExtractor = new Extractor($keyAccessor, $valueAccessor);
            return;
        }
        $this->callableExtractor = $keyAccessor;
        $this->srcExtractor = new Extractor(null, $valueAccessor);

    }

    public function getColumns() : ?ColumnList
    {
        return $this->columns;
    }

    public function setColumns(ColumnList|array $columns) : static
    {
        if($columns instanceof ColumnList){
            $this->columns = $columns;
        }
        else{
            $this->columns = ColumnList::fromArray($columns);
        }
        return $this;
    }

    protected function createProxy($item, $key, $value, $position) : ValueProxy
    {
        return new ValueProxy($item);
    }

    protected function setProxyValues(ValueProxy &$proxy, $key, $value, $position) : void
    {
        $proxy->_setKey($key);
        $proxy->_setValue($value);
        $proxy->_setPosition($position);
        if($this->columns){
            $this->columns->setSrc($proxy->getSrc());
            $proxy->setColumns($this->columns);
        }
    }

    public function __invoke($originalKey, $item, $position=null) : array
    {
        if ($this->callableExtractor) {
            list($key, $value) = call_user_func($this->callableExtractor, $originalKey, $item, $position);
        } else {
            list($key, $value) = $this->srcExtractor->__invoke($originalKey, $item, $position);
        }

        $proxy = $this->createProxy($item, $key, $value, $position);
        $this->setProxyValues($proxy, $key, $value, $position);

        return [$key,$proxy];
    }

    public function getKeyAccessor() : string
    {
        return $this->srcExtractor->getKeyAccessor();
    }

    public function setKeyAccessor(string $accessor) : static
    {
        return $this->srcExtractor->setKeyAccessor($accessor);
    }

    public function getValueAccessor() : string
    {
        return $this->srcExtractor->getValueAccessor();
    }

    public function setValueAccessor(string $accessor) : static
    {
        return $this->srcExtractor->setValueAccessor($accessor);
    }

    public function setItemValue(object|array &$item, mixed $value) : void
    {
        $this->srcExtractor->setItemValue($item, $value);
    }

    public function unSetItemKey(object|array &$item, mixed $key=null) : void
    {
        $this->srcExtractor->unSetItemKey($item, $key);
    }
}