<?php

require_once dirname(__FILE__).'/lib/AutoLoader.php';

use Collection\Iterator\ArrayIterator;

$test = array(2=>'Apple',1=>'Banana',0=>'Grape',13=>'Kiwi');

$it = new ArrayIterator($test);

echo "Plain ArrayIterator:";
foreach($it as $key=>$value){
    echo "$key => $value\n";
}

echo "First:".$it->first();
echo "\nLast:".$it->last();

echo "\n\nList:\n";
$list = $it->toList();
foreach($list as $key=>$value){
    echo "$key => $value\n";
}

echo "First:".$list->getIterator()->first();
echo "\nLast:".$list->getIterator()->last();

echo "\n\nDictionary:\n";
$dict = $it->toDictionary();
foreach($dict as $key=>$value){
    echo "$key => $value\n";
}
echo "First:".$dict->getIterator()->first();
echo "\nLast:".$dict->getIterator()->last();


echo "\n";