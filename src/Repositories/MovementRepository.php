<?php 

namespace App\Repositories;

use PDO;

class MovementRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    } 

    /**
     * Buscar um movimento pelo ID
     */
    public function findMovementById(int $id): array {
        $statement = $this->pdo->prepare(
            "SELECT id, name
                FROM movements 
                WHERE id = :id LIMIT 1"
        );

        $statement->execute(['id' => $id]);
 
        $movement = $statement->fetch();
 
        return $movement ?: [];
    }

    /**
     * Buscar um movimento pelo nome
     */
    public function findMovementByName(string $name): array {
        $statement = $this->pdo->prepare(
            "SELECT 
                id, 
                name
            FROM movements 
            WHERE name = :name
            LIMIT 1"
        );

        $statement->execute(['name' => $name]);

        $movement = $statement->fetch();

        return $movement ?: [];
    }
}