<?php

namespace Shasoft\DbSchemaDev\Table;

use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchemaDev\Table\User;
use Shasoft\DbSchema\Column\ColumnId;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Column\ColumnText;
use Shasoft\DbSchema\Command\MaxLength;
use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Command\MinLength;
use Shasoft\DbSchema\Command\RelNameTo;
use Shasoft\DbSchema\Tests\Table\User0;
use Shasoft\DbSchema\Column\ColumnRefId;
use Shasoft\DbSchema\Command\RelTableTo;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\Column\ColumnString;
use Shasoft\DbSchema\Command\ReferenceTo;
use Shasoft\DbSchema\Reference\Reference;
use Shasoft\DbSchema\Column\ColumnInteger;
use Shasoft\DbSchema\Command\DbSchemaType;
use Shasoft\DbSchema\Column\ColumnDatetime;
use Shasoft\DbSchema\Command\NumberOfSpaces;
use Shasoft\DbSchema\Relation\RelationOneToOne;
use Shasoft\DbSchema\Relation\RelationManyToOne;
use Shasoft\DbSchema\Relation\RelationOneToMany;

//#[Migration('2010-01-18T12:00:00+03:00')]
#[Title('Информация о пользователе')]
class UserInfo
{
    //
    #[Title('Идентификатор')]
    protected ColumnId $id;
    #[Title('Ссылка на пользователя')]
    #[ReferenceTo(User::class, 'id')]
    protected Reference $userId;
    #[Title('Описание')]
    #[MinLength(8)]
    #[MaxLength(128)]
    #[NumberOfSpaces(10)]
    protected ColumnString $description;
    #[Columns('id')]
    protected IndexPrimary $pkId;
    // Отношение
    #[RelTableTo(User::class)]
    #[RelNameTo('userInfo')]
    #[Columns(['userId' => 'id'])]
    #[Title('Пользователь')]
    protected RelationOneToOne $user;
}
