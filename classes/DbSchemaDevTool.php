<?php

namespace Shasoft\DbSchemaDev;

use Shasoft\DbTool\DbToolPdo;
use Shasoft\DbSchema\Command\PdoParam;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\State\StateDatabase;
use Shasoft\DbSchema\Column\ColumnInteger;

class DbSchemaDevTool
{
    // Сгенерировать заданное количество строк
    public function seeder(\PDO $pdo, StateDatabase $database, int $count = 1, int $procNULL = 10): array
    {
        $ret = [];
        // Первичная генерация
        foreach ($database->tables() as $table) {
            $ret[$table->name()] = $table->seeder($count, $procNULL);
        }
        // Корректировка в соответствии с отношениями
        foreach ($database->tables() as $table) {
            foreach ($table->relations() as $relation) {
                if ($relation->has(Origin::class)) {
                    // Список значений
                    $rowsTo = $ret[$relation->to()->table()->name()];
                    $rowsFrom = $ret[$relation->from()->table()->name()];
                    if ($relation->from()->one()) {
                        // Одно значение
                        foreach ($rowsFrom as &$rowFrom) {
                            $index = ColumnInteger::random(0, count($rowsTo) - 1);
                            foreach ($relation->on() as $from => $to) {
                                $rowFrom[$from] = $rowsTo[$index][$to];
                            }
                            // Удалить выбранный
                            unset($rowsTo[$index]);
                            // Если значения закончились
                            if (empty($rowsTo)) {
                                // то выходим
                                break;
                            }
                            // Перенумеровать
                            $rowsTo = array_values($rowsTo);
                        }
                    } else {
                        // Много значений
                        foreach ($rowsFrom as &$rowFrom) {
                            $index = ColumnInteger::random(0, count($rowsTo));
                            foreach ($relation->on() as $from => $to) {
                                $rowFrom[$from] = $rowsTo[$index][$to];
                            }
                        }
                    }
                    $ret[$relation->from()->table()->name()] = $rowsFrom;
                }
            }
        }
        // Добавить данные в БД
        foreach ($ret as $tableClass => $rows) {
            self::insert($pdo, $database->table($tableClass), $rows);
        }
        return $ret;
    }
    // Добавить данные в таблицу
    static public function insert(\PDO $pdo, StateTable $table, array $rows): int
    {
        if (!empty($rows)) {
            // Если это одна строка
            if (!array_key_exists(0, $rows)) {
                // то преобразовать в массив строк
                $rows = [$rows];
            }
            // INSERT INTO tableName (col1, col2, ...colN) VALUES (val1, val2, ...valN)
            $rowsValues = [];
            foreach ($rows as $row) {
                //
                $values = [];
                foreach ($row as $name => $value) {
                    $values[$name] = $table->column($name)->input($value);
                }
                $rowsValues[] = $values;
            }
            // Типы
            $types = [];
            foreach ($rows[0] as $name => $value) {
                $types[$name] = $table->column($name)->value(PdoParam::class, \PDO::PARAM_STR);
            }
            //
            $ret = 0;
            // А есть вообще данные для вставки?
            if (!empty($rows)) {
                // Если это одна строка
                if (!array_key_exists(0, $rows)) {
                    // то преобразовать в массив строк
                    $rows = [$rows];
                }
                //
                $columns = [];
                $params = [];
                foreach ($row as $name => $value) {
                    $columns[] = DbToolPdo::quote($pdo, $name);
                    $params[] = ':' . $name;
                }
                //
                $queryInsert = $pdo->prepare(
                    'INSERT INTO ' .
                        DbToolPdo::quote($pdo, $table->tabname()) .
                        ' (' .
                        implode(',', $columns) .
                        ') VALUES (' . implode(',', $params) . ')'
                );
                // INSERT INTO tableName (col1, col2, ...colN) VALUES (val1, val2, ...valN)
                foreach ($rows as $row) {
                    // Привязать параметры
                    foreach ($row as $key => $value) {
                        $queryInsert->bindValue(
                            $key,
                            $table->column($key)->input($value),
                            $types[$key]
                        );
                    }
                    if ($queryInsert->execute()) {
                        $ret += $queryInsert->rowCount();
                    }
                }
            }
            // Вернуть количество добавленных строк
            return $ret;
        }
        return 0;
    }
};
