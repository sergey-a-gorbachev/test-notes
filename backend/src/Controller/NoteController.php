<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Dto\ErrorResponse;
use App\Application\Dto\NoteListResponse;
use App\Application\Dto\CreateNoteRequest;
use App\Application\Dto\NoteResponse;
use App\Application\Dto\UpdateNoteRequest;
use App\Application\UseCase\CreateNote;
use App\Application\UseCase\DeleteNote;
use App\Application\UseCase\GetNote;
use App\Application\UseCase\ListNotes;
use App\Application\UseCase\UpdateNote;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/notes', name: 'notes_')]
#[OA\Tag(name: 'Notes', description: 'Операции для управления заметками.')]
final readonly class NoteController
{
    #[Route('', name: 'index', methods: ['GET'])]
    #[OA\Get(summary: 'Получить список заметок', description: 'Возвращает все заметки, начиная с недавно изменённых.')]
    #[OA\Response(response: 200, description: 'Список заметок получен.', content: new Model(type: NoteListResponse::class))]
    public function index(ListNotes $useCase): JsonResponse { return new JsonResponse($useCase()); }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[OA\Get(summary: 'Получить заметку', description: 'Возвращает заметку с указанным идентификатором.')]
    #[OA\Response(response: 200, description: 'Заметка получена.', content: new Model(type: NoteResponse::class))]
    #[OA\Response(response: 404, description: 'Заметка не найдена.', content: new Model(type: ErrorResponse::class))]
    public function show(int $id, GetNote $useCase): JsonResponse { return new JsonResponse($useCase($id)); }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(summary: 'Создать заметку', description: 'Создаёт заметку и возвращает её с присвоенным идентификатором.')]
    #[OA\Response(response: 201, description: 'Заметка создана.', content: new Model(type: NoteResponse::class))]
    #[OA\Response(response: 400, description: 'Некорректное тело запроса.', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 422, description: 'Поля не прошли проверку.', content: new Model(type: ErrorResponse::class))]
    public function create(
        #[MapRequestPayload(acceptFormat: 'json', serializationContext: [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false])] CreateNoteRequest $request,
        CreateNote $useCase,
    ): JsonResponse {
        $note = $useCase($request);
        return new JsonResponse($note, Response::HTTP_CREATED, ['Location' => '/api/notes/'.$note->id]);
    }

    #[Route('/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[OA\Put(summary: 'Изменить заметку', description: 'Полностью заменяет название и содержимое заметки.')]
    #[OA\Response(response: 200, description: 'Заметка изменена.', content: new Model(type: NoteResponse::class))]
    #[OA\Response(response: 400, description: 'Некорректное тело запроса.', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 404, description: 'Заметка не найдена.', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 409, description: 'Версия заметки устарела.', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 422, description: 'Поля не прошли проверку.', content: new Model(type: ErrorResponse::class))]
    public function update(
        int $id,
        #[MapRequestPayload(acceptFormat: 'json', serializationContext: [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false])] UpdateNoteRequest $request,
        UpdateNote $useCase,
    ): JsonResponse { return new JsonResponse($useCase($id, $request)); }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Delete(summary: 'Удалить заметку', description: 'Удаляет заметку с указанным идентификатором.')]
    #[OA\Response(response: 204, description: 'Заметка удалена.')]
    #[OA\Response(response: 404, description: 'Заметка не найдена.', content: new Model(type: ErrorResponse::class))]
    public function delete(int $id, DeleteNote $useCase): Response
    {
        $useCase($id);
        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
