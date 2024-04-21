<?php
/**
 *
 * Created by mtils on 21.04.2024 at 13:46.
 **/

namespace Collection\Test;


use Collection\Column;
use Collection\ColumnList;
use LogicException;
use PHPUnit\Framework\TestCase;

class ColumnListTest extends TestCase
{
    protected $columnList;

    protected function setUp(): void
    {
        $this->columnList = new ColumnList();
    }

    public function testAppendAndCountColumns()
    {
        $column1 = Column::create()->setAccessor('name');
        $column2 = Column::create()->setAccessor('email');

        $this->columnList->append($column1);
        $this->columnList->append($column2);

        $this->assertCount(2, $this->columnList);
        $this->assertSame($column1, $this->columnList->columns()[0]);
        $this->assertSame($column2, $this->columnList->columns()[1]);
    }

    public function testIterator()
    {
        $column1 = Column::create()->setAccessor('name');
        $column2 = Column::create()->setAccessor('email');
        $this->columnList->append($column1)->append($column2);

        $count = 0;
        foreach ($this->columnList as $column) {
            $count++;
        }

        $this->assertEquals(2, $count);
    }

    public function testIndexOf()
    {
        $column1 = Column::create()->setAccessor('name');
        $column2 = Column::create()->setAccessor('email');
        $this->columnList->append($column1)->append($column2);

        $index = $this->columnList->indexOf($column1);
        $this->assertEquals(0, $index);

        $this->expectException(LogicException::class);
        $this->columnList->indexOf(
            new Column()
        );  // This should throw an exception
    }

    public function testSetAndGetSrc()
    {
        $data = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $this->columnList->setSrc($data);
        $this->assertEquals($data, $this->columnList->getSrc());
    }

    public function testFromArray()
    {
        $array = [
            'name' => 'Name',
            'email' => 'Email'
        ];

        $list = ColumnList::fromArray($array);
        $this->assertCount(2, $list);
        $columns = $list->columns();

        $this->assertEquals('name', $columns[0]->getAccessor());
        $this->assertEquals('Name', $columns[0]->getTitle());
        $this->assertEquals('email', $columns[1]->getAccessor());
        $this->assertEquals('Email', $columns[1]->getTitle());
    }
}
