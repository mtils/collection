<?php namespace Collection\Table;

use Collection\Column as BaseColumn;
use DomainException;

class Column extends BaseColumn{

    protected $table;

    protected $valueFormatter;


    public function getTable(){
        return $this->table;
    }

    public function _setTable(Table $table){
        $this->table = $table;
        return $this;
    }

    public function isSortColumn(){
        return $this->table->hasSortColumn($this->accessor);
    }

    /**
     * @brief For __get
     **/
    public function getSorted(){
        return $this->isSortColumn();
    }

    public function getSortOrder(){
        return $this->table->getSortOrder($this->accessor);
    }

    public function getSortHref(){

        $ascName = $this->table->getAscName();
        $descName = $this->table->getDescName();
        $sortParamName = $this->table->getSortParamName();
        $orderParamName = $this->table->getOrderParamName();

        if($this->getSortOrder() == $ascName){
            $newOrder = $descName;
        }
        else{
            $newOrder = $ascName;
        }

        return $this->table->buildLink(array(
            $sortParamName => $this->accessor,
            $orderParamName => $newOrder
        ));

    }

    public function getValueFormatter(){
        return $this->valueFormatter;
    }

    public function setValueFormatter($formatter){
        if(!is_callable($formatter)){
            throw new DomainException('Formatter has to be callable');
        }
        $this->valueFormatter = $formatter;
        return $this;
    }

    public function getValue(){
        $value = parent::getValue();
        if($this->valueFormatter){
            $formatter = $this->valueFormatter;
            return $formatter($value);
        }
        return $value;
    }
}