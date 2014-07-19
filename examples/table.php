<?php

require_once dirname(__FILE__).'/lib/AutoLoader.php';

use Collection\Map;
use Collection\Map\Extractor;
use Collection\Map\ProxyExtractor;
use Collection\Iterator\CastableIterator;
use Collection\ColumnList;

use Collection\Table\Column;
use Collection\Table\Table;

class Person{

    public $id;
    public $name;
    public $age;

    public function __construct($id, $name, $age){
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
    }

    public function getId(){
        return $this->id;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function getAge(){
        return $this->age;
    }
    
}

$arrayTestData = array(
    array('name'=>'Peter','age'=>35),
    array('name'=>'Monika','age'=>12),
    array('name'=>'Paul','age'=>15),
    array('name'=>'Mary','age'=>76),
    array('name'=>'Angela','age'=>2),
);

$objectTestData = array();
$i = 1;
foreach($arrayTestData as $personData){
    $objectTestData[] = new Person("#$i",$personData['name'],
                                   $personData['age']);
    $i++;
}

$columns = ColumnList::create()
    ->push(Column::create()->setAccessor('id')->setTitle('#'))
    ->push(Column::create()->setAccessor('name')->setTitle('First name'))
    ->push(Column::create()->setAccessor('age')->setTitle('Age'));

// $columns->setSrc($objectTestData[0]);

$table = new Table();
$table->setColumns($columns);
$table->setSrc($objectTestData);

echo "Just Columns:";
foreach($table->columns as $col){
    echo "\n$col->name:$col->title";
}

echo "\n\nIterating over result";
foreach($table as $idx=>$row){
    echo "\nRow #$idx --------------------------";
    foreach($table->columns as $col){
        echo "\n$col->name ($col->title): $col->value";
    }
}

echo "\n\nValue Formatting";
$formatter = function($value){
    if(is_numeric($value)){
        return number_format($value,2);
    }
    return $value;
};
foreach($table->columns as $col){
    $col->setValueFormatter($formatter);
}

foreach($table as $idx=>$row){
    echo "\nRow #$idx --------------------------";
    foreach($table->columns as $col){
        echo "\n$col->name ($col->title): $col->value";
    }
}

$table->addSortColumn('age','desc');

echo "\n\nSorting";
foreach($table->columns as $col){
    echo "\n$col->name: sorted:".var_export($col->sorted, true) . " " . $col->sortOrder;
}


$table->setLinkBuilder(function($table, $params){
    return "http://localhost?" . http_build_query($params);
});

$table->addSortColumn('name','asc');

echo "\n\nLinks";
foreach($table->columns as $col){
    echo "\n$col->name: $col->sortHref";
}

echo "\n\nForwarded Link Params";
$table->setLinkParams(array('page'=>2,'name'=>'Monika'));
foreach($table->columns as $col){
    echo "\n$col->name: $col->sortHref";
}

echo "\n";