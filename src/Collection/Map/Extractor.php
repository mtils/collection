<?php namespace Collection\Map;

use \DomainException;

class Extractor{

    const POSITION = '__position';
    const VALUE = '__value';
    const KEY = '__key';

    protected $keyAccessor = NULL;
    protected $valueAccessor = NULL;

    private $keyNeedsMethodAccess = FALSE;
    private $valueNeedsMethodAccess = FALSE;

    public function __construct($keyAccessor=NULL, $valueAccessor=NULL){
        if($keyAccessor !== NULL){
            $this->setKeyAccessor($keyAccessor);
        }
        if($valueAccessor !== NULL){
            $this->setValueAccessor($valueAccessor);
        }
    }

    public function __invoke($originalKey, $item, $position=NULL){
        $result = array('',NULL);

        if(is_object($item)){
            if($this->keyNeedsMethodAccess){
                $result[0] = $item->{$this->keyAccessor}();
            }
            else{
                $result[0] = $item->{$this->keyAccessor};
            }
            if($this->valueNeedsMethodAccess){
                $result[1] = $item->{$this->valueAccessor}();
            }
            else{
                $result[1] = $item->{$this->valueAccessor};
            }
        }
        elseif(is_array($item)){
            $result[0] = $item[$this->keyAccessor];
            $result[1] = $item[$this->valueAccessor];
        }
        else{
            switch($this->keyAccessor){
                case self::VALUE:
                    $result[0] = $item;
                    break;
                case self::POSITION:
                    $result[0] = $position;
                    break;
                default:
                    $result[0] = $originalKey;
                    break;
            }
            switch($this->valueAccessor){
                case self::KEY:
                    $result[1] = $originalKey;
                    break;
                case self::POSITION:
                    $result[1] = $position;
                    break;
                default:
                    $result[1] = $item;
                    break;
            }
        }
        return $result;
    }
    
    public function getKeyAccessor(){
        return $this->keyAccessor;
    }
    
    public function setKeyAccessor($accessor){
        if($this->needsMethodAccess($accessor)){
            $this->keyNeedsMethodAccess = TRUE;
            $this->keyAccessor = $this->extractMethodAccessorString($accessor);
        }
        else{
            $this->keyNeedsMethodAccess = FALSE;
            $this->keyAccessor = $accessor;
        }
        return $this;
    }
    
    public function getValueAccessor(){
        return $this->valueAccessor;
    }
    
    public function setValueAccessor($accessor){
        if($this->needsMethodAccess($accessor)){
            $this->valueNeedsMethodAccess = TRUE;
            $this->valueAccessor = $this->extractMethodAccessorString($accessor);
        }
        else{
            $this->valueNeedsMethodAccess = FALSE;
            $this->valueAccessor = $accessor;
        }
        return $this;
    }
    
    protected function needsMethodAccess($accessor){
        return (strpos($accessor,'(') !== FALSE);
    }
    
    protected function extractMethodAccessorString($accessor){
        return substr($accessor,0,strpos($accessor,'('));
    }

    public function setItemValue($item, $value){
        if(is_object($item)){
            if($this->keyNeedsMethodAccess){
                throw new DomainException("Setting via methods is not implemented");
            }
            $item->{$this->keyAccessor} = $value;
        }
        elseif(is_array($item)){
            $item[$this->keyAccessor] = $value;
        }
        else{
            throw new DomainException("Cannot set value for unknown var " . gettype($item));
        }
    }

    public function unSetItemKey($item, $key){
        if(is_object($item)){
            if($this->keyNeedsMethodAccess){
                throw new DomainException("Unsetting via methods is not implemented");
            }
            unset($item->{$this->keyAccessor});
        }
        elseif(is_array($item)){
            unset($item[$this->keyAccessor]);
        }
        else{
            throw new DomainException("Cannot set value for unknown var " . gettype($item));
        }
    }
}

