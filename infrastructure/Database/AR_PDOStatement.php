<?php

namespace Infrastructure\Database;

use PDOStatement;
use RuntimeException;

/**
 * PDO implementation of StatementInterface.
 *
 * Adapts a PDOStatement to the ORM statement contract.
 */
class AR_PDOStatement implements StatementInterface {
    /**
     * Native PDO statement instance.
     *
     * @var PDOStatement
     */
    private PDOStatement $stmt;

    /**
     * Creates a new PDO statement wraper.
     * 
     * @param PDOStatement $stmt
     */
    public function __construct(PDOStatement $stmt) {
        $this->stmt = $stmt;
    }

    /**
     * Binds parameters to the statement.
     *
     * @param array<int|string, mixed> $params
     */
    public function bind(array $params): void {
        foreach ($params as $key => $value) {
            $this->stmt->bindValue(
                is_int($key) ? $key + 1 : $key,
                $value
            );
        }
    }

    /**
     * Executes the statement.
     *
     * @return bool
     */
    public function execute(): bool {
        if (!$this->stmt->execute()) {
            throw new RuntimeException('Error executing PDO statement');
        }

        return true;
    }

    /**
     * Fetches all result rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(): array {
        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetches the first row or null.
     *
     * @return array<string, mixed>|null
     */
    public function fetchOne(): ?array {
        $row = $this->stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /**
     * Returns affected rows.
     *
     * @return int
     */
    public function affectedRows(): int {
        return $this->stmt->rowCount();
    }
}