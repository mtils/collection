<?php

require_once dirname(__FILE__).'/lib/AutoLoader.php';

use Collection\Map;
use Collection\Map\Extractor;
use Collection\Map\ProxyExtractor;
use Collection\Iterator\CastableIterator;
use Collection\ColumnList;

use Collection\Column;

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
    ->push(Column::create()->setAccessor('getId()')->setTitle('#'))
    ->push(Column::create()->setAccessor('getName()')->setTitle('First name'))
    ->push(Column::create()->setAccessor('getAge()')->setTitle('Age'));

$columns->setSrc($objectTestData[0]);

echo "Single Object:";
foreach($columns as $col){
    echo "\n$col->title:$col->value";
}

echo "\n\nSingle Array instantiated by numeric array:";
$columns = ColumnList::fromArray(array('name','age'))->setSrc($arrayTestData[0]);

foreach($columns as $col){
    echo "\n$col->title:$col->value";
}

echo "\n\nSingle Array instantiated by assoc array:";
$columns = ColumnList::fromArray(array('name'=>'The name','age'=>'How old?'))
                       ->setSrc($arrayTestData[1]);

foreach($columns as $col){
    echo "\n$col->title:$col->value";
}

$headers = array('id'=>'Number','name'=>'The name','age'=>'How old?');

$ex = new ProxyExtractor('getId()','getName()');
$ex->setColumns($headers);

$map = new Map($objectTestData, $ex);

echo "\n\nMultiple Objects instantiated by assoc array:";
foreach($map as $item){
    foreach($item->getColumns() as $col){
        echo "\n$col->title: $col->value";
    }
    echo "\n------------------------------------";
}

echo "\n";