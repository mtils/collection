<?php namespace Collection\Table;

use Collection\ColumnList;
use Collection\StringList;
use Collection\ClassNamer;
use DomainException;
use Iterator;
use Countable;
use IteratorAggregate;
use ArrayIterator;
use UnderflowException;

class Table implements Iterator, Countable
{

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

    protected $itemClass;

    protected $calculatedItemClass;

    /**
    * @brief CSS Classes
    * @var StringList
    */
    protected $cssClasses;

    public function getSrc(){
        return $this->src;
    }

    public function setSrc($src){
        $this->src = $src;
        return $this;
    }

    public function getItemClass(){

        if(!$this->itemClass){
            return $this->getCalculatedItemClass();
        }

        return $this->itemClass;

    }

    public function setItemClass($itemClass){
        $this->itemClass = $itemClass;
    }

    protected function getCalculatedItemClass(){

        if(!$this->calculatedItemClass){

            foreach($this->createSrcIterator() as $item){
                if(is_object($item)){
                    $className = get_class($item);
                    if($className == 'stdClass'){
                        if(isset($item->className)){
                            $this->calculatedItemClass = $item->className;
                            break;
                        }
                    }
                    $this->calculatedItemClass = $className;
                    break;
                }
            }
            if(!$this->calculatedItemClass){
                throw new UnderflowException('Could not determine itemClass, please set it manually via setItemClass');
            }
        }

        return $this->calculatedItemClass;

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

    public function getCssClasses(){
        if(!$this->cssClasses){
            $this->cssClasses = $this->createCssClasses();
        }
        return $this->cssClasses;
    }

    public function setCssClasses(StringList $cssClasses){
        $this->cssClasses = $cssClasses;
        return $this;
    }

    protected function createCssClasses(){

        $classes = new StringList();

        try{
            $classes->append(ClassNamer::cssClass($this->getItemClass()));
        }
        catch(UnderflowException $e){}

        return $classes;
    }

    protected function createSrcIterator($src){
        if($src instanceof IteratorAggregate){
            return $src->getIterator();
        }
        elseif($src instanceof Iterator){
            return $src;
        }
        elseif(is_array($src)){
            return new ArrayIterator($src);
        }
        else{
            throw new DomainException('Src has to be array, Iterator or IteratorAggregate');
        }
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

        if (isset($_GET[$this->sortParamName]) &&
            $_GET[$this->sortParamName] == $colName &&
            isset($_GET[$this->orderParamName])){
                return $_GET[$this->orderParamName];
        }
        return '';
    }

    public function buildLink(array $addParams=array()){

        $params = array_merge($this->linkParams, $addParams);

        if ($builder = $this->linkBuilder) {
            return $builder($this, $params);
        }

        $allParams = array_merge($_GET, $params);

        $parsed = parse_url($_SERVER['REQUEST_URI']);

        return '/' . trim($parsed['path'],'/') . '?'.http_build_query($allParams);
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

    public function __set($name, $value){
        return $this->{"set$name"}($value);
    }

    public function __isset($name){
        return method_exists($this, 'get'.ucfirst($name));
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
        $this->srcIterator = $this->createSrcIterator($this->src);
        $this->srcIterator->rewind();
        $this->iteratorPos = 0;
    }

    public function count()
    {
        if (is_array($this->src) || $this->src instanceof \Countable) {
            return count($this->src);
        }
        if ($this->src instanceof \Traversable) {
//             dd($this->src);
        //             dd(iterator_to_array($this->src));
            return count(iterator_to_array($this->src));
        }
        return 0;
    }

    public function valid(){
        return $this->srcIterator->valid();
    }
}
