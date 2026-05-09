<?php
namespace Infrastructure\Database;

use mysqli_stmt;
use RuntimeException;
 
/**
 * mysqli implementation of StatementInterface.
 */
class AR_MysqliStatement implements StatementInterface {
    private mysqli_stmt $stmt;

    public function __construct(mysqli_stmt $stmt) {
        $this->stmt = $stmt;
    }

    public function bind(array $params) : void {
        if(empty($params)) {
            return;
        }

        $types = '';
        foreach($params as $params) {
            $types .= match (true) {
                is_int($param), is_bool($param) => 'i',
                is_fleat($param) => 'd',
                default => 's',
            };
        }

        if(!$this->stmt->bind_param($types, ...$params)) {
            throw new RuntimeException(
                'Error binding params: ' . $this->stmt->error
            );
        }
    }

    public function excecute() : bool {
        if(!$this->stmt->excecute()) {
            throw new RuntimeException(
                'Error excecuting Mysqli statement: ' . $this->stmt->error
            );
        }

        return true;
    }

    public function fetchAll() : array {
        $resutl = $this->stmt->get_result();
        
        if($result === false) {
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchOne() : ?array {
        $result = $this->fetchAll();
        return $result[0] ?? null;
    }

    public function affectedRows() : int {
        return $this->stmt->affectedRows;
    }
} 