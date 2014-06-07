<?php namespace Collection\Map;

use \DomainException;

use Collection\ColumnList;

class ProxyExtractor extends Extractor{

    /**
     * @brief The source Extractor (callable)
     * @var Extractor
     */
    protected $srcExtractor = NULL;

    protected $columns = NULL;

    public function __construct($keyAccessor=NULL, $valueAccessor=NULL){
        if(is_callable($keyAccessor)){
            $this->srcExtractor = $keyAccessor;
        }
        else{
            $this->srcExtractor = new Extractor($keyAccessor, $valueAccessor);
        }
    }

    public function getColumns(){
        return $this->columns;
    }

    public function setColumns($columns){
        if($columns instanceof ColumnList){
            $this->columns = $columns;
        }
        else{
            $this->columns = ColumnList::fromArray($columns);
        }
        return $this;
    }

    protected function createProxy($item, $key, $value, $position){
        return new ValueProxy($item);
    }

    protected function setProxyValues(ValueProxy &$proxy, $key, $value, $position){
        $proxy->_setKey($key);
        $proxy->_setValue($value);
        $proxy->_setPosition($position);
        if($this->columns){
            $this->columns->setSrc($proxy->getSrc());
            $proxy->setColumns($this->columns);
        }
    }

    public function __invoke($originalKey, $item, $position=NULL){
        list($key, $value) = $this->srcExtractor->__invoke($originalKey, $item, $position);

        $proxy = $this->createProxy($item, $key, $value,
                                    $position);
        $this->setProxyValues($proxy, $key, $value, $position);

        return array($key,$proxy);
    }

    public function getKeyAccessor(){
        return $this->srcExtractor->getKeyAccessor();
    }

    public function setKeyAccessor($accessor){
        return $this->srcExtractor->setKeyAccessor($accessor);
    }

    public function getValueAccessor(){
        return $this->srcExtractor->getValueAccessor();
    }

    public function setValueAccessor($accessor){
        return $this->srcExtractor->setValueAccessor($accessor);
    }

    public function setItemValue($item, $value){
        return $this->srcExtractor->setItemValue($item, $value);
    }

    public function unSetItemKey($item, $key){
        return $this->srcExtractor->unSetItemKey($item, $key);
    }
}