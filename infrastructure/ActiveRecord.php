<?php
namespace Infrastructure;

class ActiveRecord {
    // =============== DATABASE ===============
    protected static $connections = [];
    protected static $db = 'default';
    protected static $table = '';
    protected static $colsDB = [];

    // Registrar la conexión a la base de datos
    public static function setDB($name, $database) {
        self::$connections[$name] = $database;
    }

    public static function getDB($name) {
        return self::$connections[$name] ?? null;
    }


    // ================= CRUD =================
    public function save() {
        $this->id ? $this->update() : $this->create();
    }

    public function create() {
        $atributes = $this->getAtributes();

        $cols = join(', ', array_keys($atributes));
        $placeholders = implode(', ', array_fill(0, count($atributes), '?'));
        
        $query = "INSERT INTO " . static::$table . " ($cols) VALUES ($placeholders)";
        $params = array_values($atributes);

        return static::executeSQL($query, $params);
    }

    public function update() {
        $atributes = $this->getAtributes();
        $set = implode(', ', array_map(fn($col) => "$col = ?", array_keys($atributes)));

        $query = "UPDATE " . static::$table . " SET $set WHERE id = ?";

        $params = array_values($atributes);
        $params[] = $this->id;
        return  static::executeSQL($query, $params);
    }

    public function delete() {
        $query = "DELETE FROM " . static::$table . " WHERE id = ?";
        return static::executeSQL($query, [$this->id], "i");
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
        return static::executeSQL($query, $params);
    }

    public static function getOne($filters = []) {
        $result = static::get($filters);
        return $result[0] ?? null;
    }

    public static function getAll() {
        $query = "SELECT * FROM " . static::$table;
        return static::executeSQL($query);
    }

    public static function getById($id) {
        $query = "SELECT * FROM " . static::$table . " WHERE id = ?";
        $result = static::executeSQL($query, [$id], 'i');
        return $result[0] ?? null;
    }


    // =============== HELPERS ===============

    // Return an array with the attributes of the objects, except the id
    public function getAtributes() {
        $atributes = [];
        foreach(static::$colsDB as $col) {
            if($col === 'id') continue;
            $atributes[$col] = $this->$col;
        }
        return $atributes;
    }

    public static function executeSQL($query, $params = [], $types = '') {
        $dbName = self::getDB(static::$db);
        $stmt = $dbName->prepare($query);
        if ($stmt === false) {
            throw new \Exception("Error al preparar la consulta: " . $db->error);
        }

        if(!empty($params)) {
            // si no se especifican los tipos, se infieren automáticamente
            if($types === '') {
                foreach($params as $param) {
                    if(is_int($param)) {
                        $types .= 'i';
                    } elseif(is_double($param)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                }
            }

            if (!$stmt->bind_param($types, ...$params)) {
                throw new \Exception("Error al enlazar parámetros: " . $stmt->error);
            }
        }

        if (!$stmt->execute()) {
            throw new \Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result === false && $stmt->errno) {
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