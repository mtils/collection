<?php namespace Collection;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;

class ColumnList  implements Countable, IteratorAggregate{

    /**
     * @var Column[]
     */
    protected array $_columns = [];

    public array $_src = [];

    /**
     * @var ?callable
     */
    protected mixed $valueAccessor;

    /**
     * @return Column[]
     */
    public function columns() : array
    {
        return $this->_columns;
    }

    public function append(Column $column) : static
    {
        $column->setColumnList($this);
        $this->_columns[] = $column;
        return $this;
    }

    public function push(Column $column) : static
    {
        return $this->append($column);
    }

    /**
     * @param Column $column
     * @return int
     */
    public function indexOf($column) : int
    {
        $count = $this->count();
        for($i=0; $i<$count; $i++){
            if($column === $this->_columns[$i]){
                return $i;
            }
        }
        throw new LogicException("Value " . $column->getName() . " not found");
    }

    public function count() : int
    {
        return count($this->_columns);
    }

    public function getIterator() : \Iterator
    {
        return new ArrayIterator($this->_columns);
    }

    public function getSrc() : mixed
    {
        return $this->_src;
    }

    public function setSrc(mixed $src) : static
    {
        $this->_src = $src;
        return $this;
    }

    public static function create()
    {
        $class = get_called_class();
        return new $class();
    }

    /**
     * @param Column[]|string[]|array<string,string> $array
     * @return static
     */
    public static function fromArray(array $array) : static
    {

        /** @var ColumnList $list */
        $list = static::create();

        // Numeric array
        if(isset($array[0])){
            foreach($array as $accessor){
                $list->append(Column::create()->setAccessor($accessor));
            }
            return $list;
        }
        // Assoc array
        foreach($array as $accessor=>$title){
            $list->append(Column::create()
                                  ->setAccessor($accessor)
                                  ->setTitle($title));
        }
        return $list;
    }
}