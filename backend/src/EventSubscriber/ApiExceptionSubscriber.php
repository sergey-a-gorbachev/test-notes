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
        if (!$exception instanceof HttpExceptionInterface || !str_starts_with($event->getRequest()->getPathInfo(), '/api/')) return;

        $validation = $exception->getPrevious();
        if ($validation instanceof ValidationFailedException) {
            $violations = [];
            foreach ($validation->getViolations() as $violation) $violations[] = ['field' => $violation->getPropertyPath(), 'message' => $violation->getMessage()];
            $event->setResponse(new JsonResponse(['error' => 'Validation failed.', 'violations' => $violations], Response::HTTP_UNPROCESSABLE_ENTITY));
            return;
        }
        $message = $exception->getStatusCode() === 400 ? 'Malformed JSON.' : $exception->getMessage();
        $event->setResponse(new JsonResponse(['error' => $message], $exception->getStatusCode()));
    }
}
