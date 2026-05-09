<?php
namespace Infrastructure\Database;

use Infrastructure\Database\ConnectionInterface;
use Infrastructure\Database\TestStatement;

/**
 * Fake connection implementation for testing purposes.
 *
 * This class allows the ORM to be tested without connecting
 * to a real database.
 *
 * It returns fake statement objects that simulate
 * database behavior in-memory.
 */
class AR_TestConnection implements ConnectionInterface {
    /**
     * Prepares a fake SQL statement.
     *
     * @param string $sql
     *
     * @return StatementInterface
     */
    public function prepare(string $sql): StatementInterface {
        return new AR_TestStatement($sql);
    }
}