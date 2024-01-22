<?php

namespace Shasoft\DbSchemaDev\Table;

use Shasoft\DbSchema\Command\Migration;
use Shasoft\DbSchema\Command\Title;
use Shasoft\DbSchema\Command\ReferenceTo;
use Shasoft\DbSchema\Reference\Reference;
use Shasoft\DbSchema\Column\ColumnInteger;


#[Title('Тестовая таблица 1')]
class TabReference1
{
    #[Title('Идентификатор')]
    protected ColumnInteger $id;
    #[ReferenceTo(self::class, 'refId')]
    protected Reference $refId2;
    #[ReferenceTo(self::class, 'id')]
    #[Migration('2011-11-11T12:00:00+03:00')]
    #[Title('Ссылка на поле')]
    protected Reference $refId;
}
