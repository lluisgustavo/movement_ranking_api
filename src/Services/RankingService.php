<?php

namespace App\Services;

use App\Exceptions\MovementNotFoundException;
use App\Repositories\MovementRepository;
use App\Repositories\PersonalRecordRepository;

class RankingService
{
    private MovementRepository $movementRepository;
    private PersonalRecordRepository $personalRecordRepository;

    public function __construct(
        MovementRepository $movementRepository,
        PersonalRecordRepository $personalRecordRepository
    ) {
        $this->movementRepository = $movementRepository;
        $this->personalRecordRepository = $personalRecordRepository;
    }

    /**
     * Retorna o ranking com nome do movimento e lista ordenada.
     * Usuários com mesmo recorde compartilham a mesma posição (empate).
     *
     * @throws MovementNotFoundException Quando o movimento não for encontrado
     */
    public function getRankingByMovementIdentifier(string|int $identifier): array
    {
        $movement = ctype_digit((string) $identifier)
            ? $this->movementRepository->findMovementById((int) $identifier)
            : $this->movementRepository->findMovementByName((string) $identifier); 
        
        if (empty($movement)) {
            throw new MovementNotFoundException('Movimento não encontrado');
        }
 
        $ranking = $this->personalRecordRepository->getRankingByMovementId($movement['id']);

        return [
            'movement_name' => $movement['name'],
            'ranking' => $ranking,
        ];
    }
}