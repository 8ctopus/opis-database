<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Database\SQL;

use Closure;
use Opis\Database\Connection;
use Opis\Database\ResultSet;

class Query extends BaseStatement
{
    /** @var Connection */
    protected $connection;

    /** @var array */
    protected $tables;

    /**
     * Query constructor.
     *
     * @param Connection        $connection
     * @param                   $tables
     * @param null|SQLStatement $statement
     */
    public function __construct(Connection $connection, $tables, SQLStatement $statement = null)
    {
        parent::__construct($statement);
        $this->tables = $tables;
        $this->connection = $connection;
    }

    /**
     * @param bool $value (optional)
     *
     * @return Select|SelectStatement
     */
    public function distinct($value = true)
    {
        return $this->buildSelect()->distinct($value);
    }

    /**
     * @param array|Closure|Expression|string $columns
     *
     * @return Select
     */
    public function groupBy($columns)
    {
        return $this->buildSelect()->groupBy($columns);
    }

    /**
     * @param string  $column
     * @param Closure $value  (optional)
     *
     * @return Select
     */
    public function having($column, Closure $value = null)
    {
        return $this->buildSelect()->having($column, $value);
    }

    /**
     * @param string  $column
     * @param Closure $value  (optional)
     *
     * @return Select
     */
    public function andHaving($column, Closure $value = null)
    {
        return $this->buildSelect()->andHaving($column, $value);
    }

    /**
     * @param Closure|Expression|string $column
     * @param Closure                   $value  (optional)
     *
     * @return Select
     */
    public function orHaving($column, Closure $value = null)
    {
        return $this->buildSelect()->orHaving($column, $value);
    }

    /**
     * @param array|Closure|Expression|string $columns
     * @param string                          $order   (optional)
     * @param string                          $nulls   (optional)
     *
     * @return Select|SelectStatement
     */
    public function orderBy($columns, $order = 'ASC', $nulls = null)
    {
        return $this->buildSelect()->orderBy($columns, $order, $nulls);
    }

    /**
     * @param int $value
     *
     * @return Select|SelectStatement
     */
    public function limit($value)
    {
        return $this->buildSelect()->limit($value);
    }

    /**
     * @param int $value
     *
     * @return Select|SelectStatement
     */
    public function offset($value)
    {
        return $this->buildSelect()->offset($value);
    }

    /**
     * @param string $table
     * @param string $database (optional)
     *
     * @return Select|SelectStatement
     */
    public function into($table, $database = null)
    {
        return $this->buildSelect()->into($table, $database);
    }

    /**
     * @param array $columns (optional)
     *
     * @return ResultSet
     */
    public function select($columns = [])
    {
        return $this->buildSelect()->select($columns);
    }

    /**
     * @param Closure|Expression|string $name
     *
     * @return false|mixed
     */
    public function column($name)
    {
        return $this->buildSelect()->column($name);
    }

    /**
     * @param Closure|Expression|string $column   (optional)
     * @param bool                      $distinct (optional)
     *
     * @return int
     */
    public function count($column = '*', $distinct = false)
    {
        return $this->buildSelect()->count($column, $distinct);
    }

    /**
     * @param Closure|Expression|string $column
     * @param bool                      $distinct (optional)
     *
     * @return float|int
     */
    public function avg($column, $distinct = false)
    {
        return $this->buildSelect()->avg($column, $distinct);
    }

    /**
     * @param Closure|Expression|string $column
     * @param bool                      $distinct (optional)
     *
     * @return float|int
     */
    public function sum($column, $distinct = false)
    {
        return $this->buildSelect()->sum($column, $distinct);
    }

    /**
     * @param Closure|Expression|string $column
     * @param bool                      $distinct (optional)
     *
     * @return float|int
     */
    public function min($column, $distinct = false)
    {
        return $this->buildSelect()->min($column, $distinct);
    }

    /**
     * @param Closure|Expression|string $column
     * @param bool                      $distinct (optional)
     *
     * @return float|int
     */
    public function max($column, $distinct = false)
    {
        return $this->buildSelect()->max($column, $distinct);
    }

    /**
     * @param string[] $tables (optional)
     *
     * @return int
     */
    public function delete($tables = [])
    {
        return $this->buildDelete()->delete($tables);
    }

    /**
     * @return Select
     */
    protected function buildSelect() : Select
    {
        return new Select($this->connection, $this->tables, $this->sql);
    }

    /**
     * @return Delete
     */
    protected function buildDelete() : Delete
    {
        return new Delete($this->connection, $this->tables, $this->sql);
    }
}
