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

    protected ?ColumnList $columns = null;

    protected array $sortColumns = [];

    /**
     * @var ?callable
     */
    protected $linkBuilder=null;

    protected string $sortParamName = 'sort';

    protected string $orderParamName = 'order';

    protected string $ascName = 'asc';

    protected string $descName = 'desc';

    protected array $linkParams = [];

    protected int $iteratorPos = -1;

    protected ?iterable $src;

    protected ?Iterator $srcIterator=null;

    protected string $itemClass = '';

    protected string $calculatedItemClass = '';

    protected ?StringList $cssClasses = null;

    public function getSrc() : iterable
    {
        return $this->src;
    }

    public function setSrc(iterable $src) : static
    {
        $this->src = $src;
        return $this;
    }

    public function getItemClass() : string
    {
        if(!$this->itemClass){
            return $this->getCalculatedItemClass();
        }
        return $this->itemClass;

    }

    public function setItemClass(string $itemClass) : void
    {
        $this->itemClass = $itemClass;
    }

    protected function getCalculatedItemClass() : string
    {

        if(!$this->calculatedItemClass){

            foreach($this->createSrcIterator($this->src) as $item){
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

    public function getColumns() : ?ColumnList
    {
        return $this->columns;
    }

    public function setColumns(ColumnList|array $columns) : static
    {
        if(!$columns instanceof ColumnList){
            $columns = ColumnList::fromArray($columns);
        }
        $this->columns = $columns;
        foreach($this->columns as $col){
            $col->_setTable($this);
        }
        return $this;
    }

    public function getCssClasses() : StringList
    {
        if(!$this->cssClasses){
            $this->cssClasses = $this->createCssClasses();
        }
        return $this->cssClasses;
    }

    public function setCssClasses(StringList $cssClasses) : static
    {
        $this->cssClasses = $cssClasses;
        return $this;
    }

    protected function createCssClasses() : StringList
    {

        $classes = new StringList();

        try{
            $classes->append(ClassNamer::cssClass($this->getItemClass()));
        }
        catch(UnderflowException $e){}

        return $classes;
    }

    protected function createSrcIterator(iterable $src) : Iterator
    {
        if($src instanceof IteratorAggregate){
            /** @noinspection PhpUnhandledExceptionInspection */
            return $src->getIterator();
        }
        if($src instanceof Iterator){
            return $src;
        }
        if(is_array($src)){
            return new ArrayIterator($src);
        }
        throw new DomainException('Src has to be array, Iterator or IteratorAggregate');
    }

    public function addSortColumn(string $colName, string $order) : void
    {
        $this->sortColumns[$colName] = $order;
    }

    public function hasSortColumn(string $colName) : bool
    {
        return isset($this->sortColumns[$colName]);
    }

    public function getSortOrder(string $colName) : string
    {
        if (isset($this->sortColumns[$colName])) {
            return $this->sortColumns[$colName];
        }

        if (isset($_GET[$this->sortParamName]) &&
            $_GET[$this->sortParamName] == $colName &&
            isset($_GET[$this->orderParamName])){
                return $_GET[$this->orderParamName];
        }
        return -1;
    }

    public function buildLink(array $addParams=[]) : string
    {

        $params = array_merge($this->linkParams, $addParams);

        if ($builder = $this->linkBuilder) {
            return $builder($this, $params);
        }

        $allParams = array_merge($_GET, $params);

        $parsed = parse_url($_SERVER['REQUEST_URI']);

        return '/' . trim($parsed['path'],'/') . '?'.http_build_query($allParams);
    }

    public function getLinkBuilder() : ?callable
    {
        return $this->linkBuilder;
    }

    public function setLinkBuilder(?callable $builder) : static
    {
        $this->linkBuilder = $builder;
        return $this;
    }

    public function getSortParamName() : string
    {
        return $this->sortParamName;
    }

    public function setSortParamName(string $name) : static
    {
        $this->sortParamName = $name;
        return $this;
    }

    public function getOrderParamName() : string
    {
        return $this->orderParamName;
    }

    public function setOrderParamName(string $name) : static
    {
        $this->orderParamName = $name;
        return $this;
    }

    public function getAscName() : string
    {
        return $this->ascName;
    }

    public function setAscName(string $name) : static
    {
        $this->ascName = $name;
        return $this;
    }

    public function getDescName() : string
    {
        return $this->descName;
    }

    public function setDescName(string $name) : static
    {
        $this->descName = $name;
        return $this;
    }

    public function getLinkParams() : array
    {
        return $this->linkParams;
    }

    public function setLinkParams(array $params) : static
    {
        $this->linkParams = [];
        foreach($params as $name=>$value){
            $this->linkParams[$name] = $value;
        }
        return $this;
    }

    public function __get(string $name) : mixed
    {
        return $this->{"get$name"}();
    }

    public function __set($name, $value) : void
    {
        $this->{"set$name"}($value);
    }

    public function __isset(string $name) : bool
    {
        return method_exists($this, 'get'.ucfirst($name));
    }

    public function current() : mixed
    {
        $current = $this->srcIterator->current();
        $this->columns->setSrc($current);
        return $current;
    }

    public function key() : int
    {
        return $this->iteratorPos;
    }

    public function next() : void
    {
        $this->iteratorPos++;
        $this->srcIterator->next();
    }

    public function rewind() : void
    {
        $this->srcIterator = $this->createSrcIterator($this->src);
        $this->srcIterator->rewind();
        $this->iteratorPos = 0;
    }

    public function count() : int
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

    public function valid() : bool
    {
        return $this->srcIterator->valid();
    }
}
