<?php

namespace Shasoft\DbSchemaDev\Table;

use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Command\Create;
use Shasoft\DbSchema\Index\IndexKey;
use Shasoft\DbSchema\Column\ColumnId;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Column\ColumnReal;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Column\ColumnBoolean;
use Shasoft\DbSchema\Command\DefaultValue;

#[Title('Таблица с изменениями')]
#[Migration('2011-11-11T12:00:00+03:00')]
#[Title('Таблица для тестов изменений')]
class AllMigrations
{
    // Колонка
    #[Title('Идентификатор')]
    protected ColumnId $id;
    // Колонка
    #[Migration('2012-12-12T12:00:00+03:00')]
    #[DefaultValue('Имя')]
    #[Migration('2013-12-14T12:00:00+03:00')]
    #[DefaultValue]
    #[Title('Колонка с именем')]
    #[Migration('2017-12-12T12:00:00+03:00')]
    #[Name('Imj')]
    #[Migration('2018-12-12T12:00:00+03:00')]
    #[Type(ColumnBoolean::class)]
    #[Name('NAME')]
    #[Migration('2019-12-12T12:00:00+03:00')]
    #[MaxLength(2 ** 16)]
    protected ColumnString $name;
    // Колонка
    #[Migration('2015-12-12T12:00:00+03:00')]
    protected ColumnReal $rost;
    //
    #[Columns('id')]
    protected IndexPrimary $pkKey;
    // Индекс
    #[Columns('id')]
    #[Migration('2011-11-11T12:00:00+03:00')]
    #[Drop]
    #[Migration('2015-12-12T12:00:00+03:00')]
    #[Create]
    #[Columns('id', 'name')]
    #[Migration('2016-12-12T12:00:00+03:00')]
    #[Columns('id', 'rost')]
    protected IndexKey $testIdx;
}
