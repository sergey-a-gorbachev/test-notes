<?php

declare(strict_types=1);

namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController
{
    #[Route('/api/health', name: 'health', methods: ['GET'])]
    #[OA\Get(summary: 'Проверить состояние сервиса')]
    #[OA\Response(
        response: 200,
        description: 'Сервис работает.',
        content: new OA\JsonContent(properties: [new OA\Property(property: 'status', type: 'string', example: 'ok')]),
    )]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }
}
