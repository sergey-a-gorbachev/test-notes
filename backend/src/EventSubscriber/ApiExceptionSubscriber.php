<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Domain\Exception\NoteNotFoundException;
use App\Domain\Exception\NoteVersionConflictException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ApiExceptionSubscriber
{
    #[AsEventListener]
    public function onKernelException(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $event->getThrowable();
        if ($exception instanceof NoteNotFoundException) {
            $event->setResponse(new JsonResponse(['error' => 'Заметка не найдена.'], Response::HTTP_NOT_FOUND));
            return;
        }
        if ($exception instanceof OptimisticLockException || $exception instanceof NoteVersionConflictException) {
            $event->setResponse(new JsonResponse([
                'error' => 'Заметка уже изменена. Получите актуальную версию и повторите попытку.',
            ], Response::HTTP_CONFLICT));
            return;
        }
        if ($exception instanceof ExtraAttributesException) {
            $fields = $exception->getExtraAttributes();
            $event->setResponse(new JsonResponse([
                'error' => 'Проверка данных не пройдена.',
                'violations' => [['field' => (string) reset($fields), 'message' => 'Неизвестное поле.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY));
            return;
        }
        $validation = $exception instanceof HttpExceptionInterface ? $exception->getPrevious() : null;
        if ($validation instanceof ValidationFailedException) {
            $violations = [];
            foreach ($validation->getViolations() as $violation) {
                $violations[] = ['field' => $violation->getPropertyPath(), 'message' => $violation->getMessage()];
            }
            $event->setResponse(new JsonResponse(['error' => 'Проверка данных не пройдена.', 'violations' => $violations], Response::HTTP_UNPROCESSABLE_ENTITY));
            return;
        }
        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $messages = [
                Response::HTTP_BAD_REQUEST => 'Некорректный JSON.',
                Response::HTTP_NOT_FOUND => 'Ресурс не найден.',
                Response::HTTP_METHOD_NOT_ALLOWED => 'Метод не поддерживается.',
                Response::HTTP_UNSUPPORTED_MEDIA_TYPE => 'Неподдерживаемый тип содержимого.',
                Response::HTTP_PRECONDITION_REQUIRED => 'Для операции необходимо указать актуальную версию.',
            ];
            if ($status === Response::HTTP_BAD_REQUEST && str_contains($exception->getMessage(), 'X-Note-Version')) {
                $messages[$status] = $exception->getMessage();
            }
            $event->setResponse(new JsonResponse(
                ['error' => $messages[$status] ?? 'Не удалось выполнить запрос.'],
                $status,
                $exception->getHeaders(),
            ));
            return;
        }

        $event->setResponse(new JsonResponse(['error' => 'Внутренняя ошибка сервера.'], Response::HTTP_INTERNAL_SERVER_ERROR));
    }
}
