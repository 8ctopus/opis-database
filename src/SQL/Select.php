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

class Select extends SelectStatement
{
    /** @var Connection */
    protected $connection;

    /**
     * Select constructor.
     *
     * @param Connection        $connection
     * @param array|string      $tables
     * @param null|SQLStatement $statement
     */
    public function __construct(Connection $connection, $tables, SQLStatement $statement = null)
    {
        parent::__construct($tables, $statement);
        $this->connection = $connection;
    }

    /**
     * @param Closure|Closure[]|Expression|Expression[]|string|string[] $columns (optional)
     *
     * @return ResultSet
     */
    public function select($columns = [])
    {
        parent::select($columns);
        $compiler = $this->connection->getCompiler();
        return $this->connection->query($compiler->select($this->sql), $compiler->getParams());
    }

    /**
     * @param Closure|Expression|string $name
     *
     * @return false|mixed
     */
    public function column($name)
    {
        parent::column($name);
        return $this->getColumnResult();
    }

    /**
     * @param Closure|Closure[]|Expression|Expression[]|string|string[] $column   (optional)
     * @param bool                                                      $distinct (optional)
     *
     * @return int
     */
    public function count($column = '*', bool $distinct = false)
    {
        parent::count($column, $distinct);
        return $this->getColumnResult();
    }

    /**
     * @param Closure|Expression|string $column
     * @param bool                      $distinct (optional)
     *
     * @return float|int
     */
    public function avg($column, bool $distinct = false)
    {
        parent::avg($column, $distinct);
        return $this->getColumnResult();
    }

    /**
     * @param Closure|Expression|string $column
     * @param bool                      $distinct (optional)
     *
     * @return float|int
     */
    public function sum($column, bool $distinct = false)
    {
        parent::sum($column, $distinct);
        return $this->getColumnResult();
    }

    /**
     * @param Closure|Expression|string $column
     * @param bool                      $distinct (optional)
     *
     * @return float|int
     */
    public function min($column, bool $distinct = false)
    {
        parent::min($column, $distinct);
        return $this->getColumnResult();
    }

    /**
     * @param Closure|Expression|string $column
     * @param bool                      $distinct (optional)
     *
     * @return float|int
     */
    public function max($column, bool $distinct = false)
    {
        parent::max($column, $distinct);
        return $this->getColumnResult();
    }

    protected function getColumnResult()
    {
        $compiler = $this->connection->getCompiler();
        return $this->connection->column($compiler->select($this->sql), $compiler->getParams());
    }
}
