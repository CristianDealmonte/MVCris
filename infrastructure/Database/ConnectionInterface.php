<?php
namespace Infrastructure\Database;

use Infrastructure\Database\StatementInterface;


/**
 * Defines a contract for database connections.
 * 
 * This interface abstracts the underlying database driver
 * (mysqli, PDO, etc.) and allows the ORM to remain decoupled
 * from any apecific implementation.
 */
interface ConnectionInterface {
    /**
     * Prepares an SQL statement for execution.
     * 
     * @param string $sql The SQL query to prepare.
     * 
     * @return StatementInterface
     */
    public function prepare(string $sql): StatementInterface;
}