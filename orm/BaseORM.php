<?php
// orm/BaseORM.php (Enhanced with Join Methods and NULL Support)

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

    // Insert için static değişken
    protected static $insertData = [];

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

    public static function whereNull($column) {
        static::$wheres[] = [
            'type' => 'null',
            'column' => $column,
            'operator' => 'IS NULL',
            'value' => null,
            'boolean' => 'AND'
        ];
        return new static();
    }

    public static function whereNotNull($column) {
        static::$wheres[] = [
            'type' => 'null',
            'column' => $column,
            'operator' => 'IS NOT NULL',
            'value' => null,
            'boolean' => 'AND'
        ];
        return new static();
    }

    public static function orWhereNull($column) {
        static::$wheres[] = [
            'type' => 'null',
            'column' => $column,
            'operator' => 'IS NULL',
            'value' => null,
            'boolean' => 'OR'
        ];
        return new static();
    }

    public static function orWhereNotNull($column) {
        static::$wheres[] = [
            'type' => 'null',
            'column' => $column,
            'operator' => 'IS NOT NULL',
            'value' => null,
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

    // INSERT methods
    public static function insert($data) {
        if (is_array($data) && isset($data[0])) {
            // Multiple insert
            return static::insertMultiple($data);
        } else {
            // Single insert
            static::$insertData = $data;
            return new static();
        }
    }

    public function execute() {
        if (empty(static::$insertData)) {
            throw new Exception("No data to insert");
        }

        $sql = static::buildInsertQuery();
        $params = array_values(static::$insertData);

        $instance = new static();

        try {
            $stmt = $instance->pdo->prepare($sql);
            $result = $stmt->execute($params);
            // Reset insert data
            static::$insertData = [];

            return $result;
        } catch (PDOException $e) {
            static::$insertData = [];
            throw new Exception("Error in insert: " . $e->getMessage());
        }
    }

    protected static function buildInsertQuery() {
        $columns = array_keys(static::$insertData);
        $placeholders = array_fill(0, count($columns), '?');

        return "INSERT INTO " . static::$table . " (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    }

    protected static function insertMultiple($data) {
        if (empty($data)) {
            throw new Exception("No data to insert");
        }

        $columns = array_keys($data[0]);
        $placeholders = "(" . implode(', ', array_fill(0, count($columns), '?')) . ")";
        $allPlaceholders = array_fill(0, count($data), $placeholders);

        $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $columns) . ") VALUES " . implode(', ', $allPlaceholders);

        $params = [];
        foreach ($data as $row) {
            foreach ($columns as $column) {
                $params[] = $row[$column] ?? null;
            }
        }

        $instance = new static();

        try {
            $stmt = $instance->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Error in multiple insert: " . $e->getMessage());
        }
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

        // Add where conditions - NULL support eklendi
        if (!empty(static::$wheres)) {
            $whereConditions = [];
            foreach (static::$wheres as $index => $where) {
                if ($where['type'] === 'null') {
                    // NULL kontrolleri için parametre gerekmez
                    if ($index === 0) {
                        $whereConditions[] = "{$where['column']} {$where['operator']}";
                    } else {
                        $whereConditions[] = "{$where['boolean']} {$where['column']} {$where['operator']}";
                    }
                } else {
                    // Normal where koşulları
                    if ($index === 0) {
                        $whereConditions[] = "{$where['column']} {$where['operator']} ?";
                    } else {
                        $whereConditions[] = "{$where['boolean']} {$where['column']} {$where['operator']} ?";
                    }
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

        // Where parameters - sadece null olmayan where'ler için
        foreach (static::$wheres as $where) {
            if ($where['type'] !== 'null') {
                $params[] = $where['value'];
            }
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

    public static function delete() {
        $sql = static::buildDeleteQuery();
        $params = static::getWhereParams();
        $instance = new static();
        try {
            $stmt = $instance->pdo->prepare($sql);
            $result = $stmt->execute($params);
            // Reset query builder
            static::resetQuery();
            return $result;
        } catch (PDOException $e) {
            static::resetQuery();
            throw new Exception("Error in delete query: " . $e->getMessage());
        }
    }

    protected static function buildDeleteQuery() {
        $sql = "DELETE FROM " . static::$table;
        // Add where conditions - NULL support eklendi
        if (!empty(static::$wheres)) {
            $whereConditions = [];
            foreach (static::$wheres as $index => $where) {
                if ($where['type'] === 'null') {
                    // NULL kontrolleri için parametre gerekmez
                    if ($index === 0) {
                        $whereConditions[] = "{$where['column']} {$where['operator']}";
                    } else {
                        $whereConditions[] = "{$where['boolean']} {$where['column']} {$where['operator']}";
                    }
                } else {
                    // Normal where koşulları
                    if ($index === 0) {
                        $whereConditions[] = "{$where['column']} {$where['operator']} ?";
                    } else {
                        $whereConditions[] = "{$where['boolean']} {$where['column']} {$where['operator']} ?";
                    }
                }
            }
            $sql .= " WHERE " . implode(" ", $whereConditions);
        } else {
            throw new Exception("DELETE operation requires WHERE clause for safety");
        }
        return $sql;
    }

    public function save() {
        if ($this->exists) {
            return $this->update();
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
}