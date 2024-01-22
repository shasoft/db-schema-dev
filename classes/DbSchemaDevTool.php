<?php

namespace Shasoft\DbSchemaDev;

use Shasoft\DbTool\DbToolPdo;
use Shasoft\DbSchema\Command\Type;
use Shasoft\DbTool\DbToolSqlFormat;
use Shasoft\DbSchema\Command\Origin;
use Shasoft\DbSchema\Command\PdoParam;
use Shasoft\DbSchema\State\StateTable;
use Shasoft\DbSchema\DbSchemaMigrations;
use Shasoft\DbSchema\Index\IndexPrimary;
use Shasoft\DbSchema\State\StateDatabase;
use Shasoft\DbSchema\Column\ColumnInteger;

class DbSchemaDevTool
{
    // Сгенерировать заданное количество строк
    static public function seeder(\PDO $pdo, StateDatabase $database, int $count = 1, int $procNULL = 10): array
    {
        $ret = [];
        // Первичная генерация
        foreach ($database->tables() as $table) {
            $rows = [];
            foreach (self::seederRow($table, $count, $procNULL) as $row) {
                $rows[] = self::insertRow($pdo, $table, $row);
            }
            $ret[$table->name()] = $rows;
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
                                // Изменить значение в БД
                                self::updateRow($pdo, $relation->from()->table(), $rowFrom, [$from]);
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
                                // Изменить значение в БД
                                self::updateRow($pdo, $relation->from()->table(), $rowFrom, [$from]);
                            }
                        }
                    }
                    $ret[$relation->from()->table()->name()] = $rowsFrom;
                }
            }
        }
        return $ret;
    }
    // Сгенерировать заданное количество строк
    static public function seederRow(StateTable $table, int $count = 1, int $procNULL = 10): array
    {
        $ret = [];
        // Сгенерировать все строки
        while (count($ret) < $count) {
            // Сгенерировать строку
            $row = [];
            foreach ($table->columns() as $name => $column) {
                if (!$column->hasAutoIncrement()) {
                    // Сгенерировать значение
                    $row[$name] = $column->seeder($procNULL);
                }
            }
            //
            $ret[] = $row;
        }
        return $ret;
    }
    // Изменить данные в таблице
    static public function updateRow(\PDO $pdo, StateTable $table, array $row, array $columns): int
    {
        // Обновить значение строки в БД
        foreach ($table->indexes() as $index) {
            if ($index->value(Type::class) == IndexPrimary::class) {
                // Значения
                $set = [];
                foreach ($columns as $columnName) {
                    //
                    $column = $table->column($columnName);
                    //
                    $set[] = DbToolPdo::quote($pdo, $column->name()) . '=' . $pdo->quote(
                        $column->input($row[$columnName]),
                        $column->value(PdoParam::class, \PDO::PARAM_STR)
                    );
                }
                // Условия
                $where = [];
                foreach ($index->columns() as $columnName) {
                    //
                    $column = $table->column($columnName);
                    //
                    $where[] = DbToolPdo::quote($pdo, $column->name()) . '=' . $pdo->quote(
                        $row[$columnName],
                        $column->value(PdoParam::class, \PDO::PARAM_STR)
                    );
                }
                // Сформировать SQL запрос
                $sql =
                    'UPDATE ' .
                    DbToolPdo::quote($pdo, $table->tabname()) .
                    ' SET ' . implode(', ', $set) .
                    ' WHERE ' . implode(' AND ', $where);
                // Дальше можно не продолжать
                return $pdo->query($sql)->rowCount();
            }
        }
        return 0;
    }
    // Добавить данные в таблицу и вернуть данные из таблицы БД
    static public function insertRow(\PDO $pdo, StateTable $table, array $row): array|false
    {
        // INSERT INTO tableName (col1, col2, ...colN) VALUES (val1, val2, ...valN)
        $columns = [];
        $values = [];
        $params = [];
        $types = [];
        foreach ($row as $name => $value) {
            $columns[] = DbToolPdo::quote($pdo, $name);
            $values[$name] = $table->column($name)->input($value);
            $params[] = ':' . $name;
            $types[$name] = $table->column($name)->value(PdoParam::class, \PDO::PARAM_STR);
        }
        //
        $sql = 'INSERT INTO ' .
            DbToolPdo::quote($pdo, $table->tabname()) .
            ' (' .
            implode(',', $columns) .
            ') VALUES (' . implode(',', $params) . ')';
        $queryInsert = $pdo->prepare($sql);
        // INSERT INTO tableName (col1, col2, ...colN) VALUES (val1, val2, ...valN)
        // Привязать параметры
        foreach ($row as $key => $value) {
            $queryInsert->bindValue(
                $key,
                $table->column($key)->input($value),
                $types[$key]
            );
        }
        if ($queryInsert->execute()) {
            // Читать значение строки из БД
            foreach ($table->indexes() as $index) {
                if ($index->value(Type::class) == IndexPrimary::class) {
                    $where = [];
                    foreach ($index->columns() as $columnName) {
                        //
                        $column = $table->column($columnName);
                        //
                        if ($column->hasAutoIncrement()) {
                            $value = $column->output($pdo->lastInsertId());
                        } else {
                            $value = $row[$columnName];
                        }
                        //
                        $where[] = DbToolPdo::quote($pdo, $column->name()) . '=' . $pdo->quote(
                            $value,
                            $column->value(PdoParam::class, \PDO::PARAM_STR)
                        );
                    }
                    // Выбрать добавленную строку из БД
                    $sql = 'SELECT * FROM ' . DbToolPdo::quote($pdo, $table->tabname()) . ' WHERE ' . implode(' AND ', $where);
                    $row = [];
                    foreach ($pdo->query($sql)->fetch(\PDO::FETCH_ASSOC) as $key => $value) {
                        $row[$key] = $table->column($key)->output($value);
                    }
                    // Дальше можно не продолжать
                    break;
                }
            }
        }
        // Вернуть количество добавленных строк
        return $row;
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
                $sql = 'INSERT INTO ' .
                    DbToolPdo::quote($pdo, $table->tabname()) .
                    ' (' .
                    implode(',', $columns) .
                    ') VALUES (' . implode(',', $params) . ')';
                $queryInsert = $pdo->prepare($sql);
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
    // Вывод для отладки
    static public function dump(DbSchemaMigrations $migration): void
    {
        echo '<div style="padding:4px;border:solid 1px green">';
        //
        $rows = debug_backtrace();
        echo '<div>file: <strong style="color:green">' . $rows[0]['file'] . ':' . $rows[0]['line'] . '</strong></div>';
        // Вывести
        foreach ($migration->all() as $migrationItem) {
            echo '<div style="padding:4px;border:solid 1px Teal;background-color:LightCyan">';
            echo '<div style="padding:2px;background-color:PaleTurquoise"><strong style="color:DarkCyan">' . $migrationItem['name'] . '</strong></div>';
            foreach ($migrationItem['migrations'] as $tabname => $migration) {
                foreach ($migration['up'] as $sql) {
                    echo DbToolSqlFormat::auto($sql);
                }
            }
            echo '</div>';
        }
        echo '</div>';
    }
};
