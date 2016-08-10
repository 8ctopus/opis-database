<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013-2015 Marius Sarca
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

namespace Opis\Database;

use PDO;
use PDOException;
use Serializable;

class Connection implements Serializable
{
    /** @var    string  Username */
    protected $username;

    /** @var    string  Password */
    protected $password;

    /** @var    bool    Log queries flag */
    protected $logQueries = false;

    /** @var    array   Logged queries */
    protected $log = array();

    /** @var    array   Init commands */
    protected $commands = array();

    /** @var    array   PDO connection options */
    protected $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    );

    /** @var    \PDO    The PDO object associated with this connection */
    protected $pdo;

    /** @var    SQL\Compiler The compiler associated with this connection */
    protected $compiler;

    /** @var    Schema\Compiler The schema compiler associated with this connection */
    protected $schemaCompiler;

    /** @var    string  The DSN for this connection */
    protected $dsn;

    /** @var    string  Driver's name */
    protected $driver;

    /** @var    Schema   Schema instance */
    protected $schema;

    /** @var    array  Compiler options */
    protected $compilerOptions = array();

    /** @var    array   Schema compiler options */
    protected $schemaCompilerOptions = array();
    
    /**
     * Constructor
     * 
     * @param   string  $dsn        The DSN string
     * @param   string  $username   (optional) Username
     * @param   string  $password   (optional) Password
     * @param   string  $driver     (optional) Driver's name
     * @param   PDO     $pdo        (optional) PDO object
     */
    public function __construct(string $dsn = null, string $username = null, string $password = null, string $driver = null, PDO $pdo = null)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->driver = $driver;
        $this->pdo = $pdo;
    }

    /**
     * @param PDO $pdo
     * @return Connection
     */
    public static function fromPDO(PDO $pdo): self
    {
        return new static(null, null, null, null, $pdo);
    }

    /**
     * Enable or disable query logging
     *
     * @param   bool    $value  (optional) Value
     *
     * @return  Connection
     */
    public function logQueries(bool $value = true): self
    {
        $this->logQueries = $value;
        return $this;
    }

    /**
     * Add an init command
     *
     * @param   string  $query SQL command
     * @param   array   $params (optional) Params
     *
     * @return  Connection
     */
    public function initCommand(string $query, array $params = []): self
    {
        $this->commands[] = array(
            'sql' => $query,
            'params' => $params,
        );

        return $this;
    }

    /**
     * Set the username
     *
     * @param   string  $username   Username
     *
     * @return  Connection
     */
    public function username(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Set the password
     *
     * @param   string  $password   Password
     * 
     * @return  Connection
     */
    public function password(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Set PDO connection options
     *
     * @param   array   $options    PDO options
     * 
     * @return  Connection
     */
    public function options(array $options): self
    {
        foreach ($options as $name => $value) {
            $this->option($name, $value);
        }

        return $this;
    }

    /**
     * Set a PDO connection option
     *
     * @param  mixed $name
     * @param  mixed $value
     * 
     * @return  Connection
     */
    public function option($name, $value): self
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Use persistent connections
     *
     * @param   bool    $value  (optional) Value
     * 
     * @return  Connection
     */
    public function persistent(bool $value = true): self
    {
        return $this->option(PDO::ATTR_PERSISTENT, $value);
    }

    /**
     * Set date format
     *
     * @param   string  $format Date format
     * 
     * @return  Connection
     */
    public function setDateFormat(string $format): self
    {
        $this->compilerOptions['dateFormat'] = $format;
        return $this;
    }

    /**
     * Set identifier wrapper
     *
     * @param   string  $wrapper    Identifier wrapper
     * 
     * @return  Connection
     */
    public function setWrapperFormat(string $wrapper): self
    {
        $this->compilerOptions['wrapper'] = $wrapper;
        $this->schemaCompilerOptions['wrapper'] = $wrapper;
        return $this;
    }

    /**
     * Returns the DSN associated with this connection
     *
     * @return  string
     */
    public function getDSN()
    {
        return $this->dsn;
    }

    /**
     * Returns the driver's name
     *
     * @return  string
     */
    public function getDriver()
    {
        if ($this->driver === null) {
            $this->driver = $this->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
        }

        return $this->driver;
    }

    /**
     * Returns the schema associated with this connection
     *
     * @return  Schema
     */
    public function getSchema(): Schema
    {
        if ($this->schema === null) {
            $this->schema = new Schema($this);
        }

        return $this->schema;
    }

    /**
     * Returns the PDO object associated with this connection
     *
     * @return PDO
     */
    public function getPDO(): PDO
    {
        if ($this->pdo == null) {
            $this->pdo = new PDO($this->getDSN(), $this->username, $this->password, $this->options);

            foreach ($this->commands as $command) {
                $this->command($command['sql'], $command['params']);
            }
        }

        return $this->pdo;
    }

    /**
     * Returns an instance of the compiler associated with this connection
     *
     * @return  SQL\Compiler
     */
    public function getCompiler(): SQL\Compiler
    {
        if ($this->compiler === null) {
            switch ($this->getDriver()) {
                case 'mysql':
                    $this->compiler = new \Opis\Database\SQL\Compiler\MySQL();
                    break;
                case 'dblib':
                case 'mssql':
                case 'sqlsrv':
                case 'sybase':
                    $this->compiler = new \Opis\Database\SQL\Compiler\SQLServer();
                    break;
                case 'oci':
                case 'oracle':
                    $this->compiler = new \Opis\Database\SQL\Compiler\Oracle();
                    break;
                case 'firebird':
                    $this->compiler = new \Opis\Database\SQL\Compiler\Firebird();
                    break;
                case 'db2':
                case 'ibm':
                case 'odbc':
                    $this->compiler = new \Opis\Database\SQL\Compiler\DB2();
                    break;
                case 'nuodb':
                    $this->compiler = new \Opis\Database\SQL\Compiler\NuoDB();
                    break;
                default:
                    $this->compiler = new SQL\Compiler();
            }

            $this->compiler->setOptions($this->compilerOptions);
        }

        return $this->compiler;
    }

    /**
     * Returns an instance of the schema compiler associated with this connection
     *
     * @throws  \Exception
     * 
     * @return  Schema\Compiler
     */
    public function schemaCompiler(): Schema\Compiler
    {
        if ($this->schemaCompiler === null) {
            switch ($this->getDriver()) {
                case 'mysql':
                    $this->schemaCompiler = new \Opis\Database\Schema\Compiler\MySQL($this);
                    break;
                case 'pgsql':
                    $this->schemaCompiler = new \Opis\Database\Schema\Compiler\PostgreSQL($this);
                    break;
                case 'dblib':
                case 'mssql':
                case 'sqlsrv':
                case 'sybase':
                    $this->schemaCompiler = new \Opis\Database\Schema\Compiler\SQLServer($this);
                    break;
                case 'sqlite':
                    $this->schemaCompiler = new \Opis\Database\Schema\Compiler\SQLite($this);
                    break;
                case 'oci':
                case 'oracle':
                    $this->schemaCompiler = new \Opis\Database\Schema\Compiler\Oracle($this);
                    break;
                default:
                    throw new \Exception('Schema not supported yet');
            }

            $this->schemaCompiler->setOptions($this->schemaCompilerOptions);
        }

        return $this->schemaCompiler;
    }

    /**
     * Close the current connection by destroying the associated PDO object
     */
    public function disconnect()
    {
        $this->pdo = null;
    }

    /**
     * Returns the query log for this database.
     * 
     * @return  array
     */
    public function getLog(): array
    {
        return $this->log;
    }

    /**
     * Execute a query
     *
     * @param   string  $sql    SQL Query
     * @param   array   $params (optional) Query params
     *
     * @return  ResultSet
     */
    public function query(string $sql, array $params = [])
    {
        $prepared = $this->prepare($sql, $params);
        $this->execute($prepared);
        return new ResultSet($prepared['statement']);
    }

    /**
     * Execute a non-query SQL command
     *
     * @param   string  $sql    SQL Command
     * @param   array   $params (optional) Command params
     *
     * @return  mixed   Command result
     */
    public function command(string $sql, array $params = [])
    {
        return $this->execute($this->prepare($sql, $params));
    }

    /**
     * Execute a query and return the number of affected rows
     *
     * @param   string  $sql    SQL Query
     * @param   array   $params (optional) Query params
     *
     * @return  int
     */
    public function count($sql, array $params = [])
    {
        $prepared = $this->prepare($sql, $params);
        $this->execute($prepared);
        $result = $prepared['statement']->rowCount();
        $prepared['statement']->closeCursor();
        return $result;
    }

    /**
     * Execute a query and fetch the first column
     *
     * @param   string  $sql    SQL Query
     * @param   array   $params (optional) Query params
     *
     * @return  mixed
     */
    public function column(string $sql, array $params = [])
    {
        $prepared = $this->prepare($sql, $params);
        $this->execute($prepared);
        $result = $prepared['statement']->fetchColumn();
        $prepared['statement']->closeCursor();
        return $result;
    }

    /**
     * Log a query.
     *
     * @param   string  $query  SQL query
     * @param   array   $params Query parameters
     * @param   int|float   $start  Start time in microseconds
     */
    protected function log(string $query, array $params, $start)
    {
        $time = microtime(true) - $start;
        $query = $this->replaceParams($query, $params);
        $this->log[] = compact('query', 'time');
    }

    /**
     * Replace placeholders with parameteters.
     *
     * @param   string  $query  SQL query
     * @param   array   $params Query paramaters
     * 
     * @return  string
     */
    protected function replaceParams(string $query, array $params): string
    {
        $compiler = $this->getCompiler();

        return preg_replace_callback('/\?/', function ($matches) use (&$params, $compiler) {
            $param = array_shift($params);
            $param = is_object($param) ? get_class($param) : $param;

            if (is_int($param) || is_float($param)) {
                return $param;
            } elseif ($param === null) {
                return 'NULL';
            } else {
                return $compiler->quote($param);
            }
        }, $query);
    }

    /**
     * Prepares a query.
     *
     * @param   string  $query  SQL query
     * @param   array   $params Query parameters
     * 
     * @return  array
     */
    protected function prepare(string $query, array $params): array
    {
        try {
            $statement = $this->getPDO()->prepare($query);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage() . ' [ ' . $this->replaceParams($query, $params) . ' ] ', (int) $e->getCode(), $e->getPrevious());
        }

        return array('query' => $query, 'params' => $params, 'statement' => $statement);
    }

    /**
     * Executes a prepared query and returns TRUE on success or FALSE on failure.
     *
     * @param   array   $prepared   Prepared query
     *
     * @return  boolean
     */
    protected function execute(array $prepared)
    {
        if ($this->logQueries) {
            $start = microtime(true);
        }

        try {
            $result = $prepared['statement']->execute($prepared['params']);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage() . ' [ ' . $this->replaceParams($prepared['query'], $prepared['params']) . ' ] ', (int) $e->getCode(), $e->getPrevious());
        }

        if ($this->logQueries) {
            $this->log($prepared['query'], $prepared['params'], $start);
        }

        return $result;
    }

    /**
     * Implementation of Serializable::serialize
     *
     * @return  string
     */
    public function serialize()
    {
        return serialize(array(
            'username' => $this->username,
            'password' => $this->password,
            'logQueries' => $this->logQueries,
            'options' => $this->options,
            'commands' => $this->commands,
            'dsn' => $this->dsn,
        ));
    }

    /**
     * Implementation of Serializable::unserialize
     *
     * @param   string  $data   Serialized data
     */
    public function unserialize($data)
    {
        $object = unserialize($data);

        foreach ($object as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
