<?php

declare(strict_types=1);

namespace App\Tests;

use App\Infrastructure\Persistence\NoteRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Console\Application;
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
        $notes = static::getContainer()->get(NoteRepository::class);
        $notes->clear();
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

        $client->jsonRequest('PUT', '/api/notes/'.$id, ['title' => 'Updated', 'content' => 'Changed']);
        self::assertResponseIsSuccessful();
        self::assertSame('Updated', $this->json()['title']);
        self::assertSame('Changed', $this->json()['content']);

        $client->request('DELETE', '/api/notes/'.$id);
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

        $client->jsonRequest('PUT', '/api/notes/99999', ['title' => 'hello']);
        self::assertResponseStatusCodeSame(404);

        $client->request('DELETE', '/api/notes/99999');
        self::assertResponseStatusCodeSame(404);
    }

    private function json(): array
    {
        return json_decode((string) $this->browser->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }
}
