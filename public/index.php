<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
  
use App\Exceptions\MovementNotFoundException;
use App\Http\JsonResponse;
use App\Http\Router;
use App\Repositories\MovementRepository;
use App\Repositories\PersonalRecordRepository;
use App\Services\RankingService;

$pdo = (require __DIR__ . '/../config/database.php')(); 
 
$router = new Router();
$movementRepository = new MovementRepository($pdo);
$personalRecordRepository = new PersonalRecordRepository($pdo);
$rankingService = new RankingService($movementRepository, $personalRecordRepository);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->get('/ranking/{identifier}', function ($identifier) use ($rankingService) {
    $data = $rankingService->getRankingByMovementIdentifier(urldecode($identifier));
    JsonResponse::success($data)->send();
});

try { 
    $router->dispatch($method, $uri);
} catch (MovementNotFoundException $e) { 
    JsonResponse::error($e->getMessage(), 404)->send(); 
} catch (\Throwable $e) { 
    JsonResponse::error('Erro interno do servidor', 500)->send();
}