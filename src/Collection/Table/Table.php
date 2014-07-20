<?php namespace Collection\Table;

use Collection\ColumnList;
use DomainException;
use Iterator;
use IteratorAggregate;
use ArrayIterator;

class Table implements Iterator{

    protected $columns;

    protected $sortColumns = array();

    protected $linkBuilder;

    protected $sortParamName = 'sort';

    protected $orderParamName = 'order';

    protected $ascName = 'asc';

    protected $descName = 'desc';

    protected $linkParams = array();

    protected $iteratorPos = -1;

    protected $src;

    protected $srcIterator;

    public function getSrc(){
        return $this->src;
    }

    public function setSrc($src){

        if($src instanceof IteratorAggregate){
            $this->srcIterator = $src->getIterator();
        }
        elseif($src instanceof Iterator){
            $this->srcIterator = $src;
        }
        elseif(is_array($src)){
            $this->srcIterator = new ArrayIterator($src);
        }
        else{
            throw new DomainException('Src has to be array, Iterator or IteratorAggregate');
        }

        $this->src = $src;

        return $this;
    }

    public function getColumns(){
        return $this->columns;
    }

    public function setColumns($columns){
        if(!$columns instanceof ColumnList){
            $columns = ColumnList::fromArray($columns);
        }
        $this->columns = $columns;
        foreach($this->columns as $col){
            $col->_setTable($this);
        }
        return $this;
    }

    public function addSortColumn($colName, $order){
        $this->sortColumns[$colName] = $order;
    }

    public function hasSortColumn($colName){
        return isset($this->sortColumns[$colName]);
    }

    public function getSortOrder($colName){
        if(isset($this->sortColumns[$colName])){
            return $this->sortColumns[$colName];
        }
        return '';
    }

    public function buildLink(array $addParams=array()){
        $builder = $this->linkBuilder;
        $params = array_merge($this->linkParams, $addParams);
        return $builder($this, $params);
    }

    public function getLinkBuilder(){
        return $this->linkBuilder;
    }

    public function setLinkBuilder($builder){
        if(!is_callable($builder)){
            throw new DomainException('Builder has to be callable');
        }
        $this->linkBuilder = $builder;
        return $this;
    }

    public function getSortParamName(){
        return $this->sortParamName;
    }

    public function setSortParamName($name){
        $this->sortParamName = $name;
        return $this;
    }

    public function getOrderParamName(){
        return $this->orderParamName;
    }

    public function setOrderParamName($name){
        $this->orderParamName = $name;
        return $this;
    }

    public function getAscName(){
        return $this->ascName;
    }

    public function setAscName($name){
        $this->ascName = $name;
        return $this;
    }

    public function getDescName(){
        return $this->descName;
    }

    public function setDescName($name){
        $this->descName = $name;
        return $this;
    }

    public function getLinkParams(){
        return $this->linkParams;
    }

    public function setLinkParams($params){
        $this->linkParams = array();
        foreach($params as $name=>$value){
            $this->linkParams[$name] = $value;
        }
        return $this;
    }

    public function __get($name){
        return $this->{"get$name"}();
    }

    public function current(){
        $current = $this->srcIterator->current();
        $this->columns->setSrc($current);
        return $current;
    }

    public function key(){
        return $this->iteratorPos;
    }

    public function next(){
        $this->iteratorPos++;
        $this->srcIterator->next();
    }

    public function rewind(){
        $this->srcIterator->rewind();
        $this->iteratorPos = 0;
    }

    public function valid(){
        return $this->srcIterator->valid();
    }
}