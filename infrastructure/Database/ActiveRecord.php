<?php
namespace Infrastructure;

use RuntimeException;
use InvalidArgumentException;
use Infrastructure\Database\ConnectionInterface;
use Infrastructure\Database\StatementInterface;

/**
 * Base Active Record implementation.
 * 
 * Consumes the Database Abstraction Layer (DBAL) to provide
 * object-oriented CRUD operations without tying the models 
 * to a specific database driver. Features dynamic attributes
 * and mass-assignment protection via $columns.
 */
class ActiveRecord {
    // =============== DATABASE CONFIGURATION ===============
    
    /** @var array<string, ConnectionInterface> */
    protected static array $connections = [];
    protected static string $db = 'default';
    
    /** @var string The database table associated with the model. */
    protected static string $table = '';
    
    /** @var string The primary key for the model. */
    protected static string $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     * If empty, all attributes are allowed (unsafe).
     * 
     * @var array<string>
     */
    protected static array $columns = [];

    /**
     * The model's dynamic attributes mapping.
     * 
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Injects a DBAL-compliant connection.
     */
    public static function setDB(string $name, ConnectionInterface $database): void {
        self::$connections[$name] = $database;
    }

    public static function getDB(string $name): ?ConnectionInterface {
        return self::$connections[$name] ?? null;
    }

    // ================= MAGIC METHODS =================

    /**
     * Initializes the model and hydrates attributes dynamically.
     */
    public function __construct(array $attributes = []) {
        foreach ($attributes as $key => $value) {
            $this->$key = $value; // This triggers __set() for validation
        }
    }

    /**
     * Intercepts property assignment and validates against the $columns whitelist.
     */
    public function __set(string $name, mixed $value): void {
        // Validation against whitelist
        if (!empty(static::$columns)) {
            // Allow assignment ONLY if it's in the columns array OR if it's the primary key
            if (!in_array($name, static::$columns) && $name !== static::$primaryKey) {
                throw new InvalidArgumentException(
                    "ORM Error: The property '{$name}' is not columns in model " . static::class
                );
            }
        }

        $this->attributes[$name] = $value;
    }

    /**
     * Intercepts property reading for dynamic attributes.
     */
    public function __get(string $name): mixed {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Allows the use of isset() or empty() on dynamic properties.
     */
    public function __isset(string $name): bool {
        return isset($this->attributes[$name]);
    }

    // ================= CRUD OPERATIONS =================
    
    public function save(): bool {
        $pk = static::$primaryKey;
        // If the primary key exists and is not null, update. Otherwise, create.
        return (isset($this->$pk) && !is_null($this->$pk)) ? $this->update() : $this->create();
    }

    public function create(): bool {
        $dbAttributes = $this->getAttributesForDb();

        $cols = join(', ', array_keys($dbAttributes));
        $placeholders = implode(', ', array_fill(0, count($dbAttributes), '?'));
        
        $query = "INSERT INTO " . static::$table . " ($cols) VALUES ($placeholders)";
        $params = array_values($dbAttributes);

        return static::executeWrite($query, $params);
    }

    public function update(): bool {
        $dbAttributes = $this->getAttributesForDb();
        $set = implode(', ', array_map(fn($col) => "$col = ?", array_keys($dbAttributes)));

        $pk = static::$primaryKey;
        $query = "UPDATE " . static::$table . " SET $set WHERE $pk = ?";

        $params = array_values($dbAttributes);
        $params[] = $this->$pk;
        
        return static::executeWrite($query, $params);
    }

    public function delete(): bool {
        $pk = static::$primaryKey;
        $query = "DELETE FROM " . static::$table . " WHERE $pk = ?";
        return static::executeWrite($query, [$this->$pk]);
    }

    public static function get(array $filters = []): array {
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

    public static function getOne(array $filters = []): ?static {
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
        
        $query .= " LIMIT 1"; 
        $results = static::executeRead($query, $params);
        
        return $results[0] ?? null;
    }

    public static function getAll(): array {
        $query = "SELECT * FROM " . static::$table;
        return static::executeRead($query);
    }

    public static function getById(int|string $id): ?static {
        $pk = static::$primaryKey;
        $query = "SELECT * FROM " . static::$table . " WHERE $pk = ?";
        $results = static::executeRead($query, [$id]);
        return $results[0] ?? null;
    }

    // =============== INTERNAL HELPERS ===============

    /**
     * Returns the attributes ready for a DB insert/update, ignoring the primary key.
     */
    protected function getAttributesForDb(): array {
        $attrs = $this->attributes;
        $pk = static::$primaryKey;
        
        if (array_key_exists($pk, $attrs)) {
            unset($attrs[$pk]);
        }
        
        return $attrs;
    }

    /**
     * Internal helper to prepare the query, bind parameters, and execute via the DBAL.
     */
    private static function prepareQuery(string $query, array $params): StatementInterface {
        $conn = self::getDB(static::$db);
        
        if (!$conn) {
            throw new RuntimeException("Database connection '" . static::$db . "' not found.");
        }

        $stmt = $conn->prepare($query);
        
        if(!empty($params)) {
            $stmt->bind($params);
        }

        $stmt->execute();

        return $stmt;
    }

    /**
     * Executes SELECT queries and returns an array of hydrated model instances.
     */
    protected static function executeRead(string $query, array $params = []): array {
        $stmt = self::prepareQuery($query, $params);
        
        $rows = $stmt->fetchAll();
        $array = [];
        
        foreach($rows as $row) {
            $array[] = static::createObject($row);
        }
        
        return $array;
    }

    /**
     * Executes INSERT, UPDATE, DELETE queries.
     */
    protected static function executeWrite(string $query, array $params = []): bool {
        $stmt = self::prepareQuery($query, $params);
        return $stmt->affectedRows() >= 0; 
    }

    /**
     * Maps an associative array from the database directly to an object instance
     * bypassing the __set validation for performance and strictness.
     */
    public static function createObject(array $row): static {
        $obj = new static();
        // Llenamos el arreglo interno directamente. 
        // Esto es un bypass seguro porque los datos vienen directamente de la DB, no del usuario.
        $obj->attributes = $row; 
        return $obj;
    }
}