<?php

require_once dirname(__FILE__).'/lib/AutoLoader.php';

use Collection\Map;
use Collection\Map\Extractor;
use Collection\Map\ProxyExtractor;
use Collection\Iterator\CastableIterator;

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

$map = new Map($arrayTestData, array('name','age'));

echo "Array Test Data:";
foreach(array('Peter','Monika','Paul','Mary','Angela') as $name){
    echo "\n$name: {$map[$name]}";
}

echo "\n\nObject Test Data:";
$map = new Map($objectTestData, array('name','age'));
foreach(array('Peter','Monika','Paul','Mary','Angela') as $name){
    echo "\n$name: {$map[$name]}";
}

echo "\n\nObject Test Data with id,name:";
$map = new Map($objectTestData, array('id','name'));
foreach(array('#1','#2','#3','#4','#5') as $id){
    echo "\n$id: {$map[$id]}";
}

echo "\n\nMap->getIterator():";
foreach($map as $key=>$value){
    echo "\n$key => $value";
}

$map = new Map($objectTestData, new ProxyExtractor('getName()','getAge()'));

echo "\n\nProxyExtractor('getName()','getAge()'):";
foreach($map as $key=>$value){
    $values = array();
    $values[] = $value->id;
    $values[] = $value->name;
    $values[] = $value->age;

    echo "\n$key => $value (" . implode(', ',$values) . ')';
}

$simpleData = array('Tina','Jessie','Christine');

echo "\n\nSimple array:";
$it = new CastableIterator($simpleData, new Extractor());
foreach($it as $key=>$val){
    echo "\n$key => $val";
}

$assocData = array('#1'=>'Tina','#2'=>'Jessie','#3'=>'Christine');

echo "\n\nAssoc array:";
$it = new CastableIterator($assocData, new Extractor());
foreach($it as $key=>$val){
    echo "\n$key => $val";
}

echo "\n\nSimple array position, position:";
$it = new CastableIterator($simpleData, new Extractor(Extractor::POSITION,
                                                      Extractor::POSITION));
foreach($it as $key=>$val){
    echo "\n$key => $val";
}

echo "\n\nSimple array value, key:";
$it = new CastableIterator($simpleData, new Extractor(Extractor::VALUE,
                                                      Extractor::KEY));
foreach($it as $key=>$val){
    echo "\n$key => $val";
}

echo "\n";