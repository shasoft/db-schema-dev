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
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Column\ColumnReal;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Column\ColumnBoolean;
use Shasoft\DbSchema\Command\DefaultValue;

// Комментарий таблицы
#[Title('Таблица для примера')]
class TabExample1
{
    #[Title('Идентификатор')]
    protected ColumnId $id;
    #[Title('Имя')]
    protected ColumnString $name;
    #[Migration('2023-12-28T22:00:00+03:00')]
    #[Title('Фамилия')]
    protected ColumnString $fam;
    // Первичный ключ
    #[Columns('id')]
    protected IndexPrimary $pkKey;
}
// Комментарий таблицы при создании
#[Title('Таблица для примера')]
// Изменим комментарий таблицы
#[Migration('2001-01-01T00:00:00+03:00')]
#[Title('Таблица-пример')]
class TabExample2
{
    //-- Колонка добавляется вместе с созданием таблицы
    #[Title('Идентификатор')]
    protected ColumnId $id;
    //-- Добавим колонку
    #[Migration('2002-01-01T00:00:00+03:00')]
    // Установим максимальную длину
    #[MaxLength(2 ** 16)]
    // Установим значение по умолчанию
    #[DefaultValue('Имя')]
    //-- Изменим колонку
    #[Migration('2003-01-01T00:00:00+03:00')]
    //-- Добавим комментарий    
    #[Title('Колонка с именем')]
    //-- Изменим колонку
    #[Migration('2004-01-01T00:00:00+03:00')]
    // Изменим имя колонки    
    #[Name('firstName')]
    //-- Изменим колонку
    #[Migration('2005-01-01T00:00:00+03:00')]
    // Изменим тип колонки
    #[Type(ColumnBoolean::class)]
    // Изменим тип колонки
    #[Name('hasMale')]
    protected ColumnString $name;
    // Первичный ключ
    #[Columns('id')]
    protected IndexPrimary $pkKey;
}
