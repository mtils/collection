<?php namespace Collection\Table;

use Collection\Column as BaseColumn;
use XType\AbstractType;
use Collection\StringList;
use DomainException;

class Column extends BaseColumn{

    protected ?Table $table;

    /**
     * @var ?callable
     */
    protected mixed $valueFormatter = null;

    protected array $xtype2CssClass = [
        1 => 'custom',
        2 => 'number',
        3 => 'string',
        4 => 'bool',
        5 => 'complex',
        6 => 'temporal',
        7 => 'mixed'
    ];

    /**
    * @brief CSS Classes
    * @var ?StringList
    */
    protected ?StringList $cssClasses=null;

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


    public function getTable() : ?Table
    {
        return $this->table;
    }

    public function _setTable(Table $table) : static
    {
        $this->table = $table;
        return $this;
    }

    public function isSortColumn() : bool
    {
        return $this->table->hasSortColumn($this->accessor);
    }

    /**
     * @brief For __get
     **/
    public function getSorted() : bool
    {
        return $this->isSortColumn();
    }

    public function getSortOrder() : int
    {
        return $this->table->getSortOrder($this->accessor);
    }

    public function getSortHref() : string
    {

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

    public function getValueFormatter() : ?callable
    {
        return $this->valueFormatter;
    }

    public function setValueFormatter(callable $formatter) : static
    {
        $this->valueFormatter = $formatter;
        return $this;
    }

    public function getValue() : mixed
    {
        $value = parent::getValue();
        if($this->valueFormatter){
            $formatter = $this->valueFormatter;
            return $formatter($value);
        }
        return $value;
    }
}