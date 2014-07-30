<?php namespace Collection\Table;

use Collection\Column as BaseColumn;
use XType\AbstractType;
use Collection\StringList;
use DomainException;

class Column extends BaseColumn{

    protected $table;

    protected $valueFormatter;

    protected $xtype2CssClass = array(
        1 => 'custom',
        2 => 'number',
        3 => 'string',
        4 => 'bool',
        5 => 'complex',
        6 => 'temporal',
        7 => 'mixed'
    );

    /**
    * @brief CSS Classes
    * @var StringList
    */
    protected $cssClasses;

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
        if($this->valueFormatter instanceof AbstractType){
            if(isset($this->xtype2CssClass[$this->valueFormatter->getGroup()])){
                $classes->append($this->xtype2CssClass[$this->valueFormatter->getGroup()]);
            }
        }
        if($acc = $this->getAccessor()){
            $search = array('(',')','->','[',"'",'"');
            $replace = array('','','','',"",'');
            $cleaned = str_replace($search, $replace, $acc);
            $classes->append($acc);
        }
        return $classes;
    }


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