<?php

namespace Shasoft\DbSchemaDev\Table;

use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbSchema\Column\ColumnId;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
use Shasoft\DbSchema\Column\ColumnText;
use Shasoft\DbSchema\Command\LimitText;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Command\MinLength;
use Shasoft\DbSchema\Tests\Table\User0;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Column\ColumnDatetime;
use Shasoft\DbSchema\Column\RelationOneToMany;

//#[Migration('2010-01-18T12:00:00+03:00')]
#[Title('Пользователи')]
class User
{
    //
    #[Title('Идентификатор')]
    protected ColumnId $id;
    #[Title('Имя')]
    #[MinLength(4)]
    #[MaxLength(8)]
    protected ColumnString $name;
    #[Title('Возраст')]
    #[MinValue(1)]
    #[MaxValue(100)]
    protected ColumnInteger $age;
    #[Title('Роль')]
    #[MinLength(4)]
    #[MaxLength(12)]
    protected ColumnString $role;
    #[Columns('id')]
    protected IndexPrimary $pkId;
}
