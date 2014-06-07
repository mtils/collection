<?php namespace Collection;

use DomainException;
use UnexpectedValueException;

class Column{

    protected $accessor;

    protected $title;

    protected $src;

    private $_needsMethodAccess = FALSE;

    private $srcType;

    protected $columnList;

    public function getAccessor(){
        return $this->accessor;
    }

    public function setAccessor($accessor){
        $this->_needsMethodAccess = (strpos($accessor,'(') !== FALSE);        
        if($this->_needsMethodAccess){
            $this->accessor = $this->extractMethodAccessorString($accessor);
        }
        else{
            $this->accessor = $accessor;
        }
        return $this;
    }

    public function getName(){
        return $this->accessor;
    }

    public function getTitle(){
        if(!$this->title){
            return $this->getName();
        }
        return $this->title;
    }

    public function setTitle($title){
        $this->title = $title;
        return $this;
    }

    public function getValue(){
        if($this->columnList){
            $src = $this->columnList->_src;
        }
        else{
            $src = $this->src;
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

    public function getSrc(){
        return $this->src;
    }

    public function setSrc($src){
        $this->src = $src;
        return $this;
    }

    protected function needsMethodAccess(){
        return $this->_needsMethodAccess;
    }

    public function getColumnList(){
        return $this->columnList;
    }

    public function setColumnList(ColumnList $list){
        $this->columnList = $list;
        return $this;
    }

    protected function extractMethodAccessorString($accessor){
        return substr($accessor,0,strpos($accessor,'('));
    }

    public static function create(){
        $class = get_called_class();
        return new $class();
    }

    public function __get($n){
        $methodName = 'get'.ucfirst($n);
        return $this->$methodName();
    }
}