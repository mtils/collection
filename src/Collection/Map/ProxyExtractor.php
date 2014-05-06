<?php namespace Collection\Map;

use \DomainException;

class ProxyExtractor extends Extractor{

    /**
     * @brief The source Extractor (callable)
     * @var Extractor
     */
    protected $srcExtractor = NULL;

    public function __construct($keyAccessor=NULL, $valueAccessor=NULL){
        if(is_callable($keyAccessor)){
            $this->srcExtractor = $keyAccessor;
        }
        else{
            $this->srcExtractor = new Extractor($keyAccessor, $valueAccessor);
        }
    }

    protected function createProxy($item, $key, $value, $position){
        $proxy = new ValueProxy($item);
        $proxy->_setKey($key);
        $proxy->_setValue($value);
        $proxy->_setPosition($position);
        return $proxy;
    }

    public function __invoke($originalKey, $item, $position=NULL){
        list($key, $value) = $this->srcExtractor->__invoke($originalKey, $item, $position);
        $proxy = $this->createProxy($item, $key, $value,
                                    $position);
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