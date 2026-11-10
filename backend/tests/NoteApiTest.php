<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class NoteApiTest extends WebTestCase
{
    private static bool $migrated = false;
    private KernelBrowser $browser;

    protected function setUp(): void
    {
        $this->browser = static::createClient();
        if (!self::$migrated) {
            $application = new Application(self::$kernel);
            $application->setAutoExit(false);
            $application->run(new ArrayInput([
                'command' => 'doctrine:migrations:migrate',
                '--no-interaction' => true,
            ]), new NullOutput());
            self::$migrated = true;
        }
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->getConnection()->executeStatement('DELETE FROM notes');
        $entityManager->clear();
        $this->browser->disableReboot();
    }

    public function testCreateListShowUpdateAndDelete(): void
    {
        $client = $this->browser;
        $client->jsonRequest('POST', '/api/notes', ['title' => 'My first note', 'content' => 'Hello!']);
        self::assertResponseStatusCodeSame(201);
        $created = $this->json();
        self::assertSame('My first note', $created['title']);
        self::assertSame('Hello!', $created['content']);
        self::assertNotEmpty($created['created_at']);
        self::assertSame('/api/notes/'.$created['id'], $client->getResponse()->headers->get('Location'));

        $id = $created['id'];
        $client->request('GET', '/api/notes');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $this->json()['items']);

        $client->request('GET', '/api/notes/'.$id);
        self::assertResponseIsSuccessful();
        self::assertSame($id, $this->json()['id']);

        $client->jsonRequest('PUT', '/api/notes/'.$id, ['title' => 'Updated', 'content' => 'Changed', 'version' => $created['version']]);
        self::assertResponseIsSuccessful();
        $updated = $this->json();
        self::assertSame('Updated', $updated['title']);
        self::assertSame('Changed', $updated['content']);

        $client->request('DELETE', '/api/notes/'.$id, server: ['HTTP_X_NOTE_VERSION' => (string) $updated['version']]);
        self::assertResponseStatusCodeSame(204);
        self::assertSame('', $client->getResponse()->getContent());

        $client->request('GET', '/api/notes/'.$id);
        self::assertResponseStatusCodeSame(404);
        $client->request('GET', '/api/notes');
        self::assertSame([], $this->json()['items']);
    }

    public function testRequiredTitleIsValidated(): void
    {
        $client = $this->browser;
        $client->jsonRequest('POST', '/api/notes', ['title' => '   ']);
        self::assertResponseStatusCodeSame(422);
        self::assertSame('title', $this->json()['violations'][0]['field']);
    }

    public function testCreateBoundariesAndContract(): void
    {
        $client = $this->browser;
        $client->jsonRequest('POST', '/api/notes', ['title' => str_repeat('t', 160), 'content' => str_repeat('c', 10000)]);
        self::assertResponseStatusCodeSame(201);

        foreach ([
            ['title' => str_repeat('t', 161)],
            ['title' => 'ok', 'content' => str_repeat('c', 10001)],
            ['content' => 'missing title'],
            ['title' => null],
            ['title' => 'ok', 'version' => 1],
        ] as $payload) {
            $client->jsonRequest('POST', '/api/notes', $payload);
            self::assertResponseStatusCodeSame(422);
            self::assertArrayHasKey('error', $this->json());
        }
    }

    public function testUpdateRejectsStaleVersion(): void
    {
        $client = $this->browser;
        $client->jsonRequest('POST', '/api/notes', ['title' => 'Initial', 'content' => 'Text']);
        $created = $this->json();

        $client->jsonRequest('PUT', '/api/notes/'.$created['id'], [
            'title' => 'Current',
            'content' => 'Changed',
            'version' => $created['version'],
        ]);
        self::assertResponseIsSuccessful();

        $client->jsonRequest('PUT', '/api/notes/'.$created['id'], [
            'title' => 'Stale',
            'content' => 'Must not be saved',
            'version' => $created['version'],
        ]);
        self::assertResponseStatusCodeSame(409);

        $client->request('GET', '/api/notes/'.$created['id']);
        self::assertSame('Current', $this->json()['title']);
    }

    public function testUpdateRequiresPositiveVersionAndIncrementsIt(): void
    {
        $client = $this->browser;
        $client->jsonRequest('POST', '/api/notes', ['title' => 'Initial']);
        $created = $this->json();

        foreach ([null, 0, -1, '1'] as $version) {
            $payload = ['title' => 'Changed', 'content' => 'text'];
            if ($version !== null) {
                $payload['version'] = $version;
            }
            $client->jsonRequest('PUT', '/api/notes/'.$created['id'], $payload);
            self::assertResponseStatusCodeSame(422);
        }

        $client->jsonRequest('PUT', '/api/notes/'.$created['id'], [
            'title' => 'Changed', 'content' => 'text', 'version' => $created['version'],
        ]);
        self::assertResponseIsSuccessful();
        self::assertSame($created['version'] + 1, $this->json()['version']);
    }

    public function testListIsEmptyAndRecentlyUpdatedNoteComesFirst(): void
    {
        $client = $this->browser;
        $client->request('GET', '/api/notes');
        self::assertSame([], $this->json()['items']);
        $client->jsonRequest('POST', '/api/notes', ['title' => 'First']);
        $first = $this->json();
        $client->jsonRequest('POST', '/api/notes', ['title' => 'Second']);
        $client->jsonRequest('PUT', '/api/notes/'.$first['id'], [
            'title' => 'First updated', 'content' => '', 'version' => $first['version'],
        ]);
        $client->request('GET', '/api/notes');
        self::assertSame($first['id'], $this->json()['items'][0]['id']);
    }

    public function testWrongFieldTypeAndOversizedContentAreRejected(): void
    {
        $client = $this->browser;
        $client->jsonRequest('POST', '/api/notes', ['title' => 42, 'content' => 'ok']);
        self::assertResponseStatusCodeSame(422);

        $client->jsonRequest('POST', '/api/notes', ['title' => 'ok', 'content' => str_repeat('x', 10001)]);
        self::assertResponseStatusCodeSame(422);
    }

    public function testMalformedJsonAndWrongContentTypeAreRejected(): void
    {
        $client = $this->browser;
        $client->request('POST', '/api/notes', server: ['CONTENT_TYPE' => 'application/json'], content: '{');
        self::assertResponseStatusCodeSame(400);

        $client->request('POST', '/api/notes', server: ['CONTENT_TYPE' => 'text/plain'], content: 'hi');
        self::assertResponseStatusCodeSame(415);

        $client->jsonRequest('POST', '/api/notes', ['title' => 'ok', 'extra' => true]);
        self::assertResponseStatusCodeSame(422);
    }

    public function testMissingNoteOperationsReturnNotFound(): void
    {
        $client = $this->browser;
        $client->request('GET', '/api/notes/99999');
        self::assertResponseStatusCodeSame(404);

        $client->jsonRequest('PUT', '/api/notes/99999', ['title' => 'hello', 'content' => '', 'version' => 1]);
        self::assertResponseStatusCodeSame(404);

        $client->request('DELETE', '/api/notes/99999', server: ['HTTP_X_NOTE_VERSION' => '1']);
        self::assertResponseStatusCodeSame(404);
    }

    public function testDeleteTwiceAndMethodNotAllowedPreservesAllow(): void
    {
        $client = $this->browser;
        $client->jsonRequest('POST', '/api/notes', ['title' => 'Delete me']);
        $created = $this->json();
        $id = $created['id'];
        $client->request('DELETE', '/api/notes/'.$id, server: ['HTTP_X_NOTE_VERSION' => (string) $created['version']]);
        self::assertResponseStatusCodeSame(204);
        self::assertSame('', $client->getResponse()->getContent());
        $client->request('DELETE', '/api/notes/'.$id, server: ['HTTP_X_NOTE_VERSION' => (string) $created['version']]);
        self::assertResponseStatusCodeSame(404);

        $client->request('PATCH', '/api/notes/'.$id);
        self::assertResponseStatusCodeSame(405);
        self::assertNotNull($client->getResponse()->headers->get('Allow'));
        self::assertArrayHasKey('error', $this->json());
    }

    public function testDeleteRequiresCurrentVersionAndPreservesChangedNote(): void
    {
        $client = $this->browser;
        $client->jsonRequest('POST', '/api/notes', ['title' => 'Исходная']);
        $created = $this->json();

        $client->request('DELETE', '/api/notes/'.$created['id']);
        self::assertResponseStatusCodeSame(428);

        $client->request('DELETE', '/api/notes/'.$created['id'], server: ['HTTP_X_NOTE_VERSION' => 'W/"1"']);
        self::assertResponseStatusCodeSame(400);

        $client->request('DELETE', '/api/notes/'.$created['id'], server: ['HTTP_X_NOTE_VERSION' => '"1"']);
        self::assertResponseStatusCodeSame(400);

        $client->jsonRequest('PUT', '/api/notes/'.$created['id'], [
            'title' => 'Изменённая', 'content' => '', 'version' => $created['version'],
        ]);
        $updated = $this->json();

        $client->request('DELETE', '/api/notes/'.$created['id'], server: [
            'HTTP_X_NOTE_VERSION' => (string) $created['version'],
        ]);
        self::assertResponseStatusCodeSame(409);

        $client->request('GET', '/api/notes/'.$created['id']);
        self::assertResponseIsSuccessful();
        self::assertSame('Изменённая', $this->json()['title']);

        $client->request('DELETE', '/api/notes/'.$created['id'], server: [
            'HTTP_X_NOTE_VERSION' => (string) $updated['version'],
        ]);
        self::assertResponseStatusCodeSame(204);
    }

    private function json(): array
    {
        return json_decode((string) $this->browser->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }
}
