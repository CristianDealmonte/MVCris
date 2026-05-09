<?php

namespace Infrastructure;

use Infrastructure\Database\ConnectionInterface;
use Infrastructure\Database\StatementInterface;
use RuntimeException;

/**
 * Base Active Record implementation.
 *
 * This abstract class provides basic CRUD functionality
 * for database-backed models using the Active Record pattern.
 *
 * It is completely decoupled from any specific database driver
 * and relies only on ConnectionInterface and StatementInterface.
 */
abstract class ActiveRecord
{
    /**
     * Database connection used by all models.
     *
     * @var ConnectionInterface|null
     */
    protected static ?ConnectionInterface $connection = null;

    /**
     * Database table name.
     *
     * Must be defined by child classes.
     *
     * @var string
     */
    protected static string $table;

    /**
     * Primary key column.
     *
     * @var string
     */
    protected static string $primaryKey = 'id';

    /**
     * List of database columns.
     *
     * Must be defined by child classes.
     *
     * @var array<int, string>
     */
    protected static array $columns = [];

    /**
     * Assigns the database connection to the ActiveRecord system.
     *
     * This method should be called once during application bootstrap.
     */
    public static function setConnection(ConnectionInterface $connection): void
    {
        self::$connection = $connection;
    }

    /**
     * Persists the current model to the database.
     *
     * Performs INSERT or UPDATE depending on the presence
     * of the primary key.
     */
    public function save(): bool
    {
        $pk = static::$primaryKey;

        return isset($this->$pk)
            ? $this->update()
            : $this->insert();
    }

    /**
     * Inserts a new record into the database.
     */
    protected function insert(): bool
    {
        $attributes = $this->attributes();

        $columns = implode(', ', array_keys($attributes));
        $placeholders = implode(', ', array_fill(0, count($attributes), '?'));

        $sql = "INSERT INTO " . static::$table .
               " ($columns) VALUES ($placeholders)";

        $stmt = $this->statement($sql, array_values($attributes));

        return $stmt->affectedRows() >= 0;
    }

    /**
     * Updates an existing database record.
     */
    protected function update(): bool
    {
        $attributes = $this->attributes();
        $pk = static::$primaryKey;

        $set = implode(', ', array_map(
            fn ($col) => "$col = ?",
            array_keys($attributes)
        ));

        $sql = "UPDATE " . static::$table .
               " SET $set WHERE $pk = ?";

        $params = array_values($attributes);
        $params[] = $this->$pk;

        $stmt = $this->statement($sql, $params);

        return $stmt->affectedRows() >= 0;
    }

    /**
     * Deletes the current model from the database.
     */
    public function delete(): bool
    {
        $pk = static::$primaryKey;

        $sql = "DELETE FROM " . static::$table . " WHERE $pk = ?";
        $stmt = $this->statement($sql, [$this->$pk]);

        return $stmt->affectedRows() > 0;
    }

    /**
     * Retrieves all records.
     *
     * @return static[]
     */
    public static function all(): array
    {
        return static::query("SELECT * FROM " . static::$table);
    }

    /**
     * Finds a record by primary key.
     */
    public static function find(int|string $id): ?static
    {
        $pk = static::$primaryKey;
        $results = static::query(
            "SELECT * FROM " . static::$table . " WHERE $pk = ?",
            [$id]
        );

        return $results[0] ?? null;
    }

    /**
     * Executes a SELECT query and hydrates results.
     *
     * @return static[]
     */
    protected static function query(string $sql, array $params = []): array
    {
        if (!self::$connection) {
            throw new RuntimeException('Database connection not set.');
        }

        $stmt = self::$connection->prepare($sql);
        $stmt->bind($params);
        $stmt->execute();

        $rows = $stmt->fetchAll();

        return array_map(
            fn ($row) => static::hydrate($row),
            $rows
        );
    }

    /**
     * Creates a prepared statement and executes it.
     */
    protected function statement(string $sql, array $params): StatementInterface
    {
        if (!self::$connection) {
            throw new RuntimeException('Database connection not set.');
        }

        $stmt = self::$connection->prepare($sql);
        $stmt->bind($params);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Extracts model attributes for persistence.
     */
    protected function attributes(): array
    {
        $data = [];
        $pk = static::$primaryKey;

        foreach (static::$columns as $column) {
            if ($column === $pk) {
                continue;
            }

            $data[$column] = $this->$column ?? null;
        }

        return $data;
    }

    /**
     * Hydrates a model instance from database row data.
     */
    protected static function hydrate(array $row): static
    {
        $model = new static();

        foreach ($row as $key => $value) {
            if (property_exists($model, $key)) {
                $model->$key = $value;
            }
        }

        return $model;
    }
}