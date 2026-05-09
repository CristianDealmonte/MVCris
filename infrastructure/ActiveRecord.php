<?php
namespace Infrastructure;

use mysqli;

class ActiveRecord {
    // =============== DATABASE ===============
    protected static $connections = [];
    protected static $db = 'default';
    protected static $table = '';
    protected static $colsDB = [];
    protected static $primaryKey = 'id';

    // Registrar la conexión a la base de datos
    public static function setDB(string $name, mysqli $database) : void {
        self::$connections[$name] = $database;
    }

    public static function getDB(string $name) {
        return self::$connections[$name] ?? null;
    }


    // ================= CRUD =================
    public function save() {
        $pk = static::$primaryKey;
        return(isset($this->$pk) && !is_null($this->$pk)) 
            ? $this->update() 
            : $this->create();
    }

    public function create() {
        $atributes = $this->getAtributes();

        $cols = join(', ', array_keys($atributes));
        $placeholders = implode(', ', array_fill(0, count($atributes), '?'));
        
        $query = "INSERT INTO " . static::$table . " ($cols) VALUES ($placeholders)";
        $params = array_values($atributes);

        $result = static::executeWrite($query, $params);
        if($result) {
            // Hidratamos el objeto con el ID recien creado en la DB
            $db = self::getDB(static::$db);
            $pk = static::$primaryKey;
            $this->$pk = $db->insert_id;
        }

        return $result;
    }

    public function update() {
        $atributes = $this->getAtributes();
        $set = implode(', ', array_map(fn($col) => "$col = ?", array_keys($atributes)));

        $pk = static::$primaryKey;
        $query = "UPDATE " . static::$table . " SET $set WHERE $pk = ?";

        $params = array_values($atributes);
        $params[] = $this->$pk;
        return  static::executeWrite($query, $params);
    }

    public function delete() {
        $pk = static::$primaryKey;
        $query = "DELETE FROM " . static::$table . " WHERE $pk = ?";
        return static::executeWrite($query, [$this->$pk], "i");
    }

    public static function get($filters = []) {
        $query = "SELECT * FROM " . static::$table;
        $params = [];

        if(!empty($filters)) {
            $conditions = [];
            foreach($filters as $col => $value) {
                if(is_array($value)) {
                    $conditions[] = "$col {$value[0]} ?";
                    $params[] = $value[1];
                } else {
                    $conditions[] = "$col = ?";
                    $params[] = $value;
                }
            }
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        return static::executeRead($query, $params);
    }

    public static function getOne($filters = []) {
        $result = static::get($filters);
        return $result[0] ?? null;
    }

    public static function getAll() {
        $query = "SELECT * FROM " . static::$table;
        return static::executeRead($query);
    }

    public static function getById($id) {
        $pk = static::$primaryKey;
        $query = "SELECT * FROM " . static::$table . " WHERE $pk = ?";
        $result = static::executeRead($query, [$id], 'i');
        return $result[0] ?? null;
    }


    // =============== HELPERS ===============

    // Return an array with the attributes of the objects, except the id
    public function getAtributes() {
        $atributes = [];
        $pk = static::$primaryKey;

        foreach(static::$colsDB as $col) {
            if($col === $pk) continue;
            $atributes[$col] = $this->$col ?? null;
        }
        return $atributes;
    }

    protected static function executeRead(string $query, $params = [], $types = '') {
        $stmt = self::prepareQuery($query, $params, $types);

        $result = $stmt->get_result();
        if($result === false && $stmt->errno) {
            throw new \Exception("Error al obtener el resultado: " . $stmt->error);
        }

        $array = [];
        if($result) {
            while($row = $result->fetch_assoc()) {
                $array[] = static::createObject($row);
            }
        }
        $stmt->close();
        return $array;
    }

    protected static function executeWrite(string $query, $params = [], $types = '') {
        $stmt = self::prepareQuery($query, $params, $types);
        $success = $stmt->affected_rows >= 0; // para UPDATEs que no cambian datos devuelve 0
        $stmt->close();
        return $success;
    }

    private static function prepareQuery(string $query, $params, $types) {
        $db = self::getDB(static::$db);
        $stmt = $db->prepare($query);

        if($stmt === false) {
            throw new \Exception("Error al preparar la consulta: " . $db->errpr);
        }

        if(!empty($params)) {
            if($types === '') {
                foreach($params as $param) {
                    if(is_int($param) || is_bool($param)) { // Los booleanos se guardan como tinyint
                        $types .= 'i';
                    } elseif(is_double($param) || is_float($param)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                }
            }

            if (!$stmt->bind_param($types, ...$params)) {
                throw new \Exception("Error al enlazar parámetros: " . $stmt->error);
            }

            if(!$stmt->execute()) {
                throw new \Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            return $stmt;
        }
    }

    // Transform each row from the DB to an object
    public static function createObject($row) {
        $obj = new static();
        foreach($row as $key => $value) {
            if(property_exists($obj, $key)) {
                $obj->$key = $value;
            }
        }
        return $obj;
    }
}