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

namespace Opis\Database\Schema;

class CreateTable
{
    
    protected $columns = array();
    
    protected $primaryKey;
    
    protected $uniqueKeys = array();
    
    protected $indexes = array();
    
    protected $foreignKeys = array();
    
    protected $table;
    
    protected $engine;
    
    protected $autoincrement;

    /**
     * CreateTable constructor.
     * @param $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * @param $name
     * @param $type
     * @return CreateColumn
     */
    protected function addColumn($name, $type)
    {
        $column = new CreateColumn($this, $name, $type);
        $this->columns[$name] = $column;
        return $column;
    }
    
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }
    
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return array
     */
    public function getUniqueKeys()
    {
        return $this->uniqueKeys;
    }

    /**
     * @return array
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @return array
     */
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }
    
    public function getEngine()
    {
        return $this->engine;
    }
    
    public function getAutoincrement()
    {
        return $this->autoincrement;
    }

    /**
     * @param $name
     * @return $this
     */
    public function engine($name)
    {
        $this->engine = $name;
        return $this;
    }

    /**
     * @param $name
     * @param null $columns
     * @return $this
     */
    public function primary($name, $columns = null)
    {
        if($columns === null)
        {
            $columns = array($name);
        }
        elseif(!is_array($columns))
        {
            $columns = array($columns);
        }
        
        $this->primaryKey = array(
            'name' => $name,
            'columns' => $columns,
        );
        
        return $this;
    }

    /**
     * @param $name
     * @param null $columns
     * @return $this
     */
    public function unique($name, $columns = null)
    {
        
        if($columns === null)
        {
            $columns = array($name);
        }
        elseif(!is_array($columns))
        {
            $columns = array($columns);
        }
        
        $this->uniqueKeys[$name] = $columns; 
        
        return $this;
    }

    /**
     * @param $name
     * @param null $columns
     * @return $this
     */
    public function index($name, $columns = null)
    {
        if($columns === null)
        {
            $columns = array($name);
        }
        elseif(!is_array($columns))
        {
            $columns = array($columns);
        }
        
        $this->indexes[$name] = $columns;
        
        return $this;
    }

    /**
     * @param $name
     * @param null $columns
     * @return ForeignKey
     */
    public function foreign($name, $columns = null)
    {
        if($columns === null)
        {
            $columns = array($name);
        }
        elseif(!is_array($columns))
        {
            $columns = array($columns);
        }
        
        $foreign = new ForeignKey($columns);
        
        $this->foreignKeys[$name] = $foreign;
        return $foreign;
    }

    /**
     * @param CreateColumn $column
     * @return $this|CreateTable
     */
    public function autoincrement(CreateColumn $column)
    {
        if($column->getType() !== 'integer')
        {
           return $this; 
        }
        
        $this->autoincrement = $column->set('autoincrement', true);
        return $this->primary($column->getName());
    }

    /**
     * @param $name
     * @return CreateColumn
     */
    public function integer($name)
    {
        return $this->addColumn($name, 'integer');
    }

    /**
     * @param $name
     * @return CreateColumn
     */
    public function float($name)
    {
        return $this->addColumn($name, 'float');
    }

    /**
     * @param $name
     * @return CreateColumn
     */
    public function double($name)
    {
        return $this->addColumn($name, 'double');
    }

    /**
     * @param $name
     * @param null $maximum
     * @param null $decimal
     * @return $this
     */
    public function decimal($name, $maximum = null, $decimal = null)
    {
        return $this->addColumn($name, 'decimal')->set('M', $maximum)->set('D', $maximum);
    }

    /**
     * @param $name
     * @return CreateColumn
     */
    public function boolean($name)
    {
        return $this->addColumn($name, 'boolean');
    }

    /**
     * @param $name
     * @return CreateColumn
     */
    public function binary($name)
    {
        return $this->addColumn($name, 'binary');
    }

    /**
     * @param $name
     * @param int $length
     * @return $this
     */
    public function string($name, $length = 255)
    {
        return $this->addColumn($name, 'string')->set('length', $length);
    }

    /**
     * @param $name
     * @param int $length
     * @return $this
     */
    public function fixed($name, $length = 255)
    {
        return $this->addColumn($name, 'fixed')->set('length', $length);
    }

    /**
     * @param $name
     * @return CreateColumn
     */
    public function text($name)
    {
        return $this->addColumn($name, 'text');
    }

    /**
     * @param $name
     * @return CreateColumn
     */
    public function time($name)
    {
        return $this->addColumn($name, 'time');
    }

    /**
     * @param $name
     * @return CreateColumn
     */
    public function timestamp($name)
    {
        return $this->addColumn($name, 'timestamp');
    }

    /**
     * @param $name
     * @return CreateColumn
     */
    public function date($name)
    {
        return $this->addColumn($name, 'date');
    }

    /**
     * @param $name
     * @return CreateColumn
     */
    public function dateTime($name)
    {
        return $this->addColumn($name, 'dateTime');
    }
    
}
