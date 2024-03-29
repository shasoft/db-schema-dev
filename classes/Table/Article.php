<?php

namespace Shasoft\DbSchemaDev\Table;

use Shasoft\DbSchema\Command\Drop;
use Shasoft\DbSchema\Command\Name;
use Shasoft\DbSchemaDev\Table\User;
use Shasoft\DbSchema\Column\ColumnId;
use Shasoft\DbSchema\Command\Columns;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Command\MaxValue;
use Shasoft\DbSchema\Command\MinValue;
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
use Shasoft\DbSchema\Relation\RelationManyToOne;
use Shasoft\DbSchema\Relation\RelationOneToMany;

//#[Migration('2010-01-18T12:00:00+03:00')]
#[Title('Статьи')]
class Article
{
    //
    #[Title('Идентификатор')]
    protected ColumnId $id;
    #[Title('Ссылка на автора')]
    #[ReferenceTo(User::class, 'id')]
    protected Reference $userId;
    #[Title('Название')]
    #[MinLength(8)]
    #[MaxLength(64)]
    #[NumberOfSpaces(6)]
    protected ColumnString $title;
    #[Title('Дата создания')]
    protected ColumnDatetime $createAt;
    #[Title('Рейтинг')]
    #[MinValue(0)]
    #[MaxValue(1000000)]
    protected ColumnInteger $rate;
    #[Columns('id')]
    protected IndexPrimary $pkId;
    // Отношение
    #[RelTableTo(User::class)]
    #[RelNameTo('articles')]
    #[Columns(['userId' => 'id'])]
    #[Title('Автор')]
    protected RelationManyToOne $author;
}
