<?php namespace Collection;

use UnexpectedValueException;

class Column
{

    protected string $accessor = "";

    /**
     * @var callable|null
     */
    protected mixed $valueGetter;

    protected string $title = "";

    protected mixed $src;

    private bool $_needsMethodAccess = false;

    protected ?ColumnList $columnList = null;

    public function getAccessor() : string
    {
        return $this->accessor;
    }

    public function setAccessor(string $accessor, callable $valueGetter=null) : static
    {

        $this->_needsMethodAccess = (str_contains($accessor, '('));

        if($this->_needsMethodAccess){
            $this->accessor = $this->extractMethodAccessorString($accessor);
        } else{
            $this->accessor = $accessor;
        }

        $this->valueGetter = $valueGetter;

        return $this;
    }

    public function getValueGetter() : ?callable
    {
        return $this->valueGetter;
    }

    public function setValueGetter(callable $valueGetter) : static
    {
        $this->valueGetter = $valueGetter;
        return $this;
    }

    public function getName() : string
    {
        return $this->accessor;
    }

    public function getTitle() : string
    {
        if(!$this->title){
            return $this->getName();
        }
        return $this->title;
    }

    public function setTitle(string $title) : static
    {
        $this->title = $title;
        return $this;
    }

    public function getValue() : mixed
    {

        if($this->columnList){
            $src = $this->columnList->_src;
        }
        else{
            $src = $this->src;
        }

        if($this->valueGetter){
            $func = $this->valueGetter;
            return $func($this, $src, $this->accessor);
        }

        if(is_object($src)){
            if($this->_needsMethodAccess){
                return $src->{$this->accessor}();
            }
            return $src->{$this->accessor};
        }
        elseif(is_array($src)){
            return $src[$this->accessor];
        }
        else{
            throw new UnexpectedValueException('Column works only with non-scalar types');
        }
    }

    public function getSrc() : mixed
    {
        return $this->src;
    }

    public function setSrc(mixed $src) : static
    {
        $this->src = $src;
        return $this;
    }

    public function needsMethodAccess() : bool
    {
        return $this->_needsMethodAccess;
    }

    public function getColumnList() : ?ColumnList
    {
        return $this->columnList;
    }

    public function setColumnList(ColumnList $list) : static
    {
        $this->columnList = $list;
        return $this;
    }

    protected function extractMethodAccessorString(string $accessor) : string
    {
        return substr($accessor,0,strpos($accessor,'('));
    }

    public static function create()
    {
        $class = get_called_class();
        return new $class();
    }

    public function __get($n){
        $methodName = 'get'.ucfirst($n);
        return $this->$methodName();
    }
}