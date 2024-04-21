<?php namespace Collection\Map;

use DomainException;

use function is_array;
use function var_dump;

class Extractor
{

    const POSITION = '__position';
    const VALUE = '__value';
    const KEY = '__key';

    protected ?string $keyAccessor = null;
    protected ?string $valueAccessor = null;

    private bool $keyNeedsMethodAccess = false;
    private bool $valueNeedsMethodAccess = false;

    public function __construct(?string $keyAccessor=null, ?string $valueAccessor=null)
    {
        if($keyAccessor !== null) {
            $this->setKeyAccessor($keyAccessor);
        }
        if($valueAccessor !== NULL){
            $this->setValueAccessor($valueAccessor);
        }
    }

    public function __invoke($originalKey, $item, $position=null) : array
    {
        $result = ['' ,null];

        if (is_object($item)) {

            if($this->keyNeedsMethodAccess){
                $result[0] = $item->{$this->keyAccessor}();
            } else{
                $result[0] = $item->{$this->keyAccessor};
            }

            if($this->valueNeedsMethodAccess){
                $result[1] = $item->{$this->valueAccessor}();
            } else {
                $result[1] = $item->{$this->valueAccessor};
            }
            return $result;
        }
        if(is_array($item)){
            return [$item[$this->keyAccessor], $item[$this->valueAccessor]];
        }

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
        return $result;
    }

    public function getKeyAccessor() : string
    {
        return $this->keyAccessor;
    }

    public function setKeyAccessor(string $accessor) : static
    {
        if($this->needsMethodAccess($accessor)){
            $this->keyNeedsMethodAccess = true;
            $this->keyAccessor = $this->extractMethodAccessorString($accessor);
        }
        else{
            $this->keyNeedsMethodAccess = false;
            $this->keyAccessor = $accessor;
        }
        return $this;
    }

    public function getValueAccessor() : string
    {
        return $this->valueAccessor;
    }

    public function setValueAccessor(string $accessor) : static
    {
        if ($this->needsMethodAccess($accessor)) {
            $this->valueNeedsMethodAccess = true;
            $this->valueAccessor = $this->extractMethodAccessorString($accessor);
            return $this;
        }

        $this->valueNeedsMethodAccess = false;
        $this->valueAccessor = $accessor;

        return $this;
    }

    protected function needsMethodAccess(string $accessor) : bool
    {
        return (str_contains($accessor, '('));
    }

    protected function extractMethodAccessorString(string $accessor) : string
    {
        return substr($accessor,0,strpos($accessor,'('));
    }

    public function setItemValue(object|array &$item, mixed $value) : void
    {
        if (is_array($item)) {
            $item[$this->keyAccessor] = $value;
            return;
        }

        if($this->keyNeedsMethodAccess){
            throw new DomainException("Setting via methods is not implemented");
        }
        $item->{$this->keyAccessor} = $value;

    }

    public function unSetItemKey(object|array &$item, mixed $key=null) : void
    {
        if ($key === null) {
            $key = $this->keyAccessor;
        }
        if(is_array($item)){
            unset($item[$key]);
            return;
        }

        if($this->keyNeedsMethodAccess) {
            throw new DomainException("Unsetting via methods is not implemented");
        }
        unset($item->{$key});

    }
}

