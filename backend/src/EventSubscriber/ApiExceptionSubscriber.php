<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Domain\Exception\NoteNotFoundException;
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
            $event->setResponse(new JsonResponse(['error' => 'Note not found.'], Response::HTTP_NOT_FOUND));
            return;
        }
        if ($exception instanceof OptimisticLockException) {
            $event->setResponse(new JsonResponse([
                'error' => 'Note was changed by another request. Reload it and try again.',
            ], Response::HTTP_CONFLICT));
            return;
        }
        if ($exception instanceof ExtraAttributesException) {
            $fields = $exception->getExtraAttributes();
            $event->setResponse(new JsonResponse([
                'error' => 'Validation failed.',
                'violations' => [['field' => (string) reset($fields), 'message' => 'Unknown field.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY));
            return;
        }
        $validation = $exception instanceof HttpExceptionInterface ? $exception->getPrevious() : null;
        if ($validation instanceof ValidationFailedException) {
            $violations = [];
            foreach ($validation->getViolations() as $violation) $violations[] = ['field' => $violation->getPropertyPath(), 'message' => $violation->getMessage()];
            $event->setResponse(new JsonResponse(['error' => 'Validation failed.', 'violations' => $violations], Response::HTTP_UNPROCESSABLE_ENTITY));
            return;
        }
        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $messages = [
                Response::HTTP_BAD_REQUEST => 'Malformed JSON.',
                Response::HTTP_NOT_FOUND => 'Resource not found.',
                Response::HTTP_METHOD_NOT_ALLOWED => 'Method not allowed.',
                Response::HTTP_UNSUPPORTED_MEDIA_TYPE => 'Unsupported media type.',
            ];
            $event->setResponse(new JsonResponse(
                ['error' => $messages[$status] ?? (Response::$statusTexts[$status] ?? 'Request failed.')],
                $status,
                $exception->getHeaders(),
            ));
            return;
        }

        $event->setResponse(new JsonResponse(['error' => 'Internal server error.'], Response::HTTP_INTERNAL_SERVER_ERROR));
    }
}
