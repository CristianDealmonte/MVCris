<?php
namespace Infrastructure\Database;

use mysqli; 
use RuntimeException;
use Infrastructure\Database\ConnectionInterface;

/**
 * mysqli implementation of ConnectionInterface.
 * 
 * This class acts as an adapter between the ORM and the mysqli database driver.
 * 
 * Its responsability is to:
 *  - Wrap a native mysqli connection.
 *  - Prepare SQL statements.
 *  - Return driver-agnostic statements objects.
 * 
 * IMPORTANT
 * This class contains NO business logic and NO ORM logic.
 * It is purely an infrastructure component.
*/
class AR_MysqliConnection implements ConnectionInterface {


    /**
     * Native mysqli connection instance.
     * 
     * This object represents an active connection to
     * a MySQL database using the mysqli driver.
     * 
     * @var mysqli
     */
    private mysqli $connection;


    /**
     * Creates a new MySQLi connection wrapper.
     * 
     * @param mysqli $connection An initialized and connected mysqli instance.
     */
    public function __construct(mysqli $connection) {
        $this->connection = $connection;
    }


    /**
     * Prepares an SQL statement for execution.
     * 
     * This method delegates the preparation of the SQL query
     * to the underlying mysqli driver and weaps the resulting
     * mysqli_stmt inside a StatementInterface implementation.
     * 
     * @param string $sql The SQL query to prepare.
     * 
     * @return StatementInterface A prepared, driver-agnostic statement.
     * 
     * @throws RuntimeException If the SQL statement cannot be prepared.
     */
    public function prepare(string $sql) : StatementInterface {
        $stmt = $this->connection->prepare($sql);

        if($stmt === false) {
            throw new RuntimeException(
                'Error preparing SQL (Mysqli): ' . $this->connection->error
            );
        }

        return new AR_MysqliStatement($stmt);
    }
}