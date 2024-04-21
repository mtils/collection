<?php namespace Collection\Map;

use Collection\FullProxy;
use Collection\ColumnList;

class ValueProxy extends FullProxy
{

    protected ?int $position = null;
    protected $key = null;
    protected mixed $value = null;

    protected ?ColumnList $columns;

    public function getKey()
    {
        return $this->key;
    }

    public function _setKey($key) : static
    {
        $this->key = $key;
        return $this;
    }

    public function getValue() : mixed
    {
        return $this->value;
    }

    public function _setValue(mixed $value) : static
    {
        $this->value = $value;
        return $this;
    }

    public function getPosition() : ?int
    {
        return $this->position;
    }

    public function _setPosition(?int $pos) : static
    {
        $this->position = $pos;
        return $this;
    }

    public function __toString() : string
    {
        return (string)$this->value;
    }

    public function __get(string $name): mixed
    {
        if(in_array($name, ['key','value','position'])){
            return $this->{$name};
        }
        return parent::__get($name);
    }

    public function getColumns() : ?ColumnList
    {
        return $this->columns;
    }

    public function setColumns(ColumnList $columns) : static
    {
        $this->columns = $columns;
        return $this;
    }
}