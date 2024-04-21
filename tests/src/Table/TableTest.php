<?php
/**
 *
 * Created by mtils on 21.04.2024 at 17:17.
 **/

namespace Collection\Test\Table;


use ArrayObject;
use Collection\ColumnList;
use Collection\StringList;
use Collection\Table\Column;
use Collection\Table\Table;
use PHPUnit\Framework\TestCase;
use UnderflowException;

class TableTest extends TestCase
{
    public function testSetAndGetColumns()
    {
        $table = new Table();
        $columns = new ColumnList();
        $column1 = new Column();
        $column1->setAccessor('name');
        $columns->append($column1);

        $table->setColumns($columns);
        $this->assertSame($columns, $table->getColumns());
    }

    public function testIteration()
    {
        $table = new Table();
        $data = new ArrayObject(
            [['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Jane']]
        );
        $table->setSrc($data);

        $columns = new ColumnList();
        $column1 = new Column();
        $column1->setAccessor('name');
        $columns->append($column1);
        $table->setColumns($columns);

        $result = [];
        foreach ($table as $item) {
            $result[] = $item['name'];
        }

        $this->assertEquals(['John', 'Jane'], $result);
    }

    public function testCountable()
    {
        $table = new Table();
        $data = [1, 2, 3, 4, 5];
        $table->setSrc($data);
        $this->assertCount(5, $table);
    }

    public function testSorting()
    {
        $table = new Table();
        $table->addSortColumn('name', 1);
        $this->assertTrue($table->hasSortColumn('name'));
        $this->assertEquals(1, $table->getSortOrder('name'));
    }

    public function testCssClasses()
    {
        $table = new Table();
        $data = new class {
            public $className = 'test-class';
        };
        $table->setSrc([$data]);
        $table->setItemClass('custom-class');

        $cssClasses = $table->getCssClasses();
        $this->assertInstanceOf(StringList::class, $cssClasses);
        $this->assertTrue($cssClasses->contains('custom-class'));
    }

    public function testLinkBuilding()
    {
        $table = new Table();
        $table->setLinkBuilder(function ($table, $params) {
            return 'https://example.com?' . http_build_query($params);
        });
        $link = $table->buildLink(['page' => 1]);
        $this->assertEquals('https://example.com?page=1', $link);
    }
}
