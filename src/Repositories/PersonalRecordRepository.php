<?php 

namespace App\Repositories;

use PDO;

class PersonalRecordRepository {
    private PDO $pdo; 

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo; 
    }

    /**
     * Busca todos os registros de recordes pessoais de um movimento.
     * Retorna: user_id, user_name, value, recorded_at.
     */
    public function getRankingByMovementId(int $movementId): array
    {
        $sql = "WITH best_records AS (
                    SELECT
                        pr.user_id,
                        pr.value AS personal_record,
                        pr.recorded_at AS record_date,
                        ROW_NUMBER() OVER (
                            PARTITION BY pr.user_id
                            ORDER BY pr.value DESC, pr.recorded_at ASC
                        ) AS rn
                    FROM personal_records pr
                    WHERE pr.movement_id = :movement_id
                )
                SELECT
                    u.name AS user_name,
                    br.personal_record,
                    br.record_date,
                    RANK() OVER (ORDER BY br.personal_record DESC, br.record_date ASC) AS ranking_position
                FROM best_records br
                JOIN users u ON u.id = br.user_id
                WHERE br.rn = 1
                ORDER BY br.personal_record DESC, br.record_date ASC";

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['movement_id' => $movementId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    } 
}