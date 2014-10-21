<?php namespace Collection;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class ColumnList  implements Countable, IteratorAggregate{

    protected $_columns = array();

    public $_src;

    protected $valueAccessor;

    public function columns(){
        return $this->_columns;
    }

    public function append(Column $column){
        $column->setColumnList($this);
        $this->_columns[] = $column;
        return $this;
    }

    public function push(Column $column){
        return $this->append($column);
    }

    public function indexOf($column){
        $count = $this->count();
        $found = FALSE;
        for($i=0; $i<$count; $i++){
            if($column === $this->_array[$i]){
                return $i;
            }
        }
        throw new LogicException("Value $column not found");
    }

    public function count(){
        return count($this->_columns);
    }

    public function getIterator(){
        return new ArrayIterator($this->_columns);
    }

    public function getSrc(){
        return $this->_src;
    }

    public function setSrc($src){
        $this->_src = $src;
        return $this;
    }

    public static function create(){
        $class = get_called_class();
        return new $class();
    }

    public static function fromArray($array){

        $list = static::create();

        // Numeric array
        if(isset($array[0])){
            foreach($array as $accessor){
                $list->append(Column::create()->setAccessor($accessor));
            }
        }
        // Assoc array
        else{
            foreach($array as $accessor=>$title){
                $list->append(Column::create()
                                      ->setAccessor($accessor)
                                      ->setTitle($title));
            }
        }
        return $list;
    }
}