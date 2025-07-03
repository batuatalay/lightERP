<?php
// orm/BaseORM.php (Enhanced with Join Methods)

abstract class BaseORM extends Mysql {
    protected static $table;
    protected static $primaryKey = 'id';
    protected static $fillable = [];
    protected $attributes = [];
    protected $exists = false;
    protected static $connection = null;
    protected static $query = [];
    
    // Query builder için static değişkenler
    protected static $select = '*';
    protected static $joins = [];
    protected static $wheres = [];
    protected static $groups = [];
    protected static $havings = [];
    protected static $orders = [];
    protected static $limitCount = null;
    
    public function __construct($attributes = []) {
        if (self::$connection === null) {
            self::$connection = $this->connect();
        }
        $this->pdo = self::$connection;
        $this->fill($attributes);
    }
    
    // Query builder methods
    public static function select($columns = '*') {
        static::$select = is_array($columns) ? implode(', ', $columns) : $columns;
        return new static();
    }
    
    public static function from($table) {
        static::$table = $table;
        return new static();
    }
    
    public static function orWhere($column, $operator = '=', $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        static::$wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR'
        ];
        return new static();
    }
    
    public static function join($table, $first, $operator = '=', $second = null) {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }
        static::$joins[] = "JOIN {$table} ON {$first} {$operator} {$second}";
        return new static();
    }
    
    public static function leftJoin($table, $first, $operator = '=', $second = null) {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }
        static::$joins[] = "LEFT JOIN {$table} ON {$first} {$operator} {$second}";
        return new static();
    }
    
    public static function rightJoin($table, $first, $operator = '=', $second = null) {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }
        static::$joins[] = "RIGHT JOIN {$table} ON {$first} {$operator} {$second}";
        return new static();
    }
    
    public static function innerJoin($table, $first, $operator = '=', $second = null) {
        return static::join($table, $first, $operator, $second);
    }
    
    public static function whereColumn($column, $operator = '=', $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        static::$wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];
        return new static();
    }
    
    public static function groupBy($columns) {
        $columns = is_array($columns) ? $columns : func_get_args();
        static::$groups = array_merge(static::$groups, $columns);
        return new static();
    }
    
    public static function having($column, $operator = '=', $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        static::$havings[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        return new static();
    }
    
    public static function orderBy($column, $direction = 'ASC') {
        static::$orders[] = "{$column} {$direction}";
        return new static();
    }
    
    public static function limit($count) {
        static::$limitCount = $count;
        return new static();
    }
    
    // Build and execute query
    public static function get() {
        $sql = static::buildSelectQuery();
        $params = static::getWhereParams();
        
        $instance = new static();
        
        try {
            $stmt = $instance->pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Reset query builder
            static::resetQuery();
            
            return $results; // Raw results için
        } catch (PDOException $e) {
            static::resetQuery();
            throw new Exception("Error in query: " . $e->getMessage());
        }
    }
    
    protected static function buildSelectQuery() {
        $sql = "SELECT " . static::$select . " FROM " . static::$table;
        
        // Add joins
        if (!empty(static::$joins)) {
            $sql .= " " . implode(" ", static::$joins);
        }
        
        // Add where conditions
        if (!empty(static::$wheres)) {
            $whereConditions = [];
            $boolean = 'WHERE';
            foreach (static::$wheres as $index => $where) {
                if ($index === 0) {
                    $whereConditions[] = "{$where['column']} {$where['operator']} ?";
                } else {
                    $whereConditions[] = "{$where['boolean']} {$where['column']} {$where['operator']} ?";
                }
            }
            $sql .= " WHERE " . implode(" ", $whereConditions);
        }
        
        // Add group by
        if (!empty(static::$groups)) {
            $sql .= " GROUP BY " . implode(", ", static::$groups);
        }
        
        // Add having
        if (!empty(static::$havings)) {
            $havingConditions = [];
            foreach (static::$havings as $having) {
                $havingConditions[] = "{$having['column']} {$having['operator']} ?";
            }
            $sql .= " HAVING " . implode(" AND ", $havingConditions);
        }
        
        // Add order by
        if (!empty(static::$orders)) {
            $sql .= " ORDER BY " . implode(", ", static::$orders);
        }
        
        // Add limit
        if (static::$limitCount !== null) {
            $sql .= " LIMIT " . static::$limitCount;
        }
        
        return $sql;
    }
    
    protected static function getWhereParams() {
        $params = [];
        
        // Where parameters
        foreach (static::$wheres as $where) {
            $params[] = $where['value'];
        }
        
        // Having parameters
        foreach (static::$havings as $having) {
            $params[] = $having['value'];
        }
        
        return $params;
    }
    
    protected static function resetQuery() {
        static::$select = '*';
        static::$joins = [];
        static::$wheres = [];
        static::$groups = [];
        static::$havings = [];
        static::$orders = [];
        static::$limitCount = null;
    }
    
    // Mevcut metodlar (değişmeden)...
    protected function fill($attributes) {
        foreach ($attributes as $key => $value) {
            if (empty(static::$fillable) || in_array($key, static::$fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }
    
    public function __get($key) {
        return $this->attributes[$key] ?? null;
    }
    
    public function __set($key, $value) {
        if (empty(static::$fillable) || in_array($key, static::$fillable)) {
            $this->attributes[$key] = $value;
        }
    }
    
    public static function all() {
        return static::select('*')->get();
    }
    
    public static function find($id) {
        $instance = new static();
        $sql = "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?";
        
        try {
            $stmt = $instance->pdo->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $model = new static($result);
                $model->exists = true;
                return $model;
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception("Error finding record: " . $e->getMessage());
        }
    }
    
    public static function where($column, $operator = '=', $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        return static::whereColumn($column, $operator, $value);
    }
    
    public static function first() {
        return static::limit(1)->get()[0] ?? null;
    }
    
    // Raw SQL method
    public static function raw($sql, $params = []) {
        $instance = new static();
        
        try {
            $stmt = $instance->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error in raw query: " . $e->getMessage());
        }
    }
    
    // Instance methods (save, create, update, delete) remain the same...
    public function save() {
        if ($this->exists) {
            return $this->update();
        } else {
            return $this->create();
        }
    }
    
    protected function create() {
        $columns = array_keys($this->attributes);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute(array_values($this->attributes));
            
            if ($result) {
                $this->attributes[static::$primaryKey] = $this->pdo->lastInsertId();
                $this->exists = true;
            }
            
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Error creating record: " . $e->getMessage());
        }
    }
    
    protected function update() {
        if (!isset($this->attributes[static::$primaryKey])) {
            throw new Exception("Cannot update record without primary key");
        }
        
        $updates = [];
        $values = [];
        
        foreach ($this->attributes as $key => $value) {
            if ($key !== static::$primaryKey) {
                $updates[] = "{$key} = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $this->attributes[static::$primaryKey];
        
        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $updates) . " WHERE " . static::$primaryKey . " = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            throw new Exception("Error updating record: " . $e->getMessage());
        }
    }
    
    public function delete() {
        if (!$this->exists || !isset($this->attributes[static::$primaryKey])) {
            throw new Exception("Cannot delete record without primary key");
        }
        
        $sql = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$this->attributes[static::$primaryKey]]);
            
            if ($result) {
                $this->exists = false;
            }
            
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Error deleting record: " . $e->getMessage());
        }
    }
}