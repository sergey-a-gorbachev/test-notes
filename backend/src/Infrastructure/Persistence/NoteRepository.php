<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Model\Note;
use App\Domain\Repository\NoteRepositoryInterface;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

final readonly class NoteRepository implements NoteRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /** @return list<Note> */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Note::class)->findBy([], ['updatedAt' => 'DESC', 'id' => 'DESC']);
    }

    public function find(int $id): ?Note
    {
        return $this->entityManager->find(Note::class, $id);
    }

    public function create(string $title, string $content): Note
    {
        $note = new Note($title, $content);
        $this->entityManager->persist($note);
        $this->entityManager->flush();

        return $note;
    }

    public function update(Note $note, string $title, string $content, int $expectedVersion): Note
    {
        $this->entityManager->lock($note, LockMode::OPTIMISTIC, $expectedVersion);
        $note->update($title, $content);
        $this->entityManager->flush();

        return $note;
    }

    public function delete(Note $note, int $expectedVersion): void
    {
        $this->entityManager->lock($note, LockMode::OPTIMISTIC, $expectedVersion);
        $this->entityManager->remove($note);
        $this->entityManager->flush();
    }
}
